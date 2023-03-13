<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CommentsModel extends CI_Model
{

	public function fetchComments($paramArray)
	{
		$limit = $paramArray['limit'];
		$offset = $paramArray['offset'];
		$fetchDeleted = $paramArray['fetchDeleted'];
		$showDeleted = $paramArray['showDeleted'];
		$orderType = $paramArray['order']['orderType'];
		$orderSort = $paramArray['order']['orderSort'];
		$ids = $paramArray['ids'] ?? null;
		$postIds = $paramArray['postIds'] ?? null;
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
		if (!empty($ids)) {
			$this->db->where_in('id', $ids);
		}
		if (!empty($postIds)) {
			$this->db->where_in('postId', $postIds);
		}

		$query = $this->db->get_where('comments', $searchParams, $limit, $offset);
		return $query->result_array();
	}

	public function createComment($data_to_insert): int
	{
		$commentInsertQuery = $this->db->insert('comments', $data_to_insert);

		if (!$commentInsertQuery) {
			$error = $this->db->error();
			$errorMessage = $error['message'];
			$errorCode = $error['code'];
			$outputErrorMessage = sprintf('%s. Error %d.', $errorMessage, $errorCode);
			throw new DatabaseOperationException($outputErrorMessage, 403);
		}

		$inserted_id = $this->db->insert_id();
		return $inserted_id;
	}

	public function editComment($data_to_insert)
	{
		$commentOnDb = $this->fetchComment(
			['id' => $data_to_insert['id']]);
		if (empty($commentOnDb)) {
			throw new DatabaseOperationException('Comment not found.', 404);
		}

		$commentAuthor = $commentOnDb['authorId'];
		if ($commentAuthor != $data_to_insert['authorId']) {
			throw new DatabaseOperationException('Not allowed to edit this comment.', 403);
		}

		$this->db
			->set('content', $data_to_insert['content'])
			->where('id', $data_to_insert['id'])
			->where('authorId', $data_to_insert['authorId'])
			->update('comments');
	}

	public function fetchComment($params, $fetch_deleted = false)
	{
		if ($fetch_deleted) {
			$params['(deletedAt IS NULL OR deletedAt IS NOT NULL)'] = null;
		} else {
			$params['deletedAt IS NULL'] = null;
		}

		$query = $this->db->get_where('comments', $params);
		return (array)$query->row();
	}

	public function deleteComment($id, $userId)
	{
		$onDB = $this->fetchComment(['id' => $id]);
		if (empty($onDB)) {
			return;
		}

		$authorId = $onDB['authorId'];
		$deleted = $onDB['deletedAt'] != null;

		if ($userId == $authorId && !$deleted) {
			$this->db->query(
				'UPDATE comments SET deletedAt=current_timestamp() WHERE id=? AND authorId=?',
				array(
					$id,
					$authorId
				));
		}
	}

	public function fetchNumComments($postId)
	{
		$query =
			$this->db->query(
				'SELECT COUNT(postId) as numReplies FROM comments WHERE postId=? AND deletedAt IS NULL GROUP BY postId;',
				array($postId));
		$row = (array)$query->row();
		if (empty($row)) {
			return ['numReplies' => 0];
		}

		return $row;
	}
}
