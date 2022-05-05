<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\SignObserver;
use Carbon\Carbon;
    
use App\Models\Traits\UnixTimestamp;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sign extends Model
{
    use UnixTimestamp;
    use HasFactory;

    public static function boot() {
        parent::boot();
        Sign::observe(new SignObserver());
    }
    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'b_sign';

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
        'delete_time'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'contract_address','contract_address');
    }



}
