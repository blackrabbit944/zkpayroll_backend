<?php
use Illuminate\Support\Facades\Route;

if (!function_exists('base64url_decode')) {
    /**
     * Decode data from Base64URL
     * @param string $data
     * @param boolean $strict
     * @return boolean|string
     */
    function base64url_decode($data, $strict = false)
    {
      // Convert Base64URL to Base64 by replacing “-” with “+” and “_” with “/”
      $b64 = strtr($data, '-_', '+/');

      // Decode Base64 string and return the original data
      return base64_decode($b64, $strict);
    }
}

if (!function_exists('random_color')) {
    /**
     * get Random Color in 0x
     * @return string
     */
    function random_color() {
        return sprintf('%06X', mt_rand(0, 0xFFFFFF));
    }
}

if (!function_exists('base64url_encode')) {
    /**
     * Encode data to Base64URL
     * @param string $data
     * @return boolean|string
     */
    function base64url_encode($data)
    {
      // First of all you should encode $data to Base64 string
      $b64 = base64_encode($data);

      // Make sure you get a valid result, otherwise, return FALSE, as the base64_encode() function do
      if ($b64 === false) {
        return false;
      }

      // Convert Base64 to Base64URL by replacing “+” with “-” and “/” with “_”
      $url = strtr($b64, '+/', '-_');

      // Remove padding character from the end of line and return the Base64URL result
      return rtrim($url, '=');
    }
}

if (!function_exists('mysql_timestamp')) {
    /**
     * create mysql_timestamp from php
     * @param phptime $phptime
     * @return mysql_timestamp
     */
    function mysql_timestamp($phptime)
    {
        return date('Y-m-d H:i:s',(int)$phptime);
    }
}

if (!function_exists('get_page_size')) {
    /**
     * get page size from request
     * @param request $request
     * @return int
     */
    function get_page_size($request)
    {
        if ($request->input('page_size')) {
            $page_size = (int)$request->input('page_size');
        }else {
            $page_size = 20;
        }
        return $page_size;
    }
}

if (!function_exists('get_page')) {
    /**
     * get page from request
     * @param request $request
     * @return int
     */
    function get_page($request)
    {
        if ($request->input('page')) {
            $page = (int)$request->input('page');
        }else {
            $page = 1;
        }
        return $page;
    }
}



if (!function_exists('get_order_by')) {
    /**
     * get orer_by from request
     * @param request $request
     * @return int
     */
    function get_order_by($request,$default_order_by = 'create_time_desc')
    {
        if ($request->input('order_by')) {
            $order_by_str = $request->input('order_by');
        }else {
            $order_by_str = $default_order_by;
        }

        $arr = explode('_',$order_by_str);
        $last = array_pop($arr);

        return [implode('_',$arr),$last];
    }

}
if (!function_exists('get_page_size')) {
    /**
     * get page size from request
     * @param request $request
     * @return int
     */
    function get_page_size($request)
    {
        if ($request->input('page_size') && $request->input('page_size') <= 40) {
            $page_size = (int)$request->input('page_size');
        }else {
            $page_size = 20;
        }
        return $page_size;
    }
}

if (!function_exists('is_row_change')) {
    /**
     * get page from request
     * @param request $request
     * @return int
     */
    function is_row_change($before,$after)
    {

        // $diff = array_diff($before, $after);

        $diff = array_diff(array_map('serialize',$before), array_map('serialize',$after)); 
        if (isset($diff['update_time'])) {
            unset($diff['update_time']);
        }
        
        return (count($diff) > 0);
    }
}


if (!function_exists('get_current_controller_name')) {
    /**
     * 获取当前控制器名
     *
     * @return string
     */
    function get_current_controller_name()
    {
        return get_current_action()['controller'];
    }
}

if (!function_exists('get_current_method_name')) {
    /**
     * 获取当前方法名
     *
     * @return string
     */
    function get_current_method_name()
    {
        return get_current_action()['method'];
    }
}

if (!function_exists('get_current_action')) {
    /**
     * 获取当前action
     *
     * @return string
     */
    function get_current_action()
    {

        $action = app('Illuminate\Http\Request')->route()[1]['uses'];
        list($class, $method) = explode('@', $action);
        return ['controller' => $class, 'method' => $method];
    }
}

if (!function_exists('get_current_login_user')) {
    /**
     * 获取当前action
     *
     * @return string
     */
    function get_current_login_user()
    {
        try{
            $user = auth('api')->user();
        }catch(Exception $e) {
            $user = null;
        }
        return $user;
    }
}

if (!function_exists('mtime')) {
    /**
     * 获得当前的微秒数字 
     */
    function mtime() {
        $t = gettimeofday();
        return $t['sec'] * 1000000 + $t['usec'];
    }
}

if (!function_exists('ymdhism')) {
    /**
     * 返回一个时间，格式为 
     * yymmddhhiissmmmmmm
     */
    function ymdhism() {
        $t = mtime() % 1000000;
        $ret = sprintf('%s%06d', date('ymdHis'), $t);
        return $ret;
    }
}

if (!function_exists('user_ip')) {

    /**
     * 获取当前用户的IP地址，适配cloudflare
     *
     * @return string
     */
    function user_ip()
    {
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            return $_SERVER["HTTP_CF_CONNECTING_IP"];
        }else {
            return request()->ip();
        }
    }
    
}



if ( ! function_exists('config_path'))
{
    /**
     * Get the configuration path.
     *
     * @param  string $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}