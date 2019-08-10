<?php

namespace Home\Controller;
use OT\DataDictionary;
use Admin\Model\ApplyModel;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class ApplyController extends BaseController {

    public function jion_list($model=array(),$p,$map = array()){

        $page = intval($p);
        $page = $page ? $page : 1; //默认显示第一页数据
        $name = $model['name'];
        $row    = empty($model['list_row']) ? 15 : $model['list_row'];
        $data = M($name,'tab_')
            /* 查询指定字段，不指定则查询所有字段 */
            ->field(empty($fields) ? true : $fields)
            ->join($model['jion'])
            // 查询条件
            ->where($map)
            /* 默认通过id逆序排列 */
            ->order($model['need_pk']?'id DESC':'')
            /* 数据分页 */
            ->page($page, $row)
            /* 执行查询 */
            ->select();

        /* 查询记录总数 */
        $count = M($name,"tab_")->where($map)->count();

        //分页
        if($count > $row){
            $page = new \Think\Page($count, $row);
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            $this->assign('_page', $page->show());
        }

        $this->assign('list_data', $data);
        $this->meta_title = $model['title'];
        $this->display($model['tem_list']);
    }
	//首页
    public function index($p = 0){
        if(isset($_REQUEST['game_name'])){
            //$map['tab_game.game_name']=trim($_REQUEST['game_name']);
            $map['tab_game.game_name'] = array('like','%'.$_REQUEST['game_name'].'%') ;
        }
        $page = intval($p);
        $page = $page ? $page : 1; //默认显示第一页数据
        $row    = 10;
        $map['game_status']=1;
        $data = M("game","tab_")
            /* 查询指定字段，不指定则查询所有字段 */
            ->field("tab_game.id,tab_game.game_name,icon,game_type_name,game_size,version,recommend_status,game_address,promote_id,status,dow_status")
            ->join("tab_apply ON tab_game.id = tab_apply.game_id and tab_apply.promote_id = ".get_pid(),"LEFT")
            // 查询条件
            ->where($map)
            /* 默认通过id逆序排列 */
            ->order("sort asc")
            /* 数据分页 */
            ->page($page, $row)
            /* 执行查询 */
            ->select();

        /* 查询记录总数 */
        $count = M("game","tab_")
            /* 查询指定字段，不指定则查询所有字段 */
            ->field("tab_game.id,game_name,icon,game_type_name,file_size,version,recommend_status,game_address,promote_id,status,dow_status")
            ->join("tab_apply ON tab_game.id = tab_apply.game_id and tab_apply.promote_id = ".get_pid(),"LEFT")
              ->where($map)
            ->count();

        //分页
        if($count > $row){
            $page = new \Think\Page($count, $row);
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            $this->assign('_page', $page->show());
        }
        $this->assign("count",$count);
        $this->assign('model', $model);
        $this->assign('list_data', $data);
        $this->display();
    }

    public function my_game($type=-1,$p=0){
        if(isset($_REQUEST['game_name'])){
            $map['tab_game.game_name']=array('like','%'.trim($_REQUEST['game_name']).'%');
            unset($_REQUEST['game_name']);
        }
        $map['promote_id'] = session("promote_auth.pid");
        if($type==-1){
            unset($map['status']);
        }else{
            $map['status'] =  $type;
        }
    	$page = intval($p);
        $page = $page ? $page : 1; //默认显示第一页数据
        $row    = 10;
        $data = M("game","tab_")
            /* 查询指定字段，不指定则查询所有字段 */
            ->field("tab_game.*,tab_apply.promote_id,tab_apply.status,tab_apply.pack_url,tab_apply.id as apply_id")
            ->join("tab_apply ON tab_game.id = tab_apply.game_id and tab_apply.promote_id = ".session('promote_auth.pid'))
            // 查询条件
            ->where($map)
            /* 默认通过id逆序排列 */
            ->order("sort asc")
            /* 数据分页 */
            ->page($page, $row)
            /* 执行查询 */
            ->select();

        /* 查询记录总数 */
        $count =  M("game","tab_")
            /* 查询指定字段，不指定则查询所有字段 */
            ->field("tab_game.*,tab_apply.promote_id,tab_apply.status,tab_apply.pack_url")
            ->join("tab_apply ON tab_game.id = tab_apply.game_id and tab_apply.promote_id = ".session('promote_auth.pid'))
            // 查询条件
            ->where($map)
            ->count();

        //分页
        if($count > $row){
            $page = new \Think\Page($count, $row);
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            $this->assign('_page', $page->show());
        }
        $url="http://".$_SERVER['HTTP_HOST'].__ROOT__."/media.php/member/preg/pid/".session("promote_auth.pid");

        foreach ($data as $key=>$value){
            $data[$key]['pack_url'] = substr($value['pack_url'],1);
        }
       // print_r($data);exit;
        $this->assign("url",$url);
        $this->assign("count",$count);
        $this->assign('model', $model);
        $this->assign('list_data', $data);
        $this->display();
    }
    /**
	申请游戏
    */
    public function apply(){
    	if(isset($_POST['game_id'])){
    		$model = new ApplyModel(); //D('Apply');
    		$data['game_id'] = $_POST['game_id'];
            $data['game_name'] = get_game_name($_POST['game_id']);
    		$data['promote_id'] = session("promote_auth.pid");
            $data['promote_account'] = session("promote_auth.account");
    		$data['apply_time'] = NOW_TIME;
    		$data['status'] = 0;
    		$data['enable_status'] = 1;
            $wherein['game_id']=$_POST['game_id'];
            $wherein['promote_id']=session("promote_auth.pid");
            $is=M('apply','tab_')->where($wherein)->select();
            if ($is) {
                $this->ajaxReturn(array("status"=>"-1","msg"=>"已申请"));
            } else {
                $res = $model->add($data);
                if($res){
                    $this->ajaxReturn(array("status"=>"1","msg"=>"申请成功"));
                }
                else{
                    $this->ajaxReturn(array("status"=>"0","msg"=>"申请失败"));
                }
            }
    		
    	}
    	else{
    		$this->ajaxReturn(array("status"=>"0","msg"=>"操作失败"));
    	}
    	

    }

    public function game_verify(){
        $map['parent_id'] = session("promote_auth.pid");
        $data =  M("Promote","tab_")->where($map)->select();
        $this->assign('list_data', $data);
        $this->display();
    }
    //查询子渠道
    public function promote_name(){

    }

    public function game_verify_edit(){
        $uc_auth_sign = 'oq0d^*AcXB$-2[]PkFaKY}eR(Hv+<?g~CImW>xyV';
        if(IS_POST){
            $promote = M('Promote','tab_');
            $promote->account = I("post.account");
            $promote->password = $this->think_ucenter_md5(I("post.password"),$uc_auth_sign);
            $promote->real_name = I("post.real_name");
            $promote->email = I("post.email");
            $promote->mobile_phone = I("post.mobile_phone");
            $promote->bank_name = I("post.bank_name");
            $promote->bank_card = I("post.bank_card");
            $promote->alipayway_sign = I("post.alipayway_sign");
            $promote->balance_coin = I("post.balance_coin");
            $promote->status = I("post.status");
            $map['id'] = I("post.id");
            $bool=$promote->where($map)->save();
            if($bool){
                echo "<meta charset='UTF-8'><script>alert('审核成功');location.href='/index.php?s=/Home/Apply/game_verify.html';</script>";
            }else{
                echo "<meta charset='UTF-8'><script>alert('审核失败');window.history.back();</script>";
            }
        }else{
            $map['id'] = $_GET['id'];
            $data =  M("Promote","tab_")->where($map)->find();
            $this->assign('list_data', $data);
            $this->display();
        }
    }
    public function childlists(){
        $row=10;
        $page = intval($_GET['p']);
        $page = $page ? $page : 1; //默认显示第一页数据
        $map["tab_promote.parent_id"]=session('promote_auth.pid');
        if(isset($_REQUEST['game_name'])){
            $map['game_name']=array('like','%'.$_REQUEST['game_name'].'%');
            unset($_REQUEST['game_name']);
        }
        $model=M('apply','tab_');
        $data=$model
            ->field('tab_apply.id,tab_apply.game_name,tab_apply.promote_account,tab_apply.apply_time,tab_apply.status,tab_apply.enable_status,tab_apply.dispose_time')
            ->join("tab_promote ON tab_promote.id = tab_apply.promote_id  ")
            ->where($map)
            ->order('id desc')
            ->page($page, $row)
            ->select();
        $count=$model
            ->field('tab_apply.id,tab_apply.game_name,tab_apply.promote_account,tab_apply.apply_time,tab_apply.status,tab_apply.enable_status,tab_apply.dispose_time')
            ->join("tab_promote ON tab_promote.id = tab_apply.promote_id  ")
            ->where($map)
            ->count();
        if($count > $row){
            $page = new \Think\Page($count, $row);
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            $this->assign('_page', $page->show());
        }
        $this->assign('list_data', $data);
        $this->display();
    }
    public function apply_edit($id=null){
        $id || $this->error('请选择要编辑的用户！');
        //查看审核状态是否改变且为1
        $whereapp['id']=$id;
        $appdata = M('apply','tab_')->where($whereapp)->find();
        //print_r($appdata);exit;
        if(isset($_POST['apply_id'])) {
            $dis_data = I('post.');
           // print_r($dis_data);exit;
            $whereapp['id']=$dis_data['apply_id'];
            $appdata = M('apply','tab_')->where($whereapp)->find();
            $applydata['status']=$dis_data['status'];
            $applydata['enable_status']=$dis_data['enable_status'];
            $applydata['ratio']=$dis_data['ratio'];
            $as=M('apply','tab_')->where($whereapp)->save($applydata);
            if ( $dis_data['status'] == 1) {
                 $wheredis['promote_id'] = $dis_data['dis_promoteid'];
                    $wheredis['game_id'] = $dis_data['dis_gameid'];
                    $is = M('charge', 'tab_')->where($wheredis)->find();
                    if (empty($is)) {
                         $bind_discount = get_bind_discount($dis_data['dis_gameid']);
                       // echo $bind_discount;exit;
                        if ((!empty($bind_discount)) && $bind_discount >= 3) {
                            $add['promote_id'] = $dis_data['dis_promoteid'];
                            $add['promote_name'] = get_promote_name($dis_data['dis_promoteid']);
                            $add['game_id'] = $dis_data['dis_gameid'];
                            $add['game_name'] = get_game_name($dis_data['dis_gameid']);
                            $add['discount'] = $bind_discount;
                            $add['create_time'] = time();
                            $result = M('charge', 'tab_')->add($add);
                            if ($result === false) {
                                $this->error('渠道折扣添加失败！！');
                            }
                        }

                    }

            }
            echo "<meta charset='UTF-8'><script>alert('审核成功');location.href='index.php?s=/Home/Apply/childlists.html'</script>";
        }
        $this->assign('data', $appdata);
        $this->display();
    }
    public function think_ucenter_md5($str, $key = 'ThinkUCenter'){
        return '' === $str ? '' : md5(sha1($str) . $key);
    }

    public function game_source($game_id,$type){
        $model = M('game_source',"tab_");;
        $map['game_id'] = $game_id;
        $map['type'] = $type;
        $data = $model->where($map)->find();
        return $data;
    }
    //$file1原包   $file2分包
    function copyfiles($file1,$file2){
        $contentx =@file_get_contents($file1);  //读取原包数据
        $openedfile = fopen($file2, "w");  //打开创建分包，
        fwrite($openedfile, $contentx);  //分包写入原包数据
        fclose($openedfile);  //关闭分包
        if ($contentx === FALSE) {
            $status=false;
        }else{
            $status=true;
        };
        return $status;
    }
    /**
     *上传到OSS
     */
    public function upload_game_pak_oss($return_data=null){
        /**
         * 根据Config配置，得到一个OssClient实例
         */
        try {
            Vendor('OSS.autoload');
            $ossClient = new \OSS\OssClient(C("oss_storage.accesskeyid"), C("oss_storage.accesskeysecr"), C("oss_storage.domain"));
        } catch (OssException $e) {
            $this->error($e->getMessage());
        }

        $bucket = C('oss_storage.bucket');
        $oss_file_path ="GamePak/". $return_data["savename"];
        $avatar = $return_data["path"];
        try {
            $this->multiuploadFile($ossClient,$bucket,$oss_file_path,$avatar);
            return true;
        } catch (OssException $e) {
            /* 返回JSON数据 */
            $this->error($e->getMessage());
        }
    }

    /**
     *修改申请信息
     */
    public function updateinfo($id,$pack_url,$promote,$type){
        $model = M('Apply',"tab_");
        $data['id'] = $id;
        $data['pack_url'] = $pack_url;
        $data['dow_url']  = '/index.php?s=/Home/Down/down_file/game_id/' . $promote['game_id'] . '/promote_id/' . $promote['promote_id'];
        if($type!=1) {
            $data['dispose_id'] = UID;
        }
        $data['dispose_time'] = NOW_TIME;
        $res = $model->save($data);
        return $res;
    }
    // 获取游戏appid
    function get_game_appid($game_name=null,$field='game_name'){
        $map[$field]=$game_name;
        $data=M('Game','tab_')->where($map)->find();
        if(empty($data)){return false;}
        return $data['game_appid'];
    }
    //返回扩展工具开启状态
    function get_tool_status($name){
        $map['name']=$name;
        $tool=M("tool","tab_")->where($map)->find();
        return $tool['status'];
    }
    //打包
    public function package($ids=null) //3311
    {
        header("Content-Type:text/html;charset=utf-8");
        try{
            $ids || $this->error("打包数据不存在");
            $whereapply['id'] = $ids;
            $apply_data = M('Apply',"tab_")->where($whereapply)->find();
            //验证数据正确性
            if(empty($apply_data) || $apply_data["status"] != 1){$this->error("未审核或数据错误"); exit();}
            #获取原包数据
            $source_file = $this->game_source($apply_data["game_id"],1);
            //验证原包是否存在
            if(empty($source_file) || !file_exists($source_file['file_url'])){
                $this->error("游戏原包不存在");
                exit();
            }
            //$files   = $_SERVER['DOCUMENT_ROOT'].$source_file['file_url'];
            $newname = "game_package" . $apply_data["game_id"] . "-" . $apply_data['promote_id'] . ".apk";
            //打包新路径
            $to = "./Uploads/GamePack/".$newname;
            $this->copyfiles($source_file['file_url'],$to);  //生成分包文件
            $zip = new \ZipArchive;
            $res = $zip->open($to, \ZipArchive::CREATE);//
            if ($res === TRUE) {
                $pack_data = array(
                    "game_id"    => $source_file["game_id"],
                    "game_name"  => $source_file['game_name'],
                    "game_appid" => $this->get_game_appid($source_file["game_id"],"id"),
                    "promote_id" => $apply_data['promote_id'],
                    "promote_account" => $apply_data["promote_account"],
                );
                $zip->addFromString('META-INF/wz.properties', json_encode($pack_data));
                $zip->close();
                $source  = $source_file['file_url'];
                switch ($this->get_tool_status("oss_storage")) {
                    case 0://服务器
                        $promote = array('game_id'=>$apply_data['game_id'],'promote_id'=>$apply_data['promote_id']);
                        break;
                    case 1: //OSS
                        $newname = "game_package" . $apply_data["game_id"] . "-" . $apply_data['promote_id'] . ".apk";
                        $to = "http://".C("oss_storage.bucket") . "." . C("oss_storage.domain") . "/GamePak/" . $newname;
                        $updata['savename'] = $newname;
                        $updata['path'] = $source;
                        $promote = array('game_id'=>$apply_data['game_id'],'promote_id'=>$apply_data['promote_id']);
                        $this->upload_game_pak_oss($updata);
                        break;
                }
                $jieguo = $this->updateinfo($ids,$to,$apply_data);
                if($jieguo){
                    $this->success("成功");
                }
                else{
                    $this->error("操作失败");
                }
            } else {
                throw new \Exception('分包失败');
            }
        }
        catch(\Exception $e){
            $this->error($e->getMessage());
        }
    }
}