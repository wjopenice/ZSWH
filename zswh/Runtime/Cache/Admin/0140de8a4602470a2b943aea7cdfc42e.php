<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo ($meta_title); ?>-<?php echo C('WEB_SITE_TITLE');?></title>
    <!-- <link href="/Public/favicon.ico" type="image/x-icon" rel="shortcut icon"> -->
    <link href="<?php echo get_cover(C('SITE_ICO'),'path');?>" type="image/x-icon" rel="shortcut icon">
    <link rel="stylesheet" type="text/css" href="/Public/Admin/css/base.css" media="all">
    <link rel="stylesheet" type="text/css" href="/Public/Admin/css/common.css" media="all">
    <link rel="stylesheet" type="text/css" href="/Public/Admin/css/module.css">
    <link rel="stylesheet" type="text/css" href="/Public/Admin/css/style.css" media="all">
    <link rel="stylesheet" type="text/css" href="/Public/Admin/css/<?php echo (C("COLOR_STYLE")); ?>.css" media="all">
     <!--[if lt IE 9]>
    <script type="text/javascript" src="/Public/static/jquery-1.10.2.min.js"></script>
    <![endif]--><!--[if gte IE 9]><!-->
    <script type="text/javascript" src="/Public/static/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="/Public/Admin/js/jquery.mousewheel.js"></script>
     <script>
        var _hmt = _hmt || [];
        (function() {
          var hm = document.createElement("script");
          hm.src = "https://hm.baidu.com/hm.js?bc19aa51515f215def6b091f540c83ea";
          var s = document.getElementsByTagName("script")[0];
          s.parentNode.insertBefore(hm, s);
        })();
    </script>
    <!--<![endif]-->
    
