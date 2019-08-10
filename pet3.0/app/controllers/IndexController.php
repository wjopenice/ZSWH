<?php
use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Http\Request;
class IndexController extends Controller
{
    const APP_KEY = "MDc2OWJkYWI0ZGJiMmMxMzBjNzA3MGQ5NTU0MDVkODE=";
    //宠爱wifi官网首页
    public function indexAction(){
        if(isMobile()){
            $this->dispatcher->forward(
                [
                    'controller' => 'website',
                    'action'     => 'mobileIndex'
                ]
            );
        }else{
            $this->dispatcher->forward(
                [
                    'controller' => 'website',
                    'action'     => 'pcIndex'
                ]
            );
        }
    }
    //文件上传
    public function uploadAction(){
          if(!empty($_FILES['file'])){
              $time = time();
              $dir = BASE_PATH."/public/merc/".$time;
              if(!file_exists($dir)){
                  mkdir($dir,0777,true);
              }
              $fileArr = "";
              foreach ( $this->request->getUploadedFiles('file') as $file){
                  $fileArr = "/merc/".$time."/".$file->getName();
                  $file->moveTo($dir."/".$file->getName());
              }
              echo json_encode(["msg"=>"ok","data"=>$fileArr]);
          }else{
              echo json_encode(["msg"=>"no"]);
          }
      }
      //登录
      public function loginAction(){

      }
      public function logoutAction(){
          if(!empty($_COOKIE['mobile_num'])){
              setcookie("mobile_num","",time()-1);
              header("location:/index/login");
          }else{
              header("location:/index/login");
              exit;
          }
      }
}
