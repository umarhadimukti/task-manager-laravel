<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * the attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'assigned_to',
        'status',
        'due_date',
        'created_by',
    ];

    /**
     * the attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date',
    ];

    /**
     * check if task is overdue
     *
     * @return bool
     */
    public function isOverdue(): bool
    {
        return $this->due_date->isPast() && $this->status !== 'done';
    }

    /**
     * get the user this task is assigned to
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * get the user who created this task
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
