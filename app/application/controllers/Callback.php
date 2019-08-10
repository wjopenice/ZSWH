<?php
use Yaf\Application;
use Yaf\Dispatcher;
class CallbackController extends Yaf\Controller_Abstract {
    //充值送积分
    public function recharge_point($payamount)
    {
        if($payamount==6)
        {
            $point=2;
        }
        elseif($payamount==30)
        {
            $point=12;
        }
        elseif($payamount==68)
        {
            $point=34;
        }
        elseif($payamount==98)
        {
            $point=58;
        }
        elseif($payamount==198)
        {
            $point=138;
        }
        elseif($payamount==328)
        {
            $point=262;
        }
        elseif($payamount==488)
        {
            $point=439;
        }
        elseif($payamount==648)
        {
            $point=648;
        }
        elseif($payamount==1000)
        {
            $point=1200;
        }
        else{
            $point=intval($payamount/5);
        }
        return $point;
    }
    //支付宝app回调
    public function alipaynotifyAction(){
       $data = $_POST;
        file_put_contents(APP_PATH."/log.txt",json_encode($data).PHP_EOL."\r\n",FILE_APPEND);//确认是支付宝回调的
         //商户订单号
         $order_id     = $data['out_trade_no'];
         //交易状态
         $trade_status = $data['trade_status'];
         $this->db = new dbModel();
         //查看是否存在该未支付订单，下面可以根据自己业务逻辑编写
         $result= $this->db->field("*")->table("tab_deposit")->where("pay_order_number = '{$order_id}' and pay_status=0")->find();
         $game=$this->db->field("*")->table("tab_deposit_game")->where("pay_order_number = '{$data['out_trade_no']}'")->find();
         $user=$this->db->field("balance,points")->table("tab_user")->where("id = '{$result['user_id']}'")->find();
         //存在该订单并支付成功，进行修改
         if ($result && $trade_status=="TRADE_SUCCESS") {
             $sqlData = array(
                 "pay_status" => 1,
                 'order_number'=>$data['trade_no']
             );
             $res = $this->db->action($this->db->updateSql("deposit",$sqlData,"pay_order_number = '{$order_id}'"));
             //修改成功
             if ($res) {
                  $balance['balance']=intval($user['balance'])+$result['pay_amount'];
                 $balance['vip_level']=$this->switch_user_level($balance['balance']);
                 if(empty($game)) {
                     if($this->recharge_point($result['pay_amount'])>0) {
                         $balance['points'] = intval($user['points']) + $this->recharge_point($result['pay_amount']);
                         //用户积分日志
                         $zs_user_bp['user'] = $result['user_id'];
                         $zs_user_bp['bp'] = "+" . $this->recharge_point($result['pay_amount']) . "积分";
                         $zs_user_bp['optime'] = date("Y-m-d", time()); //时间戳转为日期
                         $zs_user_bp['bp_type'] = "充值平台币";
                         $zs_user_bp['type'] = 0;
                         $this->db->action($this->db->insertSql("user_bp", $zs_user_bp));
                     }
                 }
                 else{
                     $user=$this->db->field("vip_level")->table("tab_user")->where("id = {$result['user_id']} ")->find();
                     $vip_level=$user['vip_level'];
                     $task=$this->db->field("game_id,game_name,is_member,point,v1,v2,v3,v4,v5,v6,v7,v8")->table("tab_recharge_point")->where("game_id = {$game['game_id']} ")->find();
                     $point=$task['point'];
                     if(!empty($task['is_member']))
                     {
                         switch ($vip_level){
                             case 1:
                                 $point=$point+$task['v1'];
                                 break;
                             case 2:
                                 $point=$point+$task['v2'];
                                 break;
                             case 3:
                                 $point=$point+$task['v3'];
                                 break;
                             case 4:
                                 $point=$point+$task['v4'];
                                 break;
                             case 5:
                                 $point=$point+$task['v5'];
                                 break;
                             case 6:
                                 $point=$point+$task['v6'];
                                 break;
                             case 7:
                                 $point=$point+$task['v7'];
                                 break;
                             case 8:
                                 $point=$point+$task['v8'];
                                 break;
                         }
                     }
                     $bp = $this->db->field("*")->table("tab_user_bp")->where(" user = {$result['user_id']} and game_id = '{$task['game_id']}' and type=3")->find();
                         $zs_user_bp['user'] = $result['user_id'];
                         $zs_user_bp['bp'] = "+".$point."积分";
                         $zs_user_bp['optime'] = date("Y-m-d");
                         $zs_user_bp['bp_type'] = "充值".$task['game_name'];
                         $zs_user_bp['type'] = 3;
                         $zs_user_bp['game_id'] = $task['game_id'];
                         $zs_user_bp['status'] = 0;
                         if(empty($bp)) {
                             $this->db->action($this->db->insertSql("user_bp", $zs_user_bp));
                         }
                 }
                  $this->db->action($this->db->updateSql("user",$balance,"id = {$result['user_id']}"));
                 file_put_contents(APP_PATH."/log.txt",$order_id.":修改数据成功"."\r\n",FILE_APPEND);//确认是支付宝回调的
             }
         }
         elseif ($trade_status=="TRADE_CLOSED")
         {
             $arr = array(
                 'pay_status' =>2
             );
             $this->db->action($this->db->updateSql("deposit",$arr,"pay_order_number = '{$data['out_trade_no']}'"));
             file_put_contents(APP_PATH."/log.txt",$order_id.":支付交易超时"."\r\n",FILE_APPEND);//确认是支付宝回调的
         }

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
    //换算等级
    public function switch_user_level($level){
        $vip_level = 0;
        switch(true){
            case $level>=30 && $level<90: $vip_level = 1; break;
            case $level>=90 && $level<270: $vip_level = 2; break;
            case $level>=270 && $level<810: $vip_level = 3; break;
            case $level>=810 && $level<2430: $vip_level = 4; break;
            case $level>=2430 && $level<6075: $vip_level = 5; break;
            case $level>=6075 && $level<15187: $vip_level = 6; break;
            case $level>=15187 && $level<37968: $vip_level = 7; break;
            case $level>=37968 : $vip_level = 8; break;
            default:$vip_level = 0; break;
        }
        return $vip_level;
    }
     // 微信支付回调
    function wx_notifyAction(){
        //接收微信返回的数据数据,返回的xml格式
       $xmlData = file_get_contents('php://input');
        //将xml格式转换为数组
        $data = $this->FromXml($xmlData);
        //用日志记录检查数据是否接受成功，验证成功一次之后，可删除。
        file_put_contents(APP_PATH."/wxlog.txt",$data."\r\n",FILE_APPEND);
        //为了防止假数据，验证签名是否和返回的一样。
        //记录一下，返回回来的签名，生成签名的时候，必须剔除sign字段。
        $sign = $data['sign'];
        unset($data['sign']);
        if($sign == $this->getSign($data)){
            $this->db = new dbModel();
            $result= $this->db->field("*")->table("tab_deposit")->where("pay_order_number = '{$data['out_trade_no']}' and pay_status=0")->find();
            $game=$this->db->field("*")->table("tab_deposit_game")->where("pay_order_number = '{$data['out_trade_no']}'")->find();
            $user=$this->db->field("balance,points")->table("tab_user")->where("id = '{$result['user_id']}'")->find();
            //签名验证成功后，判断返回微信返回的
            if ($data['result_code'] == 'SUCCESS' && $result) {
                //存在该订单并支付成功，进行修改
                //根据返回的订单号做业务逻辑
                $arr = array(
                    'pay_status' => 1,
                    'order_number'=>$data['transaction_id']
                );
                $re = $this->db->action($this->db->updateSql("deposit",$arr,"pay_order_number = '{$data['out_trade_no']}'"));
                //处理完成之后，告诉微信成功结果！
                if($re){
                    $balance['balance']=intval($user['balance'])+$result['pay_amount'];
                    $balance['vip_level']=$this->switch_user_level($balance['balance']);
                    if(empty($game)) {
                        if($this->recharge_point($result['pay_amount'])>0) {
                            $balance['points'] = intval($user['points']) + $this->recharge_point($result['pay_amount']);
                            //用户积分日志
                            $zs_user_bp['user'] = $result['user_id'];
                            $zs_user_bp['bp'] = "+" . $this->recharge_point($result['pay_amount']) . "积分";
                            $zs_user_bp['optime'] = date("Y-m-d", time()); //时间戳转为日期
                            $zs_user_bp['bp_type'] = "充值平台币";
                            $zs_user_bp['type'] = 0;
                            $this->db->action($this->db->insertSql("user_bp", $zs_user_bp));
                        }
                    }
                    else{
                        $user=$this->db->field("vip_level")->table("tab_user")->where("id = {$result['user_id']} ")->find();
                        $vip_level=$user['vip_level'];
                        $task=$this->db->field("game_id,game_name,is_member,point,v1,v2,v3,v4,v5,v6,v7,v8")->table("tab_recharge_point")->where("game_id = {$game['game_id']} ")->find();
                        $point=$task['point'];
                        if(!empty($task['is_member']))
                        {
                            switch ($vip_level){
                                case 1:
                                    $point=$point+$task['v1'];
                                    break;
                                case 2:
                                    $point=$point+$task['v2'];
                                    break;
                                case 3:
                                    $point=$point+$task['v3'];
                                    break;
                                case 4:
                                    $point=$point+$task['v4'];
                                    break;
                                case 5:
                                    $point=$point+$task['v5'];
                                    break;
                                case 6:
                                    $point=$point+$task['v6'];
                                    break;
                                case 7:
                                    $point=$point+$task['v7'];
                                    break;
                                case 8:
                                    $point=$point+$task['v8'];
                                    break;
                            }
                        }
                        $bp = $this->db->field("*")->table("tab_user_bp")->where(" user = {$result['user_id']} and game_id = '{$task['game_id']}' and type=3")->find();
                        $zs_user_bp['user'] = $result['user_id'];
                        $zs_user_bp['bp'] = "+".$point."积分";
                        $zs_user_bp['optime'] = date("Y-m-d");
                        $zs_user_bp['bp_type'] = "充值".$task['game_name'];
                        $zs_user_bp['type'] = 3;
                        $zs_user_bp['game_id'] = $task['game_id'];
                        $zs_user_bp['status'] = 0;
                        if(empty($bp)) {
                             $this->db->action($this->db->insertSql("user_bp", $zs_user_bp));
                        }
                    }
                    $this->db->action($this->db->updateSql("user",$balance,"id = {$result['user_id']}"));
                    echo '<xml>
              <return_code><![CDATA[SUCCESS]]></return_code>
              <return_msg><![CDATA[OK]]></return_msg>
              </xml>';exit();
                }
            }
            //支付失败，输出错误信息
            else{
                $arr = array(
                    'pay_status' =>2
                 );
                $this->db->action($this->db->updateSql("deposit",$arr,"pay_order_number = '{$data['out_trade_no']}'"));
                file_put_contents(APP_PATH."/wxlog.txt","微信支付错误信息：".$data['return_msg'].date("Y-m-d H:i:s")."\r\n",FILE_APPEND);

            }
        }
        else{
            $arr = array(
                'pay_status' =>2
            );
            $this->db->action($this->db->updateSql("deposit",$arr,"pay_order_number = '{$data['out_trade_no']}'"));
            file_put_contents(APP_PATH."/wxlog.txt","微信支付错误信息：签名验证失败".date("Y-m-d H:i:s")."\r\n",FILE_APPEND);
        }

    }
}