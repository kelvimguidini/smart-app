<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusHistory extends Model
{
    protected $table = 'status_history';

    protected $fillable = [
        'status',
        'user_id',
        'observation',
        'table',
        'table_id'
    ];

    protected $dates = ['created_at'];

    const BLOCKED_STATUSES = [
        'approved_by_manager',
        'dating_with_customer',
        'Cancelled',
        'sent_to_customer',
    ];

    public static function queryLatestFor(string $table, $tableId)
    {
        return self::where('table', $table)
            ->where('table_id', $tableId)
            ->latest('created_at');
    }

    public static function latestFor(string $table, $tableId)
    {
        return self::queryLatestFor($table, $tableId)->first();
    }

    public static function isBlockedStatus(?string $status): bool
    {
        return $status !== null && in_array($status, self::BLOCKED_STATUSES, true);
    }

    public static function isBlockedTableRecord(string $table, $tableId): bool
    {
        return self::isBlockedStatus(self::latestFor($table, $tableId)?->status);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function eventHotel()
    {
        return $this->belongsTo(EventHotel::class, 'table_id', 'id')
            ->where('table', 'event_hotels');
    }

    public function eventAB()
    {
        return $this->belongsTo(EventAB::class, 'table_id', 'id')
            ->where('table', 'event_abs');
    }

    public function eventHall()
    {
        return $this->belongsTo(EventHall::class, 'table_id', 'id')
            ->where('table', 'event_halls');
    }

    public function eventAdd()
    {
        return $this->belongsTo(EventAdd::class, 'table_id', 'id')
            ->where('table', 'event_adds');
    }

    public function eventTransport()
    {
        return $this->belongsTo(EventTransport::class, 'table_id', 'id')
            ->where('table', 'event_transports');
    }
}
