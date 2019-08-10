<?php
use Yaf\Application;
use Yaf\Dispatcher;
class NativeController extends Yaf\Controller_Abstract  {
    public $db;
    public function init(){
        $this->db = new dbModel();
    }
    //Dispatcher::getInstance()->autoRender(false);

    //NATIVE首页文章
    public function newsAction(){
        Dispatcher::getInstance()->autoRender(false);
        if(!empty($_GET["u_id"])){
            $currentPage = empty($_GET["page"])?"1":$_GET["page"];
            $showPage = empty($_GET["showpage"])?"9":$_GET["showpage"];
            $start =  ($currentPage-1)*$showPage;
            $result = $this->db->action("SELECT * FROM zs_news ORDER BY id DESC LIMIT {$start},{$showPage} ");
            foreach ($result as $key=>$value){
                $result[$key]['content'] = mb_substr(strip_tags(htmlspecialchars_decode($value['content'])),0,60,"utf-8");
                $arrData = $this->db->field("*")->table("zs_collection")->where("u_id = {$_GET["u_id"]} and n_id = {$result[$key]['id']}")->find();
                $result[$key]['is_collection'] = !empty($arrData)?'1':'0';
                $sqlstr = "SELECT f.id as feed_id,f.body,f.feedtime,f.n_id,f.u_id,u.avatar,u.uid,u.nick_name,u.competence FROM zs_feedback as f LEFT JOIN zs_user as u ON u.id = f.u_id WHERE f.n_id = {$value['id']} ORDER BY f.feedtime DESC LIMIT 0,3";
                $feedback = $this->db->action($sqlstr);
                $result[$key]['feedbackdata'] = $feedback;
            }
            echo json_encode($result);
        }else{
            $currentPage = empty($_GET["page"])?"1":$_GET["page"];
            $showPage = empty($_GET["showpage"])?"9":$_GET["showpage"];
            $start =  ($currentPage-1)*$showPage;
            $result = $this->db->action("SELECT * FROM zs_news ORDER BY id DESC LIMIT {$start},{$showPage} ");
            foreach ($result as $key=>$value){
                $result[$key]['content'] = mb_substr(strip_tags(htmlspecialchars_decode($value['content'])),0,60,"utf-8");
                $result[$key]['is_collection'] = '0';
                $sqlstr = "SELECT f.id as feed_id,f.body,f.feedtime,f.n_id,f.u_id,u.avatar,u.uid,u.nick_name,u.competence FROM zs_feedback as f LEFT JOIN zs_user as u ON u.id = f.u_id WHERE f.n_id = {$value['id']} ORDER BY f.feedtime DESC LIMIT 0,3";
                $feedback = $this->db->action($sqlstr);
                $result[$key]['feedbackdata'] = $feedback;
            }
            echo json_encode($result);
        }
    }

    //NATIVE首页banner
    public function bannerAction(){
        Dispatcher::getInstance()->autoRender(false);
        $arrData = $this->db->field("*")->table("zs_banner")->selectobj();
        echo json_encode($arrData);
    }

