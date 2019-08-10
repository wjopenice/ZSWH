<?php
namespace Sdk\Controller;
use Think\Controller;
use Common\Api\GameApi;
class PayController extends BaseController{

    private function pay($param=array()){

        //限制去吧皮卡丘游戏充值
        if($param['game_id'] == "33" || $param['game_id'] == "112" || $param['game_id'] == "212" || $param['game_id'] == "224" || $param['game_id'] == "199" ) {
            echo "游戏暂停充值";exit();
        }
        $table  = $param['code'] == 1 ? "spend" : "deposit";
        $prefix = $param['code'] == 1 ? "SP_" : "PF_";
        $out_trade_no = $prefix.date('Ymd').date('His').sp_random_string(4);
        $user = get_user_entity($param['user_id']);
        switch ($param['apitype']) {
            case 'swiftpass':
                $pay  = new \Think\Pay($param['apitype'],$param['config']);
                break;

            default:
                $pay  = new \Think\Pay($param['apitype'],C($param['config']));
                break;
        }
        $param['pay_source'] = empty($param['pay_source'])?0:$param['pay_source'];
        $vo   = new \Think\Pay\PayVo();
<<<<<<< .mine
        $vo->setBody("充值记录描述")
            ->setFee($param['price'])//支付金额
            ->setPaySource($param['pay_source'])
            ->setTitle($param['title'])
            ->setBody($param['body'])
            ->setOrderNo($out_trade_no)
            ->setService($param['server'])
            ->setSignType($param['signtype'])
            ->setPayMethod("mobile")
            ->setTable($table)
            ->setPayWay($param['p ayway'])
            ->setGameId($param['game_id'])
            ->setGameName($param['game_name'])
            ->setGameAppid($param['game_appid'])
            ->setServerId(0)
            ->setServerName("")
            ->setUserId($param['user_id'])
            ->setAccount($user['account'])
            ->setUserNickName($user['nickname'])
            ->setPromoteId($user['promote_id'])
            ->setPromoteName($user['promote_account'])
            ->setExtend($param['extend'])
            ->setSdkVersion($param['sdk_version']);
=======
        if(isset($param['coupon_id']) && $table=="spend")
        {
            $total_amount=$param['credit']+$param['price'];
            $vo->setBody("充值记录描述")
                ->setFee($param['price'])//支付金额
                ->setTotalAmount($total_amount)//订单总额
                ->setCredit($param['credit'])//优惠券抵用金额
                ->setCouponId($param['coupon_id'])//优惠券ID
                ->setPaySource($param['pay_source'])
                ->setTitle($param['title'])
                ->setBody($param['body'])
                ->setOrderNo($out_trade_no)
                ->setService($param['server'])
                ->setSignType($param['signtype'])
                ->setPayMethod("mobile")
                ->setTable($table)
                ->setPayWay($param['payway'])
                ->setGameId($param['game_id'])
                ->setGameName($param['game_name'])
                ->setGameAppid($param['game_appid'])
                ->setServerId(0)
                ->setServerName("")
                ->setUserId($param['user_id'])
                ->setAccount($user['account'])
                ->setUserNickName($user['nickname'])
                ->setPromoteId($user['promote_id'])
                ->setPromoteName($user['promote_account'])
                ->setExtend($param['extend'])
                ->setSdkVersion($param['sdk_version']);
        }
        else{
            $vo->setBody("充值记录描述")
                ->setFee($param['price'])//支付金额
                ->setPaySource($param['pay_source'])
                ->setTitle($param['title'])
                ->setBody($param['body'])
                ->setOrderNo($out_trade_no)
                ->setService($param['server'])
                ->setSignType($param['signtype'])
                ->setPayMethod("mobile")
                ->setTable($table)
                ->setPayWay($param['payway'])
                ->setGameId($param['game_id'])
                ->setGameName($param['game_name'])
                ->setGameAppid($param['game_appid'])
                ->setServerId(0)
                ->setServerName("")
                ->setUserId($param['user_id'])
                ->setAccount($user['account'])
                ->setUserNickName($user['nickname'])
                ->setPromoteId($user['promote_id'])
                ->setPromoteName($user['promote_account'])
                ->setExtend($param['extend'])
                ->setSdkVersion($param['sdk_version']);
        }

>>>>>>> .r853
        return $pay->buildRequestForm($vo);
    }

