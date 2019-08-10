<?php

namespace Think\Pay\Driver;

class Alipay extends \Think\Pay\Pay {

    protected $gateway = 'https://mapi.alipay.com/gateway.do';
    protected $verify_url = 'http://notify.alipay.com/trade/notify_query.do';
    protected $config = array(
        'email' => '',
        'key' => '',
        'partner' => ''
    );

    public function check() {
        if (!$this->config['email'] || !$this->config['key'] || !$this->config['partner']) {
            E("支付宝设置有误！");
        }
        return true;
    }

    public function buildRequestForm(\Think\Pay\PayVo $vo){
        switch ($vo->getPayMethod()) {
            case 'direct':
                $seller = $vo->getPayMethod()=="mobile"?"seller_id":"seller_email";
                $param = array(
                    'service'        => $vo->getService(),//create_direct_pay_by_user
                    'payment_type'   => '1',
                    '_input_charset' => 'utf-8',
                    $seller          => $this->config['email'],//seller_email
                    'partner'        => $this->config['partner'],
                    'notify_url'     => $this->config['notify_url'],
                    'return_url'     => $this->config['return_url'],
                    'out_trade_no'   => $vo->getOrderNo(),
                    'subject'        => $vo->getTitle(),
                    'body'           => $vo->getBody(),
                    'total_fee'      => $vo->getFee(),
                    'it_b_pay'       => '30m'
                );
                #对数组进行排序
                $param = $this->argSort($param);
                #把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
                $arg = $this->createLinkstring($param);
                $param['sign'] = md5($arg . $this->config['key']);
                $param['sign_type'] = 'MD5';
                $sHtml = $this->_buildForm($param, $this->gateway, 'get');
                break;
            case 'mobile':
                //组装商品内容
                $jsonData = str_replace("\\/", "/", json_encode([
                    'timeout_express'=>'30m',
                    'product_code'=>"QUICK_MSECURITY_PAY",
                    'total_amount'=>$vo->getFee(),
                    'subject'=>$vo->getTitle(),
                    'body'=>$vo->getBody(),
                    'out_trade_no'=>$vo->getOrderNo()],JSON_UNESCAPED_UNICODE));
                $payData['charset'] = "utf-8";
                $payData['biz_content'] = $jsonData;
                $payData['method'] = "alipay.trade.app.pay";
                $payData['app_id'] = "2018072060681440";
                $payData['sign_type'] = "RSA2";
                $payData['version'] = '1.0';
                $payData['notify_url'] = 'http://www.zhishengwh.com/callback.php/Notify/sdknotify';
                $payData['timestamp'] = date("Y-m-d H:i:s",time());
                #对数组进行排序
                $param = $this->argSort($payData);
                $arg = $this->createLinkstring($param);
                $payData['sign'] = urlencode($this->sign2($arg));
                $datax = $this->createLinkstring($payData);
                //返回给SDK控制器
                $sHtml['arg']  = $datax;
                $sHtml['sign'] = $this->sign2($arg);
                $sHtml['out_trade_no'] = $vo->getOrderNo();

                break;
        }

        return $sHtml;
    }

