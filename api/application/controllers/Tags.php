<?php
defined('BASEPATH') or exit('No direct script access allowed');


require APPPATH . '/libraries/Enums.php';
require APPPATH . '/libraries/RestController.php';

use chriskacerguis\RestServer\RestController;
use Enums\PostTagsFetchParams;
use Enums\TagsFetchOrderValues;
use Enums\TagsFetchParams;

class Tags extends RestController
{

	function __construct()
	{
		// Construct the parent class
		parent::__construct();

		// Load the models
		$this->load->model('TagsModel');
		$this->load->model('PostsModel');
		$this->load->model('SanitiserModel');
		$this->load->model('QueryParametersModel');

		// Define this endpoint's paths
		$this->paths = ['index' => '/(index)',
			'entity' => '/(\d+)',
			'posts' => '/(post)',
			'post' => '/(post)\/(\d+)'
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
			], 404);
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

		// Fetch the data for a single tag if id is set and method is not
		if (is_numeric($id) && ($method == 'index' || !isset($method))) {
			$this->fetchSingleTag($id);
			return;
		}

		switch ($method) {
			// Fetch multiple tags
			case 'index':
				$this->fetchMultipleTags($_GET);
				return;
			// Fetch the tags of a single post
			case 'post' && isset($id):
				$this->fetchSinglePostTags($id, $_GET);
				return;
			// Fetch the tags of a multiple posts
			case 'post' && !isset($id):
				$this->fetchMultiplePostsTags($_GET);
				return;
		}

