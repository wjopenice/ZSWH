<?php
namespace Callback\Controller;
use Org\Itppay\itppay;
/**
 * 支付回调控制器
 * @author 小纯洁 
 */
class NotifyController extends BaseController {
    const APPID = "155918185956762";
    const KEY = "LfY7iGPUdy2qo8H2Y1vsxYIhmqj1lNvD";
    const TIMEZONE = "Asia/Shanghai";
    const TIMEOUT = "3600";
    const FRONT_URL = "http://www.zhishengwh.com/callback/notify/mininotify/"; //缺少前台通知地址
    const NOTIFY_URL = "http://www.zhishengwh.com/callback/notify/mininotify/";
    const CURRENCYTYPE = "156";
    const CHARSET = "UTF-8";
    const DEVICE_TYPE = "01";
    const SIGN_TYPE = "MD5";
    const TRADE_URL = "https://pay.ipaynow.cn";

    /**
     * Sdk异步通知
     */
    public function sdknotify(){
        $data = $_POST;
        file_put_contents("./log/log1.txt","order ".json_encode($data)."\r\n",FILE_APPEND);
        if ($data['trade_status'] == 'TRADE_SUCCESS') {
            //订单号
            $out_trade_no = $data['out_trade_no'];  //我们的订单号
            $pay_where = substr($out_trade_no,0,2);
            $trade_no = $data['trade_no'];  //支付宝订单号
            switch ($pay_where) {
                case 'SP':
                    //获取数据库信息（支付宝）
                    $spend = M('Spend',"tab_");
                    $cpdata = $spend->where(['pay_order_number'=>$out_trade_no])->find();
                    if(!empty($cpdata)){
                        $gameappid = $cpdata['game_appid']; //游戏appid
                        $rescp['gameid'] = $cpdata['game_id']; //cp游戏
                        $rescp['extend'] = $cpdata['extend']; //cp订单号
                        $rescp['total_amount'] = $cpdata['pay_amount']; //支付金额
                        $rescp['props_name'] = $cpdata['props_name']; //商品名称
                        $cpsignstr = json_encode($rescp);
                        $rescp['pay_status'] = 1; //支付状态
                        $rescp['sign'] = hash_hmac("sha256",$cpsignstr,$gameappid);
                        //获取cp回掉地址
                        $cpnoturl = M('Game_set',"tab_")->field("pay_notify_url")->where(['game_id'=>$cpdata['game_id']])->find();
                        if(!empty($cpnoturl['pay_notify_url'])){
                            $noturl = $cpnoturl['pay_notify_url'];
                            //修改订单状态
                            $data_save['pay_status'] = 1;
                            $data_save['pay_game_status'] = 1;
                            $data_save['order_number'] = $trade_no;
                            $map_s['pay_order_number'] = $out_trade_no;
                            $bool = $spend->where($map_s)->save($data_save);
                            try{
                                $coupon=M('User_coupon',"tab_")->where($map_s)->find();
                                if(!empty($coupon)) {
                                    $user_coupon['status'] = 1;
                                    M('User_coupon', 'tab_')->where($map_s)->save($user_coupon);
                                }
                            }catch (\Exception $e){
                                file_put_contents("./log/log4.txt","代金卷错误====---\r\n",FILE_APPEND);
                            }
                            if($bool!== false){
                                //通知CP,5次间隔5秒
                                for($i=0;$i<5;$i++){
                                    $rescp['uid'] = $cpdata['user_id'];
                                    $this->post($rescp,$noturl);
                                    sleep(5);
                                    file_put_contents("./log/log3.txt",json_encode($rescp)."order response cp ok.-".$noturl."--\r\n",FILE_APPEND);
                                }
                                unset($rescp['uid']);
                            }else{
                                $this->record_logs("修改数据失败");
                            }
                        }else{
                            $this->record_logs("未找到回掉信息信息！");
                            exit;
                        }
                    }else{
                        $this->record_logs("未找到下单信息！");
                        exit;
                    }
                    break;
                case 'PF':
                    //获取数据库信息（平台币）
                    $deposit = M('Deposit',"tab_");
                    $sdkdata = $deposit->where(['pay_order_number'=>$out_trade_no])->find();
                    if(!empty($sdkdata)){
                        if($sdkdata['pay_status'] != 1){
                            $ressdk['user_id'] = $sdkdata['user_id']; //用户ID
                            $ressdk['user_account'] = $sdkdata['user_account']; //用户账号
                            $ressdk['total_amount'] = $sdkdata['pay_amount']; //支付金额
                            $ressdk['pay_status'] = 1; //支付状态
                            //修改订单状态
                            $data_save_sdk['pay_status'] = 1;
                            $data_save_sdk['order_number'] = $trade_no;
                            $map_sdk['pay_order_number'] = $out_trade_no;
                            $boolsdk = $deposit->where($map_sdk)->save($data_save_sdk);
                            //修改用户金额
                            $userbal= M("User","tab_")->field("balance")->where(['account'=>$sdkdata['user_account']])->find();
                            $userfee = $userbal['balance'] + $sdkdata['pay_amount'];
                            M("User","tab_")->where(['id'=>$sdkdata['user_id']])->save(['balance'=>$userfee]);
                            if($boolsdk!== false){
                                file_put_contents("./log/log4.txt",json_encode($ressdk)."order response sdk ok--balance".$userbal['balance']."==".$sdkdata['pay_amount']."====---\r\n",FILE_APPEND);
                                unset($userbal['balance']);
                                unset($sdkdata['pay_amount']);
                            }else{
                                $this->record_logs("修改数据失败");
                                exit;
                            }
                        }else{
                            file_put_contents("./log/log5.txt","alipay order response sdk ok 11111 \r\n",FILE_APPEND);
                        }
                    }else{
                        $this->record_logs("未找到下单信息！");
                        exit;
                    }
                    break;
                default:break;
            }
        }else{
            $this->record_logs("支付失败！");
            exit;
        }
    }
    /**
     * Sdk官方微信异步通知
     */
    public function wxnotify(){
        //接收微信返回的数据数据,返回的xml格式
        $xmlData = file_get_contents('php://input');
        //将xml格式转换为数组
        $data = $this->FromXml($xmlData);
        //用日志记录检查数据是否接受成功，验证成功一次之后，可删除。
        file_put_contents("./log/log1.txt","order ".json_encode($data)."\r\n",FILE_APPEND);
        //为了防止假数据，验证签名是否和返回的一样。
        //记录一下，返回回来的签名，生成签名的时候，必须剔除sign字段。
        $sign = $data['sign'];
        unset($data['sign']);
        if($sign == $this->getSign($data)){
            //签名验证成功后，判断返回微信返回的
            if ($data['result_code'] == 'SUCCESS' ) {
                //存在该订单并支付成功，进行修改
                //根据返回的订单号做业务逻辑
                //处理完成之后，告诉微信成功结果！
                //订单号
                $out_trade_no = $data['out_trade_no'];  //我们的订单号
                $pay_where = substr($out_trade_no,0,2);
                $trade_no = $data['transaction_id'];  //支付宝订单号
                switch ($pay_where) {
                    case 'SP':
                        //获取数据库信息（支付宝）
                        $spend = M('Spend',"tab_");
                        $cpdata = $spend->where(['pay_order_number'=>$out_trade_no])->find();
                        if(!empty($cpdata)){
                            $gameappid = $cpdata['game_appid']; //游戏appid
                            $rescp['gameid'] = $cpdata['game_id']; //cp游戏
                            $rescp['extend'] = $cpdata['extend']; //cp订单号
                            $rescp['total_amount'] = $cpdata['pay_amount']; //支付金额
                            $rescp['props_name'] = $cpdata['props_name']; //商品名称
                            $cpsignstr = json_encode($rescp);
                            $rescp['pay_status'] = 1; //支付状态
                            $rescp['sign'] = hash_hmac("sha256",$cpsignstr,$gameappid);
                            //获取cp回掉地址
                            $cpnoturl = M('Game_set',"tab_")->field("pay_notify_url")->where(['game_id'=>$cpdata['game_id']])->find();
                            if(!empty($cpnoturl['pay_notify_url'])){
                                $noturl = $cpnoturl['pay_notify_url'];
                                //修改订单状态
                                $data_save['pay_status'] = 1;
                                $data_save['pay_game_status'] = 1;
                                $data_save['order_number'] = $trade_no;
                                $map_s['pay_order_number'] = $out_trade_no;
                                $bool = $spend->where($map_s)->save($data_save);
                                try{
                                    $coupon=M('User_coupon',"tab_")->where($map_s)->find();
                                    if(!empty($coupon)) {
                                        $user_coupon['status'] = 1;
                                        M('User_coupon', 'tab_')->where($map_s)->save($user_coupon);
                                    }
                                }catch (\Exception $e){
                                    file_put_contents("./log/log4.txt","代金卷错误====---\r\n",FILE_APPEND);
                                }
                                if($bool!== false){
                                    //通知CP,5次间隔5秒
                                    for($i=0;$i<5;$i++){
                                        $rescp['uid'] = $cpdata['user_id'];
                                        $this->post($rescp,$noturl);
                                        sleep(5);
                                        file_put_contents("./log/log3.txt",json_encode($rescp)."order response cp ok.-".$noturl."--\r\n",FILE_APPEND);
                                    }
                                    unset($rescp['uid']);
                                }else{
                                    $this->record_logs("修改数据失败");
                                }
                            }else{
                                $this->record_logs("未找到回掉信息信息！");
                                exit;
                            }
                        }else{
                            $this->record_logs("未找到下单信息！");
                            exit;
                        }
                        break;
                    case 'PF':
                        //获取数据库信息（平台币）
                        $deposit = M('Deposit',"tab_");
                        $sdkdata = $deposit->where(['pay_order_number'=>$out_trade_no])->find();
                        if(!empty($sdkdata)){
                            if($sdkdata['pay_status'] != 1){
                                $ressdk['user_id'] = $sdkdata['user_id']; //用户ID
                                $ressdk['user_account'] = $sdkdata['user_account']; //用户账号
                                $ressdk['total_amount'] = $sdkdata['pay_amount']; //支付金额
                                $ressdk['pay_status'] = 1; //支付状态
                                //修改订单状态
                                $data_save_sdk['pay_status'] = 1;
                                $data_save_sdk['order_number'] = $trade_no;
                                $map_sdk['pay_order_number'] = $out_trade_no;
                                $boolsdk = $deposit->where($map_sdk)->save($data_save_sdk);
                                //修改用户金额
                                $userbal= M("User","tab_")->field("balance")->where(['account'=>$sdkdata['user_account']])->find();
                                $userfee = $userbal['balance'] + $sdkdata['pay_amount'];
                                M("User","tab_")->where(['id'=>$sdkdata['user_id']])->save(['balance'=>$userfee]);
                                if($boolsdk!== false){
                                    file_put_contents("./log/log4.txt",json_encode($ressdk)."order response sdk ok--balance".$userbal['balance']."==".$sdkdata['pay_amount']."====---\r\n",FILE_APPEND);
                                    unset($userbal['balance']);
                                    unset($sdkdata['pay_amount']);
                                }else{
                                    $this->record_logs("修改数据失败");
                                    exit;
                                }
                            }else{
                                file_put_contents("./log/log5.txt","alipay order response sdk ok 11111 \r\n",FILE_APPEND);
                            }
                        }else{
                            $this->record_logs("未找到下单信息！");
                            exit;
                        }
                        break;
                    default:break;
                }
                    echo '<xml>
              <return_code><![CDATA[SUCCESS]]></return_code>
              <return_msg><![CDATA[OK]]></return_msg>
              </xml>';exit();

            }
            //支付失败，输出错误信息
            else{
                $this->record_logs("微信支付错误信息：".$data['return_msg'].date("Y-m-d H:i:s"));
               }
        }
        else{
            $this->record_logs("微信支付错误信息：签名验证失败：".$data['return_msg'].date("Y-m-d H:i:s"));
        }

    }
    /**
    *通知方法
    */
    public function notify()
    {
        file_put_contents("./log/log2.txt","order ".json_encode($_GET)."\r\n",FILE_APPEND);

        $apitype = I('get.apitype');#获取支付api类型
        if (IS_POST && !empty($_POST)) {
            $notify = $_POST;
        } elseif (IS_GET && !empty($_GET)) {
            $notify = $_GET;
            unset($notify['method']);
            unset($notify['apitype']);
        } else {
            $notify = file_get_contents("php://input");
            if(empty($notify)){
                $this->record_logs("Access Denied");
                exit('Access Denied');
            }
        }

        $pay_way = $apitype;
        if($apitype == "swiftpass"){$apitype = "weixin";}
        
        $pay = new \Think\Pay($pay_way, C($apitype));
        if ($pay->verifyNotify($notify)) {
            //获取回调订单信息
            $order_info = $pay->getInfo();

            if ($order_info['status']) {
                $pay_where = substr($order_info['out_trade_no'],0,2);
                $result = false;
                switch ($pay_where) {
                    case 'SP':
                        file_put_contents("./log/log1.txt","SP".time());
                        $result = $this->set_spend($order_info);
                        break;
                    case 'PF':
                        file_put_contents("./log/log1.txt","PF".time());
                        $result = $this->set_deposit($order_info);
                        break;
                    case 'AG':
                        $result = $this->set_agent($order_info); 
                        break;
                    case 'QD':
                        $result = $this->set_promoteDeposit($order_info);
                        break;
                    default:
                        exit('accident order data');
                        break;
                }
                if (I('get.method') == "return") {
                    //根据不同订单来源跳转对应的页面
                    switch ($pay_where) {
                        case 'SP':
                            redirect('http://'.$_SERVER['HTTP_HOST'].'/index.php?s=/Home/Promote/index.html');
                            break;
                        case 'PF':
                           redirect('http://'.$_SERVER['HTTP_HOST'].'/media.php?s=/Index/index.html');
                            break;
                        case 'AG':
                            redirect('http://'.$_SERVER['HTTP_HOST'].'/index.php?s=/Home/Promote/index.html');
                            break;
                        case 'QD':
                            redirect('http://'.$_SERVER['HTTP_HOST'].'/index.php?s=/Home/Promote/index.html');
                            break;
                        default:
                            redirect('http://'.$_SERVER['HTTP_HOST'].'/index.php?s=/Home/Promote/index.html');
                            break;
                    }
                   redirect('http://'.$_SERVER['HTTP_HOST'].'/index.php?s=/Home/Promote/index.html');
                } else {
                    $pay->notifySuccess();
                }
            }else{
                $this->record_logs("支付失败！");
            }
        }else{
            $this->record_logs("支付验证失败");
            redirect('http://'.$_SERVER['HTTP_HOST'].'/media.php',3,'支付验证失败');
        }
    }


