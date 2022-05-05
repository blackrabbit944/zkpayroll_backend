<?php
namespace App\Helpers;

/**
 * 
 */
class Rank
{
    static function calculate($create_time,$up_count,$down_count) {
        
        $t = $create_time - 1635151474;

        $x = $up_count - $down_count;

        $z = abs($x);
        if ($x > 0) {
            $y = 1;
        }else if ($x == 0) {
            $y = 0;
            $z = 1;
        }else {
            $y = -1;
        }

        $score = log($z,10) + $y * $t/45000; 
        return $score;
    }

    static function calculateTopRank($up_count,$down_count) {
        return $up_count - 2 * $down_count;
    }

}