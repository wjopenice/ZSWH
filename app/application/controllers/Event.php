<?php
use Yaf\Application;
use Error\CodeConfigModel;
//注释：继承此类的子类不会出发空操作
//查看隐藏类请使用反射：ReflectionClass::export(类名);
class EventController extends Yaf\Controller_Abstract {
    public $db;
    public $error;
    public $requests;
    public $response;
    public $cookies;
    public $files;
    public $posts;
    public $input;
    public function init(){
        $this->db = new dbModel();
        $this->error = (new CodeConfigModel())->getCodeConfig();
        $this->requests = $this->getRequest();
        $this->posts = $this->getRequest()->getPost();
        $this->response = $this->getResponse();
        $this->cookies = $this->getRequest()->getCookie();
        $this->files = $this->getRequest()->getFiles();
        $this->input = json_decode(file_get_contents("php://input"),true);
    }

    public function ajax_return($code,$data = null){
        if(is_null($data)){
            echo json_encode(['code'=>$code,'message'=>$this->error[$code]]);exit;
        }else{
            echo json_encode(['code'=>$code,'message'=>$this->error[$code],"data"=>$data]);exit;
        }
    }

    public function ajax_return_320($code,$data = null){
        if(is_null($data)){
            echo json_encode(['code'=>$code,'message'=>$this->error[$code]],320);exit;
        }else{
            echo json_encode(['code'=>$code,'message'=>$this->error[$code],"data"=>$data],320);exit;
        }
    }

    public function uploadone($file,$path){
        $time = time();
        $dir = APP_PATH."/public/".$path."/".$time;
        if(!file_exists($dir)){
            mkdir($dir,0777,true);
        }
    /*    $pathicon = $dir."/".$file['name'];
        $name=$_FILES['userfile']['name'];   //将所上传的文件名称赋予name
        move_uploaded_file( $file['tmp_name'],$pathicon);
        $fileArr = "/public/".$path."/".$time."/".$file['name'];*/
      //  return $fileArr;
        $ext =$this->getFileExt($file['name']);
        $f['ext'] = $ext;
        $f['title'] = $file['name'];
        $f['size'] = $file['size'];
        $file_base = date('YmdHis') . '-' . sprintf( '%06d', rand( 0, 999999 ) );
        $pathicon=APP_PATH."/public/".$path."/".$time."/";
        if(move_uploaded_file($file['tmp_name'],$pathicon.$file_base.".".$ext))
        {
              return "/public/".$path."/".$time."/".$file_base.".".$ext;
        }
    }
   public function getFileExt($filename)
    {
        $filename = trim($filename);
        $pos =  strrpos( $filename, '.' );
        if(empty($pos)){
            return "";
        }
        $ext = substr( $filename, $pos+1 ) ;
        return strtolower($ext);
    }
    public function uploadss($file,$path){
        $time = time();
        $dir = APP_PATH."/public/".$path."/".$time;
        if(!file_exists($dir)){
            mkdir($dir,0777,true);
        }
        $fileArr = [];
        for($i=0;$i<count($file['name']);$i++){
            $pathicon = $dir."/".$file['name'][$i];
            if(!empty($file['name'][$i])) {
                move_uploaded_file($file['tmp_name'][$i], $pathicon);
                $fileArr[] = "/public/" . $path . "/" . $time . "/" . $file['name'][$i];
            }
        }
        $filedata = json_encode($fileArr,320);
        return $filedata;
    }

    //301跳转
    public function redirect($url){
        $this->forward($url);
    }
    public function success($msg,$url){
        echo "<script>alert('".$msg."');window.location.href='".$url."';</script>";
    }
    public function error($msg){
        echo "<script>alert('".$msg."');window.history.back();</script>";
    }
    public function statusUrl($bool,string $success_msg, string $success_url,string $error_msg){
        if($bool){
            $this->success($success_msg,$success_url);
        }else{
            $this->error($error_msg);
        }
    }

    public function emptyAction()
    {
        // TODO: Implement __call() method.
    }

}