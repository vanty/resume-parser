<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Degree extends Model
{
    use SoftDeletes;

    protected $table = 'degrees_abbreviations';

    public static function getDegrees(){

        return self::pluck('abbr')->toArray();
    }

    public static function getDegreesАssoc(){

        return self::pluck('name','abbr')->toArray();
    }
}