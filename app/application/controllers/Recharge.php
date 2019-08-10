<?php
use Helper\Page;
use Helper\Idcard;
require_once APP_PATH.'/alipay_app/aop/AopClient.php';
require_once APP_PATH.'/alipay_app/aop/request/AlipayTradeAppPayRequest.php';
class RechargeController extends BaseController{
    public $u_id;
    //支付宝公钥
    const alipay_public_key = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAkTerLUqqWaVGNTID0Di4Oni5XpX4QXbDySqXdC4/Yr++zknCHroxneEZMcLCt1qfjTG9N0ZlSWTC3SYwaYZvFagsNxk1og/v3kEyEfg29yTHPyI6OmKTQo4U/sejkxcLaYnp1VMiBnsiPv8ocE+S+4q0DKB9xSgj5iIVEbUL6xN7EBIpjN6PHr2ap3m2KkSil3ORocsFFhejz6kLNoWJQrqL31fUK1RTuavf9lWB34tJaJovE1Bf6G2F4+T2nxK0FzfUXAhblsK8qHG/4XNx0F39qtjauqQa91iDGHa6hgpqcYj0mr3haceSUP73m71R7bmIwfHZ89cyBIHoPNGTqwIDAQAB";
    //商户私钥
    const merchant_private_key = "MIIEpAIBAAKCAQEAuXd7XjAEWVKRrGoKQFYB5Vxm5Io3fb6tsovb+pG6AkoFsmlWBn9NCf6MqaOWxgl205ImHBnHRxbP2d/PzXZB3+Ng7gGzw4tipoT0VRkXPab93ZXKE+mgFvr/GZXOR3jsLNflqnu7zTvDNX8HvqeUBtndLb4nteHNXMEeNqT4m/8mO7DlQ7DJ64Dbd0L0WA4KR13w4h7R1dqMtgv5U6mIInszEhEdiGjOnB+VrL8EoY3LC5Hrn0rB4BNMDJ8hlao2jZPItVeI9E+Z0VhiX14HUJ5OeMWCPszl3FerkfrLr+NfFr+wdBBKMy3OJcHImcwisM+j6yvRI0X1OqxiDkh7awIDAQABAoIBAQCavqdPce7e/DaRTbSZ82kHju5Gt1APeb4BoBH94gL6D/rq3lqpdyO3OAzzKYwOVi0v39wuTA/qL41i8wu2GXpjLJteWks715uK5pnaOuIaTa+5Z1ZBAQfSxL9+AHEpTyp3S/fTJAQQ/FEm3IOAvt+SS8rwdJ07c1hekL79xu2rcW899NnF0ZCH+V94mbsE8Kyhg2WM7DDUM6LMA+8yIOpC4blEQhWTIGRJR3JuS79OeoJcn7fLQEQmjyH3WWJN1AEgHvTekyG8USV/qU3JuOyRaXnHDwyGlbVIzc4pa/DUmja7g/s5QfJIoFU3ye4Wq3377lCPnEa4DBQ3jhM5iMCBAoGBANr+3iu/yY6nROhQVb5SrG7VcBMvyavYRtnAZWfVcGTMKdct69MZxAJhZxm4RW4jUcZzhzKsRkApVUP/gi3mdgqH3LUG2OmqYOgWSuH0E6hVf273HsKJfrghvD/wg8hn3TRVJmCz52H6oo0cuuH6Odd88Tqf07NLmQpFW41XDWWrAoGBANjOQBXzx72iouv010Fw9EELk+XByvSFmMwk4YKLSg+AdAMfH8MvsGgNSmoEEtOQl527Twpg05q+NoWaT77KIHZUZTjbRAUaswR7CT5uMJjOxnyDm++i8VKJ6sG1FxCcvnEaz1u4efCrLVqbK5GKPSnR8xyJx7VAdlLCB/J/dAFBAoGAedi05MKg8q4+uMN58ZsuNbyrzwEXxHVhdmaGBW/MSUkPPppeS+ZaGLj5FGZiuxULus8suhUAQVK+Dkdrtv4zT0iolFBrABe8M2Wz5GRZS5/Gd4cnpjW6O9kJVMoNiMPBYAzAfa2bX/iD2N/TW0hORodN8MBcmbXGQOC2P73fxmECgYBCc1zzHYgQGKQk/CNp3GwQ77KCDlbdgYEmuPshnv2xKKbmOgjrM1e3XLN9MQhwLfY6kymTvb+9wyVE59ofWSZ//jgUKCh+BAPwkKFxsCZW/7GYgmIuHdwndzwr6QxLvC8mzZfWvgEqAd1h0wOUlTFP+xivm49Jf5uEnBIBgo0UwQKBgQCmo9LIa/xDwfEOfmFG/F3b9oQbSDiVWGsKVrWEqGIvMo0i4+zcnsDQVB7iKf6oH1oQ6AqDztDLXQxnZdh+7Zcq5U4o25q6ltc3az2HLS8ggWVDVrOnRbFJHcNAMJO74SNcZMvm0Gag3FbxYVDbk4hyLO3XE2/pPcfK8ZLPxJeA1Q==";
    //支付宝网关
    const gatewayUrl = "https://openapi.alipay.com/gateway.do";
    //应用ID
    const app_id = "2018072060681440";
    const signkey="MDc2OWJkYWI0ZGJiMmMxMzBjNzA3MGQ5NTU0MDVkODE=";
    public function init(){
        parent::init();
       if(empty($this->input['id'])){
            $this->ajax_return(1013);
        }else{
            $this->u_id = $this->input['id'];
        }
    }
    //支付宝支付
    public function alipay($body = "", $subject = "", $out_trade_no, $total_amount)
    {
         $jsonData = str_replace("\\/", "/", json_encode([
            'timeout_express'=>'30m',
            'product_code'=>"QUICK_MSECURITY_PAY",
            'total_amount'=>$total_amount,
            'subject'=>$subject,
            'body'=>$body,
            'out_trade_no'=>$out_trade_no],JSON_UNESCAPED_UNICODE));
        $payData['charset'] = "utf-8";
        $payData['biz_content'] = $jsonData;
        $payData['method'] = "alipay.trade.app.pay";
        $payData['app_id'] = self::app_id;
        $payData['sign_type'] = "RSA2";
        $payData['version'] = '1.0';
        $payData['notify_url'] = "http://" . $_SERVER['HTTP_HOST'] . "/callback/alipaynotify";
        $payData['timestamp'] = date("Y-m-d H:i:s",time());
        #对数组进行排序
        $param = $this->argSort($payData);
        $arg = $this->createLinkstring($param);
        $payData['sign'] = urlencode($this->sign2($arg));
        $datax = $this->createLinkstring($payData);
        //返回给SDK控制器
        $sHtml['arg']  = $datax;
        $sHtml['sign'] = $this->sign2($arg);
        $sHtml['out_trade_no'] = $out_trade_no;
        return $sHtml;
    }
    public function payAction(){
        if(self::signkey!==$this->input['key'])
        {
           $this->ajax_return(100);
        }
        $payway=$this->input['payway'];
        $payamount=$this->input['pay_amount'];
        $game_id=$this->input['game_id'];
        switch ($payway){
            case 'alipay':
                $deposit_data['pay_way']=1;
                break;
            case 'weixin':
                $deposit_data['pay_way']=2;
                break;
        }
        if(empty($payway)||empty($payamount))
        {
            $this->ajax_return(102);
        }
        $user=$this->db->field("account,nickname,promote_id,promote_account")->table("tab_user")->where("id = {$this->u_id} ")->find();
        //平台币充值
        $prefix = "PF_";
        $out_trade_no = $prefix . $this->build_order_no();
        $deposit_data['order_number'] = "";
        $deposit_data['pay_order_number'] = $out_trade_no;
        $deposit_data['user_id'] = $this->u_id;
        $deposit_data['user_account'] = $user['account'];
        $deposit_data['user_nickname'] = $user['nickname'];
        $deposit_data['promote_id'] = $user['promote_id'];
        $deposit_data['promote_account'] = $user['promote_account'];
        $deposit_data['pay_amount'] = $payamount;
        $deposit_data['pay_status'] = 0;
        $deposit_data['pay_ip'] = $this->getIp();
        $deposit_data['pay_source'] = 3;
        $deposit_data['create_time'] = time();
        $bool = $this->db->action($this->db->insertSql("deposit", $deposit_data));
        if($bool)
        {
            $body="充值平台币";
            $subject="充值平台币";
            if(!empty($game_id))
            {
                $game_name=$this->db->field("game_name")->table("tab_game")->where("id = {$game_id} ")->find();
                $body="充值".$game_name['game_name'];;
                $subject="充值".$game_name['game_name'];;
                $data['pay_order_number']=$out_trade_no;
                $data['game_id']=$game_id;
                $data['game_name']=$game_name['game_name'];
                $this->db->action($this->db->insertSql("deposit_game", $data));
            }
            if($payway=="alipay") {
                $aliPayString = $this->alipay($body, $subject, $deposit_data['pay_order_number'],$payamount);
                $this->ajax_return(0,$aliPayString);
            }
            else{
                $this->wx_pay($body,$deposit_data['pay_order_number'],$payamount);
            }
        }
        else{
            $this->ajax_return(500);
        }

    }

