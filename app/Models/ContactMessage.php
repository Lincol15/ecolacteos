<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'is_read',
        'is_answered',
        'answered_by',
        'answer',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'is_answered' => 'boolean',
            'answered_at' => 'datetime',
        ];
    }

    public function answeredBy()
    {
        return $this->belongsTo(User::class, 'answered_by');
    }
}
