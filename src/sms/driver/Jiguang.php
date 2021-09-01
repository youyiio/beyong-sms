<?php

namespace beyong\sms\driver;

use beyong\sms\Driver;
use beyong\sms\log\Log;

class Jiguang extends Driver
{
    protected $host = 'https://api.sms.jpush.cn/v1/messages';

    protected $base_host = 'https://api.sms.jpush.cn/v1/';


    //发送验证码
    public function sendCode($mobile, $temp_id, $sign_id = null) {
        $url = $this->base_host . 'codes';
        $body = array('mobile' => $mobile, 'temp_id' => $temp_id);
        if (isset($sign_id)) {
            $body['sign_id'] = $sign_id;
        }
        return $this->request('POST', $url, $body);
    }

    //发送语音验证码
    public function sendVoiceCode($mobile, $options = []) {
        $url = $this->base_host . 'voice_codes';
        $body = array('mobile' => $mobile);

        if (!empty($options)) {
            if (is_array($options)) {
                $body = array_merge($options, $body);
            } else {
                $body['ttl'] = $options;
            }
        }
        return $this->request('POST', $url, $body);
    }

    //验证短信验证码
    public function checkCode($msg_id, $code) {
        $url = $this->base_host . 'codes/' . $msg_id . "/valid";
        $body = array('code' => $code);
        return $this->request('POST', $url, $body);
    }

    //发送短信
    public function sendMessage($mobile, $temp_id, array $temp_para = [], $time = null, $sign_id = null) {
        $path = 'messages';
        $body = array(
            'mobile'    => $mobile,
            'temp_id'   => $temp_id,
        );
        if (!empty($temp_para)) {
            $body['temp_para'] = $temp_para;
        }
        if (isset($time)) {
            $path = 'schedule';
            $body['send_time'] = $time;
        }
        if (isset($sign_id)) {
            $body['sign_id'] = $sign_id;
        }
        $url = $this->base_host . $path;
        return $this->request('POST', $url, $body);
    }

    /**
	 * 处理手机号
	 * 未加国际区号时默认为中国手机号
	 *
	 * @param  mixed  $mobiles
     * @return array
     */
    public function handleMobile($mobiles)
    {
    	$mobiles = is_array($mobiles) ? $mobiles : [$mobiles];
    	foreach ($mobiles as $index => $value) {
    		// $first = substr($value, 0, 1);
    		// if (substr($value, 0, 1) != '+') {
    		// 	$mobiles[$index] = '+86' . $value;
    		// }
    	}
    	return $mobiles;
    }

    /**
	 * 获得数据
	 *
	 * @return array
     */
    public function getData()
    {
    	$data = [
            'mobile' => implode(',', $this->mobiles),
    		'temp_id' => $this->template,
            'sign_id' => $this->sign,
    	];
        if (!empty($this->params)) {
            $data['temp_para'] = $this->params;
        }
        // if (isset($this->time)) {
        //     $path = 'schedule';
        //     $data['send_time'] = $this->time;
        // }

    	return $data;
    }

    /**
	 * 生成签名
	 *
     * @param  array  $data
     * @return string
     */
    protected function signData($data)
    {
    	return 'none';
    }

    /**
     * 处理响应
     *
     * @param  array  $response
     * @return array
     */
    protected function handleResponse($response)
    {
        return [
            'status' => isset($response['msg_id']), 
            'code' => isset($response['msg_id']) ? 1 : $response['error']['code'], 
            'message' => isset($response['msg_id']) ? 'success' : $response['error']['message']
        ];
    }

    /**
     * 获得主机地址
     *
     * @return array
     */
    protected function getHost()
    {
        return $this->host;
    }

    /**
	 * 发送请求(重载，极光使用了basic auth)
	 *
	 * @param  array  $data
	 * @return array
     */
    protected function request($data)
    {
    	$host = 'https://' . preg_replace('/https?:\/\//', '', $this->getHost());
    	$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $host);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->key . ":" . $this->secret);

        $response = curl_exec($ch);
        curl_close($ch);

        Log::write($response);
		
        $response = $this->handleResponse(json_decode($response, true));
        $this->response = $response;
        return $response['status'];
    }
}