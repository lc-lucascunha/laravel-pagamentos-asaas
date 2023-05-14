<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'client_id',
        'asaas_id',
        'customer',
        'billing_type',
        'due_date',
        'value',
        'installment',
        'installment_token',
        'description',
        'bank_slip_url',
        'status',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function getCreatedAtAttribute($value)
    {
        $value = strtotime($value);
        $date = date("d/m/Y", $value);
        $time = date("H:i:s", $value);
        return "{$date} {$time}";
    }

    public function getUpdatedAtAttribute($value)
    {
        $value = strtotime($value);
        $date = date("d/m/Y", $value);
        $time = date("H:i:s", $value);
        return "{$date} {$time}";
    }

}
