<?php

namespace Tests\Unit;

use App\Models\StatusHistory;
use PHPUnit\Framework\TestCase;

class StatusHistoryTest extends TestCase
{
    public function test_allows_level_1_users_to_edit_level_1_statuses(): void
    {
        $this->assertTrue(StatusHistory::canUserEditStatus('change_request', false, true));
        $this->assertTrue(StatusHistory::canUserEditStatus('created', false, true));
    }

    public function test_blocks_level_1_users_from_level_2_statuses(): void
    {
        $this->assertFalse(StatusHistory::canUserEditStatus('approved_by_manager', false, true));
        $this->assertFalse(StatusHistory::canUserEditStatus('sent_to_customer', false, true));
    }

    public function test_allows_level_2_users_for_any_status(): void
    {
        $this->assertTrue(StatusHistory::canUserEditStatus('approved_by_manager', true, false));
        $this->assertTrue(StatusHistory::canUserEditStatus('change_request', true, false));
    }
}
