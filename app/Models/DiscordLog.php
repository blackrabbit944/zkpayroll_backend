<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\DiscordLogObserver;
use Carbon\Carbon;
    
use App\Models\Traits\UnixTimestamp;

use App\Helpers\DiscordCdn;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiscordLog extends Model
{
    use UnixTimestamp;
    use HasFactory;

    public static function boot() {
        parent::boot();
        DiscordLog::observe(new DiscordLogObserver());
    }
    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'b_discord_log';

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

    protected $attributes = [
        'status' => 'init',
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
    ];

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = null;

    protected $dates = [
        'create_time',
        'update_time',
        'delete_time'
    ];

    /**
     * 获取Discord绑定社区
     */
    public function discord_guild()
    {
        return $this->belongsTo('App\Models\DiscordGuild','guild_id','guild_id');
    }


    /**
     * 获取Discord绑定社区
     */
    public function club()
    {
        return $this->belongsTo('App\Models\Club','club_id','id');
    }


    /**
     * 获取Discord绑定社区
     */
    public function discord_user()
    {
        return $this->belongsTo('App\Models\DiscordUser','discord_user_id','discord_user_id');
    }

    // protected $appends = ['avatar_url'];

    // protected $hidden = ['access_token','refresh_token','email','avatar','delete_time','id','token_expire_time','update_time'];

    public function format() {
        // $this->user;
        // Log::debug('准备appendorder');
    }

    // public function user()
    // {
    //     return $this->belongsTo(User::class,'user_id','user_id');
    // }



}
