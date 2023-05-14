<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'asaas_id',

        'cpf_cnpj',
        'name',
        'email',
        'phone',

        'postal_code',
        'address',
        'address_number',
        'complement',
        'province',
    ];

    public function scopefindCpfCnpj($query, $cpf_cpnj)
    {
        return $query->where('cpf_cnpj', '=', $cpf_cpnj);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function cards()
    {
        return $this->hasMany(Card::class);
    }
}
