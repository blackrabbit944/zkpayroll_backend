<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadRequest;
use App\Helpers\Upload;
use App\Helpers\File as FileHelper;

use App\Models\UploadImg;
use Illuminate\Support\Facades\Log;


class UploadController extends Controller
{

    public function img(UploadRequest $request) {

        ///如果提交了width和height则以这个高宽为准
        if ($request->input('width') && $request->input('height')) {

            $resize = [
                'origin',
                'image' =>  [
                    'width'     =>  $request->input('width'),
                    'height'    =>  $request->input('height'),
                    'resize_type'=> 'crop',
                ]
            ];  

            $require_data = [
                'width'     =>  $request->input('width'),
                'height'    =>  $request->input('height'),
                'resize_type'=> 'crop',
            ];

            $template = 'image';

        ///以template为准
        }else if($request->input('template')) {
            $resize = config('image.template.'.$request->input('template').'.resize');

            $require_data = [
                'template'     =>  $request->input('template'),
            ];

            $template = $request->input('template');
        }



        ///获得和整理需要resize的版本，每个版本需要的参数
        $format_resize = [];
        foreach($resize as $key => $value) {


            if (is_numeric($key)) {
                $r = config('image.resize.'.$value);
                if ($r) {
                    $format_resize[$value] = $r;
                }
            }else {
                $format_resize[$key] = $value;
            }
        }

        $file = new Upload($request->file('file'));
        $file->setRequireData($require_data);
        $hash = $file->hash();

        ///保存到数据库
        ///保存到数据库 - 1检查是否已经存在这个数据了

        $cond = [
            'hash'              =>  $hash,
            'require_hash'      =>  $file->getRequireHash(),
            'file_status'       => 'success',
            'file_type'         =>  $template,
        ];


        $row = UploadImg::where($cond)->first();


        if ($row) {
            Log::debug('图像存在，直接返回。'.json_encode($row));
            return $this->success($row);
        }else {
            Log::debug('图像不存在，查询条件。'.json_encode($cond));
        }

        ///保存到数据库 - 2保存到数据库
        $data = [
            'hash'              =>  $hash,
            'require_data'      =>  json_encode($require_data),
            'require_hash'      =>  $file->getRequireHash($require_data),
            'ip'                =>  $request->getClientIp(),
            'file_status'       =>  'init',
            'file_type'         =>  $template,
            'original_img_type' =>  $file->getExtension(),    //标识这张原图的类型
            'img_type'          =>  $file->getExtension(),
            'width'             =>  $file->getImage()->width(),
            'height'            =>  $file->getImage()->height(),
        ];
        $img = UploadImg::create($data);

        ///存储图片
        foreach($format_resize as $name => $resize) {
            Log::debug('调用图像存储'.$name.'大小是:'.json_encode($resize));
            $file->saveResizeImage($name,$resize);
        }

        ///储存完成更新数据库file_status
        $img->fill(['file_status'=>'success'])->save();
        // UploadImg::instance()->update($img->img_id, ['file_status'=>'success']);

        ///获得这个图片
        $img = UploadImg::find(['img_id'=>$img->img_id])->first();

        return $this->success($img);
    }


}
