<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Observers\ItemHistoryObserver;
use Carbon\Carbon;
    
use App\Models\Traits\UnixTimestamp;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemHistory extends Model
{
    use UnixTimestamp;
    use HasFactory;

    public static function boot() {
        parent::boot();
        ItemHistory::observe(new ItemHistoryObserver());
    }
    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'b_item_history';

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
        return $this;
    }


    public function getItemAttribute()
    {
        return Item::where([
            'contract_address'  =>  $this->contract_address,
            'token_id'          =>  $this->token_id
        ])->first();
    }

}
