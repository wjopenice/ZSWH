$(function(){
    //电话号码隐藏中间四位
    var tel = $('.tel').html();
    var mtel = tel.substr(0, 3) + '****' + tel.substr(7);
    $('.tel').html(mtel);


    //新增地址
    $(document).on("click",'.address_btn',function(){
        $(".getCouponAlert").hbyPopShow({
            width: "700",
            height: "380",
            isHideTitle: true
        });
    });

    //新增地址的默认地址icon
    $(document).on("click",".active_default_icon",function(){
        var is_checked = $(".active_default_icon").hasClass('active_icon');
        if(!is_checked){
            $(this).addClass("active_icon");
        }else{
            $(this).removeClass("active_icon");
        }
    });

    //删除地址
    $(document).on("click",'.del_btn',function(){
        delBtnId = $(this).parent().parent().attr("data-id");
        delTitle = $(this).parent().siblings(".add1").text();
        delAddress = $(this).parent().siblings(".add2").text();
        delTel = $(this).parent().siblings(".add3").text();
        console.log(delTitle);
        $(".add_name").text(delTitle);
        $(".add_tel").text(delTel);
        $(".add_address").text(delAddress);
        $(".get_del_alert").hbyPopShow({
            width: "700",
            height: "290",
            isHideTitle: true
        });
    });

    //新增地址关闭
    $(document).on("click",'.close',function(){
        $(".get_coupon_alert").hbyPopHide();
    });

    //删除地址关闭
    $(document).on("click",'.close',function(){
        $(".get_del_alert").hbyPopHide();
    });

    //设置默认
    $(document).on("click",'.set_add',function(){
        $(".set_add").text("设为默认地址").removeClass('default');
        $(".set_add").addClass('add_l');
        $(this).text("默认地址").addClass('default');
        $(this).removeClass('add_l');

    });




});