<?php

namespace App\Models;

use CodeIgniter\Model;

class SubscriptionModel extends Model
{
    protected $table = 'subscription_master';
    protected $primaryKey = 'subscription_id';
    protected $allowedFields = ['subscription_id', 'subscription_title', 'subscription_duration', 'subscription_price'];
}
