<?php
use Phalcon\Mvc\Model;
use app\core\Pdb;
class Zsshoporder extends Model{
    public $order_id;
    public $user_id;
    public $shop_id;
	public $user_name;
	public $user_tel;
	public $user_location;
    public $shop_banner;
    public $shop_name;
    public $shop_type;
    public $shop_price;
    public $shop_num;
    public $shop_total_price;
    public $pay_time;
    public $pay_type;
    public $pay_status;
    public $order_info;
    public $out_trade_no;
    public $trade_no;
    public $express_status;
    public $close_status;
    public $expire_time;
    //生成订单接口
    public function buildorder($reqdata){
        $order= new Zsshoporder();
        $order->user_id = $reqdata['id'];
        $order->user_name = $reqdata['user_name'];
        $order->user_tel = $reqdata['user_tel'];
        $order->user_location= $reqdata['user_province']." ".$reqdata['user_city']." ".$reqdata['user_area']." ".$reqdata['user_addr'];
        $order->shop_banner = $reqdata['shop_banner'];
        $order->shop_name = $reqdata['shop_name'];
        $order->shop_id=$reqdata['shop_id'];
        $order->shop_type = '';
        $order->shop_price = $reqdata['shop_price'];
        $order->shop_num = $reqdata['shop_num'];
        $order->shop_total_price = $reqdata['shop_total_price'];
        $order->order_info = '';
        $order->pay_time = time();
        $order->pay_type = "支付宝";
        $order->pay_status = 0;
        $order->out_trade_no =  empty($reqdata['out_trade_no'])?$this->out_trade_no():$reqdata['out_trade_no'];
        $order->express_status = 0;
        $order->close_status= 1;
        $bool=$order->save();
        if($bool){
            //$aliPayString=$this->apppay($_POST['shop_name'],$_POST['shop_name'],$data['out_trade_no'],$data['shop_total_price']);
            $shop['shop_name']= $reqdata['shop_name'];
            $shop['out_trade_no']= $order->out_trade_no;
            $shop['shop_total_price']= $reqdata['shop_total_price'];
            return $shop;
        }else{
            return 500;
        }
    }
    public function out_trade_no(){
        return "c".rand(100,999).uniqid()."p";
    }

    //我的订单
    public function myorder($reqdata){
        //include APP_PATH."/core/Pdb.php";
        $currentPage = empty($reqdata["page"])?"1":$reqdata["page"];
        $showPage = empty($reqdata["showpage"])?"10":$reqdata["showpage"];
        $start =  ($currentPage-1)*$showPage;
        $pdb = new Pdb();
        $user_id = $reqdata['id'];
        $type = empty($reqdata['type'])?0:$reqdata['type'];
        $field = "a.order_id,a.shop_banner,a.shop_name,a.shop_id,a.shop_num,a.shop_total_price,a.shop_price as price,a.out_trade_no,a.express_status";
        $where = "";
        switch ($type){
            case 0: $where = "a.user_id = {$user_id} and a.pay_status = 1 and a.close_status = 1"; break;
            case 1: $where = "a.user_id = {$user_id} and a.pay_status = 1 and a.express_status = 0 and a.close_status = 1"; break;
            case 2: $where = "a.user_id = {$user_id} and a.pay_status = 1 and a.express_status = 1 and a.close_status = 1"; break;
            case 3: $where = "a.user_id = {$user_id} and a.pay_status = 1 and a.express_status = 2 and a.close_status = 1"; break;
            case 4: $where = "a.user_id = {$user_id} and a.pay_status = 1 and a.express_status = 4 and a.close_status = 1"; break;
            default : $where = "a.user_id = {$user_id} and a.pay_status = 1"; break;
        }
        $data =$pdb->action("SELECT {$field} FROM zsshoporder a left join zsshop b on a.shop_name=b.shop_name WHERE {$where} ORDER BY a.order_id DESC   LIMIT {$start},{$showPage} ");
        return $data;
    }
}