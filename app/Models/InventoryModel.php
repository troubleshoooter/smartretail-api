<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryModel extends Model
{
    protected $table = 'inventory_master';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id', 'item_code', 'amt', 'qty','discount', 'price', 'user_id'];
}
