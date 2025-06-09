<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
        protected $fillable = [
        'order_id',
        'total',
        'status',
        'payment_status',
    ];

    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'order_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    public function address()
    {
        return $this->belongsTo(Address::class);
    }


}
