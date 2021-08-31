<?php

namespace beyong\sms;

/**
 * Class Config
 * @package beyong\sms
 */
class Config
{
    /**
     * @var array 配置项
     */
    private static $config = [];
    /**
     * @var bool 是否初始化
     */
    private static $isInit = false;

    /**
     * 初始化配置项
     *
     * @param array $config
     */
    public static function init($config = [])
    {
        if ($config) {
            self::$config = array_merge(self::$config, $config);
            self::$isInit = true;
        } elseif (!self::$isInit) {
            self::detect();
            self::$isInit = true;
        }
    }

    /**
     * 获取配置参数 为空则获取所有配置
     *
     * @param string $name    配置参数名
     * @param mixed  $default 默认值
     *
     * @return mixed
     */
    public static function get($name = null, $default = null)
    {
        self::init();

        if (!$name) {
            return self::$config;
        } else {
            if (isset(self::$config[$name])) {
                return self::$config[$name];
            } else {
                return $default;
            }
        }
    }

    /**
     * 设置配置参数
     *
     * @param string|array $name  配置参数名
     * @param mixed        $value 配置值
     */
    public static function set($name, $value)
    {
        self::init();

        self::$config[$name] = $value;
    }

    /**
     * 自动探测配置项
     */
    private static function detect()
    {
        if (class_exists('\\think\\facade\\Config')) {
            // thinkphp5.1自动探测初始化配置项
            self::$config = \think\facade\Config::get('sms');
        } elseif (class_exists('\\think\\Config')) {
            // thinkphp5自动探测初始化配置项
            self::$config = \think\Config::get('sms');
        } elseif (function_exists('C')) {
            // thinkphp3自动探测初始化配置项
            self::$config = C('sms');
        } else {
            // 其他框架如果未初始化则抛出异常
            throw new \Exception('未初始化配置项，请使用 beyong\\sms\\Config::init()初始化配置项');
        }
    }
}
