<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/Enums.php';
require APPPATH . '/libraries/RestController.php';

use chriskacerguis\RestServer\RestController;
use Enums\UsersFetchOrderValues;
use Enums\UsersFetchParams;

class Users extends RestController
{
	function __construct()
	{
		// Construct the parent class
		parent::__construct();

		// Load the models
		$this->load->model('UsersModel');
		$this->load->model('SanitiserModel');
		$this->load->model('QueryParametersModel');

		// Define this endpoint's paths
		$this->paths = [
			'index' => '/(index)',
			'login' => '/(login)',
			'entity' => '/(\d+)',
			'logout' => '/(logout)',
			'posts' => '/(\d+)\/(posts)'
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

		// Fetch the data for a single user if id is set and method is not
		if (is_numeric($id) && ($method == 'index' || !isset($method))) {
			$this->fetchSingleUser(
				$id,
				filter_var($this->get('showDeleted') ?? false, FILTER_VALIDATE_BOOL));
			return;
		}

		switch ($method) {
			// Fetch multiple users
			case 'index':
				$this->fetchMultipleUsers($_GET);
				return;
			// Fetch the number of posts by a user
			case 'posts' && isset($id):
				$this->fetchNumPosts($id);
				return;
		}

		$this->set_response([
			'status' => 'error',
			'message' => 'Path not found.'
		], 400);
	}

	private function fetchSingleUser(int $id, $showDeleted)
	{
		// Get the data of a single user from the model
		$post = $this->UsersModel->fetchUser(['id' => $id], $showDeleted);

		// Check if user was found
		if (isset($post)) {
			$this->set_response([
				'status' => 'ok',
				'data' => $post
			], 200);
			return;
		}

		// No user found
		$this->set_response([
			'status' => 'error',
			'message' => 'User not found.'
		], 404);
	}

	public function fetchMultipleUsers($unsanitisedParams)
	{
		// Sanitise the input parameters to avoid sql injections and other bypasses
		try {
			$paramArray =
				$this->QueryParametersModel->checkQueryParams(
					$unsanitisedParams,
					UsersFetchParams::class,
					UsersFetchOrderValues::class);
		} catch (UnknownInputException $error) {
			$this->set_response([
				'status' => 'error',
				'message' => $error->getMessage()
			], $error->getCode());
			return;
		}

		// Fetch the data from the model
		$posts = $this->UsersModel->fetchUsers($paramArray);
		$this->response([
			'status' => 'ok',
			'data' => $posts,
			'limit' => $paramArray['limit'],
			'offset' => $paramArray['offset']
		], 200);
	}

	public function index_post()
	{
		// Sanitise the inputs
		$username = $this->SanitiserModel->sanitiseString($this->post('username') ?? null);
		$email = $this->SanitiserModel->sanitiseString($this->post('email') ?? null);

		// User password and confirm password must not be sanitised
		$password = $this->post('password') ?? null;
		$confirmPassword = $this->post('confirmPassword') ?? null;

		// None of the inputs can be empty
		if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Empty fields.'
			], 400);
			return;
		}

		// Username cannot be less than 3 characters and more than 20 characters
		if (strlen($username) < 3 || strlen($username) > 20) {
			$this->set_response([
				'status' => 'error',
				'message' => sprintf(
					"Username can be a minimum of 5 characters and a maximum of 20 characters, your username is %d characters.",
					strlen($username))
			], 400);
			return;
		}

		// Check if the username entered has been taken already
		$usernameTaken = $this->UsersModel->findUser(['username' => $username]);
		if (!empty($usernameTaken)) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Username has been taken.'
			], 409);
			return;
		}

		// Check if the email is in the correct format
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Email is not in the correct format.'
			], 400);
			return;
		}

		// Check if the email entered has been taken
		$emailTaken = $this->UsersModel->findUser(['email' => $email]);
		if (!empty($emailTaken)) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Email has been taken.'
			], 409);
			return;
		}

		// Password cannot be less than 5 characters and more than 72 characters
		if (strlen($password) > 72 || strlen($password) < 5) {
			$this->set_response([
				'status' => 'error',
				'message' => "Password can be a minimum of 5 characters and a maximum of 72 characters."
			], 400);
			return;
		}

		// Check if the password and confirm password match
		if (strcmp($password, $confirmPassword) != 0) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Passwords do not match.'
			], 400);
			return;
		}

		// Hash the password to keep it protected
		$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

		// Insert the data into the database
		try {
			$insertedId = $this->UsersModel->createUser($username, $email, $hashedPassword);
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

		$loggedInUserId = $this->session->userId ?? $this->post('userId');
		if ($loggedInUserId <= 0) {
			$this->set_response(['status' => 'error', 'message' => 'Not logged in.'], 401);
			return;
		}

		// Check if the url segments contain a digit for user editing
		$segments =
			$this->QueryParametersModel->segmentId(
				$this->uri->rsegment_array(),
				[$this->paths['entity']]);

		// No user to edit specified
		$putUserId = $segments['id'];
		if (!isset($putUserId)) {
			$this->set_response('No user selected.', 400);
			return;
		}

		// Fetch the user on the database
		$userDataDb = $this->UsersModel->findUser(['id' => $putUserId]);
		if (empty($userDataDb)) {
			$this->set_response('User not found.', 404);
			return;
		}

		// User on the database and logged in user do not match
		if ($loggedInUserId != $userDataDb['id']) {
			$this->set_response(
				['status' => 'error', 'message' => 'Not allowed to edit this user.'],
				403);
			return;
		}

		// Get the data to replace
		$username = $this->SanitiserModel->sanitiseString($this->put('username'));
		$email = $this->SanitiserModel->sanitiseString($this->put('email'));

		// If post request username and email is empty, use the data on the database already
		$data_to_insert['username'] = $username ?? $userDataDb['username'];
		$data_to_insert['email'] = $email ?? $userDataDb['email'];
		$passwordPut = $this->put('password') ?? null;
		$confirmPasswordPut = $this->put('confirmPassword') ?? null;

		if (!empty($username)) {
			// Username cannot be less than 3 characters and more than 20 characters
			if (strlen($username) < 3 || strlen($username) > 20) {
				$this->set_response([
					'status' => 'error',
					'message' => sprintf(
						"Username can be a minimum of 5 characters and a maximum of 20 characters, your username is %d characters.",
						strlen($username))
				], 400);
				return;
			}

			// Check if the username has been taken already
			$usernameTaken = $this->UsersModel->findUser(['username' => $username]);
			if (!empty($usernameTaken)) {
				$this->set_response([
					'status' => 'error',
					'message' => 'Username has been taken.'
				], 409);
				return;
			}
			$data_to_insert['username'] = $username;
		} else {
			// Use the username on the database already
			$data_to_insert['username'] = $userDataDb['username'];
		}

		if (!empty($email)) {
			// Check if the email is in the correct format
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$this->set_response([
					'status' => 'error',
					'message' => 'Email is not in the correct format.'
				], 400);
				return;
			}

			// Check if the email has been taken already
			$emailTaken = $this->UsersModel->findUser(['email' => $email]);
			if (!empty($emailTaken)) {
				$this->set_response([
					'status' => 'error',
					'message' => 'Email has been taken.'
				], 409);
				return;
			}
			$data_to_insert['email'] = $email;
		} else {
			// Use the email on the database
			$data_to_insert['email'] = $userDataDb['email'];
		}

		if (!empty($passwordPut)) {
			// Password cannot be less than 5 characters and more than 72 characters
			if (strlen($passwordPut) > 72 || strlen($passwordPut) < 5) {
				$this->set_response([
					'status' => 'error',
					'message' => "Password can be a minimum of 5 characters and a maximum of 72 characters."
				], 400);
				return;
			}

			// Check the passwords match
			if (strcmp($passwordPut, $confirmPasswordPut) != 0) {
				$this->set_response([
					'status' => 'error',
					'message' => 'Passwords do not match.'
				], 400);
				return;
			}

			// Hash the password
			$data_to_insert['password'] = password_hash($passwordPut, PASSWORD_DEFAULT);
		} else {
			// Don't update the password
			$data_to_insert['password'] = $userDataDb['password'];
		}

		// Update the entry on the database
		try {
			$this->UsersModel->editUser($data_to_insert, $loggedInUserId);
		} catch (DatabaseOperationException $error) {
			$this->set_response([
				'status' => 'error',
				'message' => $error->getMessage()
			], $error->getCode());
			return;
		}
		$this->set_response(['status' => 'ok', 'message' => 'User updated.'], 200);
	}

	public function index_delete()
	{
		$userId = $this->session->userId ?? $this->delete('userId');
		if ($userId <= 0) {
			$this->set_response(['status' => 'error', 'message' => 'Not logged in.'], 401);
			return;
		}

		$segments =
			$this->QueryParametersModel->segmentId(
				$this->uri->rsegment_array(),
				[$this->paths['entity']]);

		if (!isset($segments)) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Method not allowed.'
			], 405);
			return;
		}

		$id = $segments['id'] ?? null;
		if (!isset($id)) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Method not allowed.'
			], 405);
			return;
		}

		// Check if logged in user is the same as on the database
		if ($userId != $id) {
			$this->set_response(
				['status' => 'error', 'message' => 'Not allowed to delete this user.'],
				403);
			return;
		}

		try {
			$id = $this->SanitiserModel->sanitiseInputId($id);
		} catch (UnknownInputException $error) {
			$this->set_response([
				'status' => 'error',
				'message' => $error->getMessage()
			], $error->getCode());
			return;
		}

		$this->UsersModel->deleteUser($id, $userId);
		$this->set_response(['status' => 'ok', 'message' => "Deleted '{$id}'"], 201);
	}

	public function login_post()
	{
		// Sanitise the username
		$userOrEmail = $this->SanitiserModel->sanitiseString($this->post('username') ?? null);
		$password = $this->post('password') ?? null;

		// Username and password cannot be empty
		if (empty($userOrEmail) || empty($password)) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Empty fields.'
			], 400);
			return;
		}

		$loginArray = array();
		// Check if input username is in the format of an email
		if (filter_var($userOrEmail, FILTER_VALIDATE_EMAIL)) {
			$loginArray['email'] = $userOrEmail;
		} else {
			// If not, use as username
			$loginArray['username'] = $userOrEmail;
		}

		$data = null;

		// Login to user on the database
		try {
			$data = $this->UsersModel->loginUser($loginArray, $password);
		} catch (DatabaseOperationException $error) {
			$this->set_response([
				'status' => 'error',
				'message' => $error->getMessage()
			], $error->getCode());
			return;
		}

		// Update the session with the logged in user's details
		if (isset($data)) {
			$this->session->set_userdata([
				'userId' => $data['id'],
				'username' => $data['username']
			]);
			session_commit();
			$this->set_response([
				'result' => 'ok',
				'data' => $data
			], 200);
			return;
		}
		$this->set_response([
			'result' => 'error',
			'message' => 'Something went wrong.'
		], 500);
	}

	public function logout_delete()
	{
		// Remove the saved session
		$this->session->unset_userdata(['userId', 'username']);;
		$this->set_response([
			'result' => 'ok'
		], 200);
	}

	public function reset_post()
	{
		$username = $this->SanitiserModel->sanitiseString($this->post('username') ?? null);
		$email = $this->SanitiserModel->sanitiseString($this->post('email') ?? null);
		$password = $this->post('password') ?? null;
		$confirmPassword = $this->post('confirmPassword') ?? null;

		if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Empty fields.'
			], 401);
			return;
		}

		if (strlen($password) > 72 || strlen($confirmPassword) > 72) {
			$this->set_response([
				'status' => 'error',
				'message' => "Password can be a maximum of 72 characters."
			], 401);
			return;
		}

		if (strcmp($password, $confirmPassword) != 0) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Passwords do not match.'
			], 401);
			return;
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->set_response([
				'status' => 'error',
				'message' => 'Email is not in the correct format.'
			], 401);
			return;
		}

		$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

		try {
			$response = $this->UsersModel->resetPassword($username, $email, $hashedPassword);
		} catch (DatabaseOperationException $error) {
			$this->set_response([
				'status' => 'error',
				'message' => $error->getMessage()
			], $error->getCode());
			return;
		}

		$this->set_response([
			'status' => 'ok',
			'message' => 'Password reset.'
		], 200);
	}

	public function fetchNumPosts($userId)
	{
		// Get the number of posts a user has from the model
		$posts = $this->UsersModel->numberOfPosts($userId);
		$this->set_response([
			'status' => 'ok',
			'data' => $posts
		], 200);
	}
}
