<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Skill extends Model
{
    use SoftDeletes;

    protected $table = 'skills';

    public static function getSkills(){

        return self::where('language', 0)->where('stop', 0)->pluck('name')->toArray();
    }

    public static function getLanguages(){

        return self::where('language', 1)->where('stop', 0)->pluck('name')->toArray();
    }
}