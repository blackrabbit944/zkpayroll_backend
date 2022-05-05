<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
// use App\Observers\ItemObserver;
use Carbon\Carbon;
    
use App\Models\Traits\UnixTimestamp;
use App\Helpers\DiscordCdn;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\DiscordUser;

class DiscordGuild extends Model
{
    use UnixTimestamp;
    use HasFactory;
    use SoftDeletes;

    public static function boot() {
        parent::boot();
        // Item::observe(new ItemObserver());
    }
    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'b_discord_guild';

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

    protected $appends = ['avatar_url'];
    protected $hidden = ['webhook_token','webhook_id','update_time','delete_time'];

    public function format() {
        $this->collection;
        // Log::debug('准备appendorder');
        $this->append('order');
    }


    public function club()
    {
        return $this->belongsTo(Club::class,'club_id','id');
    }

    public function discord_user()
    {
        return $this->belongsTo(DiscordUser::class,'discord_user_id','discord_user_id');
    }

    public function getWebhookUrlAttribute() {
        if ($this->webhook_id && $this->webhook_token) {
            return sprintf("https://discord.com/api/webhooks/%s/%s",$this->webhook_id,$this->webhook_token);
        }else {
            return null;
        }
    }

    public function getAdminWebhookUrlAttribute() {
        if ($this->admin_webhook_id && $this->admin_webhook_token) {
            return sprintf("https://discord.com/api/webhooks/%s/%s",$this->admin_webhook_id,$this->admin_webhook_token);
        }else {
            return null;
        }
    }

    public function getAvatarUrlAttribute() {
        if ($this->guild_id && $this->icon) {
            return DiscordCdn::GuildAvatar($this->guild_id,$this->icon);
        }else {
            return '';
        }
    }


    public function getInviteLinkAttribute() {
        if ($this->invite_code) {
            return 'https://discord.gg/'.$this->invite_code;
        }else {
            return '';
        }
    }

    public function sendAdminMessage($message) {
        
        if (!$this->admin_webhook_url) {
            Log::debug('准备发送管理员信息时候发现对应的admin_webhook_url是空，guild_id:'.$this->guild_id);
            return false;
        }

        $log = [
            'guild_id'  =>  $this->guild_id,
            'club_id'   =>  $this->club_id,
            'action_type'   =>  'send_admin_notify',
            'content'   =>  $message,
        ];

        Log::debug('准备发送管理员可见消息：'.$message);
        $discord_log = DiscordLog::create($log);

        return $discord_log;
    }

    public function sendMessage($message) {
        
        if (!$this->webhook_url) {
            Log::debug('准备发送管理员信息时候发现对应的webhook_url是空，guild_id:'.$this->guild_id);
            return false;
        }

        $log = [
            'guild_id'  =>  $this->guild_id,
            'club_id'   =>  $this->club_id,
            'action_type'   =>  'send_notify',
            'content'   =>  $message,
        ];

        Log::debug('准备发送普通用户可见消息：'.$message);


        $discord_log = DiscordLog::create($log);

        return $discord_log;
    }



}
