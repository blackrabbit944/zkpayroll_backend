<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Observers\TxObserver;
use App\Models\Club;
use App\Models\User;

use Carbon\Carbon;
    
use App\Models\Traits\UnixTimestamp;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tx extends Model
{
    use UnixTimestamp;
    use HasFactory;

    public static function boot() {
        parent::boot();
        Tx::observe(new TxObserver());
    }
    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'b_tx';

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
    ];

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = null;

    protected $dates = [
        'create_time',
        'update_time',
    ];

    public function format() {
        $this->from_user;
        if ($this->from_user) {
            $this->from_user->discord_user;
        }
        $this->to_user;
        if ($this->to_user) {
            $this->to_user->discord_user;
        }
        return $this;
    }

    public function club()
    {
        return $this->belongsTo(Club::class,'contract_address','contract_address');
    }

    public function from_user()
    {
        return $this->belongsTo(User::class,'from_address','wallet_address');
    }

    public function to_user()
    {
        return $this->belongsTo(User::class,'to_address','wallet_address');
    }

}
