<?php
use Yaf\Dispatcher;
use Helper\Page;
class IndexController extends EventController {
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
    public function indexAction() {//默认Action
        $filename = APP_PATH."/public/menu.json";
        $jsonstr = file_get_contents($filename);
        $arr = json_decode($jsonstr,true);
        $this->getView()->assign(["username"=>$this->user,"arr"=>$arr,"people"=>[],"year"=>date("Y"),"monthdata"=>[]]);
    }
    //管理员
    public function administratorsAction(){
        $arrData = $this->db->field("*")->table("tab_system")->select();
        $this->getView()->assign(["arrData"=>$arrData]);
    }
    //用户
    public function userAction(){
        $page = new Page();
        $len = $this->db->zscount("user");
        $showpage = 12;
        $page->init($len,$showpage);
        $strpage = "SELECT id,account,nickname,email,phone,register_time,login_time,promote_account,points,vip_level FROM tab_user ORDER BY id DESC {$page->limit}";
        $userData = $this->db->action($strpage);
        $showstr = $page->show();
        $this->getView()->assign(["userData"=>$userData,"showstr"=>$showstr]);
    }
    //新闻分类
    public function newstypeAction(){
        $data = $this->db->field("id,type,url")->table("tab_news_type")->selectobj();
        $this->getView()->assign(["data"=>$data]);
    }
    //添加新闻分类
    public function addnewstypeAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $type = $this->posts["type"];
            $file = $this->files['url'];
            $data['type'] = $type;
            if(!empty($file['name'])){
               $data['url'] = $this->uploadone($file,"news");
            }
            $bool = $this->db->action($this->db->insertSql("news_type",$data));
            $this->statusUrl($bool,"添加成功","/index/newstype","添加失败");
        }

    }
    //删除新闻
    public function delnewstypeAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = $this->requests->get('id');
        $bool = $this->db->action($this->db->deleteSql("news_type","id = {$id}"));
        $this->statusUrl($bool,"删除成功","/index/newstype","删除失败");
    }
    //修改新闻
    public function editnewstypeAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $type = $this->posts["type"];
            $file = $this->files['url'];
            $id = $this->posts["id"];
            $data['type'] = $type;
            if(empty($data['game_id']))
            {
                $data['game_id']=0;
            }
            if(!empty($file['name'])){
                $data['url'] = $this->uploadone($file,"news");
            }
            $bool = $this->db->action($this->db->updateSql("news_type",$data,"id = {$id}"));
            $this->statusUrl($bool,"修改成功","/index/newstype","修改成功");
        }else{
            $id = $this->requests->get("id");
            $data = $this->db->field("id,type,url")->table("tab_news_type")->where("id = {$id}")->findobj();
            $this->getView()->assign(["data"=>$data]);
        }
    }

    public function editnewsAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $data = $this->posts;
            $id = $this->posts["id"];
            if(!empty($data['position'])) {
                $data['position'] = implode(",", $data['position']);
            }
            $data['content'] = htmlspecialchars($data["content"]);
            $data['create_time'] = time();
