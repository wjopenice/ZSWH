<?php
use Phalcon\Mvc\Model;
use app\core\Pdb;
class Zswealorder extends Model{
    public $id;
    public $user_id;
	public $weal_id;
	public $weal_title;
	public $order_number;
    public $pay_amount;
    public $pay_time;
    public $pay_status;
    public $pay_weal_status;
    public $pay_way;
    public $spend_ip;
    public $pay_order_number;
    public $is_hide;
    public $certno;
    public function mydonation($reqdata)
    {
       // include APP_PATH."/core/Pdb.php";
        $pdb = new Pdb();
        $id = $reqdata['id'];
        $currentPage = empty($reqdata["page"]) ? "1" : $reqdata["page"];
        $showPage = empty($reqdata["showpage"]) ? "5" : $reqdata["showpage"];
        $start = ($currentPage - 1) * $showPage;
        $orderdata =$pdb->field("SUM(pay_amount) as order_amount,COUNT(*) as order_count")->table("zswealorder")->where("user_id = {$id} and pay_status = 1")->find();
        //宠爱之家App获取某用户的全部捐款信息
        $sql = "select a.weal_id as r_id,a.pay_amount,a.pay_time,b.title ,b.banner,b.target_amount from zswealorder a  left join 
 zsrescue b on a.weal_id=b.r_id where a.user_id='" . $id . "'  and a.pay_status=1 and a.pay_weal_status=1  order by a.pay_time desc LIMIT {$start},{$showPage}  ";
        $orders = $pdb->action($sql);
        foreach ($orders as $key => $value) {
             $pay_amount = $pdb->field("SUM(pay_amount) as order_amount")->table("zswealorder")->where("weal_id = {$value['r_id']} and pay_status = 1")->find();
            $orders[$key]['already_amount'] = $pay_amount['order_amount'];
           $banner=json_decode($value['banner'],true);
           if(count($banner)>0) {
               $orders[$key]['pic'] = $banner[0];
           }
           else{
               $orders[$key]['pic']="";
           }
            unset( $orders[$key]['banner']);
        }
        if($orderdata['order_amount']==null)
        {
            $orderdata['order_amount']=0;
        }
        $result['order_amount']=$orderdata['order_amount'];

        $data['userdata'] = $result;
        $data['orderdata'] = $orders;
        return $data;
    }
}