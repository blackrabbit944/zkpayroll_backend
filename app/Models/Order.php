<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\OrderObserver;
use Carbon\Carbon;
    
use App\Models\Traits\UnixTimestamp;
use App\Models\Item;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\SQLLock;

class Order extends Model
{
    use UnixTimestamp;
    use HasFactory;
    use SoftDeletes;
    use SQLLock;

    public static function boot() {
        parent::boot();
        Order::observe(new OrderObserver());
    }
    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'b_order';

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
        return $this;
    }

    public function collection()
    {
        return $this->belongsTo(Collection::class,'contract_address','contract_address');
    }

    public function getItemAttribute()
    {
        return Item::where([
            'contract_address'  =>  $this->contract_address,
            'token_id'          =>  $this->token_id
        ])->first();
    }

}
