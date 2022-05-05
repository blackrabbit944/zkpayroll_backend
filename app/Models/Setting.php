<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
    
use App\Models\Traits\UnixTimestamp;

use Illuminate\Support\Facades\Log;

class Setting extends Model
{
    use UnixTimestamp;

    public static function boot() {
        parent::boot();
    }
    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'b_setting';

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
        'key',
        'value',
        'create_time',
        'update_time'
    ];

    /**
     * A property that cannot be batched.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'value',
    ];

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = NULL;

    protected $dates = [
        'create_time',
        'update_time'
    ];


    // static public function save($key,$value) {
    //     $setting = self::where(['key'=>$key])->first();
    //     if ($setting) {
    //         $setting->fill(['value'=>$value]);
    //         $setting->save();
    //     }else {
    //         $setting = self::create(['key'=>$key,'value'=>$value]);
    //     }
    //     return $setting;
    // }

    // static public function getByKey($key) {
    //     return self::where(['key'=>$key])->first();
    // }
}
