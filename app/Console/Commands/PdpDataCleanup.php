<?php

namespace App\Console\Commands;

use App\Models\DataRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Perintah pembersihan data sesuai UU PDP No.27/2022 Pasal 39
 * Data wajib dihapus setelah tidak diperlukan lagi.
 */
class PdpDataCleanup extends Command
{
    protected $signature   = 'pdp:cleanup {--dry-run : Tampilkan yang akan dihapus tanpa benar-benar menghapus}';
    protected $description = 'Bersihkan token download expired dan data usang (UU PDP Pasal 39)';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $this->info('🧹 Mulai pembersihan data UU PDP...');

        // 1. Hapus token download yang sudah expired
        $expiredTokens = DataRequest::where('token_expires_at', '<', now())
            ->whereNotNull('download_token')
            ->count();

        if (!$isDryRun) {
            DataRequest::where('token_expires_at', '<', now())
                ->whereNotNull('download_token')
                ->update(['download_token' => null]);
        }
        $this->line("  Token download expired: {$expiredTokens} (dihapus)");

        // 2. Audit log disimpan permanen dan tidak dihapus oleh cleanup.
        $this->line('  Audit log: disimpan permanen (tidak dihapus)');

        if (!$isDryRun) {
            Log::channel('audit')->info('PDP_CLEANUP: Pembersihan rutin selesai', [
                'expired_tokens' => $expiredTokens,
                'audit_retention'=> 'permanent',
                'executed_at'    => now()->toIso8601String(),
            ]);
        }

        $this->info($isDryRun ? '✅ Dry run selesai (tidak ada yang dihapus).' : '✅ Pembersihan selesai.');
        return Command::SUCCESS;
    }
}
