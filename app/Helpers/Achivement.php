<?php

namespace App\Helpers;

use App\Models\Share;

use App\Models\Answer;
use App\Models\CoinLog;
use App\Models\Invitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 奖励规则：
 * 邀请了 1/5/10 个用户进入社区
 * 回答了 1/10/50/100 个问题
 * 提出了 1/10/50/100 个问题
 * 分享了 1/10/50/100 个链接
 * 获得 10/100/1000/10000 个赞同
 */

/**
 * 
 */
class Achivement
{
    /// 获取目前可用的 Achivement 名字
    static public function getAchivementsConfig() {
        return [
//          '奖励名字' => ['thres' => 阈值, 'coin' => 奖励金币数 ],
            'InviteUserA' => ['thres' => 1, 'coin' => 30],
            'InviteUserB' => ['thres' => 5, 'coin' => 200],
            'InviteUserC' => ['thres' => 10, 'coin' => 1000],
            'AddAnswerA' => ['thres' => 1, 'coin' => 10],
            'AddAnswerB' => ['thres' => 10, 'coin' => 200],
            'AddAnswerC' => ['thres' => 50, 'coin' => 1000],
            'AddAnswerD' => ['thres' => 100, 'coin' => 2000],
            'AddQuestionA' => ['thres' => 1, 'coin' => 10],
            'AddQuestionB' => ['thres' => 10, 'coin' => 200],
            'AddQuestionC' => ['thres' => 50, 'coin' => 1000],
            'AddQuestionD' => ['thres' => 100, 'coin' => 2000],
            'AddShareA' => ['thres' => 1, 'coin' => 5],
            'AddShareB' => ['thres' => 10, 'coin' => 100],
            'AddShareC' => ['thres' => 50, 'coin' => 500],
            'AddShareD' => ['thres' => 100, 'coin' => 1000],
            // 'BeVoteUpA' => ['thres' => 10, 'coin' => 100],
            // 'BeVoteUpB' => ['thres' => 100, 'coin' => 100],
            // 'BeVoteUpC' => ['thres' => 1000, 'coin' => 100],
            // 'BeVoteUpD' => ['thres' => 10000, 'coin' => 100],
        ];
    }

    static public function nameIsInviteUser($name) :bool {
        return strpos($name, 'InviteUser')  === 0;
    }
    static public function nameIsAddAnswer($name) {
        return strpos($name, 'AddAnswer')  === 0;
    }
    static public function nameIsAddQuestion($name) {
        return strpos($name, 'AddQuestion')  === 0;
    }
    static public function nameIsAddShare($name) {
        return strpos($name, 'AddShare')  === 0;
    }
    static public function nameIsBeVoteUp($name) {
        return strpos($name, 'BeVoteUp')  === 0;
    }

    static public function getAchivementDesc($name) {
        $a = self::getAchivementsConfig()[$name] ?? null;
        if (!$a) {
            return "无此成就 `{$name}'";
        }

        if (self::nameIsInviteUser($name)) {
            return \sprintf(__("邀请了 %d 个用户"), $a['thres']);
        } else if (self::nameIsAddAnswer($name)) {
            return \sprintf(__("回答了 %d 个问题"), $a['thres']);
        } else if (self::nameIsAddQuestion($name)) {
            return \sprintf(__("添加了 %d 个问题"), $a['thres']);
        } else if (self::nameIsAddShare($name)) {
            return \sprintf(__("分享了 %d 个链接"), $a['thres']);
        } else if (self::nameIsBeVoteUp($name)) {
            return \sprintf(__("尚不支持 BeVoteUp 类型的奖励"));
        } else {
            throw new \Exception("不支持此种成就名字 `{$name}'");
        }
    }

    static public function getAchivement($name) {
        $a = self::getAchivementsConfig()[$name] ?? null;
        if (!$a) {
            return $a;
        }

        $a['desc'] = self::getAchivementDesc($name);
        $a['name'] = $name;

        return $a;
    }

    /// 邀请 n 个用户进入社区
    static public function verifyFactory($name) {
        $thres = self::getAchivementsConfig()[$name]['thres'] ?? PHP_INT_MAX;

        if (self::nameIsInviteUser($name)) {
            /// 返回一个函数，这个函数接受 user_id 参数，判断这个用户是否达到了领取 achivement 的资格
            return function($user_id) use ($name, $thres) :bool {
                $cnt = \App\Models\Invitation::where("user_id", $user_id)->whereNotNull('use_user_id')->count();
                Log::info("用户 {$user_id} 邀请了 {$cnt} 个用户，当前成就 {$name} 的邀请阈值是 {$thres}");

                return $cnt >= $thres;
            };
        } else if (self::nameIsAddAnswer($name)) {
            return function($user_id) use($name, $thres) :bool {
                $cnt = \App\Models\Answer::where("user_id", $user_id)->count();
                Log::info("用户 {$user_id} 回答了 {$cnt} 个问题，当前成就 {$name} 的阈值是 {$thres}");

                return $cnt >= $thres;
            };
        } else if (self::nameIsAddQuestion($name)) {
            return function($user_id) use($name, $thres) :bool {
                $cnt = \App\Models\Question::where("user_id", $user_id)->count();
                Log::info("用户 {$user_id} 添加了 {$cnt} 个问题，当前成就 {$name} 的阈值是 {$thres}");

                return $cnt >= $thres;
            };
        } else if (self::nameIsAddShare($name)) {
            return function($user_id) use($name, $thres) :bool {
                $cnt = \App\Models\Share::where("user_id", $user_id)->count();
                Log::info("用户 {$user_id} 添加了 {$cnt} 个 Share，当前成就 {$name} 的阈值是 {$thres}");

                return $cnt >= $thres;
            };
        } else if (self::nameIsBeVoteUp($name)) {
            throw new \Exception("尚不支持 BeVoteUp 类型的奖励");
        } else {
            throw new \Exception("不支持此种成就名字 `{$name}'");
        }
    }


