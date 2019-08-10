<?php
use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Http\Request;
class CardController extends Controller
{
    public $error;
    public $zscard;
    public function initialize()
    {
        include APP_PATH . "/core/Errorcode.php";
        $this->error = \app\core\Errorcode::getCodeConfig();
        $this->zscard = new Zscard();
    }

    public function ajax_return($code, $data = null)
    {
        if (is_null($data)) {
            echo json_encode(['code' => $code, 'message' => $this->error[$code]], 320);
            exit;
        } else {
            echo json_encode(['code' => $code, 'message' => $this->error[$code], "data" => $data], 320);
            exit;
        }
    }
    //文件上传
    public function uploadone($filename, $path)
    {
        $time = time();
        $dir = BASE_PATH . "/public/" . $path . "/" . $time;
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $fileArr = "";
        foreach ($this->request->getUploadedFiles($filename) as $file) {
            $fileArr = "/" . $path . "/" . $time . "/" . $file->getName();
            $file->moveTo($dir . "/" . $file->getName());
        }
        return $fileArr;
    }
    //文件上传
    public function uploadss($filename, $path)
    {
        $time = time();
        $dir = BASE_PATH . "/public/" . $path . "/" . $time;
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $fileArr = [];
        foreach ($this->request->getUploadedFiles($filename) as $file) {
            $fileArr[] = "/" . $path . "/" . $time . "/" . $file->getName();
            $file->moveTo($dir . "/" . $file->getName());
        }
        $datafileArr = json_encode($fileArr, 320);
        return $datafileArr;
    }
    //广场最新
    public function newsAction(){
        $reqdata = $this->request->getPost();
        $code = $this->zscard->news_model($reqdata);
        $this->ajax_return(0,$code);
    }
    //广场发布
    public function releaseAction(){
        $reqdata = $this->request->getPost();
        if ($this->request->hasFiles() == true) {
            $file = $this->uploadss("pic","card");
            $code = $this->zscard->release_model($reqdata,$file);
            $this->ajax_return($code);
        }else{
            $this->ajax_return(102);
        }
    }
    //广场详情
    public function detailAction(){
        $reqdata = $this->request->getPost();
        $code = $this->zscard->detail_model($reqdata);
        $this->ajax_return(0,$code);
    }
    //广场点赞
    public function clickAction(){
        $reqdata = $this->request->getPost();
        $code = $this->zscard->click_model($reqdata);
        $this->ajax_return($code);
    }
    //广场评论
    public function addfeedbackAction(){
        $reqdata = $this->request->getPost();
        $code = $this->zscard->addfeedback_model($reqdata);
        $this->ajax_return($code);
    }
    //广场评论展示
    public function feedbackAction(){
        $reqdata = $this->request->getPost();
        $code = $this->zscard->feedback_model($reqdata);
        if(is_array($code)){
            $this->ajax_return(0,$code);
        }else{
            $this->ajax_return($code);
        }
    }
    //广场添加回复
    public function addreplyAction(){
        $reqdata = $this->request->getPost();
        $code = $this->zscard->addreply_model($reqdata);
        $this->ajax_return($code);
    }

    //广场回复展示
    public function replyAction(){
        $reqdata = $this->request->getPost();
        $code = $this->zscard->reply_model($reqdata);
        $this->ajax_return(0,$code);
    }

    public function testAction(){
        include BASE_PATH."/vendor/JPush/Message.php";
        $arr=["type"=>"2","cate"=>2,"uid"=>["17","20"],"pid"=>"19","pnickname"=>"xxx","pavatar"=>"","data"=>"你好"];
        $message=new Message();
        $message->send($arr);
    }

}