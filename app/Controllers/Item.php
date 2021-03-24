<?php

namespace App\Controllers;


use App\Models\InventoryModel;
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
	public function checkBarCode()
	{
		
        $key = $this->getKey();
        $token = $this->request->getHeaderLine("Authorization");

     
        $decoded = JWT::decode($token, $key, array("HS256"));

        if ($decoded) {
            if(!$this->request->getVar('item_code')){
                 $response = [
                    'status'   => 200,
                    'message'    => 'bar code missing',
                    'data' => null
                ];
                return $this->respond($response);
            }
            
            
            $model = new InventoryModel();
            $itemModel= new ItemModel();
            $data=$itemModel->where('item_code', $this->request->getVar('item_code'))->first();
            if($model->where(['item_code' =>  $this->request->getVar('item_code'), 'user_id' =>  $decoded->data->user_ID])->first()){
                $response = [
                    'status'   => 409,
                    'message'    => 'Item Already Exists',
                    'data' => null
                ];
                return $this->respond($response);
            }else if($data){
                 $response = [
                    'status'   => 200,
                    'message'    => 'Item Found Succesfully',
                    'data' => $data
                ];
                return $this->respond($response);
            }else{
                $client = \Config\Services::curlrequest();
                $url ='https://thefutureindia.org/eBilling/v2/Api.php?apicall=get_details';
                $body = ['barcode'=> $this->request->getVar('item_code'), 'phone_number'=>'8011482688'];
                $resp=$client->request('post', $url,  ['form_params' => $body]);
                
                $data=[
                        'user_id' =>  $decoded->data->user_ID,
                        'item_code' =>  $this->request->getVar('item_code')
                    ];
                $body=(json_decode(utf8_encode($resp->getBody())));
                if($resp->getStatusCode()==200 && $body->return_code == 0){
                    $data['item_desc']=$body->description;
                    $url='/home/eude40y381t1/public_html/uploads/item/product/'.$this->request->getVar('item_code').'.jpg';
                    file_put_contents($url, json_decode(file_get_contents($body->image)));
                    $data['item_uom']=$body->uom;
                     if($itemModel->save($data)){
                        $response = [
                            'status'   => 200,
                            'message'    => 'Item Added Succesfully',
                            'data' => $data
                        ];
                        return $this->respond($response);
                    }
                    $response = [
                        'status'   => 500,
                        'message'    => 'Something went wrong',
                        'data' => null
                    ];
                    return $this->respond($response);
                } 
                $response = [
                    'status'   => 404,
                    'message'    => 'Item Not Found',
                    'data' => null
                ];
                return $this->respond($response);
            }
        }
	}
}
