<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Post;
use App\Models\Question;
use App\Models\DraftContent;
use App\Models\Tag;

use App\Http\Requests\PostRequest;

use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Gate;

use Carbon\Carbon;

class PostController extends Controller
{

    public function add(PostRequest $request) {


        $attributes = $request->only(['club_id','title']);

        if ($request->user()->cannot('create', [Post::class,$attributes])) {
            return $this->failed('you have no access for adding post');
        }

        $post = Post::where([
            'user_id'   =>  auth('api')->user()->user_id,
            'club_id'   =>  $request->input('club_id'),
            'title'     =>  $request->input('title'),
        ])->first();


        if ($post) {
            return $this->failed('you have post this title before, please use post/edit');
        }

        if (!$post) {
            ///先创建一个draft_content;
            $content = DraftContent::create([
                'content' => $request->input('content')
            ]) ;
            $content->save();

            $attributes['draft_content_id'] = $content->id;
            // dump($attributes);
            $post = Post::create($attributes);
        }

        $post->format();

        return $this->success($post);
    }

    public function update(PostRequest $request) {

        $attributes = $request->only(['content','title']);
        $post = Post::find($request->input('post_id'));

        if ($request->user()->cannot('update', $post)) {
            return $this->failed('you have no access for updating post');
        }
        if ($request->input('content')) {
            $post->draft_content->content = $request->input('content');
            $post->draft_content->save();
        }
        if ($request->input('title')) {
            $post->title = trim($request->input('title'));
            $post->save();
        }

        $post->format();
        return $this->success($post);
    }

    public function delete(PostRequest $request) {

        $post = Post::find($request->input('post_id'));
        if ($request->user()->cannot('delete', $post)) {
            return $this->failed('you have no access for deleting post');
        }
        $post->delete();

        return $this->success($post);
    }

    public function load(PostRequest $request) {

        $post = Post::find($request->input('post_id'));
        $post->format();

        return $this->success($post);
    }

    public function list(PostRequest $request) {

        $cond  = $request->only(['club_id']);
        $order = get_order_by($request);

        $data = Post::where($cond)->with(['draft_content','user'])->orderBy($order[0],$order[1])->paginate(20);

        ///循环format
        foreach($data->items() as $item) {
            $item->format();
        }
        
        return $this->success($data);

    }

}
