<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<!-- saved from url=(0028)http://tui.anfeng.com/users/ -->
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?php echo ($meta_title); ?>-个人中心</title>
    <link href="/Public/Home/css/p_admin.css" rel="stylesheet" type="text/css">
    <link rel="icon" href="/Public/Media/image/s_logo.png"/>
    <script type="text/javascript" src="/Public/Home/js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="/Public/static/layer/layer.js" ></script>
    <script type="text/javascript" src="/Public/Home/js/common.js"></script>
     <!--[if lt IE 9]>
    <script type="text/javascript" src="/Public/static/jquery-1.10.2.min.js"></script>
    <![endif]--><!--[if gte IE 9]><!-->
    <?php echo hook('pageHeader');?>
</head>

<body id="member">

<!--头部个人信息-->
<div id="top_bar">
  <div id="top_bar_box" class="wrap_w clearfix">
    <div id="l"><a href="#">合作中心</a></div>
    <div id="r"><i><?php echo '今天是:'.date('Y',time()).'年-'.date('m',time()).'月-'.date('d',time()).'日';?></i><span>你好：<?php echo session('promote_auth.account');?></span><a href="<?php echo U('Public/logout');?>" >退出</a>

	</div>
  </div>
</div>
<!--结束 头部个人信息-->

<div class="page_main wrap_w clearfix">
  <div class="page_siderbar">
    <!--左边导航-->
    <div id="subnav" class="user_menu">
      <ul>
        <li>
          <h3><span class="ficon ficon-home"></span>管理中心</h3>
          <p><a href="<?php echo U('Promote/index');?>">后台首页</a></p>
        </li>
        <li>
            <h3><span class="ficon ficon-game"></span>游戏管理</h3>
            <p><a href="<?php echo U('Apply/index');?>">申请游戏</a></p>
            <p><a href="<?php echo U('Apply/my_game');?>">我的游戏</a></p>

        </li>
        <!--<li>
              <h3><span class="ficon ficon-pay"></span>充值管理</h3>
              <p><a href="<?php echo U('Promote/alipay');?>">支付宝充值</a></p>
              <p><a href="<?php echo U('Promote/alipay_list');?>">支付宝充值平台币明细</a></p>

          </li>-->
        <li>
            <h3><span class="ficon ficon-act"></span>代充管理</h3>
             <!--<p><a href="<?php echo U('Charge/agent_pay');?>">会长代充</a></p>
             <p><a href="<?php echo U('Charge/agent_pay_list');?>">代充汇总</a></p>-->
        <?php if($parent_id == 0): ?><!-- <p><a href="<?php echo U('Charge/agency');?>">转移平台币</a></p>
             <p><a href="<?php echo U('Charge/agency_list');?>">转移记录</a></p>--><?php endif; ?>
	           <p><a href="<?php echo U('Charge/agency');?>">转移平台币</a></p>
             <p><a href="<?php echo U('Charge/agency_list');?>">转移平台币记录</a></p>
             <p><a href="<?php echo U('Charge/agency_bang');?>">转移绑定平台币</a></p>
             <p><a href="<?php echo U('Charge/agency_bang_list');?>">转移绑定平台币记录</a></p>
             <p><a href="<?php echo U('Charge/promote_game_list');?>">游戏绑定平台币余额记录</a></p>
            <!-- <p><a href="<?php echo U('Charge/agent_list');?>">消费平台币</a></p>
            <p><a href="<?php echo U('Charge/fill_list');?>">申请额度</a></p>
            <p><a href="<?php echo U('Charge/transfer_list');?>">平台币交易转移</a></p> -->
        </li>
        <li>
            <h3><span class="ficon ficon-docs"></span>数据管理</h3>
            <p><a href="<?php echo U('Query/recharge');?>">充值明细</a></p>
            <p><a href="<?php echo U('Query/bindrecharge');?>">绑币充值明细</a></p>
            <p><a href="<?php echo U('Query/register');?>">注册明细</a></p>
        </li>
        <li>
            <h3><span class="ficon ficon-star"></span>财务管理</h3>
            <p><a href="<?php echo U('Query/my_earning');?>">我的结算</a></p>
            <p><a href="<?php echo U('Query/bill');?>">账单查询</a></p>
        </li>
        <li>
            <h3><span class="ficon ficon-person"></span>账户管理</h3>
            <p><a href="<?php echo U('Promote/base_info');?>">我的基本信息</a></p>
            <?php if(PARENT_ID == 0): ?><p id="mychlid"><a href="<?php echo U('Promote/mychlid');?>">子帐号管理</a></p><?php endif; ?>
        </li>
      </ul>
    </div>
    <!--结束 左边导航-->
  </div>

  <div class="page_content">
    <div id="container">
        
    <script src="/Public/static/layer/layer.js" type="text/javascript"></script>
    <script src='/Public/static/zeroclipboard/jquery.zclip.min.js'></script>
      <div id="query">
        <div id="other_se" class="mod">
          <h2>查询</h2>
          <form action="<?php echo U('Apply/my_game');?>" method="post" enctype="multipart/form-data">
            <div>
              <input type="text" placeholder="请输入游戏名称" class="txts" name="game_name" value="<?php echo I('game_name');?>">
              <input type="submit" value="查询" class="btns">
            </div>
          </form>
        </div>
        <div id="lg" class="mod">
          <h2>游戏列表</h2>
          <div class="tabs">
           <ul class="tab_nav tabNavigation clearfix">
              <li><a href="<?php echo U('Apply/my_game?type=-1');?>" class="on" id="all">全部</a></li>
              <li><a href="<?php echo U('Apply/my_game?type=1');?>"  id="audit">已审核</a></li>
              <li><a href="<?php echo U('Apply/my_game?type=0');?>"  id="wait">待审核</a></li>
              <!-- <li><a href="<?php echo U('Apply/mygameno');?>">停止投放</a></li> -->
            </ul>
            <div id="tabcon01" class="tabcon" style="display: block;">
              <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                  <tr>
                    <th>应用名称</th>
                    <th>最新版本号</th>
                    <th>类型</th>
                    <th>应用大小</th>
                    <th>下载</th>
                  </tr>
  				        <?php if(is_array($list_data)): $i = 0; $__LIST__ = $list_data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                  <td>
                    <a href="javascript:;">
                      <img src="<?php echo (get_cover($vo["icon"],'path')); ?>" width="50" height="50" align="middle">
                      <span class="name_a"><?php echo ($vo["game_name"]); ?></span>
                    </a>
                  </td>
                  <td><?php echo ($vo["version"]); ?></td>
                  <td><?php echo ($vo['game_type_name']); ?></td>
                  <td><?php echo ($vo["game_size"]); ?></td>
                  <td style="position: relative;">
                    <?php switch($vo["status"]): case "0": ?><a href="javascript:;" class="bts proc">审核中</a><?php break;?>
                      <?php case "1": $url = substr($vo['pack_url'],1); ?>
                        <a href="http://www.zhishengwh.com<?php echo ($vo["pack_url"]); ?>" class="bts" >下载</a>
                        <a href="javascript:;" class="bts copy" data-url="http://www.zhishengwh.com<?php echo ($vo["pack_url"]); ?>" >
                          复制下载地址
                        </a><?php break;?>
                      <?php case "2": ?><a href="javascript:;" class="bts proc">审核失败</a><?php break; endswitch;?>
                  </td>
                  </tr><?php endforeach; endif; else: echo "" ;endif; ?>                              
                </tbody>
              </table>
              <div class="import">
                <span>数据量：<?php echo ($count); ?>条数据</span><span>
                <a href="<?php echo U('Export/expUser',array('id'=>1,'expid'=>$expid));?>" >导出数据(excel格式)</a></span>
              </div>
            </div>
            <!-- <div id="pagehtml" class="pagenavi clearfix"><?php echo ($page); ?></div> -->
          </div>
        </div>
         <div id="pagehtml" class="pagenavi clearfix"><?php echo ($_page); ?></div>
      </div>

    </div>
  </div>
