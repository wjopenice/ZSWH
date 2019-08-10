<?php

/**
 * 订单数据模型
 */

namespace vendor\pay;

class PayVo {

    protected $_orderNo;
    protected $_fee;
    protected $_title;
    protected $_body;
    protected $_signtype;
    protected $_callback;
    protected $_url;
    protected $_param;
    protected $_payWay;
    protected $_wealid;
    protected $_wealName;
    protected $_gameAppid;
    protected $_serverid;
    protected $_serverName;
    protected $_userid;
    protected $_account;
    protected $_userNickName;
    protected $_promoteid;
    protected $_promoteName;
    protected $_promoteNickname;
    protected $_parentId;
    protected $_parentAccount;
    protected $_extend;
    protected $_table;
    protected $_bank;
    protected $_money;
    protected $_coin;
    protected $_service;
    protected $_notifyurl;
    protected $_payMethod;
    protected $_sdkVersion;
    protected $_pay_source;
    protected $_AccountType;
    /**
     * 设置订单号
     * @param type $order_no
     * @return \Think\Pay\PayVo
     */
    public function setOrderNo($order_no) {
        $this->_orderNo = $order_no;
        return $this;
    }

    /**
     * 设置商品价格
     * @param type $fee
     * @return \Think\Pay\PayVo
     */
    public function setFee($fee) {
        $this->_fee = $fee;
        return $this;
    }

    /**
     * 设置商品名称
     * @param type $title
     * @return \Think\Pay\PayVo
     */
    public function setTitle($title) {
        $this->_title = $title;
        return $this;
    }

    /**
     * 设置商品描述
     * @param type $body
     * @return \Think\Pay\PayVo
     */
    public function setBody($body) {
        $this->_body = $body;
        return $this;
    }

    /**
    *签名方式
    *@param  signtype
    *@return \Think\Pay\PayVo
    */
    public function setSignType($signtype){
        $this->_signtype = $signtype;
        return $this;
    }

    /**
     * 设置支付完成后的后续操作接口
     * @param type $callback
     * @return \Think\Pay\PayVo
     */
    public function setCallback($callback) {
        $this->_callback = $callback;
        return $this;
    }

    /**
     * 设置支付完成后的跳转地址
     * @param type $url
     * @return \Think\Pay\PayVo
     */
    public function setUrl($url) {
        $this->_url = $url;
        return $this;
    }

    /**
     * 设置订单的额外参数
     * @param type $param
     * @return \Think\Pay\PayVo
     */
    public function setParam($param) {
        $this->_param = $param;
        return $this;
    }
    /**
     * 设置支付方式
     * @param type $payway
     * @return \Think\Pay\PayVo
     */
    public function setPayWay($payway) {
        $this->_payWay = $payway;
        return $this;
    }

    /**
     * 设置公益id $wealid
     * @param type $wealid
     * @return \vendor\Pay\PayVo
     */
    public function setWealId($wealid) {
        $this->_wealid = $wealid;
        return $this;
    }

    /**
     * $wealname
     * @param type $wealname
     * @return \vendor\Pay\PayVo
     */
    public function setWealName($wealname) {
        $this->_wealName = $wealname;
        return $this;
    }



    /**
     * 设置用户账号uid
     * @param type $userid
     * @return \vendor\Pay\PayVo
     */
    public function setUserId($userid) {
        $this->_userid = $userid;
        return $this;
    }

    /**
     * 设置用户账号
     * @param type $url
     * @return \vendor\Pay\PayVo
     */
    public function setAccount($account) {
        $this->_account = $account;
        return $this;
    }

    /**
     * 设置用户账号昵称
     * @param type $url
     * @return \vendor\Pay\PayVo
     */
    public function setUserNickName($usernickname) {
        $this->_userNickName = $usernickname;
        return $this;
    }


    /**
    *要插入的表
    */
    public function setTable($table){
        $this->_table = $table;
        return $this;
    }
    
