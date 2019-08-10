<?php

/**
 * 通用支付接口类
 * @author yunwuxin<448901948@qq.com>
 */

class Pay  {

    /**
     * 支付驱动实例
     * @var Object
     */
    public $db;
    public $user;
    public function init(){
        $this->db =new \dbModel();

    }
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
        $class = strpos($driver, '\\') ? $driver : 'vendor\\pay\\alipay.php\\' . ucfirst(strtolower($driver));
        $this->setDriver($class, $config);
    }

    public function buildRequestForm(Pay\PayVo $vo) {
        $this->payer->check();
        $result = false;
        switch ($vo->getTable()) {
            case 'weal_order':
                $result = $this->add_weal_order($vo);
                break;
            default:
                $result = false;
                break;
        }
        if($result !== false) {//$check !== false
            return $this->payer->buildRequestForm($vo);
        } else {
            return false;
        }
    }

    /**
     *公益捐款表添加数据
     */
    private function add_weal_order(Pay\PayVo $vo){
        $spend_data['user_id']          = $vo->getUserId();
        $spend_data['user_account']     = $vo->getAccount();
        $spend_data['user_nickname']    = $vo->getUserNickName();
        $spend_data['weal_id']          = $vo->getWealId();
        $spend_data['weal_title']        = $vo->getWealName();
        $spend_data['order_number']     =  $vo->getOrderNo();
        $spend_data['pay_amount']       = $vo->getFee();
        $spend_data['pay_way']          = $vo->getPayWay();
        $spend_data['pay_time']         = $_SERVER['REQUEST_TIME'];
        $spend_data['pay_status']       = 0;
        $spend_data['pay_weal_status']  = 0;
        $spend_data['spend_ip']         = getIp();
        $bool = $this->db->action($this->db->insertSql("weal_order",$spend_data));
        return $bool;
    }


    /**
     * 设置支付驱动
     * @param string $class 驱动类名称
     */
    private function setDriver($class, $config) {
        $this->payer = new $class($config);
        if (!$this->payer) {
           // E("不存在支付驱动：{$class}");
            echo "不存在支付驱动:{$class}";
        }
    }

    public function __call($method, $arguments) {
        if (method_exists($this, $method)) {
            return call_user_func_array(array(&$this, $method), $arguments);
        } elseif (!empty($this->payer) && $this->payer instanceof Pay\Pay && method_exists($this->payer, $method)) {
            return call_user_func_array(array(&$this->payer, $method), $arguments);
        }
    }

}
