<?php
defined('BASEPATH') or exit('No direct script access allowed');


require APPPATH . '/libraries/Enums.php';
require APPPATH . '/libraries/RestController.php';

use chriskacerguis\RestServer\RestController;
use Enums\ViewsFetchParams;
use Enums\ViewsOrderValues;

class Views extends RestController
{
	function __construct()
	{
		// Construct the parent class
		parent::__construct();

		// Load the models
		$this->load->model('ViewsModel');
		$this->load->model('SanitiserModel');
		$this->load->model('QueryParametersModel');
	}

	public function index_get()
	{
		// Index only fetches multiple posts' data
		$this->fetchMultiplePosts($_GET);
	}

	public function fetchMultiplePosts($unsanitisedParams)
	{
		// Sanitise the input parameters
		try {
			$paramArray =
				$this->QueryParametersModel->checkQueryParams(
					$unsanitisedParams,
					ViewsFetchParams::class,
					ViewsOrderValues::class);
		} catch (UnknownInputException $error) {
			$this->set_response([
				'status' => 'error',
				'message' => $error->getMessage()
			], $error->getCode());
			return;
		}

		// Change orderType to match the SQL query to use
		if ($paramArray['order']['orderType'] == 'viewCount') {
			$paramArray['order']['orderType'] = 'COUNT(postId)';
		}

		// Fetch views from model
		$views = $this->ViewsModel->fetchViews($paramArray);
		$this->response([
			'status' => 'ok',
			'data' => $views,
			'limit' => $paramArray['limit'],
			'offset' => $paramArray['offset']
		], 200);
	}

	public function index_post()
	{
		// Add a view to a post
		$viewerId = $this->session->userId ?? $this->post('userId');
		$postId = $this->SanitiserModel->sanitiseInt($this->post('postId'));

		try {
			$insertedId = $this->ViewsModel->createView($postId, $viewerId);
		} catch (DatabaseOperationException $error) {
			$this->set_response([
				'status' => 'error',
				'message' => $error->getMessage()
			], $error->getCode());
			return;
		}

		$this->set_response(['status' => 'ok', 'data' => ['id' => $insertedId]], 200);
	}

}
