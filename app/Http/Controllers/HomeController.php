<?php

namespace App\Http\Controllers;

use App\Models\Degree;
use App\Models\Employer;
use App\Models\Name;
use App\Models\Nationality;
use App\Models\Position;
use App\Models\Skill;
use App\Models\University;
use App\Models\User;
use Carbon\Carbon;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Spatie\PdfToText\Pdf;
use Web64\LaravelNlp\Facades\NLP;
use NlpTools\Tokenizers\WhitespaceAndPunctuationTokenizer;
use NlpTools\Tokenizers\WhitespaceTokenizer;
use Softon\LaravelFaceDetect\Facades\FaceDetect;
use NlpTools\Stemmers\PorterStemmer;
use NlpTools\Documents\TokensDocument;

class HomeController extends BaseController
{
    const MIN_NAME_LENGTH = 6;

    public function index(){

        return view('home.index');
    }

    public function profile(){

        return view('home.profile');
    }

    public function upload(Request $request){

        $this->emptyDirs();

        if($request->has('file')){

            $file = $request->file('file');
            $ext  = $file->getClientOriginalExtension();

            $hash = md5(time());
            $file->storeAs('public/cv', $hash.'.'.$ext);

            if(in_array(strtolower($ext), ['doc', 'docx', 'rtf'])) {
                $doc = storage_path() . "/app/public/cv/" . $hash . "." . $ext;
                $cmd = env('PATH_UNOCONV') . ' -f pdf ' . $doc;
                exec($cmd, $output, $return);
            }

            $pdf  = storage_path() . "/app/public/cv/" . $hash . ".pdf";

            $user = $this->getData($pdf, $hash);

            //dd($user);

            return redirect()->route('profile')
                ->with('user', $user);
        }

        return redirect()->route('profile');
    }

    public function getData($pdf, $hash){

        $text = (new Pdf(env('PATH_PDFTOTEXT')))
            ->setPdf($pdf)->text();

        $textLayout = (new Pdf(env('PATH_PDFTOTEXT')))
            ->setOptions(['layout', 'r 96'])
            ->setPdf($pdf)->text();

        //dd($text);

        /* ******** */
        $user = new User();

        $user->fullname    = $this->getName($text);
        $user->email       = $this->getEmail($text);
        $user->phone       = $this->getPhone($text);
        $user->nationality = $this->getNationality($text);
        $user->birthday    = $this->getBirthday($text);
        $user->gender      = $this->getGender($text);
        $user->linkedin    = $this->getLinkedInProfile($text);
        $user->github      = $this->getGithubProfile($text);
        $user->skills      = $this->getSkills($text);
        $user->languages   = $this->getLanguages($text);
        $user->image       = $this->getProfilePicture($pdf, $hash);



        //dd($this->getEducationSegment($text));
        //dd($this->parseExperienceSegment($text));

        $user->education   = $this->parseEducationSegment($textLayout);
        $user->experience  = $this->parseExperienceSegment($textLayout);

        //dd($user);

        return $user;
    }

    public function emptyDirs(){

        $dirs = ['cv', 'images', 'tmp'];

        foreach ($dirs as $dir){

            $dir = storage_path() . '/app/public/'.$dir;

            $files = glob($dir . '/*');
            foreach ($files as $file) {
                if (is_file($file))
                    @unlink($file);
            }
        }
    }

    public function getLines($text){

        return array_values(array_filter(explode("\n", $text)));
    }

    public function getTokens($text, $type = 'whitespace'){

        if($type == 'whitespaceAndPunctuation'){

            $tok = new WhitespaceAndPunctuationTokenizer();

        } else {

            $tok = new WhitespaceTokenizer();
        }

        $tokens = [];

        $lines = $this->getLines($text);

        foreach ($lines as $line) {

            $lineTokens = $tok->tokenize($line);

            foreach ($lineTokens as $token){
                $tokens[] = $token;
            }
        }

        return $tokens;
    }

    public function getText($text){

        return implode(" ", $this->getTokens($text));
    }

    public function nGrams($text, $n = 3){

        $tokens = $this->getTokens($text, 'whitespaceAndPunctuation');

        $len   = count($tokens);
        $ngram = [];

        for($i = 0; $i+$n <= $len; $i++){
            $string = "";
            for($j = 0; $j < $n; $j++){
                $string .= " ". $tokens[$j+$i];
            }
            $ngram[$i] = $string;
        }
        return $ngram;

    }

