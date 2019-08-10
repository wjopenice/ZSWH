<?php
namespace Mobile\Controller;
use Think\Controller;
use Org\Util\Memcache as Memcache;
use Admin\Model\GameModel;
class GiftController extends BaseController {
    private $_cache;
    public function __construct() {
        parent::__construct();
        //初始化
        $this->_cache = Memcache::instance();
    }

    public function wapgiftlist(){
         //全部礼包
        $this->game_sort();
        //最新礼包
        $this->gift();
        //推荐礼包
        $this->recommend_gift();
        $this->display();
    }
    /**
     *全部游戏礼包
     */
    public function game_sort($game_type=0,$p=0,$id=0){
        $p = I('get.p');
        $p = empty($p)?1:$p;
        $row=I('get.pagesize');
        $row = empty($row)?6:$row;
        $map['game_status']=1;
        if($_GET['game_type']){
            $map['game_type_id']=$_GET['type'];
        }
        $model=M('game','tab_');
        $data=$model
            ->alias('a')
            ->field('a.id,a.game_type_id ,a.icon,a.game_name,b.game_id,b.giftbag_name,b.id as gift_id,b.desribe')
            ->join("tab_giftbag as b on a.id = b.game_id ")
            ->where($map)
            ->order('a.sort asc')
           // ->page("$p,$row")
            ->select();
        $count = $model
            ->alias('a')
            ->field('a.id,a.game_type_id ,a.icon,a.game_name,b.game_id,b.giftbag_name,b.id as gift_id')
            ->join("tab_giftbag as b on a.id = b.game_id ")
            ->where($map)
            ->count();
        $this->assign('data',$data);
        $this->assign('count',$count);
    }
    /**
     *推荐礼包
     */
    public function recommend_gift(){
        $model = array(
            'm_name'=>'Giftbag',
            'prefix'=>'tab_',
            'field' =>'tab_giftbag.id as gift_id,game_id,tab_giftbag.game_name,giftbag_name,desribe,tab_giftbag.gift_icon',
            'join'    =>'tab_game on tab_giftbag.game_id = tab_game.id',
            'map'   =>array('game_status'=>1),
            'order' =>'tab_giftbag.create_time desc',
            'group' =>'game_name',

            'limit' =>4 ,
        );
        $recommend_gift = parent::join_data($model);
        //print_r($recommend_gift);exit;
        $this->assign('recommend_gift',$recommend_gift);
    }
    /**
     *最新礼包
     */
    public function gift(){
        $p = I('get.p');
        $model = array(
            'm_name'=>'Giftbag',
            'prefix'=>'tab_',
            'field' =>'tab_giftbag.id as gift_id,tab_giftbag.game_id,tab_giftbag.game_name,tab_giftbag.giftbag_name,tab_giftbag.desribe,tab_game.icon',
            'join'  =>'tab_game on tab_giftbag.game_id = tab_game.id',
            'map'   =>array('game_status'=>1),
            'group' =>'tab_giftbag.game_id',
            'order' =>'tab_giftbag.id desc',
            'limit' =>6    ,
        );
        $row = 16;
        $gift = parent::join_data($model,$p,$row);
        $this->assign('gift',$gift);
    }
    //领取礼包
    public function getGameGift() {
        $username = $_SESSION['username'];
        $userdata = M("user","tab_")->where(["account"=>$username])->find();
        $mid=$userdata['id'];
        if(empty($_SESSION['username']))
        {
            echo  json_encode(array('status'=>'0','msg'=>'请先用户登录'));
            exit();
        }
        else{
            if($_SESSION['status']==2){
                echo  json_encode(array('status'=>'0','msg'=>'请先用户登录'));
                exit();
            }
        }


        $list=M('record','tab_gift_');

        $is=$list->where(array('user_id'=>$mid,'gift_id'=>$giftid));

        if($is) {
            $map['user_id']=$mid;
            $map['gift_id']=$_POST['giftid'];
            $msg=$list->where($map)->find();
            if($msg){
                $data=$msg['novice'];
                echo  json_encode(array('status'=>'1','msg'=>'no','data'=>$data));
            }else{
                $bag=M('giftbag','tab_');
                $giftid= $_POST['giftid'];
                $ji=$bag->where(array("id"=>$giftid))->field("novice")->find();
                if(empty($ji['novice'])){
                    echo json_encode(array('status'=>'1','msg'=>'noc'));
                }else{
                    $at=explode(",",$ji['novice']);
                    $gameid=$bag->where(array("id"=>$giftid))->field('game_id')->find();
                    $game=M('Game','tab_')->where(array("id"=>$gameid['game_id']))->field('game_name')->find();
                    $add['game_id']=$gameid['game_id'];
                    $add['game_name']=$game['game_name'];
                    $add['gift_id']=$_POST['giftid'];
                    $add['gift_name']=$_POST['giftname'];
                    $add['status']=1;
                    $add['novice']=$at[0];
                    $add['user_id'] =$mid;
                    $add['create_time']=strtotime(date('Y-m-d h:i:s',time()));
                    $list->add($add);
                    $new=$at;
                    if(in_array($new[0],$new)){
                        $sd=array_search($new[0],$new);
                        unset($new[$sd]);
                    }
                    $act['novice']=implode(",", $new);
                    $bag->where("id=".$giftid)->save($act);
                    echo  json_encode(array('status'=>'1','msg'=>'ok','data'=>$at[0]));
                }
            }
        }
    }

}