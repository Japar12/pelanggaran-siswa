<?php

namespace App\Listeners;

use App\Events\ViolationStatusUpdated;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Mail\ParentViolationNotification;

class SendViolationStatusNotification
{
    public function handle(ViolationStatusUpdated $event): void
    {
        $violation = $event->violation;

         // 🛑 Skip kalau event ini terpanggil waktu pelanggaran baru dibuat
        if ($violation->wasRecentlyCreated) {
            \Log::info("⏸️ Di-skip: ViolationStatusUpdated fired saat create (ID {$violation->id})");
            return;
        }

        // 🛑 Skip kalau status belum berubah (misal disave ulang)
        if (!$violation->wasChanged('status')) {
            \Log::info("⏸️ Di-skip: Status tidak berubah untuk violation ID {$violation->id}");
            return;
        }

        // ✅ Hindari kirim ulang status sama dua kali
        $alreadyLogged = \App\Models\NotificationLog::where('violation_id', $violation->id)
            ->where('status', $violation->status)
            ->exists();

        if ($alreadyLogged) {
            \Log::info("⏸️ Notifikasi status '{$violation->status}' sudah pernah dikirim untuk violation ID {$violation->id}");
            return;
        }

        \Log::info("🎯 Listener SendViolationStatusNotification dijalankan untuk ID {$violation->id}");

        $teacher = $violation->createdBy;
        $student = $violation->student;
        $parent = $student->parentUser ?? null;
        $studentUser = $student->user ?? null;

        // 🧩 1️⃣ Kirim notifikasi ke Guru Pelapor (selalu dikirim)
        if ($teacher) {
            $status = ucfirst($violation->status);
            $color = match ($violation->status) {
                'approved' => 'success',
                'rejected' => 'danger',
                default => 'warning',
            };

            Notification::make()
                ->title("Pelanggaran {$status}")
                ->body("Laporan pelanggaran '{$violation->description}' untuk siswa {$student->name} telah {$status} oleh admin.")
                ->color($color)
                ->icon(match ($violation->status) {
                    'approved' => 'heroicon-o-check-circle',
                    'rejected' => 'heroicon-o-x-circle',
                    default => 'heroicon-o-information-circle',
                })
                ->sendToDatabase($teacher);

            \Log::info("✅ Notifikasi guru dikirim untuk violation ID {$violation->id}");
        } else {
            \Log::warning("⚠️ Tidak ditemukan guru pelapor untuk violation ID {$violation->id}");
        }

        // 🧩 2️⃣ Jika status = approved → kirim juga ke Orang Tua & Siswa
        if ($violation->status === 'approved') {

            // 🟢 (a) Notifikasi ke Orang Tua
            if ($parent) {
                // Web (Filament)
                Notification::make()
                    ->title("Pelanggaran Disetujui untuk {$student->name}")
                    ->body("Anak Anda melakukan pelanggaran: {$violation->description}. Status: Disetujui oleh pihak sekolah.")
                    ->color('warning')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->sendToDatabase($parent);

               // 🟢 WhatsApp - hanya kirim ke ortu saat status approved
                try {
                    $formattedMessage = <<<EOT
                📢 *Notifikasi Pelanggaran Sekolah*

                Halo, orang tua dari *{$student->name}*.
                Anak Anda melakukan pelanggaran berikut:

                📄 Jenis: {$violation->description}
                📅 Tanggal: {$violation->date->format('d M Y')}
                ⚖️ Status: *DISETUJUI OLEH SEKOLAH ✅*

                Mohon perhatian dan bimbingan lebih lanjut dari pihak orang tua 🙏
                Terima kasih atas kerja samanya.

                _Nihayatul Amal Purwasari_
                EOT;

                    Http::post('https://api.fonnte.com/send', [
                        'target' => $parent->phone_number, // pastikan format 628xxxx
                        'message' => $formattedMessage,
                        'token' => env('FONNTE_TOKEN'),
                    ]);

                    \Log::info("✅ WhatsApp terkirim ke ortu {$parent->name} ({$parent->phone_number})");
                } catch (\Exception $e) {
                    \Log::error("❌ Gagal kirim WA ke ortu {$parent->name}: " . $e->getMessage());
                }


                // Email
                try {
                    Mail::to($parent->email)->send(new ParentViolationNotification($violation));
                    \Log::info("✅ Email dikirim ke ortu {$parent->email}");
                } catch (\Exception $e) {
                    \Log::error("❌ Gagal kirim email ke ortu {$parent->email}: " . $e->getMessage());
                }
            } else {
                \Log::warning("⚠️ Tidak ada data orang tua untuk siswa {$student->name}");
            }

            // 🟡 (b) Notifikasi ke Siswa
            if ($studentUser) {
                Notification::make()
                    ->title("Pelanggaran Disetujui")
                    ->body("Pelanggaran Anda telah disetujui oleh admin. Jenis pelanggaran: {$violation->description}.")
                    ->color('warning')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->sendToDatabase($studentUser);

                \Log::info("✅ Notifikasi siswa dikirim untuk {$studentUser->name}");
            } else {
                \Log::warning("⚠️ Tidak ditemukan akun user untuk siswa {$student->name}");
            }
        }
    }
}
