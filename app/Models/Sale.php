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
        'paid_at',
        'limit_date',
        'total',
        'client_id',
    ];

    protected $casts = [
        'paid_at' => 'date',
        'limit_date' => 'date',
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
