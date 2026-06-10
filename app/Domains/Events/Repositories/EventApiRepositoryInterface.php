<?php

namespace App\Domains\Events\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface EventApiRepositoryInterface
{
    /**
     * Retrieve events for XML export, filtered by start and end dates.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return Collection
     */
    public function getEventsForExport(Carbon $startDate, Carbon $endDate): Collection;
}