    public function weixin_zl_notify(){
         /* *
         * 配置信息
         */
        $itppay_config["appid"]=C('weixin_zl.appid');//交易发起所属app
        $itppay_config["key"]=C('weixin_zl.key');//合作密钥

        /* *
         * 获取传递数据
         */
        $data = file_get_contents("php://input");
        $parameter = json_decode($data, true);
        $signature = $parameter["signature"];
        unset($parameter["signature"]);


        $order_info['trade_no']        =$parameter['orderNo'];
        $order_info['out_trade_no']    =$parameter['mchntOrderNo'];
        $pay_where = substr($order_info['out_trade_no'],0,2);
        /* *
         * 签名
         */
        $itpPay = new itpPay($itppay_config);
        $signature_local=$itpPay->setSignature($parameter);

        $logFile = fopen(dirname(__FILE__)."/log.txt", "a+");
        fclose(fopen(dirname(__FILE__)."log2.txt","w"));
        $logFile2 = fopen(dirname(__FILE__)."/log2.txt", "a+");
        if($signature && $signature == $signature_local){
            
            //$parameter["orderNo"]明天云平台生成的订单号
            //$parameter["mchntOrderNo"]商户订单号，可根据商户订单号查询商户网站中该订单信息，并执行业务处理
            //$parameter["orderDt"]下单日期
            //$parameter["paidTime"]订单支付完成时间
            //$parameter["extra"]附加数据
            //$parameter["paySt"]支付结果状态，0:待支付；1:支付中；2:支付成功；3:支付失败；4:已关闭
            
            switch($parameter["paySt"]){
                case 0:
                    fwrite($logFile, "[".$parameter["mchntOrderNo"]."]--->待支付\r\n");
                    fwrite($logFile2, "[".$parameter["mchntOrderNo"]."]--->待支付\r\n");
                    break;
                case 1:
                    fwrite($logFile, "[".$parameter["mchntOrderNo"]."]--->支付中\r\n");
                    fwrite($logFile2, "[".$parameter["mchntOrderNo"]."]--->支付中\r\n");
                    break;
                case 2:
                    fwrite($logFile, "[".$parameter["mchntOrderNo"]."]--->支付成功\r\n");
                    fwrite($logFile2, "[".$parameter["mchntOrderNo"]."]--->支付成功\r\n");
                    switch ($pay_where) {
                        case 'SP':
                            $result = $this->set_spend($order_info);
                            break;
                        case 'PF':
                            $result = $this->set_deposit($order_info);
                            break;
                        case 'AG':
                            $result = $this->set_agent($order_info); 
                            break;
                        case 'QD':
                            $result = $this->set_promoteDeposit($order_info);
                            break;
                        default:
                            exit('accident order data');
                            break;
                    }
                    break;
                case 3:
                    fwrite($logFile, "[".$parameter["mchntOrderNo"]."]--->支付失败\r\n");
                    fwrite($logFile2, "[".$parameter["mchntOrderNo"]."]--->支付失败\r\n");
                    break;
                case 4:
                    fwrite($logFile, "[".$parameter["mchntOrderNo"]."]--->已关闭\r\n");
                    fwrite($logFile2, "[".$parameter["mchntOrderNo"]."]--->已关闭\r\n");
                    break;
            }
            
            foreach($parameter as $k=>$v){
                fwrite($logFile, $k."=".$v."\r\n");
                fwrite($logFile2, $k."=".$v."\r\n");
            }
            fwrite($logFile, "\r\n\r\n");
            fwrite($logFile2, "\r\n\r\n");
            
        }else{
            fwrite($logFile, "[".$parameter["mchntOrderNo"]."]--->验签失败\r\n");
            fwrite($logFile2, "[".$parameter["mchntOrderNo"]."]--->验签失败\r\n");
        }
        fclose($logFile);
        fclose($logFile2);
        echo "{\"success\":\"true\"}";
    }