</head>
<body>
    <!-- 头部 -->
    <div class="header">
        <!-- Logo -->
        <span class="logo" ><img src="<?php echo get_cover(C('HT_LOGO'),'path');?>" width="100%" height="100%" style="width: 150px;height: 40px;padding-top: 5px;" /></span>
        <!-- /Logo -->

        <!-- 主导航 -->
        <ul class="main-nav">
            <?php if(is_array($__MENU__["main"])): $key = 0; $__LIST__ = $__MENU__["main"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu): $mod = ($key % 2 );++$key;?><li class="<?php echo ((isset($menu["class"]) && ($menu["class"] !== ""))?($menu["class"]):''); ?>"><a href="<?php echo (U($menu["url"])); ?>"><i class="menu_<?php echo ($key); ?>"></i><?php echo ($menu["title"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
        <!-- /主导航 -->

        <!-- 用户栏 -->
        <div class="user-bar">
            <span style="display:block;float:left;margin:0 10px;color:#fff;">你好，<em title="<?php echo session('user_auth.username');?>"><?php echo session('user_auth.username');?></em></span>
            <a href="javascript:;" style="float:left;" class="user-entrance"><i class="icon-user"></i></a>
            <ul class="nav-list user-menu hidden">
                <li><i  class="man_modify"></i><a href="/media.php" target="_blank">网站首页</a></li>
                <li><i  class="man_modify"></i><a href="<?php echo U('User/updatePassword');?>">修改密码</a></li>
                <li><i  class="man_quit"></i><a href="<?php echo U('Public/logout');?>">退出</a></li>
            </ul>
        </div>
    </div>
    <!-- /头部 -->
    <!-- 边栏 -->
    <div class="sidebar">
        <div class="user_nav">
           <span><img src="/Public/Admin/images/tx.jpg"></span>
           <p><?php echo session('user_auth.username');?></p>
           <p style="margin-top:0px;"><!-- 管理员 --><?php echo ($quanxian); ?></p>
        </div>
        <div  class="fgx">功能菜单</div>
        <!-- 子导航 -->
        
            <div id="subnav" class="subnav">
                <?php if(!empty($_extra_menu)): ?>
                    <?php echo extra_menu($_extra_menu,$__MENU__); endif; ?>
                <?php if(is_array($__MENU__["child"])): $i = 0; $__LIST__ = $__MENU__["child"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sub_menu): $mod = ($i % 2 );++$i;?><!-- 子导航 -->
                    <?php if(!empty($sub_menu)): if(!empty($key)): ?><h3><i class="icon icon-unfold"></i><?php echo ($key); ?></h3><?php endif; ?>
                        <ul class="side-sub-menu">
                            <?php if(is_array($sub_menu)): $i = 0; $__LIST__ = $sub_menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu): $mod = ($i % 2 );++$i;?><li>
                                    <a class="item" href="<?php echo (U($menu["url"])); ?>"><?php echo ($menu["title"]); ?></a>
                                </li><?php endforeach; endif; else: echo "" ;endif; ?>
                        </ul><?php endif; ?>
                    <!-- /子导航 --><?php endforeach; endif; else: echo "" ;endif; ?>
            </div>
        
        <!-- /子导航 -->
    </div>
    <!-- /边栏 -->

    <!-- 内容区 -->
    <div id="main-content">
        <div id="top-alert" class="fixed alert alert-error" style="display: none;">
            <button class="close fixed" style="margin-top: 4px;">&times;</button>
            <div class="alert-content">这是内容</div>
        </div>
        <div id="main" class="main">
            
            <!-- nav -->
            <?php if(!empty($_show_nav)): ?><div class="breadcrumb">
                <span>您的位置:</span>
                <?php $i = '1'; ?>
                <?php if(is_array($_nav)): foreach($_nav as $k=>$v): if($i == count($_nav)): ?><span><?php echo ($v); ?></span>
                    <?php else: ?>
                    <span><a href="<?php echo ($k); ?>"><?php echo ($v); ?></a>&gt;</span><?php endif; ?>
                    <?php $i = $i+1; endforeach; endif; ?>
            </div><?php endif; ?>
            <!-- nav -->
            

            
<link rel="stylesheet" type="text/css" href="/Public/Admin/css/admin_table.css" media="all">
<div class="main-title cf" style="border-bottom:3px solid  #C1C1C1;margin-bottom:20px ;">
    <h2 style="font-weight: bold;color:#292C33;">渠道绑定平台币修改</h2>
    </div>
    <!-- 标签页导航 -->
    <div class="tab-wrap">
      <ul class="tab-nav nav">
        <li data-tab="tab1" class="current"><a href="javascript:void(0);">单账号</a></li>
        <li data-tab="tab2" ><a href="javascript:void(0);">导入Excel</a></li>
        <li data-tab="tab3" ><a href="javascript:void(0);">多用户</a></li>
      </ul>
       <div class="tab-content zc_for">
        <!-- 表单 -->
        
          <!-- 基础文档模型 -->
          <div id="tab1" class="tab-pane in tab1 ">
          <form id="form1" action="<?php echo U('bind_balance_edit?type=1');?>" method="post" class="form-horizontal">
          <table border="0" cellspacing="0" cellpadding="0">
            <tbody>
              <tr>
                  <td class="l">游戏名称：</td>
                  <td class="r">
                    <select id="game_id" name="game_id">
                      <option value="">请选择游戏</option>
                     <?php $_result=get_game_list();if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["game_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                  </td>
              </tr>              
              <tr>
                  <td class="l">渠道账户：</td>
                <td class="r" colspan='3'>
                   <input type="text" class="txt" name="account" value="">
                </td>
              </tr>
              <tr>
                <td class="l">修改金额：</td>
                <td class="r" colspan='3'>
                   <input type="text" class="txt" name="amount" value="">
                </td>
              </tr>
              <tr>
                  <td class="r">
                    <button class="btn submit-btn ajax-post hidden" id="submit1" type="submit" target-form="form-horizontal">提 交</button>
                  </td>
                  <td class="r" colspan='3'>
                    <a class="btn btn-return" href="javascript:history.back(-1);">返 回</a>
                  </td>
              </tr> 
                                        
            </tbody>
          </table>
          </form>
           </div>
          <div id="tab2" class="tab-pane  tab2">
          <form id="form2" action="<?php echo U('bind_balance_edit?type=2');?>" method="post" class="form-horizontal" enctype="multipart/form-data">
          <table border="0" cellspacing="0" cellpadding="0">
            <tbody>
              <tr>
                <td class="l">Excel模板：</td>
                <td class="r" colspan='3'>
                   <a href="./Uploads/excel/Template.xls">下载模板</a>
                </td>
              </tr>
              <tr>
                <td class="l">导入Excel：</td>
                <td class="r" colspan='3'>
                   <input type="file" name="excelData" value=""  >
                </td>
              </tr>
              <tr>
                  <td class="r">
                    <button class="btn submit-btn ajax-post hidden" id="submit2" type="submit" target-form="form-horizontal">提 交</button>
                  </td>
                  <td class="r" colspan='3'>
                    <a class="btn btn-return" href="javascript:history.back(-1);">返 回</a>
                  </td>
              </tr>                             
            </tbody>
          </table>
          </form>
          </div>
          <div id="tab3" class="tab-pane  tab3">
          <form id="form3" action="<?php echo U('bind_balance_edit?type=3');?>" method="post" class="form-horizontal">
          <table border="0" cellspacing="0" cellpadding="0">
            <tbody>
              <tr>
                  <td class="l">游戏名称：</td>
                  <td class="r">
                    <select id="game_id1" name="game_id">
                      <option value="">请选择游戏</option>
                     <?php $_result=get_game_list();if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["game_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                  </td>
              </tr>
              
              <tr>
                <td class="l">充值账号：</td>
                <td class="r" colspan='3'>
                  <textarea name="pay_names" id="pay_names" cols="32" rows="20"></textarea>
                  (一行一个账号)
                </td>
              </tr>
              <tr>
                <td class="l">充值金额：</td>
                <td class="r" colspan='3'>
                   <input type="text" class="txt" name="amount" value="">
                </td>
              </tr>
              <tr>
                  <td class="r">
                    <button class="btn submit-btn ajax-post hidden" id="submit3" type="submit" target-form="form-horizontal">提 交</button>
                  </td>
                  <td class="r" colspan='3'>
                    <a class="btn btn-return" href="javascript:history.back(-1);">返 回</a>
                  </td>
              </tr>                               
            </tbody>
          </table> 
          </form>
          </div>
        
      </div>
    </div>

        </div>
        <div class="cont-ft">
            <div class="copyright">
                <div class="fl">感谢使用<a href="http://www.zhishengwh.com">致晟</a>游戏运营平台 V2.0.6.15</div>
                <div class="fr"></div>
            </div>
        </div>
    </div>
    <!-- /内容区 -->
    <script type="text/javascript">
    (function(){
        var ThinkPHP = window.Think = {
            "ROOT"   : "", //当前网站地址
            "APP"    : "/admin.php?s=", //当前项目地址
            "PUBLIC" : "/Public", //项目公共目录地址
            "DEEP"   : "<?php echo C('URL_PATHINFO_DEPR');?>", //PATHINFO分割符
            "MODEL"  : ["<?php echo C('URL_MODEL');?>", "<?php echo C('URL_CASE_INSENSITIVE');?>", "<?php echo C('URL_HTML_SUFFIX');?>"],
            "VAR"    : ["<?php echo C('VAR_MODULE');?>", "<?php echo C('VAR_CONTROLLER');?>", "<?php echo C('VAR_ACTION');?>"]
        }
    })();
    </script>
    <script type="text/javascript" src="/Public/static/think.js"></script>
    <script type="text/javascript" src="/Public/Admin/js/common.js"></script>
    <script type="text/javascript">
        +function(){
            var $window = $(window), $subnav = $("#subnav"), url;
            $window.resize(function(){
                $("#main").css("min-height", $window.height() - 130);
            }).resize();

            /*初始化导航菜单*/
             $subnav.find(".icon").addClass("icon-fold");
             $subnav.find(".side-sub-menu").siblings(".side-sub-menu").hide();

            /* 左边菜单高亮 */
            url = window.location.pathname + window.location.search;
            url = url.replace(/(\/(p)\/\d+)|(&p=\d+)|(\/(id)\/\d+)|(&id=\d+)|(\/(group)\/\d+)|(&group=\d+)/, "");
            $subnav.find("a[href='" + url + "']").parent().addClass("current");
            //显示选中的菜单
            $subnav.find("a[href='" + url + "']").parent().parent().prev("h3").find("i").removeClass("icon-fold");
            $subnav.find("a[href='" + url + "']").parent().parent().show();

            /* 左边菜单显示收起 */
            $("#subnav").on("click", "h3", function(){
                var $this = $(this);
                $this.find(".icon").toggleClass("icon-fold");
                $this.next().slideToggle("fast").siblings(".side-sub-menu:visible").
                      prev("h3").find("i").addClass("icon-fold").end().end().hide();
            });
            $("#subnav h3 a").click(function(e){e.stopPropagation()});
            /* 头部管理员菜单 */
            $(".user-bar").mouseenter(function(){
                var userMenu = $(this).children(".user-menu ");
                userMenu.removeClass("hidden");
                clearTimeout(userMenu.data("timeout"));
            }).mouseleave(function(){
                var userMenu = $(this).children(".user-menu");
                userMenu.data("timeout") && clearTimeout(userMenu.data("timeout"));
                userMenu.data("timeout", setTimeout(function(){userMenu.addClass("hidden")}, 100));
            });

            /* 表单获取焦点变色 */
            $("form").on("focus", "input", function(){
                $(this).addClass('focus');
            }).on("blur","input",function(){
                        $(this).removeClass('focus');
                    });
            $("form").on("focus", "textarea", function(){
                $(this).closest('label').addClass('focus');
            }).on("blur","textarea",function(){
                $(this).closest('label').removeClass('focus');
            });

            // 导航栏超出窗口高度后的模拟滚动条
            var sHeight = $(".sidebar").height();
            var subHeight  = $(".subnav").height();
            var diff = subHeight - sHeight; //250
            var sub = $(".subnav");
            if(diff > 0){
                $(window).mousewheel(function(event, delta){
                    if(delta>0){
                        if(parseInt(sub.css('marginTop'))>-10){
                            sub.css('marginTop','0px');
                        }else{
                            sub.css('marginTop','+='+10);
                        }
                    }else{
                        if(parseInt(sub.css('marginTop'))<'-'+(diff-10)){
                            sub.css('marginTop','-'+(diff-10));
                        }else{
                            sub.css('marginTop','-='+10);
                        }
                    }
                });
            }
        }();
    </script>
    
<link href="/Public/static/datetimepicker/css/datetimepicker.css" rel="stylesheet" type="text/css">
<?php if(C('COLOR_STYLE')=='blue_color') echo '<link href="/Public/static/datetimepicker/css/datetimepicker_blue.css" rel="stylesheet" type="text/css">'; ?>
<link href="/Public/static/datetimepicker/css/dropdown.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="/Public/static/datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="/Public/static/datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js" charset="UTF-8"></script>
<script type="text/javascript">
// $(function(){
    //导航高亮
    highlight_subnav('<?php echo U("BangPropay/bind_balance_edit");?>');

    $('.time').datetimepicker({
        format: 'yyyy-mm-dd hh:ii',
        language:"zh-CN",
        minView:2,
        autoclose:true
    });
    showTab();


    $("#moneynum").blur(function(){
        $("#coinnums").html($("#moneynum").val());
        $("#coinnum").val(  $("#coinnums").html());
    })

    $("#submit1").click(function(){$('#form1').submit();  })
    $("#submit2").click(function(){$('#form2').submit();  })
    $("#submit3").click(function(){$('#form3').submit();  })
    





</script>

</body>
</html>