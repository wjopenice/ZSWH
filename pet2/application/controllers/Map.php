<?php
use Yaf\Application;
use Yaf\Dispatcher;
class MapController extends Yaf\Controller_Abstract  {
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
    //添加地图分类列表
    public function addtypeAction(){
        if($this->getRequest()->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            if (!empty($_FILES['img']['name'])) {
                $time = time();
                $dir = APP_PATH."/public/maptype/".$time;
                if(!file_exists($dir)){
                    mkdir($dir,0777,true);
                }
                $fileicon = files("img");
                $pathicon = $dir."/".$fileicon['name'];
                move_uploaded_file( $fileicon['tmp_name'],$pathicon);
                $fileArr = "/public/maptype/".$time."/".$fileicon['name'];
                $data['map_id'] = null;
                $data['title'] = post("title");
                $data['info'] = post("info");
                $data['img'] = $fileArr;
                $data['color'] = post("color");
                $bool = $this->db->action($this->db->insertSql("map_type",$data));
                statusUrl($bool,"添加成功","/map/type","添加失败");
            }else{
                success("缺少展示资源","/map/type");
            }
        }else{
            $this->getView()->assign(["xxx"=>"yyy"]);
        }
    }

    public function edittypeAction(){
        if($this->getRequest()->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            if (!empty($_FILES['img']['name'])) {
                $time = time();
                $dir = APP_PATH."/public/maptype/".$time;
                if(!file_exists($dir)){
                    mkdir($dir,0777,true);
                }
                $fileicon = files("img");
                $pathicon = $dir."/".$fileicon['name'];
                move_uploaded_file( $fileicon['tmp_name'],$pathicon);
                $fileArr = "/public/maptype/".$time."/".$fileicon['name'];
                $data['map_id'] = post("map_id");
                $data['title'] = post("title");
                $data['info'] = post("info");
                $data['img'] = $fileArr;
                $data['color'] = post("color");
                $bool = $this->db->action($this->db->updateSql("map_type",$data," map_id = {$data['map_id']}"));
                statusUrl($bool,"修改成功","/map/type","修改失败");
            }else{
                success("缺少展示资源","/map/type");
            }
        }else{
            $id = get("id");
            $userData = $this->db->field("*")->table("zs_map_type")->where("map_id = {$id}")->find();
            $this->getView()->assign(["userData"=>$userData]);
        }
    }

    public function deltypeAction(){
        $id = get('id');
        $bool = $this->db->action($this->db->deleteSql("map_type"," map_id = {$id}"));
        statusUrl($bool,"删除成功","/map/type","删除失败");
    }
    //地图分类列表
    public function typeAction(){
        $data = $this->db->action("SELECT * FROM zs_map_type");
        $this->getView()->assign(["data"=>$data]);
    }

    //地图信息列表
    public function listAction(){
        include APP_PATH."/application/core/Page.php";
        $page = new \app\core\Page();
        $len = $this->db->zscount("user_map");
        $page->init($len,13);
        $showstr = $page->show();
        $page = $this->db->action("SELECT * FROM zs_user_map ORDER BY id DESC {$page->limit} ");
        $this->getView()->assign(["page"=>$page,"showstr"=>$showstr]);
    }

    //地图显示范围
    public function rangeAction(){

    }

    //编辑显示范围
    public function addrangeAction(){

    }
}
