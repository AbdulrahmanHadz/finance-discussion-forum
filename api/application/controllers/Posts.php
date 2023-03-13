<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/Enums.php';
require APPPATH . '/libraries/RestController.php';

use chriskacerguis\RestServer\RestController;
use Enums\PostsFetchOrderValues;
use Enums\PostsFetchParams;

class Posts extends RestController
{
	function __construct()
	{
		// Construct the parent class
		parent::__construct();

		// Load the models
		$this->load->model('PostsModel');
		$this->load->model('SanitiserModel');
		$this->load->model('QueryParametersModel');

		// Define this endpoint's paths
		$this->paths = [
			'index' => '/(index)',
			'entity' => '/(\d+)',
			'popular' => '/(popular)'
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
			], 405);
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

		// Fetch the data for a single post if id is set and method is not
		if (is_numeric($id) && ($method == 'index' || !isset($method))) {
			$this->fetchSinglePost(
				$id,
				filter_var($this->get('showDeleted') ?? false, FILTER_VALIDATE_BOOL));
			return;
		}

		// Fetch multiple posts
		switch ($method) {
			case 'index':
				$this->fetchMultiplePosts($_GET);
				return;
		}

		$this->set_response([
			'status' => 'error',
			'message' => 'Path not found.'
		], 405);
	}

	private function fetchSinglePost($id, $showDeleted)
	{
		// Get the data of a single post from the model
		$post = $this->PostsModel->fetchPost(['id' => $id], $showDeleted);

		// Check if post was found
		if (!empty($post)) {
			$this->set_response([
				'status' => 'ok',
				'data' => $post
			], 200);
			return;
		}

		// No post found
		$this->set_response([
			'status' => "error",
			'message' => 'Post not found.'
		], 404);
	}

	public function fetchMultiplePosts($unsanitisedParams)
	{
		// Sanitise the input parameters to avoid sql injections and other bypasses
		try {
			$paramArray =
				$this->QueryParametersModel->checkQueryParams(
					$unsanitisedParams,
					PostsFetchParams::class,
					PostsFetchOrderValues::class);
		} catch (UnknownInputException $error) {
			$this->set_response([
				'status' => 'error',
				'message' => $error->getMessage()
			], $error->getCode());
			return;
		}

		// Fetch the data from the model
		$posts = $this->PostsModel->fetchPosts($paramArray);
		$this->response([
			'status' => 'ok',
			'data' => $posts,
			'limit' => $paramArray['limit'],
			'offset' => $paramArray['offset']
		], 200);
	}

	public function index_post()
	{
		// Data array to send to database
		$data_to_insert = array();

		// Check if the person posting is logged in
		$authorId = $this->session->userId ?? $this->post('userId');
		if ($authorId <= 0) {
			$this->set_response(['status' => 'error', 'message' => 'Not logged in.'], 401);
			return;
		}

		$data_to_insert['authorId'] = $authorId;
		// Sanitise the post title and content
		$title =
		$data_to_insert['title'] = $this->SanitiserModel->sanitiseString($this->post('title'));
		$content =
		$data_to_insert['content'] = $this->SanitiserModel->sanitiseString($this->post('content'));

		// Check if the title is empty
		if (empty($title)) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Title cannot be empty or null.'
			], 400);
			return;
		}

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
			$insertedId = $this->PostsModel->createPost($data_to_insert);
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

		// No post to edit specified
		$postId = $segments['id'];
		if (!isset($postId)) {
			$this->set_response('No id specified.', 400);
			return;
		}

		// Fetch the post on the database
		$postOnDb = $this->PostsModel->fetchPost(['id' => $postId]);
		if (empty($postOnDb)) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Post not found.'
			], 404);
			return;
		}

		// Author of the post and logged in user do not match
		if ($authorId != $postOnDb['authorId']) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Not allowed to edit this post.'
			], 403);
			return;
		}

		$data_to_insert['id'] = $postId;
		$data_to_insert['authorId'] = $authorId;
		$title =
		$data_to_insert['title'] = $this->SanitiserModel->sanitiseString($this->put('title'));
		$content =
		$data_to_insert['content'] = $this->SanitiserModel->sanitiseString($this->put('content'));

		if (empty($title)) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Title cannot be empty or null.'
			], 400);
			return;
		}

		if (empty($content)) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Content cannot be empty or null.'
			], 400);
			return;
		}

		try {
			$data = $this->PostsModel->editPost($data_to_insert);
		} catch (DatabaseOperationException $error) {
			$this->set_response([
				'status' => 'error',
				'message' => $error->getMessage()
			], $error->getCode());
			return;
		}
		$this->set_response(['status' => 'ok', 'message' => 'Post updated.'], 200);
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
		$this->PostsModel->deletePost($id, $userId);
		$this->set_response(['status' => 'ok', 'message' => "Deleted '{$id}'"], 200);
	}

	public function popular_get()
	{
		// Call the model method for popular posts
		$data = $this->PostsModel->fetchPopular();

		if (isset($data)) {
			$this->set_response([
				'result' => 'ok',
				'data' => $data
			], 200);
			return;
		}
		$this->set_response([
			'result' => 'error',
			'message' => 'No posts found.'
		], 404);
	}
}
