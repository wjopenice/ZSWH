<?php
use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Http\Request;
class MapController extends Controller
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
    //救助首页
    public function indexAction(){
        $reqdata = $this->request->getPost();
        $code = $this->zsmap->index_model($reqdata);
        $this->ajax_return(0,$code);
    }
    //发布地图
    public function releaseAction(){
        $reqdata = $this->request->getPost();
        if ($this->request->hasFiles() == true) {
            $file = $this->uploadss("pic","map");
            $code = $this->zsmap->release_model($reqdata,$file);
            $this->ajax_return($code);
        }else{
            $this->ajax_return(102);
        }
    }
    //救助详细
    public function detailAction(){
        $reqdata = $this->request->getPost();
        $code = $this->zsmap->detail_model($reqdata);
        if(is_array($code)){
            $this->ajax_return(0,$code);
        }else{
            $this->ajax_return($code);
        }
    }
    //救助点赞
    public function clickAction(){
        $reqdata = $this->request->getPost();
        $code = $this->zsmap->click_model($reqdata);
        $this->ajax_return($code);
    }
    //救助记录
    public function logAction(){
        $reqdata = $this->request->getPost();
        $code = $this->zsmap->log_model($reqdata);
        if(is_array($code)){
            $this->ajax_return(0,$code);
        }else{
            $this->ajax_return($code);
        }
    }
    //救助评论
    public function addfeedbackAction(){
        $reqdata = $this->request->getPost();
        $code = $this->zsmap->addfeedback_model($reqdata);
        $this->ajax_return($code);
    }
    //救助评论展示
    public function feedbackAction(){
        $reqdata = $this->request->getPost();
        $code = $this->zsmap->feedback_model($reqdata);
        if(is_array($code)){
            $this->ajax_return(0,$code);
        }else{
            $this->ajax_return($code);
        }
    }
    //救助回复展示
    public function replyAction(){
        $reqdata = $this->request->getPost();
        $code = $this->zsmap->reply_model($reqdata);
        $this->ajax_return(0,$code);
    }
    //救助回复
    public function addreplyAction(){
        $reqdata = $this->request->getPost();
        $code = $this->zsmap->addreply_model($reqdata);
        $this->ajax_return($code);
    }
    //用户实时更新定位
    public function lbsAction(){
        $reqdata = $this->request->getPost();
        $code = $this->zsmap->lbs_model($reqdata);
        $this->ajax_return($code);
    }
    //用户屏蔽帖子
    public function screenAction(){
        $reqdata = $this->request->getPost();
        $code = $this->zsmap->screen_model($reqdata);
        $this->ajax_return($code);
    }
    //医院/救助站详情
    public function mercAction(){
        $reqdata = $this->request->getPost();
        $code = $this->zsmap->merc_model($reqdata);
        $this->ajax_return(0,$code);
    }

    //帖子消息
    public function postmessageAction(){
        $reqdata = $this->request->getPost();
        $code=$this->zsmap->message_model($reqdata);
        $this->ajax_return(0,$code);
    }

}