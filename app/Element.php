<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Order;
use App\Libro;

class Element extends Model
{
    protected $fillable = ['id','order_id','libro_id','tipo','quantity','unit_price','total', 'actual_quantity', 'actual_total'];

    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function libro(){
        return $this->belongsTo(Libro::class);
    }
}
