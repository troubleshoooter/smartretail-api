<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'user_details';
    protected $primaryKey = 'user_ID';
    protected $allowedFields = ['user_name', 'user_phone', 'user_store', 'user_address', 'user_pin', 'user_longitude', 'user_latitude', 'user_image','subscription_id','subscription_expiry'];
}
