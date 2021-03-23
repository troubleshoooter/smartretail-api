<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\SubscriptionModel;
use Firebase\JWT\JWT;
use Exception;
class Subscription extends ResourceController
{
    use ResponseTrait;

    private function getKey()
    {
        return "qsBIoaE1ReVa6cM8LahNTrN838EpXf7v";
    }
    
    // all subscriptions
    public function index()
    {
        $key = $this->getKey();
        $token = $this->request->getHeaderLine("Authorization");

        try {
            $decoded = JWT::decode($token, $key, array("HS256"));

            if ($decoded) {

                $model = new SubscriptionModel();
                $data = $model->orderBy('subscription_id', 'DESC')->findAll();
                $response = [
                    'status'   => 200,
                    'message' =>  'Subscriptions Found successfully',
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

  
}
