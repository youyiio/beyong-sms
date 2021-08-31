<?php

namespace beyong\sms\log;

use beyong\sms\Config;

/**
 * Class Log
 * 
 */
class Log
{
    const DEBUG = 'DEBUG';
    const INFO = 'INFO';
    const ERROR = 'ERROR';

    /**
     * @var object 日志驱动
     */
    private static $driver;


    public static function init()
    {
        if (null === self::$driver) {
            if (Config::get('log_driver')) {
                $driver = Config::get('log_driver');
                self::$driver = $driver;
            } else {
                self::$driver = \beyong\sms\log\File::class;
            }
        }
    }

    /**
     * 写入日志
     *
     * @param        $content
     * @param string $level
     */
    public static function write($content, $level = self::DEBUG)
    {
        self::init();

        $driver = self::$driver;
        $driver::write($content, $level);
    }
}
