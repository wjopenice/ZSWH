<?php
use Yaf\Application;
use Yaf\Dispatcher;
class MercController extends Yaf\Controller_Abstract
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

    public function addshopAction(){
        if($this->getRequest()->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $data = $_POST;
            $type = implode(",",$data['type']);
            $data['type'] = $type;
            $res['shop_banner1'] = $this->uploadone($_FILES['shop_banner1'],"shop");
            $res['shop_banner2'] = $this->uploadone($_FILES['shop_banner2'],"shop");
            $res['shop_banner3'] = $this->uploadone($_FILES['shop_banner3'],"shop");
            $data['shop_banner'] = json_encode($res,320);
            $result['pic1'] = $this->uploadone($_FILES['pic1'],"shop");
            $result['pic2'] = $this->uploadone($_FILES['pic2'],"shop");
            $result['pic3'] = $this->uploadone($_FILES['pic3'],"shop");
            $result['pic4'] = $this->uploadone($_FILES['pic4'],"shop");
            $result['pic5'] = $this->uploadone($_FILES['pic5'],"shop");
            $data['pic'] = json_encode($result,320);
            $data['create_time'] = time();
            $data['status'] = 0;
            $bool = $this->db->action($this->db->insertSql("shop",$data));
            statusUrl($bool,"添加成功","/merc/shoplist","添加失败");
        }
    }

    public function editshopAction(){
        if($this->getRequest()->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $data = $_POST;
            $type = implode(",",$data['type']);
            $data['type'] = $type;

            $res['shop_banner1'] = !empty($_FILES['shop_banner1']['name'])? $this->uploadone($_FILES['shop_banner1'],"shop") : $data['b1'];
            $res['shop_banner2'] = !empty($_FILES['shop_banner2']['name'])? $this->uploadone($_FILES['shop_banner2'],"shop") : $data['b2'];
            $res['shop_banner3'] = !empty($_FILES['shop_banner3']['name'])? $this->uploadone($_FILES['shop_banner3'],"shop") : $data['b3'];

            $result['pic1'] = !empty($_FILES['pic1']['name'])? $this->uploadone($_FILES['pic1'],"shop") : $data['p1'];
            $result['pic2'] = !empty($_FILES['pic2']['name'])? $this->uploadone($_FILES['pic2'],"shop") : $data['p2'];
            $result['pic3'] = !empty($_FILES['pic3']['name'])? $this->uploadone($_FILES['pic3'],"shop") : $data['p3'];
            $result['pic4'] = !empty($_FILES['pic4']['name'])? $this->uploadone($_FILES['pic4'],"shop") : $data['p4'];
            $result['pic5'] = !empty($_FILES['pic5']['name'])? $this->uploadone($_FILES['pic5'],"shop") : $data['p5'];

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
            $bool = $this->db->action($this->db->updateSql("shop",$data,"id = {$data['id']}"));
            statusUrl($bool,"修改成功","/merc/shoplist","修改成功");
        }else{
            $id = $_GET['id'];
            $shop =  $this->db->field("*")->table("zs_shop")->where(" id = {$id} ")->find();
            $this->getView()->assign(["shop"=>$shop]);
        }
    }

    public function shoplistAction(){
        include APP_PATH."/application/core/Page.php";
        $page = new \app\core\Page();
        $len = $this->db->zscount("shop");
        $page->init($len,13);
        $showstr = $page->show();
        $page = $this->db->action("SELECT * FROM zs_shop where status NOT IN (3) ORDER BY id DESC {$page->limit} ");
        $this->getView()->assign(["page"=>$page,"showstr"=>$showstr]);
    }

    public function shoplistbotAction(){
        include APP_PATH."/application/core/Page.php";
        $page = new \app\core\Page();
        $len = $this->db->zscount("shop");
        $page->init($len,13);
        $showstr = $page->show();
        $page = $this->db->action("SELECT * FROM zs_shop where status IN (3) ORDER BY id DESC {$page->limit} ");
        $this->getView()->assign(["page"=>$page,"showstr"=>$showstr]);
    }

    public function loginAction(){

    }

    public function orderexpressAction(){
        $field = "order_id,shop_name,user_name,shop_num,shop_price,out_trade_no,user_tel,pay_time,pay_status";
        $where = "pay_status = 1 and express_status = 1";
        include APP_PATH."/application/core/Page.php";
        $page = new \app\core\Page();
        $len = $this->db->zscount("shop_order","*","total",$where);
        $page->init($len,13);
        $showstr = $page->show();
        $page = $this->db->action("SELECT {$field} FROM zs_shop_order WHERE {$where} ORDER BY order_id DESC {$page->limit} ");
        $this->getView()->assign(["page"=>$page,"showstr"=>$showstr]);
    }

    public function botshopAction(){
        $id = $_GET['id'];
        $bool = $this->db->action($this->db->updateSql("shop",['status'=>3],"id = {$id}"));
        if($bool){
            echo json_encode(['msg'=>"ok"]);
        }else{
            echo json_encode(['msg'=>"no"]);
        }
    }
    public function topshopAction(){
        $id = $_GET['id'];
        $bool = $this->db->action($this->db->updateSql("shop",['status'=>0],"id = {$id}"));
        if($bool){
            echo json_encode(['msg'=>"ok"]);
        }else{
            echo json_encode(['msg'=>"no"]);
        }
    }
    //上传单图
    public function uploadone($file,$path){
        $time = time();
        $dir = APP_PATH."/public/".$path."/".$time;
        if(!file_exists($dir)){
            mkdir($dir,0777,true);
        }
        $pathicon = $dir."/".$file['name'];
        move_uploaded_file( $file['tmp_name'],$pathicon);
        $fileArr = "/public/".$path."/".$time."/".$file['name'];
        return $fileArr;
    }
    //多文件上传
    public function uploadss($file,$path){
        $time = time();
        $dir = APP_PATH."/public/".$path."/".$time;
        if(!file_exists($dir)){
            mkdir($dir,0777,true);
        }
        $fileArr = [];
        for($i=0;$i<count($file['name']);$i++){
            $pathicon = $dir."/".$file['name'][$i];
            move_uploaded_file( $file['tmp_name'][$i],$pathicon);
            $fileArr[] = "/public/".$path."/".$time."/".$file['name'][$i];
        }
        $filedata = json_encode($fileArr,320);
        return $filedata;
    }

    public function orderlistAction(){
        include APP_PATH."/application/core/Page.php";
        $page = new \app\core\Page();
        $where = "express_status = 0 and pay_status = 1";
        $len = $this->db->zscount("shop_order","*","total",$where);
        $page->init($len,13);
        $showstr = $page->show();
        $page = $this->db->action("SELECT * FROM zs_shop_order WHERE {$where} ORDER BY order_id DESC {$page->limit} ");
        $this->getView()->assign(["page"=>$page,"showstr"=>$showstr]);
    }

    public function userlistAction(){
        include APP_PATH."/application/core/Page.php";
        $page = new \app\core\Page();
        $len = $this->db->zscount("user_addr");
        $page->init($len,13);
        $showstr = $page->show();
        $page = $this->db->action("SELECT * FROM zs_user_addr ORDER BY addr_id DESC {$page->limit} ");
        $this->getView()->assign(["page"=>$page,"showstr"=>$showstr]);
    }
    public function addbannerAction(){
        if($this->getRequest()->isPost()){
             if(!empty($_FILES['pic']['name'])){
                 $data['pic'] = $this->uploadone($_FILES['pic'],"merc");
                 $data['url'] = $_POST['url'];
                 $bool = $this->db->action($this->db->insertSql("merc_banner",$data));
                 statusUrl($bool,"添加成功","/merc/shopbanner","添加失败");
             }else{
                 error("文件不存在");
             }
        }
    }
    public function orderdetailAction(){
        if($this->getRequest()->isPost()) {
            Dispatcher::getInstance()->autoRender(false);
            $data['express_id'] = null;
            $data['out_trade_no'] = $_POST['out_trade_no'];
            $data['express_company'] =
            $data['express_order'] = $_POST['express_order'];
            $this->db->action($this->db->updateSql("shop_order",['express_status'=>1],"out_trade_no = '{$_POST['out_trade_no']}'"));
            $bool = $this->db->action($this->db->insertSql("shop_express",$data));
            statusUrl($bool,"发货成功","/merc/orderlist","发货失败");
        }else{
            $where = "order_id = {$_GET['id']} and express_status = 0";
            $data = $this->db->field("*")->table("zs_shop_order")->where($where)->find();
            $this->getView()->assign(["data"=>$data]);
        }
    }
    public function returnapplyAction(){
        $field = "order_id,shop_name,user_name,shop_total_price,out_trade_no,user_tel,express_status";
        $where = "pay_status = 1 and express_status = 4";
        include APP_PATH."/application/core/Page.php";
        $page = new \app\core\Page();
        $len = $this->db->zscount("shop_order","*","total",$where);
        $page->init($len,13);
        $showstr = $page->show();
        $page = $this->db->action("SELECT {$field} FROM zs_shop_order WHERE {$where} ORDER BY order_id DESC {$page->limit} ");
        $this->getView()->assign(["page"=>$page,"showstr"=>$showstr]);
    }
    public function addreturnapplyAction(){
        if($this->getRequest()->isPost()) {
            Dispatcher::getInstance()->autoRender(false);
            $out_trade_no = $_POST['out_trade_no'];
            $bool = $this->db->action($this->db->updateSql("shop_order",['express_status'=>4],"out_trade_no = '{$out_trade_no}'"));
            statusUrl($bool,"修改成功","/merc/returnapply","修改成功");
        }else{
            $field = "order_id,shop_name,user_name,shop_total_price,out_trade_no,user_tel,express_status";
            $where = "pay_status = 1 and express_status = 3";
            $data = $this->db->field($field)->table("zs_shop_order")->where($where)->select();
            $this->getView()->assign(["data"=>$data]);
        }
    }

    public function ajaxapplyAction(){
        Dispatcher::getInstance()->autoRender(false);
        $order = $_POST['order'];
        $field = "order_id,shop_name,user_name,shop_total_price,out_trade_no,user_tel,express_status";
        $where = "pay_status = 1 and express_status = 3 and out_trade_no = '{$order}'";
        $data = $this->db->field($field)->table("zs_shop_order")->where($where)->find();
        echo json_encode($data);
    }

    public function shopbannerAction(){
        $data = $this->db->field("*")->table("zs_merc_banner")->select();
        $this->getView()->assign(["data"=>$data]);
    }

}
