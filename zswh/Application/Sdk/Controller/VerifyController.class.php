<?php
namespace Sdk\Controller;
use Think\Controller\RestController;
class VerifyController extends RestController{
    /**
     * 登录用户验证
     */
    public function user_verify(){
        $data = json_decode(base64_decode(file_get_contents("php://input")),true);
        $listArr = [];
        if(empty($data)){
            $listArr = ["status"=>"0","msg"=>"参数缺失"];
        }else{
            $userData = M("User",'tab_')->where("id=".$data['uid'])->find();
            $token1 = $userData['token'];
            $cptoken = $data['token'];
            if($token1 == $cptoken){
                $listArr = ['status'=>1,'msg'=>"token通过"];
                //$cpdata['login_time'] = $data['ts'];
//                $cpdata['game_id'] = $data['gameid'];
//                $cpdata['user_id'] = $data['uid'];
//                $userfind = M('user_login_record','tab_')->where($cpdata)->find();
//                if(!empty($userfind)){
//                    $listArr = ['status'=>1,'msg'=>"token通过"];
//                }else{
//                    $listArr = ['status'=>2,'msg'=>"token失效"];
//                }
            }else{
                $listArr = ['status'=>3,'msg'=>"token错误"];
            }
        }
        echo base64_encode(json_encode($listArr));
        unset($listArr);
    }

    public function test(){
        $data = I("post.");
        file_put_contents("./log/log7.txt",json_encode($data)."order response cp ok.--\r\n",FILE_APPEND);
    }

}