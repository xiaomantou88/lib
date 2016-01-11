<?php
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


    /**
     * @param $toUser  邮件发送对象,如果是多个对象,格式为array('user1@aa.com','user2@aa.com')
     * @param $Subject 邮件主题
     * @param $body    邮件内容
     * @param string $attachment   附件路径
     * 默认使用 alert@asiainnovations.com 发送邮件
     */
    public static function sendMail($toUser,$Subject,$body,$attachment='',$html=false){
        Yii::app()->mailer->Host = 'smtp.exmail.qq.com';
        Yii::app()->mailer->IsSMTP();
		Yii::app()->mailer->isHTML($html);
        Yii::app()->mailer->CharSet = 'UTF-8';
        Yii::app()->mailer->SMTPAuth = true;
        Yii::app()->mailer->Username  = 'alert@asiainnovations.com';
        Yii::app()->mailer->Password  = '1ddyyK03';
        Yii::app()->mailer->From = 'alert@asiainnovations.com';
        Yii::app()->mailer->FromName = 'Robot';
        if(is_array($toUser)){
            foreach($toUser as $v){
                Yii::app()->mailer->AddAddress($v);
            }
        }
        else{
            Yii::app()->mailer->AddAddress($toUser);
        }
        Yii::app()->mailer->Subject = $Subject;
        Yii::app()->mailer->Body = $body;
        if($attachment){
            if(is_array($attachment)){
                foreach($attachment as $file){
                    Yii::app()->mailer->AddAttachment($file);
                }
            }
            else{
                Yii::app()->mailer->AddAttachment($attachment);
            }
        }
        Yii::app()->mailer->Send();
    }
}
