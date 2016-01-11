<?php

class ApiRequest
{
	private $_server;

	public function __construct($server=null)
	{
		$this->_server = empty($server) ? Yii::app()->params['apiServer'] : $server;
	}

	public function get($api, $uri_params,$hasServer=false)
	{
		return $this->_get($api, $uri_params,$hasServer);
	}
	
	public function multiGet($api, $uri_params_group,$hasServer=false)
	{
		return $this->_multi_get($api, $uri_params_group,$hasServer);
	}

	public function post($api, $post_params,$hasServer=false)
	{
		return $this->_post($api, $post_params, true,$hasServer);
	}

	public function rawPost($api, $post_params,$hasServer=false)
	{
		return $this->_post($api, $post_params, false,$hasServer);
	}

	public static function perform($api, $params, $method,$hasServer=false)
	{
		$request = new ApiRequest();
		switch (strtoupper($method)) {
			case 'GET':
				return $request->get($api, $params,$hasServer);
			case 'MULTIGET':
				return $request->multiGet($api, $params,$hasServer);
			case 'POST':
				return $request->post($api, $params,$hasServer);
			case 'RAWPOST':
				return $request->rawPost($api, $params,$hasServer);
		}
		return $request->parseApiResult($api, $params, null);
	}

	private function _get($api, $uri_params,$hasServer=false)
	{
		Yii::trace('ApiRequest get, params:'.var_export($uri_params,true),'apiRequest._get');
		$url = $this->getUrl($api, $uri_params,$hasServer);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, Yii::app()->params['apiTimeout']);

		$output = curl_exec($ch);
		$errno = curl_errno($ch);

		curl_close($ch);

		return $this->parseApiResult($api, $uri_params, $output);
	}
	
	private function _multi_get($api, $uri_params_group,$hasServer=false)
	{
		$ch_group = array();
		$ch_multi = curl_multi_init();
		foreach ($uri_params_group as $k => $uri_params)
		{
			$url = $this->getUrl($api, $uri_params,$hasServer);
			$ch = curl_init($url);
		
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, Yii::app()->params['apiTimeout']);
			
			$ch_group[$k] = $ch;
			curl_multi_add_handle($ch_multi, $ch);
		}

		do
		{
			$mrc = curl_multi_exec($ch_multi, $active);
		}
		while ($mrc == CURLM_CALL_MULTI_PERFORM);

		while ($active && $mrc == CURLM_OK)
		{
			if (curl_multi_select($ch_multi) != -1)
			{
				do
				{
					$mrc = curl_multi_exec($ch_multi, $active);
				}
				while ($mrc == CURLM_CALL_MULTI_PERFORM);
			}
		}

		$output_group = array();
		foreach($ch_group as $k => $ch)
		{
			$output = curl_multi_getcontent($ch);
			$output_group[] = $this->parseApiResult($api, $uri_params_group[$k], $output);
			curl_close($ch);
			curl_multi_remove_handle($ch_multi, $ch);
		}
		curl_multi_close($ch_multi);

		return $output_group;
	}

	private function _post($api, $post_params, $json_mode,$hasServer=false)
	{
		Yii::trace('ApiRequest post,isRawPost:'.$json_mode.',params:'.var_export($post_params,true),'apiRequest._post');
		$url = $this->getUrl($api, null, $hasServer);

		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, Yii::app()->params['apiTimeout']);

		if ($json_mode)
		{
			$data = json_encode($post_params);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($data))
			);
		}
		else
		{
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
		}

		$output = curl_exec($ch);
		$errno = curl_errno($ch);
		curl_close($ch);

		return $this->parseApiResult($api, $post_params, $output);
	}

	private function getUrl($api, $uri_params=null,$hasServer = false)
	{
		$query = $this->buildQuery($uri_params);
        if($hasServer){
            return empty($query) ? "{$api}" : "{$api}?{$query}";
        }
		$url =  empty($query) ? "{$this->_server}{$api}" : "{$this->_server}{$api}?{$query}";
		Yii::trace('ApiRequest url:'.$url,'apiRequest.getUrl');
		return $url;
	}

	private function buildQuery($uri_params, $encode=true)
	{
		if (empty($uri_params))
			return '';
		if ($encode)
			return http_build_query($uri_params);

		$queryParms = array();
		foreach ((array)$uri_params as $k => $v)
			$queryParms[] = "{$k}={$v}";
		return implode('&', $queryParms);
	}

	private function parseApiResult($api, $params, $output)
	{
		Yii::trace("Call {$api} failed, params=" . json_encode($params) . ", response={$output}",
			'apiRequest.parseApiResult');

		$result = empty($output) ? null : @json_decode($output, true);
		if (empty($result))
			$result = array('code'=>'E000000', 'data'=>array());
		$result['success'] = $result['code'] == 'N000000';
		if (!$result['success'])
		{
			//OperationLog::prettyErrorLog("Call {$api} failed, params=" . json_encode($params) . ", response={$output}");
			Yii::log("Call {$api} failed, params=" . json_encode($params) . ", response={$output}",
				CLogger::LEVEL_ERROR,'apiRequest.parseApiResult');
		}
		return $result;
	}
}
