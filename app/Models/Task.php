<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'assigned_to',
        'ai_summary',
        'ai_priority',
    ];

    protected $casts = [
        'due_date' => 'date:Y-m-d',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Scope a query to filter tasks.
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%');
            });
        })->when($filters['status'] ?? null, function ($query, $status) {
            if ($status !== 'all' && $status !== '') {
                $query->where('status', $status);
            }
        })->when($filters['priority'] ?? null, function ($query, $priority) {
            if ($priority !== 'all' && $priority !== '') {
                $query->where('priority', $priority);
            }
        })->when($filters['assigned_to'] ?? null, function ($query, $assignedTo) {
            if ($assignedTo !== 'all' && $assignedTo !== '') {
                $query->where('assigned_to', $assignedTo);
            }
        });
    }
}
