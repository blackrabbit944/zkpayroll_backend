<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\CollectionObserver;
use Carbon\Carbon;
    
use App\Models\Traits\UnixTimestamp;
use App\Helpers\Image;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class Collection extends Model
{
    use Searchable;
    use UnixTimestamp;
    use HasFactory;
    use SoftDeletes;

    public static function boot() {
        parent::boot();
        Collection::observe(new CollectionObserver());
    }
    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'b_collection';

    /**
     * Define a primary key
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Define the table field information
     *
     * @var array
     */
    public $columns = [
    ];

    /**
     * A property that cannot be batched.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'create_time',
        'update_time',
        'delete_time'
    ];

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = 'delete_time';

    protected $dates = [
        'create_time',
        'update_time',
        'delete_time'
    ];

    public function format() {
        $this->avatar_img;
        $this->cover_img;
    }

    /**
     * 获取头像
     */
    public function avatar_img()
    {
        return $this->belongsTo('App\Models\UploadImg','avatar_img_id','img_id');
    }

    /**
     * 获取封面
     */
    public function cover_img()
    {
        return $this->belongsTo('App\Models\UploadImg','cover_img_id','img_id');
    }

    public function getAvatarAttribute() {
        if (isset($this->attributes['avatar_img_id'])) {
            return Image::getAvatar($this->attributes['avatar_img_id']);
        }
        return ['url'=>''];
    }

    public function getFloorPriceAttribute() {
        $order = Order::where([
            'contract_address'  =>  $item->contract_address
            
        ])->where('expire_time','>',time())->orderBy('price','asc')->fist();

        if ($order) {
            return $order->price;
        }else {
            return null;
        }
    }

    public function searchableAs()
    {
        return app()->environment().'_b_collection';
    }

    /**
     * 获取模型的可搜索数据
     *
     * @return array
     */
    public function toSearchableArray()
    {
        
        $array = [];

        $array['name'] = $this->name;
        $array['symbol'] = $this->symbol;
        $array['search_name'] = strtolower($array['name']);
        $array['search_symbol'] = strtolower($array['symbol']);
        $array['contract_address'] = strtolower($this->contract_address);

        return $array;
    }

    public function getCoverAttribute() {

        if (isset($this->attributes['cover_img_id'])) {
            return Image::getImage($this->attributes['cover_img_id']);
        }

        // $url = ClubHelper::getDefaultCoverUrl($this->club_id);
        
        // if ($url) {
        //     return ['url'=>$url];
        // }

        return ['url'=>'https://api.lorem.space/image?w=1024&h=180'];
    }

}
