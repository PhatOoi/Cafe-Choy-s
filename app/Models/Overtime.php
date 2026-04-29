<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Overtime extends Model
{
    protected $fillable = [
        'staff_id',
        'overtime_date',
        'hours',
        'status',
        'notes',
    ];

    protected $casts = [
        'overtime_date' => 'date',
        'hours' => 'decimal:2',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
