<?php

namespace App\Helpers;

use App\Models\Question;
use App\Models\NewsletterPost;
use App\Models\Answer;
use App\Models\Comment;
use App\Models\MinBlog;
use App\Models\Share;
use App\Models\Post;

use Illuminate\Support\Facades\Cache;

class Item {

    public static function get($item_type,$item_id,$with_trashed = false) {
        switch($item_type) {
            case 'question':
                return self::getQuestion($item_id,$with_trashed);
            case 'newsletter_post':
                return self::getNewsletterPost($item_id,$with_trashed);
            case 'answer':
                return self::getAnswer($item_id,$with_trashed);
            case 'comment':
                return self::getComment($item_id,$with_trashed);
            case 'minblog':
                return self::getMinBlog($item_id,$with_trashed);
            case 'share';
                return self::getShare($item_id,$with_trashed);
            case 'post':
                return self::getPost($item_id,$with_trashed);
			default:
				throw new App\Exceptions\ProgramException("严重错误：未定义的 item_type morph `{$item_type}'");
        }
    }
    public static function getShare($item_id,$with_trashed=false) {
        if ($with_trashed) {
            return Share::withTrashed()->find($item_id);
        }
        return Share::find($item_id);
    }
    public static function getPost($item_id,$with_trashed=false) {
        if ($with_trashed) {
            return Post::withTrashed()->find($item_id);
        }
        return Post::find($item_id);
    }

    public static function getMinBlog($item_id,$with_trashed=false) {
        if ($with_trashed) {
            return MinBlog::withTrashed()->find($item_id);
        }
        return MinBlog::find($item_id);
    }
    public static function getComment($item_id,$with_trashed=false) {
        if ($with_trashed) {
            return Comment::withTrashed()->find($item_id);
        }
        return Comment::find($item_id);
    }

    public static function getAnswer($item_id,$with_trashed=false) {
        if ($with_trashed) {
            return Answer::withTrashed()->find($item_id);
        }
        return Answer::find($item_id);
    }
    
    public static function getQuestion($item_id,$with_trashed=false) {
        if ($with_trashed) {
            return Question::withTrashed()->find($item_id);
        }
        return Question::find($item_id);
    }

    public static function getNewsletterPost($item_id,$with_trashed=false) {
        if ($with_trashed) {
            return NewsletterPost::withTrashed()->find($item_id);
        }
        return NewsletterPost::find($item_id);
    }

    public static function getBelongClubId($item_type,$item_id) {

        $item = self::get($item_type,$item_id);
        switch($item_type) {
            case 'share':
            case 'post':
            case 'question':
                return $item->club_id;
            case 'newsletter_post':
                return $item->newsletter->club_id;
            case 'answer':
                if ($item->club_id) {
                    return $item->club_id;
                }
                return $item->question->club_id;
            case 'comment':
                return $item->club_id;

            case 'minblog':
                return '';
        }
    }

    public static function getQuestionByCache($id) {
        $question = Cache::remember('question_'.$id,60,function() use ($id){
            // Log::Debug('debug,没有命中缓存:recommend_clubs_'.$category_id);
            return Question::find($id);
        });
        return $question;
    }
 }
