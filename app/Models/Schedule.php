<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'day_of_week',
        'opens_at',
        'closes_at',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Cast times to string (Carbon instances)
    protected $dates = [
        'opens_at',
        'closes_at',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}