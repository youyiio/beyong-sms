<?php
//composer库测试时，执行composer update，会自动安装本地库至vendor
//项目目录下，执行 php examples\aliyun_send_sms.php
require __DIR__ . '/../vendor/autoload.php';

//$smsConfig = include(__DIR__ . '/config.php');
$smsConfig = include(__DIR__ . '/config_aliyun.php');
\beyong\sms\Config::init($smsConfig);

$smsClient = \beyong\sms\SmsClient::instance();

# 这里的 $template 和 $params 的值需要到 "阿里云控制台 -> 短信功能 -> 国内消息 -> 模板管理" 里面获取
$sign = $smsConfig["actions"]["register"]["sign"];
$template = $smsConfig["actions"]["register"]["template"];
$params = ["code" => "123456"];

$mobile = '';

$response = $smsClient->sign($sign)->to($mobile)->template($template, $params)->send();

print_r($response);
