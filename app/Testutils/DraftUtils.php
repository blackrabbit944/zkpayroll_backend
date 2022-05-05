<?php 

namespace App\Testutils;

use \Faker;

class DraftUtils  {

    static function fakeContent($content = null) {
        if (!$content) {
            $faker = Faker\Factory::create();
            $paragraph = $faker->paragraph;
        }else {
            $paragraph = $content;
        }
        return '{"blocks":[{"key":"4c29q","text":"'.$paragraph.'","type":"unstyled","depth":0,"inlineStyleRanges":[],"entityRanges":[],"data":{}}],"entityMap":{}}';
    }
}
?>