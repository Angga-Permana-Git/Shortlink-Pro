<?php

namespace App\Support\Admin;

/** Rentang tanggal dashboard admin yang dibawa lewat parameter `periode`. */
final class DashboardPeriod
{
    /** @var array<int, string> */
    public const PILIHAN = [7 => '7 hari', 30 => '30 hari', 90 => '90 hari'];

    public static function hari(): int
    {
        $hari = (int) request()->integer('periode', 30);

        return array_key_exists($hari, self::PILIHAN) ? $hari : 30;
    }

    public static function label(): string
    {
        return self::PILIHAN[self::hari()];
    }
}
