<?php
use Phalcon\Mvc\Controller;
use Phalcon\Http\Request;

class InfoController extends Controller
{
	public $error;
	public $pdb;
	public $session;

    public function initialize(){
        include APP_PATH."/core/Errorcode.php";
        $this->error = \app\core\Errorcode::getCodeConfig();
        include APP_PATH."/core/Pdb.php";
        $this->pdb = new \app\core\Pdb();
        include APP_PATH."/core/Session.php";
        $this->session = new \app\core\Session();
        $this->pdb->action("set names utf8mb4");
    }
    public function ajax_return($code,$data = null){
        if(is_null($data)){
            echo json_encode(['code'=>$code,'message'=>$this->error[$code]],320);exit;
        }else{
            echo json_encode(['code'=>$code,'message'=>$this->error[$code],"data"=>$data],320);exit;
        }
    }

    //好友申请
    public function addfriendAction(){
        $me_id=$this->request->getPost('id');
        $friend_id=$this->request->getPost('pf_id');
        $zsfriend=new Zsfriend();
        $code=$zsfriend->addfriend($me_id,$friend_id);
        $this->ajax_return($code);

    }
    //申请接受或拒绝
    public function makefriendAction(){
        $apply_id=$this->request->getPost('apply_id');
        $status=$this->request->getPost('status');
        $zsfriend=new Zsfriend();
        $code=$zsfriend->makefriend($apply_id,$status);
        $this->ajax_return($code);

    }

