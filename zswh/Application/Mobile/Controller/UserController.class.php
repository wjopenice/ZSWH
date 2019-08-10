<?php
namespace Mobile\Controller;
use Think\Controller;
use OT\DataDictionary;
use User\Api\PromoteApi;
use User\Api\MemberApi;
use Org\Util\Memcache as Memcache;

class UserController extends Controller{
    private $_cache;
    //登录
    public function waplogin(){
        if(IS_POST){
            //初始化并传memcache前缀
            $this->_cache = Memcache::instance();
            $type = I("post.type");
            $account = I("post.account");
            $password = I("post.password");
            if($type == 'merc'){
                $promote = new PromoteApi();
                $result = $promote->login($account,$password);
                if ($result >0) {
                    $_SESSION["status"] = 2;
                    $_SESSION["username"] = $account;
                    $this->success("登陆成功",U('Mobile/Index/wapindex'));
                }else{
                    $msg = "";
                    switch ($result) {
                        case -1:
                            $msg = "用户不存在";
                            break;
                        case -2:
                            $msg = "密码错误";
                            break;
                        case -3:
                            $msg = "用户被禁用,请联系管理员";
                            break;
                        case -4:
                            $msg = "审核中,请联系管理员";
                            break;
                        default:
                            $msg = "未知错误！请联系管理员";
                            break;
                    }
                    $this->error($msg);
                }
            }else{
                $user_ip=get_client_ip();
                $key = "media_login_password_".$user_ip;
                $data = array();
                $member = new MemberApi();
                $res = $member->login($_POST['account'],$_POST['password']);
                if($res > 0){
                    $this->autoLogin($res);
                    //清除memcache  密码错误的ip
                    $this->_cache->rm($key);
                    $_SESSION["status"] = 0;
                    $_SESSION["username"] = $account;
                    $this->success("登陆成功",U('Mobile/Index/wapindex'));
                }else{
                    switch ($res) {
                        case -1:
                            $data = array('status'=>0,'msg'=>'用户不存在或被禁用,请联系客服');
                            break;
                        case -2:
                            //获取用户ip
                            $user_ip=get_client_ip();
                            $key = "media_login_password_".$user_ip;
                            $is=$this->_cache->get($key);

                            //设置超时时间1小时
                            //获取当前IP密码输错次数
                            if (empty($is)) {
                                $this->_cache->set($key, 1, 3600);
                                $data = array('status'=>0,'msg'=>'密码错误');
                            }
                            else{
                                //密码输错次数大于三次  用户需输入验证码
                                $is++;
                                if ($is >= 3) {
                                    $data = array('status'=>-999,'msg'=>'请输入正确的密码！！');
                                } else {
                                    $this->_cache->set($key, $is, 3600);
                                    $data = array('status'=>0,'msg'=>'密码错误');
                                }
                            }
                            break;
                        default:
                            $data = array('status'=>0,'msg'=>'未知错误');
                            break;
                    }
                    $this->error($data['msg']);
                }
            }
        }else{
            $this->display();
        }
    }
    //注册
    public function wapregister(){
        if(IS_POST){
            $data = I("post.");
            if($data['type'] == 'merc'){
                unset($data['type']);
                $Promote = new PromoteApi();
                $data['status']=1;
                $data['email']="";
                $pid = $Promote->register($data);
                if($pid > 0){
                    $_SESSION["status"] = 2;
                    $_SESSION["username"] = $data['account'];
                    $this->success("注册成功",U('Mobile/Index/wapindex/'));
                }
                else{
                    $this->error("注册失败,".$pid);
                }
            }else{
                unset($data['type']);
                $member = new MemberApi();
                $res = $member->register(trim($data['username']),trim($data['pwd']),"","","");
                if($res > 0 ){
                    $_SESSION["status"] = 0;
                    $_SESSION["username"] = $data['username'];
                    $this->success("注册成功",U('Mobile/Index/wapindex'));
                }else{
                    $msg = $res == -1 ?"账号已存在":"注册失败";
                    $this->error($msg);
                }
            }
        }else{
            $sys_member=M('member')->where('status=1')->select();
            $this->assign('sys_member',$sys_member);
            $this->display();
        }
    }

    public function loginout(){
        session_destroy();
        $this->success("退出成功",U('Mobile/Index/wapindex'));
    }

    public function autoLogin($uid){
        $user =$this->entity($uid);
        /* 记录登录SESSION和COOKIES */
        $auth = array(
            'mid'             => $user['id'],
            'account'		  => $user['account'],
            'nickname'        => $user['nickname'],
            'balance'         => $user['balance'],
            'last_login_time' => $user['login_time'],
        );
        session('member_auth', $auth);
        session('member_auth_sign', data_auth_sign($auth));
    }
    public function entity($id){
        $data = M('User','tab_')->find($id);
        if(empty($data)){
            return false;
        }
        return $data;
    }
}