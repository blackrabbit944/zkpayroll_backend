<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UnixTimestamp;
use App\Observers\ClubObserver;
use App\Helpers\User as UserHelper;
use App\Helpers\Image;

use App\Models\UploadImg;
use App\Models\DiscordLog;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClubUser extends Model
{
    
    use UnixTimestamp;
    use HasFactory;
    use SoftDeletes;
    
    public static function boot() {
        parent::boot();
        Club::observe(new ClubObserver());
    }
    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'b_club_user';

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

    protected $appends = ['show_avatar'];

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = 'delete_time';

    protected $dates = [
        'create_time',
        'update_time',
        'delete_time'
    ];



    /**
     * 获取拥有此club的用户
     */
    public function club()
    {
        return $this->belongsTo('App\Models\Club','contract_address','contract_address');
    }

    /**
     * 获取拥有此club的用户
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User','wallet_address','wallet_address');
    }

    public function getShowAvatarAttribute() {

        if (isset($this->attributes['avatar_img_id'])) {

            $image = Image::getAvatar($this->attributes['avatar_img_id']);
            return [
                'url'   =>  $image['url'],
            ];
        }

        $url = UserHelper::getDefaultAvatarUrl($this->user_id);

        if ($url) {
            return ['url'=>$url,'is_nft'=>0];
        }

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


    public function joinClub($count) {
        
        $this->token_number = $count;
        $this->save();

        Log::debug('调用到joinClub');
        if ($this->user && $this->user->discord_user) {
            ///发起一个创建用户的事件
            $log = [
                'guild_id'          =>  $this->club->discord_guild->guild_id,
                'club_id'           =>  $this->club->id,
                'discord_user_id'   =>  $this->user->discord_user->discord_user_id,
                'action_type'       =>  'add_member',
            ];
            $discord_log = DiscordLog::create($log);

            ///只有在discord发起了把用户加入member以后才会记录时间（在discordLog中处理了）
        }
    }

    public function leftClub() {
        
        $this->token_number = 0;
        $this->save();

        if ($this->user && $this->user->discord_user) {
            ///发起一个移除用户角色的事件
            $log = [
                'guild_id'          =>  $this->club->discord_guild->guild_id,
                'club_id'           =>  $this->club->id,
                'discord_user_id'   =>  $this->user->discord_user->discord_user_id,
                'action_type'       =>  'remove_member',
            ];
            DiscordLog::create($log);

            ///只有在discord发起了移除member以后才会记录时间（在discordLog中处理了）
        }
    }
}
