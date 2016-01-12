<?php
namespace xiaojun\help;

/**
 * curl 方法的封装
 * Class Curl
 * @package xiaojun\help
 */
class Curl
{
    public static $curlTimeOut = 5;

    public static function get($url, $uri_params=[])
    {
        $url = self::buildUrl($url, $uri_params);
        echo $url;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::$curlTimeOut);

        $output = curl_exec($ch);
        $errno = curl_errno($ch);
        //TODO 记录错误信息

        curl_close($ch);
        return $output;
    }

    /**
     * @param $url
     * @param $post_params
     * @return mixed
     * post 表单数据
     */
    public static function post($url, $post_params)
    {
        return self::_post($url, $post_params, true);
    }

    /**
     * @param $url
     * @param $post_params
     * @return mixed
     * post 原始的数据，例如: json串,xml等数据
     */
    public static function rawPost($url, $post_params)
    {
        return self::_post($url, $post_params, false);
    }

    /**
     * @param $url
     * @param $filename
     * @param string $fileFormName
     * @param array $params
     * @return mixed
     * 模拟表单提交文件
     */
    public static function filePost($url, $filename, $fileFormName='file', $params=[])
    {
        //获取文件的mime
        $finfo = finfo_open(FILEINFO_MIME); // 返回 mime 类型
        $mimeType = finfo_file($finfo, $filename);
        finfo_close($finfo);
        if($mimeType)
        {
            $tempArr = explode(";", $mimeType);
            $mimeType = $tempArr[0];
        }
        $objFile = curl_file_create($filename, $mimeType);
        $params[$fileFormName] = $objFile;


        $curl = curl_init();
        if(stripos($url,"https://")!==FALSE)
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_TIMEOUT, self::$curlTimeOut);

        $output = curl_exec($curl);
        $errno = curl_errno($curl);

        curl_close($curl);
        return $output;
    }



    private static function _post($url, $post_params, $json_mode=true)
    {
        //$url = self::buildUrl($url, null);

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::$curlTimeOut);

        if ($json_mode)
        {
            $data = json_encode($post_params);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        else
        {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
        }

        $output = curl_exec($ch);
        $errno = curl_errno($ch);
        //TODO 记录错误信息

        curl_close($ch);

        return $output;
    }

    /**
     * Build an URL with an optional query string.
     *
     * @param  string $url   the base URL without any query string
     * @param  array  $query array of GET parameters
     *
     * @return string
     */
    public static function buildUrl($url, array $query)
    {
        if (empty($query)) {
            return $url;
        }
        $parts = parse_url($url);
        $queryString = '';
        if (isset($parts['query']) && $parts['query']) {
            $queryString .= $parts['query'].'&'.http_build_query($query);
        } else {
            $queryString .= http_build_query($query);
        }
        $retUrl = $parts['scheme'].'://'.$parts['host'];
        if (isset($parts['port'])) {
            $retUrl .= ':'.$parts['port'];
        }
        if (isset($parts['path'])) {
            $retUrl .= $parts['path'];
        }
        if ($queryString) {
            $retUrl .= '?' . $queryString;
        }
        return $retUrl;
    }

}