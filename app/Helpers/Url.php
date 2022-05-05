<?php
namespace App\Helpers;

// use Illuminate\Support\Arr;

use Alaouy\Youtube\Facades\Youtube;
use App\Exceptions\ProgramException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use andreskrey\Readability\Configuration;
use andreskrey\Readability\ParseException;
use andreskrey\Readability\Readability;
/**
 * 
 */
class Url
{

    static function getUrlInfoWithCache($url) {

        $key = 'fetch_url_'.md5($url);

        // Log::info('缓存url的key:'.$key);
        $value = Cache::remember($key, 600, function () use ($url) {
            ///
            Log::info('没有找到缓存，重新获取一次：'.$url);
            return Url::getUrlInfo($url);
        });

        return $value;
    }
    
    static function getUrlInfo($url) {

        $video_id = self::getVideoIdFromYoutubeUrl($url);

        if ($video_id) {
            $video = Youtube::getVideoInfo($video_id);
            if ($video) {
                return [
                    'web_title'     =>  $video->snippet->localized->title,
                    'summary'       =>  Str::limit($video->snippet->localized->description,256),
                    'cover'         =>  $video->snippet->thumbnails->medium->url,
                    'url'           =>  $url,
                    'url_type'      =>  'youtube'
                ];
            }
        }

        return self::getUrlInfoNormal($url);


    }

    static function getUrlInfoNormal($url) {
        
        Log::info('准备请求url获得结果:'.$url);
        try {
            
            $client = new \GuzzleHttp\Client();
            $data = $client->request('get',$url)->getBody()->getContents();

            Log::info('请求url的结果是存在'.$data);

            $readability = new Readability(new Configuration());
            $readability->parse($data);


            $image_list = $readability->getImages();

            $cover = '';
            if ($readability->getImage()) {
                $cover = $readability->getImage();
            }elseif ($image_list && count($image_list) > 0) {
                $cover = $image_list[0];
            }

            if (strpos($cover,'https:https:') == 0) {
                $cover = substr($cover,6);
            }

            Log::info('请求url的结果是:'.json_encode([
                'cover'     =>  $cover,
                'web_title' =>  $readability->getTitle(),
                'summary'   =>  $readability->getExcerpt(),
                'content'   =>  $readability->getContent(),
                'url'       =>  $url,
                'url_type'  =>  '',
            ]));

            return[
                'cover'     =>  $cover,
                'web_title' =>  $readability->getTitle(),
                'summary'   =>  $readability->getExcerpt(),
                'content'   =>  $readability->getContent(),
                'url'       =>  $url,
                'url_type'  =>  '',
            ];

        } catch (ParseException $e) {

            Log::info('解析Url内容时候出错了'.'错误信息:'.$e->getMessage());

            // dump($e->getMessage());
            // echo sprintf('Error processing text: %s', $e->getMessage());

            // throw new ProgramException('抓取网页出错'. $e->getMessage(),500);

        } catch (\GuzzleHttp\RequestException $e) {

            Log::info('报错了:'.$e->messages());
            echo 'fetch fail';
        }

        return false;    
    }

    static function getHostFromUrl($url) {
        $a = explode('//',$url);
        $b = explode('/',$a[1]);
        return $b[0];
    }

    static function isVideo($url) {
        $url_data = parse_url($url);
        if (in_array($url_data['host'], [
            'www.youtube.com',
            'youtube.com',
            'vimeo.com',
            'www.vimeo.com'
        ])) {
            return true;
        }else {
            return false;
        }
    }



    static function getVideoIdFromYoutubeUrl($url) {
        // $regex = '/^((?:https?:)?\/\/)?((?:www|m)\.)?((?:youtube\.com|youtu.be))(\/(?:[\w\-]+\?v=|embed\/|v\/)?)([\w\-]+)(\S+)?$/';
        // preg_match($regex, $url, $matches);

        $url_info = parse_url($url);

        $hosts= [
            'youtube.com',
            'm.youtube.com',
            'www.youtube.com',
            'youtu.be',
            'www.youtu.be',
            'm.youtu.be'
        ];

        // dump($url_info);

        if (isset($url_info['host']) && in_array($url_info['host'],$hosts)) {
            Log::debug('检查到host属于youtube的域名'.$url_info['host']);
            if (isset($url_info['query'])) {
                Log::debug('解析query原始数据是'.$url_info['query']);
                parse_str($url_info['query'],$query_arr); 
                Log::debug('解析query获得数据是'.json_encode($query_arr));
                if (isset($query_arr['v'])) {
                    return $query_arr['v'];
                }elseif (isset($query_arr['video_id'])) {
                    return $query_arr['video_id'];
                }elseif (isset($query_arr['vid'])) {
                    return $query_arr['vid'];
                }
            }else if (isset($url_info['path'])) {
                if (substr($url_info['path'],0,1) == '/') {
                    return substr($url_info['path'],1);
                }else {
                    return false;
                }
            }
        }

    }

}