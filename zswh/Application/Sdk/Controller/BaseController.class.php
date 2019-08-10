<?php
namespace Sdk\Controller;
use Think\Controller\RestController;
class BaseController extends RestController{
    protected function _initialize(){
        $data = json_decode(base64_decode(file_get_contents("php://input")),true);
        //判断数据是否为空
        if(empty($data) || empty($data['game_id'])){
            echo base64_encode(json_encode(array("status"=>0,"return_msg"=>"操作数据或游戏ID不能为空")));exit();
        }
        $md5Sign = $data['md5_sign'];
        unset($data['md5_sign']);
        //获取游戏key
        $game_data = M("GameSet","tab_")->where("game_id=".$data["game_id"])->find();
        $md5_sign = $this->encrypt_md5($data,$game_data["access_key"]);//mengchuang DZQkkiz!@#9527
        if($md5Sign !== $md5_sign){
            $this->set_message(0,"fail","验签失败");
        }
    }

    /**
    *设置接口提示信息
    *@param  int     $status 提示状态 
    *@param  string  $return_code 提示代码
    *@param  string  $return_msg  提示信息
    *@return string  base64加密后的json格式字符串
    *@author 小纯洁
    */
    public function set_message($status=0,$return_code="fail",$return_msg="操作失败"){
        $msg = array(
            "status"      => $status,
            "return_code" => $return_code,
            "return_msg"  => $return_msg
        );
        echo base64_encode(json_encode($msg));
        exit();
    }

    /**
    *验证签名
    */
    public function validation_sign($encrypt="",$md5_sign=""){
        $signString = $this->arrSort($encrypt);
        $md5Str = $this->encrypt_md5($signString,$key="");
        if($md5Str === $md5_sign){
            return true;
        }
        else{
            return false;
        }
    }

    /**
    *对数据进行排序
    */
    private function arrSort($para){
        ksort($para);
        reset($para);
        return $para;
    }

    /**
    *MD5验签加密
    */
    public function encrypt_md5($param="",$key=""){
        #对数组进行排序拼接
        if(is_array($param)){
            $md5Str = implode($this->arrSort($param));
        }
        else{
            $md5Str = $param;
        }
        $md5 = md5($md5Str . $key);
        return '' === $param ? 'false' : $md5;
    }

    /**
     *短信验证
     */
    public function sms_verify2($phone="" ,$code=""){
        if(!empty($phone) && !empty($code) ){
            $smsdata = M('Short_message',"tab_")->where("phone = '{$phone}' and code = '{$code}'")->find();
            if(!empty($smsdata)){
                $locktime = time();
                $datatime = $smsdata['send_time'];
                if( ($locktime - $datatime) < 60){
                    $datacode = $smsdata['code'];
                    if($datacode == $code){
                        return true;
                    }else{
                        $this->set_message(-2,"fail","输入验证码不正确");
                    }
                }else{
                    $this->set_message(-1,"fail","验证超时！请重新获取");
                }
            }else{
                $this->set_message(-2,"fail","输入验证码不正确");
            }
        }else{
            $this->set_message(0,"fail","数据获取失败！");
        }
    }

    /**
    *短信验证
    */
    public function sms_verify($phone="" ,$code=""){
        $session = session($phone);
        if(empty($session)){
            $this->set_message(0,"fail","数据获取失败！");
        }
        #验证码是否超时
        $time = NOW_TIME - session($phone.".create_time");
        if($time > 60){//$tiem > 60
            $this->set_message(-1,"fail","验证超时！请重新获取");
        }
        #验证短信验证码
        if(session($phone.".code") != $code){
            $this->set_message(-2,"fail","输入验证码不正确");
        }
        return true;
    }

    /**
    *消费记录表 参数
    */
    private function spend_param($param=array()){
        $user_entity = get_user_entity($param['user_id']);
        $data_spned['user_id']          = $param["user_id"];
        $data_spned['user_account']     = $user_entity["account"];
        $data_spned['user_nickname']    = $user_entity["nickname"];
        $data_spned['game_id']          = $param["game_id"];
        $data_spned['game_appid']       = $param["game_appid"];
        $data_spned['game_name']        = $param["game_name"];
        $data_spned['server_id']        = 0;
        $data_spned['server_name']      = "";
        $data_spned['promote_id']       = $user_entity["promote_id"];
        $data_spned['promote_account']  = $user_entity["promote_account"];
        $data_spned['order_number']     = $param["order_number"];
        $data_spned['pay_order_number'] = $param["pay_order_number"];
        $data_spned['props_name']       = $param["title"];
        $data_spned['pay_amount']       = $param["price"];
        $data_spned['pay_time']         = NOW_TIME;
        $data_spned['pay_status']       = $param["pay_status"];
        $data_spned['pay_game_status']  = 1;
        $data_spned['extend']           = $param['extend'];
        $data_spned['pay_way']          = $param["pay_way"];
        $data_spned['spend_ip']         = $param["spend_ip"];
        $data_spned['sdk_version']      = $param["sdk_version"];
        return $data_spned;
    }

