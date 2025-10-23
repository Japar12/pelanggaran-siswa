<?php

namespace App\Helpers;

use App\Models\NotificationLog;
use Illuminate\Support\Facades\Log;

class NotificationLogger
{
    /**
     * Simpan log ke database dan Laravel log file.
     */
    public static function log($violation_id, $user_id, $channel, $status = 'success', $message = null)
    {
        try {
            NotificationLog::create([
                'violation_id' => $violation_id,
                'user_id' => $user_id,
                'channel' => $channel,
                'status' => $status,
                'message' => $message,
            ]);

            // Tambahkan juga ke storage/logs/laravel.log
            Log::info("📋 NotifLog [{$channel}] {$status} untuk user {$user_id} (violation {$violation_id})");
        } catch (\Exception $e) {
            Log::error("❌ Gagal mencatat log notifikasi: " . $e->getMessage());
        }
    }
}
