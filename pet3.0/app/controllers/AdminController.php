<?php
use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Http\Request;
class AdminController extends Controller
{
    public $user;
	public $session;
	public $pdb;

    public function initialize() {
    	include APP_PATH."/core/Session.php";
		$this->session = new \app\core\Session();
        if($this->session->has("username")){
            $this->user = $this->session->get("username");
			include APP_PATH."/core/Pdb.php";
    		$this->pdb = new \app\core\Pdb();
        }else{
            header("location:/login/alogin");
        }
    }
    function get_week($time = '', $format='Y-m-d'){
        $time = $time != '' ? $time : time();
        $week = date('w', $time);
        $date = [];
        for ($i=1; $i<=7; $i++){
            $date[$i] = date($format ,strtotime( '+' . $i-$week .' days', $time));
        }
        return $date;
    }
	public function indexAction(){
        $start=strtotime(date("Y-m-d 00:00:00"));
        $end=strtotime(date("Y-m-d 23:59:59"));
        $data['iosall']=$this->pdb->zscount("user","*","total","type='ios'");
        $data['androidall']=$this->pdb->zscount("user","*","total","type='android'");
        $data['iosday']=$this->pdb->zscount("user","*","total","type='ios' and register_time>={$start} and register_time<={$end}");
        $data['androidday']=$this->pdb->zscount("user","*","total","type='android' and register_time>={$start} and register_time<={$end}");
        //新增数统计
        $weekdata=$this->get_week();
        $week=[];
        foreach ($weekdata as $key=>$value)
        {

           $ioscount = count($this->pdb->field("*")->table("zsuser")
                ->where("from_unixtime(register_time,'%Y-%m-%d') = '{$value}' and type='ios'")->select());
            $androidcount = count($this->pdb->field("*")->table("zsuser")
                ->where("from_unixtime(register_time,'%Y-%m-%d') = '{$value}' and type='android'")->select());
            $array=array("day"=>$value,"ioscount"=>$ioscount,"androidcount"=>$androidcount);
            $week[]=$array;
        }
        $data['week']=json_encode($week);
        //捐助金额统计
        $monthdata=array(
            array("month"=>'01'),
            array("month"=>'02'),
            array("month"=>'03'),
            array("month"=>'04'),
            array("month"=>'05'),
            array("month"=>'06'),
            array("month"=>'07'),
            array("month"=>'08'),
            array("month"=>'09'),
            array("month"=>'10'),
            array("month"=>'11'),
            array("month"=>'12')
        );
        foreach($monthdata as $key=>$value)
        {
            $alipay = $this->pdb->field("SUM(pay_amount) as order_amount")->table("zswealorder")
                ->where("from_unixtime(pay_time,'%m') = {$value['month']} and pay_status = 1 and pay_way=0")->find();
            if($alipay['order_amount']==null)
            {
                $alipay['order_amount']=0;
            }
            $monthdata[$key]['alipay_amount']=$alipay['order_amount'];
            $weixin = $this->pdb->field("SUM(pay_amount) as order_amount")->table("zswealorder")
                ->where("from_unixtime(pay_time,'%m') = {$value['month']} and pay_status = 1 and pay_way=1")->find();
            if($weixin['order_amount']==null)
            {
                $weixin['order_amount']=0;
            }
            $monthdata[$key]['weixin_amount']=$weixin['order_amount'];
        }
        $data['year']=date("Y");
         $data['month']=json_encode($monthdata);

       // $this->view->setVar("monthdata",json_encode($monthdata));
        $this->view->setVar("arrdata",$data);
    }

	public function userAction(){
        $arrdata = $this->pdb->field("*")->table("zsuser")->order(" id desc")->select();
        foreach ($arrdata as $key=>$value)
        {
            $arrdata[$key]['nick_name']=parseHtmlemoji($value['nick_name']);
        }
		$this->view->setVar("arrdata",$arrdata);
	}

	public function lockuserAction(){
        $arrdata = $this->pdb->field("*")->table("zsuser")->where(" lock_status = '1'")->select();
        foreach ($arrdata as $key=>$value)
        {
            $arrdata[$key]['nick_name']=parseHtmlemoji($value['nick_name']);
        }
        $this->view->setVar("arrdata",$arrdata);
    }

