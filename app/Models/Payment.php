<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'sale_id',
    ];

    //relationships
    public function sale() :BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
