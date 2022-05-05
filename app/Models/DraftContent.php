<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

use App\Models\Traits\UnixTimestamp;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

use App\Observers\DraftContentObserver;


class DraftContent extends Model 
{

    use SoftDeletes;
    use UnixTimestamp;
    use HasFactory;
    
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = 'delete_time';

    protected $dates = [
        'create_time',
        'update_time',
        'delete_time'
    ];


    public static function boot() {
        parent::boot();
        DraftContent::observe(new DraftContentObserver());
    }

    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'b_draft_content';

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
        'user_id',
        'content',
        'create_time',
        'update_time',
    ];

    /**
     * A property that cannot be batched.
     *
     * @var array
     */
    protected $fillable = ['content','user_id'];
    protected $hidden   = ['user_id','delete_time'];


}