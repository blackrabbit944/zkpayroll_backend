<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\ItemObserver;
use Carbon\Carbon;
    
use App\Models\Traits\UnixTimestamp;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use UnixTimestamp;
    use HasFactory;
    use SoftDeletes;

    public static function boot() {
        parent::boot();
        Item::observe(new ItemObserver());
    }
    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'b_item';

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

    protected $appends = ['http_image_url'];

    public function format() {
        $this->collection;
        // Log::debug('准备appendorder');
        $this->append('order');
    }

    public function getOrderAttribute() {

        Log::debug('调用到getOrderAttribute');
        $order = Order::where([
            'contract_address'  =>  $this->contract_address,
            'token_id'          =>  $this->token_id,

        ])->where('expire_time','>',time())->first();

        if ($order) {
            Log::debug('查询到数据，查询条件'.json_encode([
                'contract_address'  =>  $this->contract_address,
                'token_id'          =>  $this->token_id,
            ]));
        }else {
            Log::debug('没有查询到数据，查询条件'.json_encode([
                'contract_address'  =>  $this->contract_address,
                'token_id'          =>  $this->token_id,
            ]));
        }

        return $order;
    }

    public function collection()
    {
        return $this->belongsTo(Collection::class,'contract_address','contract_address');
    }

    public function getImageUrlHttp() {
        $url = $this->image_url;
        if(substr($url, 0, 7) === "ipfs://"){
            // $gateway = 'https://cloudflare-ipfs.com/ipfs/';
            $gateway = 'https://ipfs.io/ipfs/';
            return str_replace('ipfs://',$gateway,$url);
        }else {
            return $url;
        }
    }

    public function getHttpImageUrlAttribute() {
        Log::debug('调用到：getFormatImageUrlAttribute');
        if ($this->local_path) {
            Log::debug('$this->local_path:'.$this->local_path);
            return config('global.static_url').'/'.$this->local_path;
        }else {
            return $this->getImageUrlHttp();
        }
        
    }


}
