<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'token',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

}
