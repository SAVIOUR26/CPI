<?php

namespace App\Models;

use App\Core\Model;

/** Marks per student per class. Read them through App\Support\Results so every page agrees. */
class Grade extends Model
{
    protected static string $table = 'grades';
}
