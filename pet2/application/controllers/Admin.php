<?php
use Yaf\Application;
use Yaf\Dispatcher;
class AdminController extends Yaf\Controller_Abstract  {
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
        $filename = APP_PATH."/public/menu.json";
        $jsonstr = file_get_contents($filename);
        $arr = json_decode($jsonstr,true);
        $start=strtotime(date('Y-m-d',time()));
        $end=strtotime(date("Y-m-d 23:59:59"));
        //当日捐款金额
        $alipaydata = $this->db->field("sum(pay_amount) as order_amount ")->table("zs_weal_order")->where("pay_way=0 and pay_status=1 and pay_time>={$start} and pay_time<={$end}")->find();
        $weixindata = $this->db->field("sum(pay_amount) as order_amount")->table("zs_weal_order")->where("pay_way=1 and pay_status=1 and pay_time>={$start} and pay_time<={$end}")->find();
        $people['alipay_count']=$alipaydata['order_amount']?$alipaydata['order_amount']:0;
        $people['weixin_count']=$weixindata['order_amount']?$weixindata['order_amount']:0;

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
            $alipay = $this->db->field("SUM(pay_amount) as order_amount")->table("zs_weal_order")
                ->where("from_unixtime(pay_time,'%m') = {$value['month']} and pay_status = 1 and pay_way=0")->find();
                if($alipay['order_amount']==null)
                {
                    $alipay['order_amount']=0;
                }
            $monthdata[$key]['alipay_amount']=$alipay['order_amount'];
            $weixin = $this->db->field("SUM(pay_amount) as order_amount")->table("zs_weal_order")
                ->where("from_unixtime(pay_time,'%m') = {$value['month']} and pay_status = 1 and pay_way=1")->find();
            if($weixin['order_amount']==null)
            {
                $weixin['order_amount']=0;
            }
            $monthdata[$key]['weixin_amount']=$weixin['order_amount'];
        }

        $pnum = $this->db->zscount("ios_user");

        $this->getView()->assign([
           "username"=>$this->user,
           "arr"=>$arr,
           "people"=>$people,
           "year"=>date("Y"),
           "monthdata"=>json_encode($monthdata),
           "pnum"=>$pnum
        ]);
    }

    public function newslistAction(){
        include APP_PATH."/application/core/Page.php";
        $page = new \app\core\Page();
        $len = $this->db->zscount("news");
        $page->init($len,13);
        $showstr = $page->show();
        $page = $this->db->action("SELECT * FROM zs_news ORDER BY id DESC {$page->limit} ");
        $this->getView()->assign(["page"=>$page,"showstr"=>$showstr]);
    }

    public function newsdelAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = get("id");
        $bool = $this->db->action($this->db->deleteSql("news","id = {$id}"));
        if($bool){
            echo json_encode(['msg'=>"ok"]);
        }else{
            echo json_encode(['msg'=>"no"]);
        }
    }

    public function newssearchAction(){
        $search = get("search");
        $currentPage = empty($_GET["page"])?"1":$_GET["page"];
        $showPage = 10;
        $start =  ($currentPage-1)*$showPage;
        $page = $this->db->action("SELECT * FROM zs_news WHERE title LIKE '%{$search}%' ORDER BY id DESC LIMIT {$start},{$showPage} ");
        $this->getView()->assign(["page"=>$page]);
    }

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

    public function newsaddAction(){
        if($this->getRequest()->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            if (!empty($_FILES['filename']['name'])) {
                $data = $_POST;
                $data['content'] = htmlspecialchars(post("content"));
                $time = time();
                $fileicon = files("filename");
                $dir = APP_PATH."/public/upload/".$time;
                if(!file_exists($dir)){
                    mkdir($dir,0777,true);
                }
                $fileArr = [];
                for($i=0;$i<count($fileicon['name']);$i++){
                    $pathicon = $dir."/".$fileicon['name'][$i];
                    move_uploaded_file( $fileicon['tmp_name'][$i],$pathicon);
                    $fileArr[] = $time."/".$fileicon['name'][$i];
                }
                $data['pic'] = json_encode($fileArr,320);
                $data['create_time'] = time();
                $bool = $this->db->action($this->db->insertSql("news",$data));
                statusUrl($bool,"添加成功","/admin/newslist","添加失败");
            }else{
                success("缺少展示资源","/admin/newslist");
            }
        }else{
            $this->getView()->assign(["xxx"=>"yyy"]);
        }
    }

    public function bannerAction(){
        $currentPage = empty($_GET["page"])?"1":$_GET["page"];
        $showPage = 10;
        $start =  ($currentPage-1)*$showPage;
        $page = $this->db->action("SELECT * FROM zs_banner ORDER BY id DESC LIMIT {$start},{$showPage}");
        $this->getView()->assign(["page"=>$page]);
    }

    public function banneraddAction(){
        if($this->getRequest()->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            if (!empty($_FILES['banner']['name'])) {
                $time = time();
                $dir = APP_PATH."/public/banner/".$time;
                if(!file_exists($dir)){
                    mkdir($dir,0777,true);
                }
                $fileicon = files("banner");
                $pathicon = $dir."/".$fileicon['name'];
                move_uploaded_file( $fileicon['tmp_name'],$pathicon);
                $fileArr = "/banner/".$time."/".$fileicon['name'];
                $data['pic'] = $fileArr;
                $bool = $this->db->action($this->db->insertSql("banner",$data));
                statusUrl($bool,"添加成功","/admin/banner","添加失败");
            }else{
                success("缺少展示资源","/admin/banneradd");
            }
        }else{
            $this->getView()->assign(["xxx"=>"yyy"]);
        }
    }

    public function bannereditAction(){
        if($this->getRequest()->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $data = post();
            if (!empty($_FILES['banner']['name'])) {
                $time = time();
                $dir = APP_PATH."/public/banner/".$time;
                if(!file_exists($dir)){
                    mkdir($dir,0777,true);
                }
                $fileicon = files("banner");
                $pathicon = $dir."/".$fileicon['name'];
                move_uploaded_file( $fileicon['tmp_name'],$pathicon);
                $fileArr = "/banner/".$time."/".$fileicon['name'];
                $data['pic'] = $fileArr;
            }
            $bool = $this->db->action($this->db->updateSql("banner",$data,"id = {$data['id']}"));
            statusUrl($bool,"修改成功","/admin/banner","修改失败");
        }else{
            $id = get("id");
            $userData = $this->db->field("*")->table("zs_banner")->where("id = {$id}")->find();
            $this->getView()->assign(["userData"=>$userData]);
        }
    }

    public function addcommentAction(){
        $arrData = $this->db->field("*")->table("zs_news")->selectobj();
        $this->getView()->assign(["arrData"=>$arrData]);
    }

    public function feedbackAction(){
        Dispatcher::getInstance()->autoRender(false);
        $body = post("body");
        $nid = post("nid");
        $userData = $this->db->field("id")->table("zs_user")->select();
        $data['u_id'] = $userData[rand(0,count($userData)-1)]['id'];
        $data['n_id'] = $nid;
        $data['body'] = $body;
        $data['feedtime'] = time();
        $bool = $this->db->action($this->db->insertSql("feedback",$data));
        if($bool){
            echo json_encode(["msg"=>"成功"]);
        }else{
            echo json_encode(["msg"=>"失败"]);
        }
    }

    public function emptyAction()
    {
        // TODO: Implement __call() method.
    }


}