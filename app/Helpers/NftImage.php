<?php
namespace App\Helpers;

class NftImage
{

    /**
     * 根据预设宽度让文字自动换行
     * @param int $fontsize 字体大小
     * @param string $ttfpath 字体名称
     * @param string $str 字符串
     * @param int $width 预设宽度
     * @param int $fontangle 角度
     * @param string $charset 编码
     * @return string $_string  字符串
     */
    public static function autoWrap($fontsize, $ttfpath, $str, $width, $fontangle = 0, $charset = 'utf-8')
    {
        $_string = "";
        $_width = 0;
        $temp = self::chararray($str, $charset);
        foreach ($temp[0] as $v) {
            $w = self::charWidth($fontsize, $fontangle, $v, $ttfpath);
            $_width += intval($w);
            if (($_width > $width) && ($v !== "")) {
                $_string .= PHP_EOL;
                $_width = 0;
            }
            $_string .= $v;
        }

        return $_string;
    }

    /**
     * 返回一个字符的数组
     *
     * @param string $str 文字
     * @param string $charset 字符编码
     * @return array $match   返回一个字符的数组
     */
    public static function charArray($str, $charset = "utf-8")
    {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);

        return $match;
    }

    /**
     * 返回一个字符串在图片中所占的宽度
     * @param int $fontsize 字体大小
     * @param int $fontangle 角度
     * @param string $ttfpath 字体文件
     * @param string $char 字符
     * @return int $width
     */
    protected static function charWidth($fontsize, $fontangle, $char, $ttfpath)
    {
        $box = @imagettfbbox($fontsize, $fontangle, $ttfpath, $char);
        $width = max($box[2], $box[4]) - min($box[0], $box[6]);

        return $width;
    }


}