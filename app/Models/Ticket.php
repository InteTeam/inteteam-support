<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory, HasTenantScope, HasUlids;

    protected $fillable = [
        'tenant_id',
        'end_customer_id',
        'assigned_to',
        'app',
        'page',
        'category',
        'description',
        'status',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'end_customer_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(TicketNote::class);
    }

    public function publicNotes(): HasMany
    {
        return $this->hasMany(TicketNote::class)->where('is_internal', false);
    }

    public static function validTransitions(): array
    {
        return [
            'open'        => ['in_progress', 'closed'],
            'in_progress' => ['resolved'],
            'resolved'    => ['closed'],
            'closed'      => [],
        ];
    }

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, static::validTransitions()[$this->status] ?? [], true);
    }
}
