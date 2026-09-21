<?php

namespace App\Models;

use App\Core\Model;

class Pillar extends Model
{
    protected static string $table = 'pillars';

    public static function findBySlug(string $slug): ?array
    {
        return static::first('slug', $slug);
    }
}
