<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeetingStatus extends Model
{
    public function files(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }
}
