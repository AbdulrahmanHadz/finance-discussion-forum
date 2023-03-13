<?php
defined('BASEPATH') or exit('No direct script access allowed');

class UploadsModel extends CI_Model
{

	public function index()
	{
		$this->load->database();
	}

	public function addUpload($uploadInfo)
	{
		$query = $this->db->insert('uploads', $uploadInfo);

		if (!$query) {
			$error = $this->db->error();
			$errorMessage = $error['message'];
			$errorCode = $error['code'];
			$outputErrorMessage = sprintf('%s. Error %d.', $errorMessage, $errorCode);
			throw new DatabaseOperationException($outputErrorMessage, 403);
		}

		$inserted_id = $this->db->insert_id();
		return $inserted_id;
	}

	public function fetchUpload($params)
	{
		$query = $this->db->get_where('uploads', $params);
		return (array)$query->row();
	}

}