		$this->set_response([
			'status' => 'error',
			'message' => 'Path not found.'
		], 404);
	}

	public function index_post()
	{
		// Check if the person creating a tag is logged in
		$authorId = $this->session->userId ?? $this->post('userId');
		if ($authorId <= 0) {
			$this->set_response(['status' => 'error', 'message' => 'Not logged in.'], 401);
			return;
		}

		// Get the url segments
		$segments =
			$this->QueryParametersModel->segmentId(
				$this->uri->rsegment_array(),
				[$this->paths['index'], $this->paths['post']]);

		if (!isset($segments)) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Path not found.'
			], 404);
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

		// Fetch the data for a single tag if id is set and method is not
		if (is_numeric($id) && ($method == 'index' || !isset($method))) {
			$this->fetchSingleTag($id);
			return;
		}

		switch ($method) {
			// Create a tag
			case 'index':
				$this->createTag();
				return;
			// Add a tag to a post
			case 'post' && isset($id):
				$this->updatePostTags($id, $authorId);
				return;
		}

		$this->set_response([
			'status' => 'error',
			'message' => 'Path not found.'
		], 404);

	}

	private function fetchSingleTag(int $id)
	{
		// Get the data of a single tag from the model
		$post = $this->TagsModel->fetchTag(['id' => $id]);

		// Check if tag was found
		if (isset($post)) {
			$this->set_response([
				'status' => 'ok',
				'data' => $post
			], 200);
			return;
		}

		// No tag found
		$this->set_response([
			'status' => "error",
			'message' => 'Tag not found.'
		], 404);
	}

	public function fetchMultipleTags($unsanitisedParams)
	{
		// Sanitise the input parameters to avoid sql injections and other bypasses
		try {
			$paramArray =
				$this->QueryParametersModel->checkQueryParams(
					$unsanitisedParams,
					TagsFetchParams::class,
					TagsFetchOrderValues::class);
		} catch (UnknownInputException $error) {
			$this->set_response([
				'status' => 'error',
				'message' => $error->getMessage()
			], $error->getCode());
			return;
		}

		// Fetch the data from the model
		$tags = $this->TagsModel->fetchTags($paramArray);
		$this->response([
			'status' => 'ok',
			'data' => $tags,
			'limit' => $paramArray['limit'],
			'offset' => $paramArray['offset']
		], 200);
	}

	public function fetchSinglePostTags($id, $getParams)
	{
		// Sanitise the input parameters to avoid sql injections and other bypasses
		try {
			$paramArray =
				$this->QueryParametersModel->checkQueryParams(
					$getParams,
					PostTagsFetchParams::class);
		} catch (UnknownInputException $error) {
			$this->set_response([
				'status' => 'error',
				'message' => $error->getMessage()
			], $error->getCode());
			return;
		}

		// Fetch the tags of a single post from the model
		$tags = $this->TagsModel->fetchSinglePostTags($id, $paramArray);
		$this->response([
			'status' => 'ok',
			'data' => $tags,
			'limit' => $paramArray['limit'],
			'offset' => $paramArray['offset']
		], 200);
	}

	public function fetchMultiplePostsTags($getParams)
	{
		// Sanitise the input parameters
		try {
			$paramArray =
				$this->QueryParametersModel->checkQueryParams(
					$getParams,
					PostTagsFetchParams::class);
		} catch (UnknownInputException $error) {
			$this->set_response([
				'status' => 'error',
				'message' => $error->getMessage()
			], $error->getCode());
			return;
		}

		// Fetch the tags of a multiple posts from the model
		$tags = $this->TagsModel->fetchPostTags($paramArray);
		$this->response([
			'status' => 'ok',
			'data' => $tags,
			'limit' => $paramArray['limit'],
			'offset' => $paramArray['offset']
		], 200);
	}

	public function createTag()
	{
		// Data array to send to database
		$data_to_insert = array();
		// Sanitise the tag name and description
		$tagName =
		$data_to_insert['name'] =
			$this->SanitiserModel->sanitiseString($this->post('name') ?? null);
		$data_to_insert['description'] = $this->SanitiserModel
			->sanitiseString($this->post('description') ?? null);

		// Tag name cannot be empty
		if (empty($tagName)) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Tag name cannot be empty or null.'
			], 400);
			return;
		}

		// Call the model method to add the data to the database
		try {
			$insertedId = $this->TagsModel->createTag($data_to_insert);
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

	public function updatePostTags($postId, $userId)
	{
		// Get the tag ids to add or remove from the post request and sanitise the input array as a
		// list of ints
		$tagIdsToAdd = $this->SanitiserModel->walkIntArray(
			$this->post('tagIdsToAdd') ??
			null);
		$tagIdsToRemove =
			$this->SanitiserModel->walkIntArray($this->post('tagIdsToRemove') ?? null);

		// One of the two arrays needs to be defined
		if (empty($tagIdsToAdd) && empty($tagIdsToRemove)) {
			$this->set_response([
				'status' => 'error',
				'message' => 'No tags to add or remove specified.'
			], 400);
			return;
		}

		// Fetch the data for the post to add tags to
		$postOnDb = $this->PostsModel->fetchPost(['id' => $postId]);
		if (empty($postOnDb)) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Post not found.'
			], 404);
			return;
		}

		// Logged in user is not the author of the post on the database
		if ($userId != $postOnDb['authorId']) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Not allowed to edit this post.'
			], 403);
			return;
		}

		// Sanitise the input parameters
		try {
			$paramArray =
				$this->QueryParametersModel->checkQueryParams(
					[],
					PostTagsFetchParams::class);
		} catch (UnknownInputException $error) {
			$this->set_response([
				'status' => 'error',
				'message' => $error->getMessage()
			], $error->getCode());
			return;
		}

		// Remove duplicate tags from the tagIdsToAdd array if they are in the tagIdsToRemove array
		$tagIdsToAdd = array_diff($tagIdsToAdd, $tagIdsToRemove);

		// Skip adding empty arrays to the database
		if (!empty($tagIdsToAdd)) {
			$paramArray['ids'] = [$postId];
			// Fetch the current post's tags from the database
			$currentPostTags = $this->TagsModel->fetchPostTags($paramArray);

			// Remove duplicate tags from the tagIdsToAdd array if they are already in the post
			// tags to avoid duplicate inserts
			$tagIdsToAdd = array_diff($tagIdsToAdd, $currentPostTags);
			// Don't add empty arrays to the database
			if (!empty($tagIdsToAdd)) {
				$this->TagsModel->addPostTags($postId, $tagIdsToAdd);
			}
		}

		// Skip adding empty arrays to the database
		if (!empty($tagIdsToRemove)) {
			$this->TagsModel->removePostTags($postId, $tagIdsToRemove);
		}

		$this->set_response([
			'status' => 'ok',
			'message' => "Updated tags for post '{$postId}'."
		], 200);
	}
}