    //微信支付
    public function wx_pay($body = "", $out_trade_no, $total_amount) {

        $nonce_str = $this->rand_code();        //调用随机字符串生成方法获取随机字符串
        $data['appid'] ='wxe3008dfb690f3485';   //appid
        $data['mch_id'] = '1512798391' ;        //商户号
        $data['body'] = $body;
        $data['spbill_create_ip'] = $this->getIp();  //ip地址
        $data['total_fee'] = $total_amount*100;                         //金额
        $data['out_trade_no'] = $out_trade_no;    //商户订单号,不能重复
        $data['nonce_str'] = $nonce_str;                   //随机字符串
        $data['notify_url'] = "http://" . $_SERVER['HTTP_HOST'] . "/callback/wx_notify";   //回调地址,用户接收支付后的通知,必须为能直接访问的网址,不能跟参数
        $data['trade_type'] = 'APP';      //支付方式
        //将参与签名的数据保存到数组  注意：以上几个参数是追加到$data中的，$data中应该同时包含开发文档中要求必填的剔除sign以外的所有数据
        $data['sign'] = $this->getSign($data);        //获取签名
        $xml = $this->ToXml($data);            //数组转xml
        //curl 传递给微信方
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        //header("Content-type:text/xml");
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }    else    {
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        }
        //设置header
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        //传输文件
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            //返回成功,将xml数据转换为数组.
            //file_put_contents(APP_PATH."/log.txt",$data."\r\n",FILE_APPEND);//确认是支付宝回调的
            $re = $this->FromXml($data);
            if($re['return_code'] != 'SUCCESS'){
                 echo json_encode(['code'=>201,'message'=>"签名错误"]);
            }
            else{
                //接收微信返回的数据,传给APP!
                $arr =array(
                    'prepayid' =>$re['prepay_id'],
                    'appid' => 'wxe3008dfb690f3485',
                    'partnerid' => '1512798391',
                    'package' => 'Sign=WXPay',
                    'noncestr' => $nonce_str,
                    'timestamp' =>time(),
                );
                //第二次生成签名
                $sign = $this->getSign($arr);
                $arr['sign'] = $sign;
                 echo json_encode(['code'=>0,'message'=>"success","data"=>$arr]);
            }
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            echo json_encode(['code'=>202,'message'=>"curl出错，错误码:$error"]);
        }
    }

}