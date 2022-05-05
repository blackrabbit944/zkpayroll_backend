<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Club;
use App\Http\Requests\NftRequest;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use App\Helpers\NftImage;

class NftController extends Controller
{

    private function getResourceImagePath($path) {
        return resource_path() . $path;
    }

    private function getFontByNumber($number) {
        $font_path = [];
        $font_path[] = $this->getResourceImagePath('/font/Abril_Fatface/AbrilFatface-Regular.ttf');
        $font_path[] = $this->getResourceImagePath('/font/Bangers/Bangers-Regular.ttf');
        $font_path[] = $this->getResourceImagePath('/font/Bebas_Neue/BebasNeue-Regular.ttf');
        $font_path[] = $this->getResourceImagePath('/font/Faster_One/FasterOne-Regular.ttf');
        $font_path[] = $this->getResourceImagePath('/font/Leckerli_One/LeckerliOne-Regular.ttf');
        $font_path[] = $this->getResourceImagePath('/font/Lobster/Lobster-Regular.ttf');
        $font_path[] = $this->getResourceImagePath('/font/Luckiest_Guy/LuckiestGuy-Regular.ttf');
        $font_path[] = $this->getResourceImagePath('/font/Oswald/Oswald-VariableFont_wght.ttf');
        $font_path[] = $this->getResourceImagePath('/font/Pacifico/Pacifico-Regular.ttf');
        $font_path[] = $this->getResourceImagePath('/font/Rubik_Moonrocks/RubikMoonrocks-Regular.ttf');
        $font_path[] = $this->getResourceImagePath('/font/Rubik_Wet_Paint/RubikWetPaint-Regular.ttf');
        $font_path[] = $this->getResourceImagePath('/font/Titan_One/TitanOne-Regular.ttf');
        $font_path[] = $this->getResourceImagePath('/font/MajorMonoDisplay-Regular.ttf');

        return $font_path[$number-1];
    }

    public function image(NftRequest $request) {

        $type = $request->input('type');
        $name = ucfirst($request->input('name'));
        $unique_name = trim($request->input('unique_name'));
        $font = $request->input('font');

        if (!$name) {
            $name = 'club name';
        }

        if (!$unique_name) {
            $unique_name = 'urlhash';
        }

        $url = 'https://tinyclub.com/c/'.strtolower($unique_name);

        $ubuntu_bold_path = $this->getResourceImagePath('/font/ubuntu/Ubuntu-Bold.ttf');
        $ubuntu_light_italic_path = $this->getResourceImagePath('/font/ubuntu/Ubuntu-Lightitalic.ttf');
        $ubuntu_light_path = $this->getResourceImagePath('/font/ubuntu/Ubuntu-Light.ttf');
        $monoton_regular_path = $this->getResourceImagePath('/font/monoton/Monoton-Regular.ttf');

        $font_path = $this->getFontByNumber($font);

        $bg = null;
        $cond = [
            'padding'   =>  100,
            'title' =>  [
                'size' => 120,
                'font' => $font_path,
                'color' => '#ffffff',
            ],
            'sub_title' =>  [
                'size'  =>   100,
                'font'  => $ubuntu_light_path,
                'color' => '#ffffff',
            ],
            'url'   =>  [
                'size'  =>  48,
                'font'  =>  $ubuntu_light_path,
                'color' =>  '#ffffff',
            ],
            'bg'    =>  '',
            'bg_color' => '#333333'
        ];

        
        switch($type) {
            case 'bg1':
                $cond['bg'] = $this->getResourceImagePath('/img/nftbg/bg1.png');
                break;
            case 'bg2':
                $cond['bg'] = $this->getResourceImagePath('/img/nftbg/bg2.png');
                break;
            case 'bg3':
                $cond['bg'] = $this->getResourceImagePath('/img/nftbg/bg3.png');
                break;
            case 'matrix';
                $cond['bg_color'] = '#000';
                $cond['title']['color'] = '#09b864';
                $cond['sub_title']['color'] = '#09b864';
                $cond['url']['color'] = '#09b864';
                break;

            case 'orange';
                $cond['bg_color'] = '#fcd34d';
                $cond['title']['color'] = '#333';
                $cond['sub_title']['color'] = '#333';
                $cond['url']['color'] = '#333';

            case 'pink';
                $cond['bg_color'] = '#fcd34d';
                $cond['title']['color'] = '#333';
                $cond['sub_title']['color'] = '#333';
                $cond['url']['color'] = '#333';

            default:
                $cond['bg'] = '';
                break;
        }

        $img = Image::canvas(1024, 1024, $cond['bg_color']);

        ///1.插入背景图
        if ($cond['bg']) {
            $bg_img = Image::make($cond['bg']);
            $bg_img->resize(1024, 1024);
            $img->insert($cond['bg'],'top-left',0,0);
        }

        ///2.插入文字
        $title = NftImage::autoWrap($cond['title']['size'],$cond['title']['font'],$name,1024);

        $img->text($title, $cond['padding'], $cond['padding'], function ($font) use ($cond) {
            $font->file($cond['title']['font']);
            $font->size($cond['title']['size']);
            $font->color($cond['title']['color']);
            $font->valign('top');
        });

        $titleline = substr_count($title,PHP_EOL) + 1;


        $img->text('passcard', $cond['padding'], $cond['padding'] + $titleline * $cond['title']['size'] + 80, function ($font) use ($cond) {

            $font->file($cond['sub_title']['font']);
            $font->size($cond['sub_title']['size']);
            $font->color($cond['sub_title']['color']);
            $font->valign('top');

        });

        $img->text($url, 512, 900, function ($font) use ($cond) {

            $font->file($cond['url']['font']);
            $font->size($cond['url']['size']);
            $font->color($cond['url']['color']);

            $font->align('center');
            $font->valign('top');

        });


        return $img->response();

        // $pixel_image = Image::make($disk->path($image_path));
        // $pixel_image->resize(490, 490);

        // $img->insert($pixel_image,'top-left',55,268);


        // $user_count = User::count();
        // $pixel_log_count = PixelLog::count();
        // $font_path = $this->getResourceImagePath('/font/pixel/ixpx-31p.ttf');

        // ///3.插入文字
        // $img->text($user_count, 320, 834, function($font) use ($font_path) {
        //     $font->file($font_path);
        //     $font->size(58);
        //     $font->color('#FFF');
        //     // $font->align('center');
        //     // $font->valign('top');
        // });

        // $img->text($pixel_log_count, 320, 874, function($font) use ($font_path) {
        //     $font->file($font_path);
        //     $font->size(58);
        //     $font->color('#FFF');
        //     // $font->align('center');
        //     // $font->valign('top');
        // });



    }
}
