<?php
use Yaf\Application;
use Yaf\Dispatcher;
class SetController extends Yaf\Controller_Abstract  {
    public $db;
    public $user;
    public function init(){
        $this->db = new dbModel();
        if(!empty($_SESSION['username'])){
            $this->user = $_SESSION['username'];
        }else{
            success("请先登陆!","/login/login");
            exit;
        }
    }

    public function indexAction(){
        include APP_PATH."/application/core/Page.php";
        $page = new \app\core\Page();
        $len = $this->db->zscount("filter");
        $page->init($len,13);
        $showstr = $page->show();
        $page = $this->db->action("SELECT * FROM zs_filter ORDER BY id DESC {$page->limit} ");
        $this->getView()->assign(["page"=>$page,"showstr"=>$showstr]);
    }

    public function addindexAction(){
        if($this->getRequest()->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $word = post("word");
            $filter['word'] = $word;
            $bool = $this->db->action($this->db->insertSql("filter",$filter));
            statusUrl($bool,"添加成功","/set/index","添加失败");
        }else{
            $this->getView()->assign(["xxx"=>"yyyy"]);
        }
    }

    public function delindexAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = get("id");
        $bool = $this->db->action($this->db->deleteSql("filter","id = {$id}"));
        if($bool){
            echo json_encode(['msg'=>"ok"]);
        }else{
            echo json_encode(['msg'=>"no"]);
        }
    }

    public function searchAction(){
        include APP_PATH."/application/core/Page.php";
        $page = new \app\core\Page();
        $search = get("search");
        $showPage = 10;
        $len = $this->db->zscount("filter","*","total"," word LIKE '%{$search}%' ");
        $page->init($len,$showPage);
        $showstr = $page->show();
        $result = $this->db->action("SELECT id,word FROM zs_filter WHERE word LIKE '%{$search}%' {$page->limit}");
        $this->getView()->assign(["result"=>$result,"showstr"=>$showstr]);
    }

    //系统设置
    public function appuploadAction(){
        $arrData = $this->db->field("id,version_number,version_name,update_content,update_address,package_size,is_forced_update")->table("zs_setting")->order("id desc")->select();
        $this->getView()->assign(["arrData"=>$arrData]);
    }
    //添加更新APK包
    public function addappuploadAction(){
        if($this->getRequest()->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $data['version_number'] = post("version_number");
            $data['version_name'] = post("version_name");
            $data['update_content'] = post("update_content");
            if($_FILES['update_address']['name']){
                $file = files("update_address");
                if($file['type'] == "application/vnd.android.package-archive"){
                    $dir = APP_PATH."/package";
                    if(!file_exists($dir)){
                        mkdir($dir,0777,true);
                    }
                    $pathicon = $dir."/".$file['name'];
                    move_uploaded_file( $file['tmp_name'],$pathicon);
                    $data['update_address'] = "http://".server("SERVER_NAME")."/package/".$file['name'];
                    $data['package_size'] = $file['size'];
                    $bool = $this->db->action($this->db->insertSql("setting",$data));
                    statusUrl($bool,"添加成功","/set/appupload","添加失败");
                }else{
                    success("请上传APK后缀文件","/set/addappupload");
                }
            }else{
                success("缺少展示资源","/admin/banneradd");
            }
        }else{
            $this->getView()->assign(["xxx"=>"yyyy"]);
        }
    }

    public function appedituploadAction(){
        if($this->getRequest()->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $id = post("id");
            $data['version_number'] = post("version_number");
            $data['version_name'] = post("version_name");
            $data['update_content'] = post("update_content");
            $data['is_forced_update'] = post("is_forced_update");
            if($_FILES['update_address']['name']){
                $file = files("update_address");
                if($file['type'] == "application/vnd.android.package-archive"){
                    $dir = APP_PATH."/package";
                    if(!file_exists($dir)){
                        mkdir($dir,0777,true);
                    }
                    $pathicon = $dir."/".$file['name'];
                    move_uploaded_file( $file['tmp_name'],$pathicon);
                    $data['update_address'] = "/package/".$file['name'];
                    $data['package_size'] = $file['size'];
                    $this->db->action($this->db->updateSql("setting",$data,"id = {$id}"));
                    success("修改apk成功","/set/appupload");
                }else{
                    success("请上传APK后缀文件","/set/addappupload");
                }
            }else{
                $bool = $this->db->action($this->db->updateSql("setting",$data,"id = {$id}"));
                statusUrl($bool,"修改成功","/set/appupload","修改成功");
            }
        }else{
            $id = get("id");
            $arrData = $this->db->field("*")->table("zs_setting")->where("id = {$id}")->find();
            $this->getView()->assign(["arrData"=>$arrData]);
        }
    }

    public function emptyAction(){
        // TODO: Implement __call() method.
    }

    public function sysnoticeAction(){
        include APP_PATH."/application/core/Page.php";
        $page = new \app\core\Page();
        $len = $this->db->zscount("system_notify");
        $page->init($len,13);
        $showstr = $page->show();
        $page = $this->db->action("SELECT * FROM zs_system_notify ORDER BY id DESC {$page->limit} ");
        $this->getView()->assign(["userData"=>$page,"showstr"=>$showstr]);
    }

    public function delsysnoticeAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = $_GET['id'];
        $bool = $this->db->action($this->db->deleteSql("system_notify","id = {$id}"));
        statusUrl($bool,"删除成功","/set/sysnotice","删除失败");
    }
    //添加推送
    public function addsysnoticeAction(){
        if($this->getRequest()->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $data['title'] = $_POST["title"];
            $data['content'] = $_POST["content"];
            $data['url'] = $_POST["url"];
            $data['create_time']=time();
            if($_FILES['icon']) {
                if (!empty($_FILES['icon']['name'])) {
                    $time = time();
                    $dir = APP_PATH . "/public/sysnotice/" . $time;
                    if (!file_exists($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    $fileicon = files("icon");
                    $pathicon = $dir . "/" . $fileicon['name'];
                    move_uploaded_file($fileicon['tmp_name'], $pathicon);
                    $fileArr = "/public/sysnotice/" . $time . "/" . $fileicon['name'];
                    $data['icon'] = $fileArr;
                }
            }
            $bool = $this->db->action($this->db->insertSql("system_notify",$data));
            if($bool)
            {
                $arr=["title"=>$data['title'],"content"=>$data['content'],"icon"=>"http://www.pettap.cn".$data['icon'],"url"=>$data['url'],"sendtime"=>time()];
                $this->forward("index","message","sendall",["arr"=>$arr]);
            }
            statusUrl($bool,"添加成功","/set/sysnotice","添加失败");
        }

    }

    public function iosuploadAction(){
        $data = $this->db->action("SELECT * FROM zs_ios_setting");
        $this->getView()->assign(["data"=>$data]);
    }

    public function iosedituploadAction(){
        if($this->getRequest()->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $id = post("id");
            $data['version_number'] = post("version_number");
            $data['version_name'] = post("version_name");
            $data['update_content'] = post("update_content");
            $data['is_forced_update'] = post("is_forced_update");
            $bool = $this->db->action($this->db->updateSql("ios_setting",$data,"id = {$id}"));
            statusUrl($bool,"修改成功","/set/iosupload","修改成功");
        }else{
            $id = get("id");
            $arrData = $this->db->field("*")->table("zs_ios_setting")->where("id = {$id}")->find();
            $this->getView()->assign(["arrData"=>$arrData]);
        }
    }

    public function debugAction(){
        include APP_PATH."/application/core/Page.php";
        $page = new \app\core\Page();
        $len = $this->db->zscount("user_debug");
        $page->init($len,13);
        $showstr = $page->show();
        $page = $this->db->action("SELECT * FROM zs_user_debug ORDER BY id DESC {$page->limit} ");
        $this->getView()->assign(["page"=>$page,"showstr"=>$showstr]);
    }

    public function debugeditAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = get("id");
        $bool = $this->db->action($this->db->deleteSql("user_debug"," id = {$id} "));
        statusUrl($bool,"修复成功","/set/debug","修复成功");
    }

}
