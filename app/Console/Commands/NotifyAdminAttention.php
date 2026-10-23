<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Admin\AdminAttentionService;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class NotifyAdminAttention extends Command
{
    protected $signature = 'admin:notify-attention';

    protected $description = 'Kirim notifikasi dalam aplikasi kepada admin untuk masalah operasional.';

    public function handle(AdminAttentionService $attention): int
    {
        $admin = User::where('role', 'admin')->where('is_active', true)->get();

        if ($admin->isEmpty()) {
            return self::SUCCESS;
        }

        foreach ($attention->peringatan() as $item) {
            $kunci = 'admin-peringatan:'.md5($item['judul']);

            if (Cache::has($kunci)) {
                continue;
            }

            Notification::make()
                ->warning()
                ->title($item['judul'])
                ->body($item['pesan'])
                ->sendToDatabase($admin);

            Cache::put($kunci, true, now()->addHours(6));
        }

        return self::SUCCESS;
    }
}
