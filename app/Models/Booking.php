<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = ['hotel_external_id', 'hotel_name', 'guest_name', 'guest_email', 'check_in', 'check_out', 'guests', 'total_price', 'status', 'notes'];

    protected function casts(): array
    {
        return ['check_in' => 'date', 'check_out' => 'date', 'total_price' => 'decimal:2'];
    }
}