    public function getName($text){

        $userSegment = $this->getUserSegment($text);

        //dd($userSegment);

        $names = Name::getNames();

        $tok = new WhitespaceAndPunctuationTokenizer();

        foreach($userSegment as $line){

            $lineTokens = $tok->tokenize($line);

            foreach ($lineTokens as $token){
                if(strlen($token) > 2) {
                    if (in_array(ucfirst(strtolower($token)), $names)) {
                        if (mb_strlen($line) > self::MIN_NAME_LENGTH) {
                            return $this->normalizeName($line);
                        }
                    }
                }
            }
        }

        foreach ($userSegment as $line){

            $entities = NLP::spacy_entities( $line, 'en' );

            if(!empty($entities)){
                if(isset($entities['PERSON'])){
                    if(mb_strlen($line) > self::MIN_NAME_LENGTH) {
                        return $this->normalizeName($line);
                    }
                }
            }
        }

        return null;
    }

    public function getNationality($text){

        $userSegment = $this->getUserSegment($text);

        //dd($userSegment);

        $nationalities = Nationality::getNationalities();

        $tok = new WhitespaceAndPunctuationTokenizer();

        foreach($userSegment as $line){

            $lineTokens = $tok->tokenize($line);

            foreach ($lineTokens as $token){
                if(strlen($token) > 3) {
                    if (in_array(ucfirst(strtolower($token)), $nationalities)) {
                        return $token;
                    }
                }
            }
        }
        return null;
    }

    public function getBirthday($text){

        $pattern = '/([0-9]{2})\/([0-9]{2})\/([0-9]{4})|([0-9]{2})\.([0-9]{2})\.([0-9]{4})/i';

        $userSegment = $this->getUserSegment($text);

        //dd($userSegment);

        foreach ($userSegment as $line){

            preg_match_all($pattern, $line,$matches);

            if(count($matches) > 0){

                if(isset($matches[0][0])){
                    return $this->normalizeBirthDay($matches[0][0]);
                }
            }
        }

        return null;
    }

    public function getGender($text){

        $tok = new WhitespaceAndPunctuationTokenizer();

        $userSegment = $this->getUserSegment($text);

        foreach($userSegment as $line){

            $lineTokens = $tok->tokenize($line);

            foreach ($lineTokens as $token){
                if(in_array(strtolower($token), ['male', 'female'])){
                    return ucfirst($token);
                }
            }
        }

        return null;
    }

    public function getEmail($text){

        $pattern = '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';

        preg_match_all($pattern, $text,$matches);

        if(count($matches) > 0){

            if(isset($matches[0][0])){
                return $matches[0][0];
            }
        }

        return null;
    }

    public function getPhone($text){

        $pattern = "/\d{9,}/i";

        $text = str_replace(array(" ", "-", "(", ")", "/"), array("", "", "", "", ""), $text);

        preg_match_all($pattern, $text,$matches);

        if(count($matches) > 0){
            if(isset($matches[0][0])){
                return $matches[0][0];
            }
        }

        return null;
    }

