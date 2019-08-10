<?php
use Yaf\Application;
use Yaf\Dispatcher;
class ShopController extends Yaf\Controller_Abstract
{
    public $db;
    public $user;
    public function init()
    {
        $this->db = new dbModel();
        if (!empty($_SESSION['username'])) {
            $this->user = $_SESSION['username'];
        } else {
            success("请先登陆!", "/login/login");
            exit;
        }
    }

    public function bannerAction(){
        $data = $this->db->action("SELECT * FROM zs_shop_banner");
        $this->getView()->assign(["data"=>$data]);
    }

    public function listAction(){

    }

    public function shoplistAction(){
        include APP_PATH."/application/core/Page.php";
        $page = new \app\core\Page();
        $len = $this->db->zscount("shop");
        $page->init($len,13);
        $showstr = $page->show();
        $page = $this->db->action("SELECT * FROM zs_shop where status IN (0) ORDER BY id DESC {$page->limit} ");
        $this->getView()->assign(["page"=>$page,"showstr"=>$showstr]);
    }

    public function checkshopAction(){

    }

    public function editbannerAction(){
        if($this->getRequest()->isPost()) {
            Dispatcher::getInstance()->autoRender(false);
            $data['id'] = post("id");
            $data['name'] = post("name");
            $data['size'] = post("size");
            $data['addr'] = post("addr");
            if (!empty($_FILES['pic']['name'])) {
                $time = time();
                $dir = APP_PATH."/public/shop/".$time;
                if(!file_exists($dir)){
                    mkdir($dir,0777,true);
                }
                $fileicon = files("pic");
                $pathicon = $dir."/".$fileicon['name'];
                move_uploaded_file( $fileicon['tmp_name'],$pathicon);
                $fileArr = "/public/shop/".$time."/".$fileicon['name'];
                $data['pic'] = $fileArr;
            }
            $bool = $this->db->action($this->db->updateSql("shop_banner",$data," id = {$data['id']}"));
            statusUrl($bool,"修改成功","/shop/banner","修改失败");
        }else{
            $id = get('id');
            $userData = $this->db->field("*")->table("zs_shop_banner")->where("id = {$id}")->find();
            $this->getView()->assign(["userData"=>$userData]);
        }
    }
}
