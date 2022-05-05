<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\UnixTimestamp;

use App\Helpers\Upload;
use Illuminate\Support\Facades\Storage;
use App\Observers\UploadImgObserver;

class UploadImg extends Model
{

    use SoftDeletes;
    use UnixTimestamp;

    public static function boot() {
        parent::boot();
        UploadImg::observe(new UploadImgObserver());
    }

    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'b_upload_img';

    /**
     * Define a primary key
     *
     * @var string
     */
    protected $primaryKey = 'img_id';


    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = 'delete_time';

    protected $dates = [
        'create_time',
        'update_time',
        'delete_time',
    ];
    /**
     * Define the table field information
     *
     * @var array
     */
    public $columns = [
        'img_id',
        'hash',
        'require_hash',
        'require_data',
        'admin_id',
        'create_time',
        'ip',
        'file_type',
        'file_status',
        'img_type',
        'original_img_type',
        'height',
        'width',
    ];

    /**
     * A property that cannot be batched.
     *
     * @var array
     */
    protected $guarded = ['img_id'];

    protected $appends = ['image_urls'];

    protected $visible = ['img_id','image_urls'];

     /**
     * 获得图片对应的存储图片访问url
     *
     * @return bool
     */
    public function getImageUrlsAttribute()
    {   

        $urls = [];

        $url = Upload::getUrl('origin',$this->hash,$this->require_hash,$this->img_type);


        // $url['origin'] = Upload::getUrl('origin',$this->hash,$this->require_hash,$this->img_type);


        return [
            'origin_url'  =>  Upload::getUrl('origin',$this->hash,$this->require_hash,$this->img_type),
            'url'    =>  Upload::getUrl($this->file_type,$this->hash,$this->require_hash,$this->img_type),
        ];
    }

    public function getPath($file_type = 'origin') {
        return Upload::getPath($file_type,$this->hash,$this->require_hash,$this->img_type);
    }

    public function getFullPath($file_type = 'origin') {
        return storage_path() . '/app/'. $this->getPath($file_type);
    }
}
