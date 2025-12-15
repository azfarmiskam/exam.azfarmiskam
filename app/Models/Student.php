<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'classroom_id',
        'group_id',
        'name',
        'email',
        'phone',
        'matric_number',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ClassroomGroup::class, 'group_id');
    }

    public function examSessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }
}
