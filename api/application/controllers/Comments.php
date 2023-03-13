<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/Enums.php';
require APPPATH . '/libraries/RestController.php';

use chriskacerguis\RestServer\RestController;
use Enums\CommentsFetchOrderValues;
use Enums\CommentsFetchParams;

class Comments extends RestController
{
	function __construct()
	{
		// Construct the parent class
		parent::__construct();

		// Load the models
		$this->load->model('CommentsModel');
		$this->load->model('SanitiserModel');
		$this->load->model('QueryParametersModel');

		// Define this endpoint's paths
		$this->paths = [
			'index' => '/(index)',
			'entity' => '/(\d+)',
			'num_comments' => '/(posts)\/(\d+)'
		];
	}

	public function index_get()
	{
		// Get the segments from the url
		$segments =
			$this->QueryParametersModel->segmentId($this->uri->rsegment_array(), $this->paths);

		if (!isset($segments)) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Path not found.'
			], 400);
			return;
		}

		// Get the id from the segment array or the get request
		$id = $segments['id'] ?? $this->get('id') ?? null;
		// Method is the function to call for processing the request
		$method = $segments['method'] ?? null;

		// Make sure id inputted is an int
		try {
			$id = $this->SanitiserModel->sanitiseInputId($id);
		} catch (UnknownInputException $error) {
			$this->set_response([
				'status' => 'error',
				'message' => $error->getMessage()
			], $error->getCode());
			return;
		}

		// Fetch the data for a single comment if id is set and method is not
		if (is_numeric($id) && ($method == 'index' || !isset($method))) {
			$this->fetchSingleComment(
				$id,
				filter_var($this->get('showDeleted') ?? false, FILTER_VALIDATE_BOOL));
			return;
		}

		switch ($method) {
			// Fetch multiple comments
			case 'index':
				$this->fetchMultipleComments($_GET);
				return;
			// Fetch the number of comments for a post
			case 'posts' && isset($id):
				$this->fetchNumComments($id);
				return;
		}

		$this->set_response([
			'status' => 'error',
			'message' => 'Path not found.'
		], 400);
	}

	private function fetchSingleComment(int $id, $showDeleted)
	{
		// Get the data of a single comment from the model
		$comment = $this->CommentsModel->fetchComment(['id' => $id], $showDeleted);

		// Check if comment was found
		if (!empty($comment)) {
			$this->set_response([
				'status' => 'ok',
				'data' => $comment
			], 200);
			return;
		}

		// No comment found
		$this->set_response([
			'status' => "error",
			'message' => 'Comment not found.'
		], 404);
	}

	public function fetchMultipleComments($unsanitisedParams)
	{
		// Sanitise the input parameters to avoid sql injections and other bypasses
		try {
			$paramArray =
				$this->QueryParametersModel->checkQueryParams(
					$unsanitisedParams,
					CommentsFetchParams::class,
					CommentsFetchOrderValues::class);
		} catch (UnknownInputException $error) {
			$this->set_response([
				'status' => 'error',
				'message' => $error->getMessage()
			], $error->getCode());
			return;
		}

		// Fetch the data from the model
		$comments = $this->CommentsModel->fetchComments($paramArray);
		$this->response([
			'status' => 'ok',
			'data' => $comments,
			'limit' => $paramArray['limit'],
			'offset' => $paramArray['offset']
		], 200);
	}

	public function fetchNumComments($postId)
	{
		// Call the method for fetching the number of comments a post has
		$numReplies = $this->CommentsModel->fetchNumComments($postId);
		$this->set_response(['status' => 'ok', 'data' => $numReplies], 200);
	}

	public function index_post()
	{
		// Data array to send to database
		$data_to_insert = array();

		// Check if the person creating a comment is logged in
		$authorId = $this->session->userId ?? $this->post('userId');
		if ($authorId <= 0) {
			$this->set_response(['status' => 'error', 'message' => 'Not logged in.'], 401);
			return;
		}

		$data_to_insert['postId'] = $this->post('postId');
		$data_to_insert['authorId'] = $authorId;
		// Sanitise the comment content
		$content =
		$data_to_insert['content'] =
			$this->SanitiserModel->sanitiseString($this->post('content'));

		// Check if the content is empty
		if (empty($content)) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Content cannot be empty or null.'
			], 400);
			return;
		}

		// Call the model method to add the data to the database
		try {
			$insertedId = $this->CommentsModel->createComment($data_to_insert);
		} catch (DatabaseOperationException $error) {
			$this->set_response([
				'status' => 'error',
				'message' => $error->getMessage()
			], $error->getCode());
			return;
		}
		// Show response of the inserted data with the inserted id
		$this->set_response(['status' => 'ok', 'data' => ['id' => $insertedId]], 200);
	}

	public function index_put()
	{
		$data_to_insert = array();

		$authorId = $this->session->userId ?? $this->put('userId');
		if ($authorId <= 0) {
			$this->set_response(['status' => 'error', 'message' => 'Not logged in.'], 401);
			return;
		}

		// Check if the url segments contain a digit for post editing
		$segments =
			$this->QueryParametersModel->segmentId(
				$this->uri->rsegment_array(),
				[$this->paths['entity']]);

		// No comment to edit specified
		$commentId = $segments['id'];
		if (!isset($commentId)) {
			$this->set_response('No id specified.', 400);
			return;
		}

		// Fetch the comment on the database
		$commentOnDb = $this->CommentsModel->fetchComment(['id' => $commentId]);
		if (empty($commentOnDb)) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Comment not found.'
			], 404);
			return;
		}

		// Author of the post and logged in user do not match
		if ($authorId != $commentOnDb['authorId']) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Not allowed to edit this comment.'
			], 403);
			return;
		}

		$data_to_insert['id'] = $commentId;
		$data_to_insert['authorId'] = $authorId;
		$content =
		$data_to_insert['content'] = $this->SanitiserModel->sanitiseString($this->put('content'));

		if (empty($content)) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Content cannot be empty or null.'
			], 400);
			return;
		}

		try {
			$data = $this->CommentsModel->editComment($data_to_insert);
		} catch (DatabaseOperationException $error) {
			$this->set_response([
				'status' => 'error',
				'message' => $error->getMessage()
			], $error->getCode());
			return;
		}
		$this->set_response(['status' => 'ok', 'message' => 'Comment updated.'], 200);
	}

	public function index_delete()
	{
		$userId = $this->session->userId ?? $this->delete('userId');
		if ($userId <= 0) {
			$this->set_response(['status' => 'error', 'message' => 'Not logged in.'], 401);
			return;
		}

		// Get the id of the post to delete from the url segments
		$id = $this->QueryParametersModel->segmentId($this->uri->rsegment_array());
		try {
			$id = $this->SanitiserModel->sanitiseInputId($id);
		} catch (UnknownInputException $error) {
			$this->set_response([
				'status' => 'error',
				'message' => $error->getMessage()
			], $error->getCode());
			return;
		}

		// Delete the post on the database
		$this->CommentsModel->deleteComment($id, $userId);
		$this->set_response(['status' => 'ok', 'message' => "Deleted '{$id}'"], 200);
	}
}
