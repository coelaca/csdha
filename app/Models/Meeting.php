<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meeting extends Model
{
    public function status(): BelongsTo
    {
        return $this->belongsTo(MeetingStatus::class);
    }
}
