<?php

namespace App\Helpers;

class Bc {

    static function bcmin($n1, $n2, $scale = null) {
        if ($scale !== null) {
            if (bccomp($n1, $n2, $scale) < 0) {
                return $n1;
            }
            else {
                return $n2;
            }
        }
        else {
            if (bccomp($n1, $n2) < 0) {
                return $n1;
            }
            else {
                return $n2;
            }
        }
        
    }

    static function bcmax($n1, $n2, $scale = null) {
        if ($scale !== null) {
            if (bccomp($n1, $n2, $scale) > 0) {
                return $n1;
            }
            else {
                return $n2;
            }
        }
        else {
            if (bccomp($n1, $n2) > 0) {
                return $n1;
            }
            else {
                return $n2;
            }
        }
    }

    static function bcabs($n, $scale = null) {
        if ($scale !== null) {
            if (bccomp($n, '0', $scale) >= 0) {
                return $n;
            }
            else {
                return bcmul($n, '-1', $scale);
            }
        }
        else {
            if (bccomp($n, '0') >= 0) {
                return $n;
            }
            else {
                return bcmul($n, '-1');
            }
        }
    }



    /**
     * @param number        要判断小数位数的数字
     * @param max_decimals  最大可判断的小数位数限制，避免输入太大耗时过长
     */
    static function bcdecimals($number, $max_decimals = 1000) {
        $decimals = 0;

        $len = strlen($number);

        while (1) {
            $number_rounded = bcadd($number, 0, $decimals);
            if (bccomp($number, $number_rounded, $len) == 0) {
                break;
            }
            
            $decimals++;
        }
        
        return $decimals;
    }


    static function bcround($number, $precision = 0)
    {
        if (strpos($number, '.') !== false) {
            if ($number[0] != '-') return bcadd($number, '0.' . str_repeat('0', $precision) . '5', $precision);
            return bcsub($number, '0.' . str_repeat('0', $precision) . '5', $precision);
        }
        return $number;
    }

    static function bcceil($_number, $precision = 0) {
        $number = $_number;
        
        $delta = bcdiv('9', bcpow(10, $precision + 1), $precision + 1);
        $number = bcadd($number, $delta, $precision + 1);
        $number = bcadd($number, 0, $precision);
        
        LOGD("bcceil 处理结果：输入 `%s'，精度 %d，输出 `%s'", $_number, $precision, $number);
        
        return $number;
    }

    static function bcfloor($_number, $precision = 0) {
        $number = bcsub($_number, 0, $precision);
        
        LOGD("bcfloor 处理结果：输入 `%s'，精度 %d，输出 `%s'", $_number, $precision, $number);
        
        return $number;
    }


    /**
     * 完全精度浮点数转数字方法，转换后的字符串不会有科学计数法的表示，而是完全使用普通计数法表示所有的小数位数
     */
    static function convertFloat($_floatAsString)
    {
        if ($_floatAsString === 0) {
            return '0';
        }
        
        $floatAsString = $_floatAsString;
        
        if (is_string($floatAsString)) {
            $floatAsString = str_replace('e', 'E', $floatAsString);
        }
        
        if (is_string($floatAsString) && stripos($floatAsString, 'E') === false) {
            LOGD("精确浮点数转换（A情形），输入 `%s'(%s), 原样输出", serialize($_floatAsString), var_export($_floatAsString, true));
            return $floatAsString;
        }
        
        $norm = strval(floatval($floatAsString));

        if (($e = strrchr($norm, 'E')) === false && is_float($floatAsString)) {
            LOGD("精确浮点数转换（B情形），输入 `%s'(%s), 输出 `%s'", serialize($_floatAsString), var_export($_floatAsString, true), serialize($norm));
            return $norm;
        }

        
        $tokens = explode('E', $floatAsString);
        
        if (count($tokens) < 2) {
            LOGW("输入的浮点数字符串不正确，处理结果不可预期，输入的内容是 `%s'", $_floatAsString);
        }
        
        $e = intval($tokens[1]);
        $num = $tokens[0];
        
        $decimals = strlen($num) + abs($e) + 1;

        $ret = bcmul($num, bcpow(10, $e, $decimals), $decimals);
        
        if (strpos($ret, '.') !== FALSE) {
            $ret = rtrim($ret, '0');
            if (substr($ret, -1, 1) == '.') {
                $ret = substr($ret, 0, strlen($ret) - 1);
            }
        }
        
        LOGD("精确浮点数转换（C情形），输入 `%s'(%s), 输出 `%s'", serialize($_floatAsString), var_export($_floatAsString, true), serialize($ret));
        
        return $ret;
    }



