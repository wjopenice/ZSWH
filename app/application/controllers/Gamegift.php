<?php
use Yaf\Dispatcher;
use Helper\Page;
class GamegiftController extends EventController {
    public $user;
    public function init(){
        parent::init();
        if(!empty($_SESSION['username'])){
            $this->user = $_SESSION['username'];
        }else{
            $this->success("请先登陆!","/login/login");
            exit;
        }
    }
    //入口
    public function gamegiftAction(){
        $page = new Page();
        $len = $this->db->zscount("giftbag");
        $showpage = 12;
        $page->init($len,$showpage);
        $strpage = "select a.*,b.* from tab_giftbag a left join tab_gift_position b on a.id=b.gift_id order by a.id desc {$page->limit}";
        $gift = $this->db->action($strpage);
        foreach ($gift as $key=>$value)
        {
            $gift[$key]['novice_count']=intval($this->db->zscount("gift_record","*","total","gift_id={$value['id']}"));
            $ji=$this->db->field("novice")->table("tab_giftbag")->where("id={$value['id']}")->find();
            $at=explode(",",$ji['novice']);
            $gift[$key]['novice_total']=intval($gift[$key]['novice_count']+count($at));
            $gift[$key]['remain_count']=count($at);
        }
        $showstr = $page->show();
        $this->getView()->assign(["gift" => $gift,"showstr"=>$showstr]);
    }

    public function addgamegiftAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
           // print_r($_POST);exit;
            $game_id = $this->posts['game_id'];
            $hot=$this->db->action(" select a.id,game_name,a.icon from tab_game a   where a.id = {$game_id} ");
            $data['gift_icon'] = $hot[0]['icon'];
            $data['game_name'] = $hot[0]['game_name'];
            $data['game_id'] = $game_id;
            $data['giftbag_name'] =$this->posts['giftbag_name'];
            $data['desribe'] =$this->posts['desribe'];
            $data['digest'] =$this->posts['digest'];
            $data['status'] =$this->posts['status'];
            $data['create_time']=time();
            if(!empty($this->posts['position'])) {
                $new['position'] = implode(",", $this->posts['position']);
            }
            if(strpos($new['position'],'5') !== false){
                if($this->posts['start_date'] && $this->posts['end_date']) {
                    $new['start_date'] = strtotime($this->posts['start_date'] . " 00:00:00");
                    $new['end_date'] = strtotime($this->posts['end_date'] . " 23:59:59");
                }
            }else{
                $data['start_time'] =strtotime($this->posts['start_time']." 00:00:00");
                $data['end_time'] =strtotime($this->posts['end_time']." 23:59:59");
            }
            $data['novice'] = str_replace(array("\r\n", "\r", "\n"), ",", $_POST['novice']);
          $bool = $this->db->action($this->db->insertSql("giftbag",$data));
            $new['gift_id']= $this->db->getInsertId();
          $this->db->action($this->db->insertSql("gift_position",$new));
             $this->statusUrl($bool,"添加成功","/gamegift/gamegift","添加失败");


        }else {
            $game = $this->db->action("SELECT id,game_name FROM tab_game WHERE game_status=1");
            $this->getView()->assign(["game" => $game]);
        }
    }
    public function editgamegiftAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            // print_r($_POST);exit;
            $id=$this->posts['id'];
            $game_id = $this->posts['game_id'];
            $hot=$this->db->action(" select a.id,game_name,a.icon from tab_game a   where a.id = {$game_id} ");
            $data['gift_icon'] = $hot[0]['icon'];
            $data['game_name'] = $hot[0]['game_name'];
            $data['game_id'] = $game_id;
            $data['giftbag_name'] =$this->posts['giftbag_name'];
            $data['desribe'] =$this->posts['desribe'];
            $data['digest'] =$this->posts['digest'];
            $data['status'] =$this->posts['status'];
            $data['create_time']=time();
            if(!empty($this->posts['position'])) {
                $new['position'] = implode(",", $this->posts['position']);
            }
            else{
                $new['position']='';
            }
            if(!empty($new['position']))
            {
                if(strpos($new['position'],'5') !== false){
                    if($this->posts['start_date'] && $this->posts['end_date']) {
                        $new['start_date'] = strtotime($this->posts['start_date'] . " 00:00:00");
                        $new['end_date'] = strtotime($this->posts['end_date'] . " 23:59:59");
                        $this->db->action("update tab_giftbag set start_time=null,end_time=null where id={$id}");
                    }
                }else{
                    $data['start_time'] =strtotime($this->posts['start_time']." 00:00:00");
                    $data['end_time'] =strtotime($this->posts['end_time']." 23:59:59");
                    $this->db->action("update tab_gift_position set start_date=null,end_date=null where gift_id={$id}");
                }
            }
            else{
                $data['start_time'] =strtotime($this->posts['start_time']." 00:00:00");
                $data['end_time'] =strtotime($this->posts['end_time']." 23:59:59");
                $this->db->action("update tab_gift_position set start_date=null,end_date=null where gift_id={$id}");
            }

            $data['novice'] = str_replace(array("\r\n", "\r", "\n"), ",", $_POST['novice']);
            $bool = $this->db->action($this->db->updateSql("giftbag",$data,"id = {$id}"));
            $arrData = $this->db->field("*")->table("tab_gift_position")->where("gift_id={$id}")->find();
            if(!empty($arrData))
            {
                $this->db->action($this->db->updateSql("gift_position",$new,"gift_id = {$id}"));
            }
            else{
                $new['gift_id']=$id;
                $this->db->action($this->db->insertSql("gift_position",$new));
            }

            $this->statusUrl($bool,"修改成功","/gamegift/gamegift","修改失败");


        }else {
            $id = $this->requests->get("id");
            $data =$this->db->action("select a.*,b.* from tab_giftbag a left join tab_gift_position b on a.id=b.gift_id WHERE a.id={$id}");
            $game = $this->db->action("SELECT id,game_name FROM tab_game WHERE game_status=1");
            $novice=explode(",",$data[0]['novice']);
            $data=$data[0];
            $position = explode(",", $data['position']);
            $data['recommend']=0;
            $data['hot']=0;
            $data['special']=0;
            $data['luxury']=0;
            $data['appointment']=0;
            $data['sign']=0;
            foreach ($position as $value)
            {
                if($value==1)
                {
                    $data['recommend']=1;
                }
                elseif($value==2)
                {
                    $data['hot']=1;
                }
                elseif($value==3)
                {
                    $data['special']=1;
                }
                elseif($value==4)
                {
                    $data['luxury']=1;
                }
                elseif($value==5)
                {
                    $data['appointment']=1;
                }
                elseif($value==6)
                {
                    $data['sign']=1;
                }
            }
           $this->getView()->assign(["novice" => $novice,"game" => $game,'data'=>$data]);
        }
    }
    public function delgiftAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = $this->requests->get('id');
        $bool = $this->db->action($this->db->deleteSql("giftbag","id = {$id}"));
        $this->db->action($this->db->deleteSql("gift_position","gift_id = {$id}"));
        $this->statusUrl($bool,"删除成功","/gamegift/gamegift","删除失败");
    }
}

