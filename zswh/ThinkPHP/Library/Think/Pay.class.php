<?php

/**
 * 通用支付接口类
 * @author yunwuxin<448901948@qq.com>
 */

namespace Think;
class Pay {

    /**
     * 支付驱动实例
     * @var Object
     */
    private $payer;

    /**
     * 配置参数
     * @var type 
     */
    private $config;

    /**
     * 构造方法，用于构造上传实例
     * @param string $driver 要使用的支付驱动
     * @param array  $config 配置
     */
    public function __construct($driver, $config = array()) {
        /* 配置 */
        $pos = strrpos($driver, '\\');
        $pos = $pos === false ? 0 : $pos + 1;
        $apitype = strtolower(substr($driver, $pos));
        $this->config['notify_url'] = "http://".$_SERVER ['HTTP_HOST']."/callback.php/Notify/notify/apitype/alipay/method/notify";
        $this->config['return_url'] = "http://".$_SERVER ['HTTP_HOST']."/callback.php/Notify/notify/apitype/alipay/method/return";
        $config = array_merge($this->config, $config);
        /* 设置支付驱动 */
        $class = strpos($driver, '\\') ? $driver : 'Think\\Pay\\Driver\\' . ucfirst(strtolower($driver));
        $this->setDriver($class, $config);
    }

    public function buildRequestForm(Pay\PayVo $vo) {
        $this->payer->check();
        $result = false;
        switch ($vo->getTable()) {
            case 'spend':
                $result = $this->add_spend($vo);
                break;
            case 'deposit':
                $result = $this->add_deposit($vo);
                break;
            case 'agent':
                $result = $this->add_agent($vo);
                break;
            case 'promote_deposit':
                $result = $this->add_promote_deposit($vo);
                break;
            default:
                $result = false;
                break;
        }
        if($result !== false) {//$check !== false
            return $this->payer->buildRequestForm($vo);
        } else {
            E(M($vo->getTable(),"tab_")->getDbError());
        }
    }
    /**
     *渠道平台币充值记录
     */
    private function add_promote_deposit(Pay\PayVo $vo){
        $promote_deposit = M("Promote_deposit","tab_");
        $promote_deposit_data['order_number']  = "";
        $promote_deposit_data['pay_order_number']  = $vo->getOrderNo();
        $promote_deposit_data['promote_id']  = $vo->getPromoteId();
        $promote_deposit_data['promote_account']  = $vo->getPromoteName();
        $promote_deposit_data['promote_nickname']  =  $vo->getPromoteNickname();
        $promote_deposit_data['parent_id']  = $vo->getParentId();
        $promote_deposit_data['parent_account']  = $vo->getParentAccount();
        $promote_deposit_data['pay_amount']  = $vo->getFee();
        $promote_deposit_data['pay_status']  = 0;
        $promote_deposit_data['user_id']  = $vo->getUserId();
        $promote_deposit_data['pay_way']  = $vo->getPayWay();
        $promote_deposit_data['pay_source']  = $vo->getPaySource();
        $promote_deposit_data['pay_ip']  = get_client_ip();
        $promote_deposit_data['create_time']  = NOW_TIME;
        $result = $promote_deposit->add($promote_deposit_data);
        return $result;
    }

