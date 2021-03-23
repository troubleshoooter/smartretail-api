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
            $data = $model->where('user_id', $decoded->data->user_ID)->orderBy('item_code', 'DESC')->findAll();
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
            $model = new InventoryModel();
            $data=$model->where('item_code', $this->request->getVar('item_code'))->first();
            if($model->where(['item_code' =>  $this->request->getVar('item_code'), 'user_id' =>  $decoded->data->user_ID])->first()){
                $response = [
                    'status'   => 409,
                    'message'    => 'Item Already Exists',
                    'data' => null
                ];
                return $this->respond($response);
            }else if($data){
                $data['user_id'] = $decoded->data->user_ID;
                if(!$model->save($data)){
                    $response = [
                        'status'   => 500,
                        'message'    => 'Sometyhing went wrong',
                        'data' => $data
                    ];
                    return $this->respond($response);
                 }
                 $response = [
                    'status'   => 200,
                    'message'    => 'Item Added Succesfully',
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
                        'item_code' =>  $this->request->getVar('item_code'),
                        'amt' => $this->request->getVar('amt'),
                        'qty' => $this->request->getVar('qty'),
                        'item_desc' => $this->request->getVar('item_desc')
                        ];
                $itemModel= new ItemModel();
                $body=(json_decode(utf8_encode($resp->getBody())));
                if($resp->getStatusCode()==200 && $body->return_code == 0){
                    $data['item_desc']=$body->description;
                    $data['item_image']=$body->image;
                } 
                
                if($itemModel->save($data)){
                    if($model->save($data)){
                    $response = [
                        'status'   => 200,
                        'message'    => 'Item Added Succesfully',
                        'data' => null
                    ];
                    return $this->respond($response);
                    }
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

    // single user
    public function show($phone = null)
    {
        $model = new InventoryModel();
        $data = $model->where('user_phone', $phone)->first();
        if ($data) {
            $key = $this->getKey();

            $iat = time();
            $nbf = $iat + 10;
            $exp = $iat + 3600;

            $payload = array(
                "iss" => "The_claim",
                "aud" => "The_Aud",
                "iat" => $iat,
                "nbf" => $nbf,
                "exp" => $exp,
                "data" => $data,
            );

            $token = JWT::encode($payload, $key);
            $data['token'] = $token;
            $response = [
                    'status'   => 200,
                    'data'    => $data,
                    'message' =>  'User Found successfully'
                    
                ];
            return $this->respond($response);
        } else {
            return $this->failNotFound('No user found');
        }
    }

    // update
    public function update($id = null)
    {
        $key = $this->getKey();
		$token = $this->request->getHeaderLine("Authorization");

        try {
            $decoded = JWT::decode($token, $key, array("HS256"));

            if ($decoded) {
                $model = new InventoryModel();
                $id = $this->request->getVar('id');
                $data = [
                    'user_name' => $this->request->getVar('user_name'),
                    'user_pin' => md5($this->request->getVar('user_pin')),
                    'user_phone'  => $this->request->getVar('user_phone'),
                    'user_longitude'  => $this->request->getVar('user_longitude'),
                    'user_latitude'  => $this->request->getVar('user_latitude'),
                    'user_store'  => $this->request->getVar('user_store'),
                    'user_address'  => $this->request->getVar('user_address'),
                    'user_image'  => $this->request->getVar('user_image')
        
                ];
                $model->update($id, $data);
                $response = [
                    'status'   => 200,
                    'data'    => null,
                    'message' =>  'User updated successfully'
                    
                ];
                return $this->respond($response);
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

    // delete
    public function delete($id = null)
    {
        $key = $this->getKey();
		$token = $this->request->getHeaderLine("Authorization");

        try {
            $decoded = JWT::decode($token, $key, array("HS256"));

            if ($decoded) {
                $model = new InventoryModel();
                $data = $model->where('id', $id)->delete($id);
                if ($data) {
                    $model->delete($id);
                    $response = [
                        'status'   => 200,
                        'error'    => null,
                        'message' => 'User successfully deleted'
                    ];
                    return $this->respondDeleted($response);
                } else {
                    return $this->failNotFound('No user found');
                }
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
