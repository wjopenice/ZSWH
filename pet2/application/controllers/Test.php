<?php
use Yaf\Application;
use Yaf\Dispatcher;
class TestController extends Yaf\Controller_Abstract{

    //下单API
    public function payAction(){
        $app_id = "sou0g5fq39nw";//商户号
        $appSecret = "oMqUe1gIeh2VomnqzESg2EGSHALXVKgM"; //密钥
        $url = "https://uat.entry.one/soapi/pay/unifiedorder"; //接口地址
        date_default_timezone_set("UTC"); //UTC时间

        $nonce_str = rand(0,32); //随机字符串
        $m_user_id = $app_id; //商户用户标识
        $m_user_nick = ""; //商户昵称
        $body = "测试"; //商品描述
        $attach = ""; //附加数据
        $out_trade_no = $this->buildorderno(); //订单号
        $fee_type = "CNY"; //货币类型
        $total_fee = "0.01"; //金额
        $time_start = date("YmdHis",time()); //订单生成时间
        $time_expire = date("YmdHis",time()+600); //订单失效时间
        $withdraw_address = ""; //实现提现地址
        $notify_url = ""; //回掉地址
        $trade_type = "H5"; //支付类型

        $request = str_replace("\\/", "/", json_encode([
            'app_id'=>$app_id,
            'nonce_str'=>$nonce_str,
            'm_user_id'=>$m_user_id,
            'm_user_nick'=>$m_user_nick,
            'body'=>$body,
            'attach'=>$attach,
            'out_trade_no'=>$out_trade_no,
            'fee_type'=>$fee_type,
            'total_fee'=>$total_fee,
            'time_start'=>$time_start,
            'time_expire'=>$time_expire,
            'withdraw_address'=>$withdraw_address,
            'notify_url'=>$notify_url,
            'trade_type'=>$trade_type],JSON_UNESCAPED_UNICODE));

        $content = trim($request);
        $sign = $this->hashhmac256($content.$appSecret,$appSecret);  //验签

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json','Content-Length: ' . strlen($request),'XPaySign:'.$sign) );
        curl_setopt($ch, CURLOPT_POSTFIELDS , $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        var_dump($output);
        curl_close($ch);

        exit;
    }
    //回掉地址
    public function notifyAction(){

    }
    //生成订单号
    public function buildorderno(){
        return "X".date('YmdHis').substr(implode(array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8)."pay";
    }
    //hashhmac256加密
    public function hashhmac256($data,$key){
       return strtolower(base64_encode(hash_hmac("sha256",$data,$key)));
    }
    //请求接口
    public function request_post($url = '', $post_data = array()) {
        if (empty($url) || empty($post_data)) {return false;}
        $postUrl = $url;
        $curlPost = $post_data;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        return $data;
    }
}