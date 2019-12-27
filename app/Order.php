<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    const UNASSIGNED = "UNASSIGNED";
    const TAKEN = "TAKEN";

    protected $fillable = ['origin_lat', 'origin_lng', 'destination_lat', 'destination_lng', 'distance', 'status'];

    protected $casts = [
        'distance' => 'integer',
    ];
}
