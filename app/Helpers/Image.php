<?php
namespace App\Helpers;

use App\Repositories\UploadImgRepository;
use Illuminate\Support\Facades\Cache;
use App\Models\UploadImg;
use Illuminate\Support\Facades\Log;


class Image
{

    static function getAvatar($avatar_img_id) {
        // return Cache::rememberForever('avatar_'.$avatar_img_id, function () use($avatar_img_id) {
            $img = UploadImg::find($avatar_img_id);
            if ($img && $img->file_type == 'avatar') {
                return ['url'=>$img->image_urls['url']];
            }else {
                return ['url'=>''];
            }
        // });
    }

    static function getImage($img_id) {
        return Cache::rememberForever('img_'.$img_id, function () use($img_id) {
            $img = UploadImg::find($img_id);
            if ($img) {
                return ['url'=>$img->image_urls['url']];
            }else {
                return ['url'=>''];
            }
        });
    }

    static function getExtension($mime) {
        Log::debug('传入的mime是：'.$mime);
        if ($mime == 'image/jpeg') {
            $extension = 'jpg';
        }
        elseif ($mime == 'image/png') {
            $extension = 'png';
        }
        elseif ($mime == 'image/gif') {
            $extension = 'gif';
        }
        elseif ($mime == 'image/webp') {
            $extension = 'webp';
        }
        else  {
            $extension = '';
        }
        Log::debug('得到的extension是：'.$extension);
        return $extension;
    }
 


}