    public function deluserAction(){
        $id = $this->request->getPost("id");
        $arrdata = $this->pdb->field("*")->table("zsuser")->where(" lock_status = '1' and id = {$id}")->find();
        if(!empty($arrdata)){
            $this->pdb->beginTransaction();
            try{
                //用户表
                $bool =$this->pdb->action("delete from zsuser where id={$id} and lock_status = '1'");
                include APP_PATH."/core/Easemob.php";
                $easemob = new \app\core\Easemob();
                 //群主处理
                $ogroups=$this->pdb->action("select * from zsgroup where owner_id='{$arrdata['uid']}'");
                foreach ($ogroups as $key=>$value)
                {
                    $target=[];
                    $result=$easemob->deleteGroup($value['groupid']);
                    $memebers=json_decode($value['members'],true);
                    foreach ($memebers as $v)
                    {
                        $target[]=$v;
                    }
                    $data['from']="C000000000";
                    $data['ext']=["message_from_name"=>"宠爱小助手","message_from_avatar"=>"http://test.pettap.cn/dx.png","message_conversation_name"=>"宠爱小助手","message_conversation_icon"=>"http://test.pettap.cn/dx.png"];
                    $data['msg'] = ["type"=>"txt","msg"=>"{$value['title']}"."已解散"];
                    $data['target_type'] ="users";
                    $data['target'] =$target;
                    $easemob->sendmessage($data);
                }
                $this->pdb->action("delete from zsgroup where owner_id='{$arrdata['uid']}'");
                 //群成员处理
                $mgroups=$this->pdb->action("select * from zsgroup where members  like '%{$arrdata['uid']}%'");
                foreach ($mgroups as $key=>$value)
                {
                    $easemob->deleteGroupMember($value['groupid'],$arrdata['uid']);
                    $memebers=json_decode($value['members'],true);
                    $new =array_merge(array_diff($memebers, array($arrdata['uid'])));
                    $data['members']=json_encode($new);
                    $this->pdb->action($this->pdb->updateSql("zsgroup",$data," g_id = {$value['g_id']}"));

                }
            }
            catch (Exception $e)
            {
                $this->pdb->rollback();
                echo json_encode(['code'=>3,"message"=>"操作失败"]);exit;
            }
            if($bool){
                $this->pdb->commit();
                echo json_encode(['code'=>1,"message"=>"删除完成"]);exit;
            }else{
                echo json_encode(['code'=>0,"message"=>"缺少数据"]);exit;
            }
        }else{
            echo json_encode(['code'=>2,"message"=>"缺少数据"]);exit;
        }
    }

	public function usereditAction(){
        $reqdata = $this->request->getPost();
        $id = $reqdata['id'];
        $user=new Zsuser();
        $result = $user::findFirst("id = $id");
        $result->lock_status = $reqdata['status'];
        $bool =$result->save();
        if($bool){
            echo 0;exit;
        }else{
            echo 1;exit;
        }
    }

	public function userfbAction(){
        $arrData =$this->pdb->action("select * from zsdebug order by id desc");
        foreach ($arrData as $key=>$value)
        {
            $arrData[$key]['debug']=parseHtmlemoji($value['debug']);
        }
        $this->view->setVar("data",$arrData);
	}