    /**
     * 设置充值银行
     * @param type $param
     * @return \vendor\Pay\PayVo
     */
    public function setBank($bank) {
        $this->_bank = $bank;
        return $this;
    }
    /**
     * 设置充值实际金额（除去手续费）
     * @param type $param
     * @return \Think\Pay\PayVo
     */
    public function setMoney($money) {
        $this->_money = $money;
        return $this;
    }

    /**
     * 设置支付服务类型
     * @param type $param
     * @return \Think\Pay\PayVo
     */
    public function setService($service){
        $this->_service = $service;
        return $this;
    }

    /**
    *支付异步通知地址
    */
    public function setNotifyUrl($notifyurl){
        $this->_notifyurl = $notifyurl;
        return $this;
    }

    /**
    *支付方法(第三方支付选择例如0：支付宝 )
    */
    public function setPayMethod($payMethod){
        $this->_payMethod = $payMethod;
        return $this;
    }
    /**
    *sdk版本安卓 苹果
    */
    public function setSdkVersion($SdkVersion){
        $this->_sdkVersion = $SdkVersion;
        return $this;
    }

    /**
     * 获取支付方式
     * @return type
     */
    public function getPayWay() {
        return $this->_payWay;
    }

    /**
     * 获取公益wealid
     * @return type
     */
    public function getWealId() {
        return $this->_wealid;
    }

    /**
     * 获取公益名称
     * @return type
     */
    public function getWealName() {
        return $this->_wealName;
    }


    /**
     * 获取账号uid
     * @return type
     */
    public function getUserId() {
        return $this->_userid;
    }

    /**
     * 获取用户账号
     * @return type
     */
    public function getAccount() {
        return $this->_account;
    }

    /**
     * 获取用户昵称
     * @return type
     */
    public function getUserNickName() {
        return $this->_userNickName;
    }


    /**
    * 获取要插入的表
    */
    public function getTable(){
        return $this->_table;
    }
    
    /**
     * 获取充值银行
     * @return type
     */
    public function getBank() {
        return $this->_bank;
    }
    /**
     * 获取充值实际金额（除去手续费）
     * @return type
     */
    public function getMoney() {
        return $this->_money;
    }


    /**
     * 获取订单号
     * @return type
     */
    public function getOrderNo() {
        return $this->_orderNo;
    }

    /**
     * 获取商品价格
     * @return type
     */
    public function getFee() {
        return $this->_fee;
    }

    /**
     * 获取商品名称
     * @return type
     */
    public function getTitle() {
        return $this->_title;
    }

    /**
    * 获取验签方式
    */
    public function getSignType(){
        return $this->_signtype;
    }

    /**
     * 获取支付完成后的后续操作接口
     * @return type
     */
    public function getCallback() {
        return $this->_callback;
    }

    /**
     * 获取支付完成后的跳转地址
     * @return type
     */
    public function getUrl() {
        return $this->_url;
    }

    /**
     * 获取商品描述
     * @return type
     */
    public function getBody() {
        return $this->_body;
    }

    /**
     * 获取订单的额外参数
     * @return type
     */
    public function getParam() {
        return $this->_param;
    }

    /**
    *支付服务类型
    *@return type
    */
    public function getService(){
        return $this->_service;
    }

    /**
    *支付异步通知地址
    */
    public function getNotifyUrl(){
        return $this->_notifyurl;
    }

    /**
    *支付方法
    */
    public function getPayMethod(){
        return $this->_payMethod;
    }
    /**
    *SDK版本苹果安卓
    */
    public function getSdkVersion(){
        return $this->_sdkVersion;
    }

	//渠道使用支付宝充值记录
    protected $_alipayToPromote=array();
    /**
     * 设置渠道使用支付宝充值数据
     */
    public function setAlipayToPromote($alipayToPromote){

        $this->_alipayToPromote=$alipayToPromote;
        return $this;
    }
    /**
     *获取渠道使用支付宝充值数据
     */
    public function getAlipayToPromote(){
        return $this->_alipayToPromote;
    }


}
