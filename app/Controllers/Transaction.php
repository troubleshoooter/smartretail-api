<?php

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\OrderModel;
use App\Models\TransactionModel;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Exception;

class Transaction extends ResourceController
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

   public function getRecentTransaction()
	{
		$key = $this->getKey();
		$token = $this->request->getHeaderLine("Authorization");

		$decoded = JWT::decode($token, $key, array("HS256"));

		if ($decoded) {

			$model = new TransactionModel();
			$model->join('item_master', 'item_master.item_code = transaction_master.item_code', 'left');
			$model->where('transaction_master.item_code=', $this->request->getVar()['itemCode']);
			$model->orderBy('transaction_master.trs_date', 'DESC');
			$data = $model->limit(1)->get()->getResultArray();
			if ($data) {
				$response = [
					'status'   => 200,
					'message' =>  'Transaction Found successfully',
					'data' => $data[0]
				];
				return $this->respond($response, 200, "Data Found");
			} else {
				$response = [
					'status'   => 200,
					'message' =>  'No Records Found',
					'data' => null
				];
				return $this->respond($response, 200, "No Data Found");
			}
		}else{
		    	$response = [
			    'status' => 401,
				'data' => $ex,
				'message' => 'Access denied'
			];
			return $this->respond($response);
		}
	}
	
	
	public function insert()
	{
	    $key = $this->getKey();
		$token = $this->request->getHeaderLine("Authorization");


			$decoded = JWT::decode($token, $key, array("HS256"));
			if ($decoded) {
			    $item = new ItemModel();
			    $transactionModel = new TransactionModel();
			    $transaction = array();
			    $order = new OrderModel();
			    $transactions = $this->request->getVar('transaction');
			    foreach($transactions as $t){
			        $t =  json_decode($t,true);
			        $trans = new TransactionModel();
			        $trans -> transaction_id = $t['transaction_id'];
			        $trans -> item_code = $t['item_code'];
			        $trans -> trs_uom = $t['trs_uom'];
			        $trans -> trs_quantity = $t['trs_quantity'];
			        $trans -> trs_selling = $t['trs_selling'];
			        $trans -> trs_discount = $t['trs_discount'];
			        $trans -> trs_date = $t['trs_date'];
			        $trans -> item_desc = $t['item_desc'];
			        $trans -> user_id = $this->request->getVar('user_id');
			        array_push($transaction, $trans);
			    }
                $data = [
                'user_id'  => $this->request->getVar('user_id'),
                'trs_id' => $transaction[0]->transaction_id,
                'order_cash_discount' => $this->request->getVar('order_cash_discount'),
                'order_net_payable' => $this->request->getVar('order_net_payable'),
                'order_paid_amt' => $this->request->getVar('order_paid_amt'),
                'order_customer_name' => $this->request->getVar('order_customer_name'),
                'order_customer_phone' => $this->request->getVar('order_customer_phone')
                ];
                foreach($transaction as $t){
                     if (!$item->where('item_code',$t->item_code)->first()) {
                         $arr= array();
                         $arr['item_code']= $t->item_code;
                         $arr['item_image']= $t->item_image;
                         $arr['item_desc']= $t->item_desc;
                        if (!$item->insert($arr)) {
                             $response = [
                            'status'   => 500,
                            'message'    => 'Could not save item',
                            'data' => null
                        ];
                        return $this->respond($response);
                        }
                    }
                    if (!$transactionModel->insert($t->first())) {
                        $response = [
                            'status'   => 500,
                            'message'    => 'Could not save transaction',
                            'data' => null
                        ];
                        return $this->respond($response);
                    }
                }
               
                if (!$order->save($data)) {
                    $response = [
                        'status'   => 500,
                        'message'    => 'Could not save order',
                        'data' => null
                    ];
                    return $this->respond($response);
                }
            	$response = [
					'status'   => 200,
					'message' =>  'Transaction Saved Successfully',
					'data' => null
				];
				return $this->respond($response, 200, "Data Found");
			}else{
		    	$response = [
			    'status' => 401,
				'data' => null,
				'message' => 'Access denied'
			];
			return $this->respond($response);
		}
	}
}
