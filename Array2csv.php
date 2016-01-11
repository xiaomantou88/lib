<?php

namespace xiaojun\help;

/**
 * 将数组转换成csv文件并存储
 * Class Array2csv
 * @package xiaojun\help
 */
class Array2csv
{
    public static function run(array $rows, $saveFile, array $headers = [], $delimiter=',')
    {
        $handle = fopen($saveFile, 'w+');
        if(!$handle)
        {
            return false;
        }
        if (count($headers))
        {
            fputcsv($handle, $headers, $delimiter);
        }
        foreach ($rows as $row)
        {
            fputcsv($handle, $row, $delimiter);
        }
        fclose($handle);
        return true;
    }

	
    /*
     * 将数据转换成csv，并下载
     * @param array $data 要处理的数据
     * @param string $type 数据类型 默认 array 可选 table
    */
    static public function output_csv_file($data, $type = "array", $filename="data.csv")
    {
        $csv = "";
        if($type == "array"){
            $csv = self::a2c($data);
        }elseif($type == "table"){
            $csv = self::t2c($data);
        }else{
            return false;
        }
        header("Content-Disposition: attachment; filename=$filename");
        header('Content-Type: text/plain');
        echo $csv;
    }
    /*
     * 将数据转换成csv，并写到磁盘上
     * @param array $data 要处理的数据
     * @param string $type 数据类型 默认 array 可选 table
    */
    static public function write_csv_file($data, $type = "array", $filename)
    {
        $csv = "";
        if($type == "array"){
            $csv = self::a2c($data);
        }elseif($type == "table"){
            $csv = self::t2c($data);
        }else{
            return false;
        }
        file_put_contents($filename,$csv);
    }
}