<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/Enums.php';
require APPPATH . '/libraries/RestController.php';

use chriskacerguis\RestServer\RestController;

class Uploads extends RestController
{
	public $uploadsPath = APPPATH . "uploads";

	function __construct()
	{
		// Construct the parent class
		parent::__construct();

		$this->load->model('UploadsModel');

		if (!file_exists($this->uploadsPath)) {
			mkdir($this->uploadsPath, 0777, true);
		}
	}

	public function index_post()
	{
		$uuid = $this->generate_v4_uuid();

		$config['file_name'] = $uuid;
		$config['upload_path'] = $this->uploadsPath;
		$config['allowed_types'] = 'gif|jpg|png|jpeg';
		$config['file_ext_tolower'] = true;
		$config['max_size'] = 5120;
		$config['max_width'] = 0;
		$config['max_height'] = 0;

		$this->load->library('upload', $config);
		$uploaded = $this->upload->do_upload('uploadFile');

		if ($uploaded) {
			$filename = $this->upload->data('file_name');
			$filepath = sprintf('%s\%s', $this->uploadsPath, $filename);
			$htmlTag = "<img src='$filepath' alt='Uploaded image {$filename}'/>";

			$uploadedData = ['id' => $uuid,
				'filename' => $filename,
				'filepath' => $filepath
			];

			try {
				$this->UploadsModel->addUpload($uploadedData);
			} catch (DatabaseOperationException $error) {
				$this->set_response([
					'status' => 'error',
					'message' => $error->getMessage()
				], $error->getCode());
				return;
			}

			$this->set_response([
				'status' => 'ok',
				'data' => array_merge($uploadedData, [
					'formatted' => $htmlTag
				])
			], 200);
			return;
		}

		$this->set_response([
			'status' => 'error',
			'message' => $this->upload->display_errors('', '')
		], 400);
	}

	public function generate_v4_uuid()
	{
		return sprintf(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

			// 32 bits for "time_low"
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),

			// 16 bits for "time_mid"
			mt_rand(0, 0xffff),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand(0, 0x0fff) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand(0, 0x3fff) | 0x8000,

			// 48 bits for "node"
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff)
		);
	}
}
