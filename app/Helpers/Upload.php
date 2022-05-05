<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Intervention\Image\Facades\Image;

use App\Helpers\Arr;

/**
 *  文件上传的基础类
 *  这个基础类用于处理文件目录，文件hash等问题
 */

class Upload  {

    protected $config;
    protected $extension;
    protected $clientMimeType;
    protected $hash = [];
    protected $realPath;

    public function __construct($file)
    {
        $this->size           = $file->getSize();
        $this->extension      = $file->extension(); // 真实文件扩展名
        $this->clientMimeType = $file->getClientMimeType(); 
        $this->realPath       = $file->getRealPath();
        $this->requireData    = [];
        $this->requireHash    = null;

        $this->file = $file;
        $this->image = Image::make($file);

        ///根据exif信息旋转图片
        $exif = $this->image->exif();
        if (isset($exif['Orientation']) && $exif['Orientation']) {
            switch ($exif['Orientation']) {
                case 3:
                    $this->image->rotate(180);
                    break;
                case 6:
                    $this->image->rotate(-90); // 逆时针转90度
                    break;
                case 8:
                    $this->image->rotate(90); // 顺时针转90度
                    break;
            }
        }
    }

    public function getImage() {
        // if ($this->image) {
            return $this->image;
        // }
        // $this->image = Image::make($this->file);
        // return $this->image;
    }
 
    public function setRequireData($data) {
        $this->requireData = $data;
        $this->requireHash = null;
    }

    public function getExtension() {
        return $this->extension;
    }

    public function getClientMimeType() {
        return $this->clientMimeType;
    }

    // 获取文件的哈希散列值
    public function hash($type = 'sha1')
    {
        if (!isset($this->hash[$type])) {
            $this->hash[$type] = hash_file($type, $this->realPath);
        }

        return $this->hash[$type];
    }


    public function getRequireHash() {
        if ($this->requireHash) {
            return $this->requireHash;
        }
        ksort($this->requireData);
        $require_hash = hash('sha256',json_encode($this->requireData));
        $this->requireHash = substr(md5($require_hash),8,16);
        return $this->requireHash;
    }

    public function saveResizeImage($dirName,$resize) {

        $path = $this->buildSaveName($dirName, $this->hash(),$this->getRequireHash());
        $img = $this->getImage();

        

        switch($resize['resize_type']) {
            case 'max':
                ///尝试按照最宽来缩放
                $img_width = $img->width();
                $img_height = $img->height();

                // $resize['max_width'] / $img_width = $resized_height / $img_height;
                $resized_height = $img_height * $resize['max_width'] / $img_width;
                if ($resized_height > $resize['max_height']) {  
                    ///如果按照宽度缩放后，高度超过了最大高度,则按照高度来缩放
                    $img->heighten($resize['max_height'],function ($constraint) {
                        $constraint->upsize();
                    });
                }else {
                    ///如果按照宽度缩放后，高度没有超过了最大高度,则按照宽度来缩放
                    $img->widen($resize['max_width'],function ($constraint) {
                        $constraint->upsize();
                    });
                }
                break;

            case 'max_width':
                $img->widen($resize['width'],function ($constraint) {
                    $constraint->upsize();
                });
                break;

            case 'crop':
                $img->fit($resize['width'],$resize['height']);
                break;

            default:
                Log::alert('执行saveResizeImage中代码不能运行到这里');
        }

        Log::debug('保存图片路经:'.$path);

        Storage::disk('public')->put($path, $img->encode($this->extension,90));

        return true;
    }

    // 获取保存文件名
    protected function buildSaveName($dirName, $hash , $requireHash)
    {
        return self::_buildSaveName($dirName, $hash , $requireHash, $this->extension);
    }

    static function _buildSaveName($dirName, $hash , $requireHash , $extension)
    {
        $path = $dirName.DIRECTORY_SEPARATOR;

        if ($dirName == 'image') {
            $path .= $requireHash.DIRECTORY_SEPARATOR;
        }

        $a = substr($hash, 0, 2);
        $b = substr($hash, 2, 2);

        $path .= $a.DIRECTORY_SEPARATOR.$b.DIRECTORY_SEPARATOR;

        $path .= $hash.'.'.$extension;

        return $path;
    }

    static function getUrl($dirName,$hash,$requireHash,$extension) {
        $path = self::_buildSaveName($dirName,$hash,$requireHash,$extension);
        return config('global.static_url'). '/public/'.$path;
    }

    static function getPath($dirName,$hash,$requireHash,$extension) {
        $path = self::_buildSaveName($dirName,$hash,$requireHash,$extension);
        return '/public/'.$path;
    }

}
