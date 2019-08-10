<?php
use Yaf\Dispatcher;
class ShareController extends EventController{
    //分享
    //圈子正文
    public function cotentAction(){
        Dispatcher::getInstance()->autoRender(false);
        $u_id = get("id");
        $a_id = get("a_id");
        $this->db->action("set names utf8mb4");
        $group_u1=$this->db->field("id,avatar,nickname,register_time,vip_level")->table("tab_user")->where("id = {$u_id}")->find();
        $group_t1=$this->db->field("pic,content,create_time")->table("tab_user_group")->where("id = {$a_id}")->find();

        $group_c1=['click_num'=>0];
        $result = array_merge($group_u1,$group_t1,$group_c1);
        $sql = "SELECT p.id as feedback_id,u.id,u.avatar,u.nickname,p.create_time,p.content
                FROM tab_group_feedback as p
                INNER JOIN tab_user as u ON u.id = p.u_id
                WHERE p.group_log_id = {$a_id} 
                ORDER BY p.create_time DESC 
                ";
        $result['reply_list'] = $this->db->action($sql);
        echo json_encode($result);
    }
    //h5圈子分享页面
    public function groupAction(){
        $this->getView()->assign(["xxx"=>"yyyy"]);
    }
    public function groupshareAction(){
        //$u_id = get("id");
        $a_id = get("a_id");
        $group=$this->db->field("share_num")->table("tab_user_group")->where("id = {$a_id}")->find();
        $data['share_num']=intval($group['share_num'])+1;
        if($this->db->action($this->db->updateSql("user_group",$data,"id={$a_id}"))){
             $this->ajax_return(0);
        }
        else{
            $this->ajax_return(500);
        }
    }
    //h5资讯分享页面
    public function newsAction(){
        $this->getView()->assign(["xxx"=>"yyyy"]);
    }
    //资讯正文
    public function newdetailAction(){
        Dispatcher::getInstance()->autoRender(false);
        $u_id = get("id");
        $n_id = get("n_id");
        $this->db->action("set names utf8mb4");
        $group_u1=$this->db->field("id,avatar,nickname,register_time,vip_level")->table("tab_user")->where("id = {$u_id}")->find();
        $group_t1=$this->db->field("title,content,create_time,click")->table("tab_news")->where("id = {$n_id}")->find();
        $group_t1['content']=htmlspecialchars_decode($group_t1['content']);
        $result = array_merge($group_u1,$group_t1);
        $sql = "SELECT p.id as feedback_id,u.id,u.avatar,u.nickname,p.create_time,p.content
                FROM tab_news_feedback as p
                INNER JOIN tab_user as u ON u.id = p.u_id
                WHERE p.news_id ={$n_id}
                ORDER BY p.create_time DESC 
                ";
        $result['reply_list'] = $this->db->action($sql);
        echo json_encode($result);
    }
    //h5游戏分享页面
    public function gameAction(){
        $this->getView()->assign(["xxx"=>"yyyy"]);
    }

    //盒子分享
    public function appshareAction(){
    }
    //游戏正文
    public function gamedetailAction(){
        Dispatcher::getInstance()->autoRender(false);
        $u_id = get("id");
        $game_id = get("game_id");
        $this->db->action("set names utf8mb4");
        $group_u1=$this->db->field("id,avatar,nickname,register_time,vip_level")->table("tab_user")->where("id = {$u_id}")->find();
         $group_t1=$this->db->field("game_name,introduction,screenshot")->table("tab_game")->where("id = {$game_id}")->find();
        $screen=explode(",",$group_t1['screenshot']);
        foreach($screen as $value)
        {
            $screenshot[]=get_cover($value,'path');
        }
        $group_t1['screenshot']=$screenshot;
        $group_t1['introduction']=htmlspecialchars_decode($group_t1['introduction']);
        $result = array_merge($group_u1,$group_t1);
        $sql = "SELECT p.id as feedback_id,u.id,u.avatar,u.nickname,p.create_time,p.content
                FROM tab_game_feedback as p
                INNER JOIN tab_user as u ON u.id = p.u_id
                WHERE p.game_id ={$game_id}
                ORDER BY p.create_time DESC 
                ";
        $result['reply_list'] = $this->db->action($sql);
        echo json_encode($result);
    }
    //盒子圈子分享
    public function circleAction(){
        if(isset($_GET['pf_id'])){
            $pf_id = $_GET['pf_id'];
            //头部
            $data=$this->db->field("id,avatar,nickname,u_sign")->table("tab_user")->where("id = {$pf_id}")->find();
            $res = $this->db->field("*")->table("tab_group")->where("user_id = {$pf_id}")->select();
            if(empty($data)){
                $this->ajax_return_320(1013);
            }
            $data['user_num'] = $this->db->zscount("user_follow","*","total","pf_id = {$pf_id} ");
            $data['group_type_num'] = count($res);
            //列表
            $res = $this->db->field("*")->table("tab_group")->where("user_id = {$pf_id}")->select();
            $indata = [];
            foreach ($res as $key=>$value){
                $indata[] = $value['group_type_id'];
            }
            $str = implode(",",$indata);
            if(!empty($str)) {
                $sql = "
                 SELECT A.id as a_id,U.id,U.avatar,U.nickname,A.create_time,A.content,A.pic,A.game_id,A.share_num
                 FROM tab_user_group as A
                 INNER JOIN tab_user as U ON A.u_id = U.id
                 WHERE A.game_id IN ($str) AND U.id = {$pf_id}
                 ORDER BY A.id DESC
                 LIMIT 0,10
                 ";
                $result = $this->db->action($sql);
                foreach ($result as $k => $v) {
                    $result[$k]['content'] = htmlspecialchars_decode($v['content']);
                    $result[$k]['create_time'] = get_time($v['create_time']);
                    $result[$k]['is_click'] = 0;
                    $result[$k]['click_num'] = $this->db->zscount("group_click", "*", "total", "group_log_id = {$v['a_id']} ");
                    $result[$k]['feedback_num'] = $this->db->zscount("group_feedback", "*", "total", "group_log_id = {$v['a_id']} ");
                    // $result[$k]['share_num'] = $this->db->zscount("group_share", "*", "total", "group_log_id = {$v['a_id']} ");
                }
            }else{
                $result=[];
            }
            $this->getView()->assign(["result"=>$result,"data"=>$data]);
        }else{
            $this->ajax_return_320(1013);
        }
    }
}
