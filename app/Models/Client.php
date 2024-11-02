<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rfc',
        'phone',
        'address',
        'debt',
    ];

    //relationships
    public function sales() :HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function globalPayments() :HasMany
    {
        return $this->hasMany(GlobalPayment::class);
    }
}
