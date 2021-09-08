<?php

namespace beyong\sms\driver;

use beyong\sms\Driver;
use beyong\sms\log\Log;

class Tencent extends Driver
{
	/**
	 * 主机地址
	 *
	 * @var string
	 */
	private $host = 'sms.tencentcloudapi.com';

    /**
     * 方法
     *
     * @var string
     */
    private $method = 'POST';

    /**
     * 路由
     *
     * @var string
     */
    private $route = '/';

    /**
     * 版本
     *
     * @var string
     */
    private $version = '2021-01-11';

    /**
     * service服务，sms, cvm
     *
     * @var string
     */
    private $service = "sms";

    /**
     * 短信应用id
     *
     * @var int
     */
    private $appid;

    /**
     * 设置短信应用id
     *
     * @return self
     */
    public function setAppid($appid)
    {
        $this->appid = $appid;
        return $this;
    }

    /**
     * 获得数据
     *
     * @return array
     */
    protected function getData()
    {
        $data = [
            'Action' => 'SendSms',
            //'Region' => 'ap-guangzhou', 
            //'Version' => $this->version,
            'Timestamp' => time(),
            //===============以上有公共参数====   
            'SmsSdkAppId' => $this->appid,         
            'SignName' => $this->sign, // 实际的短信签名串，不是id       
            'TemplateId' => $this->template,
        ];       

        $data["PhoneNumberSet"] = $this->mobiles;
        $data["TemplateParamSet"] = array_values($this->params);

        return $data;
    }

    /**
     * 生成签名, 使用了v3
     *
     * @param  array   $data
     * @return string
     */
    protected function signData($data)
    {
        return $this->signDataV3($data);
    }

    protected function signDataV1($data)
    {
        ksort($data);

        $string = $this->method . $this->host . $this->route . '?';
        foreach ($data as $key => $value) {
            $string .= $key . '=' . $value . '&';
        }
        $string = rtrim($string, '&');
        
        return base64_encode(hash_hmac('sha256', $string, $this->secret, true));
    }

    protected function signDataV3($data) 
    {
        $canonicalQueryString = "";
        $canonicalHeaders = "content-type:application/json\n" . "host:" . $this->host . "\n";
        $signedHeaders = "content-type;host";
        $payload = json_encode([
            "PhoneNumberSet" => $data["PhoneNumberSet"],
            "SmsSdkAppId" => $data["SmsSdkAppId"],
            "SignName" => $data["SignName"],
            "TemplateId" => $data["TemplateId"],
            "TemplateParamSet" => $data["TemplateParamSet"],
        ]);
        //echo $payload.PHP_EOL;
        $hashedRequestPayload = hash("SHA256", $payload);

        $canonicalRequest = $this->method . "\n"
            .$this->route . "\n"
            .$canonicalQueryString . "\n"
            .$canonicalHeaders . "\n"
            .$signedHeaders . "\n"
            .$hashedRequestPayload;
        //echo $canonicalRequest.PHP_EOL;

        // step 2: build string to sign
        $algorithm = "TC3-HMAC-SHA256";
        $timestamp = $data['Timestamp'];

        $date = gmdate("Y-m-d", $timestamp);
        $credentialScope = $date . "/" . $this->service . "/tc3_request";
        $hashedCanonicalRequest = hash("SHA256", $canonicalRequest);
        $stringToSign = $algorithm . "\n"
            . $timestamp . "\n"
            . $credentialScope . "\n"
            . $hashedCanonicalRequest;
        //echo $stringToSign.PHP_EOL;

        // step 3: sign string
        $secretDate = hash_hmac("SHA256", $date, "TC3" . $this->secret, true);
        $secretService = hash_hmac("SHA256", $this->service, $secretDate, true);
        $secretSigning = hash_hmac("SHA256", "tc3_request", $secretService, true);
        $signature = hash_hmac("SHA256", $stringToSign, $secretSigning);
        //echo $signature.PHP_EOL;

        // step 4: build authorization
        $authorization = $algorithm
            . " Credential=" . $this->key . "/" . $credentialScope
            . ", SignedHeaders=content-type;host, Signature=" . $signature;
        //echo $authorization.PHP_EOL;

        return $authorization;
    }

    /**
	 * 发送请求
	 *
	 * @param  array  $data
	 * @return array
     */
    protected function request($data)
    {

        $heads = [
            "Authorization: " . $data['Signature'] . "",
            "Content-Type: application/json",
            "Host: " . $this->host . "",
            "X-TC-Action: " . "SendSms",
            "X-TC-Timestamp: " . $data["Timestamp"],
            "X-TC-Version: " . $this->version,
            "X-TC-Region: " . "ap-guangzhou"
        ];

        $payload = [
            "PhoneNumberSet" => $data["PhoneNumberSet"],
            "SmsSdkAppId" => $data["SmsSdkAppId"],
            "SignName" => $data["SignName"],
            "TemplateId" => $data["TemplateId"],
            "TemplateParamSet" => $data["TemplateParamSet"],
        ];
        // var_dump($heads);
        // var_dump($payload);

    	$host = 'https://' . preg_replace('/https?:\/\//', '', $this->getHost());
    	$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $host);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $heads);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $response = curl_exec($ch);
        curl_close($ch);

		$this->response = $response;
		Log::write($response);

        $response = $this->handleResponse(json_decode($response, true));
        
        return $response['status'];
    }

    /**
     * 处理响应
     *
     * @param  array  $response
     * @return array
     */
    protected function handleResponse($response)
    {
        $result = ['status' => false];
        if (isset($response['Response']['SendStatusSet'])) {
            foreach ($response['Response']['SendStatusSet'] as $item) {
                $result['status'] = $item['Code'] == 'Ok';
                $result['code'] = $item['Code'];
                $result['message'] = $item['Message'];
                if (!$result['status']) {
                    break;
                }
            }
        } else {
            $result['code'] = $response['Response']['Error']['Code'];
            $result['message'] = $response['Response']['Error']['Message'];
        }

        if ($result['status'] !== true) {
            throw new \Exception($result["message"]);
        }

        return $result;
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
}