    //联系人
    public function connectlistAction(){
        $me_id=$this->request->getPost('id');
       // $zsuser = new Zsuser();
        $this->pdb->action("set names utf8mb4");
        $arrdata = $this->pdb->field("*")->table('zsuser')->where("id={$me_id}")->find();
        $type=$this->request->getPost('type');
        if($type=='1'){
            $data=$this->pdb->action("select a.avatar,a.nick_name,a.id ,a.uid from zsuser a left join zsfriend b on a.id=b.friend_id
 where b.me_id={$me_id} and b.status='1' order by b.create_time desc");
            foreach ($data as $k=>$v){
                if(!empty($v['nick_name'])) {
                    $data[$k]['nick_name'] = parseHtmlemoji($data[$k]['nick_name']);
                }
                else{
                    $data[$k]['nick_name'] =$v['uid'];
                }

            }

        }
        elseif($type=='2')
        {
            $data=$this->pdb->action("select a.avatar,a.nick_name,a.id ,a.uid,b.id as apply_id,b.status  from zsuser a left join zsfriend b on a.id=b.me_id
 where b.friend_id={$me_id} order by b.create_time desc");
            foreach ($data as $k=>$v){
                if(!empty($v['nick_name'])) {
                    $data[$k]['nick_name'] = parseHtmlemoji($data[$k]['nick_name']);
                }
                else{
                    $data[$k]['nick_name'] =$v['uid'];
                }

            }
        }
        elseif ($type=='3')
        {
            $where = "  status!=2 and owner_id  like '%{$arrdata['uid']}%' or members  like '%{$arrdata['uid']}%'";
            $data=$this->pdb->action("select banner,g_id,title,groupid,info,lbs,members,status from zsgroup where {$where}");
            foreach ($data as $k=>$v){
                $data[$k]['title'] = htmlspecialchars_decode( $data[$k]['title']);
                $data[$k]['info'] = htmlspecialchars_decode( $data[$k]['info']);
                $data[$k]['group_num'] = count(json_decode($v['members'],true))+1;
                unset($data[$k]['members']);
            }
        }
        $this->ajax_return(0,$data);
    }

    //动态头部
    public function gettopAction(){
        $reqdata=$this->request->getPost();
        $zsmap=new Zsfriend();
        $code=$zsmap->gettop($reqdata);
        if(is_numeric($code))
        {
            $this->ajax_return($code);
        }
        else{
            $this->ajax_return(0,$code);
        }
    }
    //动态
    public function pzoneAction(){
        $reqdata=$this->request->getPost();
        $zsmap=new Zsfriend();
        $code=$zsmap->pzone($reqdata);
        if(is_numeric($code))
        {
            $this->ajax_return($code);
        }
        else{
            $this->ajax_return(0,$code);
        }
    }

    //app意见反馈
    public function debugAction(){
        $reqdata=$this->request->getPost();
        $data['phone']=$reqdata['phone'];
        $data['debug']=parseEmojiTounicode($reqdata['debug']);
        $data['create_time']=time();
        $bool = $this->pdb->action($this->pdb->insertSql("zsdebug",$data));
        if($bool){
            $this->ajax_return(0);
        }else{
            $this->ajax_return(500);
        }

    }
    //系统通知
    public function systemlistAction(){
        $reqdata=$this->request->getPost();
        $user_id=$reqdata['id'];
        $currentPage = empty($_POST["page"]) ? "1" : $_POST["page"];
        $showPage = empty($_POST["showpage"]) ? "10" : $_POST["showpage"];
        $start = ($currentPage - 1) * $showPage;
        $data=$this->pdb->action("select * from zssystemnotify order by create_time desc limit {$start},{$showPage}");
        foreach ($data as $key=>$value)
        {
            $result = $this->pdb->field("*")->table("zsusernotice")->where("u_id={$user_id} and notice_id={$value['id']}")->find();
            if(!empty($result))
            {
                $data[$key]['status']=1;
            }
        }
        $this->ajax_return(0,$data);
    }
    //读取通知
    public function readnoticeAction(){
        $reqdata=$this->request->getPost();
        $user_id=$reqdata['id'];
        $notice_id=$reqdata['notice_id'];
        $result = $this->pdb->field("*")->table("zsusernotice")->where("u_id={$user_id} and notice_id={$notice_id}")->find();
        $notify = $this->pdb->field("*")->table("zssystemnotify")->where("id={$notice_id}")->find();
        unset($notify['status']);
        if(empty($result))
        {
            $data['u_id']=$user_id;
            $data['notice_id']=$notice_id;
            $bool=$this->pdb->action( $this->pdb->insertSql("zsusernotice",$data));
            if($bool)
            {
                $this->ajax_return(0,$notify);
            }
            else{
                $this->ajax_return(500);
            }
        }
        else{
            $this->ajax_return(0,$notify);
        }
    }
    //关于我们
    public function aboutusAction(){
        $reqdata=$this->request->getPost();
        if($reqdata['type']=='ios') {
            $setting = $this->pdb->field("*")->table("zsiossetting")->find();
        }
        else{
            $setting = $this->pdb->field("*")->table("zssetting")->find();
        }
        $this->ajax_return(0,$setting);
    }

    //广场帖子通知
    public function postmessageAction(){
        $reqdata = $this->request->getPost();
        $user_id=$reqdata['id'];
        $currentPage = empty($reqdata["page"]) ? "1" : $reqdata["page"];
        $showPage = empty($reqdata["showpage"]) ? "10" : $reqdata["showpage"];
        $start = ($currentPage - 1) * $showPage;

        //广场帖子点赞
        $mapclick=$this->pdb->action("SELECT a.u_id as id,a.create_time,a.c_id,b.nick_name,b.uid,b.avatar,c.content,c.pic from zscardclick a left join zsuser b on a.u_id=b.id
 left join zscard c on a.c_id=c.c_id   where a.card_user_id={$user_id} ");

         foreach ($mapclick as $key=>$value)
        {
            $mapclick[$key]['content'] = htmlspecialchars_decode($value['content']);
            $mapclick[$key]['mtype']=1;//点赞
            $mapclick[$key]['reply_user']="";
            $mapclick[$key]['pic']=json_decode($value['pic']);
            if(!empty($value['nick_name'])) {
                $mapclick[$key]['nick_name'] = parseHtmlemoji($mapclick[$key]['nick_name']);
            }
            else{
                $mapclick[$key]['nick_name']=$value['uid'];
            }

        }
        //广场帖子评论
        $mapfeedback=$this->pdb->action("select a.u_id as id,a.feedtime as create_time,a.body as content,b.nick_name,b.avatar,b.uid,c.pic ,
a.c_id from zscardfeedback a left join zsuser b on a.u_id=b.id left join zscard c on a.c_id=c.c_id  where a.card_user_id={$user_id}");
        foreach ($mapfeedback as $key=>$value)
        {
            $mapfeedback[$key]['content'] = htmlspecialchars_decode($value['content']);
            $mapfeedback[$key]['mtype']=2;
            $mapfeedback[$key]['pic']=json_decode($value['pic']);
            $mapfeedback[$key]['reply_user']="";
            if(!empty($value['nick_name'])) {
                $mapfeedback[$key]['nick_name'] = parseHtmlemoji($mapfeedback[$key]['nick_name']);
            }
            else{
                $mapfeedback[$key]['nick_name']=$value['uid'];
            }
        }
        //广场帖子回复
        $map_reply=$this->pdb->action("select a.user_id as id,a.create_time,a.content,a.floor_id,b.nick_name,b.avatar,b.uid,a.feedback_id as c_id from zscardreply a
left join zsuser b on a.user_id=b.id left join zscardfeedback c on a.feedback_id=c.id where a.feedback_user_id={$user_id}");
        foreach ($map_reply as $key=>$value)
        {
            $map_reply[$key]['mtype']=3;
            if(!empty($value['nick_name'])) {
                $map_reply[$key]['nick_name'] = parseHtmlemoji($map_reply[$key]['nick_name']);
            }
            else{
                $map_reply[$key]['nick_name'] =$value['uid'];
            }
            if($value['floor_id']==0)
            {
                $map_reply[$key]['reply_user']="你";
            }
            else{
                $result=$this->pdb->action("select a.nick_name,a.id from zsuser a left join zscardreply b on a.id=b.user_id where b.id={$value['floor_id']}");
                if(!empty($result[0]['nick_name']))
                {
                    $map_reply[$key]['reply_user']=parseHtmlemoji($result[0]['nick_name']);
                }
                else{
                    $map_reply[$key]['reply_user']=$result[0]['uid'];
                }

            }
            $map=$this->pdb->action("select a.pic from zscard a left join zscardfeedback b on a.c_id=b.c_id where b.id={$value['c_id']}");
            if(count($map)>0) {
                $map_reply[$key]['pic'] = json_decode($map[0]['pic']);
            }
            unset($map_reply[$key]['floor_id']);
            unset($map_reply[$key]['feedback_id']);
        }
        $data = array_merge($mapclick,$mapfeedback,$map_reply);
        $data=$this->test($data);
        $data = array_slice($data,$start,$showPage);
       $this->ajax_return(0,$data);
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