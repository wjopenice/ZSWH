<?php
use Phalcon\Mvc\Controller;
use Phalcon\Http\Request;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
class UserController extends Controller
{
    public $user;
    public function initialize() {
        if($this->session->has("username")){
            $this->user = $this->session->get("username");
        }else{
            header("location:/login/login");exit;
        }
    }

    public function administratorsAction(){}

    public function userlistAction(){
        $zs_user = new Zs_user();
        $arrData = $zs_user::find();
        $currentPage = empty($_GET["page"])?"1":$_GET["page"];
        $paginator   = new PaginatorModel(["data"  => $arrData,"limit" => 10,"page"  => $currentPage]);
        $page = $paginator->getPaginate();
        $this->view->setVar("page",$page);
    }

    public function userdelAction(){
        $request = new Request();
        $id = $request->get("id");
        $lock_status = $request->get("status");
        $zs_user = new Zs_user();
        $result = $zs_user::findFirst("id= $id and lock_status = '$lock_status' ");
        $bool = $result->delete();
        if($bool){
            return $this->response->setJsonContent(['msg'=>"ok"]);
        }else{
            return $this->response->setJsonContent(['msg'=>"no"]);
        }
        $this->view->disable();
    }

    public function usereditAction(){
        $request = new Request();
        $id = $request->get("id");
        $lock_status = $request->get("status");
        $zs_user = new Zs_user();
        $result = $zs_user::findFirstById($id);
        $result->lock_status = $lock_status;
        $bool = $result->save();
        if($bool){
            return $this->response->setJsonContent(['msg'=>"ok"]);
        }else{
            return $this->response->setJsonContent(['msg'=>"no"]);
        }
        $this->view->disable();
    }

    public function usersearchAction(){
        $request = new Request();
        $search = addslashes($request->get("search"));
        $zs_user = new Zs_user();
        $result = $zs_user::findFirst(" mobile_num = $search");
        $this->view->setVar("page",$result);
    }

    public function userblackAction(){
        $zs_user = new Zs_user();
        $arrData = $zs_user::findByLock_status('1');
        $currentPage = empty($_GET["page"])?"1":$_GET["page"];
        $paginator   = new PaginatorModel(["data"  => $arrData,"limit" => 10,"page"  => $currentPage]);
        $page = $paginator->getPaginate();
        $this->view->setVar("page",$page);
    }

    public function userbpAction(){
        $zs_user_bp = new Zs_user_bp();
        $arrData = $zs_user_bp::find();
        $currentPage = empty($_GET["page"])?"1":$_GET["page"];
        $paginator = new PaginatorModel(["data"  => $arrData,"limit" => 15,"page"  => $currentPage]);
        $page = $paginator->getPaginate();
        $this->view->setVar("page",$page);
    }

    //救助站列表
    public function verifyAction(){
        $zs_user_merc = new Zs_user_merc();
        $arrData = $zs_user_merc::findByStatus(0);
        $this->view->setVar("arrData",$arrData);
    }

    //救助站详情
    public function verifydetailAction(){
        $id = $this->request->get("id");
        $zs_user_merc = new Zs_user_merc();
        $oneData = $zs_user_merc::findFirst(['columns'=>['id','create_time','commercial','sex','real_name','id_card','mobile_num','address','info'],'conditions'=>'id = '.$id]);
        $twoData = $zs_user_merc::findFirst(['columns'=>['idcard','reidcard','handidcard','permit','field1','field2','field3','field4'],'conditions'=>'id = '.$id]);
        $mid = $oneData->id;
        $this->view->setVar("oneData",$oneData);
        $this->view->setVar("twoData",$twoData);
        $this->view->setVar("mid",$mid);
    }

