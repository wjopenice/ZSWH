<?php
use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Http\Request;
class WebsiteController extends Controller
{
    public $error;
    public $zsmap;
    public function initialize(){
        include APP_PATH."/core/Errorcode.php";
        $this->error = \app\core\Errorcode::getCodeConfig();
        $this->zsmap = new Zsmap();
    }
    public function ajax_return($code,$data = null){
        if(is_null($data)){
            echo json_encode(['code'=>$code,'message'=>$this->error[$code]],320);exit;
        }else{
            echo json_encode(['code'=>$code,'message'=>$this->error[$code],"data"=>$data],320);exit;
        }
    }
    //文件上传
    public function uploadone($filename,$path){
        $time = time();
        $dir = BASE_PATH."/public/".$path."/".$time;
        if(!file_exists($dir)){
            mkdir($dir,0777,true);
        }
        $fileArr = "";
        foreach ( $this->request->getUploadedFiles($filename) as $file){
            $fileArr = "/".$path."/".$time."/".$file->getName();
            $file->moveTo($dir."/".$file->getName());
        }
        return $fileArr;
    }
    //文件上传
    public function uploadss($filename,$path){
        $time = time();
        $dir = BASE_PATH."/public/".$path."/".$time;
        if(!file_exists($dir)){
            mkdir($dir,0777,true);
        }
        $fileArr = [];
        foreach ( $this->request->getUploadedFiles($filename) as $file){
            $fileArr[] = "/".$path."/".$time."/".$file->getName();
            $file->moveTo($dir."/".$file->getName());
        }
        $datafileArr = json_encode($fileArr,320);
        return $datafileArr;
    }
    //mobile目录规则
    public function mobileIndexAction(){
        $this->view->pick('website/mobile/index');
    }
    //pc目录规则
    public function pcIndexAction(){
        $this->view->pick('website/pc/index');
    }

    //h5 联系我们
    public function mobileContactusAction(){
        $zscontactus = new Zscontactus();
        $result = $zscontactus::findFirst("id = 1");
        $this->view->setVar("result",$result);
        $this->view->pick('website/mobile/contactus');
    }

    //h5 关于我们
    public function mobileAboutAction(){
        $zsabout = new Zsabout();
        $result = $zsabout::findFirst("id = 1");
        $this->view->setVar("result",$result);
        $this->view->pick('website/mobile/about');
    }


}
