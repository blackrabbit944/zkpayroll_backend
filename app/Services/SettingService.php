<?php

namespace App\Services;

use App\Models\Setting;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Exceptions\ProgramException;
use App\Exceptions\ApiException;

use Carbon\Carbon;
use Illuminate\Http\Request;

class SettingService 
{
    static public function save($key,$value) {
        $setting = Setting::where(['key'=>$key])->first();
        if ($setting) {
            $setting->fill(['value'=>$value]);
            $setting->save();
        }else {
            $setting = Setting::create(['key'=>$key,'value'=>$value]);
        }
        return $setting->value;
    }

    static public function getByKey($key) {
        $setting = Setting::where(['key'=>$key])->first();
        if ($setting) {
            return $setting->value;
        }else {
            return null;
        }
    }
}
