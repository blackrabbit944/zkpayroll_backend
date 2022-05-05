<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

use App\Helpers\Arr;
/**
 * 
 */
class Unsplash  {

    public function _getkey($page,$page_size,$keyword = '') {
        $arr = [
            'kw'            =>  $keyword,
            'page'          =>  $page,
            'page_size'     =>  $page_size,
            'cache_version' =>  config("unsplash.cache_version"),
        ];
        ksort($arr);
        $hash = md5(json_encode($arr));
        return $hash;
    }


    public function getList($page = 1,$page_size = 25) {
        

        $hash = $this->_getkey($page,$page_size);

        $data = Cache::remember($hash,config("unsplash.cache_time"),function() use ($page,$page_size){
            
            Log::Debug('debug,没有命中缓存');

            $response = Http::get('https://api.unsplash.com/photos', [
                'client_id' =>  config("unsplash.access_key"),
                'page'      =>  $page,
                'per_page'  =>  $page_size
            ]);
            $ret = json_decode($response,true);
            $data = [
                'results' =>  $this->formatList($ret)
            ];

            return $data;

        });
        

        return $data;

    }

    public function searchList($kw , $page = 1,$page_size = 25) {

        $hash = $this->_getkey($page,$page_size,$kw);

        $data = Cache::remember($hash,config("unsplash.cache_time"),function() use ($kw,$page,$page_size){

            Log::Debug('debug,没有命中缓存');

            $response = Http::get('https://api.unsplash.com/search/photos', [
                'client_id' =>  config("unsplash.access_key"),
                'query'     =>  $kw,
                'page'      =>  $page,
                'per_page'  =>  $page_size
            ]);
            Log::Debug('发起请求结果是：'.$response);

            $data = json_decode($response,true);
            $data['results'] = $this->formatList($data['results']);

            return $data;

        });

        return $data;
    }

    public function formatList($list) {
        foreach($list as $key => $value) {
            $list[$key] = $this->format($value);
        }
        return $list;
    }

    public function format($one) {

        $keep_keys = [
            'id',
            'urls',
            'width',
            'height',
            'links' =>  [
                'html'
            ],
            'user'  =>  [
                'id',
                'username',
                'links' =>  [
                    'html'
                ],
                'profile_image' =>  [
                    'small'
                ]
            ]
        ];

        return Arr::keepKeys($one,$keep_keys);
    }


}