    /**
    *平台币充值记录表 参数
    */
    private function deposit_param($param=array()){
        $user_entity = get_user_entity($param['user_id']);
        $data_deposit['order_number']     = $param["order_number"];
        $data_deposit['pay_order_number'] = $param["pay_order_number"];
        $data_deposit['user_id']          = $param["user_id"];
        $data_deposit['user_account']     = $user_entity["account"];
        $data_deposit['user_nickname']    = $user_entity["nickname"];
        $data_deposit['promote_id']       = $user_entity["promote_id"];
        $data_deposit['promote_account']  = $user_entity["promote_account"];
        $data_deposit['pay_amount']       = $param["price"];
        $data_deposit['reality_amount']   = $param["price"];
        $data_deposit['pay_status']       = $param["pay_status"];
        $data_deposit['pay_source']       = 2;
        $data_deposit['pay_way']          = $param["pay_way"];
        $data_deposit['pay_ip']           = $param["spend_ip"];
        $data_deposit['sdk_version']      = $param["sdk_version"];
        $data_deposit['create_time']      = NOW_TIME;
        return $data_deposit;
    }

    /**
    *绑定平台币消费
    */
    private function bind_spend_param($param = array()){
        $user_entity = get_user_entity($param['user_id']);
        $data_bind_spned['user_id']          = $param["user_id"];
        $data_bind_spned['user_account']     = $user_entity["account"];
        $data_bind_spned['user_nickname']    = $user_entity["nickname"];
        $data_bind_spned['game_id']          = $param["game_id"];
        $data_bind_spned['game_appid']       = $param["game_appid"];
        $data_bind_spned['game_name']        = $param["game_name"];
        $data_bind_spned['server_id']        = 0;
        $data_bind_spned['server_name']      = "";
        $data_bind_spned['promote_id']       = $user_entity["promote_id"];
        $data_bind_spned['promote_account']  = $user_entity["promote_account"];
        $data_bind_spned['order_number']     = $param["order_number"];
        $data_bind_spned['pay_order_number'] = $param["pay_order_number"];
        $data_bind_spned['props_name']       = $param["title"];
        $data_bind_spned['pay_amount']       = $param["price"];
        $data_bind_spned['pay_time']         = NOW_TIME;
        $data_bind_spned['pay_status']       = $param["pay_status"];
        $data_bind_spned['pay_game_status']  = 1;
        $data_bind_spned['pay_way']          = 1;
        $data_bind_spned['extend']           = $param['extend'];
        $data_bind_spned['spend_ip']         = $param["spend_ip"];
        $data_bind_spned['sdk_version']      = $param["sdk_version"];
        return $data_bind_spned;
    }

    /**
    *消费表添加数据
    */
    public function add_spend($data){
        $spend = M("spend","tab_");
        $spend_data =  $this->spend_param($data);
        $ordercheck = $spend->where(array('pay_order_number'=>$spend_data["pay_order_number"]))->find();
        if($ordercheck)
        {
            $this->set_message(0,'fail',"订单已经存在，请刷新充值页面重新下单！");
        }
        $result = $spend->add($spend_data);
         return $result;
    }

    /**
    *平台币充值记录
    */
    public function add_deposit($data){
        $deposit = M("deposit","tab_");
        $deposit_data  = $this->deposit_param($data);
        $ordercheck = $deposit->where(array('pay_order_number'=>$deposit_data["pay_order_number"]))->find();
        if($ordercheck)$this->set_message(0,'fail',"订单已经存在，请刷新充值页面重新下单！");
        $result = $deposit->add($deposit_data);
        return $result;
    }

    /**
    *绑定平台币消费记录
    */
    public function add_bind_spned($data){
        $bind_spned = M("BindSpend","tab_");
        $data_bind_spned  = $this->bind_spend_param($data);
        $ordercheck = $bind_spned->where(array('pay_order_number'=>$data_bind_spned["pay_order_number"]))->find();
        if($ordercheck)$this->set_message(0,'fail',"订单已经存在，请刷新充值页面重新下单！");
        $result = $bind_spned->add($data_bind_spned);
        return $result;
    }

    /**
    *设置数据里游戏的图片
    */
    public function set_game_icon($game_id=0){

        $icon_url ="http://".$_SERVER['HTTP_HOST'].get_cover($game_id,"path");
        return $icon_url;
    }

    //微信支付随机字符串
    public  function rand_code(){
        $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';//62个字符
        $str = str_shuffle($str);
        $str = substr($str,0,32);
        return  $str;
    }
    public function getSign($params) {
        ksort($params);        //将参数数组按照参数名ASCII码从小到大排序
        foreach ($params as $key => $item) {
            if (!empty($item)) {         //剔除参数值为空的参数
                $newArr[] = $key.'='.$item;     // 整合新的参数数组
            }
        }
        $stringA = implode("&", $newArr);         //使用 & 符号连接参数
        $stringSignTemp = $stringA."&key="."b2e9b1ada984072c1f0e6cb2709b32a0";        //拼接key
        // key是在商户平台API安全里自己设置的
        $stringSignTemp = MD5($stringSignTemp);       //将字符串进行MD5加密
        $sign = strtoupper($stringSignTemp);      //将所有字符转换为大写
        return $sign;
    }
    public function ToXml($data=array())

    {
        if(!is_array($data) || count($data) <= 0)
        {
            return '数组异常';
        }

        $xml = "<xml>";
        foreach ($data as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
    public function FromXml($xml)
    {
        if(!$xml){
            echo "xml数据异常！";
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }
}
