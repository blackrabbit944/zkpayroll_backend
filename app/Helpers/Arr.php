<?php
namespace App\Helpers;

/**
 * 
 */
class Arr
{


    /**
    * 根据指定的键对数组排序
    *
    * 用法：
    * @code php
    * $rows = array(
    *            array('id' => 1, 'value' => '1-1', 'parent' => 1),
    *            array('id' => 2, 'value' => '2-1', 'parent' => 1),
    *            array('id' => 3, 'value' => '3-1', 'parent' => 1),
    *            array('id' => 4, 'value' => '4-1', 'parent' => 2),
    *            array('id' => 5, 'value' => '5-1', 'parent' => 2),
    *            array('id' => 6, 'value' => '6-1', 'parent' => 3),
    * );
    *
    * $rows = Helper_Array::sortByCol($rows, 'id', SORT_DESC);
    * dump($rows);
    * // 输出结果为：
    * // array(
    * //          array('id' => 6, 'value' => '6-1', 'parent' => 3),
    * //          array('id' => 5, 'value' => '5-1', 'parent' => 2),
    * //          array('id' => 4, 'value' => '4-1', 'parent' => 2),
    * //          array('id' => 3, 'value' => '3-1', 'parent' => 1),
    * //          array('id' => 2, 'value' => '2-1', 'parent' => 1),
    * //          array('id' => 1, 'value' => '1-1', 'parent' => 1),
    * // )
    * @endcode
    *
    * @param array $array 要排序的数组
    * @param string $keyname 排序的键
    * @param int $dir 排序方向
    *
    * @return array 排序后的数组
    */
    static function sortByCol($array, $keyname, $dir = SORT_ASC)
    {
       return self::sortByMultiCols($array, array($keyname => $dir));
    }

    /**
    * 将一个二维数组按照多个列进行排序，类似 SQL 语句中的 ORDER BY
    *
    * 用法：
    * @code php
    * $rows = Helper_Array::sortByMultiCols($rows, array(
    *            'parent' => SORT_ASC,
    *            'name' => SORT_DESC,
    * ));
    * @endcode
    *
    * @param array $rowset 要排序的数组
    * @param array $args 排序的键
    *
    * @return array 排序后的数组
    */
    static function sortByMultiCols($rowset, $args)
    {
       $sortArray = array();
       $sortRule = '';
       foreach ($args as $sortField => $sortDir)
       {
           foreach ($rowset as $offset => $row)
           {
               $sortArray[$sortField][$offset] = $row[$sortField];
           }
           $sortRule .= '$sortArray[\'' . $sortField . '\'], ' . $sortDir . ', ';
       }
       if (empty($sortArray) || empty($sortRule)) { return $rowset; }
       eval('array_multisort(' . $sortRule . '$rowset);');
       return $rowset;
    }

    /**
     * 从数组中删除空白的元素（包括只有空白字符的元素）
     *
     * 用法：
     * @code php
     * $arr = array('', 'test', '   ');
     * Helper_Array::removeEmpty($arr);
     *
     * dump($arr);
     *   // 输出结果中将只有 'test'
     * @endcode
     *
     * @param array $arr 要处理的数组
     * @param boolean $trim 是否对数组元素调用 trim 函数
     */
    static function removeEmpty(& $arr, $trim = true)
    {
        foreach ($arr as $key => $value)
        {
            if (is_array($value))
            {
                self::removeEmpty($arr[$key]);
            }
            else
            {
                $value = trim($value);
                if ($value == '')
                {
                    unset($arr[$key]);
                }
                elseif ($trim)
                {
                    $arr[$key] = $value;
                }
            }
        }
        return $arr;
    }

