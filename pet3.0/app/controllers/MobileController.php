<?php
use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Http\Request;
use app\core\Pdb;
class MobileController extends Controller{
    public function gameAction(){}

    //捐款详情H5
    public function contributiondetailAction(){
        include APP_PATH."/core/Pdb.php";
        $db=new Pdb();
        $db->action("set names utf8mb4");
        $params=$this->dispatcher->getParams();
        $r_id=$params[0];
        $id=$params[1];
        $order_amount = $db->field("SUM(pay_amount) as total,COUNT(*) as order_count")->table("zswealorder")->where("weal_id = {$r_id} and pay_status = 1")->find();
        if($order_amount['total']==null)
        {
            $order_amount['total']=0;
        }
        $zsrescue=new Zsrescue();
        $arrdata = $zsrescue::findFirst("r_id = '{$r_id}'");
        $arrdata->already_amount=$order_amount['total'];
        $arrdata->already_num=$order_amount['order_count'];
        $arrdata->content=htmlspecialchars_decode($arrdata->content);
        $user=$db->action("select a.uid,a.nick_name,(select sum(pay_amount) from zswealorder where user_id={$id}  and pay_status=1) as total from zsuser a where a.id={$id}");
        if(empty($user[0]['nick_name']))
        {
            $arrdata->nick_name=$user[0]['uid'];
        }
        else{
            $arrdata->nick_name=parseHtmlemoji($user[0]['nick_name']);
        }

        $arrdata->total=$user[0]['total'];
        $this->view->setVar("data",$arrdata);
    }

    //帖子分享
    public function stickshareAction(){
        include APP_PATH."/core/Pdb.php";
        $db=new Pdb();
        $db->action("set names utf8mb4");
        $params=$this->dispatcher->getParams();
        $m_id=$params[0];
        $sql = "SELECT u.id,u.uid,u.avatar,u.nick_name,m.m_id,m.create_time,m.content,m.pic,m.longitude,m.latitude,m.lbs
	    FROM zsuser as u 
	    INNER JOIN zsmap as m ON m.user_id = u.id
	    WHERE m.m_id = {$m_id}
	    ";
        $data=$db->action($sql);
        foreach ($data as $key=>$value)
        {
            if(empty($value['nick_name']))
            {
                $data[$key]['nick_name']=$value['uid'];
            }
            else{
                $data[$key]['nick_name']=parseHtmlemoji($value['nick_name']);
            }
            if(strpos($value['pic'],'.mp4') !== false){
                $data[$key]['type']=1;
                $type=1;
            }
            else{
                $data[$key]['type']=2;
                $type=2;
            }
                $pic=json_decode($value['pic']);
            if(count($pic)>1 && $type==1)
            {
                $data[$key]['pic']=$pic[1];
            }
            else{
                $data[$key]['pic']=$pic[0];
            }
            $feedback=$db->action("select a.body,b.nick_name,b.id from zsmapfeedback a left join zsuser b on a.user_id=b.id where a.m_id={$value['m_id']} order by id desc limit 1");
            if($feedback) {
                if(!empty($feedback[0]['nick_name']))
                {
                    $data[$key]['feedback_user'] =parseHtmlemoji($feedback[0]['nick_name']);
                }
                else{
                    $data[$key]['feedback_user'] =$feedback[0]['uid'];
                }
                $data[$key]['body']=$feedback[0]['body'];
                }
            else{
                $data[$key]['feedback_user'] ="";
                $data[$key]['body']="";
            }
        }
        $data[0]['content'] = htmlspecialchars_decode($data[0]['content']);
       // print_r($data[0]);exit;
        $this->view->setVar("data",$data[0]);
    }

