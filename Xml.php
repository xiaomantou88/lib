<?php
namespace xiaojun\help\xml;



class Xml
{
    /**
     * XML编码
     * @param mixed $data 数据
     * @param string $root 根节点名
     * @param string $item 数字索引的子节点名
     * @param string $attr 根节点属性
     * @param string $id   数字索引子节点key转换的属性名
     * @param string $encoding 数据编码
     * @return string
     */
    public static function encode($data, $root='xml', $item='item', $attr='', $id='id', $encoding='utf-8')
    {
        $attr['encoding'] = $encoding;
        if(is_array($attr)){
            $_attr = array();
            foreach ($attr as $key => $value) {
                $_attr[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $_attr);
        }
        $attr   = trim($attr);
        $attr   = empty($attr) ? '' : " {$attr}";
        $xml   = "<{$root}{$attr}>";
        $xml   .= self::dataToxml($data, $item, $id);
        $xml   .= "</{$root}>";
        return $xml;
    }

    /**
     * @param $xmlstring
     * @return mixed
     */
    public static function decode($xmlstring)
    {
        $object = simplexml_load_string($xmlstring, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);

        return @json_decode(@json_encode($object), 1);
    }


    public static function xmlSafeStr($str)
    {
        return '<![CDATA['.preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$str).']]>';
    }

    /**
     * 数据XML编码
     * @param mixed $data 数据
     * @return string
     */
    public static function dataToxml($data)
    {
        $xml = '';
        foreach ($data as $key => $val) {
            is_numeric($key) && $key = "item id=\"$key\"";
            $xml    .=  "<$key>";
            $xml    .=  ( is_array($val) || is_object($val)) ? self::dataToxml($val)  : self::xmlSafeStr($val);
            list($key, ) = explode(' ', $key);
            $xml    .=  "</$key>";
        }
        return $xml;
    }
}