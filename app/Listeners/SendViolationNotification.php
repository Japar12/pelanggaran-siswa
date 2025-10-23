<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\ViolationCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\WebViolationNotification;
use Filament\Notifications\Notification; // 🔔 Filament 4 notification

class SendViolationNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ViolationCreated $event): void
    {
        $violation = $event->violation;
        \Log::info("🎯 Listener aktif untuk pelanggaran ID {$violation->id}");

        // 1️⃣ Kirim Laravel Notification (ke admin & guru)
        $adminUsers = User::role('admin')->get();
        $guru = $violation->teacher;

        foreach ($adminUsers as $admin) {
            $admin->notify(new WebViolationNotification($violation));
        }

        if ($guru) {
            $guru->notify(new WebViolationNotification($violation));
        }

        // 2️⃣ Kirim Filament Notification (langsung tampil di UI Filament)
        $users = $adminUsers->concat([$guru])->filter(); // gabungkan & hapus null
        foreach ($users as $user) {
            Notification::make()
                ->title('Pelanggaran Baru')
                ->body("Siswa {$violation->student->name} melakukan pelanggaran: {$violation->type}")
                ->success()
                ->sendToDatabase($user); // ⬅️ tersimpan & muncul di lonceng Filament 4
        }

        // 3️⃣ (Opsional) Kirim ke Orang Tua via Laravel Notification
        $parentUser = $violation->student->parentUser ?? null;
        if ($parentUser) {
            $parentUser->notify(new WebViolationNotification($violation));
        }
    }
}
