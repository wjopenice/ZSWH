<?php
use Yaf\Application;
use Yaf\Dispatcher;
use Error\CodeConfigModel;
class NotifyurlController extends Yaf\Controller_Abstract {

    const APPID = "155918185956762";
    const KEY = "LfY7iGPUdy2qo8H2Y1vsxYIhmqj1lNvD";
    const TIMEZONE = "Asia/Shanghai";
    const TIMEOUT = "3600";
    const FRONT_URL = "http://app.zhishengwh.com/notifyurl/notify"; //缺少前台通知地址
    const NOTIFY_URL = "http://app.zhishengwh.com/notifyurl/notify";
    const CURRENCYTYPE = "156";
    const CHARSET = "UTF-8";
    const DEVICE_TYPE = "01";
    const SIGN_TYPE = "MD5";
    const TRADE_URL = "https://pay.ipaynow.cn";
    /**
     * @author Ping
     *
     * 用于接收异步通知
     */
    public function notifyAction(){
        file_put_contents(APP_PATH."/log.txt","input：".file_get_contents('php://input')."\r\n",FILE_APPEND);
        file_put_contents(APP_PATH."/log.txt","post：".$_POST."\r\n",FILE_APPEND);
        file_put_contents(APP_PATH."/log.txt","get：".$_GET."\r\n",FILE_APPEND);
        exit;
        $res = file_get_contents('php://input');
        $Arr = array();
        $res = explode('&', $res);
        foreach($res as $v) {
            $value = explode('=', $v);
            $Arr[$value[0]] = urldecode($value[1]);
        }
        $signatrue = self::getSignTrue($Arr, self::KEY);
        if($signatrue == $Arr['signature']) {
            //异步通知验签通过，在此处写业务逻辑
            file_put_contents(APP_PATH."/log.txt","成功：".$res."\r\n",FILE_APPEND);
            exit;
            echo 'success=Y';
        } else {
            //异步通知验签失败，在此处写业务逻辑
            file_put_contents(APP_PATH."/log.txt","失败：".$res."\r\n",FILE_APPEND);//失败
            exit;
            echo 'success=Y';
        }
    }

    //对请求报文做拼接和生成签名
    static function getSignTrue( $Arr, $key )
    {
        if( !empty($Arr) ) {
            ksort($Arr);
            $str = '';
            foreach( $Arr as $k => $v) {
                if( $v == '' || $k == 'signature') {
                    continue;
                }
                $str .= $k.'='.$v.'&';
            }
            if( $key == 'post' ) {
                return substr($str, 0, -1);
            } else {
                return strtolower(md5($str.md5($key)));
            }
        }
        return false;
    }

    //发送post请求
    static function post($url, $data)
    {
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 40); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            return 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据
    }
    static function log($path, $Arr)
    {
        if( !$query = self::getSignTrue($Arr, 'post') ) {
            return false;
        }
        $str  = date('Y-m-d H:i:s');
        $str .= ': appid=';
        $str .= $Arr['appId'];
        $str .= ',请求报文：';
        $str .= $query;
        $str .= "\r\n";
        $fileName = $path.date('Ymd').'.log';
        file_put_contents($fileName, $str, FILE_APPEND);
    }
}

