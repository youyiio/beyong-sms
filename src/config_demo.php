<?php

/**
 * 示例配置文件
 *
 * 可以配置在 sms.php 或 config.php 文件
 */
return [
    'driver'      => 'aliyun', // 服务提供商, 支持 aliyun|tencent|jiguang 三种
    'key'         => '', // 短信服务key
    'secret'      => '', // 短信服务secret

    'debug' => true,
    'log_driver' => '', //\beyong\sms\log\File::class,
    'log_path' => __DIR__ . '/log'
];
