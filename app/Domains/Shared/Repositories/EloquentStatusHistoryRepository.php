<?php

namespace App\Domains\Shared\Repositories;

use App\Models\StatusHistory;

class EloquentStatusHistoryRepository implements StatusHistoryRepositoryInterface
{
    public function create(array $data): StatusHistory
    {
        return StatusHistory::create($data);
    }

    public function getByTableAndId(string $table, int $id): \Illuminate\Database\Eloquent\Collection
    {
        return StatusHistory::with('user')->where('table', $table)
            ->where('table_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function isBlockedTableRecord(string $table, int $id): bool
    {
        return StatusHistory::isBlockedTableRecord($table, $id);
    }

    public function isProviderBlockedInEvent(int $eventId, int $providerId, string $type): bool
    {
        return StatusHistory::isProviderBlockedInEvent($eventId, $providerId, $type);
    }
}
