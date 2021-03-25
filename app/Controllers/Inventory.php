<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\InventoryModel;
use App\Models\ItemModel;
use Firebase\JWT\JWT;
use Exception;
class Inventory extends ResourceController
{
    use ResponseTrait;

    private function getKey()
    {
        return "qsBIoaE1ReVa6cM8LahNTrN838EpXf7v";
    }
    
    // all users
    public function index()
    {
        $key = $this->getKey();
        $token = $this->request->getHeaderLine("Authorization");

     
        $decoded = JWT::decode($token, $key, array("HS256"));

        if ($decoded) {

            $model = new InventoryModel();
            $data = $model->where('user_id', $decoded->data->user_ID)->join('item_master', 'item_master.item_code = inventory_master.item_code')->findAll();
            $response = [
                'status'   => 200,
                'message' =>  'Inventory Found successfully',
                'data' => $data
            ];
            return $this->respond($response, 200, "Data Found");
        }
        
    }

   // create
    public function create()
    {
        $key = $this->getKey();
        $token = $this->request->getHeaderLine("Authorization");

     
        $decoded = JWT::decode($token, $key, array("HS256"));

        if ($decoded) {
            $itemModel= new ItemModel();
            $model = new InventoryModel();
            $result=$itemModel->where('item_code', $this->request->getVar('item_code'))->first();
            $data=[
                'item_code' => $this->request->getVar('item_code'),
                'amt' => $this->request->getVar('amt'),
                'qty' => $this->request->getVar('qty'),
                'discount' => $this->request->getVar('discount'),
                'price' => $this->request->getVar('price'),
                'item_desc' => $this->request->getVar('item_desc'),
                'item_uom' => $this->request->getVar('item_uom'),
                'user_id' => $decoded->data->user_ID
                ];
            if(!$result){
                if(!$itemModel->save($data)){
                    $response = [
                        'status'   => 500,
                        'message'    => 'Something went wrong',
                        'data' => null
                    ];
                }    
            }
            if($model->save($data)){
                $response = [
                    'status'   => 200,
                    'message'    => 'Item Added Successfully',
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
    }
  
}
