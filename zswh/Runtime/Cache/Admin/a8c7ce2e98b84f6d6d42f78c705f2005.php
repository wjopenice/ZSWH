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
            

            
    <!-- 标题栏 -->
    <div class="main-title">
        <h2>[<?php echo ($model['title']); ?>] 列表</h2>
    </div>
	<div class="cf top_nav_list">
		<div class="fl button_list">
		<?php if(empty($model["extend"])): ?><div class="tools">
				 <a class="btn blue" href="<?php echo U('add?model='.$model['id']);?>">新 增</a> 
				<!-- <button class="btn ajax-post confirm red" target-form="ids" url="<?php echo U('del?model='.$model['id']);?>">删 除</button> -->
                <button class="btn ajax-post org" target-form="ids" url="<?php echo U("Promote/set_status",array("status"=>1,"msg_type"=>5,"field"=>"status"));?>">审 核</button>
            <button class="btn ajax-post lahei" target-form="ids" url="<?php echo U("Promote/set_status",array("status"=>2,"msg_type"=>10,"field"=>"status"));?>">拉 黑</button>
			</div><?php endif; ?>
		</div>
		<!-- 高级搜索 -->
		<div class="search-form fr cf">
           <!-- 渠道状态 -->
            <div class="d_list">
                <div class="drop-down drop-down12" style="width:100px">
                    <span  class="sort-txt" data="{I('status')}" style="width:65px">
                        <?php if(I('status') == ''): ?>状态
                        <?php elseif(I('status') == 0): ?>未审核
                        <?php elseif(I('status') == 1): ?>正常
                        <?php elseif(I('status') == 2): ?>拉黑<?php endif; ?>
                    </span>
                    <i class="arrow arrow-down"></i>
                    <ul  class="nav-list hidden">
                        <li><a href="javascript:;" value="0" style="width:80px">未审核</a></li>
                        <li><a href="javascript:;" value="1" style="width:80px">正常</a></li>
                        <li><a href="javascript:;" value="2" style="width:80px">拉黑</a></li>
                    </ul>
                </div>
                <input type='hidden' class="hiddenvalue" id='status' name="status" value="<?php echo I('status');?>"></input>
            </div>
             <!-- 渠道直冲状态 -->
            <div class="d_list">
                <div class="drop-down drop-down13" style="width:100px">
                    <span  class="sort-txt" data="{I('alipayway_sign')}" style="width:65px">
                        <?php if(I('alipayway_sign') == ''): ?>直冲流水
                        <?php elseif(I('alipayway_sign') == 0): ?>关闭
                        <?php elseif(I('alipayway_sign') == 1): ?>开启<?php endif; ?>
                    </span>
                    <i class="arrow arrow-down"></i>
                    <ul  class="nav-list hidden">
                        <li><a href="javascript:;" value="0" style="width:80px">关闭</a></li>
                        <li><a href="javascript:;" value="1" style="width:80px">开启</a></li>
                    </ul>
                </div>
                <input type='hidden' class="hiddenvalue" id='alipayway_sign' name="alipayway_sign" value="<?php echo I('alipayway_sign');?>"></input>
            </div>
          <!-- 所属管理员 -->
         <div class="i_list">
                 <div class="drop-down drop-down22">
                    <?php if(I('nickname') == ''): ?><input type="text" id="i_list_id" class="sort-txt" value="" placeholder="请选择管理员" />
                    <?php else: ?>
                    <input type="text" id="i_list_id" class="sort-txt"  value="<?php echo I('nickname');?>" placeholder="请选择管理员" /><?php endif; ?>
                    <input type="hidden" id="i_list_idh" name="admin_id" value="<?php echo I('admin_id');?>" />
                    <input type="hidden" id="i_list_ida" name="nickname" value="<?php echo I('nickname');?>" />
                    <i class="arrow arrow-down"></i>
                    <ul id="i_list_li" class="nav-list hidden i_list_li">
                        <li><a href="javascript:;" value="" >全部</a></li>
                        <?php $_result=get_member_list();if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li><a href="javascript:;" value="<?php echo ($vo["uid"]); ?>" ><?php echo ($vo["nickname"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
                    </ul>
                </div>                
            </div>

            <!-- 上级渠道搜索 -->
            <div class="i_list">
                 <div class="drop-down drop-down2">
                    <?php if(I('promote_name') == ''): ?><input type="text" id="promoteid" class="sort-txt" value="" placeholder="请选择所属渠道" />
                    <?php else: ?>
                    <input type="text" id="promoteid" class="sort-txt" value="<?php echo I('promote_name');?>" placeholder="请选择所属渠道" /><?php endif; ?>
                    <input type="hidden" id="promoteidh" name="promote_name" value="<?php echo I('promote_name');?>" />
                    <i class="arrow arrow-down"></i>
                    <ul id="promoteidlist" class="nav-list hidden i_list_li">
                        <li><a href="javascript:;" value="" >全部</a></li>
                        <li><a href="javascript:;" value="0" >一级渠道</a></li>
                        <?php $_result=get_promote_all();if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li><a href="javascript:;" value="<?php echo ($vo["id"]); ?>" ><?php echo ($vo["account"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
                    </ul>
                </div>                
            </div>



			<div class="sleft">
				<input type="text" name="<?php echo ((isset($model['search_key']) && ($model['search_key'] !== ""))?($model['search_key']):'account'); ?>" class="search-input" value="<?php echo I('account');?>" placeholder="请输入渠道账号" id="account">
				<!-- <a class="sch-btn" href="javascript:;" id="search" url="<?php echo U('Promote/lists','model='.$model['name'],false);?>"><i class="btn-search"></i></a> -->
			</div>
            <div class="sleft">
                <input type="text" name="<?php echo ((isset($model['search_key']) && ($model['search_key'] !== ""))?($model['search_key']):'id'); ?>" class="search-input" value="<?php echo I('id');?>" placeholder="请输入渠道ID" id="qu_id">
            </div>

            <div class="input-list">
                <a class="sch-btn" href="javascript:;" id="search" url="<?php echo U('Promote/lists','model='.$model['name'],false);?>">搜素</a>
            </div>
            <div class="input-list">
                <a class="sch-btn" onclick="exportuser()">导出</a>
                <span class="totalvalue">(共计发放<i><?php echo ($total); ?></i>元)</span>

              <!--  href="
                <?php echo U('Export/expUser',array('id'=>23,'account'=>I('account'),'admin_id'=>I('admin_id'), 'status'=>I('status'),'time-start'=>I('time-start'),'end'=>I('end'),));?>"-->
            </div>

		</div>
	</div>


    <!-- 数据列表 -->
    <div class="data-table">
        <div class="data-table table-striped">
            <table>
                <!-- 表头 -->
                <thead>
                    <tr>
                        <th style="text-align:center" class="row-selected row-selected">
                            <input class="check-all" type="checkbox">
                        </th>
                        <?php if(is_array($list_grids)): $i = 0; $__LIST__ = $list_grids;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$field): $mod = ($i % 2 );++$i;?><th style="text-align:center"><?php echo ($field["title"]); ?></th><?php endforeach; endif; else: echo "" ;endif; ?>
                    </tr>
                </thead>

                <!-- 列表 -->
                <tbody>
                    <?php if(is_array($list_data)): $i = 0; $__LIST__ = $list_data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data): $mod = ($i % 2 );++$i;?><tr>
                            <td  style="border-right:1px solid #DDDDDD;text-align:center"><input class="ids" type="checkbox" value="<?php echo ($data['id']); ?>" name="ids[]"></td>
                            <?php if(is_array($list_grids)): $i = 0; $__LIST__ = $list_grids;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$grid): $mod = ($i % 2 );++$i;?><td  style="border-right:1px solid #DDDDDD;text-align:center"><?php echo get_list_field($data,$grid);?></td><?php endforeach; endif; else: echo "" ;endif; ?>
                        </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="page">
        <?php echo ((isset($_page) && ($_page !== ""))?($_page):''); ?>
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
    
<script type="text/javascript">
//导航高亮
highlight_subnav('<?php echo U('Promote/lists');?>');
function exportuser(){
    var account = $("#account").val();
    var admin_id=$("#i_list_idh").val();
    var status=$("#status").val();
    var promote_name=$("#promoteidh").val();
    var alipayway_sign=$("#alipayway_sign").val();
    var input = document.getElementsByName("ids[]");
    var ids=[];
   for (var i = 0; i < input.length; i++)
    {
        var obj = input[i];
        //判断是否是checkbox并且已经选中
        if (obj.type == "checkbox" && obj.checked)
        {
            var code = obj.value;//获取checkbox的值
           ids.push(code);
        }
    }
   var url="/admin.php?s=/Export/expUser/id/23/ids/"+ids+"/qu_id/"+$("#qu_id").val()+"/account/"+account+"/status/"+status+"/admin_id/"+admin_id+"/promote_name/"+promote_name+"/alipayway_sign/"+alipayway_sign+".html";
   window.open(url);

}
$(function(){
	//搜索功能
	$("#search").click(function(){
		var url = $(this).attr('url');
        var query  = $('.search-form').find('input').serialize();
        query = query.replace(/(&|^)(\w*?\d*?\-*?_*?)*?=?((?=&)|(?=$))/g,'');
        query = query.replace(/^&/g,'');
        if( url.indexOf('?')>0 ){
            url += '&' + query;
        }else{
            url += '?' + query;
        }
		window.location.href = url;
	});

    //回车自动提交
    $('.search-form').find('input').keyup(function(event){
        if(event.keyCode===13){
            $("#search").click();
        }
    });

     //状态
    $(".d_list").find(".drop-down12").hover(function(){
        $(this).find(".nav-list").removeClass("hidden");
    },function(){
        $(this).find(".nav-list").addClass("hidden");
    });

     $(".drop-down12 .nav-list li").find("a").each(function(){
        var that = $(".drop-down12");
        $(this).click(function(){
            var text = $(this).text(),val = $(this).attr("value");
            that.find(".sort-txt").text(text).attr("data",val);
            that.find(".nav-list").addClass("hidden");
            that.siblings('.hiddenvalue').val(val);
        })
    });

 //直冲状态
$(".d_list").find(".drop-down13").hover(function(){
        $(this).find(".nav-list").removeClass("hidden");
    },function(){
        $(this).find(".nav-list").addClass("hidden");
    });

     $(".drop-down13 .nav-list li").find("a").each(function(){
        var that = $(".drop-down13");
        $(this).click(function(){
            var text = $(this).text(),val = $(this).attr("value");
            that.find(".sort-txt").text(text).attr("data",val);
            that.find(".nav-list").addClass("hidden");
            that.siblings('.hiddenvalue').val(val);
        })
    });
     //渠道管理员名称
     $(".drop-down22").on('click',function(event) {
        var navlist = $(this).find('.i_list_li');
        if (navlist.hasClass('hidden')) {
            navlist.removeClass('hidden');
            $('#i_list_id').focus().val('');            
        } else {
            navlist.addClass('hidden');
        }
        $(document).one("click", function(){
            navlist.addClass('hidden');
        });
        event.stopPropagation();
    });

    $('#i_list_id').on('keyup',function(event) {
        var val  = $.trim($(this).val()).toLowerCase();
        $('#i_list_idh').val(val);
    });
    
    $("#i_list_li").find("a").each(function(){
        $(this).click(function(){
            var text = $.trim($(this).text()).toLowerCase();
            var admin_id = $.trim($(this).attr('value')).toLowerCase();
            $("#i_list_id").val(text);
            $('#i_list_idh').val(admin_id);
            $("#i_list_ida").val(text);
            //alert(text);
        })
    });

     //上级渠道搜索 
      $(".drop-down2").on('click',function(event) {
        var navlist = $(this).find('.i_list_li');
        if (navlist.hasClass('hidden')) {
            navlist.removeClass('hidden');
            $('#promoteid').focus().val('');            
        } else {
            navlist.addClass('hidden');
        }
        $(document).one("click", function(){
            navlist.addClass('hidden');
        });
        event.stopPropagation();
    });
    var a = $('.i_list_li li a');
    $('#promoteid').on('keyup',function(event) {
        var val  = $.trim($(this).val()).toLowerCase();
        $('#promoteidh').val(val);
    });
    
    $("#promoteidlist").find("a").each(function(){
        $(this).click(function(){
            var text = $.trim($(this).text()).toLowerCase();
            $("#promoteid").val(text);
            $('#promoteidh').val(text);
        })
    });

})
</script>

</body>
</html>