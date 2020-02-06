<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employer extends Model
{
    use SoftDeletes;

    protected $table = 'employers';

    public static function getEmployers(){

        return self::orderByRaw('CHAR_LENGTH(name) DESC')->pluck('name')->toArray();
    }
}