</div>
<!--底部信息-->
<div class="copyright">
  <div class="links"><a href="<?php echo U('Article/detail',array('id'=>32));?>">关于我们</a>|<a href="<?php echo U('Article/lists',array('category'=>tui_gg));?>">游戏公告</a>|<a href="<?php echo U('Article/lists',array('category'=>tui_zx));?>">游戏资讯</a></div>
  <div class="kf">
    <span>
        <span>客服电话：<?php echo C("MT_SITE_T2");?></span>
        <span>客服邮箱：<?php echo C("MT_SITE_E2");?></span>
        <span>服务时间：<?php echo C("MT_SITE_TIME2");?></span>
    </span>
  </div>
  <p>网络备案:<?php echo C('WEB_SITE_ICP2');?>&nbsp;&nbsp;网络文化经营许可证编号：<?php echo C('MT_SITE_LICENSE2');?>
                 版权所有:<?php echo C('MT_SITE_B2');?></p>
</div>
<!--结束 底部信息-->
</body>
</html>
<script type="text/javascript">
  var $window = $(window), $subnav = $("#subnav"), url;
  /* 左边菜单高亮 */
  url = window.location.pathname + window.location.search;
  url = url.replace(/(\/(p)\/\d+)|(&p=\d+)|(\/(id)\/\d+)|(&id=\d+)|(\/(group)\/\d+)|(&group=\d+)/, "");
  $subnav.find("a[href='" + url + "']").parent().addClass("cur");
  //导航高亮
  // function highlight_subnav(url){
  //   alert(url);
  //   $('.user_menu').find('a[href="'+url+'"]').closest('li').addClass('cur');
  // }
 if($('#data_form').length>0){
   $("#pagehtml a").on("click",function(event){
    event.preventDefault();//使a自带的方法失效，即无法调整到href中的URL(http://www.baidu.com)
    var geturl = $(this).attr('href');
    $('#data_form').attr('action',geturl);
    $('#data_form').submit();

})
};
</script>

<script type="text/javascript">


$(function(){
  //导航高亮
  highlight_subnav("<?php echo U('Apply/my_game');?>");

  var type = "<?php echo I('type',-1);?>";

  switch(type){
    case "-1":
      $("#all").addClass("on");
      $("#audit").removeClass("on");
      $("#wait").removeClass("on");
    break;
    case "1":
      $("#all").removeClass("on");
      $("#audit").addClass("on");
      $("#wait").removeClass("on");
    break;
    case "0":
      $("#all").removeClass("on");
      $("#audit").removeClass("on");
      $("#wait").addClass("on");
    break;
  }

  $('a.copy').zclip({
    path: "/Public/static/zeroclipboard/ZeroClipboard.swf",
    copy: function(){
        return $(this).attr('data-url');
    },
    beforeCopy:function(){
      $(this).addClass("proc");
    },
    afterCopy:function(){
      $(this).removeClass("copy");
      if($(this).attr('data-url')=="" || $(this).attr('data-url')==null){
        layer.msg("游戏原包暂未上传",{icon:2});
      }
      else{
        layer.msg("复制成功",{icon:1});
      }
      
    }
  });
})
</script>