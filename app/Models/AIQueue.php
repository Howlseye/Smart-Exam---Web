<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIQueue extends Model
{
    /** @use HasFactory<\Database\Factories\AIQueueFactory> */
    use HasFactory;

    protected $guarded = [];

    public function logs()
    {
        return $this->hasMany(AIQueueLog::class, 'queue_id');
    }
}
