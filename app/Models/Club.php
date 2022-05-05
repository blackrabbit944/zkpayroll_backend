<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UnixTimestamp;
use App\Observers\ClubObserver;
use App\Helpers\User as UserHelper;
use App\Helpers\Image;

use App\Models\UploadImg;
use App\Models\Tx;
use App\Models\ClubUser;
use App\Helpers\TinyclubNftContract;
use App\Helpers\TinyclubGrpc;
use App\Helpers\TinyclubContract;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Club extends Model
{
    
    use UnixTimestamp;
    use HasFactory;
    use SoftDeletes;
    
    public static function boot() {
        parent::boot();
        Club::observe(new ClubObserver());
    }
    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'b_club';

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
    ];

    protected $guarded = [
        'id',
        'create_time',
        'update_time',
    ];

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = 'delete_time';

    protected $dates = [
        'create_time',
        'update_time',
        'delete_time'
    ];


    /**
     * 获取Discord绑定社区
     */
    public function discord_guild()
    {
        return $this->hasOne('App\Models\DiscordGuild','club_id','id');
    }


    /**
     * 获取拥有此club的用户
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','user_id');
    }

    public function isMine() {
        $user = auth('api')->user();
        if (!$user) {
            return false;
        }
        if ($user->user_id != $this->user_id) {
            return false;
        } 
        return true;
    }

    public function getMintCountAttribute() {
        if (!$this->contract_address) {
            return 0;
        }

        return Tx::where(['contract_address'=>$this->contract_address,'tx_type'=>'mint'])->count();
    }

    public function getDiscordUserCountAttribute() {
        if (!$this->contract_address) {
            return 0;
        }

        return ClubUser::where(['contract_address'=>$this->contract_address,'left_time'=>0])->where('join_time','>',0)->count();
    }

    /**
     * 对应的nft_avatars
     */
    public function avatar()
    {
        return $this->belongsTo('App\Models\UploadImg','avatar_img_id','img_id');
    }


    public function format() {
        $this->avatar;
    }

    public function getInviteLinkAttribute() {

        if (!$this->discord_guild) {
            return '';
        }    


        if (!$this->discord_guild->invite_link) {
            $tinyclubGrpcHelper = new TinyclubGrpc();
            $invite_code = $tinyclubGrpcHelper->getInviteLink($this->discord_guild);
            $this->discord_guild->invite_code = $invite_code;
            $this->discord_guild->save();
        }

        return $this->discord_guild->invite_link;

    }

    public function getMintDataAttribute() {
       
        if ($this->contract_address) {
            $tinyclubNftContract = new TinyclubNftContract($this->contract_address);
            // return $tinyclubNftContract->getMintData();
            $mint_data = $tinyclubNftContract->getMintDataByCache();
            
            $sale_price = $mint_data['mint_price'];
            ////把销售价格加上现在的费率
            $tinyclubContractHelper = new TinyclubContract();
            $tinyclub_fee = $tinyclubContractHelper->platformFeePPM();
            $tinyclub_fee_in_sale_price = bcmul($mint_data['mint_price'],$tinyclub_fee,8);

            $sale_price = bcadd($mint_data['mint_price'] ,$tinyclub_fee_in_sale_price , 12);

            $mint_data['sale_price'] = $sale_price;
            $mint_data['tinyclub_fee'] = $tinyclub_fee_in_sale_price;

            return $mint_data;

        }else {
            return [
                'mint_price'    =>  0,
                'max_supply'    =>  0,
                'sale_price'    =>  0,
                'tinyclub_fee'  =>  0,
                'total_minted'  =>  0
            ];
        }
    }
 }
