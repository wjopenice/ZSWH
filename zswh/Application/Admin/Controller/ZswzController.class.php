<?php
// +----------------------------------------------------------------------
// | Author: openice <a756412465@163.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use User\Api\MemberApi as MemberApi;
/**
 * 玩转实时数据同步
 * @author openice <a756412465@163.com>
 */
class ZswzController extends ThinkController {

    const UC_DB_DSN = 'mysql://root:12345678@47.103.50.184:3306/u7858#utf8';

    //玩转玩家注册日志
    public function register($p=0){
        $map = array();
        if(isset($_REQUEST['promote_id'])){
            if($_REQUEST['promote_id']=='全部'){
                #unset($_REQUEST['promote_name']);
            }else if($_REQUEST['promote_id']=='自然注册'){
                $map['promote_id']=array("elt",0);
                #unset($_REQUEST['promote_name']);
            }else{
                $map['promote_id']=$_REQUEST['promote_id'];
                # unset($_REQUEST['promote_name']);
            }
        }
        if(isset($_REQUEST['account'])){
            $map['tab_user.account'] = array('like','%'.$_REQUEST['account'].'%');
            #unset($_REQUEST['account']);
        }
        if(isset($_REQUEST['game_id'])){
            $map['tab_game.id'] = $_REQUEST['game_id'];
            #unset($_REQUEST['game_id']);
        }
        if(isset($_REQUEST['register_way'])){
            $map['register_way'] = $_REQUEST['register_way'];
            #unset($_REQUEST['register_way']);
        }
        if(isset($_REQUEST['time-start']) && isset($_REQUEST['time-end'])){
            $map['register_time'] =array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])+24*60*60-1));
            #unset($_REQUEST['time-start']);unset($_REQUEST['time-end']);
        }
        if(isset($_REQUEST['start']) && isset($_REQUEST['end'])){
            $map['register_time'] = array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])+24*60*60-1));
            #unset($_REQUEST['start']);unset($_REQUEST['end']);
        }
        if(isset($_REQUEST['lock_status'])){
            $map['lock_status']=$_REQUEST['lock_status'];
            unset($_REQUEST['lock_status']);
        }
        $extend=array();
        $extend['map']=$map;
        $this->lists('user',$p,$extend['map']);
    }
    //玩转玩家登录日志
    public function login($p=1){
        if(isset($_REQUEST['game_name'])){
            $map['game_id']=get_game_id($_REQUEST['game_name']);
            unset($_REQUEST['game_name']);
        }
        if(isset($_REQUEST['time-start'])&&isset($_REQUEST['time-end'])){
            $map['login_time'] =array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])+24*60*60-1));
            unset($_REQUEST['time-start']);unset($_REQUEST['time-end']);
        }
        if(isset($_REQUEST['start'])&&isset($_REQUEST['end'])){
            $map['login_time'] =array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])+24*60*60-1));
            unset($_REQUEST['start']);unset($_REQUEST['end']);
        }
        if(isset($_REQUEST['account'])){
            $map['user_account']=array('like','%'.$_REQUEST['account'].'%');
            unset($_REQUEST['account']);
        }
        $extend=array();
        $extend['map']=$map;
        $this->lists('UserLoginRecord',$p,$extend['map']);
    }
    //玩转玩家充值日志
    public function recharge(){
        if(isset($_REQUEST['user_account'])){
            $map['user_account']=array('like','%'.$_REQUEST['user_account'].'%');
            unset($_REQUEST['user_account']);
        }
        if(isset($_REQUEST['promote_id'])){
            if($_REQUEST['promote_id']=='全部'){
                unset($_REQUEST['promote_id']);
            }else if($_REQUEST['promote_id']=='自然注册'){
                $map['promote_id']=array("elt",0);
                #unset($_REQUEST['promote_name']);
            }else{
                $map['promote_id']=$_REQUEST['promote_id'];
                # unset($_REQUEST['promote_name']);
            }
        }

        if(isset($_REQUEST['time-start'])&&isset($_REQUEST['time-end'])){
            $map['create_time'] =array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])+24*60*60-1));
            unset($_REQUEST['time-start']);unset($_REQUEST['time-end']);
        }
        if(isset($_REQUEST['start'])&&isset($_REQUEST['end'])){
            $map['create_time'] =array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])+24*60*60-1));
            unset($_REQUEST['start']);unset($_REQUEST['end']);
        }
        if(isset($_REQUEST['pay_way'])){
            $map['pay_way']=$_REQUEST['pay_way'];
            unset($$_REQUEST['pay_way']);
        }
        if(isset($_REQUEST['pay_status'])){
            $map['pay_status']=$_REQUEST['pay_status'];
            unset($$_REQUEST['pay_status']);
        }
        $map1=$map;
        $map1['pay_status']=1;
        $total=M("Deposit","tab_",self::UC_DB_DSN)->where($map1)->sum('pay_amount');
        if(isset($map['pay_status'])&&$map['pay_status']==0){
            $total=sprintf("%.2f",0);
        }else{
            $total=sprintf("%.2f",$total);
        }
        $this->assign('total',$total);
        $this->lists("Deposit",$_GET["p"],$map);
    }
    //玩转玩家消费日志
    public function spend(){
        if(isset($_REQUEST['user_account'])){
            $map['user_account']=array('like','%'.$_REQUEST['user_account'].'%');
            unset($_REQUEST['user_account']);
        }
        if(isset($_REQUEST['time-start'])&&isset($_REQUEST['time-end'])){
            $map['pay_time'] =array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])));
            unset($_REQUEST['time-start']);unset($_REQUEST['time-end']);
        }
        if(isset($_REQUEST['start'])&&isset($_REQUEST['end'])){
            $map['pay_time'] =array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])));
            unset($_REQUEST['start']);unset($_REQUEST['end']);
        }
        if(isset($_REQUEST['cp_name'])){
            if($_REQUEST['cp_name'] != '全部'){
                $where_cp_name['cp_name']=I('cp_name');
                $game_id_arr = M('Game','tab_',self::UC_DB_DSN)->where($where_cp_name)->getField('id',true);
                $map['game_id'] = array('in',$game_id_arr);
            }
        }
        if(isset($_REQUEST['game_id'])){
            if($_REQUEST['game_id']=='全部'){
                unset($_REQUEST['game_id']);
            }else{
                $map['game_id']=$_REQUEST['game_id'];
                unset($_REQUEST['game_id']);
            }
        }

        if(isset($_REQUEST['pay_order_number'])){
            $map['pay_order_number']=array('like','%'.$_REQUEST['pay_order_number'].'%');
            unset($_REQUEST['pay_order_number']);
        }
        if(isset($_REQUEST['pay_status'])){
            $map['pay_status']=$_REQUEST['pay_status'];
            unset($_REQUEST['pay_status']);
        }
        if(isset($_REQUEST['pay_game_status'])){
            $map['pay_game_status']=$_REQUEST['pay_game_status'];
            unset($_REQUEST['pay_game_status']);
        }
        if(isset($_REQUEST['pay_way'])){
            $map['pay_way']=$_REQUEST['pay_way'];
            unset($_REQUEST['pay_status']);
        }
        $map1=$map;
        $map1['pay_status']=1;
        $total=M("Spend","tab_",self::UC_DB_DSN)->where($map1)->sum('pay_amount');
        if(isset($map['pay_status'])&&$map['pay_status']==0){
            $total=sprintf("%.2f",0);
        }else{
            $total=sprintf("%.2f",$total);
        }
        $this->assign('total',$total);
        $map['order']='pay_time DESC';
        $this->lists("Spend",$_GET["p"],$map);
    }
    //玩转渠道充值日志
    public function promotechange(){
        $uid=$_SESSION['onethink_admin']['user_auth']['uid'];
        $auth_group_id=M('auth_group_access')->where('uid='.$uid)->getField('group_id');

        if($auth_group_id!=1)
        {
            $admin_ids=M('promote','tab_',self::UC_DB_DSN)->where('admin_id='.$uid)->select();
            $admin_id='';
            foreach ($admin_ids as $value)
            {
                $admin_id=$admin_id.$value['id'].",";
            }
            $admin_id=substr($admin_id,0,-1);
            if(!empty($admin_id)) {
                $map['user_id'] = array('in', $admin_id);
                $this->assign('admin_id','1');
            }
            else{
                $this->assign('admin_id','0');
            }
        }
        else{
            $this->assign('admin_id','1');
        }


        if(isset($_REQUEST['game_name'])){
            if($_REQUEST['game_name']=='全部'){
                unset($_REQUEST['game_name']);
            }else{
                $map['game_name']=$_REQUEST['game_name'];
                unset($_REQUEST['game_name']);
            }
        }
        if(isset($_REQUEST['promote_account'])){
            $map['user_account']=array('like','%'.$_REQUEST['promote_account'].'%');
            unset($_REQUEST['promote_account']);
        }
        if(isset($_REQUEST['time-start'])&&isset($_REQUEST['time-end'])){
            $map['create_time'] =array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])+24*60*60-1));
            unset($_REQUEST['time-start']);unset($_REQUEST['time-end']);
        }

        if(isset($_REQUEST['start']) && isset($_REQUEST['end'])){
            $map['create_time'] = array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])+24*60*60-1));
            #unset($_REQUEST['start']);unset($_REQUEST['end']);
        }
        $total=M("bang_propay",'tab_',self::UC_DB_DSN)->where($map)->sum('amount');
        if(!empty($admin_id)||$auth_group_id==1) {
            $total = sprintf("%.2f", $total);
        }
        else{
            $total = sprintf("%.2f", 0);
        }
        $this->assign('total',$total);
        $this->lists("bang_propay",$_GET["p"],$map);
    }

    public function lists($model = null, $p = 0,$extend_map = array()){
        $model || $this->error('模型名标识必须！');
        $page = intval($p);
        $page = $page ? $page : 1; //默认显示第一页数据
        //获取模型信息
        //$model = M($model,'tab_',self::UC_DB_DSN);
        $model = M("Model")->getByName($model);
        $model || $this->error('模型不存在！');
        //解析列表规则
        $fields = array();
        $grids  = preg_split('/[;\r\n]+/s', trim($model['list_grid']));
        foreach ($grids as &$value) {
            if(trim($value) === ''){
                continue;
            }
            // 字段:标题:链接
            $val      = explode(':', $value);
            // 支持多个字段显示
            $field   = explode(',', $val[0]);
            $value    = array('field' => $field, 'title' => $val[1]);
            if(isset($val[2])){
                // 链接信息
                $value['href']	=	$val[2];
                // 搜索链接信息中的字段信息
                preg_replace_callback('/\[([a-z_]+)\]/', function($match) use(&$fields){$fields[]=$match[1];}, $value['href']);
            }
            if(strpos($val[1],'|')){
                // 显示格式定义
                list($value['title'],$value['format'])    =   explode('|',$val[1]);
            }
            foreach($field as $val){
                $array	=	explode('|',$val);
                $fields[] = $array[0];
            }
        }
        // 过滤重复字段信息
        $fields =   array_unique($fields);
        // 关键字搜索
        $map	=	$extend_map;
        $key	=	$model['search_key']?$model['search_key']:'title';
        if(isset($_REQUEST[$key])){
            $map[$key]	=	array('like','%'.$_GET[$key].'%');
            unset($_REQUEST[$key]);
        }
        // 条件搜索
        foreach($_REQUEST as $name=>$val){
            if(in_array($name,$fields)){
                $map[$name]	=	$val;
            }
        }
        $row    = empty($model['list_row']) ? 15 : $model['list_row'];

        //读取模型数据列表
        if($model['extend']){
            $name   = get_table_name($model['id']);
            $parent = get_table_name($model['extend']);
            $fix    = "tab_";
            $key = array_search('id', $fields);
            if(false === $key){
                array_push($fields, "{$fix}{$parent}.id as id");
            } else {
                $fields[$key] = "{$fix}{$parent}.id as id";
            }
            /* 查询记录数 */
            $count = M($parent,"tab_",self::UC_DB_DSN)->join("INNER JOIN {$fix}{$name} ON {$fix}{$parent}.id = {$fix}{$name}.id")->where($map)->count();
            // 查询数据
            $data   = M($parent,"tab_",self::UC_DB_DSN)
                ->join("INNER JOIN {$fix}{$name} ON {$fix}{$parent}.id = {$fix}{$name}.id")
                /* 查询指定字段，不指定则查询所有字段 */
                ->field(empty($fields) ? true : $fields)
                // 查询条件
                ->where($map)
                /* 默认通过id逆序排列 */
                ->order("{$fix}{$parent}.id DESC")
                /* 数据分页 */
                ->page($page, $row)
                /* 执行查询 */
                ->select();
        } else {
            if($model['need_pk']){
                in_array('id', $fields) || array_push($fields, 'id');
            }
            $name = parse_name(get_table_name($model['id']), true);
            $data = M($name,"tab_",self::UC_DB_DSN)
                /* 查询指定字段，不指定则查询所有字段 */
                ->field(empty($fields) ? true : $fields)
                // 查询条件
                ->where($map)
                /* 默认通过id逆序排列 */
                ->order(empty($map['order'])?"id desc":$map['order'])
                /* 数据分页 */
                ->page($page, $row)
                /* 执行查询 */
                ->select();
            /* 查询记录总数 */
            $count = M($name,"tab_",self::UC_DB_DSN)->where($map)->count();
        }
        //分页
        if($count > $row){
            $page = new \Think\Page($count, $row);
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            $this->assign('_page', $page->show());
        }
        $data   =   $this->parseDocumentList($data,$model['id']);
        $this->assign('model', $model);
        $this->assign('list_grids', $grids);
        $this->assign('list_data', $data);
        $this->meta_title = $model['title'].'列表';
        $this->display($model['template_list']);
    }
}