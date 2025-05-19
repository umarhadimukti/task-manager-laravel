<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'logged_at',
    ];

    protected $casts = [ 'logged_at' => 'datetime' ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * create a new activity log.
     *
     * @param string $userId
     * @param string $action
     * @param string $description
     * @return ActivityLog
     */
    public static function log($userId, $action, $description)
    {
        return static::create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'logged_at' => now(),
        ]);
    }
}
