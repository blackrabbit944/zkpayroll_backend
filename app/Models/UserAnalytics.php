<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
    
use App\Models\Traits\UnixTimestamp;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Observers\UserAnalyticsObserver;

class UserAnalytics extends Model
{
    use UnixTimestamp;
    use HasFactory;

    public static function boot() {
        parent::boot();
        UserAnalytics::observe(new UserAnalyticsObserver());
    }
    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'b_user_analytics';

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
        'has_buy_record',
        'has_order',
        'has_sell_record',
        'invite_count',
        'invite_address',
        'sell_volume',
        'buy_volume'
    ];

    protected $attributes = [
        'has_buy_record' => 0,
        'has_order'     =>  0,
        'has_sell_record'   =>  0,
        'invite_count'      =>  0,
        'invite_address'    =>  '',
        'sell_volume'       =>  0,
        'buy_volume'        =>  0
    ];

    /**
     * A property that cannot be batched.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'create_time',
    ];

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = null;

    protected $dates = [
        'create_time',
        'update_time',
        'delete_time'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'wallet_address','wallet_address');
    }

}
