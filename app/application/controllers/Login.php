<?php
use Yaf\Dispatcher;
use Helper\Image;
class LoginController extends EventController{
    //入口
    public function loginAction(){
        if($this->requests->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $u = addslashes(post("u"));
            $p = $this->hmac256(addslashes(post("p")));
            $result = $this->db->field("*")->table("tab_system")->where("user = '{$u}' and pass = '{$p}'")->find();
            if($u == $result['user'] && $p == $result['pass']){
                $_SESSION['username'] = $u;
                $time = date("Y-m-d H:i:s",time());
                $this->db->action($this->db->updateSql("system", ["login_time"=>$time], " user = '{$u}'"));
                echo json_encode(["msg"=>"ok"]);
            }else{
                echo json_encode(["msg"=>"no"]);
            }
        }else{
          // echo  $this->hmac256(addslashes("admin12345678"));
            $this->getView()->assign("content", "xxxxxx");
        }
    }
    public function logoutAction(){
        Dispatcher::getInstance()->autoRender(false);
        unset($_SESSION['username']);
        header("location:/login/login");
    }
    public function codeAction(){
        Dispatcher::getInstance()->autoRender(false);
        header("content-type:image/png");
        Image::code(160,56,25,15,35,35,"/public/fonts/MSYHBD.TTC");
    }
    public function groupAction(){}
    public function gameAction(){}
    public function newsAction(){}
    public function hmac256($data){
        return hash_hmac("sha256",$data,"MDc2OWJkYWI0ZGJiMmMxMzBjNzA3MGQ5NTU0MDVkODE=");
    }
}
?>
