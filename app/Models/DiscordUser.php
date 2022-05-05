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

class DiscordUser extends Model
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
    protected $table = 'b_discord_user';

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

    protected $hidden = ['access_token','refresh_token','email','avatar','delete_time','id','token_expire_time','update_time'];

    public function format() {
        $this->user;
        // Log::debug('准备appendorder');
    }

    public function getAvatarUrlAttribute() {
        return DiscordCdn::UserAvatar($this->discord_user_id,$this->avatar);
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','user_id');
    }



}
