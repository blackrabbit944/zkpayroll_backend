<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Link;
use App\Models\User;
use App\Models\Profile;

use App\Helpers\Alchemy;

use App\Http\Requests\LinkRequest;

use App\Services\LinkService;

use App\Events\FetchTokenInfoEvent;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Collection;

use Illuminate\Http\Response;

use Carbon\Carbon;


class LinkController extends Controller
{

    public function add(LinkRequest $request) {

        $attributes = $request->only([
            'text',
            'show_type',
            'url',
            'platform',
            'account'
        ]);

        if ($request->user()->cannot('create', [Link::class,$attributes])) {
            return $this->failed('you have no access for add Link');
        }

        $user_id = auth('api')->user()->user_id;
        $count = Link::where(['user_id'=>$user_id])->count();

        if ($count >= 100) {
            return $this->failed('you cannot create over 100 links');
        }

        $Link = Link::create($attributes);

        if ($Link) {
            $Link->refresh();
            return $this->success($Link);
        }else {
            return $this->failure('create error');
        }
 
    }


    public function delete(LinkRequest $request) {

        $Link = Link::where([
            'id'     =>  $request->input('id'),
        ])->first();

        if (!$Link) {
            return $this->failed('Link is not exist');
        }

        if ($request->user()->cannot('delete', $Link)) {
            return $this->failed('you have no access for delete Link');
        }

        $ret = $Link->delete();

        return $this->success([]);
    }

    public function update(LinkRequest $request) {


        $attributes = $request->only([
            'text','url','is_visible','show_type','sort_id','platform','account'
        ]);

        $Link = Link::find($request->input('id'));

        if (!$Link) {
            return $this->failed('Link is not exist');
        }

        if ($request->user()->cannot('update', $Link)) {
            return $this->failed('you have no access for update Link');
        }

        $Link->fill($attributes);
        $Link->save();

        $Link->fresh();
        // $Link->format();

        return $this->success($Link);
    }



    public function load(LinkRequest $request) {

        $Link = Link::find($request->input('id'));

        if (!$Link) {
            return $this->failed('Link is not exist');
        }
        
        $Link->format();
        // $Link->append('');

        return $this->success($Link);
    }


    public function list(LinkRequest $request) {

        $user = User::where(['wallet_address'=>$request->input('address')])->first();
        
        if (!$user) {
            return $this->failed('User does not exist');
        }

        $cond=[
            'user_id'   =>  $user->user_id,    
        ];

        if ($request->input('show_type')) {
            $cond['show_type'] = $request->input('show_type');
        }

        $data = Link::where($cond)->orderby('sort_id','asc')->get();


        return $this->success($data);

    }

    public function allList(LinkRequest $request) {

        if ($request->input('address')) {
            $user = User::where(['wallet_address'=>$request->input('address')])->first();
        }elseif ($request->input('name')) {
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

        $cond=[
            'user_id'   =>  $user->user_id,    
        ];

        $data = Link::where($cond)->orderby('sort_id','asc')->get();

        $icon_list = new Collection([]);
        $button_list = new Collection([]);

        foreach($data as $row) {
            switch($row->show_type) {
                case 'button':
                    $button_list->push($row);
                    break;
                case 'icon':
                    $icon_list->push($row);
                    break;
            }
        }

        return $this->success([
            'button_list'   => $button_list,
            'icon_list'   => $icon_list,
        ]);

    }

    public function sort(LinkRequest $request) {

        $ids = explode(',',$request->input('ids'));

        foreach($ids as $id) {
            $link = Link::find($id);
            if ($link) {
                if ($request->user()->cannot('update', $link)) {
                    return $this->failed('you have no access for updating UsefulLink');
                }
            }else {
                return $this->failed('link不存在，id:'.$id);
            }
        }

        $i = 0;
        foreach ($ids as $id) {
            $link = Link::find($id);
            $link->sort_id = $i;
            $link->save();
            $i += 1;
        }

        return $this->success('');

    }



}
