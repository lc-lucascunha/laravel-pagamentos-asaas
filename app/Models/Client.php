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

    /*public function scopeSearch($query, $q)
    {
        return $query->where('name', 'like', "%{$q}%");
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getNameAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
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
    }*/
}
