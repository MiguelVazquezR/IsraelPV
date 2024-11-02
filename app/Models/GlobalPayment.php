<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlobalPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'initial_amount',
        'amount',
        'date',
        'notes',
        'client_id',
    ];

    protected $casts = [
        'date' => 'datetime'
    ];

    //relationships
    public function client() :BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
