<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

class HelperController extends BaseController
{
    public function index()
    {

    }

    public static function isWordInTextSimple($word, $text) {
        $patt = "/(?:^|[^a-zA-Z])" . preg_quote($word, '/') . "(?:$|[^a-zA-Z])/i";
        return preg_match($patt, $text);
    }

    public static function isWordInText($word, $text){

        if( (strpos($text, ucfirst($word)) > -1) || (strpos($text, strtoupper($word)) > -1) ){
            return true;
        }

        return false;
    }
}