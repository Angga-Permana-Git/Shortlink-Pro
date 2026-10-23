<?php

namespace App\Filament\Concerns;

use UnitEnum;

/**
 * Menerjemahkan label resource tanpa mengubah properti statis bawaan Filament.
 *
 * Properti statis tidak boleh memanggil __() saat deklarasi, jadi terjemahan
 * dilakukan di method yang memang dipanggil saat panel dirender.
 */
trait BahasaSumberDaya
{
    public static function getModelLabel(): string
    {
        return __(static::$modelLabel ?? parent::getModelLabel());
    }

    public static function getPluralModelLabel(): string
    {
        return __(static::$pluralModelLabel ?? parent::getPluralModelLabel());
    }

    public static function getNavigationLabel(): string
    {
        return __(static::$navigationLabel ?? static::getTitleCasePluralModelLabel());
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        $grup = static::$navigationGroup;

        return is_string($grup) ? __($grup) : $grup;
    }
}