    public function useragAction(){
        if($this->request->isPost())
        {
            $zsuserag=new Zsuserag();
            $rs=$zsuserag::findFirst("id = 1");
            $rs->content=htmlspecialchars($this->request->getPost('content'));
            if($rs->save()){
                echo 0;exit;
            }
            else{
                echo 1;exit;
            }
        }
        else{
            $arrData=$this->pdb->field("*")->table('zsuserag')->where("id =1")->find();
            $arrData['content'] = htmlspecialchars_decode($arrData['content']);
            $this->view->setVar("arrdata",$arrData);
        }
    }
    //编辑医院
    public function edithospitalAction(){
        if($this->request->isPost()) {
            $data = $this->request->getPost();
          //  $data['himg'] = $this->uploadss("himg","merc");
           // $data['doctors_cid'] = $this->uploadone("doctors_cid","merc");
           // $data['business_license'] = $this->uploadone("business_license","merc");
            $bool = $this->pdb->action($this->pdb->updateSql("zshospital",$data,"h_id = {$data['h_id']}"));
            statusUrl(true,"修改成功","/admin/hospitallist/hospitallist/1","修改成功");
        }
        $params=$this->dispatcher->getParams();
        $h_id=$params[2];
        $zshospital=new Zshospital();
        $arrdata=$zshospital::findFirst("h_id={$h_id}");
        $arr_banner = json_decode($arrdata->himg,true);
        $arrdata->himg=$arr_banner;
        $this->view->setVar("arrdata",$arrdata);
    }
    //添加医院
    public function addhospitalAction(){
        if($this->request->isPost()){
            if ($this->request->hasFiles() == true) {
                $reqdata = $this->request->getPost();
                $zshospital = new Zshospital();
                $zshospital->h_id = null;
                $zshospital->name = $reqdata['name'];
                $zshospital->info = $reqdata['info'];
                $zshospital->tel = $reqdata['tel'];
                $zshospital->lbs = $reqdata['lbs'];
                $zshospital->longitude = $reqdata['longitude'];
                $zshospital->latitude = $reqdata['latitude'];
                $zshospital->himg = $this->uploadss("himg","merc");
                $zshospital->merc_type = 0;
                $zshospital->scale = $reqdata['scale'];
                $zshospital->pet_r_num = 0;
                $zshospital->receiver_cid = "";
                $zshospital->business_license = $this->uploadone("business_license","merc");
                $zshospital->chain = $reqdata['chain'];
                $zshospital->sphere = $reqdata['sphere'];
                $zshospital->doctors_cid = $this->uploadone("doctors_cid","merc");
                $bool = $zshospital->save();
                statusUrl($bool,"添加成功","/admin/hospitallist/hospitallist/1","添加失败");
            }else{
                error("缺少文件");
            }
        }
    }
    //医院列表
    public function hospitallistAction(){
        $zshospital = new Zshospital();
        $arrData = $zshospital::find("merc_type = 0");
        $currentPage = empty($_GET["page"])?"1":$_GET["page"];
        $paginator = new PaginatorModel(["data"  => $arrData,"limit" => 1000,"page"  => $currentPage]);
        $page = $paginator->paginate();
        $this->view->setVar("page",$page);
    }
    //添加救助站信息
    public function addrescuestationAction(){
        if($this->request->isPost()){
            if ($this->request->hasFiles() == true) {
                $reqdata = $this->request->getPost();
                $zshospital = new Zshospital();
                $zshospital->h_id = null;
                $zshospital->name = $reqdata['name'];
                $zshospital->info = $reqdata['info'];
                $zshospital->tel = $reqdata['tel'];
                $zshospital->lbs = $reqdata['lbs'];
                $zshospital->longitude = $reqdata['longitude'];
                $zshospital->latitude = $reqdata['latitude'];
                $zshospital->himg = $this->uploadss("himg","merc");
                $zshospital->merc_type = 1;
                $zshospital->scale = $reqdata['scale'];
                $zshospital->pet_r_num = $reqdata['pet_r_num'];
                $zshospital->receiver_cid = $reqdata['receiver_cid'];
                $zshospital->business_license = $this->uploadone("business_license","merc");
                $zshospital->chain = "";
                $zshospital->sphere = "";
                $zshospital->doctors_cid = "";
                $bool = $zshospital->save();
                statusUrl($bool,"添加成功","/admin/rescuestation/rescuestation/1","添加失败");
            }else{
                error("缺少文件");
            }
        }
    }
    //救助站
    public function rescuestationAction(){
        $zshospital = new Zshospital();
        $arrData = $zshospital::find("merc_type = 1");
        $currentPage = empty($_GET["page"])?"1":$_GET["page"];
        $paginator = new PaginatorModel(["data"  => $arrData,"limit" => 1000,"page"  => $currentPage]);
        $page = $paginator->paginate();
        $this->view->setVar("page",$page);
    }
    //编辑救助站
    public function editrescuestationAction(){
        if($this->request->isPost()) {
            $data = $this->request->getPost();
           // $data['himg'] = $this->uploadss("himg","merc");
           // $data['business_license'] = $this->uploadone("business_license","merc");
            $bool = $this->pdb->action($this->pdb->updateSql("zshospital",$data,"h_id = {$data['h_id']}"));
            statusUrl(true,"修改成功","/admin/rescuestation/rescuestation/1","修改成功");
        }
        $params=$this->dispatcher->getParams();
        $h_id=$params[2];
        $zshospital=new Zshospital();
        $arrdata=$zshospital::findFirst("h_id={$h_id}");
        $arr_banner = json_decode($arrdata->himg,true);
        $arrdata->himg=$arr_banner;
        $this->view->setVar("arrdata",$arrdata);
    }
    //删除医院或者救助站
    public function delhospitalAction(){
        $reqdata = $this->request->getPost();
        $id = $reqdata['id'];
        $user=new Zshospital();
        $result = $user::findFirst("h_id = $id");
        $bool =$result->delete();
        if($bool){
            echo 0;exit;
        }else{
            echo 1;exit;
        }
    }
    //系统通知列表
    public function systemntionAction(){
        $zshospital = new Zssystemnotify();
        $arrData = $zshospital::find("");
        $currentPage = empty($_GET["page"])?"1":$_GET["page"];
        $paginator = new PaginatorModel(["data"  => $arrData,"limit" => 1000,"page"  => $currentPage]);
        $page = $paginator->paginate();
        $this->view->setVar("page",$page);
    }
    //添加系统通知
    public function addsystemntionAction(){
        $zssetting = new Zssystemnotify();
        if($this->request->isPost()) {
            if ($this->request->hasFiles() == true) {
                $zssetting->icon = $this->uploadone("himg", "systems");
            }
            $reqdata = $this->request->getPost();
            $zssetting->title = $reqdata['title'];
            $zssetting->content = $reqdata['content'];
             $zssetting->url = $reqdata['url'];
            $zssetting->create_time = time();
            $zssetting->status = 0;
            $bool = $zssetting->save();
            if ($bool) {
                $arr = ["title" => $reqdata['title'], "content" => $reqdata['content'], "icon" => "http://test.pettap.cn" . $zssetting->icon, "url" => $reqdata['url'], "sendtime" => time()];
                include BASE_PATH . "/vendor/JPush/Message.php";
                $message = new Message();
                $message->sendall($arr);
            }
            statusUrl($bool, "添加成功", "/admin/systemntion/setting/2", "添加失败");
        }
    }
    //删除系统通知
    public function delsystemntionAction(){
        $reqdata = $this->request->getPost();
        $id = $reqdata['id'];
        $user=new Zssystemnotify();
        $result = $user::findFirst("id = $id");
        $bool =$result->delete();
        if($bool){
            echo 0;exit;
        }else{
            echo 1;exit;
        }
    }
    //单文件上传
    public function uploadone($filename,$path){
        $time = time();
        $dir = BASE_PATH."/public/".$path."/".$time;
        if(!file_exists($dir)){
            mkdir($dir,0777,true);
        }
        $fileArr = "";
        foreach ( $this->request->getUploadedFiles($filename) as $file){
          /*  $fileArr = "/".$path."/".$time."/".$file->getName();
            $file->moveTo($dir."/".$file->getName());*/
            if(strstr($file->getKey(),$filename)){
                $fileArr = "/".$path."/".$time."/".$file->getName();
                $file->moveTo($dir."/".$file->getName());
            }
        }
        return $fileArr;
    }
    public function uploadss($filename,$path){
        $time = time();
        $dir = BASE_PATH."/public/".$path."/".$time;
        if(!file_exists($dir)){
            mkdir($dir,0777,true);
        }
        $fileArr = [];
        foreach ( $this->request->getUploadedFiles($filename) as $file){
            if(strstr($file->getKey(),$filename)){
                $fileArr[] = "/".$path."/".$time."/".$file->getName();
                $file->moveTo($dir."/".$file->getName());
            }
        }
        $datafileArr = json_encode($fileArr,320);
        return $datafileArr;
    }
    public function uploadfile($file,$path){
        $time = time();
        $dir = BASE_PATH."/public/".$path."/".$time;
        if(!file_exists($dir)){
            mkdir($dir,0777,true);
        }
        $pathicon = $dir."/".$file['name'];
        move_uploaded_file( $file['tmp_name'],$pathicon);
        if($file['name']) {
            $fileArr = "/" . $path . "/" . $time . "/" . $file['name'];
        }
        else{
            $fileArr="";
        }
        return $fileArr;
    }
    //删除捐助
    public function deldonationAction(){
        $reqdata = $this->request->getPost();
        $id = $reqdata['id'];
        $bool =$this->pdb->action("delete from zsrescue where r_id={$id}");
        if($bool){
           // $this->pdb->action("delete from zswealorder where weal_id={$id}");
            echo 0;exit;
        }else{
            echo 1;exit;
        }
    }
    //捐助列表
    public function donationlistAction(){
        $zsrescue = new Zsrescue();
        $arrData = $zsrescue::find();
        $currentPage = empty($_GET["page"])?"1":$_GET["page"];
        $paginator = new PaginatorModel(["data"  => $arrData,"limit" => 1000,"page"  => $currentPage]);
        $page = $paginator->paginate();

        $this->view->setVar("page",$page);
    }
    //添加捐助列表
    public function adddonationlistAction(){
        if($this->request->isPost()){
            if($this->request->hasFiles() == true){
                $reqdata = $this->request->getPost();
                $zsrescue = new Zsrescue();
                $zsrescue->r_id = NULL;
                $zsrescue->title = $reqdata['title'];
                $zsrescue->minititle = $reqdata['minititle'];
                $zsrescue->banner = $this->uploadss("banner","rescue");
                $zsrescue->content = htmlspecialchars($reqdata['content']);
                $zsrescue->target_amount = $reqdata['target_amount'];
                $zsrescue->sponsor = $reqdata['sponsor'];
                $zsrescue->receiver = $reqdata['receiver'];
                $zsrescue->mech_cid = $reqdata['mech_cid'];
                $zsrescue->mech_name = $reqdata['mech_name'];
                $zsrescue->number = $reqdata['number'];
                $zsrescue->bankcard= $reqdata['bankcard'];
                $zsrescue->quarantine = $this->uploadone("quarantine","rescue");
                $zsrescue->diagnosis = $this->uploadone("diagnosis","rescue");
                $zsrescue->expire = 1;
                $zsrescue->create_time = time();
                $bool = $zsrescue->save();
                statusUrl($bool,"添加成功","/admin/donationlist/donationlist/1","添加失败");
            }else{
                error("缺少文件");
            }
        }
    }
    //编辑救助站
    public function editdonationlistAction(){
        if($this->request->isPost()) {
            $data = $this->request->getPost();
           // $data['banner'] = $this->uploadss("banner","rescue");
           // $data['quarantine'] = $this->uploadone("quarantine","rescue");
           // $data['diagnosis'] = $this->uploadone("diagnosis","rescue");
            $bool = $this->pdb->action($this->pdb->updateSql("zsrescue",$data,"r_id = {$data['r_id']}"));
            statusUrl(true,"修改成功","/admin/donationlist/donationlist/1","修改成功");
        }
        $params=$this->dispatcher->getParams();
        $h_id=$params[2];
        $zshospital=new Zsrescue();
        $arrdata=$zshospital::findFirst("r_id={$h_id}");
        $arr_banner = json_decode($arrdata->banner,true);
        $arrdata->banner=$arr_banner;
        $this->view->setVar("arrdata",$arrdata);
    }
    //安卓更新
    public function appuploadAction(){
        $zssetting = new Zssetting();
        $arrData = $zssetting::find();
        $this->view->setVar("arrdata",$arrData);
    }
    //编辑安卓更新
    public function addappuploadAction(){
        if($this->request->isPost())
        {
            $id=$this->request->getPost('id');
            if($this->request->hasFiles() == true){
                if($this->request->getUploadedFiles('update_address')) {
                    $time = time();
                    $dir = BASE_PATH . "/public/package/" . $time;
                    if (!file_exists($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    $fileArr = [];
                    foreach ($this->request->getUploadedFiles('update_address') as $file) {
                        $fileArr = "/package/" . $time . "/" . $file->getName();
                        $file->moveTo($dir . "/" . $file->getName());
                    }
                    $data['update_address'] = $fileArr;
                    $data['package_size'] = $file->getSize();
                }
            }
            $data['version_number'] = $this->request->getPost("version_number");
            $data['version_name'] = $this->request->getPost("version_name");
            $data['update_content'] = $this->request->getPost("update_content");
            $data['is_forced_update'] =$this->request->getPost("is_forced_update");
            $data['type'] =$this->request->getPost("type");
           $bool= $this->pdb->action($this->pdb->updateSql("zssetting",$data,"id={$id}"));
            statusUrl($bool,"编辑成功","/admin/appupload/setting/5","编辑成功");
        }
        $params=$this->dispatcher->getParams();
        $id=$params[2];
        $arrData=$this->pdb->field("*")->table('zssetting')->where("id ={$id}")->findobj();
        $this->view->setVar("arrdata",$arrData);

    }
    public function edituploadAction(){
       if($this->request->isPost())
        {
            if($this->request->hasFiles() == true){
                if($this->request->getUploadedFiles('update_address')) {
                    $time = time();
                    $dir = BASE_PATH . "/public/package/" . $time;
                    if (!file_exists($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    $fileArr = [];
                    foreach ($this->request->getUploadedFiles('update_address') as $file) {
                        $fileArr = "/package/" . $time . "/" . $file->getName();
                        $file->moveTo($dir . "/" . $file->getName());
                    }
                    $data['update_address'] = $fileArr;
                    $data['package_size'] = $file->getSize();
                }
            }
            $data['version_number'] = $this->request->getPost("version_number");
            $data['version_name'] = $this->request->getPost("version_name");
            $data['update_content'] = $this->request->getPost("update_content");
            $data['is_forced_update'] =$this->request->getPost("is_forced_update");
            $data['type'] =$this->request->getPost("type");
            $bool= $this->pdb->action($this->pdb->insertSql("zssetting",$data));
            statusUrl($bool,"添加成功","/admin/appupload/setting/5","添加成功");
        }

    }
    //ios更新
    public function iosuploadAction(){
        $zssetting = new Zsiossetting();
        $arrData = $zssetting::find();
        $currentPage = empty($_GET["page"])?"1":$_GET["page"];
        $paginator = new PaginatorModel(["data"  => $arrData,"limit" => 1000,"page"  => $currentPage]);
        $page = $paginator->paginate();
        $this->view->setVar("page",$page);
    }
    //编辑ios更新
    public function addiosuploadAction(){
        if($this->request->isPost())
        {
            $id=$this->request->getPost('id');
            $data['version_number'] = $this->request->getPost("version_number");
            $data['version_name'] = $this->request->getPost("version_name");
            $data['update_content'] = $this->request->getPost("update_content");
            $data['is_forced_update'] =$this->request->getPost("is_forced_update");
            $bool= $this->pdb->action($this->pdb->updateSql("zsiossetting",$data,"id={$id}"));
            statusUrl(true,"编辑成功","/admin/iosupload/setting/6","编辑成功");
        }
        else{
            $arrData=$this->pdb->field("*")->table('zsiossetting')->where("id =1")->findobj();
            $this->view->setVar("arrdata",$arrData);
        }
    }
    //富文本编辑器文件上传
    public function uploadAction(){
        if(!empty($_FILES['file'])){
            $time = time();
            $fileicon = $_FILES["file"];
            $dir = BASE_PATH."/public/upload/".$time;
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
    //新建群聊审核列表
    public function newgroupAction(){
       /* $zsgroup = new Zsgroup();
        $arrData = $zsgroup::find("status = 0");
        $currentPage = empty($_GET["page"])?"1":$_GET["page"];
        $paginator = new PaginatorModel(["data"  => $arrData,"limit" => 1000,"page"  => $currentPage]);
        $page = $paginator->paginate();
        $this->view->setVar("page",$page);*/
        $this->pdb->action("set names utf8mb4");
        $data=$this->pdb->action("select * from zsgroup  where status=0 order by g_id desc");
        foreach ($data as $key=>$value)
        {
            $data[$key]['title'] = htmlspecialchars_decode( $data[$key]['title']);
            $data[$key]['info'] = htmlspecialchars_decode( $data[$key]['info']);
        }
        $this->view->setVar("arrdata",$data);
    }
    //已通过群列表
    public function grouplistAction(){
        $this->pdb->action("set names utf8mb4");
        $data=$this->pdb->action("select * from zsgroup  where status=1 order by g_id desc");
        foreach ($data as $key=>$value)
        {
            $data[$key]['title'] = htmlspecialchars_decode( $data[$key]['title']);
            $data[$key]['info'] = htmlspecialchars_decode( $data[$key]['info']);
        }
        $this->view->setVar("arrdata",$data);
    }
    //拉黑群列表
    public function delgroupAction(){
        $this->pdb->action("set names utf8mb4");
        $gid = $_POST['gid'];
      /*  $result = $zsgroup::findFirst("g_id = {$gid}");
        $result->status = 2;
        $bool = $result->save();*/
        $result = $this->pdb->field("*")->table("zsgroup")->where(" g_id = {$gid}")->find();
        $data['status']=2;
        $bool = $this->pdb->action($this->pdb->updateSql("zsgroup",$data,"g_id={$gid}"));
        if($bool){
            include APP_PATH."/core/Easemob.php";
            $easemob = new \app\core\Easemob();
            $easemob->deleteGroup($result['groupid']);
            $target=[];
            $avatar="http://test.pettap.cn/dx.png";
            $data['from']="C000000000";
            $data['ext']=["message_from_name"=>"宠爱小助手","message_from_avatar"=>$avatar,"message_conversation_name"=>"宠爱小助手","message_conversation_icon"=>$avatar];
            $data['msg'] = ["type"=>"txt","msg"=>"你加入的 [".htmlspecialchars_decode($result['title'])."] 群已被群主解散"];
            $data['target_type'] ="users";
            $target[]=$result['owner_id'];
            $data['target'] =$target;
            $result=$easemob->sendmessage($data);
            echo 1;
        }else{
            echo 0;
        }
    }
    //审核不通过
    public function outgroupAction(){
        $this->pdb->action("set names utf8mb4");
        $zsgroup = new Zsgroup();
        $gid = $_POST['gid'];
       /* $result = $zsgroup::findFirst("g_id = {$gid}");
        $result->status = 3;
        $bool = $result->save();*/
        $result = $this->pdb->field("*")->table("zsgroup")->where(" g_id = {$gid}")->find();
        $data['status']=3;
        $bool = $this->pdb->action($this->pdb->updateSql("zsgroup",$data,"g_id={$gid}"));
        if($bool){
            include APP_PATH."/core/Easemob.php";
            $easemob = new \app\core\Easemob();
            $target=[];
            $avatar="http://test.pettap.cn/dx.png";
            $data['from']="C000000000";
            $data['ext']=["message_from_name"=>"宠爱小助手","message_from_avatar"=>$avatar,"message_conversation_name"=>"宠爱小助手","message_conversation_icon"=>$avatar];
            $data['msg'] = ["type"=>"txt","msg"=>"由于你创建的 [".htmlspecialchars_decode($result['title'])."] 群资料包含违规信息，未通过审核，请重新编辑再提交"];
            $data['target_type'] ="users";
            $target[]=$result['owner_id'];
            $data['target'] =$target;
            $result=$easemob->sendmessage($data);
            echo 1;
        }else{
            echo 0;
        }
    }
    //商品列表
    public function shoplistAction(){
        $zsshop = new Zsshop();
        $arrData = $zsshop::find("status NOT IN (3)");
        $this->view->setVar("arrdata",$arrData);
    }
    //删除商品
    public function delshopAction(){
        $reqdata = $this->request->getPost();
        $id = $reqdata['id'];
        $user=new Zsshop();
        $result = $user::findFirst("id = $id");
        $bool =$result->delete();
        if($bool){
           // $this->pdb->action("delete from zsshoporder where shop_id={$id}");
            echo 0;exit;
        }else{
            echo 1;exit;
        }
    }
    //添加商品
    public function addshoplistAction(){
        if($this->request->isPost()){
            $data = $this->request->getPost();
            if(isset($data['type'])) {
                $type = implode(",", $data['type']);
                $data['type'] = $type;
            }
           $data['create_time'] = time();
            $data['status'] = 0;
            if ($this->request->hasFiles() == true) {
                $shopbanner['shop_banner1']=$this->uploadfile($_FILES['shop_banner1'],'shop');
                $shopbanner['shop_banner2']=$this->uploadfile($_FILES['shop_banner2'],'shop');
                $shopbanner['shop_banner3']=$this->uploadfile($_FILES['shop_banner3'],'shop');
                $data['shop_banner'] = json_encode($shopbanner,320);
                $result['pic1'] = $this->uploadfile($_FILES['pic1'],"shop");
                $result['pic2'] = $this->uploadfile($_FILES['pic2'],"shop");
                $result['pic3'] = $this->uploadfile($_FILES['pic3'],"shop");
                $result['pic4'] = $this->uploadfile($_FILES['pic4'],"shop");
                $result['pic5'] = $this->uploadfile($_FILES['pic5'],"shop");
                $data['pic'] = json_encode($result,320);
            }
           $bool = $this->pdb->action($this->pdb->insertSql("zsshop",$data));
            statusUrl(true,"添加成功","/admin/shoplist/shop/1","添加成功");
        }
    }
    //编辑商品
    public function editshoplistAction(){
    if($this->request->isPost()) {
        $data = $this->request->getPost();
        $res['shop_banner1'] = !empty($_FILES['shop_banner1']['name'])? $this->uploadfile($_FILES['shop_banner1'],"shop") : $data['b1'];
        $res['shop_banner2'] = !empty($_FILES['shop_banner2']['name'])? $this->uploadfile($_FILES['shop_banner2'],"shop") : $data['b2'];
        $res['shop_banner3'] = !empty($_FILES['shop_banner3']['name'])? $this->uploadfile($_FILES['shop_banner3'],"shop") : $data['b3'];

        $result['pic1'] = !empty($_FILES['pic1']['name'])? $this->uploadfile($_FILES['pic1'],"shop") : $data['p1'];
        $result['pic2'] = !empty($_FILES['pic2']['name'])? $this->uploadfile($_FILES['pic2'],"shop") : $data['p2'];
        $result['pic3'] = !empty($_FILES['pic3']['name'])? $this->uploadfile($_FILES['pic3'],"shop") : $data['p3'];
        $result['pic4'] = !empty($_FILES['pic4']['name'])? $this->uploadfile($_FILES['pic4'],"shop") : $data['p4'];
        $result['pic5'] = !empty($_FILES['pic5']['name'])? $this->uploadfile($_FILES['pic5'],"shop") : $data['p5'];

        unset($data['b1']);
        unset($data['p1']);
        unset($data['b2']);
        unset($data['p2']);
        unset($data['b3']);
        unset($data['p3']);
        unset($data['p5']);
        unset($data['p4']);
        $data['shop_banner'] = json_encode($res,320);
        $data['pic'] = json_encode($result,320);
        $bool = $this->pdb->action($this->pdb->updateSql("zsshop",$data,"id = {$data['id']}"));
        statusUrl(true,"修改成功","/admin/shoplist/shop/1","修改成功");
    }
        $params=$this->dispatcher->getParams();
        $shop_id=$params[2];
        $zsshop=new Zsshop();
        $arrdata=$zsshop::findFirst("id={$shop_id}");
        $arr_banner = json_decode($arrdata->shop_banner,true);
        $arrdata->shopbanner=$arr_banner;
        $pic = json_decode($arrdata->pic,true);
        $arrdata->pic=$pic;
     $this->view->setVar("arrdata",$arrdata);
    }
    //已下单列表
    public function orderedlistAction(){
        $zsshoporder=new Zsshoporder();
        $arrData=$zsshoporder::find("pay_status=1 and express_status=0");
        $this->view->setVar("arrdata",$arrData);
    }
    //已发货列表
    public function shippedlistAction(){
        $zsshoporder=new Zsshoporder();
        $arrData=$zsshoporder::find("pay_status=1 and (express_status=1 or express_status=2)");
        $this->view->setVar("arrdata",$arrData);
    }
    //用户地址
    public function useraddressAction(){
        $useraddr=new Zsuseraddr();
        $this->pdb->action("set names utf8mb4");
        $arrData=$this->pdb->action("select a.* ,b.uid from zsuseraddr a left join zsuser b on a.user_id=b.id order by a.addr_id desc");
        $this->view->setVar("arrdata",$arrData);
    }
    //去发货
    public function goshippedAction(){
        if($this->request->isPost()) {
            $data = $this->request->getPost();
            $express=new Zsshopexpress();
            $express->out_trade_no = $data['out_trade_no'];
            $express->express_company =$data['express_company'];
            $express->express_order = $data['express_order'];
            $zsshoporder=new Zsshoporder();
            $result=$zsshoporder::findFirst("out_trade_no='{$data['out_trade_no']}'");
            if($express->save())
            {
                $result->express_status=1;
                $result->save();
                $arr = ["type" =>5, "data" =>"", "pavatar" => "http://test.pettap.cn/dx.png", "uid" =>$result->user_id, "pid" => "","pnickname"=>"","cate"=>3];
               include BASE_PATH . "/vendor/JPush/Message.php";
                $message = new Message();
                $message->send($arr);
                statusUrl(true,"发货成功","/admin/orderedlist/shop/2","发货成功");
            }
        }
        $params=$this->dispatcher->getParams();
        $order_id=$params[2];
        $zsshoporder=new Zsshoporder();
        $arrdata=$zsshoporder::findFirst("order_id={$order_id}")->toArray();
        $this->view->setVar("data",$arrdata);
    }
    //捐款开关
    public function switchAction(){
        $switch=new Zsswitch();
        $arrData=$switch::findFirst("id=1");
        $this->view->setVar("arrdata",$arrData);
    }
    public function editswitchAction(){
        $reqdata = $this->request->getPost();
        $switch=new Zsswitch();
        $result = $switch::findFirst("id = 1");
        $result->expire = $reqdata['status'];
        $bool =$result->save();
        if($bool){
            echo 0;exit;
        }else{
            echo 1;exit;
        }
    }
    //广场帖子列表
    public function squarelistAction(){
        $this->pdb->action("set names utf8mb4");
        $data=$this->pdb->action("select * from zscard order by c_id desc");
        foreach ($data as $key=>$value)
        {
            if(strpos($value['pic'],'.mp4') !== false){
                $type=1;
            }
            else{
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
            $data[$key]['content'] = htmlspecialchars_decode( $data[$key]['content']);
            $data[$key]['click_num'] = $this->pdb->zscount("cardclick","*","total","c_id={$value['c_id']}");;
        }
        $this->view->setVar("arrdata",$data);
    }
    //添加广场
    public function addsquareAction(){
        if($this->request->isPost()){
            $zsuserdata =  $this->pdb->action("select * from zsuser");
            $carddata['c_id'] = NULL;
            $carddata['u_id'] = $zsuserdata[rand(0,count($zsuserdata)-1)]['id'];
            $data = $this->request->getPost();
            $carddata['content'] = $data['content'];
            $carddata['click'] = 0;
            $carddata['create_time'] = time()-rand(11111,99999);
            $carddata['lbs'] = $data['lbs'];
            $carddata['pic'] = $this->uploadss("pic","card");
            $carddata['longitude'] = $data['longitude'];
            $carddata['latitude'] = $data['latitude'];
            $carddata['is_lbs'] = 1;
            $carddata['share_num'] = 0;
            $bool = $this->pdb->action($this->pdb->insertSql("zscard",$carddata));
            statusUrl($bool,"添加成功","/admin/squarelist/group/1","添加失败");
        }
    }
    //删除广场帖子(评论/回复)
    public function delsquareAction(){
        $c_id = $this->request->getPost("c_id");
        if(!empty($c_id)){
            $this->pdb->beginTransaction();
            try{
                $bool =$this->pdb->action("delete from zscard where c_id={$c_id}");
                $this->pdb->action("delete from zscardclick where c_id={$c_id}");
                $this->pdb->action("delete from zscardfeedback where c_id={$c_id}");
                $this->pdb->action("delete from zscardreply where c_id={$c_id}");
            }
            catch (Exception $e)
            {
                $this->pdb->rollback();
                echo json_encode(['code'=>3,"message"=>"操作失败"]);exit;
            }
            if($bool){
                $this->pdb->commit();
                echo json_encode(['code'=>1,"message"=>"删除完成"]);exit;
            }else{
                echo json_encode(['code'=>0,"message"=>"缺少数据"]);exit;
            }
        }else{
            echo json_encode(['code'=>2,"message"=>"缺少数据"]);exit;
        }
    }
    //删除地图帖子
    public function delmapAction(){
        $m_id = $this->request->getPost("id");
        if(!empty($m_id)){
            $this->pdb->beginTransaction();
            try{
                $bool =$this->pdb->action("delete from zsmap where m_id={$m_id}");
                $this->pdb->action("delete from zsmapclick where m_id={$m_id}");
                $this->pdb->action("delete from zsmapfeedback where m_id={$m_id}");
                $this->pdb->action("delete from zsmapreply where m_id={$m_id}");
            }
            catch (Exception $e)
            {
                $this->pdb->rollback();
                echo json_encode(['code'=>3,"message"=>"操作失败"]);exit;
            }
            if($bool){
                $this->pdb->commit();
                echo json_encode(['code'=>1,"message"=>"删除完成"]);exit;
            }else{
                echo json_encode(['code'=>0,"message"=>"缺少数据"]);exit;
            }
        }else{
            echo json_encode(['code'=>2,"message"=>"缺少数据"]);exit;
        }
    }
    //地图帖子列表
    public function mapAction(){
        $this->pdb->action("set names utf8mb4");
        $data=$this->pdb->action("select * from zsmap order by m_id desc");
        foreach ($data as $key=>$value)
        {
            if(strpos($value['pic'],'.mp4') !== false){
                $type=1;
            }
            else{
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
            $data[$key]['content'] = htmlspecialchars_decode( $data[$key]['content']);
            $data[$key]['click_num'] = $this->pdb->zscount("mapclick","*","total","m_id={$value['m_id']}");
        }
        $this->view->setVar("arrdata",$data);
    }
    //添加地图
    public function addmapAction(){
        if($this->request->isPost()){
            $zsuserdata =  $this->pdb->action("select * from zsuser");
            $mapdata['m_id'] = NULL;
            $mapdata['user_id'] = $zsuserdata[rand(0,count($zsuserdata)-1)]['id'];
            $data = $this->request->getPost();
            $mapdata['pic'] = $this->uploadss("pic","map");
            $mapdata['content'] = $data['content'];
            $mapdata['longitude'] = $data['longitude'];
            $mapdata['latitude'] = $data['latitude'];
            $mapdata['lbs'] = $data['lbs'];
            $mapdata['create_time'] = time()-rand(11111,99999);
            $mapdata['status'] = 1;
            $mapdata['type'] = NULL;
            $mapdata['share_num'] = 0;
            $bool = $this->pdb->action($this->pdb->insertSql("zsmap",$mapdata));
            statusUrl($bool,"添加成功","/admin/map/map/1","添加失败");
        }
    }
    //发送文本消息
    public function sendtextAction(){
        $zsuser=new Zsuser();
        $users=$zsuser::find();
        $target=[];
        foreach ($users as $value)
        {
            $target[]=$value->uid;
        }
        if($this->request->isPost()) {
            include APP_PATH."/core/Easemob.php";
            $avatar="http://test.pettap.cn/dx.png";
            $reqdata = $this->request->getPost();
            $data['from']="C000000000";
            $data['ext']=["message_from_name"=>$reqdata['name'],"message_from_avatar"=>$avatar,"message_conversation_name"=>$reqdata['name'],"message_conversation_icon"=>$avatar];
            $data['msg'] = ["type"=>"txt","msg"=>$reqdata['content']];
            $easemob = new \app\core\Easemob();
            $data['target_type'] ="users";
            $data['target'] =$target;
            $result=$easemob->sendmessage($data);
            statusUrl(true,"发送成功","/admin/sendtext/ai/1","发送失败");
        }
    }
    //发送图片消息
    public function sendimageAction(){
        $zsuser=new Zsuser();
        $users=$zsuser::find();
        $target=[];
        foreach ($users as $value)
        {
            $target[]=$value->uid;
        }
        if($this->request->isPost()) {
            include APP_PATH."/core/Easemob.php";
            $easemob = new \app\core\Easemob();
            $file = $this->uploadone("image","chats");
            $filepath="http://test.pettap.cn".$file;
            $reqdata = $this->request->getPost();
            $from="C000000000";
            $target_type="users";
            $target=$target;
            $filename=basename($file);
            $ext['message_from_name']=$reqdata['name'];
            $ext['message_from_avatar']="http://test.pettap.cn/dx.png";
            $ext['message_conversation_name']=$reqdata['name'];
            $ext['message_conversation_icon']="http://test.pettap.cn/dx.png";
            $result=$easemob->sendImage($filepath,$from,$target_type,$target,$filename,$ext);
            statusUrl(true,"发送成功","/admin/sendimage/ai/2","发送失败");
        }
    }

    //捐款流水单
    public function commonwealAction(){
        $data=$this->pdb->action("select a.*,b.uid,c.title from zswealorder a left join zsuser b on a.user_id=b.id left join zsrescue c on a.weal_id=c.r_id 
where a.pay_status=1 order by a.id desc");
        $this->view->setVar("arrdata",$data);
    }

    //订单流水
    public function shopflowAction(){

    }
}
