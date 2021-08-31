<?php
//composer库测试时，执行composer update，会自动安装本地库至vendor
//项目目录下，执行 php examples\aliyun_send_sms.php
require __DIR__ . '/../vendor/autoload.php';

\beyong\sms\Config::init(include(__DIR__ . '/config.php'));

$smsClient = \beyong\sms\SmsClient::instance();

# 这里的 $temp_id 和 $temp_para 的值需要到 "极光控制台 -> 短信验证码 -> 模板管理" 里面获取
$temp_id = '1';
$temp_para = [];
$sign = '';

$response = $smsClient->to('xxxx')->template($temp_id, $temp_para)->send();

print_r($response);
