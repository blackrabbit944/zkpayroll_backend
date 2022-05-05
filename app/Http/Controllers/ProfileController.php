<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile;
use App\Http\Requests\ProfileRequest;
use App\Services\ItemService;

use App\Models\DraftContent;
use App\Models\User;

use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{

    public function set(ProfileRequest $request) {

        $attributes = $request->only([
            'bio',
            'ens',
            'name',
            'theme',
            'unique_name',
            'avatar_img_id',
            'nft_avatar_id'
        ]);

        $user = $request->user();
        $profile = $request->user()->profile;


        if (
            $request->input('nft_contract_address') 
            && $request->input('nft_token_id') 
            ) {
            $data = [
                'asset_contract_address'=> $request->input('nft_contract_address'),
                'token_id'              => $request->input('nft_token_id'),
            ];

            Log::debug('NFT头像：准备从alchemy验证头像的所属.');

            ///检查用户是否持要使用的NFT
            $alchemyHelper = new \App\Helpers\Alchemy;
            $is_belong_user = $alchemyHelper->isOwner($user->wallet_address,$data['asset_contract_address'],$data['token_id']);

            Log::debug('NFT头像：获得资源的结果是'.json_encode($is_belong_user));

            if ($is_belong_user === true) {
                Log::debug('NFT头像：获得资源的确是用户拥有');

                $itemService = new ItemService();
                $nft = $itemService->create([
                    'contract_address'       => $request->input('nft_contract_address'),
                    'token_id'               => $request->input('nft_token_id'),
                ]);

                if ($nft) {
                    $attributes['avatar_item_id'] = $nft->id;
                }
            }else {
                Log::debug('NFT头像：获得资源并不属于当前用户');
            }
        }

        //验证uniquename部分
        if ($request->input('unique_name')) {
            $unique_name = trim($request->input('unique_name'));
            $check = Profile::where(['unique_name'=>$unique_name])->first();
            if ($check) {
                if ($profile && $check->id == $profile->id) {
                    //允许如果是我自己的
                }else {
                    return $this->failed('unique_name already exist');
                }
            }
        }

        if ($request->input('avatar_img_id')) {
            $attributes['avatar_item_id'] = '0';
        }

        if (!$profile) {
            $profile = $user->profile()->save(new Profile($attributes));
        }else {
            $profile->fill($attributes);
            $profile->save();
        }


        $user = auth('api')->user()->refresh();
        $user->profile;
        $user->profile->format();

        return $this->success([
            'user'      =>  $user
        ]);
    }



    public function load(ProfileRequest $request) {

        if ($request->input('user_id')) {
            $user = User::find($request->input('user_id'));
        }elseif($request->input('name')) {
            $user = User::where(['wallet_address'=>$request->input('name')])->first();
            if (!$user) {
                $profile = Profile::where(['unique_name'=>$request->input('name')])->first();
                if ($profile) {
                    $user = $profile->user;
                }
            }
        }
        if (!$user) {
            return $this->failed('User does not exist');
        }

        $user->profile;
        if ($user->profile) {
            $user->profile->format();
        }

        return $this->success($user);
    }



}
