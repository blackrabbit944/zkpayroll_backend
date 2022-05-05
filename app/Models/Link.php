<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\LinkObserver;
use Carbon\Carbon;
    
use App\Models\Traits\UnixTimestamp;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Link extends Model
{
    use UnixTimestamp;
    use HasFactory;
    use SoftDeletes;

    public static function boot() {
        parent::boot();
        Link::observe(new LinkObserver());
    }
    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'b_link';

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
        $this->user;
    }


    public function user()
    {
        return $this->belongsTo(User::class,'id','user_id');
    }


}
