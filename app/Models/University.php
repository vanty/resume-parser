<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class University extends Model
{
    use SoftDeletes;

    protected $table = 'universities';

    public static function getUniversities(){

        return self::pluck('name')->toArray();
    }
}