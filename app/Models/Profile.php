<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UnixTimestamp;
use App\Observers\ProfileObserver;
use App\Helpers\User as UserHelper;
use App\Helpers\Image;

use App\Models\UploadImg;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Profile extends Model
{
    
    use UnixTimestamp;
    use HasFactory;

    public static function boot() {
        parent::boot();
        Profile::observe(new ProfileObserver());
    }
    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'b_profile';

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
        'id',
    ];

    protected $guarded = [
        'id',
        'create_time',
        'update_time',
    ];


    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    protected $dates = [
        'create_time',
        'update_time'
    ];

    /**
     * 获取拥有此profile的用户
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','user_id');
    }

    public function getShowAvatarAttribute() {



        if ($this->nft_avatar) {

            $nft_name = ($this->nft_avatar->collection) ? $this->nft_avatar->collection->name : '';

            return [
                'url'               =>  $this->nft_avatar->http_image_url, 
                'is_nft'            =>  1,
                'contract'          =>  [
                    'contract_address'  =>  $this->nft_avatar->contract_address,
                    'token_id'          =>  $this->nft_avatar->token_id,
                    'name'              =>  $nft_name
                ]
            ];
        }


        if (isset($this->attributes['avatar_img_id'])) {

            $image = Image::getAvatar($this->attributes['avatar_img_id']);
            return [
                'url'   =>  $image['url'],
                'is_nft'    =>  0
            ];
        }

        $url = UserHelper::getDefaultAvatarUrl($this->user_id);

        if ($url) {
            return ['url'=>$url,'is_nft'=>0];
        }

        return ['url'=>'https://api.lorem.space/image?w=200&h=200','is_nft'=>0];

    }


    /**
     * 对应的nft_avatars
     */
    public function nft_avatar()
    {
        return $this->belongsTo('App\Models\Item','avatar_item_id','id');
    }


    /**
     * 对应的nft_avatars
     */
    public function avatar()
    {
        return $this->belongsTo('App\Models\UploadImg','avatar_img_id','avatar_id');
    }


    public function format() {
        // $this->nft_avatar;
        // $this->avatar;
        $this->append('show_avatar');
    }
}
