<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [

        'customer_id',
        'created_at',
        'updated_at'
    ];

    // Relation between Orders and Users Table
    public function user(){
        return $this->belongsTo(User::class);
    }

    // Relation between Orders and OrderDetails Table
    public function orderDetails(){
        return $this->hasMany(OrderDetail::class);
    }


}
