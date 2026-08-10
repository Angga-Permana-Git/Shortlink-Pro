<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    public static function log(
        Model|int|null $actor,
        string $action,
        ?string $resourceType = null,
        int|string|null $resourceId = null,
        array $metadata = [],
    ): AuditLog {
        return AuditLog::create([
            'actor_id' => $actor instanceof Model ? $actor->getKey() : $actor,
            'actor_type' => $actor instanceof Model ? $actor->getMorphClass() : null,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'metadata' => $metadata ?: null,
            'ip_address' => request()->ip() ?? null,
        ]);
    }
}