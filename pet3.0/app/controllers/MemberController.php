<?php
use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Http\Request;
use app\core\Pdb;
class MemberController extends Controller{

    public $error;
    public $zsgroup;
    public function initialize(){
        include APP_PATH."/core/Errorcode.php";
        $this->error = \app\core\Errorcode::getCodeConfig();
        $this->zsgroup = new Zsgroup();
        include APP_PATH."/core/Pdb.php";
        $this->pdb = new \app\core\Pdb();
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
    //SDK接口添加群
    public function addgroupsAction(){
        $reqdata = $this->request->getPost();
        $this->pdb->action("set names utf8mb4");
        if($reqdata['g_id'] == -1){
            if ($this->request->hasFiles() == true) {
                $file = $this->uploadone("banner","group");
                $arrdata = $this->pdb->field("*")->table("zsgroup")->where(" title = '".htmlspecialchars($reqdata['title'])."'")->find();
                if($arrdata)
                {
                    $this->ajax_return(1500);
                }
                $addr['banner'] = $file;
                $addr['title'] = htmlspecialchars($reqdata['title']);
                $addr['groupid'] = "";
                $addr['info'] = htmlspecialchars($reqdata['info']);
                $addr['lbs'] = $reqdata['lbs'];
                $addr['longitude'] = $reqdata['longitude'];
                $addr['latitude'] = $reqdata['latitude'];
                $addr['owner_id'] = $reqdata['owner_id'];
                $addr['status'] = 0;
                $addr['members'] = json_encode([]);
                $addr['create_time'] = time();
               // echo $this->pdb->insertSql("zsgroup",$addr);exit;
                $bool = $this->pdb->action($this->pdb->insertSql("zsgroup",$addr));
                if($bool)
                {
                    $this->ajax_return(0);
                }
                else{
                    $this->ajax_return(500);
                }
            }else{
                $this->ajax_return(102);
            }
        }else{
            $file = $this->uploadone("banner","group");
            if($file) {
                $addr['banner'] = $file;
            }
            else{
                $arrdata = $this->pdb->field("*")->table("zsgroup")->where("g_id={$reqdata['g_id']}")->find();
                $addr['banner'] = $arrdata['banner'];
            }
            $addr['title'] = htmlspecialchars($reqdata['title']);
            $addr['groupid'] = "";
            $addr['info'] = htmlspecialchars($reqdata['info']);
            $addr['lbs'] = $reqdata['lbs'];
            $addr['longitude'] = $reqdata['longitude'];
            $addr['latitude'] = $reqdata['latitude'];
            $addr['owner_id'] = $reqdata['owner_id'];
            $addr['status'] = 0;
            $addr['members'] = json_encode([]);
            $addr['create_time'] = time();
            $bool = $this->pdb->action($this->pdb->updateSql("zsgroup",$addr,"g_id={$reqdata['g_id']}"));
            if($bool)
            {
                $this->ajax_return(0);
            }
            else{
                $this->ajax_return(500);
            }
        }
    }
    //SDK接口附近群
    public function groupsAction(){
        $reqdata = $this->request->getPost();
        $code = $this->zsgroup->groups_model($reqdata);
        $this->ajax_return(0,$code);
    }
    //SDK接口群成员
    public function groupsmembersAction(){
        $reqdata = $this->request->getPost();
        $code = $this->zsgroup->groupsmembers_model($reqdata);
        if(is_array($code)){
            $this->ajax_return(0,$code);
        }else{
            $this->ajax_return($code);
        }
    }
    //SDK接口群详情
    public function detailAction(){
        $reqdata = $this->request->getPost();
        $int = $this->zsgroup->detail_model($reqdata);
        $code = $this->zsgroup->groupsmembers_model($reqdata);
        if(is_array($code)){
            $code['material'] = $int;
            $this->ajax_return(0,$code);
        }else{
            $this->ajax_return($code);
        }
    }

    //添加群
    public function easemobaddgroupsAction(){
        include APP_PATH."/core/Easemob.php";
        $easemob = new \app\core\Easemob();
        $this->pdb->action("set names utf8mb4");
        $zsgroup = new Zsgroup();
        $gid = $_POST['gid'];
        //查询群数据
        $reqdata = $this->pdb->field("*")->table("zsgroup")->where(" g_id = {$gid}")->find();
        $data['groupname'] = htmlspecialchars_decode($reqdata['title']);
        $data['desc'] = htmlspecialchars_decode($reqdata['info']);
        $data['public'] = true;
        $data['maxusers'] = 500;
        $data['members_only'] = false;
        $data['allowinvites'] = true;
        $data['owner'] = $reqdata['owner_id'];
        //获取环信群ID
        $result = $easemob->createGroup($data);
        $groupid = $result['data']['groupid'];
        //保存环信群ID
       /* $zsres = $zsgroup::findFirst("g_id = '{$gid}'");
        $zsres->groupid = $groupid;*/
        $groupdata['groupid']=$groupid;
        $groupdata['status']=1;
       // echo $this->pdb->updateSql("zsgroup",$data,"g_id={$gid}");exit;
        $bool = $this->pdb->action($this->pdb->updateSql("zsgroup",$groupdata,"g_id={$gid}"));
        if($bool){
            $target=[];
            $avatar="http://test.pettap.cn/dx.png";
            $data['from']="C000000000";
            $data['ext']=["message_from_name"=>"宠爱小助手","message_from_avatar"=>$avatar,"message_conversation_name"=>"宠爱小助手","message_conversation_icon"=>$avatar];
            $data['msg'] = ["type"=>"txt","msg"=>"你创建的 [".htmlspecialchars_decode($reqdata['title'])."] 群已通过审核，快去邀好友加群吧"];
            $data['target_type'] ="users";
            $target[]=$reqdata['owner_id'];
            $data['target'] =$target;
            $result=$easemob->sendmessage($data);
            echo 1;
        }else{
            echo 0;
        }
    }

    //群组单个加人
    public function addgroupmemberAction(){
        include APP_PATH."/core/Easemob.php";
        $easemob = new \app\core\Easemob();
        $zsgroup = new Zsgroup();
        $groupid=$_POST['g_id'];
        $uid=$_POST['uid'];
        $zsres = $zsgroup::findFirst("groupid = '{$groupid}' or g_id={$groupid}");
        if($uid==$zsres->owner_id)
        {
            $this->ajax_return(1502);
        }
        //环信成员加群
        $result=$easemob->addGroupMember($groupid,$uid);
        $memebers=json_decode($zsres->members,true);
        $key=array_search($uid,$memebers);
       if(is_numeric($key))
       {
           $this->ajax_return(0,$uid);
       }
        if(count($memebers)==500)
        {
            $this->ajax_return(1501);
        }
        if(count($memebers)==0)
            {
                $memebers[]=$uid;
                $zsres->members=json_encode($memebers);
            }
            else{
                array_push($memebers,$uid);
                $zsres->members=json_encode($memebers);
            }
            $bool = $zsres->save();
            if($bool){
               $this->ajax_return(0,$uid);
            }else{
                $this->ajax_return(500);
            }

    }
    //群组单个减人
    public function deletegroupmemberAction(){
        include APP_PATH."/core/Easemob.php";
        $easemob = new \app\core\Easemob();
        $zsgroup = new Zsgroup();
        $groupid=$_POST['g_id'];
        $uid=strtoupper($_POST['uid']);
        //环信成员退群
        $zsres = $zsgroup::findFirst("g_id = '{$groupid}' or groupid={$groupid} ");
        $result=$easemob->deleteGroupMember($zsres->groupid,$uid);
        $memebers=json_decode($zsres->members,true);
         $new =array_merge(array_diff($memebers, array($uid)));
            $zsres->members=json_encode($new);
            $bool = $zsres->save();
            if($bool){
                $this->ajax_return(0);
            }else{
                $this->ajax_return(500);
            }

    }
    /*群组批量减人
    */
       public function deletegroupmembersAction(){
           include APP_PATH."/core/Easemob.php";
           $easemob = new \app\core\Easemob();
           $zsgroup = new Zsgroup();
           $groupid=$_POST['g_id'];
           $zsres = $zsgroup::findFirst("g_id = '{$groupid}' or groupid={$groupid}");
           //$uids=json_decode($_POST['uid']);
           $uid=json_decode($_POST['uid']);
            $arr=implode($uid,",");
           //环信成员退群
           $result=$easemob->deleteGroupMembers($zsres->groupid,$arr);
           $memebers=json_decode($zsres->members,true);
           $new =array_merge(array_diff($memebers,$uid));
           $zsres->members=json_encode($new);
               $bool = $zsres->save();
               if($bool){
                   $this->ajax_return(0);
               }else{
                   $this->ajax_return(500);
               }



        }
        public function testAction(){
            include APP_PATH."/core/Easemob.php";
            $easemob = new \app\core\Easemob();
            $data['target_type'] ="users";
            $data['target'] =["C560736224"];
            $data['msg'] = ["type"=>"txt","msg"=>"4566"];
            //$data['msg'] = ["type"=>"img","filename"=>"http://zhishengwh.com/Uploads/Picture/2019-03-20/5c920e52e8c28.png",
              //  "secret"=>"YXA6RKFmWAIEys5Eko0s_3F579I4Epg","url"=>"https://a1.easemob.com/easemob-demo/pettap/chatfiles/YXA6i0ZPUEu3Eem4ZW3y4vAKUw"];
            $data['size']=["width"=>100,"height"=>100];
            $data['from']="宠爱小助手";
            $data['ext']=["message_from_name"=>"宠爱小助手","message_from_avatar"=>"http://test.pettap.cn/dx.png"];
            $result=$easemob->sendmessage($data);
            print_r($result);
            exit;
        }
        public function uploadfileAction(){
            include APP_PATH."/core/Easemob.php";
            $easemob = new \app\core\Easemob();
            $file = $this->uploadone("file","chats");
            $filepath="http://test.pettap.cn".$file;
            $from='宠爱小助手';
            $target_type="users";
            $target=["C560736224"];
            $filename=basename($file);
            $ext['message_from_name']="宠爱小助手";
            $ext['message_from_avatar']="http://test.pettap.cn/dx.png";
            $result=$easemob->sendImage($filepath,$from,$target_type,$target,$filename,$ext);
            print_r($result);
            exit;
        }

}