     /**
     * 从2维数组中按照某个key分组
     *
     * 用法：
     * @code php
     * $array = array(
      *
      *     '0' =>  array(
      *                 'key'   =>  1,
      *                 'value' =>  1,
      *             ),
      *     '1' =>  array(
      *                 'key'   =>  2,
      *                 'value' =>  1,
      *             ),
      *     '2' =>  array(
      *                 'key'   =>  1,
      *                 'value' =>  1,
      *             ),
      *
      * );
     * Helper_Array::MakeGroup($array,'key');
     *
     * dump($arr);
     *
     *
     * @endcode
     *
     * @param array   $array 要处理的数组
     * @param string  $key  是否对数组分组调用 KEY
     */

    static function MakeGroup($array,$key){
        $newarray = array();
        foreach($array as $value) {
            $newarray[strtoupper($value[$key])][] = $value;
        }
        return $newarray;
    }
    
    static function removeSame($array) {
        return array_flip(array_flip($array));
    }
    
    static function removeAttr($array,$string) {
        $a = explode(',', $string);
        $b = array();
        
        foreach($array as $key => $value) {
            foreach($a as $akey) {
                $b[$key][$akey] = $value[$akey];
            }
        }
        return $b;
    }
    
    static function removeArray($array1,$array2) {
        
        //从array1中剔除array2
        
        foreach($array1 as $key => $value) {
            if (in_array($value,$array2)) {
                unset($array1[$key]);
            }
        }
        return $array1;
        
    }

    
    static function mustHave($array1,$array2) {
        foreach($array2 as $key) {
            if (!isset($array1[$key])) {
                return false;
            }
        }
        return true;
    }


        
    static function removeKeys($array,$keys_arr) {
        foreach($array as $key => $value) {
            if (in_array($key, $keys_arr)) {
                unset($array[$key]);
            }
        }
        return $array;
    }

    static function formatData($data,$allows) {
        $output = $data;
        foreach($allows as $key => $value) {
            if (isset($data[$key])) {
                switch ($value) {
                    case 'string':
                        $output[$key] = (string)$data[$key];
                        break;
                    case 'int':
                        $output[$key] = (int)$data[$key];
                        break;
                    case 'bool':
                        if ((int)$data[$key]) {
                            $output[$key] = 1;
                        }else {
                            $output[$key] = 0;
                        }
                        break;
                    case 'float':
                        $output[$key] = (float)$data[$key];
                        break;

                    case 'draft':
                        $draft_data = Helper_Draft::isDraftEmpty($data[$key]);
                        if ($draft_data == true) {
                            $output[$key] =  '';
                        }else {
                            $output[$key] =  $data[$key];
                        }
                        break;
                        
                    default:
                        $output[$key] = $data[$key];
                        break;
                }
            }
        }
        return $output;
    }

    static public function keepKeys($data,$keep_keys) {
        $formated_keep_keys = self::formatKeepKeys($keep_keys);
        $formated = self::formatKey($data,$formated_keep_keys);
        return $formated;
    }

    static public function formatKey($data,$formated_keep_keys) {

        $formated = [];
        if (is_array($data)) {
          foreach($data as $key => $value) {
              $is_allowed = self::isKeyInAllow($formated_keep_keys,$key);
              if ($is_allowed) {
                  if (is_array($value)) {
                      if (is_array($formated_keep_keys[$key])) {
                          $formated[$key] = self::formatKey($value,$formated_keep_keys[$key]);
                      }else {
                          $formated[$key] = $value;
                      }
                  }else {
                      $formated[$key] = $value;
                  }
              }
          }
        }
        return $formated;
    }

    static function formatKeepKeys($keep_keys) {
        $formated = [];
        foreach($keep_keys as $key => $value) {
            if (is_array($value)) {
                $formated[$key] = self::formatKeepKeys($value);
            }else {
                $formated[$value] = 'all';
            }
        }
        return $formated;
    }

    static function isKeyInAllow($formated_keep_keys,$key_list) {

        if (!is_array($key_list)) {
            $key_list = [$key_list];
        }

        $check = $formated_keep_keys;
        $is_find = true;
        foreach($key_list as $key) {
            if (isset($check[$key])) {
                $check == $check[$key];
            }else {
                $is_find = false;
            }
        }

        if ($is_find) {
            return true;
        }else {
            return false;
        }
        
    }
}