    //RSA2签名
    public function sign2($data) {
        //读取私钥文件
        $priKey = file_get_contents("./Application/Sdk/SecretKey/alipay/rsa_private_key.pem");//私钥文件路径
        //转换为openssl密钥，必须是没有经过pkcs8转换的私钥
        $res = openssl_get_privatekey($priKey);
        //调用openssl内置签名方法，生成签名$sign
        openssl_sign($data, $sign, $res ,OPENSSL_ALGO_SHA256);
        //释放资源
        openssl_free_key($res);
        //base64编码
        $sign = base64_encode($sign);
        return $sign;
    }

//    public function buildRequestFormxxxx(\Think\Pay\PayVo $vo) {
//        $seller = $vo->getPayMethod()=="mobile"?"seller_id":"seller_email";
//        $param = array(
//            //'service'        => $vo->getService(),//create_direct_pay_by_user
//            //'payment_type'   => '1',
//            //'_input_charset' => 'utf-8', //编码
//            //$seller          => $this->config['email'],//seller_email
//            //'partner'        => $this->config['partner'],  //APPID合作ID
//            //'notify_url'     => $this->config['notify_url'],
//            //'return_url'     => $this->config['return_url'],
//            //'total_fee'      => $vo->getFee(), //金额
//            // 'it_b_pay'       => '30m' //时间
//
//            'gatewayUrl'     => "https://openapi.alipay.com/gateway.do",
//            'charset'        => 'utf-8',  //编码
//            'app_id'          => '2018072060681440',  //APPID合作ID
//            'format'         => "json",  //格式
//            'rsaPrivateKey'  => 'MIIEpAIBAAKCAQEAuXd7XjAEWVKRrGoKQFYB5Vxm5Io3fb6tsovb+pG6AkoFsmlWBn9NCf6MqaOWxgl205ImHBnHRxbP2d/PzXZB3+Ng7gGzw4tipoT0VRkXPab93ZXKE+mgFvr/GZXOR3jsLNflqnu7zTvDNX8HvqeUBtndLb4nteHNXMEeNqT4m/8mO7DlQ7DJ64Dbd0L0WA4KR13w4h7R1dqMtgv5U6mIInszEhEdiGjOnB+VrL8EoY3LC5Hrn0rB4BNMDJ8hlao2jZPItVeI9E+Z0VhiX14HUJ5OeMWCPszl3FerkfrLr+NfFr+wdBBKMy3OJcHImcwisM+j6yvRI0X1OqxiDkh7awIDAQABAoIBAQCavqdPce7e/DaRTbSZ82kHju5Gt1APeb4BoBH94gL6D/rq3lqpdyO3OAzzKYwOVi0v39wuTA/qL41i8wu2GXpjLJteWks715uK5pnaOuIaTa+5Z1ZBAQfSxL9+AHEpTyp3S/fTJAQQ/FEm3IOAvt+SS8rwdJ07c1hekL79xu2rcW899NnF0ZCH+V94mbsE8Kyhg2WM7DDUM6LMA+8yIOpC4blEQhWTIGRJR3JuS79OeoJcn7fLQEQmjyH3WWJN1AEgHvTekyG8USV/qU3JuOyRaXnHDwyGlbVIzc4pa/DUmja7g/s5QfJIoFU3ye4Wq3377lCPnEa4DBQ3jhM5iMCBAoGBANr+3iu/yY6nROhQVb5SrG7VcBMvyavYRtnAZWfVcGTMKdct69MZxAJhZxm4RW4jUcZzhzKsRkApVUP/gi3mdgqH3LUG2OmqYOgWSuH0E6hVf273HsKJfrghvD/wg8hn3TRVJmCz52H6oo0cuuH6Odd88Tqf07NLmQpFW41XDWWrAoGBANjOQBXzx72iouv010Fw9EELk+XByvSFmMwk4YKLSg+AdAMfH8MvsGgNSmoEEtOQl527Twpg05q+NoWaT77KIHZUZTjbRAUaswR7CT5uMJjOxnyDm++i8VKJ6sG1FxCcvnEaz1u4efCrLVqbK5GKPSnR8xyJx7VAdlLCB/J/dAFBAoGAedi05MKg8q4+uMN58ZsuNbyrzwEXxHVhdmaGBW/MSUkPPppeS+ZaGLj5FGZiuxULus8suhUAQVK+Dkdrtv4zT0iolFBrABe8M2Wz5GRZS5/Gd4cnpjW6O9kJVMoNiMPBYAzAfa2bX/iD2N/TW0hORodN8MBcmbXGQOC2P73fxmECgYBCc1zzHYgQGKQk/CNp3GwQ77KCDlbdgYEmuPshnv2xKKbmOgjrM1e3XLN9MQhwLfY6kymTvb+9wyVE59ofWSZ//jgUKCh+BAPwkKFxsCZW/7GYgmIuHdwndzwr6QxLvC8mzZfWvgEqAd1h0wOUlTFP+xivm49Jf5uEnBIBgo0UwQKBgQCmo9LIa/xDwfEOfmFG/F3b9oQbSDiVWGsKVrWEqGIvMo0i4+zcnsDQVB7iKf6oH1oQ6AqDztDLXQxnZdh+7Zcq5U4o25q6ltc3az2HLS8ggWVDVrOnRbFJHcNAMJO74SNcZMvm0Gag3FbxYVDbk4hyLO3XE2/pPcfK8ZLPxJeA1Q==', //私钥
//            'alipayrsaPublicKey' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuXd7XjAEWVKRrGoKQFYB5Vxm5Io3fb6tsovb+pG6AkoFsmlWBn9NCf6MqaOWxgl205ImHBnHRxbP2d/PzXZB3+Ng7gGzw4tipoT0VRkXPab93ZXKE+mgFvr/GZXOR3jsLNflqnu7zTvDNX8HvqeUBtndLb4nteHNXMEeNqT4m/8mO7DlQ7DJ64Dbd0L0WA4KR13w4h7R1dqMtgv5U6mIInszEhEdiGjOnB+VrL8EoY3LC5Hrn0rB4BNMDJ8hlao2jZPItVeI9E+Z0VhiX14HUJ5OeMWCPszl3FerkfrLr+NfFr+wdBBKMy3OJcHImcwisM+j6yvRI0X1OqxiDkh7awIDAQAB',  //公钥
//            'out_trade_no'   => $vo->getOrderNo(), //订单号
//            'subject'        => $vo->getTitle(),  //商品名称
//            'total_amount'   => $vo->getFee(), //金额
//            'body'           => $vo->getBody(),  //商品介绍
//            'product_code'   => 'QUICK_MSECURITY_PAY',
//            'timeout_express'=> '30m'
//        );
//        #对数组进行排序
//        $param = $this->argSort($param);
//        switch ($vo->getPayMethod()) {
//            case 'direct':
//                #把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
//                $arg = $this->createLinkstring($param);
//                $param['sign'] = md5($arg . $this->config['key']);
//                $param['sign_type'] = 'MD5';
//                $sHtml = $this->_buildForm($param, $this->gateway, 'get');
//                break;
//            case 'mobile':
//                #把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串 并对字符串做urlencode编码
//                $arg = $this->createLinkstring($param);
//                $param['sign'] = $this->sign($arg);  //私钥
//                //$sHtml['arg']  = $arg."&sign=".urlencode($param['sign'])."&sign_type=RSA";
//                $sHtml['arg']  = $arg."&sign=".urlencode($param['sign'])."&sign_type=RSA2";
//                $sHtml['sign'] = $param['sign'];
//                $sHtml['out_trade_no'] = $vo->getOrderNo();
//                break;
//        }
//        return $sHtml;
//    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    protected function createLinkstring($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key."=".$val."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);
        
        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
        
