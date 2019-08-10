<?php
use Yaf\Application;
use Yaf\Dispatcher;
class CardController extends Yaf\Controller_Abstract  {
    public $db;
    public function init(){
        $this->db = new dbModel();
    }
    //Dispatcher::getInstance()->autoRender(false);

    public function indexAction(){
        include APP_PATH."/application/core/Page.php";
        $page = new \app\core\Page();
        $len = $this->db->zscount("card");
        $page->init($len,13);
        $showstr = $page->show();
        $page = $this->db->action("SELECT * FROM zs_card ORDER BY id DESC {$page->limit} ");
        $this->getView()->assign(["page"=>$page,"showstr"=>$showstr]);
    }

    public function delAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = get("id");
        $bool = $this->db->action($this->db->deleteSql("card","id = {$id}"));
        if($bool){
            echo json_encode(['msg'=>"ok"]);
        }else{
            echo json_encode(['msg'=>"no"]);
        }
    }

    public function classifyAction(){
        $arrData = $this->db->field("*")->table("zs_card_type")->selectobj();
        $this->getView()->assign(["arrData"=>$arrData]);
    }

    public function classifydelAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = get("id");
        $bool = $this->db->action($this->db->deleteSql("card_type","id = {$id}"));
        if($bool){
            echo json_encode(['msg'=>"ok"]);
        }else{
            echo json_encode(['msg'=>"no"]);
        }
    }

    public function addclassifyAction(){
        if($this->getRequest()->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $data['title'] = post("title");
            $data['info'] = post("info");
            if (!empty($_FILES['pic']['name'])) {
                $time = time();
                $fileicon = files("file");
                $dir = APP_PATH."/public/card/".$time;
                if(!file_exists($dir)){
                    mkdir($dir,0777,true);
                }
                $pathicon = $dir."/".$fileicon['name'];
                move_uploaded_file( $fileicon['tmp_name'],$pathicon);
                $data['pic'] = "/card/".$time."/".$fileicon['name'];
                $bool = $this->db->action($this->db->insertSql("card_type",$data));
                statusUrl($bool,"添加成功","/card/classify","添加失败");
            }else{
                error("缺少图片资源");
            }
        }else{
            $this->getView()->assign(["xxx"=>"yyyy"]);
        }
    }

    public function classifyeditAction(){
        if($this->getRequest()->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $id = post("id");
            $data['title'] = post("title");
            $data['info'] = post("info");
            if (!empty($_FILES['pic']['name'])) {
                $time = time();
                $fileicon = files("pic");
                $dir = APP_PATH."/public/card/".$time;
                if(!file_exists($dir)){
                    mkdir($dir,0777,true);
                }
                $pathicon = $dir."/".$fileicon['name'];
                move_uploaded_file( $fileicon['tmp_name'],$pathicon);
                $data['pic'] = "/card/".$time."/".$fileicon['name'];
                $bool = $this->db->action($this->db->updateSql("card_type",$data,"id = {$id}"));
                statusUrl($bool,"修改成功","/card/classify","修改失败");
            }else{
                $bool = $this->db->action($this->db->updateSql("card_type",$data,"id = {$id}"));
                statusUrl($bool,"修改成功","/card/classify","修改失败");
            }
        }else{
            $id = get("id");
            $result = $this->db->field("*")->table("zs_card_type")->where(" id = {$id} ")->findobj();
            $this->getView()->assign(["arrData"=>$result]);
        }
    }

    //NATIVE 点赞
    public function clickAction(){
        Dispatcher::getInstance()->autoRender(false);
        $data = file_get_contents("php://input");
        $reqdata = json_decode($data,true);
        if(!empty($reqdata)){
            $u_id = $reqdata['u_id'];
            $c_id = $reqdata['c_id'];
            $cardData = $this->db->field("*")->table("zs_card")->where(" id = {$c_id}")->findobj();
            if(!empty($cardData)){
                $cldata['click'] = (int)$cardData->click + 1;
                $bool = $this->db->action($this->db->updateSql("card",$cldata,"id = {$c_id}"));
                if($bool){
                    $data2['c_id'] = $c_id;
                    $data2['u_id'] = $u_id;
                    $data2['status'] = 1;
                    $this->db->action($this->db->insertSql("card_click",$data2));
                    echo json_encode(["code"=>0,"message"=>"success"]);
                }else{
                    echo json_encode(["code"=>500,"message"=>"系统繁忙，请稍候再试"]);
                }
            }else{
                echo json_encode(["code"=>1100,"message"=>"没有找到此帖"]);
            }
        }else{
            echo json_encode(["code"=>104,"message"=>"请求方式错误"]);
        }
    }

    //NATIVE 评论
    public function feedbackAction(){
        Dispatcher::getInstance()->autoRender(false);
        $data = file_get_contents("php://input");
        $reqdata = json_decode($data,true);
        if(!empty($reqdata)){
            $u_id = $reqdata['u_id'];
            $c_id = $reqdata['c_id'];
            $cardData = $this->db->field("*")->table("zs_card")->where("id = {$c_id}")->find();
            if(!empty($cardData)){
                $data2['u_id'] = $u_id;
                $data2['c_id'] = $c_id;
                $data2['body'] = str_rep(addslashes($reqdata['body']));
                $data2['feedtime'] = $reqdata['ts'];
                $this->db->action("set names utf8mb4");
                $bool = $this->db->action($this->db->insertSql("card_feedback",$data2));
                if($bool){
                    echo json_encode(["code"=>0,"message"=>"success"]);
                }else{
                    echo json_encode(["code"=>500,"message"=>"系统繁忙，请稍候再试"]);
                }
            }else{
                echo json_encode(["code"=>1100,"message"=>"没有找到此帖"]);
            }
        }else{
            echo json_encode(["code"=>104,"message"=>"请求方式错误"]);
        }
    }

    //NATIVE 发帖
    public function addcardAction(){
        Dispatcher::getInstance()->autoRender(false);
        if($this->getRequest()->isPost()){
            $data = json_decode($_POST['json'],true);
            $savedata['u_id'] = $data['u_id'];
            $savedata['t_id'] = $data['t_id'];
            $savedata['content'] = str_rep(addslashes($data['content']));  //str_replace('\\u','\\\\u',$data['content']) ;
            $savedata['click'] = 0;
            $savedata['create_time'] = $data['ts'];
            $savedata['lbs'] = $data['lbs'];
            if (!empty($_FILES['image']['name'])) {
                $time = time();
                $fileicon = files("image");
                $dir = APP_PATH."/public/card/".$time;
                if(!file_exists($dir)){
                    mkdir($dir,0777,true);
                }
                $fileArr = [];
                for($i=0;$i<count($fileicon['name']);$i++){
                    $pathicon = $dir."/".$fileicon['name'][$i];
                    move_uploaded_file( $fileicon['tmp_name'][$i],$pathicon);
                    $fileArr[] = $time."/".$fileicon['name'][$i];
                }
                $savedata['card_pic'] = json_encode($fileArr,320);
            }else{
                $savedata['card_pic'] = json_encode([]);
            }
            $this->db->action("set names utf8mb4");
            $bool = $this->db->action($this->db->insertSql("card",$savedata));
            if($bool){
                echo json_encode(["code"=>0,"message"=>"success"]);
            }else{
                echo json_encode(["code"=>500,"message"=>"系统繁忙，请稍候再试"]);
            }
        }else{
            echo json_encode(["code"=>104,"message"=>"请求方式错误"]);
        }
    }

    //NATIVE 列表（热门/关注/最新）版块
    public function listAction(){
        Dispatcher::getInstance()->autoRender(false);
        $u_id = get("u_id");
        $t_id = get("t_id");
        $this->db->action("set names utf8mb4");
        $cardData = $this->db->field("*")->table("zs_card")->where("t_id = {$t_id}")->select();
        if(!empty($cardData)){
            $currentPage = empty($_GET["page"])?"1":$_GET["page"];
            $showPage = empty($_GET["showpage"])?"9":$_GET["showpage"];
            $start =  ($currentPage-1)*$showPage;
            $onedaytime = 60*60*24;
            if($currentPage == 1){
                $sqlstrone = "SELECT c.id,u.avatar,u.nick_name,u.id as u_id,u.uid,c.lbs,u.competence,c.content,c.card_pic,c.click as click_num,c.create_time FROM zs_card as c LEFT JOIN zs_user as u ON u.id = c.u_id WHERE c.t_id = {$t_id} and u.competence = '官方小宠爱' and unix_timestamp(NOW()) - c.create_time < {$onedaytime} ORDER BY c.id DESC LIMIT 0,1";
                $pageone = $this->db->action($sqlstrone);
                foreach($pageone as $k=>$v){
                    $pageone[$k]['is_click'] = !empty($this->db->field("*")->table("zs_card_click")->where(" u_id = {$u_id} and c_id = {$v['id']} ")->select()) ? 1 : 0;
                    $sqlstr2one = "SELECT f.id as feedback_id,f.body,f.feedtime,f.c_id,f.u_id,u.avatar,u.uid,u.nick_name,u.competence FROM zs_card_feedback as f LEFT JOIN zs_user as u ON u.id = f.u_id WHERE f.c_id = {$v['id']} ORDER BY f.feedtime DESC LIMIT 0,3";
                    $feedbackone = $this->db->action($sqlstr2one);
                    $pageone[$k]['feedbackdata'] =  $feedbackone;
                }
            }else{
                $pageone = [];
            }
            $sqlstr = "SELECT c.id,u.avatar,u.nick_name,u.id as u_id,u.uid,c.lbs,u.competence,c.content,c.card_pic,c.click as click_num,c.create_time FROM zs_card as c LEFT JOIN zs_user as u ON u.id = c.u_id WHERE c.t_id = {$t_id} and u.competence <> '官方小宠爱' ORDER BY c.id DESC LIMIT {$start},{$showPage}";
            $page = $this->db->action($sqlstr);
            foreach($page as $key=>$value){
                $page[$key]['is_click'] = !empty($this->db->field("*")->table("zs_card_click")->where(" u_id = {$u_id} and c_id = {$value['id']} ")->select()) ? 1 : 0;
                $sqlstr2 = "SELECT f.id as feedback_id,f.body,f.feedtime,f.c_id,f.u_id,u.avatar,u.uid,u.nick_name,u.competence FROM zs_card_feedback as f LEFT JOIN zs_user as u ON u.id = f.u_id WHERE f.c_id = {$value['id']} ORDER BY f.feedtime DESC LIMIT 0,3";
                $feedback = $this->db->action($sqlstr2);
                $page[$key]['feedbackdata'] =  $feedback;
            }
            $newAcrdData = array_merge($pageone,$page);
            if(!empty($newAcrdData)){
                echo json_encode(["code"=>0,"message"=>"success","data"=>$newAcrdData]);
            }else{
                echo json_encode(["code"=>0,"message"=>"success","data"=>[]]);
            }
        }else{
            echo json_encode(["code"=>0,"message"=>"success","data"=>[]]);
        }
    }

    //NATIVE 帖子内容
    public function cotentAction(){
        Dispatcher::getInstance()->autoRender(false);
        $u_id = get("u_id");
        $c_id = get("c_id");
        $this->db->action("set names utf8mb4");
        $cardOne = $this->db->field("*")->table("zs_card")->where("id = {$c_id}")->select();
        if(!empty($cardOne)){
            $sqlstr = "SELECT c.id,u.uid,u.avatar,u.competence,u.nick_name,c.lbs,c.content,c.card_pic,c.click as click_num FROM zs_card as c LEFT JOIN zs_user as u ON u.id = c.u_id WHERE c.id = {$c_id}";
            $builder = $this->db->action($sqlstr);
            $builder[0]['is_click'] = !empty($this->db->field("*")->table("zs_card_click")->where("u_id = {$u_id} and c_id = {$c_id}")->select()) ? 1 : 0;
            $sqlstr2 = "SELECT f.id as feedback_id,f.body,f.feedtime,f.c_id,f.u_id,u.avatar,u.uid,u.nick_name,u.competence FROM zs_card_feedback as f LEFT JOIN zs_user as u ON u.id = f.u_id WHERE f.c_id = {$c_id} ORDER BY f.feedtime DESC";
            $feedback = $this->db->action($sqlstr2);
            $builder[0]['feedback_num'] =  count($feedback);
            $builder[0]['feedbackdata'] =  $feedback;
            echo json_encode($builder);
        }else{
            echo json_encode(["code"=>1100,"message"=>"没有找到此帖"]);
        }
    }

    //h5分享页面
    public function shareAction(){
        $this->getView()->assign(["xxx"=>"yyyy"]);
    }

    public function emptyAction()
    {
        // TODO: Implement __call() method.
    }
}