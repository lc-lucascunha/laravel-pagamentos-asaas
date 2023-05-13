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
        'description',
        'bank_slip_url',
        'status',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

}
