$(function () {
    //单条删除
    $(document).on("click",'.de_btn',function(){
        $(this).parent().parent().remove();
    });

    //点击批量删除的全选和反选
    $(document).on("click",'.collection_title .check_i',function(){
        var is_checked = $(this).hasClass('active');
        if(!is_checked){
            $(".col_ul .check_info").addClass('active');
            $(".collection_title .check_i").addClass('active');
        }else{
            $(".col_ul .check_info").removeClass('active');
            $(".collection_title .check_i").removeClass('active');
        }

    });

    //批量删除
    $(document).on("click",'.delete',function(){
        var is_checked = $(".collection_title .check_i").hasClass('active');
        if(!is_checked){
            modal('400','请选择您要删除的商品');
        }else{
            var bool = true;
            $.each($(".col_ul .check_info"),function(i,obj){
                bool = $(obj).hasClass('active') && bool;
            });
            if(bool){
                $(".col_ul").empty();
                $(".collection_title .check_i").removeClass('active');
                /*$(".noCart").show();*/
            }else{
                $(".col_ul .check_info.active").parents(".col_ul li").remove();

            }
        }
    });

});

