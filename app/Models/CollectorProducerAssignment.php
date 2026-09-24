<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollectorProducerAssignment extends Model
{
    protected $fillable = [
        'collector_id',
        'producer_id',
    ];

    public function collector()
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function producer()
    {
        return $this->belongsTo(Producer::class);
    }
}
