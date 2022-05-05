<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Club;
use App\Http\Requests\ClubRequest;
use App\Services\ItemService;

use App\Models\DraftContent;
use App\Models\User;

use Illuminate\Support\Facades\Log;

use App\Helpers\TinyclubNftContract;
use App\Services\DiscordService;
use App\Helpers\TinyclubGrpc;

class ClubController extends Controller
{

    public function add(ClubRequest $request) {

        $attributes = $request->only([
            'name',
            'introduction',
            'avatar_img_id',
            'unique_name',
            'name_in_nft',
            'nft_bg',
            'nft_font'
        ]);


        
        if ($request->user()->cannot('create', [Club::class,$attributes])) {
            return $this->failed('you have no access for add Club');
        }

        $user_id = auth('api')->user()->user_id;
        $count = Club::where(['user_id'=>$user_id])->count();

        if ($count >= 100) {
            return $this->failed('you cannot create over 100 clubs');
        }

        $Club = Club::create($attributes);

        if ($Club) {
            $Club->refresh();
            return $this->success($Club);
        }else {
            return $this->failure('create error');
        }
 
    }


    public function delete(ClubRequest $request) {

        $Club = Club::where([
            'id'     =>  $request->input('id'),
        ])->first();

        if (!$Club) {
            return $this->failed('Club is not exist');
        }

        if ($request->user()->cannot('delete', $Club)) {
            return $this->failed('you have no access for delete Club');
        }

        $ret = $Club->delete();

        return $this->success([]);
    }

    public function update(ClubRequest $request) {


        $attributes = $request->only([
            'name',
            'introduction',
            'passcard_max_count',
            'passcard_type',
            'avatar_img_id', 
            'unique_name',
            'royalty'
        ]);

        $Club = Club::find($request->input('id'));

        if (!$Club) {
            return $this->failed('Club is not exist');
        }

        if ($request->user()->cannot('update', $Club)) {
            return $this->failed('you have no access for update Club');
        }

        $Club->fill($attributes);
        $Club->save();

        $Club->fresh();
        // $Club->format();

        return $this->success($Club);
    }

    public function getInviteLink(ClubRequest $request) {
        
        ///创建一个club的邀请链接
        if ($request->input('id')) {
            $club = Club::find($request->input('id'));
        }else {
            $club = Club::where(['unique_name'=>$request->input('name')])->first();
            if (!$club) {
                $club = Club::where(['id'=>$request->input('name')])->first();
            }
        }

        if (!$club) {
            return $this->failed('club is not exist');
        }


        $discord_guild = $club->discord_guild;
        if (!$discord_guild) {
            return $this->failed('discord_guild is not exist');
        }    


        if (!$discord_guild->invite_link) {
            $tinyclubGrpcHelper = new TinyclubGrpc();
            $invite_code = $tinyclubGrpcHelper->getInviteLink($discord_guild);
            
            $discord_guild->invite_code = $invite_code;
            $discord_guild->save();
        }

        return $this->success([
            'invite_link'   =>  $discord_guild->invite_link
        ]);
    }



    public function load(ClubRequest $request) {

        if ($request->input('id')) {
            $club = Club::find($request->input('id'));
        }else {
            $club = Club::where(['unique_name'=>$request->input('name')])->first();
            if (!$club) {
                $club = Club::where(['id'=>$request->input('name')])->first();
            }
        }

        if (!$club) {
            return $this->failed('club is not exist');
        }
        
        $club->format();

        ///增加合约数据
        $club->append(['mint_data']);
        ///增加统计数据
        $club->append(['mint_count','discord_user_count']);
        
        $club->append(['invite_link']);

        return $this->success($club);
    }


    public function list(ClubRequest $request) {

        if ($request->input('address')) {
            $user = User::where(['wallet_address'=>$request->input('address')])->first();
        }elseif ($request->input('is_mine')) {
            $user = auth('api')->user();
        }

        if (!$user) {
            return $this->failed('User does not exist');
        }

        $cond=[
            'user_id'   =>  $user->user_id,    
        ];

        if ($request->input('show_type')) {
            $cond['show_type'] = $request->input('show_type');
        }

        $data = Club::where($cond)->orderby('create_time','desc')->get();

        $data->transform(function ($value) {
            $value->format();
            return $value;
        });

        return $this->success($data);

    }


}
