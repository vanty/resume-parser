<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Name extends Model
{
    use SoftDeletes;

    protected $table = 'names';

    public static function getNames(){

        return self::pluck('name')->toArray();
    }
}