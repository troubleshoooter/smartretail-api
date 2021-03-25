<?php

namespace App\Controllers;


use App\Models\InventoryModel;
use App\Models\ItemModel;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Exception;

class Reports extends BaseController
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
	
	public function getItemReport(){
	    $key = $this->getKey();
		$token = $this->request->getHeaderLine("Authorization");
		$decoded = JWT::decode($token, $key, array("HS256"));
		if ($decoded) {
		    $db = db_connect();
		    $sql;
		    if($this->request->getVar('type')=='item'){
    		    $sql='Select item_master.item_code, SUM(transaction_master.trs_selling) AS amt, SUM(transaction_master.trs_quantity) AS qty, item_master.item_desc, item_master.item_uom 
                        FROM inventory_master 
                        JOIN item_master 
                        ON item_master.item_code=inventory_master.item_code
                        JOIN transaction_master 
                        ON item_master.item_code=transaction_master.item_code
                        WHERE transaction_master.user_id = ?
                        AND transaction_master.trs_date
                        BETWEEN ? AND ?
                        GROUP BY item_code
                        ORDER By qty DESC
                        LIMIT 5';
		    }else{
		        $sql='Select item_master.item_code, SUM(transaction_master.trs_selling) AS amt, SUM(transaction_master.trs_quantity) AS qty, item_master.item_desc, item_master.item_uom 
                        FROM inventory_master 
                        JOIN item_master 
                        ON item_master.item_code=inventory_master.item_code
                        JOIN transaction_master 
                        ON item_master.item_code=transaction_master.item_code
                        WHERE transaction_master.user_id = ?
                        AND transaction_master.trs_date
                        BETWEEN ? AND ?
                        GROUP BY item_code
                        ORDER By amt DESC
                        LIMIT 5';
		    }
            $res=$db->query($sql, [
                $decoded->data->user_ID,
                $this->request->getVar('from'),
                $this->request->getVar('to')
                ]);
			$response = [
				'status'   => 200,
				'message' =>  'Data found Successfully',
				'data' => $res->getResultArray()
			];
			return $this->respond($response, 200, "Data Found");
		} else {
			$response = [
			    'status' => 401,
				'data' => null,
				'message' => 'Access denied'
			];
			return $this->respond($response);
		}
	}
	
    public function getCustomers(){
	    $key = $this->getKey();
		$token = $this->request->getHeaderLine("Authorization");
		$decoded = JWT::decode($token, $key, array("HS256"));
		if ($decoded) {
		    $db = db_connect();
		    $sql='Select order_customer_name, order_customer_phone, SUM(order_paid_amt) FROM order_master Where trs_id 
                    IN (
                        SELECT id FROM transaction_master 
                        Where transaction_master.user_ID=?
                        AND transaction_master.trs_date 
                        BETWEEN ?
                        AND ?
                    )';
            $res=$db->query($sql, [
                $decoded->data->user_ID,
                $this->request->getVar('from'),
                $this->request->getVar('to')
                ]);
            $data = array();
            foreach($res->getResultArray() as $usr){
                $arr =[
                    'customer_name' => $usr['order_customer_name'],
                    'customer_phone' => $usr['order_customer_phone'],
                    'amount' => $usr['SUM(order_paid_amt)']
                    ];
                array_push($data, $arr);
            }
			$response = [
				'status'   => 200,
				'message' =>  'User found Successfully',
				'data' => $data
			];
			return $this->respond($response, 200, "Data Found");
		} else {
			$response = [
			    'status' => 401,
				'data' => null,
				'message' => 'Access denied'
			];
			return $this->respond($response);
		}
	}
}
