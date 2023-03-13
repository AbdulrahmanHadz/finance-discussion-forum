<?php
defined('BASEPATH') or exit('No direct script access allowed');

class TagsModel extends CI_Model
{

	public function index()
	{
		$this->load->database();
	}

	public function fetchTags($paramArray)
	{
		$searchParams = array();
		$limit = $paramArray['limit'];
		$offset = $paramArray['offset'];
		$orderType = $paramArray['order']['orderType'];
		$orderSort = $paramArray['order']['orderSort'];
		$ids = $paramArray['ids'] ?? null;
		$name = $paramArray['name'] ?? null;

		if ($orderType == 'bestMatch') {
			if (isset($name)) {
				$this->db->order_by(
					sprintf(
						"LOCATE('%s', name)",
						$name));
			} else {
				$orderType = 'createdAt';
			}
		}

		if ($orderType != 'bestMatch') {
			$this->db->order_by($orderType, $orderSort);
		}

		if (isset($ids)) {
			$this->db->where_in('id', $ids);
		}
		if (isset($name)) {
			$this->db->where('name', $name);
		}

		$query = $this->db->get_where('tags', $searchParams, $limit, $offset);
		return $query->result_array();
	}

	public function fetchTag($params)
	{
		$query = $this->db->get_where('tags', $params);
		return (array)$query->row();
	}

	public function fetchSinglePostTags($id, $paramArray)
	{
		$paramArray['ids'] = $id;
		$query = $this->fetchPostTags($paramArray);
		if (array_key_exists($id, $query)) {
			return $query[$id];
		}

		return $query;
	}

	public function createTag($data_to_insert): int
	{
		$postInsertQuery = $this->db->insert('tags', $data_to_insert);

		if (!$postInsertQuery) {
			$error = $this->db->error();
			$errorMessage = $error['message'];
			$errorCode = $error['code'];
			$outputErrorMessage = sprintf('%s. Error %d.', $errorMessage, $errorCode);
			throw new DatabaseOperationException($outputErrorMessage, 403);
		}

		$inserted_id = $this->db->insert_id();
		return $inserted_id;
	}

	public function fetchPostTags($paramArray)
	{
		$limit = $paramArray['limit'];
		$offset = $paramArray['offset'];
		$postIds = $paramArray['ids'] ?? null;
		$tagIds = $paramArray['tags'] ?? null;

		if (isset($postIds)) {
			$this->db->where_in('postId', $postIds);
		}

		if (isset($tagIds)) {
			$this->db->or_where_in('tagId', $tagIds);
		}

		$query = $this->db->limit(
			$limit,
			$offset)
			->get('post_tags');

		$result = $query->result_array();
		$returnArray = array();
		foreach ($result as $key => $tagArray) {
			if (array_key_exists($tagArray['postId'], $returnArray)) {
				$returnArray[$tagArray['postId']][] = $tagArray['tagId'];
			} else {
				$returnArray[$tagArray['postId']] = [$tagArray['tagId']];
			}
		}
		return $returnArray;
	}

	public function removePostTags($postId, $tagIds)
	{
		$this->db->where('postId', $postId)->where_in('tagId', $tagIds)->delete('post_tags');
	}

	public function addPostTags($postId, $tagIds)
	{
		$formattedInsertTags = $this->_create_tag_array($postId, $tagIds);
		$this->db->insert_batch('post_tags', $formattedInsertTags);
	}

	protected function _create_tag_array($postId, $tagIds)
	{
		$array = array();
		foreach ($tagIds as $key => $value) {
			$array[] = array(
				'tagId' => $value,
				'postId' => $postId
			);
		}
		return $array;
	}
}
