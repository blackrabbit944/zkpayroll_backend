<?php

namespace App\Helpers;

class  Draft {

    public static function isDraft($json_data) {
        try {
            return json_decode($json_data,true);
        }catch (Exception $e) {
            return false;
        }   
    }

    public static function isDraftEmpty($json_data) {

        $data = self::isDraft($json_data);
        if (!$data) {
            return True;
        } 
        $text = self::getDraftText($data);
        $imgs = self::getDraftPhoto($data);
        $audios = self::getDraftAudio($data);
        if (count($imgs) == 0 && count($audios) == 0 && !$text) {
            return True;
        }else {
            return false;
        }
    }


    private static function generateId($length) {
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyz';
           // $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';  
        $key = '';
        for($i=0;$i<$length;$i++)   
        {   
            $key .= $pattern[mt_rand(0,35)];    //生成php随机数   
        }   

        while(is_numeric($key)) {
            $key = self::generateId($length);
        }
        return $key;   
    }
    


    /*
    *   把数据变成一个格式化的draft.js数据
    *   支持以下的一些内容
    *   
    *   1.图片
    *   [
            'type'  =>  'img',
            'text'  =>  '说明文字',
            'url'   =>  '//cdn.jianda.com...'               
        ]
    *   
    *   2.普通文本。
    *   [
            'text'  =>  '说明文字',
        ]

    *   3.list文本。
    *   [
            'type'  =>  'unordered-list-item',
            'text'  =>  '说明文字',
        ]
    */
    public static function convertFromData($data_arr) {
        $output = [];
        foreach($data_arr as $one) {
            $output[] = self::convertFromDataOne($one);
        }
        return [
            'blocks'    => $output,
            'entityMap' => new stdClass()
        ];
    }

    public static function convertFromDataOne($one) {
        switch (arrv($one,'type')) {
            case 'img':
                return [
                    "key"   =>  self::generateId(5),
                    "text"  =>  arrv($one,'text'),
                    "type"  =>  "atomic",
                    "depth" =>  0,
                    "inlineStyleRanges"  =>  [
                    ],
                    "entityRanges"  =>  [
                    ],
                    "data"  =>  [
                        "src"   =>  arrv($one,'url'),
                        "type"  =>  "image",
                        "text"  =>  ""
                    ]
                ];
                break;
            case 'unordered-list-item':
                return [
                    "key"   =>  self::generateId(5),
                    "text"  =>  arrv($one,'text'),
                    "type"  =>  "unordered-list-item",
                    "depth" =>  0,
                    "inlineStyleRanges"  =>  [
                    ],
                    "entityRanges"  =>  [
                    ],
                    "data"  =>  new stdClass()
                ];
                break;
            case 'header-three':
                return [
                    "key"   =>  self::generateId(5),
                    "text"  =>  arrv($one,'text'),
                    "type"  =>  "header-three",
                    "depth" =>  0,
                    "inlineStyleRanges"  =>  [
                    ],
                    "entityRanges"  =>  [
                    ],
                    "data"  =>  new stdClass()
                ];
                break;
            case 'unstyled':
            default:
                return [
                    "key"   =>  self::generateId(5),
                    "text"  =>  arrv($one,'text'),
                    "type"  =>  "unstyled",
                    "depth" =>  0,
                    "inlineStyleRanges"  =>  [
                    ],
                    "entityRanges"  =>  [
                    ],
                    "data"  =>  new stdClass()
                ];
                break;

        }

    }
    
    
    /** 
     * 把一个我们自己定义的类 Markdown 同时带有 UBB 语法的内容转换成 draft 内容
     */
    public function convertFromMarkdownUBB($md) {
        $output = [];
        
        $lines = explode("\n", $md);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            $matches = NULL;
            
            if ($line == '') {
                /// 空行
                $output[] = [
                    'type' => 'unstyled',
                    'text' => '',
                ];
            }
            else if (substr($line, 0, 3) == '###') {
                /// h3
                $output[] = [
                    'type' => 'header-three',
                    'text' => trim(substr($line, 3)),
                ];
            }
            else if (preg_match('/\[img\](\((.+)\))*(.+)\[\/img\]/iu', $line, $matches)) {
                /// 图片
                /// [img](可选的图片说明）http://www.domain.com/image[/img]
                $output[] = [
                    'type' => 'img',
                    'text' => $matches[2],
                    'url' => $matches[3],
                ];
            }
            else if (substr($line, 0, 1) == '*') {
                /// 无序列表
                $output[] = [
                    'type' => 'unordered-list-item',
                    'text' => trim(substr($line, 1)),
                ];
            }
            else {
                /// 普通段落
                $output[] = [
                    'type' => 'unstyled',
                    'text' => $line,
                ];
            }
        }
        $content = $this->convertFromData($output);
                
