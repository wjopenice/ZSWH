<?php
use Phalcon\Mvc\Model;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use app\core\Pdb;
class Zsrescue extends Model{
    public $r_id;
    public $title;
	public $minititle;
	public $banner;
	public $content;
    public $target_amount;
    public $sponsor;
    public $receiver;
    public $expire;
    public $create_time;
    public $mech_name;
    public $mech_cid;
    public $number;
    public $bankcard;
    public $quarantine;
    public $diagnosis;
	public function index_model($reqdata){
        $currentPage = empty($reqdata["page"])?"1":$reqdata["page"];
        $showPage = empty($reqdata["showpage"])?"9":$reqdata["showpage"];
        $start =  ($currentPage-1)*$showPage;
        $robots = $this->modelsManager->createBuilder()
            ->columns(["r_id","banner","title","minititle"])
            ->from('Zsrescue')
            ->where("expire = 1")
            ->orderBy('r_id DESC')
            ->limit($showPage,$start)
            ->getQuery()
            ->execute()
            ->toArray();
        if($robots){
            foreach ($robots as $k=>$v){
                $robots[$k]['pic'] = json_decode($v['banner'],true)[0];
                unset($robots[$k]['banner']);
            }
            return $robots;
        }else{
            return [];
        }
    }

    public function detail_model($reqdata){
       // include APP_PATH."/core/Pdb.php";
	    $db=new Pdb();
        $currentPage = empty($reqdata["page"])?"1":$reqdata["page"];
        $showPage = empty($reqdata["showpage"])?"9":$reqdata["showpage"];
        $start =  ($currentPage-1)*$showPage;
       // $result = self::findFirst(" (expire = 1 or expire = 2) and r_id = {$reqdata['r_id']}")->toArray();
        $result= $db->field("*")->table("zsrescue")->where(" (expire = 1 or expire = 2) and r_id = {$reqdata['r_id']}")->find();
        if($result){
            $result['banner']=json_decode($result['banner']);
            $order_amount = $db->field("SUM(pay_amount) as total,COUNT(*) as order_count")->table("zswealorder")->where("weal_id = {$reqdata['r_id']} and pay_status = 1")->find();
            if($order_amount['total']==null)
            {
                $order_amount['total']=0;
            }
            $result['content'] = htmlspecialchars_decode($result['content']);
            $result['already_amount'] =  $order_amount['total'];
            $result['r_num'] =  $order_amount['order_count'];
            $data['rescuelist'] = $result;
            $sql = "select a.weal_id as r_id,a.user_id as id,b.nick_name ,b.uid,a.pay_amount,a.pay_time,b.avatar,a.is_hide from zswealorder a left join zsuser b on a.user_id=b.id where a.weal_id='" .$reqdata['r_id']. "' 
        and a.pay_status=1 and a.pay_weal_status=1  order by a.pay_time desc LIMIT {$start},{$showPage}  ";
             $rescuelog = $db->action($sql);
            foreach ($rescuelog as $key => $value) {
                $rescuelog[$key]['pay_time'] = get_time($value['pay_time']);
                $rescuelog[$key]['nick_name']=parseHtmlemoji($rescuelog[$key]['nick_name']);
            }
            $data['rescuelog'] = $rescuelog;
            return $data;
        }else{
            return (object)[];
        }
    }
    public function createorder($request){
        $user_id=$request['id'];
        $user= Zsuser::findFirst("id={$user_id}")->toArray();
        $weal_id=$request['r_id'];
        $weal=self::findFirst("r_id = {$weal_id}")->toArray();
        if(empty($user)||empty($weal) || empty($request['pay_amount']))
        {
            return 102;
        }
        $wealorder=new Zswealorder();
        $wealorder->user_id=$user_id;
        $wealorder->weal_id=$request['r_id'];
        $wealorder->weal_title=$weal['title'];
        $wealorder->pay_amount=$request['pay_amount'];
        $wealorder->is_hide=$request['is_hide'];
        $wealorder->pay_time=time();
        $wealorder->pay_status=0;
        $wealorder->order_number=$this->build_order_no();
        $wealorder->pay_weal_status=0;
        $wealorder->pay_way=0;
        $wealorder->spend_ip=getIp();
        $wealorder->certno='';
        $bool = $wealorder->save();
        if($bool)
        {
            $order['title']=$weal['title'];
            $order['out_trade_no']=$wealorder->order_number;
            $order['pay_amount']=$request['pay_amount'];
            return $order;
        }
        else{
            return 500;
        }

    }
    //订单生成
    function build_order_no(){
        return date('Ymd').substr(implode(array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }

}