    public function alipay(){
        #获取SDK上POST方式传过来的数据 然后base64解密 然后将json字符串转化成数组
        $request = json_decode(base64_decode(file_get_contents("php://input")),true);
        $game_set_data = get_game_set_info($request['game_id']);
        $request['apitype'] = "alipay";
        $request['config']  = "alipay";
        $request['signtype']= "MD5";
        $request['server']  = "mobile.securitypay.pay";
        $request['payway']  = 1;
        $data = $this->pay($request);
        $md5_sign = $this->encrypt_md5(base64_encode($data['arg']),$game_set_data["access_key"]);
        //回调给SDK
        $data = array("orderInfo"=>base64_encode($data['arg']),"out_trade_no"=>$data['out_trade_no'],"order_sign"=>$data['sign'],"md5_sign"=>$md5_sign);
        echo base64_encode(json_encode($data));
    }


    /**
     *支付宝移动支付
     */
    public function alipay_pay(){
        #获取SDK上POST方式传过来的数据 然后base64解密 然后将json字符串转化成数组
        $request = json_decode(base64_decode(file_get_contents("php://input")),true);
        $game_set_data = get_game_set_info($request['game_id']);

        $request['apitype'] = "alipay";
        $request['config']  = "alipay";
        $request['signtype']= "MD5";
        $request['server']  = "mobile.securitypay.pay";
        $request['payway']  = 1;
        $data = $this->pay($request);

        $md5_sign = $this->encrypt_md5(base64_encode($data['arg']),$game_set_data["access_key"]);
        $data = array("orderInfo"=>base64_encode($data['arg']),"out_trade_no"=>$data['out_trade_no'],"order_sign"=>$data['sign'],"md5_sign"=>$md5_sign);
        echo base64_encode(json_encode($data));
    }
    //微信支付
    public function wx_pay() {
    #获取SDK上POST方式传过来的数据 然后base64解密 然后将json字符串转化成数组
        $param = json_decode(base64_decode(file_get_contents("php://input")),true);
        if($param['game_id'] == "33" || $param['game_id'] == "112" || $param['game_id'] == "212" || $param['game_id'] == "224" || $param['game_id'] == "199" ) {
            echo "游戏暂停充值";exit();
        }
        $prefix = $param['code'] == 1 ? "SP_" : "PF_";
        $out_trade_no = $prefix.date('Ymd').date('His').sp_random_string(4);
        $user = get_user_entity($param['user_id']);
        $param['pay_source'] = empty($param['pay_source'])?0:$param['pay_source'];
        if($param['code'] == 1 ){
            $spend = M("spend","tab_");
            #TODO添加消费记录
            $spend_data['user_id']          = $param['user_id'];
            $spend_data['user_account']     = $user['account'];
            $spend_data['user_nickname']    = $user['nickname'];
            $spend_data['game_id']          = $param['game_id'];
            $spend_data['game_appid']       = $param['game_appid'];
            $spend_data['game_name']        = $param['game_name'];
            $spend_data['server_id']        = 0;
            $spend_data['server_name']      = "";
            $spend_data['promote_id']       = $user['promote_id'];
            $spend_data['promote_account']  = $user['promote_account'];
            $spend_data['order_number']     = "";
            $spend_data['pay_order_number'] = $out_trade_no;
            $spend_data['props_name']       =$param['title'];
            $spend_data['pay_amount']       = $param['price'];
            $spend_data['pay_way']          = 2;
            $spend_data['pay_time']         = time();
            $spend_data['pay_status']       = 0;
            $spend_data['pay_game_status']  = 0;
            $spend_data['extend']           = $param['extend'];
            $spend_data['spend_ip']         = get_client_ip();
            $result = $spend->add($spend_data);
            if($result){
                if(isset($param['coupon_id']))
                {
                    $total_amount=$param['credit']+$param['price'];
                    $user_coupon['pay_order_number']=$out_trade_no;
                    $user_coupon['status']=0;
                    $user_coupon['game_id']=$param['game_id'];
                    $user_coupon['game_name']= $param['game_name'];
                    $user_coupon['game_appid']=$param['game_appid'];
                    $user_coupon['promote_id']=$user['promote_id'];
                    $user_coupon['promote_account']  = $user['promote_account'];
                    $user_coupon['total_amount']=$total_amount;
                    $user_coupon['credit']=$param['credit'];
                    $mapchr['user_id'] = $param['user_id'];
                    $mapchr['coupon_id'] = $param['coupon_id'];
                    M('user_coupon','tab_')->where($mapchr)->save($user_coupon);

                }
            }
        }else{
            #TODO添加平台币充值记录
            $deposit = M("deposit","tab_");
            $deposit_data['order_number']     = "";
            $deposit_data['pay_order_number'] = $out_trade_no;
            $deposit_data['user_id']          = $param['user_id'];
            $deposit_data['user_account']     =  $user['account'];
            $deposit_data['user_nickname']    = $user['nickname'];
            $deposit_data['promote_id']       = $user['promote_id'];
            $deposit_data['promote_account']  = $user['promote_account'];
            $deposit_data['pay_amount']       = $param['price'];
            $deposit_data['reality_amount']   = $param['price'];
            $deposit_data['pay_status']       = 0;
            $deposit_data['pay_way']          =2;
            $deposit_data['pay_ip']           = get_client_ip();
            $deposit_data['pay_source']       =   $param['pay_source'];
            $deposit_data['create_time']      = time();
            $result = $deposit->add($deposit_data);
        }
        $nonce_str = $this->rand_code();        //调用随机字符串生成方法获取随机字符串
        $data['appid'] ='wxe3008dfb690f3485';   //appid
        $data['mch_id'] = '1512798391' ;        //商户号
        $data['body'] = $param['body'];
        $data['spbill_create_ip'] = get_client_ip();  //ip地址
        $data['total_fee'] = $param['price']*100;                         //金额
        $data['out_trade_no'] = $out_trade_no;    //商户订单号,不能重复
        $data['nonce_str'] = $nonce_str;                   //随机字符串
        $data['notify_url'] = "http://www.zhishengwh.com/callback/notify/wxnotify/";    //回调地址,用户接收支付后的通知,必须为能直接访问的网址,不能跟参数
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
            $re = $this->FromXml($data);
            if($re['return_code'] != 'SUCCESS'){
                echo base64_encode(json_encode(['status'=>201,'return_code'=>"fail","return_msg"=>"验签失败"]));
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
               echo base64_encode(json_encode(['status'=>0,'return_code'=>"success","return_msg"=>$arr]));
            }
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            echo base64_encode(json_encode(['status'=>202,'return_code'=>"fail","return_msg"=>"curl出错，错误码:$error"]));
        }
    }
    //微信小程序下单
    public function unifiedorder() {
        #获取SDK上POST方式传过来的数据 然后base64解密 然后将json字符串转化成数组
        $param = json_decode(base64_decode(file_get_contents("php://input")),true);
        if($param['game_id'] == "33" || $param['game_id'] == "112" || $param['game_id'] == "212" || $param['game_id'] == "224" || $param['game_id'] == "199" ) {
            echo "游戏暂停充值";exit();
        }
        $prefix = $param['code'] == 1 ? "SP_" : "PF_";
        $out_trade_no = $prefix.date('Ymd').date('His').sp_random_string(4);
        $user = get_user_entity($param['user_id']);
        $param['pay_source'] = empty($param['pay_source'])?0:$param['pay_source'];
        if($param['code'] == 1 ){
            $spend = M("spend","tab_");
            #TODO添加消费记录
            $spend_data['user_id']          = $param['user_id'];
            $spend_data['user_account']     = $user['account'];
            $spend_data['user_nickname']    = $user['nickname'];
            $spend_data['game_id']          = $param['game_id'];
            $spend_data['game_appid']       = $param['game_appid'];
            $spend_data['game_name']        = $param['game_name'];
            $spend_data['server_id']        = 0;
            $spend_data['server_name']      = "";
            $spend_data['promote_id']       = $user['promote_id'];
            $spend_data['promote_account']  = $user['promote_account'];
            $spend_data['order_number']     = "";
            $spend_data['pay_order_number'] = $out_trade_no;
            $spend_data['props_name']       =$param['title'];
            $spend_data['pay_amount']       = $param['price'];
            $spend_data['pay_way']          = 2;
            $spend_data['pay_time']         = time();
            $spend_data['pay_status']       = 0;
            $spend_data['pay_game_status']  = 0;
            $spend_data['extend']           = $param['extend'];
            $spend_data['spend_ip']         = get_client_ip();
            $result = $spend->add($spend_data);
            if($result){
                if(isset($param['coupon_id']))
                {
                    $total_amount=$param['credit']+$param['price'];
                    $user_coupon['pay_order_number']=$out_trade_no;
                    $user_coupon['status']=0;
                    $user_coupon['game_id']=$param['game_id'];
                    $user_coupon['game_name']= $param['game_name'];
                    $user_coupon['game_appid']=$param['game_appid'];
                    $user_coupon['promote_id']=$user['promote_id'];
                    $user_coupon['promote_account']  = $user['promote_account'];
                    $user_coupon['total_amount']=$total_amount;
                    $user_coupon['credit']=$param['credit'];
                    $mapchr['user_id'] = $param['user_id'];
                    $mapchr['coupon_id'] = $param['coupon_id'];
                    M('user_coupon','tab_')->where($mapchr)->save($user_coupon);

                }
                echo base64_encode(json_encode(['status'=>0,'return_code'=>"success","out_trade_no"=>$out_trade_no]));
            }
            else{
                echo base64_encode(json_encode(['status'=>202,'return_code'=>"fail","return_msg"=>"下单失败"]));
            }
        }else{
            #TODO添加平台币充值记录
            $deposit = M("deposit","tab_");
            $deposit_data['order_number']     = "";
            $deposit_data['pay_order_number'] = $out_trade_no;
            $deposit_data['user_id']          = $param['user_id'];
            $deposit_data['user_account']     =  $user['account'];
            $deposit_data['user_nickname']    = $user['nickname'];
            $deposit_data['promote_id']       = $user['promote_id'];
            $deposit_data['promote_account']  = $user['promote_account'];
            $deposit_data['pay_amount']       = $param['price'];
            $deposit_data['reality_amount']   = $param['price'];
            $deposit_data['pay_status']       = 0;
            $deposit_data['pay_way']          =2;
            $deposit_data['pay_ip']           = get_client_ip();
            $deposit_data['pay_source']       =   $param['pay_source'];
            $deposit_data['create_time']      = time();
            $result = $deposit->add($deposit_data);
            if($result){
                echo base64_encode(json_encode(['status'=>0,'return_code'=>"success","out_trade_no"=>$out_trade_no]));
            }
            else{
                echo base64_encode(json_encode(['status'=>202,'return_code'=>"fail","return_msg"=>"下单失败"]));
            }
        }

    }
    /**
     *其他支付
     */
    public function outher_pay(){
        #获取SDK上POST方式传过来的数据 然后base64解密 然后将json字符串转化成数组
        $request = json_decode(base64_decode(file_get_contents("php://input")),true);
        $game_set_data = get_game_set_info($request['game_id']);

        if(empty($game_set_data['partner']) || empty($game_set_data['key'])){
            $this->set_message(0,"faill","未设置此应用的威富通账号");
        }

        // if(($request['apk_pck_name'] != $game_set_data['apk_pck_name']) || ($request['apk_md5_sign'] != $game_set_data['apk_pck_sign'])){
        //     $this->set_message(0,"faill","游戏签名包与微信应该包名签名不符");
        // }

        $request['apitype'] = "swiftpass";
        $request['config']  = array("partner"=>$game_set_data['partner'],"email"=>"","key"=>$game_set_data['key']);
        $request['signtype']= "MD5";
        $request['server']  = "unified.trade.pay";
        $request['payway']  = 2;
        $result_data = $this->pay($request);

        $data['status'] = 1;
        $data['return_code'] = "success";
        $data['return_msg'] = "下单成功";
        $data['token_id'] = $result_data['token_id'];
        $data['out_trade_no'] = $result_data['out_trade_no'];
        //$data['partner'] = $game_set_data['partner']; //C('weixin.partner');
        //$data['key'] = $game_set_data['key'];
        $data['game_pay_appid'] = $game_set_data['game_pay_appid'];
        echo base64_encode(json_encode($data));
    }

    /**
     * 掌灵微信支付
     * @return [type] [description]
     */
    public function weixin_zl(){
        #获取SDK上POST方式传过来的数据 然后base64解密 然后将json字符串转化成数组
        $request = json_decode(base64_decode(file_get_contents("php://input")),true);
        $prefix = $request['code'] == 1 ? "SP_" : "PF_";
        $out_trade_no = $prefix.date('Ymd').date('His').sp_random_string(4);
        $request['pay_order_number'] = $out_trade_no;
        $request['pay_status'] = 0;
        $request['pay_way']    = 2;
        $request['spend_ip']   = get_client_ip();
        if($request['code'] == 1 ){
            #TODO添加消费记录
            $this->add_spend($request);
        }else{
            #TODO添加平台币充值记录
            $this->add_deposit($request);
        }
        $data['status'] = 1;
        $data['return_code'] = "success";
        $data['return_msg']  = "下单成功";
        $data['appid']  =   C('weixin_zl.appid');
        $data['appkey']  =  C('weixin_zl.key');
        $data['out_trade_no'] = $out_trade_no;
        $data['notify_url'] = "http://" . $_SERVER['HTTP_HOST'] . "/callback.php/Notify/weixin_zl_notify";
        //$data['agent_id'] = C('jubaobar.parent');//"1234567890";
        echo base64_encode(json_encode($data));

    }
    /**
     * 查询支付结果
     * @return [type] [description]
     */
    public function weixin_zl_notify(){
        #获取SDK上POST方式传过来的数据 然后base64解密 然后将json字符串转化成数组
        $request = json_decode(base64_decode(file_get_contents("php://input")),true);
        $pay_where = substr($request['out_trade_no'],0,2);
        $map['pay_order_number']=$request['out_trade_no'];
        if($pay_where=="SP"){
            $res=M('spend','tab_')->where($map)->find();
        }elseif($pay_way=="PF"){
            $res=M('deposit','tab_')->where($map)->find();
        }
        if($res['pay_status']==1){
            $data['status'] = 1;
            $data['return_msg']  = "支付成功";
        }else{
            $data['status'] = 0;
            $data['return_msg']  = "支付失败";
        }

        echo base64_encode(json_encode($data));

    }

    public function jubaobar_pay(){
        #获取SDK上POST方式传过来的数据 然后base64解密 然后将json字符串转化成数组
        $request = json_decode(base64_decode(file_get_contents("php://input")),true);
        $prefix = $request['code'] == 1 ? "SP_" : "PF_";
        $out_trade_no = $prefix.date('Ymd').date('His').sp_random_string(4);
        $request['pay_order_number'] = $out_trade_no;
        $request['pay_status'] = 0;
        $request['pay_way']    = 3;
        $request['spend_ip']   = get_client_ip();
        if($request['code'] == 1 ){
            #TODO添加消费记录
            $this->add_spend($request);
        }else{
            #TODO添加平台币充值记录
            $this->add_deposit($request);
        }
        $data['status'] = 1;
        $data['return_code'] = "success";
        $data['return_msg']  = "下单成功";
        $data['appid']  =   C("jubaobar.appid");
        $data['out_trade_no'] = $out_trade_no;
        //$data['agent_id'] = C('jubaobar.parent');//"1234567890";
        echo base64_encode(json_encode($data));
    }

    /**
     *平台币支付
     */
    public function platform_coin_pay(){
        #获取SDK上POST方式传过来的数据 然后base64解密 然后将json字符串转化成数组
        $request = json_decode(base64_decode(file_get_contents("php://input")),true);
        #记录信息
        $user_entity = get_user_entity($request['user_id']);
        $out_trade_no = "PF_".date('Ymd').date('His').sp_random_string(4);
        $request['order_number']     = $out_trade_no;
        $request['pay_order_number'] = $out_trade_no;
        $request['out_trade_no']     = $out_trade_no;
        $request['title'] = $request['title'];
        $request['pay_status'] = 1;
        $request['pay_way'] = 0;
        $request['spend_ip']   = get_client_ip();

        //限制去吧皮卡丘游戏充值
        if($request['game_id'] == "33" || $request['game_id'] == "112" || $request['game_id'] == "212" || $request['game_id'] == "224" || $request['game_id'] == "199"  ) {
            echo base64_encode(json_encode(array("status"=>-3,"return_code"=>"fail","return_msg"=>"游戏暂停充值")));
            exit();
        }

        //$result = false;
        switch ($request['code']) {
            case 1:
                //非绑定平台币
                $user = M("user","tab_");
                if($user_entity['balance'] < $request['price']){
                    echo base64_encode(json_encode(array("status"=>-2,"return_code"=>"fail","return_msg"=>"余额不足")));
                    exit();
                }
                //扣除平台币
                $user->where("id=".$request["user_id"])->setDec("balance",$request['price']);
                // 添加非绑定平台币消费记录
                $this->add_spend($request);
                //获取游戏数据
                $cpdata = M("Spend","tab_")->where(['pay_order_number'=>$out_trade_no])->find();
                $gameappid = $cpdata['game_appid']; //游戏appid
                $rescp['gameid'] = $cpdata['game_id']; //cp游戏
                $rescp['extend'] = $cpdata['extend']; //cp订单号
                $rescp['total_amount'] = $cpdata['pay_amount']; //支付金额
                $rescp['props_name'] = $cpdata['props_name']; //商品名称
                $cpsignstr = json_encode($rescp);
                $rescp['pay_status'] = 1; //支付状态
                $rescp['sign'] = hash_hmac("sha256",$cpsignstr,$gameappid);
                $cpnoturl = M('Game_set',"tab_")->field("pay_notify_url")->where(['game_id'=>$cpdata['game_id']])->find();
                if(!empty($cpnoturl['pay_notify_url'])){
                    echo base64_encode(json_encode(array("return_status"=>1,"return_code"=>"success","return_msg"=>"支付成功","out_trade_no"=>$out_trade_no)));
                    for($i=0;$i<3;$i++){
                        $noturl = $cpnoturl['pay_notify_url'];
                        $this->post($rescp,$noturl);
                        sleep(1);
                        file_put_contents("./log/log6.txt",json_encode($rescp)."order response cp ok.-".$noturl."--\r\n",FILE_APPEND);
                    }
                }else{
                    echo base64_encode(json_encode(array("status"=>-2,"return_code"=>"fail","return_msg"=>"未找到回掉信息")));
                    exit;
                }
                break;
            case 2:
                //绑定平台币
                $user_play = M("UserPlay","tab_");
                $user_play_map['user_id'] = $request['user_id'];
                $user_play_map['game_id'] = $request['game_id'];
                $user_play_data = $user_play->where($user_play_map)->find();

                if($user_play_data['bind_balance'] < $request['price']){
                    echo base64_encode(json_encode(array("status"=>-2,"return_code"=>"fail","return_msg"=>"余额不足")));
                    exit();
                }
                //扣除平台币
                $user_play->where($user_play_map)->setDec("bind_balance",$request['price']);
                // 添加绑定平台币消费记录
                $this->add_bind_spned($request);
                //获取游戏数据
                $cpdata = M("Bind_spend","tab_")->where(['pay_order_number'=>$out_trade_no])->find();
                $gameappid = $cpdata['game_appid']; //游戏appid
                $rescp['gameid'] = $cpdata['game_id']; //cp游戏
                $rescp['extend'] = $cpdata['extend']; //cp订单号
                $rescp['total_amount'] = $cpdata['pay_amount']; //支付金额
                $rescp['props_name'] = $cpdata['props_name']; //商品名称
                $cpsignstr = json_encode($rescp);
                $rescp['pay_status'] = 1; //支付状态
                $rescp['sign'] = hash_hmac("sha256",$cpsignstr,$gameappid);
                $cpnoturl = M('Game_set',"tab_")->field("pay_notify_url")->where(['game_id'=>$cpdata['game_id']])->find();
                if(!empty($cpnoturl['pay_notify_url'])){

                    echo base64_encode(json_encode(array("return_status"=>1,"return_code"=>"success","return_msg"=>"支付成功","out_trade_no"=>$out_trade_no)));

                    $noturl = $cpnoturl['pay_notify_url'];
                    for($i=0;$i<3;$i++){
                        $this->post($rescp,$noturl);
                        sleep(1);
                        file_put_contents("./log/log6.txt",json_encode($rescp)."order response cp ok.-".$noturl."--\r\n",FILE_APPEND);
                    }

                }else{
                    echo base64_encode(json_encode(array("status"=>-2,"return_code"=>"fail","return_msg"=>"未找到回掉信息")));
                    exit;
                }
                break;
            default:
                echo base64_encode(json_encode(array("status"=>-3,"return_code"=>"fail","return_msg"=>"支付方式不明确")));
                exit();
                break;
        }



//       $game = new GameApi();
//        $game->game_pay_notify($request,$request['code']);
//        if($result){
//            echo base64_encode(json_encode(array("return_status"=>1,"return_code"=>"success","return_msg"=>"支付成功","out_trade_no"=>$out_trade_no)));
//        }
//        else{
//            echo base64_encode(json_encode(array("status"=>-1,"return_code"=>"fail","return_msg"=>"支付失败")));
//        }
    }

    /**
     *支付验证
     */
    public function pay_validation(){
        #获取SDK上POST方式传过来的数据 然后base64解密 然后将json字符串转化成数组
        $request = json_decode(base64_decode(file_get_contents("php://input")),true);
        $out_trade_no = $request['out_trade_no'];
        $pay_where = substr($out_trade_no,0,2);
        $result = 0;
        $map['pay_order_number'] = $out_trade_no;
        switch ($pay_where) {
            case 'SP':
                $data = M('spend','tab_')->field('pay_status')->where($map)->find();
                $result = $data['pay_status'];
                break;
            case 'PF':
                $data = M('deposit','tab_')->field('pay_status')->where($map)->find();
                $result = $data['pay_status'];
                break;
            case 'AG':
                $data = M('agent','tab_')->field('pay_status')->where($map)->find();
                $result = $data['pay_status'];
                break;
            default:
                exit('accident order data');
                break;
        }
        if($result){
            echo base64_encode(json_encode(array("status"=>1,"return_code"=>"success","return_msg"=>"支付成功")));
            exit();
        }else{
            echo base64_encode(json_encode(array("status"=>0,"return_code"=>"fail","return_msg"=>"支付失败")));
            exit();
        }
    }

    /**
    通知CP
     */
    protected function post($param,$url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);//设置等待时间
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }



}
