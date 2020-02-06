<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\University;
use Illuminate\Routing\Controller as BaseController;

class ImportController extends BaseController
{
    public function index(){

//        $data = json_decode(file_get_contents('world_universities_and_domains.json'), true);
//
//        foreach ($data as $row){
//
//            $u = new University();
//
//            $u->name    = $row['name'];
//            $u->code    = $row['alpha_two_code'];
//            $u->state   = $row['state-province'];
//            $u->country = $row['country'];
//
//            $u->save();
//        }
//
//        $fn = fopen("skills.txt","r");
//
//        while(! feof($fn))  {
//            $result = fgets($fn);
//
//            $skill = new Skill();
//
//            $skill->name = trim($result);
//
//            $skill->save();
//
//
//            //echo $result;
//            //echo "<br>";
//        }
//
//        fclose($fn);

//        $fn = fopen("languages.txt","r");
//
//        while(! feof($fn))  {
//            $result = fgets($fn);
//
//            $exists = Skill::where('name', trim($result))->exists();
//
//            if(!$exists){
//
//                $skill = new Skill();
//
//                $skill->name      = trim($result);
//                $skill->language = 1;
//
//                $skill->save();
//
//            } else {
//
//               $skill =  Skill::where('name', trim($result))->first();
//
//               $skill->language = 1;
//
//               $skill->save();
//            }
//
//            //echo $result;
//            //echo "<br>";
//        }
//
//        fclose($fn);
    }
}
