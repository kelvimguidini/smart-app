<?php

namespace App\Domains\Events\Services;

interface EventApiServiceInterface
{
    /**
     * Generate XML payload for events within the specified date range.
     *
     * @param string $startDate
     * @param string $endDate
     * @return string The generated XML string
     */
    public function generateXmlPayload(string $startDate, string $endDate): string;
}