    public function verifydetail2Action(){
        $id = $this->request->get("id");
        $zs_user_merc = new Zs_user_merc();
        $oneData = $zs_user_merc::findFirst(['columns'=>['id','create_time','commercial','sex','real_name','id_card','mobile_num','address','info'],'conditions'=>'id = '.$id]);
        $twoData = $zs_user_merc::findFirst(['columns'=>['idcard','reidcard','handidcard','permit','field1','field2','field3','field4'],'conditions'=>'id = '.$id]);
        $mid = $oneData->id;
        $this->view->setVar("oneData",$oneData);
        $this->view->setVar("twoData",$twoData);
        $this->view->setVar("mid",$mid);
    }

    public function phpqrcodeAction(){
        include BASE_PATH."/vendor/phpqrcode/phpqrcode.php";
        $dir = BASE_PATH."/public";
        $url = "/qrcode/2.png";
        $path = $dir.$url;
        QRcode::png("xxxxx",$path,"L",20,15);

        include BASE_PATH."/app/core/Image.php";
        \app\core\Image::logoP($path,$dir."/qrcode/12.png",$dir."/qrcode/3.png");

        echo "<img src='/qrcode/3.png' />";
        $this->view->disable();
    }

    public function verifydataAction(){
        $zs_user_merc = new Zs_user_merc();
        $id = $this->request->getPost("id");
        $result = $zs_user_merc::findFirstById($id);
        $result->is_commercial = $this->request->getPost("is_commercial");//经营性 非经营性
        $result->is_sex = $this->request->getPost("is_sex");//男 女
        $result->is_real_name = $this->request->getPost("is_real_name");//姓名
        $result->is_id_card = $this->request->getPost("is_id_card");//身份证
        $result->is_mobile_num = $this->request->getPost("is_mobile_num");//手机号
        $result->is_address = $this->request->getPost("is_address");//地址
        $result->is_info = $this->request->getPost("is_info");//公司描述
        $result->is_idcard = $this->request->getPost("is_idcard");//身份证正面照
        $result->is_reidcard = $this->request->getPost("is_reidcard");//身份证反面照
        $result->is_handidcard = $this->request->getPost("is_handidcard");//手持身份证照
        $result->is_permit = $this->request->getPost("is_permit");//营业执照
        $result->is_field1 = $this->request->getPost("is_field1");//场地照片图
        $result->is_field2 = $this->request->getPost("is_field2");//场地照片图
        $result->is_field3 = $this->request->getPost("is_field3");//场地照片图
        $result->is_field4 = $this->request->getPost("is_field4");//场地照片图
        $bool = $result->save();
        if($bool){
            if($result->is_commercial == '1' && $result->is_sex == '1' && $result->is_real_name == '1' && $result->is_id_card == '1' && $result->is_mobile_num == '1' && $result->is_address == '1' && $result->is_info == '1' && $result->is_idcard == '1' && $result->is_reidcard == '1' && $result->is_handidcard == '1' && $result->is_permit == '1' && $result->is_field1 == '1' && $result->is_field2 == '1' && $result->is_field3 == '1' && $result->is_field4 == '1'){
                $result2 = $zs_user_merc::findFirstById($id);
                $result2->status = '1';
                $result2->save();
                $user = new Zs_user();
                $userData = $user::findFirstByMobile_num($result->mobile_num);
                $userData->competence = ($result->commercial == '经营性')?"官方救助站":"民间救助站";
                $userData->sex = $result->sex;
                $userData->real_name = $result->real_name;
                $userData->id_card = $result->id_card;
                $userData->save();
                success("提交成功","/user/verify");
            }else{
                success("提交成功","/user/verify");
            }
        }else{
            error("提交失败");
        }
//        $bool = $result->save($this->request->getPost(),["is_commercial","is_sex","is_real_name","is_id_card","is_mobile_num","is_address","is_info","is_idcard","is_reidcard","is_handidcard","is_permit","is_field1","is_field2","is_field3","is_field4"]);
//        statusUrl($bool,"提交成功","/user/verify","提交失败");
    }

    public function verifystatusAction(){
        $zs_user_merc = new Zs_user_merc();
        $arrData = $zs_user_merc::findByStatus(1);
        $this->view->setVar("arrData",$arrData);
    }

}
