<?php
namespace beyong\sms\log;

class Console
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

        $remoteAddr = '';
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $remoteAddr = $_SERVER['REMOTE_ADDR'];
        }
        $requestUri = '';
        if (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = $_SERVER['REQUEST_URI'];
        }
        $content = '[ ' . $level . ' ] ' . $content;
        echo "[{$now}] " . $remoteAddr . ' ' . $requestUri . "\r\n{$content}\r\n";
    }
}
