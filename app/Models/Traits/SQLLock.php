<?php 
namespace App\Models\Traits;

use Illuminate\Support\Facades\DB;


trait SQLLock{
    static public function mustLock($lock_name) {
        return self::mustGetLock($lock_name);
    }
    
    static public function mustGetLock($lock_name) {
        $lock_qs = DB::connection()->getPdo()->quote($lock_name);
        
        $ret = DB::select("SELECT GET_LOCK($lock_qs, 10) AS res");
        if (!isset($ret[0]) || $ret[0]->res != 1) {
            throw new \Exception(sprintf("获取锁(%s)失败", $lock_name));
        }
    }
    
    static public function unlock($lock_name) {
        $lock_qs = DB::connection()->getPdo()->quote($lock_name);
        $ret = DB::statement("SELECT RELEASE_LOCK($lock_qs)");
    }
}