    /**
    *消费表添加数据
    */
    private function add_spend(Pay\PayVo $vo){
        $spend = M("spend","tab_");
        $spend_data['user_id']          = $vo->getUserId();
        $spend_data['user_account']     = $vo->getAccount();
        $spend_data['user_nickname']    = $vo->getUserNickName();
        $spend_data['game_id']          = $vo->getGameId();
        $spend_data['game_appid']       = $vo->getGameAppid();
        $spend_data['game_name']        = $vo->getGameName();
        $spend_data['server_id']        = $vo->getServerId();
        $spend_data['server_name']      = $vo->getServerName();
        $spend_data['promote_id']       = $vo->getPromoteId();
        $spend_data['promote_account']  = $vo->getPromoteName();
        $spend_data['order_number']     = "";
        $spend_data['pay_order_number'] = $vo->getOrderNo();
        $spend_data['props_name']       = $vo->getTitle();
        $spend_data['pay_amount']       = $vo->getFee();
        $spend_data['pay_way']          = $vo->getPayWay();
        $spend_data['pay_time']         = NOW_TIME;
        $spend_data['pay_status']       = 0;
        $spend_data['pay_game_status']  = 0;
        $spend_data['extend']           = $vo->getExtend();
        $spend_data['spend_ip']         = get_client_ip();
        $result = $spend->add($spend_data);
        if($vo->getCouponID())
        {
            $user_coupon['pay_order_number']=$vo->getOrderNo();
            $user_coupon['status']=0;
            $user_coupon['game_id']=$vo->getGameId();
            $user_coupon['game_name']= $vo->getGameName();
            $user_coupon['game_appid']=$vo->getGameAppid();
            $user_coupon['promote_id']=$vo->getPromoteId();
            $user_coupon['promote_account']  = $vo->getPromoteName();
            $user_coupon['total_amount']=$vo->getTotalAmount();
            $user_coupon['credit']=$vo->getCredit();
            $mapchr['user_id'] = $vo->getUserId();
            $mapchr['coupon_id'] = $vo->getCouponID();
             M('user_coupon','tab_')->where($mapchr)->save($user_coupon);

        }
        return $result;
    }

    /**
    *平台币充值记录
    */
    private function add_deposit(Pay\PayVo $vo){
        $deposit = M("deposit","tab_");
        // $ordercheck = $deposit->where(array('pay_order_number'=>$data["order_no"]))->find();
        // if($ordercheck)$this->error("订单已经存在，请刷新充值页面重新下单！");
        $deposit_data['order_number']     = "";
        $deposit_data['pay_order_number'] = $vo->getOrderNo();
        $deposit_data['user_id']          = $vo->getUserId();
        $deposit_data['user_account']     = $vo->getAccount();
        $deposit_data['user_nickname']    = $vo->getUserNickName();
        $deposit_data['promote_id']       = $vo->getPromoteId();
        $deposit_data['promote_account']  = $vo->getPromoteName();
        $deposit_data['pay_amount']       = $vo->getFee();
        $deposit_data['reality_amount']   = $vo->getFee();
        $deposit_data['pay_status']       = 0;
        $deposit_data['pay_way']          = $vo->getPayWay();
        
        $deposit_data['pay_ip']           = get_client_ip();
        $deposit_data['pay_source']       = $vo->getPaySource();
        $deposit_data['create_time']      = NOW_TIME;
        $result = $deposit->add($deposit_data);
        return $result;
    }

