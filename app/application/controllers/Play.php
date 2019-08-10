<?php
use Yaf\Dispatcher;
use Helper\Page;
class PlayController extends EventController{
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
//    public function playlistAction(){
//        $data = $this->db->action("SELECT * FROM tab_user_play");
//        $this->getView()->assign(["data"=>$data]);
//    }
//    public function addplayAction(){
//        if($this->requests->isPost()){
//            Dispatcher::getInstance()->autoRender(false);
//            $file = $this->files['icon'];
//            if(!empty($file['name'])){
//                $data = $this->posts;
//                $data['icon'] = $this->uploadone($file,"play");
//                $bool = $this->db->action($this->db->insertSql("user_play",$data));
//                $this->statusUrl($bool,"添加成功","/play/playlist","添加失败");
//            }else{
//                $this->success("缺少展示资源","/play/playlist");
//            }
//        }else{
//            $this->getView()->assign(["xxx"=>"yyy"]);
//        }
//    }

    public function addgrouptypeAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $id = $this->posts['id'];
            $hot=$this->db->action("
            select a.id,game_name,a.icon,a.and_dow_address
            from tab_game a 
            left join tab_game_set b on a.id=b.game_id 
            where a.id = {$id}
            ");
            if(!empty($hot[0]['and_dow_address'])) {
                $hot[0]['and_dow_address'] = ZSWH . substr($hot[0]['and_dow_address'], 1);
            }
            $hot[0]['icon']=$this->get_cover($hot[0]['icon'],'path');
            $data['icon'] = $hot[0]['icon'];
            $data['game_name'] = $hot[0]['game_name'];
            $data['game_id'] = $hot[0]['id'];
            $data['addr'] = $hot[0]['and_dow_address'];
            $bool = $this->db->action($this->db->insertSql("group_type",$data));
            $this->statusUrl($bool,"添加成功","/play/grouptype","添加失败");
        }else{
            $ids='';
            $group_type=$this->db->action("select game_id from tab_group_type");
            foreach ($group_type as $value)
            {
                $ids=$ids.$value['game_id'].",";
            }
            $ids=substr($ids,0,-1);
            $game = $this->db->action("SELECT id,game_name FROM tab_game WHERE game_status=1 and id not in ({$ids})");
            $this->getView()->assign(["game"=>$game]);
        }
    }
    public function grouptypeAction(){
        $data = $this->db->action("SELECT * FROM tab_group_type");
        $this->getView()->assign(["data"=>$data]);
    }

    public function groupbannerAction(){
        $arrData = $this->db->action("SELECT * FROM tab_group_banner");
        $this->getView()->assign(["arrData"=>$arrData]);
    }

    public function addgroupbannerAction(){
        if(!empty($this->requests->isPost())){
            Dispatcher::getInstance()->autoRender(false);
            $file = $this->files['url'];
            if(!empty($file['name'])){
                $data['addr'] = $this->posts['addr'];
                $data['banner'] = $this->uploadone($file,"playbanner");
                $bool = $this->db->action($this->db->insertSql("group_banner",$data));
                $this->statusUrl($bool,"添加成功","/play/groupbanner","添加失败");
            }else{
                $this->success("缺少展示资源","/play/groupbanner");
            }
        }else{
            $this->getView()->assign(["xxx"=>"yyy"]);
        }
    }
    public function editgroupbannerAction(){
        if(!empty($this->requests->isPost())){
            Dispatcher::getInstance()->autoRender(false);
            $file = $this->files['url'];
            $id = $this->posts["id"];
            $data['addr'] = $this->posts['addr'];
            if(!empty($file['name'])){
                $data['banner'] = $this->uploadone($file,"playbanner");
            }
            $bool = $this->db->action($this->db->updateSql("group_banner",$data,"id = {$id}"));
            $this->statusUrl($bool,"修改成功","/play/groupbanner","修改成功");
        }else{
            $id = $this->requests->get("id");
            $data = $this->db->field("*")->table("tab_group_banner")->where("id = {$id}")->findobj();
            $this->getView()->assign(["data"=>$data]);
        }
    }
    public function delgroupbannerAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = $this->requests->get('id');
        $bool = $this->db->action($this->db->deleteSql("group_banner","id = {$id}"));
        $this->statusUrl($bool,"删除成功","/play/groupbanner","删除失败");
    }

    public function groupAction(){
        $page = new Page();
        $len = $this->db->zscount("user_group");
        $showpage = 12;
        $page->init($len,$showpage);
        $strpage = "SELECT * FROM tab_user_group ORDER BY id DESC {$page->limit}";
        $groupData = $this->db->action($strpage);
        $showstr = $page->show();
        $this->getView()->assign(["groupData"=>$groupData,"showstr"=>$showstr]);
    }

    function get_cover($cover_id, $field = null){
        if(empty($cover_id)){
            return false;
        }
        $picture=$this->db->field("*")->table("sys_picture")->where("id = {$cover_id}")->find();
        if($field == 'path'){
            if(!empty($picture['url'])){
                $picture['path'] = $picture['url'];
            }else{
                $picture['path'] = "http://www.zhishengwh.com".$picture['path'];
            }
        }
        return empty($field) ? $picture : $picture[$field];
    }
    //每日分享赚积分
    public function dailyshareAction(){
        $page = new Page();
        $len = $this->db->zscount("share_point");
        $showpage = 12;
        $page->init($len,$showpage);
        $strpage = "SELECT * FROM tab_share_point ORDER BY id DESC {$page->limit}";
        $groupData = $this->db->action($strpage);
        $showstr = $page->show();
        $this->getView()->assign(["arrdata"=>$groupData,"showstr"=>$showstr]);
    }
    public function editdailyshareAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $data['point']=$this->posts['point'];
            $data['is_member']=$this->posts['is_member'];
            if($data['is_member']==1)
            {
                $data['v1']=$this->posts['v1']?$this->posts['v1']:0;
                $data['v2']=$this->posts['v2']?$this->posts['v2']:0;
                $data['v3']=$this->posts['v3']?$this->posts['v3']:0;
                $data['v4']=$this->posts['v4']?$this->posts['v4']:0;
                $data['v5']=$this->posts['v5']?$this->posts['v5']:0;
                $data['v6']=$this->posts['v6']?$this->posts['v6']:0;
                $data['v7']=$this->posts['v7']?$this->posts['v7']:0;
                $data['v8']=$this->posts['v8']?$this->posts['v8']:0;
            }
            else{
                $data['v1']=0;
                $data['v2']=0;
                $data['v3']=0;
                $data['v4']=0;
                $data['v5']=0;
                $data['v6']=0;
                $data['v7']=0;
                $data['v8']=0;
            }
            $data['content'] = $this->posts['content'];
            $data['remark'] = $this->posts['describe'];
            $id = $this->posts["id"];
            $file = $this->files['icon'];
            if(!empty($file['name'])){
                $data['icon'] = $this->uploadone($file,"dailyshare");
            }
            $bool = $this->db->action($this->db->updateSql("share_point",$data,"id = {$id}"));
            $this->statusUrl($bool,"修改成功","/play/dailyshare","修改成功");
        }else{
            $id = $this->requests->get("id");
            $data = $this->db->field("*")->table("tab_share_point")->where("id = {$id}")->findobj();
           $this->getView()->assign(["data"=>$data]);
        }
    }
    public function deldailyshareAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = $this->requests->get('id');
        $bool = $this->db->action($this->db->deleteSql("share_point","id = {$id}"));
        $this->statusUrl($bool,"删除成功","/play/dailyshare","删除失败");
    }
    public function adddailyshareAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $data['point']=$this->posts['point'];
            $data['is_member']=$this->posts['is_member'];
            $file = $this->files['icon'];
            if(!empty($file['name'])){
                $data['icon'] = $this->uploadone($file,"dailyshare");
            }
            if($data['is_member']==1)
            {
                $data['v1']=$this->posts['v1']?$this->posts['v1']:0;
                $data['v2']=$this->posts['v2']?$this->posts['v2']:0;
                $data['v3']=$this->posts['v3']?$this->posts['v3']:0;
                $data['v4']=$this->posts['v4']?$this->posts['v4']:0;
                $data['v5']=$this->posts['v5']?$this->posts['v5']:0;
                $data['v6']=$this->posts['v6']?$this->posts['v6']:0;
                $data['v7']=$this->posts['v7']?$this->posts['v7']:0;
                $data['v8']=$this->posts['v8']?$this->posts['v8']:0;
            }
            else{
                $data['v1']=0;
                $data['v2']=0;
                $data['v3']=0;
                $data['v4']=0;
                $data['v5']=0;
                $data['v6']=0;
                $data['v7']=0;
                $data['v8']=0;
            }
            $data['content'] = $this->posts['content'];
            $data['remark'] = $this->posts['describe'];
            $bool = $this->db->action($this->db->insertSql("share_point",$data));
            $this->statusUrl($bool,"添加成功","/play/dailyshare","添加失败");
        }else{
            $this->getView()->assign(["xxx"=>"yyy"]);
        }
    }
    //下载赚积分
    public function downintegralAction(){
        $page = new Page();
        $len = $this->db->zscount("download_point");
        $showpage = 12;
        $page->init($len,$showpage);
        $strpage = "SELECT * FROM tab_download_point ORDER BY id DESC {$page->limit}";
        $groupData = $this->db->action($strpage);
        $showstr = $page->show();
        $this->getView()->assign(["arrdata"=>$groupData,"showstr"=>$showstr]);
    }
    public function deldownintegralAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = $this->requests->get('id');
        $bool = $this->db->action($this->db->deleteSql("download_point","id = {$id}"));
        $this->statusUrl($bool,"删除成功","/play/downintegral","删除失败");
    }
    public function editdownintegralAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $game_id = $this->posts['game_id'];
            $hot=$this->db->action("
            select a.id,game_name,a.icon,a.and_dow_address
            from tab_game a 
            left join tab_game_set b on a.id=b.game_id 
            where a.id = {$game_id}
            ");
            if(!empty($hot[0]['icon']))
            {
                $hot[0]['icon']=$this->get_cover($hot[0]['icon'],'path');
                $data['icon'] = $hot[0]['icon'];
            }
            if(!empty($hot[0]['and_dow_address'])) {
                $hot[0]['and_dow_address'] = ZSWH . substr($hot[0]['and_dow_address'], 1);
                $data['and_dow_address'] = $hot[0]['and_dow_address'];
            }
            $data['point']=$this->posts['point'];
            $data['is_member']=$this->posts['is_member'];
            if($data['is_member']==1)
            {
                $data['v1']=$this->posts['v1']?$this->posts['v1']:0;
                $data['v2']=$this->posts['v2']?$this->posts['v2']:0;
                $data['v3']=$this->posts['v3']?$this->posts['v3']:0;
                $data['v4']=$this->posts['v4']?$this->posts['v4']:0;
                $data['v5']=$this->posts['v5']?$this->posts['v5']:0;
                $data['v6']=$this->posts['v6']?$this->posts['v6']:0;
                $data['v7']=$this->posts['v7']?$this->posts['v7']:0;
                $data['v8']=$this->posts['v8']?$this->posts['v8']:0;
            }
            else{
                $data['v1']=0;
                $data['v2']=0;
                $data['v3']=0;
                $data['v4']=0;
                $data['v5']=0;
                $data['v6']=0;
                $data['v7']=0;
                $data['v8']=0;
            }
            $data['game_name'] = $hot[0]['game_name'];
            $data['game_id'] = $hot[0]['id'];
            $data['start_time'] = strtotime($this->posts['start_time']."00:00:00");
            $data['end_time'] = strtotime($this->posts['end_time']." 23:59:59");
            $id = $this->posts["id"];
            $bool = $this->db->action($this->db->updateSql("download_point",$data,"id = {$id}"));
            $this->statusUrl($bool,"修改成功","/play/downintegral","修改成功");
        }else{
            $id = $this->requests->get("id");
            $data = $this->db->field("*")->table("tab_download_point")->where("id = {$id}")->findobj();
            $game = $this->db->action("SELECT id,game_name FROM tab_game WHERE game_status=1 ");
            $this->getView()->assign(["game"=>$game,"data"=>$data]);
        }
    }
    public function adddownintegralAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $id = $this->posts['game_id'];
            $hot=$this->db->action("
            select a.id,game_name,a.icon,a.and_dow_address
            from tab_game a 
            left join tab_game_set b on a.id=b.game_id 
            where a.id = {$id}
            ");
            if(!empty($hot[0]['icon']))
            {
                $hot[0]['icon']=$this->get_cover($hot[0]['icon'],'path');
                $data['icon'] = $hot[0]['icon'];
            }
            if(!empty($hot[0]['and_dow_address'])) {
                $hot[0]['and_dow_address'] = ZSWH . substr($hot[0]['and_dow_address'], 1);
                $data['and_dow_address'] = $hot[0]['and_dow_address'];
            }
             $data['point']=$this->posts['point'];
            $data['is_member']=$this->posts['is_member'];
            if($data['is_member']==1)
            {
                $data['v1']=$this->posts['v1']?$this->posts['v1']:0;
                $data['v2']=$this->posts['v2']?$this->posts['v2']:0;
                $data['v3']=$this->posts['v3']?$this->posts['v3']:0;
                $data['v4']=$this->posts['v4']?$this->posts['v4']:0;
                $data['v5']=$this->posts['v5']?$this->posts['v5']:0;
                $data['v6']=$this->posts['v6']?$this->posts['v6']:0;
                $data['v7']=$this->posts['v7']?$this->posts['v7']:0;
                $data['v8']=$this->posts['v8']?$this->posts['v8']:0;
            }
            else{
                $data['v1']=0;
                $data['v2']=0;
                $data['v3']=0;
                $data['v4']=0;
                $data['v5']=0;
                $data['v6']=0;
                $data['v7']=0;
                $data['v8']=0;
            }
            $data['game_name'] = $hot[0]['game_name'];
            $data['game_id'] = $hot[0]['id'];
            $data['start_time'] = strtotime($this->posts['start_time']."00:00:00");
            $data['end_time'] = strtotime($this->posts['end_time']." 23:59:59");
            $bool = $this->db->action($this->db->insertSql("download_point",$data));
            $this->statusUrl($bool,"添加成功","/play/downintegral","添加失败");
        }else{
            $game = $this->db->action("SELECT id,game_name FROM tab_game WHERE game_status=1 ");
            $this->getView()->assign(["game"=>$game]);
        }
    }
    //充值赚积分
    public function rechargeintegralAction(){
        $page = new Page();
        $len = $this->db->zscount("recharge_point");
        $showpage = 12;
        $page->init($len,$showpage);
        $strpage = "SELECT * FROM tab_recharge_point ORDER BY id DESC {$page->limit}";
        $groupData = $this->db->action($strpage);
        $showstr = $page->show();
        $this->getView()->assign(["arrdata"=>$groupData,"showstr"=>$showstr]);
    }
    public function delrechargeintegralAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = $this->requests->get('id');
        $bool = $this->db->action($this->db->deleteSql("recharge_point","id = {$id}"));
        $this->statusUrl($bool,"删除成功","/play/rechargeintegral","删除失败");
    }
    public function editrechargeintegralAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $game_id = $this->posts['game_id'];
            $hot=$this->db->action("
            select a.id,game_name,a.icon,a.and_dow_address
            from tab_game a 
            left join tab_game_set b on a.id=b.game_id 
            where a.id = {$game_id}
            ");
            if(!empty($hot[0]['icon']))
            {
                $hot[0]['icon']=$this->get_cover($hot[0]['icon'],'path');
                $data['icon'] = $hot[0]['icon'];
            }
            $data['amount']=$this->posts['amount'];
            $data['point']=$this->posts['point'];
            $data['is_member']=$this->posts['is_member'];
            if($data['is_member']==1)
            {
                $data['v1']=$this->posts['v1']?$this->posts['v1']:0;
                $data['v2']=$this->posts['v2']?$this->posts['v2']:0;
                $data['v3']=$this->posts['v3']?$this->posts['v3']:0;
                $data['v4']=$this->posts['v4']?$this->posts['v4']:0;
                $data['v5']=$this->posts['v5']?$this->posts['v5']:0;
                $data['v6']=$this->posts['v6']?$this->posts['v6']:0;
                $data['v7']=$this->posts['v7']?$this->posts['v7']:0;
                $data['v8']=$this->posts['v8']?$this->posts['v8']:0;
            }
            else{
                $data['v1']=0;
                $data['v2']=0;
                $data['v3']=0;
                $data['v4']=0;
                $data['v5']=0;
                $data['v6']=0;
                $data['v7']=0;
                $data['v8']=0;
            }
            $data['game_name'] = $hot[0]['game_name'];
            $data['game_id'] = $hot[0]['id'];
            $data['start_time'] = strtotime($this->posts['start_time']."00:00:00");
            $data['end_time'] = strtotime($this->posts['end_time']." 23:59:59");
            $id = $this->posts["id"];
            $bool = $this->db->action($this->db->updateSql("recharge_point",$data,"id = {$id}"));
            $this->statusUrl($bool,"修改成功","/play/rechargeintegral","修改成功");
        }else{
            $id = $this->requests->get("id");
            $data = $this->db->field("*")->table("tab_recharge_point")->where("id = {$id}")->findobj();
            $game = $this->db->action("SELECT id,game_name FROM tab_game WHERE game_status=1 ");
            $this->getView()->assign(["game"=>$game,"data"=>$data]);
        }
    }
    public function addrechargeintegralAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $id = $this->posts['game_id'];
            $hot=$this->db->action("
            select a.id,game_name,a.icon,a.and_dow_address
            from tab_game a 
            left join tab_game_set b on a.id=b.game_id 
            where a.id = {$id}
            ");
            if(!empty($hot[0]['icon']))
            {
                $hot[0]['icon']=$this->get_cover($hot[0]['icon'],'path');
                $data['icon'] = $hot[0]['icon'];
            }
            $data['amount']=$this->posts['amount'];
            $data['point']=$this->posts['point'];
            $data['is_member']=$this->posts['is_member'];
            if($data['is_member']==1)
            {
                $data['v1']=$this->posts['v1']?$this->posts['v1']:0;
                $data['v2']=$this->posts['v2']?$this->posts['v2']:0;
                $data['v3']=$this->posts['v3']?$this->posts['v3']:0;
                $data['v4']=$this->posts['v4']?$this->posts['v4']:0;
                $data['v5']=$this->posts['v5']?$this->posts['v5']:0;
                $data['v6']=$this->posts['v6']?$this->posts['v6']:0;
                $data['v7']=$this->posts['v7']?$this->posts['v7']:0;
                $data['v8']=$this->posts['v8']?$this->posts['v8']:0;
            }
            $data['game_name'] = $hot[0]['game_name'];
            $data['game_id'] = $hot[0]['id'];
            $data['start_time'] = strtotime($this->posts['start_time']."00:00:00");
            $data['end_time'] = strtotime($this->posts['end_time']." 23:59:59");
            $bool = $this->db->action($this->db->insertSql("recharge_point",$data));
            $this->statusUrl($bool,"添加成功","/play/rechargeintegral","添加失败");
        }else{
            $game = $this->db->action("SELECT id,game_name FROM tab_game WHERE game_status=1 ");
            $this->getView()->assign(["game"=>$game]);
        }
    }
    //积分商城代金券
    public function integralmallAction(){
        $arrData = $this->db->action("SELECT * FROM tab_coupons");
        $this->getView()->assign(["arrData"=>$arrData]);
    }
    public function delintegralmallAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = $this->requests->get('id');
        $bool = $this->db->action($this->db->deleteSql("coupons","id = {$id}"));
        $this->statusUrl($bool,"删除成功","/play/integralmall","删除失败");
    }
    public function editintegralmallAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $file = $this->files['pic'];
            $id = $this->posts["id"];
            $data['userule'] = $this->posts['userule'];
            $data['apply_game'] = $this->posts['apply_game'];
            $data['point'] = $this->posts['point'];
            $data['amount'] = $this->posts['amount'];
            $data['credit'] = $this->posts['credit'];
            $data['num'] = $this->posts['num'];
            $data['content'] = $this->posts['content'];
            $data['start_time'] = strtotime($this->posts['start_time']."00:00:00");
            $data['end_time'] = strtotime($this->posts['end_time']." 23:59:59");
             if(!empty($file['name'])){
                 $data['pic'] = $this->uploadone($file,"playcoupon");
             }
            $bool = $this->db->action($this->db->updateSql("coupons",$data,"id = {$id}"));
            $this->statusUrl($bool,"修改成功","/play/integralmall","修改成功");
        }else{
            $id = $this->requests->get("id");
            $data = $this->db->field("*")->table("tab_coupons")->where("id = {$id}")->findobj();
            $this->getView()->assign(["data"=>$data]);
        }
    }
    public function addintegralmallAction(){
        if(!empty($this->requests->isPost())){
            Dispatcher::getInstance()->autoRender(false);
            $file = $this->files['pic'];
            if(!empty($file['name'])){
                $data['userule'] = $this->posts['userule'];
                $data['apply_game'] = $this->posts['apply_game'];
                $data['point'] = $this->posts['point'];
                $data['amount'] = $this->posts['amount'];
                $data['credit'] = $this->posts['credit'];
                $data['num'] = $this->posts['num'];
                $data['content'] = $this->posts['content'];
                $data['start_time'] = strtotime($this->posts['start_time']."00:00:00");
                $data['end_time'] = strtotime($this->posts['end_time']." 23:59:59");
                $data['pic'] = $this->uploadone($file,"playcoupon");
                //echo $this->db->insertSql("coupons",$data);exit;
                $bool = $this->db->action($this->db->insertSql("coupons",$data));
                $this->statusUrl($bool,"添加成功","/play/integralmall","添加失败");
            }else{
                $this->success("缺少展示资源","/play/integralmall");
            }
        }else{
            $this->getView()->assign(["xxx"=>"yyy"]);
        }
    }

    public function typebannerAction(){
        $arrData = $this->db->action("SELECT * FROM tab_game_type_banner");
        $this->getView()->assign(["arrData"=>$arrData]);
    }
    public function addtypebannerAction(){
        if(!empty($this->requests->isPost())){
            Dispatcher::getInstance()->autoRender(false);
            $file = $this->files['url'];
            if(!empty($file['name'])){
                $data['title'] = $this->posts['title'];
                $data['type'] = $this->posts['type'];
                $data['url'] = $this->posts['url'];
                $data['pic'] = $this->uploadone($file,"gamebanner");
                $bool = $this->db->action($this->db->insertSql("game_type_banner",$data));
                $this->statusUrl($bool,"添加成功","/play/typebanner","添加失败");
            }else{
                $this->success("缺少展示资源","/play/typebanner");
            }
        }else{
            $this->getView()->assign(["xxx"=>"yyy"]);
        }
    }
    public function deltypebannerAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = $this->requests->get('id');
        $bool = $this->db->action($this->db->deleteSql("game_type_banner","id = {$id}"));
        $this->statusUrl($bool,"删除成功","/play/typebanner","删除失败");
    }
}
