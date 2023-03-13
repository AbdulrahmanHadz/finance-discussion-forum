<?php
defined('BASEPATH') or exit('No direct script access allowed');

class UsersModel extends CI_Model
{

	public function index()
	{
		$this->load->database();
	}

	public function fetchUsers($paramArray)
	{
		$limit = $paramArray['limit'];
		$offset = $paramArray['offset'];
		$fetchDeleted = $paramArray['fetchDeleted'] ?? false;
		$showDeleted = $paramArray['showDeleted'] ?? false;
		$orderType = $paramArray['order']['orderType'];
		$orderSort = $paramArray['order']['orderSort'];
		$ids = $paramArray['ids'] ?? null;
		$searchParams = array();

		if ($showDeleted) {
			$searchParams['(deletedAt IS NULL OR deletedAt IS NOT NULL)'] = null;
		} else {
			if ($fetchDeleted) {
				$searchParams['deletedAt IS NOT NULL'] = null;
			} else {
				$searchParams['deletedAt IS NULL'] = null;
			}
		}

		$this->db->order_by($orderType, $orderSort);
		if (isset($ids)) {
			$this->db->where_in('id', $ids);
		}
		$query = $this->db->get_where('users', $searchParams, $limit, $offset);
		$returnArray = array();

		foreach ($query->result_array() as $results) {
			$returnArray[] = $this->getApiUserInfo($results);
		}

		return $returnArray;
	}

	public function getApiUserInfo(mixed $results): array
	{
		$userInfo = [];
		foreach ($results as $resultKey => $resultValue) {
			if (!str_contains(strtolower($resultKey), 'password')) {
				$userInfo[$resultKey] = $resultValue;
			}
		}
		return $userInfo;
	}

	public function createUser(string $username, string $email, string $password)
	{
		$data_to_insert = [
			'username' => $username,
			'email' => $email,
			'password' => $password
		];
		$userInsertQuery = $this->db->insert('users', $data_to_insert);

		if (!$userInsertQuery) {
			$error = $this->db->error();
			$errorMessage = $error['message'];
			$errorCode = $error['code'];
			$outputErrorMessage = sprintf('%s. Error %d.', $errorMessage, $errorCode);
			throw new DatabaseOperationException($outputErrorMessage, 403);
		}

		$inserted_id = $this->db->insert_id();
		return $inserted_id;
	}

	public function deleteUser($id, $userId)
	{
		$onDB = $this->fetchUser(['id' => $id]);
		if (!isset($onDB)) {
			return;
		}

		$idOnDB = $onDB['id'];
		$deleted = $onDB['deletedAt'] != null;

		if ($userId == $idOnDB && !$deleted) {
			$this->db->query('UPDATE users SET deletedAt=current_timestamp() WHERE id=?', array(
				$id
			));
		}
	}

	public function fetchUser($params, $fetch_deleted = false)
	{
		if ($fetch_deleted) {
			$params['(deletedAt IS NULL OR deletedAt IS NOT NULL)'] = null;
		} else {
			$params['deletedAt IS NULL'] = null;
		}

		$query = $this->db->get_where('users', $params);
		$data = $query->row();
		if (!empty($data)) {
			return $this->getApiUserInfo($data);
		}
		return null;
	}

	public function editUser($data_to_insert, $id)
	{
		$this->db->set($data_to_insert)->where('id', $id)->update('users');
	}

	public function loginUser($loginInfo, $password)
	{
		$typeOfLogin = array_key_first($loginInfo);
		$loginInfo['deletedAt is NULL'] = null;
		$foundUser = $this->findUser($loginInfo);
		if (empty($foundUser)) {
			throw new DatabaseOperationException("User not found.", 404);
		}

		$passwordOnDB = $foundUser['password'];
		$passwordMatches = password_verify($password, $passwordOnDB);
		if ($passwordMatches) {
			return $this->getApiUserInfo($foundUser);
		} else {
			throw new DatabaseOperationException("Wrong {$typeOfLogin} or password.", 403);
		}
	}

	public function findUser($loginInfo)
	{
		$query = $this->db->get_where('users', $loginInfo);
		return (array)$query->row();
	}

	public function resetPassword($username, $email, $newPassword)
	{
		$foundUser = $this->findUser(['username' => $username, 'email' => $email]);
		if (empty($foundUser)) {
			throw new DatabaseOperationException("Wrong username or email.", 401);
		}

		$query =
			$this->db->set('password', $newPassword)->where('email', $email)->where(
				'username',
				$username)->update('users');
	}

	public function numberOfPosts($userId)
	{
		$query =
			$this->db->query(
				'SELECT authorId as userId, COUNT(authorId) as posts FROM posts WHERE authorId=? AND deletedAt IS NULL',
				[$userId]);
		return (array)$query->row();
	}
}
