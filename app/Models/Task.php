<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    public function type(): BelongsTo
    {
        return $this->belongsTo(TaskType::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TaskStatus::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function assignees(): HasMany
    {
        return $this->hasMany(TaskAssignee::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(TaskFile::class);
    }
}
