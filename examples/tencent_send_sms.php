<?php
//composer库测试时，执行composer update，会自动安装本地库至vendor
//测试运行: php examples/tencent_send_sms.php
require __DIR__ . '/../vendor/autoload.php';

//$smsConfig = include(__DIR__ . '/config.php');
$smsConfig = include(__DIR__ . '/config_tencent.php');
\beyong\sms\Config::init($smsConfig);

$smsClient = \beyong\sms\SmsClient::instance();

# 这里的 $template 和 $params 的值需要到 "腾讯云控制台 -> 短信功能 -> 国内消息 -> 模板管理" 里面获取
$sign = $smsConfig["actions"]["register"]["sign"];
$template = $smsConfig["actions"]["register"]["template"];
$params = ["123456"];

$mobile = '';

$response = $smsClient->to($mobile)->sign($sign)->template($template, $params)->send();

print_r($response);
