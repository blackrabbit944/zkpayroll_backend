<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\PostObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\Traits\UnixTimestamp;

use App\Helpers\Draft;
use App\Models\Traits;

use Carbon\Carbon;

use Laravel\Scout\Searchable;
use Illuminate\Support\Facades\Log;
use App\Helpers\Rank;


class Post extends Model
{

    use SoftDeletes;
    // use Searchable;
    use UnixTimestamp;
    use HasFactory;
    // use \App\Models\Traits\JiandaLang;

    public static function boot() {
        parent::boot();
        Post::observe(new PostObserver());
    }

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = 'delete_time';

    protected $dates = [
        'create_time',
        'update_time',
        'delete_time',
    ];

    protected $attributes = [
        'comment_count' =>  0,
        'up_count'      =>  0,
        'down_count'    =>  0,
    ];

    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'b_post';

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
        'draft_content_id',
        'user_id',
        'create_time',
        'update_time',
        'delete_time',
        'up_count',
        'down_count',
        'comment_count',
        'club_id',
        'title'
    ];

    /**
     * A property that cannot be batched.
     *
     * @var array
     */
    protected $fillable = ['draft_content_id','user_id','club_id','title'];

    /**
     * 获取拥有此post的用户
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','user_id');
    }

    /**
     * 获取博客文章
     */
    public function draft_content() {
        return $this->belongsTo('App\Models\DraftContent','draft_content_id','id');
    }

    /**
     * 获取博客文章
     */
    public function club() {
        return $this->belongsTo('App\Models\Club','club_id','id');
    }

    /**
     * votes
     */
    // public function votes()
    // {
    //     return $this->morphToMany('App\Models\Vote','item','b_vote','item_id','vote_id');
    // }


    
    /**
     * feed
     */
    // public function feed()
    // {
    //     return $this->morphOne('App\Models\Feed','item');
    // }

    /*
    *   计算rank的方式
    *   这里会计算2个rank，一个是会随着时间衰减的rank（算法使用的是reddit），另一个是不衰减时间的top_rank
     */
    // public function calcRank() {

    //     //一个comment被当作2个赞同票
    //     $up_count = $this->up_count + 2 * $this->comment_count;

    //     $rank = Rank::calculate($this->create_time,$up_count,$this->down_count);
    //     $top_rank = Rank::calculateTopRank($up_count,$this->down_count);

    //     $this->rank = $rank;
    //     $this->top_rank = $top_rank;

    //     Log::info('当前计算的结果是,rank:'.$this->rank.'，top_rank:'.$this->top_rank);
    //     $this->save();

    //     $is_changed = ($up_count > 0 || $this->down_count > 0);

    //     ///需要同步更新对应的feed的数值
    //     if ($this->feed && $is_changed) {
    //         $this->feed->rank = $rank;
    //         $this->feed->top_rank = $top_rank;
    //         $this->feed->save();
    //     }
    // }
    // public function getClubUserAttribute() {
    //     return ClubUser::where(['club_id'=>$this->club_id,'user_id'=>$this->user_id])->first();
    // }

    public function searchableAs()
    {
        return app()->environment().'_b_post';
    }
    /**
     * 获取模型的可搜索数据
     *
     * @return array
     */
    // public function toSearchableArray()
    // {

    //     $array = $this->toArray();

    //     if ($this->draft_content_id) {
    //         if ($this->draft_content) {
    //             if (Draft::isDraft($this->draft_content->content)) {
    //                 $array['content'] = Draft::getDraftText($this->draft_content->content);
    //             }else {
    //                 $array['content'] = $this->draft_content->content;
    //             }
    //         }
    //     }


    //     return $array;
    // }

    // public function getMyVoteAttribute() {
    //     if (auth('api')->user()) {
    //         return Vote::where([
    //             'user_id'=>auth('api')->user()->user_id,
    //             'item_type'=>'post',
    //             'item_id'=>$this->post_id
    //         ])->first();
    //     }else {
    //         return null;
    //     }
    // }

    public function format() {
        $this->draft_content;
        // $this->user;
        // $this->club;
        // $this->append('club_user');
        return $this;
    }

}