<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
    ];

    public function scopeSearch($query, $q)
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
    }
}
