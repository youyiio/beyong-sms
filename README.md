# beyong-sms

**一款支持多家短信服务商优美的短信发送库**，ThinkPHP系列框架【5.0.x,5.1.x,6.0.x】开箱即用，其他框架初始化配置即可使用

基于 阿里云、腾讯云、极光最新短信发送功能的极简包 二次开发, 为ThinkPHP系列框架量身定制, 使 ThinkPHP 支持短信模板、纯文本发送以及更多短信功能, 短信发送简单到只需一行代码

[github地址](https://github.com/youyiio/beyong-sms)

## 目录 
* [优雅的发送短信](#优雅的发送短信) 
* [安装](#安装) 
    * [使用 Composer 安装 (强烈推荐)](#使用-composer-安装-强烈推荐)
    * [github下载 或 直接手动下载源码](#github下载-或-直接手动下载源码)
        * [下载文件](#下载文件)
        * [引入自动载入文件](#引入自动载入文件)
* [配置](#配置) 
    * [部分配置详解](#部分配置详解)
* [使用](#使用) 
    * [使用beyong-sms](#使用beyong-sms)
    * [创建实例](#创建实例)
    * [设置收件人](#设置收件人)
    * [设置发件人](#设置发件人)
    * [设置短信主题](#设置短信主题)
    * [设置短信内容 - template](#设置短信内容-template)
    * [设置短信内容](#设置短信内容)
    * [发送短信](#发送短信)
* [Issues](#issues)
* [License](#license)


## 优雅的发送短信

**ThinkPHP5/6 示例**
```
use beyong\sms\SmsClient;

$client = SmsClient::instance();
$client->to('177xxxxx')
    ->template('temp_id', $data)
    ->sign('sign_id')
    ->send();
```



## 安装
### 使用 Composer 安装 (强烈推荐):
支持 `psr-4` 规范, 开箱即用
```
composer require youyiio/beyong-sms
```

### github下载 或 直接手动下载源码:
需手动引入自动载入文件

#### 下载文件:
git clone https://github.com/youyiio/beyong-sms beyong-sms


#### 引入自动载入文件:
使用时引入或者全局自动引入

`require_once '/path/to/beyong-sms/src/autoload.php`;


## 配置
在配置文件里配置如下信息, 可以配置在 `sms.php` 或 `config.php` 文件中, 内容如下:
```
return [
    'driver'      => 'aliyun', // 服务提供商, 支持 aliyun|tencent|jiguang 三种
    'key'         => '', // 短信服务key
    'secret'      => '', // 短信服务secret
    'SDKAppID'    => '', // 腾讯短信平台需要
    'debug'      => true,
    'log_driver' => '', //\beyong\sms\log\File::class,
    'log_path'   => __DIR__ . '/log'
];
```
### 部分配置详解
#### driver
可选值是字符串，只能是 `aliyun|tencent|jiguang`

#### key & secret
在短信提供商注册时的密钥

#### log_driver
日志驱动，如果不配置则为类库自带简单的日志驱动 `\beyong\sms\log\File::class`，可自定义配置为框架的日志驱动，例如 `'log_driver' => '\\think\\Log'`，日志驱动类必须实现静态方法 `write`，例如:
```
public static function write($content, $level = 'debug')
{
    echo '日志内容：' . $content;
    echo '日志级别：' . $level;
}
```

#### log_path
日志驱动为默认是日志存储路径，不配置默认为 `beyong-sms/log/`，例如可配置为 `ROOT_PATH . 'runtime/log/'`

## 使用

以下示例以 ThinkPHP5 里使用为例, 其他框架完全一样

### 使用beyong-sms
```
// 不支持自动载入的框架请手动引入自动载入文件
// require_once '/path/to/beyong-sms/src/autoload.php';

use beyong\sms\SmsClient
```

### 创建实例
不传递任何参数表示短信驱动使用配置文件里默认的配置
```
$client = SmsClient::instance();
```

### 设置收件人
以下几种方式任选一种
```
$client->to(['177xxxx']);
$client->to(['177xxxx', '132xxxx']);

```

### 设置短信内容-template
```
$client->template('欢迎使用beyong-sms');
```

或者使用变量替换模板内容
```
$client->template('欢迎使用{name}', ['name' => 'beyong-sms']);
```

### 设置短信内容
```
$client->subject('短信主题');
```

#### 示例
```
SmsClient::instance()
    ->to('132xxxx') 
    ->template('temp_id', [])
    ->sign('sign_id')
    ->send();
```


## Issues
如果有遇到问题请提交 [issues](https://github.com/youyiio/beyong-sms/issues)


## License
Apache 2.0