        return $arg;
    }
    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    protected function createLinkstringUrlencode($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key."=".urlencode($val)."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);
        
        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
        return $arg;
    }
    /**
     * 除去数组中的空值和签名参数
     * @param $para 签名参数组
     * return 去掉空值与签名参数后的新签名参数组
     */
    protected function paraFilter($para) {
        $para_filter = array();
        while (list ($key, $val) = each ($para)) {
            if($key == "sign" || $key == "sign_type" || $val == "")continue;
            else    $para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }
    /**
     * 对数组排序
     * @param $para 排序前的数组
     * return 排序后的数组
     */
    protected function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }

    //RSA签名
    public function sign($data) {
        //读取私钥文件
        $priKey = file_get_contents("./Application/Sdk/SecretKey/alipay/rsa_private_key.pem");//私钥文件路径
        //转换为openssl密钥，必须是没有经过pkcs8转换的私钥
        $res = openssl_get_privatekey($priKey);
        //$res = openssl_pkey_get_private($priKey);
        //调用openssl内置签名方法，生成签名$sign
        openssl_sign($data, $sign, $res);
        //释放资源
        openssl_free_key($res);
        //base64编码
        $sign = base64_encode($sign);
        return $sign;
    }

    //验签
    public function rsa_verify($data, $sign) {
        // 读取公钥文件
        $pubKey = file_get_contents("./Application/Sdk/SecretKey/alipay/rsa_public_key.pem");//私钥文件路径
        //$pubKey = file_get_contents("./Application/Sdk/SecretKey/alipay/ali_public_key.pem");//私钥文件路径
        // 转换为openssl格式密钥
        $res = openssl_get_publickey($pubKey);
        // 调用openssl内置方法验签，返回bool值
        $result = ( bool ) openssl_verify ( $data, base64_decode ( $sign ), $res );
        // 释放资源
        openssl_free_key ( $res );   
        return $result;
    }

    /**
     * 获取返回时的签名验证结果
     * @param $para_temp 通知返回来的参数数组
     * @param $sign 返回的签名结果
     * @return 签名验证结果
     */
    protected function getSignVeryfy($param, $sign) {
        
        $param_filter = array();
        #除去待签名参数数组中的空值和签名参数
        $param_filter = $this->paraFilter($param);
        #对数组排序
        $param_filter = $this->argSort($param_filter);

        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($param_filter);
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $isSgin = false;
        switch (strtoupper(trim($param['sign_type']))) {
            case "MD5" :
                $mysgin = MD5($prestr . $this->config['key']);
                $isSgin = $mysgin == $sign ? true:false;
                break;
            case "RSA" :
                $isSgin = $this->rsa_verify($prestr,$sign);
                break;
            default :
                $isSgin = false;
        }
        return $isSgin;
    }

    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
    public function verifyNotify($notify) {
        //生成签名结果
        $isSign = $this->getSignVeryfy($notify, $notify["sign"]);
        //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
        $responseTxt = 'true';
        if (!empty($notify["notify_id"])) {
            $responseTxt = $this->getResponse($notify["notify_id"]);
        }

        if (preg_match("/true$/i", $responseTxt) && $isSign) {
            $this->setInfo($notify);
            return true;
        } else {
            return false;
        }
    }

    protected function setInfo($notify) {
        $info = array();
        //支付状态
        $info['status'] = ($notify['trade_status'] == 'TRADE_FINISHED' || $notify['trade_status'] == 'TRADE_SUCCESS') ? true : false;
        $info['money']  = $notify['total_fee'];
        $info['trade_no'] = $notify['trade_no'];
        $info['out_trade_no'] = $notify['out_trade_no'];
        $this->info = $info;
    }

    /**
     * 获取远程服务器ATN结果,验证返回URL
     * @param $notify_id 通知校验ID
     * @return 服务器ATN结果
     * 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空 
     * true 返回正确信息
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
    protected function getResponse($notify_id) {
        $partner = $this->config['partner'];
        $veryfy_url = $this->verify_url . "?partner=" . $partner . "&notify_id=" . $notify_id;
        $responseTxt = $this->fsockOpen($veryfy_url);
        return $responseTxt;
    }

}
