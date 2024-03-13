<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'has_credit',
        'total',
        'client_id',
    ];

    //relationships
    public function client() :BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function payments() :HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function products() :BelongsToMany
    {
        return $this->belongsToMany(Product::class)
                    ->withPivot([
                        'quantity',
                        'price',
                    ])->withTimestamps();
    }
}
