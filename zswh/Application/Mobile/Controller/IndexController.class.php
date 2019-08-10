<?php
namespace Mobile\Controller;
use Think\Controller;
use Org\Util\Memcache as Memcache;
use Admin\Model\GameModel;
class IndexController extends BaseController {
    private $_cache;
    public function __construct() {
        parent::__construct();
        //初始化
        $this->_cache = Memcache::instance();
    }
    public function wapindex(){
        $this->meta_title = '首页';
        $adv = M("Adv","tab_");
        $map['status'] = 1;
        $map['pos_id'] = array('in','8,9,10,11');
        $adv_top= $adv->where($map)->order('sort ASC')->select();
        foreach ($adv_top as $key=>$value)
        {
            /* 获取详细信息 */
            $game=M("Game","tab_")->where("game_name='".$value['title']."' and game_status=1")->order('sort desc')->field("id")->find();
            $adv_top[$key]['game_id']=$game['id'];
        }
        $game_list = $this->_get_game_list();
        //卡牌游戏
        $kapai = $this->get_game_by_type($game_list, 'game_type_id', '9', 3);
        //角色扮演
        $juese = $this->get_game_by_type($game_list, 'game_type_id', '6', 3);
        //策略养成
        $celue = $this->get_game_by_type($game_list, 'game_type_id', '8', 3);
        //休闲益智
        $xiuxian = $this->get_game_by_type($game_list, 'game_type_id', '16', 3);

        $this->assign('kapai',$kapai);
        $this->assign('juese',$juese);
        $this->assign('celue',$celue);
        $this->assign("adv_top",$adv_top);
        $this->assign('xiuxian',$xiuxian);

        //游戏礼包
        $key = 'mobile_index_game_gift';
        $gift = $this->_cache->get($key);
        if(empty($gift)){
            $model = array(
                'm_name'=>'Giftbag',
                'prefix'=>'tab_',
                'field' =>'tab_giftbag.id as gift_id,game_id,tab_giftbag.game_name,tab_giftbag.desribe,giftbag_name,giftbag_type,tab_game.icon,tab_giftbag.create_time',
                'join'	=>'tab_game on tab_giftbag.game_id = tab_game.id',
                'map'   =>array('game_status'=>1),
                //'order' =>'giftbag_name desc',
                'group' =>'tab_giftbag.game_id',
                'order' =>'tab_giftbag.id desc',
                'limit' =>10    ,
            );
            $gift = parent::join_data($model);

            $this->_cache->set($key, $gift);
        }
        $this->assign('gift',$gift);
        $this->display();
    }

    public function wapsearch(){
        $this->display();
    }
    public function search(){
        $keyword = I('get.keyword');
        if ($_GET['p']) {
            $p = $_GET['p'];
        }else{
            $p=1;
        }
        if ($_GET['pagesize']) {
            $row = $_GET['pagesize'];
        }else {
            $row = 20;
        }
        $map['game_status'] = 1;
        if($keyword){
            $map['game_name'] = array('like',"%".$keyword."%");
        }
        $game = M("Game","tab_");
        $listdata= $game->where($map)->order('sort ASC')->field("id,game_name,icon,game_size,game_type_name,introduction,dow_status")->select();
        foreach ($listdata as $key=>$value)
        {
            $listdata[$key]['icon']=get_cover($value['icon'],'path');
        }
       echo json_encode($listdata);exit;
    }

    private function _get_game_list() {
        //取所有在线游戏列表
        $key = "media_all_game_list";
        $game_list = $this->_cache->get($key);
        if(empty($game_list)) {
            $model = array(
                'm_name'=>'Game',
                'prefix'=>'tab_',
                'map'   =>array('game_status'=>1),
                'field' =>true,
                'order' =>'sort DESC'
            );
            $game_list = parent::list_data($model);
            $this->_cache->set($key, $game_list);
        }

        return $game_list;
    }
    private function get_game_by_type($game_list, $type_name, $type_value, $limit) {
        if(empty($game_list) || empty($limit)) {
            return array();
        }

        $result = array();

        foreach($game_list as $key => $value) {
            if($value[$type_name] == $type_value) {
                if(!$result[$value['id']] && count($result) < $limit) {
                    $result[$value['id']] = $value;
                }
            }
        }

        return $result;
    }
    public function wapgamelist(){
        $adv = M("Adv","tab_");
        $map['status'] = 1;
        $map['pos_id'] = array('in','8,9,10,11');
        $adv_top= $adv->where($map)->order('sort ASC')->select();
        foreach ($adv_top as $key=>$value)
        {
            /* 获取详细信息 */
            $game=M("Game","tab_")->where("game_name='".$value['title']."' and game_status=1")->order('sort desc')->field("id")->find();
            $adv_top[$key]['game_id']=$game['id'];
        }
        $this->assign("adv_top",$adv_top);
        $map['game_status'] = 1;
        if ($_GET['game_type']!=0) {
            $map['game_type_id'] = $_GET['game_type'];
        }
        else{
            $map['recommend_status']=1;
        }
       $data=M("Game","tab_")->where($map)->order("sort desc")->select();
        $this->assign("list_data",$data);
        $this->assign("game_type",$_GET['game_type']);
        $this->display();
    }

    public function wapgamedetail(){
        $this->meta_title = '游戏详情';
        $id=I('get.id');
        /* 获取详细信息 */
        $game = new GameModel();
        $game->detail();
        $info = $game->detail($id);
        if(!$info){
            $this->error($game->getError());
        }
      //  print_r($info);exit;
        $this->assign('vo', $info);
        $this->display();
    }

    public function change(){
        $len = count(get_game_type_all_show());
        $start = rand(0,$len);
        $map['game_status'] = 1;
        $data=M("Game","tab_")->where($map)->limit($start,5)->field("id,game_name,icon,game_size,game_type_name,introduction,dow_status")->select();
        foreach ($data as $key=>$value)
        {
            $data[$key]['icon']=get_cover($value['icon'],'path');
        }
       echo json_encode($data);exit;
    }
}