    public function mapsharesuccessAction(){
        $reqdata = $this->request->getPost();
        $m_id=$reqdata['m_id'];
        include APP_PATH."/core/Pdb.php";
        $db=new Pdb();
        $db->action("set names utf8mb4");
        $result= $db->field("*")->table("zsmap")->where(" m_id = {$m_id}")->find();
        $data['share_num']=intval($result['share_num'])+1;
        $bool = $db->action($db->updateSql("zsmap",$data," m_id = {$m_id}"));
        echo json_encode(['code'=>0,'message'=>"success"],320);exit;


    }
    //广场帖子分享
    public function cardshareAction(){
        include APP_PATH."/core/Pdb.php";
        $db=new Pdb();
        $db->action("set names utf8mb4");
        $params=$this->dispatcher->getParams();
        $m_id=$params[0];
        $sql = "SELECT u.id,u.uid,u.avatar,u.nick_name,m.c_id,m.create_time,m.content,m.pic,m.longitude,m.latitude,m.lbs
	    FROM zsuser as u 
	    INNER JOIN zscard as m ON m.u_id = u.id
	    WHERE m.c_id = {$m_id}
	    ";
        $data=$db->action($sql);
        foreach ($data as $key=>$value)
        {
            if(empty($value['nick_name']))
            {
                $data[$key]['nick_name']=$value['uid'];
            }
            else{
                $data[$key]['nick_name']=parseHtmlemoji($value['nick_name']);
            }
            if(strpos($value['pic'],'.mp4') !== false){
                $data[$key]['type']=1;
                $type=1;
            }
            else{
                $data[$key]['type']=2;
                $type=2;
            }
            $pic=json_decode($value['pic']);
            if(count($pic)>1 && $type==1)
            {
                $data[$key]['pic']=$pic[1];
            }
            else{
                $data[$key]['pic']=$pic[0];
            }
            $feedback=$db->action("select a.body,b.nick_name,b.id from zscardfeedback a left join zsuser b on a.u_id=b.id where a.c_id={$value['c_id']} order by id desc limit 1");
            if($feedback) {
                if(!empty($feedback[0]['nick_name']))
                {
                    $data[$key]['feedback_user'] =parseHtmlemoji($feedback[0]['nick_name']);
                }
                else{
                    $data[$key]['feedback_user'] =$feedback[0]['uid'];
                }
                $data[$key]['body']=$feedback[0]['body'];
            }
            else{
                $data[$key]['feedback_user'] ="";
                $data[$key]['body']="";
            }
        }
        $data[0]['content'] = htmlspecialchars_decode($data[0]['content']);
        //print_r($data[0]);exit;
        $this->view->setVar("data",$data[0]);
    }
    public function cardsharesuccessAction(){
        $reqdata = $this->request->getPost();
        $m_id=$reqdata['c_id'];
        include APP_PATH."/core/Pdb.php";
        $db=new Pdb();
        $db->action("set names utf8mb4");
        $result= $db->field("*")->table("zscard")->where(" c_id = {$m_id}")->find();
        $data['share_num']=intval($result['share_num'])+1;
        $bool = $db->action($db->updateSql("zscard",$data," c_id = {$m_id}"));
        echo json_encode(['code'=>0,'message'=>"success"],320);exit;


    }
    //免责声明
    public function petdisclaimerAction(){

    }

    //使用规范
    public function petusingAction(){

    }

    //隐私政策
    public function petprivacyAction(){

    }

    //用户协议
    public function petuagreementAction(){

    }

    //宠物保险
    public function petinsureAction(){

    }

    //宠物医生
    public function petdoctorAction(){

    }

    //宠物清洁
    public function petcleanAction(){

    }

    //宠物理发
    public function pethaircutAction(){

    }

    //群分享
    public function groupshareAction(){
        include APP_PATH."/core/Pdb.php";
        $db=new Pdb();
        $params=$this->dispatcher->getParams();
        $id=$params[0];
        $g_id=$params[1];
        $zsgroup=new Zsgroup();
        $result = $zsgroup::findFirst("g_id = {$g_id}");
        $mdata = json_decode($result->members,true);
        $len = count($mdata);
        $data['group_num'] = $len+1;
        $user=Zsuser::findFirst("id = {$id}");
        $data['nick_name']=parseHtmlemoji($user->nick_name);
        if(empty($user->nick_name))
        {
            $data['nick_name']=$user->uid;
        }
        $data['banner']=$result->avatar;
        $data['title']=$result->title;
        $this->view->setVar("data",$data);
    }

}