    //NATIVE 文章点赞
    public function cardclickAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = get("id");
        if(!empty($id)){
            $card = $this->db->field("*")->table("zs_news")->where("id = {$id}")->findobj();
            $click =(int)$card->click + 1;
            //$data['content'] = "";
            $data['click'] = $click;
            $bool = $this->db->action($this->db->updateSql("news",$data,"id = {$id}"));
            if($bool){
                echo json_encode(["code"=>0,"message"=>"ok"]);
            }else{
                echo json_encode(["code"=>500,"message"=>"系统繁忙，请稍候再试"]);
            }
        }else{
            echo json_encode(["code"=>1201,"message"=>"缺少文章ID"]);
        }
    }

    //NATIVE 文章评论
    public function cardfeedAction(){
        Dispatcher::getInstance()->autoRender(false);
        $data = file_get_contents("php://input");
        $reqdata = json_decode($data,true);
        if(!empty($reqdata)){
            $news['u_id'] = $reqdata['u_id'];
            $news['n_id'] = $reqdata['n_id'];
            $news['body']= str_rep(addslashes($reqdata['body']));
            $news['feedtime'] = $reqdata['ts'];
            $this->db->action("set names utf8mb4");
            $bool = $this->db->action($this->db->insertSql("feedback",$news));
            if($bool){
                echo json_encode(["code"=>0,"message"=>"ok"]);
            }else{
                echo json_encode(["code"=>500,"message"=>"系统繁忙，请稍候再试"]);
            }
        }else{
            echo json_encode(["code"=>104,"message"=>"请求方式错误"]);
        }
    }

    //NATIVE 收藏
    public function collectionAction(){
        Dispatcher::getInstance()->autoRender(false);
        $data = file_get_contents("php://input");
        $reqdata = json_decode($data,true);
        if(!empty($reqdata)){
            $news['u_id'] = $reqdata['u_id'];
            $news['n_id'] = $reqdata['n_id'];
            $news['n_title']= $reqdata['n_title'];
            $arrData = $this->db->field("*")->table("zs_collection")->where(" u_id = {$news['u_id']} and n_id = {$news['n_id']} ")->find();
            if(!empty($arrData)){
                //取消收藏操作
                $bool = $this->db->action($this->db->deleteSql("collection"," u_id = {$news['u_id']} and n_id = {$news['n_id']} "));
                if($bool){
                    echo json_encode(["code"=>0,"message"=>"ok"]);
                }else{
                    echo json_encode(["code"=>500,"message"=>"系统繁忙，请稍候再试"]);
                }
            }else{
                //加入收藏操作
                $data2['u_id'] =  $reqdata['u_id'];
                $data2['n_id'] =  $reqdata['n_id'];
                $data2['n_title'] =  $reqdata['n_title'];
                $bool = $this->db->action( $this->db->insertSql("collection",$data2) );
                if($bool){
                    echo json_encode(["code"=>0,"message"=>"ok"]);
                }else{
                    echo json_encode(["code"=>500,"message"=>"系统繁忙，请稍候再试"]);
                }
            }
        }else{
            echo json_encode(["code"=>104,"message"=>"请求方式错误"]);
        }
    }

    //NATIVE文章内容页
    public function cardcontent2Action(){
        Dispatcher::getInstance()->autoRender(false);
        $id = get("id");
        $this->db->action("set names utf8mb4");
        $card = $this->db->field("*")->table("zs_news")->where(" id = {$id} ")->find();
        if(!empty($card)){
            $card['content'] = "";
            $card['collection_num'] = count($this->db->field("*")->table("zs_collection")->where(" n_id = {$id} ")->select());
            $card['cardfeed_num'] = count($this->db->field("*")->table("zs_feedback")->where(" n_id = {$id} ")->select());
            $sqlstr = "SELECT f.id as feed_id,f.body,f.feedtime,f.n_id,f.u_id,u.avatar,u.uid,u.nick_name,u.competence FROM zs_feedback as f LEFT JOIN zs_user as u ON u.id = f.u_id WHERE f.n_id = {$id} ORDER BY f.feedtime DESC";
            $feedback = $this->db->action($sqlstr);
            $card['feedbackdata'] = $feedback;
            echo json_encode($card);
        }else{
            echo json_encode(['code'=>1012,'message'=>"没有此文章"]);exit;
        }
    }

    //NATIVE帖子类型版块
    public function cardindexAction(){
        Dispatcher::getInstance()->autoRender(false);
        //需要联合帖子查询数量
        $arrData = $this->db->field("*")->table("zs_card_type")->select();
        foreach ($arrData as $key=>$value){
            $arrData[$key]['num'] = count($this->db->field("*")->table("zs_card")->where(" t_id = {$value['id']} ")->select());;
        }
        echo json_encode($arrData);
    }

    //NATIVE宠物品种SDK接口
    public function petbreedAction(){
        Dispatcher::getInstance()->autoRender(false);
        $pid = get("p_id");
        if(!empty($pid)){
            $result = $this->db->field("*")->table("zs_pet_breed")->where("p_id = {$pid}")->select();
            if(!empty($result)){
                $currentPage = empty($_GET["page"])?"1":$_GET["page"];
                $showPage = empty($_GET["showpage"])?"9":$_GET["showpage"];
                $start =  ($currentPage-1)*$showPage;
                $page = $this->db->action("SELECT * FROM zs_pet_breed WHERE p_id = {$pid} LIMIT {$start},{$showPage}");
                echo json_encode($page);
            }else{
                echo json_encode(['code'=>1011,'message'=>"没有此品种"]);exit;
            }
        }else{
            echo json_encode(['code'=>1010,'message'=>"缺少品种ID"]);exit;
        }
    }

    //NATIVE用户基本信息修改SDK接口
    public function usereditAction(){
        Dispatcher::getInstance()->autoRender(false);
        if($this->getRequest()->isPost()){
            $reqdata = json_decode($_POST['json'],true);
            $id = $reqdata['id'];
            $resultdb = $this->db->field("*")->table("zs_user")->where("id = {$id}")->find();
            if(!empty($resultdb)){
                $data['nick_name'] = $reqdata['nick_name'];
                $data['sex'] = $reqdata['sex'];
                $data['location_province'] = $reqdata['location_province'];
                $data['location_city'] = $reqdata['location_city'];
                $data['u_sign'] = $reqdata['u_sign'];
                $data['my_pet'] = $reqdata['my_pet'];
                $data['pet_breed'] = $reqdata['pet_breed'];
                //ios
                $point['lbs'] = $reqdata['location_province'].$reqdata['location_city'];
                $point['u_id']=$id;
                $user['nick_name'] = $reqdata['nick_name'];
                $user['sex'] = $reqdata['sex'];
                $user['u_sign'] = $reqdata['u_sign'];
                $user['my_pet'] = $reqdata['my_pet'];
                $user['pet_breed'] = $reqdata['pet_breed'];
                if (!empty($_FILES['my_avatar']['name'][0])) {
                    $time = time();
                    $fileicon = files("my_avatar");
                    $dir = APP_PATH."/public/user/".$time;
                    if(!file_exists($dir)){
                        mkdir($dir,0777,true);
                    }
                    $pathicon = $dir."/".$fileicon['name'][0];
                    move_uploaded_file( $fileicon['tmp_name'][0],$pathicon);
                    $fileArr = $time."/".$fileicon['name'][0];
                    $data['avatar'] = $fileArr;
                    $user['avatar']=$fileArr;
                    $bool = $this->db->action($this->db->updateSql("user",$data,"id = {$id}"));
                    $this->db->action($this->db->updateSql("ios_user",$user,"id = {$id}"));
                }else{
                    $bool = $this->db->action($this->db->updateSql("user",$data,"id = {$id}"));
                    $this->db->action($this->db->updateSql("ios_user",$user,"id = {$id}"));
                }
                $resultdb2 = $this->db->field("*")->table("zs_user")->where("id = {$id}")->find();
                if($bool){
                    echo json_encode(["code"=>0,"message"=>"success",'data'=>$resultdb2['avatar']]);
                }else{
                    echo json_encode(["code"=>0,"message"=>"success",'data'=>$resultdb2['avatar']]);
                }
            }else{
                echo json_encode(['code'=>1004,'message'=>"手机号不存在，请注册"]);exit;
            }
        }else{
            echo json_encode(["code"=>104,"message"=>"请求方式错误"]);
        }

    }

    //WAP文章内容页
    public function cardcontentAction(){
        $id = get("id");
        $card = $this->db->field("*")->table("zs_news")->where(" id = {$id} ")->findobj();
        $card->content = htmlspecialchars_decode($card->content);
        $this->getView()->assign(["card"=>$card]);
    }

    //玩转游戏列表
    public function gameAction(){
        $this->getView()->assign(["xxx"=>"yyyy"]);
    }

    //WAP注册协议
    public function agreementAction(){
        $this->getView()->assign(["xxx"=>"yyyy"]);
    }

    //系统设置
    public function settingAction(){
        Dispatcher::getInstance()->autoRender(false);
        $arrData = $this->db->field("version_number,version_name,update_content,is_forced_update,update_address,package_size")->table("zs_setting")->order("id desc")->find();
        echo json_encode($arrData);
    }

    public function phpqrcodeAction(){
        Dispatcher::getInstance()->autoRender(false);
        include APP_PATH."/vendor/phpqrcode/phpqrcode.php";
        $dir = APP_PATH."/public";
        $url = "/qrcode/2.png";
        $path = $dir.$url;
        $arrData = $this->db->field("update_address")->table("zs_setting")->find();
        $filename = "http://".server("SERVER_NAME").$arrData['update_address'];
        QRcode::png($filename,$path,"L",20,1);
        include APP_PATH."/application/core/Image.php";
        //\app\core\Image::logoP($path,$dir."/qrcode/12.png",$dir."/qrcode/3.png");
        \app\core\Image::logoP($path,$dir."/ /12.png");
        //echo "<img src='/public/qrcode/3.png' />";
    }
    //通用用户文章收藏接口
    public function usercollectionAction()
    {
        Dispatcher::getInstance()->autoRender(false);
        $uid = get("id");
        $arrData = $this->db->field("id")->table("zs_user")->where(" id = {$uid} ")->find();
        if(!empty($arrData)){
            include APP_PATH."/application/core/Page.php";
            $page2 = new \app\core\Page();
            $len = $this->db->zscount("collection","*","total"," id = {$uid} ");
            $showpage = empty($_GET['showpage']) ? 10 : $_GET['showpage'];
            $page2->init($len,$showpage);
            $strpage = "SELECT n.id,n.title,n.author,n.create_time,n.click,n.pic,n.content,n.type FROM zs_collection AS c INNER JOIN zs_news as n ON n.id=c.n_id WHERE c.u_id = {$uid} {$page2->limit} ";
            $page = $this->db->action($strpage);
            foreach($page as $key=>$value){
                $page[$key]['content'] = mb_substr(strip_tags(htmlspecialchars_decode($value['content'])),0,60,"utf-8");
            }
            echo json_encode($page);
        }else{
            echo json_encode(['code'=>1004,'message'=>"手机号不存在，请注册"]);exit;
        }
    }

    public function emptyAction()
    {
        // TODO: Implement __call() method.
    }
}