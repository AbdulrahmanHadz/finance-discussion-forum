<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ViewsModel extends CI_Model
{
	public function fetchViews($paramArray)
	{
		$limit = $paramArray['limit'];
		$offset = $paramArray['offset'];
		$orderType = $paramArray['order']['orderType'];
		$orderSort = $paramArray['order']['orderSort'];

		$this->db->order_by($orderType, $orderSort);
		if (isset($paramArray['postIds'])) {
			$this->db->where_in('postId', $paramArray['postIds']);
		}
		$query =
			$this->db->select('postId, COUNT(postId) as views')->from('views')->group_by('postId')
				->order_by($orderType, $orderSort)->limit($limit, $offset)->get();
		return $query->result_array();
	}

	public function createView($postId,
		$viewerId): int
	{
		$data_to_insert = [
			'postId' => $postId,
			'viewerId' => $viewerId
		];
		$postInsertQuery = $this->db->insert('views', $data_to_insert);

		if (!$postInsertQuery) {
			$error = $this->db->error();
			$errorMessage = $error['message'];
			$errorCode = $error['code'];
			$outputErrorMessage = sprintf('%s. Error %d.', $errorMessage, $errorCode);
			throw new DatabaseOperationException($outputErrorMessage, 500);
		}

		$inserted_id = $this->db->insert_id();
		return $inserted_id;
	}
}
