<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\UserDashboardService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * Mengekspor daftar tautan pengguna ke CSV.
 *
 * Filter (pencarian, status, urutkan) diterima dari query string dan
 * diteruskan ke service yang sama dengan halaman daftar tautan.
 */
class LinkExportController extends Controller
{
    public function __invoke(UserDashboardService $service): Response
    {
        $pengguna = Auth::user();
        $pencarian = (string) request('search', '');
        $filterStatus = (string) request('status', '');
        $urutkan = (string) request('sort', 'terbaru');

        $csv = $service->eksporCsv($pengguna, $pencarian, $filterStatus, $urutkan);

        $nama = 'tautan-'.now()->format('Ymd-His').'.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$nama\"",
        ]);
    }
}
