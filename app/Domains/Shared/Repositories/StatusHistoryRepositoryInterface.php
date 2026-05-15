<?php

namespace App\Domains\Shared\Repositories;

use App\Models\StatusHistory;

interface StatusHistoryRepositoryInterface
{
    public function create(array $data): StatusHistory;
    public function getByTableAndId(string $table, int $id): \Illuminate\Database\Eloquent\Collection;
    public function isBlockedTableRecord(string $table, int $id): bool;
    public function isProviderBlockedInEvent(int $eventId, int $providerId, string $type): bool;
}
