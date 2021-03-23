<?php

namespace App\Controllers;

use App\Models\ItemModel;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Exception;

class Item extends BaseController
{
	use ResponseTrait;
	private function getKey()
	{
		return "qsBIoaE1ReVa6cM8LahNTrN838EpXf7v";
	}
	public function index()
	{
		//
	}
	public function getItems($itemCode = null)
	{
		$key = $this->getKey();
		$token = $this->request->getHeaderLine("Authorization");

		
		$decoded = JWT::decode($token, $key, array("HS256"));

		if ($decoded) {

			$model = new ItemModel();
			$data = $model->builder()->like('item_code', $itemCode, 'before')->get()->getResultArray();
			$response = [
				'status'   => 200,
				'message' =>  'Items Found successfully',
				'data' => $data
			];
			return $this->respond($response, 200, "Data Found");
		}
	}
	public function putItemImage()
	{
		$key = $this->getKey();
		$token = $this->request->getHeaderLine("Authorization");

		try {
			$decoded = JWT::decode($token, $key, array("HS256"));

			if ($decoded) {
			    
                $this->request->getFile('item_image')->store(
                '../../../uploads/item/product/',
                $this->request->getVar('item_code') . '.jpg'
                );
				$response = [
					'status'   => 200,
					'message' =>  'Image Uploaded Successfully',
					'data' => null
				];
				return $this->respond($response, 200, "Data Found");
			}
		} catch (Exception $ex) {
			$response = [
			    'status' => 401,
				'data' => null,
				'message' => 'Access denied'
			];
			return $this->respond($response);
		}
	}
}
