<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_date',
        'amount',
        'status',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
    ];

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
