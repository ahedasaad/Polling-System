<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class); // Admin user
    }

    public function options()
    {
        return $this->hasMany(PollOption::class);
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at < now();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'EXPIRED');
    }
}