    public function weixin_zl_return(){
        /**
         * 获取异步通知日志
         */
        dump(2222);die();
    }


    function wite_text($txt,$name){
        $myfile = fopen($name, "w") or die("Unable to open file!");
        fwrite($myfile, $txt);
        fclose($myfile);
    }


    /**
     * @author Ping
     *
     * 用于接收异步通知
     */
    public function mininotify(){
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
            $out_trade_no = $Arr['mhtOrderNo'];  //我们的订单号
            $pay_where = substr($out_trade_no,0,2);
            $trade_no = $Arr['nowPayOrderNo'];  //支付宝订单号
            $extend=$Arr['channelOrderNo'];//渠道订单号
            switch ($pay_where) {
                case 'SP':
                    //获取数据库信息（支付宝）
                    $spend = M('Spend',"tab_");
                    $cpdata = $spend->where(['pay_order_number'=>$out_trade_no])->find();
                    if(!empty($cpdata)){
                        $gameappid = $cpdata['game_appid']; //游戏appid
                        $rescp['gameid'] = $cpdata['game_id']; //cp游戏
                        $rescp['extend'] = $cpdata['extend']; //cp订单号
                        $rescp['total_amount'] = $cpdata['pay_amount']; //支付金额
                        $rescp['props_name'] = $cpdata['props_name']; //商品名称
                        $cpsignstr = json_encode($rescp);
                        $rescp['pay_status'] = 1; //支付状态
                        $rescp['sign'] = hash_hmac("sha256",$cpsignstr,$gameappid);
                        //获取cp回掉地址
                        $cpnoturl = M('Game_set',"tab_")->field("pay_notify_url")->where(['game_id'=>$cpdata['game_id']])->find();
                        if(!empty($cpnoturl['pay_notify_url'])){
                            $noturl = $cpnoturl['pay_notify_url'];
                            //修改订单状态
                            $data_save['pay_status'] = 1;
                            $data_save['pay_game_status'] = 1;
                            $data_save['order_number'] = $trade_no;
                            $map_s['pay_order_number'] = $out_trade_no;
                            $bool = $spend->where($map_s)->save($data_save);
                            try{
                                $coupon=M('User_coupon',"tab_")->where($map_s)->find();
                                if(!empty($coupon)) {
                                    $user_coupon['status'] = 1;
                                    M('User_coupon', 'tab_')->where($map_s)->save($user_coupon);
                                }
                            }catch (\Exception $e){
                                file_put_contents("./log/log4.txt","代金卷错误====---\r\n",FILE_APPEND);
                            }
                            if($bool!== false){
                                //通知CP,5次间隔5秒
                                for($i=0;$i<5;$i++){
                                    $rescp['uid'] = $cpdata['user_id'];
                                    $this->post($rescp,$noturl);
                                    sleep(5);
                                    file_put_contents("./log/log3.txt",json_encode($rescp)."order response cp ok.-".$noturl."--\r\n",FILE_APPEND);
                                }
                                unset($rescp['uid']);
                            }else{
                                $this->record_logs("修改数据失败");
                            }
                        }else{
                            $this->record_logs("未找到回掉信息信息！");
                            exit;
                        }
                    }else{
                        $this->record_logs("未找到下单信息！");
                        exit;
                    }
                    break;
                case 'PF':
                    //获取数据库信息（平台币）
                    $deposit = M('Deposit',"tab_");
                    $sdkdata = $deposit->where(['pay_order_number'=>$out_trade_no])->find();
                    if(!empty($sdkdata)){
                        if($sdkdata['pay_status'] != 1){
                            $ressdk['user_id'] = $sdkdata['user_id']; //用户ID
                            $ressdk['user_account'] = $sdkdata['user_account']; //用户账号
                            $ressdk['total_amount'] = $sdkdata['pay_amount']; //支付金额
                            $ressdk['pay_status'] = 1; //支付状态
                            //修改订单状态
                            $data_save_sdk['pay_status'] = 1;
                            $data_save_sdk['order_number'] = $trade_no;
                            $map_sdk['pay_order_number'] = $out_trade_no;
                            $boolsdk = $deposit->where($map_sdk)->save($data_save_sdk);
                            //修改用户金额
                            $userbal= M("User","tab_")->field("balance")->where(['account'=>$sdkdata['user_account']])->find();
                            $userfee = $userbal['balance'] + $sdkdata['pay_amount'];
                            M("User","tab_")->where(['id'=>$sdkdata['user_id']])->save(['balance'=>$userfee]);
                            if($boolsdk!== false){
                                file_put_contents("./log/log4.txt",json_encode($ressdk)."order response sdk ok--balance".$userbal['balance']."==".$sdkdata['pay_amount']."====---\r\n",FILE_APPEND);
                                unset($userbal['balance']);
                                unset($sdkdata['pay_amount']);
                            }else{
                                $this->record_logs("修改数据失败");
                                exit;
                            }
                        }else{
                            file_put_contents("./log/log5.txt","alipay order response sdk ok 11111 \r\n",FILE_APPEND);
                        }
                    }else{
                        $this->record_logs("未找到下单信息！");
                        exit;
                    }
                    break;
                default:break;
            }
            echo 'success=Y';
        } else {
            //异步通知验签失败，在此处写业务逻辑
            $this->record_logs("回调失败！");
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
    static function minipost($url, $data)
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
    static function minilog($path, $Arr)
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