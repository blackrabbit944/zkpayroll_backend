<?php
namespace App\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Log;

class JiandaLangScope implements Scope {

    protected $isWithLang = false;
    protected $withLangName = null;

    public function apply(Builder $builder, Model $model) {
//        Log::info("应用了 JiandaLang Scope", [$this->isWithLang, $this->withLangName]);


        if ($this->isWithLang) {
            $withLang = $this->withLangName;
            if (!$withLang) {
                $withLang = auth('api')->user()->lang ?? "";
            }
            if (!$withLang) {
                $withLang = 'zh';
            }

            return $builder->where($model->getLangColumn(), $withLang);
        } else {
            return $builder;
        }
        //return $builder->where("lang", "zh");
    }

    public function extend(Builder $builder) {
//        Log::info("简答国际化：extend() 被调用了");
        $this->addWithLang($builder);
        $this->addWithoutLang($builder);
    }

    public function addWithLang(Builder $builder) {
//        Log::info("简答国际化：addWithLang() 被调用了");

        $builder->macro('withLang', function (Builder $builder, $lang = "") {
//            Log::info("简答国际化：withLang() 被调用了");

            $this->isWithLang = true;
            $this->withLangName = $lang;
            return $builder;
            //return $builder->withoutGlobalScope($this);
        });

        //return $builder->where("lang", "zh");
    }

    public function addWithoutLang(Builder $builder) {
        $builder->macro('withoutLang', function(Builder $builder) {
            $this->isWithLang = false;
            return $builder;
        });
    }



}
