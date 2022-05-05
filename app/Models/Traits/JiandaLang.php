<?php
namespace App\Models\Traits;

use Illuminate\Support\Facades\Log;
use App\Scopes\JiandaLangScope;
use Illuminate\Database\Eloquent\Model;

/**
 * 用法说明：
 * 
 * 在需要根据语言进行查询的 Model 中，使用
 *  use JiandaLang;
 * 
 * 之后，在查询的时候，调用 withLang() 方法，即可获得过滤了语言的结果，例如
 *  Question::withLang()->where(...)->get();
 *  Question::withLang()->where()->first();
 * 即可查询到当前用户语言的内容。
 * 如果用户没有指定语言信息，则使用 zh 作为默认语言。
 * 
 * 此外，withLang() 还支持传入 $lang 参数。当传入了 lang 参数时，将根据 lang 指定的语言进行过滤，例如：
 *  Question::withLang('jp')->get();        /// 查询 jp 语言的结果
 *  Question::withLang('')->get();          /// 根据当前用户的语言，自动设定 lang 参数
 */
trait JiandaLang {

    public static function bootJiandaLang() {
//        Log::info("Jianda 国际化：bootJiandaLang 了");                                          
        static::addGlobalScope(new JiandaLangScope);
    }

    public function initializeJiandaLang()
    {
        //Log::info("Jianda 国际化: initializeJiandaLang() 了");
        if (! isset($this->casts[$this->getLangColumn()])) {
            $this->casts[$this->getLangColumn()] = 'string';
        }
    }


    public function getLangColumn()
    {
        return defined('static::LANG') ? static::LANG : 'lang';
    }


    public function autoFillLang() {
        
        $c = $this->getLangColumn();
        if (isset($this->$c) && $this->$c) {
            /// 已经有语言信息了，不进行任何处理
            return $this;
        }

        $lang = '';
        switch (get_class($this)) {
            case 'App\Models\Club':
            case 'App\Models\MinBlog':    
                $user = \App\Models\User::find($this->user_id);
                $lang = $user->lang;
                break;

            case 'App\Models\Question':
            case 'App\Models\Share':
            case 'App\Models\Post':
            case 'App\Models\Feed':
                $club = \App\Models\Club::find($this->club_id);
                $lang = $club->lang;
                break;

            case 'App\Models\Answer':
                $question = \App\Models\Question::find($this->question_id);
                $lang = $question->lang;
                break;

            default:
                throw new \Exception(\sprintf("无法对类 %s 自动填充语言字段", get_class($this)));
                break;
        }

        if ($lang == '') {
            Log::warning("由于无法找到 lang，直接使用默认语言", [get_class($this), config('misc.default_lang')]);
            $lang = config('misc.default_lang');
        }

        $this->$c = $lang;
    

        return $this;
    }
    
    public static function getWithLangName() {
        $user = auth('api')->user();
        if ($user) {
            return $user->lang;
        } else {
            return '';
        }
    }

}
