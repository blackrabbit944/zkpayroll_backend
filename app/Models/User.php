<?php

namespace App\Models;

use Carbon\Carbon;
use App\Helpers\User as UserHelper;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Auth\Authorizable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use App\Models\Traits\UnixTimestamp;

use Illuminate\Notifications\Notifiable;

use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordInterface;

use App\Notifications\ResetPasswordNotification;
use App\Mail\ResetPasswordMail;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject, CanResetPasswordInterface
{

    use Authenticatable, Authorizable;
    use UnixTimestamp;
    use CanResetPasswordTrait;
    use Notifiable;
    use HasFactory;

    ///发送重置密码的email
    public function sendPasswordResetNotification($token) {
        $notification = new ResetPasswordNotification($token);
        $this->notify($notification);
    }


    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = null;

    protected $dates = [
        'create_time',
        'update_time',
    ];

    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'b_user';

    /**
     * Define a primary key
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * Define the table field information
     *
     * @var array
     */
    public $columns = [
        'user_id',
        'wallet_address',
        'create_time',
        'update_time',
    ];

    // protected $appends = ['avatar'];

    /**
     * A property that cannot be batched.
     *
     * @var array
     */
    protected $guarded = ['user_id','create_time','update_time','is_super_admin'];

    protected $hidden = ['unique_hash'];
   
    public static function boot() {
        parent::boot();
        User::observe(new \App\Observers\UserObserver());
    }
    
    /**
     * 获取Discord绑定用户
     */
    public function discord_user()
    {
        return $this->hasOne('App\Models\DiscordUser','user_id','user_id');
    }

    /**
     * 获取Profile。
     */
    public function profile()
    {
        return $this->hasOne('App\Models\Profile','user_id','user_id');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {   
        return $this->user_id;
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function isSuperAdmin() {
        return $this->is_super_admin ? true :false;
    }
    

    public function format() {
    }


    public function toArray() {
        return parent::toArray();
    }

}