    public function getProfilePicture($pdf, $hash){

        $tmp = storage_path() . '/app/public/tmp';

        $cmd = env('PATH_PDFIMAGES') . ' -all -f 1 ' . $pdf . ' ' . $tmp . '/prefix';
        exec($cmd);

        $images = array_diff(preg_grep('~\.(jpeg|jpg|png)$~', scandir($tmp)), array('.', '..', '.DS_Store'));
        $images = array_slice($images, 0, 3, true);

        foreach ($images as $image) {

            $imageInfo = getimagesize($tmp . '/' . $image);

            $width  = $imageInfo[0];
            $height = $imageInfo[1];

            if ($height > 50) {

                if ($width > 200) {

                    $img = Image::make($tmp . '/' . $image);
                    $img->resize(200, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $img->save($tmp . '/' . $image);
                }

                $ext = File::extension($tmp . '/' . $image);

                if ($ext == 'png' || $ext == 'jpeg') {
                    $newImage = str_replace('png', 'jpg', $image);
                    $newImage = str_replace('jpeg', 'jpg', $newImage);

                    $img = Image::make($tmp . '/' . $image)->encode('jpg', 75);
                    $img->save($tmp . '/' . $newImage);
                    $image = $newImage;
                }

                $isFace = FaceDetect::extract($tmp . '/' . $image)->face_found;

                if ($isFace) {
                    $imageDir = storage_path() . "/app/public/images/".$hash . ".jpg";
                    FaceDetect::extract($tmp . '/' . $image)->save($imageDir);
                    break;
                }
            }
        }

        return (isset($imageDir))? $hash . ".jpg" : null;
    }

    public function getSkills($text){

        $allSkills = Skill::getSkills();

        $skills = [];

        $text = $this->getText($text);

        foreach ($allSkills as $skill){

            if(HelperController::isWordInText($skill, $text)){

                $skills[] = $skill;
            }
        }

        return $skills;
    }

    public function getLanguages($text){

        $allLanguages = Skill::getLanguages();

        $languages = [];

        $text = $this->getText($text);

        foreach ($allLanguages as $language){

            if(HelperController::isWordInText($language, $text)){

                $languages[] = $language;
            }
        }

        return $languages;
    }

    public function getLinkedInProfile($text){

        $needle = "linkedin.com";

        $tokens = $this->getTokens($text);

        foreach($tokens as $token){

            $pos = strpos(strtolower($token), $needle);

            if ($pos > - 1) {
                return $token;
            }
        }

        return "";
    }

    public function getGithubProfile($text){

        $needle = "github.com";

        $tokens = $this->getTokens($text);

        foreach($tokens as $token){

            $pos = strpos(strtolower($token), $needle);

            if ($pos > - 1) {
                return $token;
            }
        }

        return "";
    }


    /* SEGMENTS */

    public function getEducationSegmentKeywords(){

        return config('segments.education');
    }

    public function getDegreeSegmentKeywords(){

        return config('segments.degree');
    }

    public function getExperienceSegmentKeywords(){

        return config('segments.experience');
    }

    public function getSkillSegmentKeywords(){

        return config('segments.skill');
    }

    public function getProjectSegmentKeywords(){

        return config('segments.project');
    }

    public function getAccomplishmentSegmentKeywords(){

        return config('segments.accomplishment');
    }

    public function searchKeywordsInText($keywords, $text){

        foreach ($keywords as $keyword){
            if(HelperController::isWordInText($keyword, $text)){
                return true;
            }
        }
        return false;
    }

    public function getUserSegment($text){

        $segment = [];

        $lines = $this->getLines($text);

        $educationKeywords      = $this->getEducationSegmentKeywords();
        $degreeKeywords         = $this->getDegreeSegmentKeywords();
        $projectKeywords        = $this->getProjectSegmentKeywords();
        $skillKeywords          = $this->getSkillSegmentKeywords();
        $accomplishmentKeywords = $this->getAccomplishmentSegmentKeywords();
        $experienceKeywords     = $this->getExperienceSegmentKeywords();

        foreach ($lines as $line){

            if(!$this->searchKeywordsInText($educationKeywords, $line) &&
                !$this->searchKeywordsInText($degreeKeywords, $line) &&
                !$this->searchKeywordsInText($projectKeywords, $line) &&
                !$this->searchKeywordsInText($skillKeywords, $line) &&
                !$this->searchKeywordsInText($accomplishmentKeywords, $line) &&
                !$this->searchKeywordsInText($experienceKeywords, $line)
              ){
                $segment[] = $line;
            } else {
                break;
            }
        }

        return $segment;
    }

    public function getEducationSegment($text){

        $segment = [];

        $lines = $this->getLines($text);

        $educationKeywords      = $this->getEducationSegmentKeywords();
        $projectKeywords        = $this->getProjectSegmentKeywords();
        $skillKeywords          = $this->getSkillSegmentKeywords();
        $accomplishmentKeywords = $this->getAccomplishmentSegmentKeywords();
        $experienceKeywords     = $this->getExperienceSegmentKeywords();

        $i = 0;
        foreach ($lines as $line){

            $i++;
            $flag = false;

            if($this->searchKeywordsInText($educationKeywords, $line)){

                $segment[] = $line;
                //$i++;
                $flag = true;

                while ($i < count($lines)){

                    $row = $lines[$i];

                    if(!$this->searchKeywordsInText($projectKeywords, $row) &&
                        !$this->searchKeywordsInText($skillKeywords, $row) &&
                        !$this->searchKeywordsInText($accomplishmentKeywords, $row) &&
                        !$this->searchKeywordsInText($experienceKeywords, $row)
                    ){
                        $segment[] = $row;
                    } else {
                        break;
                    }
                    $i++;
                }
            }

            if($flag) {
                break;
            }
        }
        return $segment;
    }

    public function getExperienceSegment($text){

        $segment = [];

        $lines = $this->getLines($text);

        //dd($lines);

        $educationKeywords      = $this->getEducationSegmentKeywords();
        $degreeKeywords         = $this->getDegreeSegmentKeywords();
        $projectKeywords        = $this->getProjectSegmentKeywords();
        $skillKeywords          = $this->getSkillSegmentKeywords();
        $accomplishmentKeywords = $this->getAccomplishmentSegmentKeywords();
        $experienceKeywords     = $this->getExperienceSegmentKeywords();

        $i = 0;
        foreach ($lines as $line){

            $i++;
            $flag = false;

            if($this->searchKeywordsInText($experienceKeywords, $line)){

                $segment[] = $line;
                //$i++;
                $flag = true;

                while ($i < count($lines)){

                    $row = $lines[$i];

                    if(!$this->searchKeywordsInText($projectKeywords, $row) &&
                        !$this->searchKeywordsInText($skillKeywords, $row) &&
                        !$this->searchKeywordsInText($accomplishmentKeywords, $row) &&
                        !$this->searchKeywordsInText($educationKeywords, $row) &&
                        !$this->searchKeywordsInText($degreeKeywords, $row)
                    ){
                        $segment[] = $row;
//                        echo $row;
//                        echo "<br>";
                    } else {
                        break;
                    }
                    $i++;
                }
            }

            if($flag) {
                break;
            }
        }
        return $segment;
    }

    public function parseEducationSegment($text){

        $datesFound   = [];
        $degreesFound = [];
        $schoolsFound = [];

        $education = [];

        $educationSegment = $this->getEducationSegment($text);

        //dd($educationSegment);


        $pattern      = $this->dateRegex();
        $degrees      = Degree::getDegrees();
        $degreesAssoc = Degree::getDegreesАssoc();
        $universities = University::getUniversities();

        $datesSegments = [];
        $i = 0;

        foreach ($educationSegment as $line){

            $datesSegments[$i][] = $line;

            preg_match_all($pattern, $line,$matches);

            if(count($matches) > 0){

                if(isset($matches[0][0])){
                    $datesFound[] = $matches[0][0];

                    $i++;
                    $datesSegments[$i][] = $line;

                    array_pop($datesSegments[$i-1]);
                }
            }

        }

        array_shift($datesSegments);

        for($i = 0; $i < count($datesSegments); $i++){

            $flag = false;

            for($j = 0; $j < count($datesSegments[$i]); $j++){

                foreach ($degrees as $degree) {

                    if(strpos(ucwords($datesSegments[$i][$j]), $degree) > - 1){
                        $degreesFound[] = $degree;
                        $flag = true;
                        break;
                    }
                }

                if($flag) break;
            }

            if(!$flag) {
                $degreesFound[] = '';
            }
        }

        for($i = 0; $i < count($datesSegments); $i++){

            $flag = false;

            for($j = 0; $j < count($datesSegments[$i]); $j++){

                foreach ($universities as $university) {

                    if(strpos($datesSegments[$i][$j], $university) > - 1){
                        $schoolsFound[] = $university;
                        $flag = true;
                        break;
                    }
                }

                if($flag) {
                    break;
                } else {

                    $entities = NLP::entitiy_types($datesSegments[$i][$j]);

                    if(!empty($entities)){
                        if(isset($entities['Organizations'])){
                            $schoolsFound[] = $entities['Organizations'][0];
                            $flag = true;
                            break;
                        }
                    }
                };
            }

            if(!$flag) {
                $schoolsFound[] = '';
            }
        }

        //exit;
        //dd($datesSegments);
        //dd($datesFound);
        //dd($degreesFound);
        //var_dump($datesFound);
        //var_dump($degreesFound);
        //exit;

        $i = 0;
        foreach ($datesFound as $date){

            $education[$i]['date']        = $date;
            $education[$i]['degree']      = (isset($degreesFound[$i]) && isset($degreesAssoc[$degreesFound[$i]])) ? $degreesAssoc[$degreesFound[$i]] : '';
            $education[$i]['university']  = isset($schoolsFound[$i])? $schoolsFound[$i] : '';;

            $i++;
        }

        return $education;
    }

    public function parseExperienceSegment($text){

        $datesFound     = [];
        $positionsFound = [];
        $employersFound = [];


        $positions = Position::getPositions();
        $employers = Employer::getEmployers();
        //dd($employers);

        $experience = [];

        $experienceSegment = $this->getExperienceSegment($text);
        //dd($experienceSegment);


        $pattern = $this->dateRegex();

        $datesSegments = [];
        $i = 0;


        foreach ($experienceSegment as $line){

            $datesSegments[$i][] = $line;

            preg_match_all($pattern, $line,$matches);

            if(count($matches) > 0){

                if(isset($matches[0][0])){
                    $datesFound[] = $matches[0][0];

                    $i++;
                    $datesSegments[$i][] = $line;

                    array_pop($datesSegments[$i-1]);
                }
            }
        }

        array_shift($datesSegments);

        for($i = 0; $i < count($datesSegments); $i++){

            $flag = false;

            for($j = 0; $j < count($datesSegments[$i]); $j++){

                foreach ($positions as $position) {

                    if(strpos(ucwords($datesSegments[$i][$j]), $position) > -1){
                        $positionsFound[] = $position;
                        $flag = true;
                        break;
                    }
                }

                if($flag) {
                    break;
                }
            }

            if(!$flag) {
                $positionsFound[] = '';
            }
        }

        for($i = 0; $i < count($datesSegments); $i++){

            $flag = false;

            for($j = 0; $j < count($datesSegments[$i]); $j++){

                foreach ($employers as $employer) {

                    if(strpos(strtolower($datesSegments[$i][$j]), strtolower(trim($employer))) > -1){
                        $employersFound[] = $employer;
                        $flag = true;
                        break;
                    }
                }

                if($flag) {
                    break;
                } else {

                    $entities = NLP::entitiy_types($datesSegments[$i][$j]);

                    if(!empty($entities)){
                        if(isset($entities['Organizations'])){
                            $employersFound[] = $entities['Organizations'][0];
                        }
                    }
                }
            }

            if(!$flag) {
                $employersFound[] = '';
            }
        }

        //exit;
        //dd($datesSegments);
        //dd($positionsFound);
        //dd($dates);

        $i = 0;
        foreach ($datesFound as $date){

            $experience[$i]['date']     = $date;
            $experience[$i]['position'] = isset($positionsFound[$i])? $positionsFound[$i] : '';
            $experience[$i]['company']  = isset($employersFound[$i])? $employersFound[$i] : '';

            $i++;
        }


        return $experience;
    }

    /* NORMALIZE */

    public function normalizeName($name){

        return ucwords(strtolower($name));
    }

    public function normalizeBirthDay($birthday){

        $birthday = str_replace(['/'], ['.'], $birthday);

        return Carbon::parse($birthday)->format('d.m.Y');
    }

    public function normalizePosition($name){

        return ucwords(strtolower($name));
    }

    public function dateRegex(){

        $patterns = [];

        $patterns[] = '(Jan(?:uary)?|Feb(?:ruary)?|Mar(?:ch)?|Apr(?:il)?|May|Jun(?:e)?|Jul(?:y)?|Aug(?:ust)?|Sep(?:tember)?|Oct(?:ober)?|Nov(?:ember)?|Dec(?:ember)?)\s+(\d{4})[\s–\-]+(Jan(?:uary)?|Feb(?:ruary)?|Mar(?:ch)?|Apr(?:il)?|May|Jun(?:e)?|Jul(?:y)?|Aug(?:ust)?|Sep(?:tember)?|Oct(?:ober)?|Nov(?:ember)?|Dec(?:ember)?)\s+(\d{4})';
        $patterns[] = '(Jan(?:uary)?|Feb(?:ruary)?|Mar(?:ch)?|Apr(?:il)?|May|Jun(?:e)?|Jul(?:y)?|Aug(?:ust)?|Sep(?:tember)?|Oct(?:ober)?|Nov(?:ember)?|Dec(?:ember)?)\s+(\d{4})[\s­]+(Jan(?:uary)?|Feb(?:ruary)?|Mar(?:ch)?|Apr(?:il)?|May|Jun(?:e)?|Jul(?:y)?|Aug(?:ust)?|Sep(?:tember)?|Oct(?:ober)?|Nov(?:ember)?|Dec(?:ember)?)\s+(\d{4})';
        $patterns[] = '(Jan(?:uary)?|Feb(?:ruary)?|Mar(?:ch)?|Apr(?:il)?|May|Jun(?:e)?|Jul(?:y)?|Aug(?:ust)?|Sep(?:tember)?|Oct(?:ober)?|Nov(?:ember)?|Dec(?:ember)?)\s+(\d{4})[\s­]+(till now)';
        $patterns[] = '(Jan(?:uary)?|Feb(?:ruary)?|Mar(?:ch)?|Apr(?:il)?|May|Jun(?:e)?|Jul(?:y)?|Aug(?:ust)?|Sep(?:tember)?|Oct(?:ober)?|Nov(?:ember)?|Dec(?:ember)?)\s+(\d{4})[\s–\-]+(till now)';
        $patterns[] = '(Jan(?:uary)?|Feb(?:ruary)?|Mar(?:ch)?|Apr(?:il)?|May|Jun(?:e)?|Jul(?:y)?|Aug(?:ust)?|Sep(?:tember)?|Oct(?:ober)?|Nov(?:ember)?|Dec(?:ember)?)\s+(\d{4})[\s–\-]+(now)';
        $patterns[] = '(Jan(?:uary)?|Feb(?:ruary)?|Mar(?:ch)?|Apr(?:il)?|May|Jun(?:e)?|Jul(?:y)?|Aug(?:ust)?|Sep(?:tember)?|Oct(?:ober)?|Nov(?:ember)?|Dec(?:ember)?)\s+(\d{4})[\s–\-]+(ongoing)';
        $patterns[] = '([0-9]{2})\/([0-9]{2})\/([0-9]{4})[\s–\-]+([0-9]{2})\/([0-9]{2})\/([0-9]{4})';
        $patterns[] = '([0-9]{2})\.([0-9]{2})\.([0-9]{4})[\s–\-]+([0-9]{2})\.([0-9]{2})\.([0-9]{4})';
        $patterns[] = '([0-9]{2})\/([0-9]{4})[\s–\-]+([0-9]{2})\/([0-9]{4})';
        $patterns[] = '([0-9]{2})\.([0-9]{4})[\s–\-]+([0-9]{2})\.([0-9]{4})';
        $patterns[] = '([0-9]{2})\/([0-9]{4})[\s–\-]+(present)';
        $patterns[] = '([0-9]{2})\.([0-9]{4})[\s–\-]+(present)';
        $patterns[] = '([0-9]{2})\/([0-9]{4})[\s–\-]+(till now)';
        $patterns[] = '([0-9]{2})\.([0-9]{4})[\s–\-]+(till now)';
        $patterns[] = '([0-9]{2})\/([0-9]{4})[\s–\-]+(till today)';
        $patterns[] = '([0-9]{2})\.([0-9]{4})[\s–\-]+(till today)';
        $patterns[] = '([0-9]{4})[\s–\-]+([0-9]{4})';
        $patterns[] = '([0-9]{4})[\s–\-]+(present)';
        $patterns[] = '([0-9]{4})[\s–\-]+(till now)';
        $patterns[] = '([0-9]{4})[\s–\-]+(till today)';
        $patterns[] = '([0-9]{4})[\s–\-]+(still)';
        $patterns[] = '([0-9]{4})[\s–\-]+(ongoing)';

        $patterns[] = '([0-9]{2})\.[\s]([0-9]{4})[\s–\-]+([0-9]{2})\.[\s]([0-9]{4})';
        $patterns[] = '([0-9]{2})\/([0-9]{2})\/([0-9]{4})[ to ]+([0-9]{2})\/([0-9]{2})\/([0-9]{4})';
        $patterns[] = '([0-9]{1})\/([0-9]{2})\/([0-9]{4})[\s]+(to now)';
        //$patterns[] = '([0-9]{4})';

        $pattern = '/'. implode('|', $patterns) .'/i';

        return $pattern;
    }

}
