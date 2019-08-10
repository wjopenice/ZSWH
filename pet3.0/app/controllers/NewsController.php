<?php
use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Http\Request;
class NewsController extends Controller
{
    public $user;
	public $session;
	public $pdb;
    public function initialize() {
    	include APP_PATH."/core/Session.php";
		$this->session = new \app\core\Session();
        if($this->session->has("username")){
            $this->user = $this->session->get("username");
			include APP_PATH."/core/Pdb.php";
    		$this->pdb = new \app\core\Pdb();
        }else{
            header("location:/login/alogin");
        }
    }
    //单文件上传
    public function uploadone($filename,$path){
        $time = time();
        $dir = BASE_PATH."/public/".$path."/".$time;
        if(!file_exists($dir)){
            mkdir($dir,0777,true);
        }
        $fileArr = "";
        foreach ( $this->request->getUploadedFiles($filename) as $file){
            /*  $fileArr = "/".$path."/".$time."/".$file->getName();
              $file->moveTo($dir."/".$file->getName());*/
            if(strstr($file->getKey(),$filename)){
                $fileArr = "/".$path."/".$time."/".$file->getName();
                $file->moveTo($dir."/".$file->getName());
            }
        }
        return $fileArr;
    }
    public function uploadss($filename,$path){
        $time = time();
        $dir = BASE_PATH."/public/".$path."/".$time;
        if(!file_exists($dir)){
            mkdir($dir,0777,true);
        }
        $fileArr = [];
        foreach ( $this->request->getUploadedFiles($filename) as $file){
            if(strstr($file->getKey(),$filename)){
                $fileArr[] = "/".$path."/".$time."/".$file->getName();
                $file->moveTo($dir."/".$file->getName());
            }
        }
        $datafileArr = json_encode($fileArr,320);
        return $datafileArr;
    }
    public function uploadfile($file,$path){
        $time = time();
        $dir = BASE_PATH."/public/".$path."/".$time;
        if(!file_exists($dir)){
            mkdir($dir,0777,true);
        }
        $pathicon = $dir."/".$file['name'];
        move_uploaded_file( $file['tmp_name'],$pathicon);
        if($file['name']) {
            $fileArr = "/" . $path . "/" . $time . "/" . $file['name'];
        }
        else{
            $fileArr="";
        }
        return $fileArr;
    }
    public function wapaboutAction(){
        $zsabout = new Zsabout();
        $result = $zsabout::findFirst("id = 1");
        if($this->request->isPost()){
            $reqdata = $this->request->getPost();
            $result->title = $reqdata['title'];
            $result->text = $reqdata['text'];
            $bool = $result->save();
            statusUrl($bool,"添加成功","/news/wapabout/news/1","添加失败");
        }else{
            $this->view->setVar("result",$result);
        }
    }

    public function wapcontactusAction(){
        $zscontactus = new Zscontactus();
        $result = $zscontactus::findFirst("id = 1");
        if($this->request->isPost()){
            $reqdata = $this->request->getPost();
            $result->title = $reqdata['title'];
            $result->address = $reqdata['address'];
            $result->postal_code = $reqdata['postal_code'];
            $result->qq = $reqdata['qq'];
            $result->email = $reqdata['email'];
            $result->code_title1 = $reqdata['code_title1'];
            $result->code_pic1 = $this->uploadone("code_pic1","news");
            $result->code_title2 = $reqdata['code_title2'];
            $result->code_pic2 = $this->uploadone("code_pic2","news");
            $bool = $result->save();
            statusUrl($bool,"添加成功","/news/wapcontactus/news/2","添加失败");
        }else{
            $this->view->setVar("result",$result);
        }
    }
}
