<?php
//composer库测试时，执行composer update，会自动安装本地库至vendor
//测试运行: php examples/jiguang_send_sms.php
require __DIR__ . '/../vendor/autoload.php';

//$smsConfig = include(__DIR__ . '/config.php');
$smsConfig = include(__DIR__ . '/config_jiguang.php');
\beyong\sms\Config::init($smsConfig);

$smsClient = \beyong\sms\SmsClient::instance();

# 这里的 $temp_id 和 $temp_para 的值需要到 "极光控制台 -> 短信验证码 -> 模板管理" 里面获取
$sign = $smsConfig["actions"]["register"]["sign"];
$template = $smsConfig["actions"]["register"]["template"];
$params = ["code" => "123456"];

$mobile = '';

$response = $smsClient->to($mobile)->sign($sign)->template($temp_id, $temp_params)->send();

print_r($response);
