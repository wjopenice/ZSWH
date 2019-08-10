<?php
use Phalcon\Mvc\Model;
use app\core\Pdb;
class Zsfriend extends Model{
    public $id;
    public $friend_id;
	public $me_id;
	public $create_time;
	public $status;
    public function initialize(){
        $this->pdb = new Pdb();
        $this->pdb->action("set names utf8mb4");
    }
	//加好友申请
	public function addfriend($me_id,$friend_id){
        $zsfriend=new Zsfriend();
        $result = $zsfriend::findFirst(" me_id = '{$me_id}' and friend_id = '{$friend_id}' and status='0'");
        if(!empty($result))
        {
            return 0;
        }
        else{
            $zsfriend->friend_id=$friend_id;
            $zsfriend->me_id=$me_id;
            $zsfriend->status=0;
            $zsfriend->create_time=time();
            if($zsfriend->save()){
                return 0;
            }
            else{
                return 500;
            }
        }


    }

    //好友请求判断
    public function makefriend($apply_id,$status)
    {
        $zsfriend=new Zsfriend();
        $result = $zsfriend::findFirst(" id = {$apply_id}  and status='0' ");
        if(empty($result))
        {
            return 1303;
        }
        if($status==1)
        {
            $result->status=1;
            $result->save();
            $zsfriend->friend_id=$result->me_id;
            $zsfriend->me_id=$result->friend_id;
            $zsfriend->status=1;
            $zsfriend->create_time=time();
            $zsfriend->save();
        }
        else{
            $result->status=2;
            $result->save();
        }
        return 0;
    }
    public function gettop($reqdata)
    {
        if (substr($reqdata['id'], 0, 1) == "C") {
            $userData = $this->pdb->field("*")->table("zsuser")->where("uid = '{$reqdata['id']}'")->find();
            $pfData = $this->pdb->field("*")->table("zsuser")->where("uid = '{$reqdata['pf_id']}'")->find();
            $u_id = $userData['id'];
            $f_id = $pfData['id'];
        } else {
            $u_id = $reqdata['id'];
            $f_id = $reqdata['pf_id'];
        }
        if($u_id!=$f_id) {
            $res=$this->pdb->field("*")->table("zsfriend")->where("me_id = {$u_id} and friend_id = {$f_id} ")->order("id desc")->find();
            if($res)
            {
                if($res['status']==0)
                {
                    $is_attention=2;
                }
                elseif ($res['status']==1)
                {
                    $is_attention=1;
                }
                elseif ($res['status']==2)
                {
                    $is_attention=0;
                }
            }
            else{
                $is_attention=0;
            }
        }
        else{
            $is_attention=0;
        }
        $user=$this->pdb->field("id,uid,nick_name,avatar,sex,user_level,location_province,location_city,birthday,u_sign")->table("zsuser")->where("id={$f_id}")->find();
        $user['nick_name']=parseHtmlemoji($user['nick_name']);
        $user['u_sign']=parseHtmlemoji($user['u_sign']);
        $user['is_friend']=$is_attention;
        return $user;
    }
    //主页动态
    public function pzone($reqdata){
        if (substr($reqdata['id'], 0, 1) == "C") {
            $userData = $this->pdb->field("*")->table("zsuser")->where("uid = '{$reqdata['id']}'")->find();
            $pfData = $this->pdb->field("*")->table("zsuser")->where("uid = '{$reqdata['pf_id']}'")->find();
            $u_id = $userData['id'];
            $f_id = $pfData['id'];
        } else {
            $u_id = $reqdata['id'];
            $f_id = $reqdata['pf_id'];
        }
        $currentPage = empty($reqdata["page"])?"1":$reqdata["page"];
        $showPage = empty($reqdata["showpage"])?"10":$reqdata["showpage"];
        $start =  ($currentPage-1)*$showPage;
        if($u_id!=$f_id) {
            $is_attention = empty($this->pdb->field("*")->table("zsfriend")->where("me_id = {$u_id} and friend_id = {$f_id} and status='1'")->find()) ? "0":"1";
           /* if($is_attention==0)
            {
                $start=0;
                $showPage=3;
            }*/
        }
        else{
            $is_attention=0;
        }
        $this->pdb->action("set names utf8mb4");
        //帖子列表
        $userinfo = $this->pdb->field("*")->table("zsuser")->where("id = {$f_id}")->find();
        if(!empty($userinfo)){
            $sqlstr = "SELECT c.m_id as post_id,u.avatar,u.nick_name,u.uid, u.id,c.lbs,c.content,c.pic,c.create_time,c.share_num FROM zsmap as c LEFT JOIN zsuser as u ON u.id = c.user_id   WHERE c.user_id = {$f_id} ORDER BY c.m_id DESC ";
            $userinfomap = $this->pdb->action($sqlstr);
            foreach ($userinfomap as $key=>$value){
                $userinfomap[$key]['pic']=json_decode($value['pic']);
                $userinfomap[$key]['is_click'] = !empty($this->pdb->field("*")->table("zsmapclick")->where("u_id = {$u_id} and m_id = {$value['post_id']}")->select()) ? 1 : 0;
                $userinfomap[$key]['feedback_num']=$this->pdb->zscount("mapfeedback","*","total","m_id={$value['post_id']}");
                $userinfomap[$key]['click_num']=$this->pdb->zscount("mapclick","*","total","m_id={$value['post_id']}");
               // $userinfomap[$key]['share_num']="0";
                $userinfomap[$key]['ntype']=1;
                $userinfomap[$key]['is_lbs']=1;
                if(!empty($value['nick_name']))
                {
                    $userinfomap[$key]['nick_name']=parseHtmlemoji( $value['nick_name']);
                }
                else{
                    $userinfomap[$key]['nick_name']=$value['uid'];
                }

             }
            $userinfocard= $this->pdb->action("SELECT c.c_id as post_id,u.avatar,u.nick_name, u.id,u.uid,c.lbs,c.content,c.pic,c.create_time,c.is_lbs,c.share_num  FROM zscard 
as c LEFT JOIN zsuser as u ON u.id = c.u_id   WHERE c.u_id = {$f_id} ORDER BY c.c_id DESC");
            foreach ($userinfocard as $key=>$value){
                $userinfocard[$key]['pic']=json_decode($value['pic']);
                $userinfocard[$key]['is_click'] = !empty($this->pdb->field("*")->table("zscardclick")->where("u_id = {$u_id} and c_id = {$value['post_id']}")->select()) ? 1 : 0;
                $userinfocard[$key]['feedback_num']=$this->pdb->zscount("cardfeedback","*","total","c_id={$value['post_id']}");
                $userinfocard[$key]['click_num']=$this->pdb->zscount("cardclick","*","total","c_id={$value['post_id']}");
                //$userinfocard[$key]['share_num']="0";
                $userinfocard[$key]['ntype']=2;
                if(!empty($value['nick_name']))
                {
                    $userinfocard[$key]['nick_name']=parseHtmlemoji( $value['nick_name']);
                }
                else{
                    $userinfocard[$key]['nick_name']=$value['uid'];
                }
            }
            $datas = array_merge($userinfocard,$userinfomap);
            $datas=$this->test($datas);
            $datas = array_slice($datas,$start,$showPage);
            return $datas;
        }else{
            return 1004;
        }

    }
    public function test($data){

        $newArr = array();

        foreach($data as $key=>$v){
            $newArr[$key]['create_time'] = $v['create_time'];
        }
        array_multisort($newArr,SORT_DESC,$data);//SORT_DESC为降序，SORT_ASC为升序
        return $data;

    }

}