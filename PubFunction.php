<?php
namespace xiaojun\help;


/**
 * 公用方法集合
 */
class PubFunction
{
    //java  hashcode 
    public static function hashCode($s)
    {
         $len = strlen($s);
         $hash = 0;
         for($i=0; $i<$len; $i++)
         {
              //一定要转成整型
              $hash = (int)($hash*31 + ord($s[$i]));
              //64bit下判断符号位
              if(($hash & 0x80000000) == 0)
              {
                   //正数取前31位即可
                   $hash &= 0x7fffffff;
              }
              else
              {
                   //负数取前31位后要根据最小负数值转换下
                   $hash = ($hash & 0x7fffffff) - 2147483648;
              }
         }
         return $hash;
    }
    
    //unicode 
    public static function unicodeString($str, $encoding=null) 
    {

        return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/u', create_function('$match', 'return mb_convert_encoding(pack("H*", $match[1]), "utf-8", "UTF-16BE");'), $str);

    }

}
