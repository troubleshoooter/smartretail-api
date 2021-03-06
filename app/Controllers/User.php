<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use Firebase\JWT\JWT;
use Exception;
class User extends ResourceController
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

        try {
            $decoded = JWT::decode($token, $key, array("HS256"));

            if ($decoded) {

                $model = new UserModel();
                $data = $model->orderBy('user_ID', 'DESC')->findAll();
                $response = [
                    'status'   => 200,
                    'message' =>  'Users Found successfully',
                    'data' => $data
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

   // create
    public function create()
    {
        $model = new UserModel();
        if ($model->where('user_phone', $this->request->getVar('user_phone'))->first()) {
            $response = [
                'status'   => 409,
                'message'    => 'User Already Exists',
                'data' => null
            ];
            return $this->respond($response);
        } else {
            $this->request->getFile('user_image')->store(
                '../../../uploads/profile_pic/',
                $this->request->getVar('user_phone') . '.jpg'
            );
            $data = [
                'user_name' => $this->request->getVar('user_name'),
                'user_pin' => $this->request->getVar('user_pin'),
                'user_phone'  => $this->request->getVar('user_phone'),
                'user_longitude'  => $this->request->getVar('user_longitude'),
                'user_latitude'  => $this->request->getVar('user_latitude'),
                'user_store'  => $this->request->getVar('user_store'),
                'user_address'  => $this->request->getVar('user_address'),
                'user_image'  => 'https://smartretail.net.in/uploads/profile_pic/' . $this->request->getVar('user_phone') . '.jpg',
                'subscription_id' => 0,
                'subscription_expiry' => mdate('%Y-%m-%d %H:%i:%s', now())

            ];
            if (!$model->insert($data)) {
                $response = [
                    'status'   => 500,
                    'message'    => 'Something went wrong',
                    'data' => null
                ];
                return $this->respond($response);
            } else {
                $key = $this->getKey();

                $iat = time();
                $nbf = $iat + 10;
                $exp = $iat + 3600;

                $payload = array(
                    "iss" => "The_claim",
                    "aud" => "The_Aud",
                    "iat" => $iat,
                    "nbf" => $nbf,
                    "data" => $data,
                );

                $token = JWT::encode($payload, $key);
                $res = $model->find($model->insertId());
                $res['token'] = $token;
                $response = [
                    'status'   => 201,
                    'message'    => 'User created successfully',
                    'data' => $res
                ];
                return $this->respond($response);
            }
        }
    }

    // single user
    public function show($phone = null)
    {
        $model = new UserModel();
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
                $model = new UserModel();
                $id = $this->request->getVar('id');
                $data = [
                    'user_id' => $this->request->getVar('user_ID'),
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
                $model = new UserModel();
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