        return $content;
    }



    public static function getDraftPhoto($json_data) {
        try {
            $photoList = array();
            foreach($json_data['entityMap'] as $p){  
                if ($p['type'] == 'image') {
                    $photoList[] = $p['data']['src'];
                }
            }
            foreach($json_data['blocks'] as $b){  
                if (arrv($b,'type') == 'atomic' && arrv($b,'data.type') == 'image' && arrv($b,'data.src')) {
                    $photoList[] = $b['data']['src'];
                }
            }
            return $photoList;
        }catch (Exception $e)  {
            return [];
        }
    }


    public static function filterUrl($text) {
        $s1 = stripos($text,'//');
        $s2 = stripos($text,'.mp3');
        if ($s1 !== false && $s2 !== false) {
            if ($s1 == 0) {
                $summary = substr($text,$s2 + 4);
            }else {
                $a1 = substr($text,0,$s1);
                $a2 = substr($text,$s2 + 4);
                $summary = $a1 . $a2;
            }
            return self::filterUrl($summary);
        }else {
            return $text;
        }
    }
    public static function getDraftAudio($json_data) {
        try {
            $photoList = array();
            if (isset($json_data['entityMap'])) {
                foreach($json_data['entityMap'] as $p){  
                    if ($p['type'] == 'audio' || $p['type'] == 'VOICE') {
                        if (isset($p['data']['src'])) {
                            $photoList[] = $p['data']['src'];
                        }else {
                            $photoList[] = $p['data']['url'];
                        }
                    }
                }
            }
            if (isset($json_data['blocks'])) {
                foreach($json_data['blocks'] as $b){  
                    if (arrv($b,'type') == 'atomic' && arrv($b,'data.type') == 'audio' && arrv($b,'data.src')) {
                        $photoList[] = $b['data']['src'];
                    }
                }
            }
            return $photoList;
        }catch (Exception $e)  {
            return [];
        }

    }

    public static function getDraftText($json_data) {
        $text = '';
        try {

            if (gettype($json_data) == 'string') {
                $json_data = json_decode($json_data,true);
            }

            if (!$json_data) {
                return '';
            }

            if (is_array($json_data['blocks'])) {
                foreach($json_data['blocks'] as $p){  
                    if (isset($p['text'])) {
                        $text .= self::filterUrl(trim($p['text']));
                    }
                } 
            }
            return $text;
        }catch (Exception $e)  {
            return '';
            // return $text;
        }
    }

    public static function getContentInfo($json_string) {
        $raw = self::isDraft($json_string);
        $default_data = ARRAY(
            'text'  =>   '',
            'photo' =>   array(),
            'audio' =>   array(),
        );
        if (!$raw) {
            return $default_data;
        } 

        $default_data['text'] = self::getDraftText($raw);
        $default_data['photo'] = self::getDraftPhoto($raw);
        $default_data['audio'] = self::getDraftAudio($raw);
        return $default_data;
    }

    public static function getSummaryData($json_data) {
        $summary = [
            'char_count'  =>  '',
            'audio_count' =>  0,
            'img_count'   =>  0
        ];
        $data = self::isDraft($json_data);
       
        if (!$data) {
            return $summary;
        } 
        $imgs = self::getDraftPhoto($data);
        $audios = self::getDraftAudio($data);


        $summary['char_count'] = mb_strlen(self::getDraftText($data),'utf8');
        $summary['img_count'] = count($imgs);
        $summary['audio_count'] = count($audios);

        return $summary;
    }
    

    public static function getSummary($json_data) {
        $data = self::isDraft($json_data);
        $summary = [
            'text'  =>  '',
            'cover' =>  '',
            'audio_count' => 0,
            'img_count'   => 0
        ];
        if (!$data) {
            return $summary;
        } 
        $summary['text'] = self::getDraftText($data);
        $imgs = self::getDraftPhoto($data);
        $audios = self::getDraftAudio($data);

        $summary['img_count'] = count($imgs);
        $summary['audio_count'] = count($audios);

        if ($summary['img_count'] > 0) {
            $summary['cover'] = $imgs[0];
        }
        return $summary;
    }

 }
