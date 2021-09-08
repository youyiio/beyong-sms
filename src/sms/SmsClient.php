<?php

namespace beyong\sms;

use beyong\sms\driver\Aliyun;
use beyong\sms\driver\Jiguang;
use beyong\sms\driver\Tencent;
use beyong\sms\log\Log;

class SmsClient
{

    /*
     * @var Mailer 单例
     */
    protected static $instance;

    /**
     * @var array 注册的方法
     */
    protected static $methods = [];

    /**
     *
     * @var sms driver
     */
    protected $driver;

    /**
     * @var string|null 错误信息
     */
    protected $errMsg;
    /**
     * @var array|null 发送失败的帐号
     */
    protected $fails;

    /**
     * @param null $transport
     *
     * @return Mailer
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * 动态注入方法
     *
     * @param string   $methodName
     * @param callable $methodCallable
     */
    public static function addMethod($methodName, $methodCallable)
    {
        if (!is_callable($methodCallable)) {
            throw new \Exception('Second param must be callable');
        }
        self::$methods[$methodName] = \Closure::bind($methodCallable, SmsClient::instance(), get_class());
    }

    /**
     * 动态调用方法
     *
     * @param string $methodName
     * @param array  $args
     *
     * @return $this
     */
    public function __call($methodName, array $args)
    {
        if (isset(self::$methods[$methodName])) {
            return call_user_func_array(self::$methods[$methodName], $args);
        }

        throw new \Exception('There is no method with the given name to call');
    }

    /**
     * SmsClient constructor.
     *
     * @param mixed $transport
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * 重置实例
     *
     * @return $this
     */
    public function init()
    {
        $driver = strtolower(Config::get("driver"));
        switch($driver) {
            case "aliyun":
                $this->driver = new Aliyun(Config::get("key"), Config::get("secret"));
                break;
            case "tencent":
                if (empty(Config::get("SDKAppID"))) {
                    throw new \Exception("'SDKAppID' key is needed in config.php by tencent sms! 腾讯配置很特殊!");
                }
                $this->driver = new Tencent(Config::get("key"), Config::get("secret"));
                $this->driver->setAppid(Config::get("SDKAppID"));
                break;
            case "jiguang":
            case "jpush":
                $this->driver = new Jiguang(Config::get("key"), Config::get("secret"));
                break;
        }
        
        return $this;
    }

    /**
     * 设置接收人
     *
     * @param  string|array $mobile
     *
     * @return $this
     */
    public function to($mobile)
    {
        $this->driver->to($mobile);

        return $this;
    }

    /**
     * 设置短信签名
     *
     * @param  string|array $mobile
     *
     * @return $this
     */
    public function sign($sign)
    {
        $this->driver->sign($sign);

        return $this;
    }

    /**
     * 短信内容，设置短信模板
     *
     * @param string $template
     *
     * @return $this
     */
    public function template($template, $param=[])
    {
        $this->driver->template($template)->params($param);

        return $this;
    }

    /**
     * 短信内容，设置短信内容模板
     *
     * @param string $subject
     *
     * @return $this
     */
    public function subject($subject, $param=[])
    {
        //$this->driver->withTemplate($template)->params($param);

        return $this;
    }

    /**
     * 设置字符编码
     *
     * @param string $charset
     *
     * @return $this
     */
    public function charset($charset)
    {
        $this->driver->setCharset($charset);

        return $this;
    }

    /**
     * 设置邮件最大长度
     *
     * @param int $length
     *
     * @return $this
     */
    public function lineLength($length)
    {
        $this->driver->setMaxLineLength($length);

        return $this;
    }

    /**
     * 获取响应信息
     *
     * @return 
     */
    public function getResponse()
    {
        return $this->driver->getResponse();
    }

    /**
     * 发送短信
     *
     * @return bool|int
     * @throws Exception
     */
    public function send()
    {
        try {            
            return $this->driver->send();            
        } catch (\Exception $e) {
            $this->errMsg = $e->getMessage();

            // 将错误信息记录在日志中
            $log = "Error: " . $this->errMsg . "\n"
                . '响应信息：' . "\n"
                . var_export($this->getResponse(), true);
            Log::write($log, Log::ERROR);

            // 异常处理
            if (Config::get('debug')) {
                // 调试模式直接抛出异常
                throw new \Exception($e->getMessage());
            } else {
                return false;
            }
        }
    }

    /**
     * 获取错误信息
     *
     * @return mixed
     */
    public function getError()
    {
        return $this->errMsg;
    }

    /**
     * 获取发送错误的邮箱帐号列表
     *
     * @return mixed
     */
    public function getFails()
    {
        return $this->fails;
    }

    /**
     * 中文文件名编码, 防止乱码
     *
     * @param string $string
     *
     * @return string
     */
    public function cnEncode($string)
    {
        return "=?UTF-8?B?" . base64_encode($string) . "?=";
    }

    /**
     * 将参数中的key值替换为可替换符号
     *
     * @param array $param
     * @param array $config
     *
     * @return mixed
     */
    protected function parseParam(array $param, array $config = [])
    {
        $ret = [];
        $leftDelimiter = isset($config['left_delimiter'])
            ? $config['left_delimiter']
            : Config::get('left_delimiter', '{');
        $rightDelimiter = isset($config['right_delimiter'])
            ? $config['right_delimiter']
            : Config::get('right_delimiter', '}');
        foreach ($param as $k => $v) {
            // 处理变量中包含有对元数据嵌入的变量
            $this->embedImage($k, $v, $param);
            $ret[$leftDelimiter . $k . $rightDelimiter] = $v;
        }

        return $ret;
    }

}