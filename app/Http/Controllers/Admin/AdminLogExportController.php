<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminLogExportService;
use Illuminate\Http\Response;
use InvalidArgumentException;

/** Mengunduh jejak admin sebagai CSV (audit, masuk, klik, atau perubahan peran). */
class AdminLogExportController extends Controller
{
    private const JENIS = ['audit', 'login', 'klik', 'peran'];

    public function __invoke(AdminLogExportService $service, string $jenis): Response
    {
        abort_unless(in_array($jenis, self::JENIS, true), 404);

        try {
            $hasil = $service->ekspor($jenis);
        } catch (InvalidArgumentException) {
            abort(404);
        }

        $csv = fopen('php://temp', 'r+');
        foreach ($hasil['baris'] as $baris) {
            fputcsv($csv, $baris, ';');
        }
        rewind($csv);
        $isi = (string) stream_get_contents($csv);
        fclose($csv);

        $nama = $hasil['judul'].'-'.now()->format('Ymd-His').'.csv';

        return response($isi, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$nama\"",
        ]);
    }
}