    /**
     * 发放指定类型的成就奖励
     */
    static public function send($name, $user_id) {    
        $ret = self::sendCoin($name, $user_id);
    
        if ($ret == true) {
            /// 2. 将奖励更新为“已发放”
            $cond = [
                'name' => $name,
                'user_id' => $user_id,
                'send_time' => 0,
            ];
            $data = [
                'send_time' => time(),
            ];
            $updateRet = \App\Models\Achivement::where($cond)->update($data);

            Log::debug("将成就奖励更新为“已发送”", [$cond, $data, $updateRet]);
        }

        return $ret;
    }

    /**
     * 发放成就的奖励
     */
    static protected function sendCoin($name, $user_id) {
    
        /// 对应的奖励金额：  成就名字 => [金币数]
        $bonus = [
            'InviteUserA' => [100],
            'InviteUserB' => [100],
            'InviteUserC' => [100],
        ];
        $aConfig = self::getAchivement($name);
        if (!$aConfig) {
            throw new \App\Exceptions\ApiException(\sprintf(__("不存在名为 `%s' 的成就"), $name));
        }
        
        $bonus_coin_amount = $aConfig['coin'];
        
        /// 1. 检查用户是否能够获得此奖励
        $verifyFunc = self::getVerifyFunc($name);
        
        if ($verifyFunc($user_id) == false) {
            throw new \App\Exceptions\ApiException(__("尚不能获得此奖励"));
        }

        /// 2. 发放奖励
        DB::beginTransaction();
        $ret = false;
        do {
            $ret = self::isShouldSend($name, $user_id);
            if (!$ret) {
                break;
            }

            /// 2.3 发放金币奖励
            $achivement = \App\Models\Achivement::where(['user_id' => $user_id, 'name' => $name])->first();
            if (!$achivement) {
                Log::error("找不到用户成就记录", $user_id, $name);
                $ret = false;
                break;
            }

            $data = [
                'user_id' => $user_id,
                'amount' => $bonus_coin_amount,
                'verb' => 'got_achivement',
                'verb_id' => $achivement->achivement_id,
            ];
            $ret = CoinLog::add($data);
            if (!$ret) {
                break;
            }
        }
        while(0);

        if ($ret) {
            DB::commit();
        } else {
            Log::warning("事务回滚了", [$ret]);
            DB::rollback();
        }

        return $ret;
    }

    /**
     * 检查是否应该向用户发送成就奖励。
     * 此方法会检查：
     * 1. 用户是否已经获得成就
     * 2. 用户是否已经获得过成就奖励 (send_time != 0)
     */
    static public function isShouldSend($name, $user_id) :bool {
        /// 2.2 检查成就奖励是否已经发放过
        $cond = [
            'name' => $name,
            'user_id' => $user_id,
        ];
        $dup = \App\Models\Achivement::lockForUpdate()->withTrashed()->where($cond)->first();

        if ($dup && $dup->send_time != 0) {
            Log::info("成就奖励已经发放过了", $cond);
            return false;
        }

        $dup = \App\Models\Achivement::lockForUpdate()->where($cond)->first();
        if (!$dup) {
            Log::info("用户尚未获得成就奖励", $cond);
            return false;
        }

        Log::debug("可以向用户发送成就奖励", $cond);
        return true;
    }

    static public function getVerifyFunc($name) {
        return self::verifyFactory($name);
    }

    /**
     * 向用户发放指定的成就（但不发放奖励）
     * 如果用户目前还未达到获取奖励的条件，则不发放
     */
    static public function tryGive($name, $user_id) {
        /// 1. 检查用户是否应该获得这个奖励
        $is = self::verifyFactory($name)($user_id);
        if (!$is) {
            Log::info("用户尚未达到获取此成就的条件", [ $user_id, $name]);
            return false;
        }

        /// 2. 发放奖励
        Log::info("准备给用户发放成就",  [$name, $user_id]);
        return self::give($name, $user_id);
    }

    static public function give($name, $user_id) {
        DB::beginTransaction();
        $ret = false;
        do {
            /// 1. 检查是否已经发放过这个成就
            $cond = [
                'user_id' => $user_id,
                'name' => $name,
            ];
            $old = \App\Models\Achivement::lockForUpdate()->withTrashed()->where($cond)->first();
            if ($old) {
                Log::info("已经为用户发放过奖励了，直接返回成功", $cond);
                $ret = true;
                break;
            }

            /// 2. 发放奖励
            $data = [
                'user_id' => $user_id,
                'name' => $name,
                'send_time' => 0,
            ];
            $ret = \App\Models\Achivement::create($data);
        } 
        while (0);

        if ($ret) {
            DB::commit();
        } else {
            DB::rollback();
        }

        return $ret;
    }


}