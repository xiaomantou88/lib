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
}