     /**
    *添加代充记录
    * @author zdd_20170512
    */
    private function add_agent(Pay\PayVo $vo){
        $agent = M("agent","tab_");
        $agnet_data['order_number']     = "";
        $agnet_data['pay_order_number'] = $vo->getOrderNo();
        $agnet_data['game_id']          = $vo->getGameId();
        $agnet_data['game_appid']       = $vo->getGameAppid();
        $agnet_data['game_name']        = $vo->getGameName();
        $agnet_data['promote_id']       = $vo->getPromoteId();
        $agnet_data['promote_account']  = $vo->getPromoteName();
        $agnet_data['user_id']          = $vo->getUserId();
        $agnet_data['user_account']     = $vo->getAccount();
        $agnet_data['user_nickname']    = $vo->getUserNickName();
        $agnet_data['pay_type']         = 0;//代充 转移
        $agnet_data['amount']           = $vo->getMoney();
        $agnet_data['real_amount']      = $vo->getFee();
        $agnet_data['pay_status']       = 0;
        $agnet_data['pay_way']          = $vo->getPayWay();
        $agnet_data['create_time']      = time();
        $agnet_data['zhekou']           = $vo->getParam();
        $account_type =$vo->getAccountType();
        if( $account_type == 'user'){
            $agnet_data['account_type'] = 1;
        }else{
            $agnet_data['account_type'] = 2;
        }
       
        
        $agent->create($agnet_data);
        $result = $agent->add();
        return $result;
    }
    /**
     * 设置支付驱动
     * @param string $class 驱动类名称
     */
    private function setDriver($class, $config) {
        $this->payer = new $class($config);
        if (!$this->payer) {
            E("不存在支付驱动：{$class}");
        }
    }

//    public function verifyNotify($notify) {
//        //生成签名结果
//        $isSign = $this->getSignVeryfy($notify, $notify["sign"]);
//        $response = true;
//        if (!empty($notify["notify_id"])) {
//            $response = $this->getResponse($notify["notify_id"]);
//        }
//        if ($response && $isSign) {
//            $this->setInfo($notify);
//            return true;
//        } else {
//            return false;
//        }
//    }
//
//    /**
//     * 获取远程服务器ATN结果,验证返回URL
//     * @param $notify_id 通知校验ID
//     * @return 服务器ATN结果
//     * 验证结果集：
//     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
//     * true 返回正确信息
//     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
//     */
//    public function getResponse($notify_id) {
//        $partner = $this->config['partner'];
//        $params = array(
//            'input_charset' => 'UTF-8',
//            'partner' => $partner,
//            'notify_id' => $notify_id
//        );
//        $sign = $this->createSign($params);
//        $veryfy_url = $this->verify_url . "?input_charset=UTF-8&sign={$sign}&partner=" . $partner . "&notify_id=" . $notify_id;
//        $responseTxt = $this->fsockOpen($veryfy_url);
//
//        $responseTxt = simplexml_load_string($responseTxt);
//        return (int) $responseTxt->retcode == 0;
//    }
//
//
//    public function setInfo($notify) {
//        $info = array();
//        //支付状态
//        $info['status'] = $notify['trade_state'] == 0 ? true : false;
//        $info['money'] = $notify['total_fee'] / 100;
//        $info['out_trade_no'] = $notify['out_trade_no'];
//        $this->info = $info;
//    }
//
//    /**
//     * 获取返回时的签名验证结果
//     * @param $para_temp 通知返回来的参数数组
//     * @param $sign 返回的签名结果
//     * @return 签名验证结果
//     */
//    public function getSignVeryfy($param, $sign) {
//        //除去待签名参数数组中的空值和签名参数
//        $param_filter = array();
//        while (list ($key, $val) = each($param)) {
//            if ($key == "sign" || $val == "") {
//                continue;
//            } else {
//                $param_filter[$key] = $param[$key];
//            }
//        }
//
//        $mysgin = $this->createSign($param_filter);
//
//        if ($mysgin == $sign) {
//            return true;
//        } else {
//            return false;
//        }
//    }
//
//    /**
//     * 创建签名
//     * @param type $params
//     */
//    public function createSign($params) {
//
//        ksort($params);
//        reset($params);
//
//        $arg = '';
//        foreach ($params as $key => $value) {
//            $arg .= "{$key}={$value}&";
//        }
//        return strtoupper(md5($arg . 'key=' . $this->config['key']));
//    }
//
//    /**
//     * 异步通知验证成功返回信息
//     */
//    public function notifySuccess() {
//        echo "success";
//    }
//
//    /**
//     * 验证通过后获取订单信息
//     * @return type
//     */
//    public function getInfo() {
//        return $this->info;
//    }


    public function __call($method, $arguments) {
        if (method_exists($this, $method)) {
            return call_user_func_array(array(&$this, $method), $arguments);
        } elseif (!empty($this->payer) && $this->payer instanceof Pay\Pay && method_exists($this->payer, $method)) {
            return call_user_func_array(array(&$this->payer, $method), $arguments);
        }
    }

}
