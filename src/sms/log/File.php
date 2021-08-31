<?php
namespace beyong\sms\log;

use beyong\sms\Config;

class File
{
    const DEBUG = 'debug';
    const INFO = 'info';

    /**
     * 写入日志
     *
     * @param $content
     * @param string $level
     */
    public static function write($content, $level = self::DEBUG)
    {
        $now = date(' c ');
        $path = Config::get('log_path', __DIR__ . '/../../../../log');
        $destination = $path . '/sms-' . date('Y-m-d') . '.log';
        // 自动创建日志目录
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $remoteAddr = '';
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $remoteAddr = $_SERVER['REMOTE_ADDR'];
        }
        $requestUri = '';
        if (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = $_SERVER['REQUEST_URI'];
        }
        $content = '[ ' . $level . ' ] ' . $content;
        error_log("[{$now}] " . $remoteAddr . ' ' . $requestUri . "\r\n{$content}\r\n", 3, $destination);
    }
}
