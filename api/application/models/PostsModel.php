<?php
defined('BASEPATH') or exit('No direct script access allowed');

class PostsModel extends CI_Model
{

	public function fetchPosts($paramArray)
	{
		// Set the parameters needed for search
		$searchParams = array();
		$limit = $paramArray['limit'];
		$offset = $paramArray['offset'];
		$fetchDeleted = $paramArray['fetchDeleted'];
		$showDeleted = $paramArray['showDeleted'];
		$orderType = $paramArray['order']['orderType'];
		$orderSort = $paramArray['order']['orderSort'];
		// Ids, authorId and title can be null to not search for them
		$ids = $paramArray['ids'] ?? null;
		$authorId = $paramArray['authorId'] ?? null;
		$title = $paramArray['title'] ?? null;

		// Show the deleted posts
		if ($showDeleted) {
			$searchParams['(deletedAt IS NULL OR deletedAt IS NOT NULL)'] = null;
		} else {
			if ($fetchDeleted) {
				// Show only the deleted posts
				$searchParams['deletedAt IS NOT NULL'] = null;
			} else {
				// Don't show the deleted posts
				$searchParams['deletedAt IS NULL'] = null;
			}
		}

		// bestMatch order type uses a different SQL query
		if ($orderType == 'bestMatch') {
			if (isset($title)) {
				// Order by the best match of the title
				$this->db->order_by(
					sprintf(
						"LOCATE('%s', title)",
						$title));
			} else {
				// Default sort option
				$orderType = 'createdAt';
			}
		}

		// Add the ordering if bestMatch is not the orderType
		if ($orderType != 'bestMatch') {
			$this->db->order_by($orderType, $orderSort);
		}
		// Search only for posts if their id is in the ids array
		if (isset($ids)) {
			$this->db->where_in('id', $ids);
		}
		// Search for posts by the specified author
		if (isset($authorId)) {
			$this->db->where('authorId', $authorId);
		}
		// Search for posts where the title is like the input search title
		if (isset($title)) {
			$this->db->like('title', $title);
		}

		// Get the posts from the database
		$query = $this->db->get_where('posts', $searchParams, $limit, $offset);
		return $query->result_array();
	}

	public function createPost($data_to_insert): int
	{
		// Add the post to the database using the data_to_insert array
		$postInsertQuery = $this->db->insert('posts', $data_to_insert);

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

	public function editPost($data_to_insert)
	{
		// Fetch the post from the database using the id
		$postOnDb = $this->fetchPost(
			['id' => $data_to_insert['id']]);

		// Post wasn't found
		if (empty($postOnDb)) {
			throw new DatabaseOperationException('Post not found.', 404);
		}

		// Logged in user doesn't match author on database
		$postAuthor = $postOnDb['authorId'];
		if ($postAuthor != $data_to_insert['authorId']) {
			throw new DatabaseOperationException('Not allowed to edit this post.', 403);
		}

		// Edit the record with the new data
		$this->db
			->set('title', $data_to_insert['title'])
			->set('content', $data_to_insert['content'])
			->where('id', $data_to_insert['id'])
			->where('authorId', $data_to_insert['authorId'])
			->update('posts');
	}

	public function fetchPost($params, $fetch_deleted = false)
	{
		if ($fetch_deleted) {
			$params['(deletedAt IS NULL OR deletedAt IS NOT NULL)'] = null;
		} else {
			$params['deletedAt IS NULL'] = null;
		}

		// Fetch a single post from the database
		$query = $this->db->get_where('posts', $params);
		return (array)$query->row();
	}

	public function deletePost($id, $userId)
	{
		// Fetch the post on the database
		$onDB = $this->fetchPost(['id' => $id]);
		// Silent return if the post doesn't exist
		if (empty($onDB)) {
			return;
		}

		$authorId = $onDB['authorId'];
		$deleted = $onDB['deletedAt'] != null;

		// Silent return if the user doesn't match the author on database
		if ($userId == $authorId && !$deleted) {
			// Update the record to have deleted be now()
			$this->db->query(
				'UPDATE posts SET deletedAt=current_timestamp() WHERE id=? AND authorId=?',
				array(
					$id,
					$authorId
				));
		}
	}

	public function fetchPopular()
	{
		// WHERE viewedAt > (now() - INTERVAL 24 HOUR)

		// Fetch the 5 posts with the most views
		$query =
			$this->db->query(
				'SELECT postId, COUNT(postId) as views FROM views GROUP BY postId ORDER BY COUNT(postId) DESC LIMIT 5;');
		$resultArray = $query->result_array();

		// Return an empty array if there are no posts
		if (!isset($resultArray)) {
			return [];
		}

		return $resultArray;
	}
}