    /**
     * 保留一个正实数（不包括科学计数法的数）的 figures_num 位有效数字
     */
    static function significant_figures($positive_number, string $figures_num) {
            $len = strlen($positive_number);
            for ($i = 0; $i < $len; $i++) {
                $c = $positive_number[$i];
                if ($c != '0' && $c != '.') {
                    break;
                }
            }
            
            /// 4.2 从非 0 字符后面第 $figures_num 个字符开始，把后面的字符都设为 0（遇到小数点则跳过）
            $pos = $i;
            $n = 0;
            while ($n < $figures_num && $pos < $len) {
                if ($positive_number[$pos] == '.') {
                }
                else {
                    $n++;
                }
                $pos++;
            }
            
            for ( ; $pos < $len; $pos++) {
                if ($positive_number[$pos] != '.') {
                    $positive_number[$pos] = '0';
                }
            }
            
            return $positive_number;
    }



    /**
     * 按照小数有效数字精度格式化一个数字，保留至少 $decimals 位有效小数
     */
    static function significant_decimals($num, $decimals) {
        $comp = bccomp($num, 0, 20);
        if ($comp < 0) {
            LOGD("significant_decimals 方法不支持处理负数，直接返回传入的数字");
            return $num;
        }
        else if ($comp == 0) {
            return '0';
        }

        
        $tokens = explode('.', $num);
        
        $tokens[0] = ltrim($tokens[0], '0');
        $len = strlen($tokens[0]);
        
        $d = $len + max($decimals, 0);
        
        $ret = self::significant_figures($num, $d);
        
        $ret = rtrim($ret, '0');
        $ret = rtrim($ret, '.');
        
        
        return $ret;
    }


        
    /**
     * 删除一个小数右侧的0
     */
    static function trimr0($number) { 
        if (strpos($number, '.') !== FALSE) {
            $number = rtrim($number, '0');
            if (substr($number, -1, 1) == '.') {
                $number = substr($number, 0, strlen($number) - 1);
            }
        }
        
        return $number;
    }


    /*
    *   换算10进制到16进制
    */
    static function dechex($num) {
        $num = gmp_init($num);
        return gmp_strval($num, 16);

    }

    /*
    *   换算16进制到10进制
    */
    static function hexdec($num) {
        $num = gmp_init($num);
        return gmp_strval($num, 16);

    }

    /*
    *   根据小数点decimals计算token的真实amount
    */
    static function formatTokenAmount($amount,$decimals = 18) {


        $result = bcdiv($amount, bcpow(10, $decimals),$decimals);
        return $result;
    }

    /*
    *   自动截断一个数字的小数点
    */
    static function autoDecimal($num) {

        $num_abs = Bc::bcabs($num);

        if (bccomp($num_abs,10000) >= 0) {
            $decimals = 0;
        }elseif (bccomp($num_abs,1) >= 0) {
            $decimals = 2;
        }elseif (bccomp($num_abs,0.001,8) >= 0) {
            $decimals = 4;
        }elseif(bccomp($num_abs,0.000001,10) >= 0) {
            $decimals = 8;
        }elseif(bccomp($num_abs,0.0000000001,16) >= 0) {
            $decimals = 12;
        }elseif(bccomp($num_abs,0.00000000000001,20) >= 0) {
            $decimals = 16;
        }else {
            $decimals = 32;
        }

        // dump($decimals);
        // dump(Bc::significant_decimals((String)$num,$decimals));
        return Bc::significant_decimals((String)$num,$decimals);
        
    }


    /*
    *   把可能是科学记数法的数字变化为字符串
    */
    static function anyToString($number) {
        
        $double = 32;
        if(false !== stripos($number, "e")){
            $a = explode("e",strtolower($number));
            $bc_number = (string)bcmul($a[0], bcpow(10, $a[1], $double), $double);
        }else {
            $bc_number = (string)$number;
        }

        ///移除结尾的0
        return self::trimr0($bc_number);
    }
}