//            $file = $this->files['filename'];
//            if(!empty($file['name'][0])){
//                $data['pic'] = $this->uploadss($file,"news");
//            }else{
//                $this->success("缺少展示资源","/index/newslist");
//            }
            $bool = $this->db->action($this->db->updateSql("news",$data,"id = {$id}"));
            $this->statusUrl($bool,"修改成功","/index/newslist","修改成功");
        }else{
            $newstype = $this->db->field("id,type")->table("tab_news_type")->selectobj();
            $gametype = $this->db->field("id,game_name")->table("tab_game")->select();
            $id = $this->requests->get("id");
            $data = $this->db->field("*")->table("tab_news")->where("id = {$id}")->findobj();
            $game=$this->db->field('game_name')->table("tab_game")->where("id={$data->game_id}")->find();
            $position = explode(",", $data->position);
            $data->one=0;
            $data->two=0;
            $data->three=0;
            foreach ($position as $value)
            {
                if($value==1)
                {
                    $data->one=1;
                }
                elseif($value==2)
                {
                    $data->two=1;
                }
                elseif($value==3)
                {
                    $data->three=1;
                }
            }
            $data->pic=json_decode($data->pic,true);
            $this->getView()->assign(["data"=>$data,"newstype"=>$newstype,"gametype"=>$gametype,"game"=>$game]);
        }
    }
    //删除新闻
    public function delnewsAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = $this->requests->get('id');
        $bool = $this->db->action($this->db->deleteSql("news","id = {$id}"));
        $this->statusUrl($bool,"删除成功","/index/newslist","删除失败");
    }
    //添加新闻
    public function addnewsAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $data = $this->posts;
            if(!empty($data['position'])) {
                $data['position'] = implode(",", $data['position']);
            }
            if(empty($data['game_id']))
            {
                $data['game_id']=0;
            }
            $data['content'] = htmlspecialchars($data["content"]);
            $file = $this->files['filename'];
            if(!empty($file['name'])){
                $data['pic'] = $this->uploadss($file,"news");
                $data['create_time'] = time();
                //echo $this->db->insertSql("news",$data);exit;
                $bool = $this->db->action($this->db->insertSql("news",$data));
                $this->statusUrl($bool,"添加成功","/index/newslist","添加失败");
            }else{
                $this->success("缺少展示资源","/index/newslist");
            }
        }else{
            $newstype = $this->db->field("id,type")->table("tab_news_type")->selectobj();
            $gametype = $this->db->field("id,game_name")->table("tab_game")->select();
            $this->getView()->assign(["newstype"=>$newstype,"gametype"=>$gametype]);
        }
    }
    //新闻
    public function newslistAction(){
        $page = new Page();
        $len = $this->db->zscount("news");
        $showpage = 12;
        $page->init($len,$showpage);
        $strpage = "SELECT id,title,author,click,type,position,game_id,create_time FROM tab_news ORDER BY id DESC {$page->limit}";
        $newData = $this->db->action($strpage);
        $showstr = $page->show();
        $this->getView()->assign(["newData"=>$newData,"showstr"=>$showstr]);
    }
    public function getgiftbagAction(){
        $game_id=get('game_id');
        $gift=$this->db->action("select a.*,b.* from tab_giftbag a left join tab_gift_position b on a.id=b.gift_id  where a.status=1 and b.position 
 LIKE '%6%' and game_id={$game_id} order by a.id desc");
        echo json_encode($gift);exit;

    }
    //修改签到
    public function editcheckinAction(){
        $m = $this->requests->get("m");
        $y = isset($_GET['y'])?$_GET['y']:date("Y"); //当前年
        $days = date("t",mktime(0,0,0,$m,1,$y));//获取当月的天数
        $sql = "SELECT * FROM tab_checkin WHERE create_time BETWEEN '{$y}-{$m}-1' AND '{$y}-{$m}-{$days}'";
        $arrData = $this->db->action($sql);
        $this->getView()->assign(["arrData"=>$arrData]);
    }
    //添加签到
    public function addcheckinAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $file = $this->files['icon'];
            $m = $this->posts['m'];
            if(!empty($file['name'])) {
                $data['icon'] = $this->uploadone($file,"checkin");
            }
                $data['type'] = $this->posts['type'];
                $data['game_id'] = $this->posts['game_id']?$this->posts['game_id']:0;
                $data['gift_id'] = $this->posts['gift_id']? $this->posts['gift_id']:0;
                if($this->posts['type']!='礼包')
                {
                    $data['game_id'] = 0;
                    $data['gift_id'] = 0;
                }
                if(isset($this->posts['text'])) {
                    $data['text'] = $this->posts['text'];
                }
                $data['create_time'] = $this->posts['create_time'];

                //判断是否修改还是添加
                $checkData = $this->db->field("id")->table("tab_checkin")->where(" create_time = '{$data['create_time']}'")->find();
                if(!empty($checkData)){
                    $bool = $this->db->action($this->db->updateSql("checkin",$data,"create_time = '{$data['create_time']}'"));
                }else{
                    $bool = $this->db->action($this->db->insertSql("checkin",$data));
                }
                $this->statusUrl($bool,"操作成功","/index/editcheckin?m=$m","操作成功");
           /* }else{
                $this->success("缺少展示资源","/index/editcheckin?m=$m");
            }*/
        }else{
            $data = $this->requests->get("date");
            $m = $this->requests->get("m");
            $nowtime=time();
            $arr = $this->db->action("SELECT * FROM tab_coupons where end_time >='{$nowtime}' and num>0");
            $game = $this->db->action("SELECT  distinct a.id, a.game_name FROM tab_game a left join tab_giftbag b on a.id=b.game_id left join 
tab_gift_position c on b.id=c.gift_id where a.game_status=1 and c.position  LIKE '%6%'");
            $checkin=$this->db->field("*")->table("tab_checkin")->where(" create_time = '{$data}'")->find();
            $gift=[];
            if($checkin) {
                $gift = $this->db->action("select a.*,b.* from tab_giftbag a left join tab_gift_position b on a.id=b.gift_id  where a.status=1 and b.position 
 LIKE '%6%' and game_id={$checkin['game_id']} order by a.id desc");
            }
            $this->getView()->assign(["data"=>$data,"m"=>$m,"arr"=>$arr,"game"=>$game,"checkin"=>$checkin,"gift"=>$gift]);
        }
    }
    //签到展示
    public function checkinAction(){
        $this->getView()->assign(["xxxx"=>"yyyy"]);
    }
    //选择签到
    public function ischeckinAction(){

    }
    //日志
    public function logAction(){
        $page = new Page();
        $len = $this->db->zscount("user_bp");
        $showpage = 12;
        $page->init($len,$showpage);
        $strpage = "SELECT * FROM tab_user_bp ORDER BY id DESC {$page->limit}";
        $newData = $this->db->action($strpage);
        $showstr = $page->show();
        $this->getView()->assign(["newData"=>$newData,"showstr"=>$showstr]);
    }
    //意见反馈
    public function debugAction(){
        $page = new Page();
        $len = $this->db->zscount("user_log");
        $showpage = 12;
        $page->init($len,$showpage);
        $strpage = "SELECT id,debug,info,create_time FROM tab_user_debug ORDER BY id DESC {$page->limit}";
        $newData = $this->db->action($strpage);
        $showstr = $page->show();
        $this->getView()->assign(["newData"=>$newData,"showstr"=>$showstr]);
    }
    //公共上传接口
    public function uploadAction(){
        Dispatcher::getInstance()->autoRender(false);
        if(!empty($_FILES['file'])){
            $time = time();
            $fileicon = files("file");
            $dir = APP_PATH."/public/upload/".$time;
            if(!file_exists($dir)){
                mkdir($dir,0777,true);
            }
            $pathicon = $dir."/".$fileicon['name'];
            $bool = move_uploaded_file( $fileicon['tmp_name'],$pathicon);
            if($bool){
                echo json_encode(["code"=>0,"msg"=>"ok","data"=>["src"=>"http://".server("SERVER_NAME")."/public/upload/".$time."/".$fileicon['name'],"title"=>$fileicon['name']] ]);
            }else{
                echo json_encode(["msg"=>"error"]);
            }
        }else{
            echo json_encode(['msg'=>"上传失败"]);
        }
    }
    public function usersignlistAction(){

    }
    public function usersignAction(){

    }
    public function continuityAction(){
        $page = new Page();
        $len = $this->db->zscount("checkin_continuity");
        $showpage = 12;
        $page->init($len,$showpage);
        $strpage = "SELECT * FROM tab_checkin_continuity ORDER BY id DESC {$page->limit}";
        $userData = $this->db->action($strpage);
        $showstr = $page->show();
        $this->getView()->assign(["userData"=>$userData,"showstr"=>$showstr]);
    }
    public function editcontinuityAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $data = $this->posts;
            //echo $this->db->insertSql('checkin_continuity',$data);exit;
            $bool = $this->db->action($this->db->updateSql('checkin_continuity',$data,"id={$data['id']}"));
            $this->statusUrl($bool,"修改成功","/index/continuity","修改失败");
        }else{
            $id = $this->requests->get("id");
            $nowtime=time();
            $arr = $this->db->action("SELECT id,amount,userule,start_time,end_time FROM tab_coupons where end_time >='{$nowtime}' and num>0");
            $game = $this->db->action("SELECT  distinct a.id, a.game_name FROM tab_game a left join tab_giftbag b on a.id=b.game_id left join 
tab_gift_position c on b.id=c.gift_id where a.game_status=1 and c.position  LIKE '%6%'");
            $arrData = $this->db->field("*")->table("tab_checkin_continuity")->where("id = {$id}")->find();
            $gift=$this->db->action("select a.*,b.* from tab_giftbag a left join tab_gift_position b on a.id=b.gift_id  where a.status=1 and b.position 
 LIKE '%6%' and game_id={$arrData['game_id']} order by a.id desc");
            if(!empty($arrData)){
                $this->getView()->assign(["arr"=>$arr,"game"=>$game,"arrData"=>$arrData,"gift"=>$gift]);
            }else{
                $this->error("没有数据");
            }
        }

    }
    public function addcontinuityAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $data = $this->posts;
            //echo $this->db->insertSql('checkin_continuity',$data);exit;
            $bool = $this->db->action($this->db->insertSql('checkin_continuity',$data));
            $this->statusUrl($bool,"添加成功","/index/continuity","添加失败");
        }else{
            $nowtime=time();
            $arr = $this->db->action("SELECT id,amount,userule,start_time,end_time FROM tab_coupons where end_time >='{$nowtime}' and num>0");
            $game = $this->db->action("SELECT  distinct a.id, a.game_name FROM tab_game a left join tab_giftbag b on a.id=b.game_id left join 
tab_gift_position c on b.id=c.gift_id where a.game_status=1 and c.position  LIKE '%6%'");
            $this->getView()->assign(["arr"=>$arr,"game"=>$game]);
        }
    }
    public function delcontinuityAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = $this->requests->get('id');
        $bool = $this->db->action($this->db->deleteSql("checkin_continuity","id = {$id}"));
        $this->statusUrl($bool,"删除成功","/index/continuity","删除失败");
    }
    public function membergiftAction(){
        $page = new Page();
        $len = $this->db->zscount("user_level");
        $showpage = 12;
        $page->init($len,$showpage);
        $strpage = "SELECT * FROM tab_user_level ORDER BY id DESC {$page->limit}";
        $userData = $this->db->action($strpage);
        $showstr = $page->show();
        $this->getView()->assign(["userData"=>$userData,"showstr"=>$showstr]);
    }
    public function addmembergiftAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $data = $this->posts;
            $bool = $this->db->action($this->db->insertSql('user_level',$data));
            $this->statusUrl($bool,"添加成功","/index/membergift","添加失败");
        }else{
            $nowtime=time();
            $arr = $this->db->action("SELECT id,amount,userule,start_time,end_time FROM tab_coupons where end_time >='{$nowtime}' and num>0");
            $game = $this->db->action("SELECT  distinct a.id, a.game_name FROM tab_game a left join tab_giftbag b on a.id=b.game_id left join 
tab_gift_position c on b.id=c.gift_id where a.game_status=1 and c.position  LIKE '%6%'");
            $this->getView()->assign(["arr"=>$arr,"game"=>$game]);
        }
    }
    public function editmembergiftAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $data = $this->posts;
            if(empty($data['balance']))
            {
                $data['balance']=0;
            }
            $bool = $this->db->action($this->db->updateSql('user_level',$data,"id={$data['id']}"));
            $this->statusUrl($bool,"修改成功","/index/membergift","修改失败");
        }else{
            $id = $this->requests->get("id");
            $nowtime=time();
            $arr = $this->db->action("SELECT id,amount,userule,start_time,end_time FROM tab_coupons where end_time >='{$nowtime}' and num>0");
            $game = $this->db->action("SELECT  distinct a.id, a.game_name FROM tab_game a left join tab_giftbag b on a.id=b.game_id left join 
tab_gift_position c on b.id=c.gift_id where a.game_status=1 and c.position  LIKE '%6%'");
            $arrData = $this->db->field("*")->table("tab_user_level")->where("id = {$id}")->find();
            $gift=$this->db->action("select a.*,b.* from tab_giftbag a left join tab_gift_position b on a.id=b.gift_id  where a.status=1 and b.position 
 LIKE '%6%' and game_id={$arrData['game_id']} order by a.id desc");
            if(!empty($arrData)){
                $this->getView()->assign(["arr"=>$arr,"game"=>$game,"arrData"=>$arrData,"gift"=>$gift]);
            }else{
                $this->error("没有数据");
            }
        }

    }
    public function delmembcheckAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = $this->requests->get('id');
        $bool = $this->db->action($this->db->deleteSql("user_loca_level","id = {$id}"));
        $this->statusUrl($bool,"删除成功","/index/membcheck","删除失败");
    }
    public function addmembcheckAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $data = $this->posts;
            $bool = $this->db->action($this->db->insertSql('user_loca_level',$data));
            $this->statusUrl($bool,"添加成功","/index/membcheck","添加失败");
        }else{
            $nowtime=time();
            $arr = $this->db->action("SELECT id,amount,userule,start_time,end_time FROM tab_coupons where end_time >='{$nowtime}' and num>0");
            $game = $this->db->action("SELECT  distinct a.id, a.game_name FROM tab_game a left join tab_giftbag b on a.id=b.game_id left join 
tab_gift_position c on b.id=c.gift_id where a.game_status=1 and c.position  LIKE '%6%'");
            $this->getView()->assign(["arr"=>$arr,"game"=>$game]);
        }
    }
    public function delmembergiftAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = $this->requests->get('id');
        $bool = $this->db->action($this->db->deleteSql("user_level","id = {$id}"));
        $this->statusUrl($bool,"删除成功","/index/membergift","删除失败");
    }
    public function membcheckAction(){
        $page = new Page();
        $len = $this->db->zscount("user_loca_level");
        $showpage = 12;
        $page->init($len,$showpage);
        $strpage = "SELECT * FROM tab_user_loca_level ORDER BY id DESC {$page->limit}";
        $userData = $this->db->action($strpage);
        $showstr = $page->show();
        $this->getView()->assign(["userData"=>$userData,"showstr"=>$showstr]);
    }
    public function editmembcheckAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $data = $this->posts;
            $bool = $this->db->action($this->db->updateSql('user_loca_level',$data,"id={$data['id']}"));
            $this->statusUrl($bool,"修改成功","/index/continuity","修改失败");
        }else{
            $id = $this->requests->get("id");
            $nowtime=time();
            $arr = $this->db->action("SELECT id,amount,userule,start_time,end_time FROM tab_coupons where end_time >='{$nowtime}' and num>0");
            $game = $this->db->action("SELECT  distinct a.id, a.game_name FROM tab_game a left join tab_giftbag b on a.id=b.game_id left join 
tab_gift_position c on b.id=c.gift_id where a.game_status=1 and c.position  LIKE '%6%'");
            $arrData = $this->db->field("*")->table("tab_user_loca_level")->where("id = {$id}")->find();
            $gift=$this->db->action("select a.*,b.* from tab_giftbag a left join tab_gift_position b on a.id=b.gift_id  where a.status=1 and b.position 
 LIKE '%6%' and game_id={$arrData['game_id']} order by a.id desc");
            if(!empty($arrData)){
                $this->getView()->assign(["arr"=>$arr,"game"=>$game,"arrData"=>$arrData,"gift"=>$gift]);
            }else{
                $this->error("没有数据");
            }
        }

    }

    public function memberweekAction(){
        $page = new Page();
        $len = $this->db->zscount("user_level");
        $showpage = 12;
        $page->init($len,$showpage);
        $strpage = "SELECT * FROM tab_user_level_week ORDER BY id DESC {$page->limit}";
        $userData = $this->db->action($strpage);
        $showstr = $page->show();
        $this->getView()->assign(["userData"=>$userData,"showstr"=>$showstr]);
    }
    public function addmemberweekAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $data = $this->posts;
            $bool = $this->db->action($this->db->insertSql('user_level_week',$data));
            $this->statusUrl($bool,"添加成功","/index/memberweek","添加失败");
        }else{
            $nowtime=time();
            $arr = $this->db->action("SELECT id,amount,userule,start_time,end_time FROM tab_coupons where end_time >='{$nowtime}' and num>0");
            $game = $this->db->action("SELECT  distinct a.id, a.game_name FROM tab_game a left join tab_giftbag b on a.id=b.game_id left join 
tab_gift_position c on b.id=c.gift_id where a.game_status=1 and c.position  LIKE '%6%'");
            $this->getView()->assign(["arr"=>$arr,"game"=>$game]);
        }
    }
    public function editmemberweekAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $data = $this->posts;
            if(empty($data['balance']))
            {
                $data['balance']=0;
            }
            $bool = $this->db->action($this->db->updateSql('user_level_week',$data,"id={$data['id']}"));
            $this->statusUrl($bool,"修改成功","/index/memberweek","修改失败");
        }else{
            $id = $this->requests->get("id");
            $nowtime=time();
            $arr = $this->db->action("SELECT id,amount,userule,start_time,end_time FROM tab_coupons where end_time >='{$nowtime}' and num>0");
            $game = $this->db->action("SELECT  distinct a.id, a.game_name FROM tab_game a left join tab_giftbag b on a.id=b.game_id left join 
tab_gift_position c on b.id=c.gift_id where a.game_status=1 and c.position  LIKE '%6%'");
            $arrData = $this->db->field("*")->table("tab_user_level_week")->where("id = {$id}")->find();
            $gift=$this->db->action("select a.*,b.* from tab_giftbag a left join tab_gift_position b on a.id=b.gift_id  where a.status=1 and b.position 
 LIKE '%6%' and game_id={$arrData['game_id']} order by a.id desc");
            if(!empty($arrData)){
                $this->getView()->assign(["arr"=>$arr,"game"=>$game,"arrData"=>$arrData,"gift"=>$gift]);
            }else{
                $this->error("没有数据");
            }
        }

    }
    public function delmemberweekAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = $this->requests->get('id');
        $bool = $this->db->action($this->db->deleteSql("user_level_week","id = {$id}"));
        $this->statusUrl($bool,"删除成功","/index/memberweek","删除失败");
    }
    public function membersignAction(){
        $page = new Page();
        $len = $this->db->zscount("user_level");
        $showpage = 12;
        $page->init($len,$showpage);
        $strpage = "SELECT * FROM tab_user_sign_num ORDER BY id DESC {$page->limit}";
        $userData = $this->db->action($strpage);
        $showstr = $page->show();
        $this->getView()->assign(["userData"=>$userData,"showstr"=>$showstr]);
    }
    public function addmembersignAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $data = $this->posts;
            $data['status_num'] = $data['num'];
            $bool = $this->db->action($this->db->insertSql('user_sign_num',$data));
            $this->statusUrl($bool,"添加成功","/index/membersign","添加失败");
        }
    }
    public function editmembersignAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $data = $this->posts;
            $bool = $this->db->action($this->db->updateSql('user_sign_num',$data,"id={$data['id']}"));
            $this->statusUrl($bool,"修改成功","/index/membersign","修改失败");
        }else{
            $id = $this->requests->get("id");
            $arrData = $this->db->field("*")->table("tab_user_sign_num")->where("id = {$id}")->find();
            if(!empty($arrData)){
                $this->getView()->assign(["arrData"=>$arrData]);
            }else{
                $this->error("没有数据");
            }
        }

    }
    public function delmembersignAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = $this->requests->get('id');
        $bool = $this->db->action($this->db->deleteSql("user_sign_num","id = {$id}"));
        $this->statusUrl($bool,"删除成功","/index/membersign","删除失败");
    }
    public function sysnoticeAction(){
        $page = new Page();
        $len = $this->db->zscount("system_notify");
        $showpage = 12;
        $page->init($len,$showpage);
        $strpage = "SELECT * FROM tab_system_notify ORDER BY id DESC {$page->limit}";
        $userData = $this->db->action($strpage);
        $showstr = $page->show();
        $this->getView()->assign(["userData"=>$userData,"showstr"=>$showstr]);
    }

    public function delsysnoticeAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = $this->requests->get('id');
        $bool = $this->db->action($this->db->deleteSql("system_notify","id = {$id}"));
        $this->statusUrl($bool,"删除成功","/index/sysnotice","删除失败");
    }
    //添加推送
    public function addsysnoticeAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
             $file = $this->files['icon'];
            $data['title'] = $this->posts["title"];
            $data['content'] = $this->posts["content"];
            $data['url'] = $this->posts["url"];
            $data['create_time']=time();
            if(!empty($file['name'])){
                $data['icon'] = $this->uploadone($file,"sysnotice");
            }
            $bool = $this->db->action($this->db->insertSql("system_notify",$data));
            if($bool)
            {
                $arr=["title"=>$data['title'],"content"=>$data['content'],"icon"=>WZAPP.$data['icon'],"url"=>$data['url'],"sendtime"=>time()];
                $this->forward("index","message","send",["arr"=>$arr]);
            }
            $this->statusUrl($bool,"添加成功","/index/sysnotice","添加失败");
        }
    }

    //APP UPDATE
    public function settingAction(){
        $arrData = $this->db->field("*")->table("tab_setting")->order("id desc")->select();
        $this->getView()->assign(["arrData"=>$arrData]);
    }

    public function editsettingAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $id = $this->posts["id"];
            $data['version_number'] = $this->posts["version_number"];
            $data['version_name'] = $this->posts["version_name"];
            $data['update_content'] = $this->posts["update_content"];
            $data['is_forced_update'] = $this->posts["is_forced_update"];
            $data['type'] = $this->posts["type"];
            $file = $this->files["update_address"];
            if($file['name']){
                if($file['type'] == "application/vnd.android.package-archive"){
                    $data['update_address'] = $this->uploadone($file,"package");
                    $data['package_size'] = $file['size'];
                    $this->db->action($this->db->updateSql("setting",$data,"id = {$id}"));
                    $this->success("修改apk成功","/index/setting");
                }else{
                    $this->success("请上传APK后缀文件","/index/editsetting");
                }
            }else{
                $bool = $this->db->action($this->db->updateSql("setting",$data,"id = {$id}"));
                $this->statusUrl($bool,"修改成功","/index/setting","修改成功");
            }
        }else{
            $id = $this->requests->get('id');
            $arrData = $this->db->field("*")->table("tab_setting")->where("id = {$id}")->find();
            $this->getView()->assign(["arrData"=>$arrData]);
        }
    }

    //添加管理员
    public function addmtorsAction(){

    }
}

