/**
 * Created by ASUS on 2016/1/2.
 */
$(function () {
   
    //详情弹窗
    $('#details-dialog').dialog({
        width : 780,
        height : 500,
        modal : true,
        closed : true,
        maximizable : true,
        iconCls : 'icon-tip',
        buttons : [{
            text : '关闭',
            size : 'large',
            iconCls : 'icon-cross',
            handler : function () {
                $('#details-dialog').dialog('close');
            }
        }]
    });

    //详情弹窗
    $('#details-prints-dialog').dialog({
        width : 780,
        height : 500,
        modal : true,
        closed : true,
        maximizable : true,
        iconCls : 'icon-tip',
        buttons : [
            {
                text : '打印',
                size : 'large',
                iconCls : 'icon-cross',
                handler : function () {
                    printWin();
                }
            },
            {
            text : '关闭',
            size : 'large',
            iconCls : 'icon-cross',
            handler : function () {
                $('#details-prints-dialog').dialog('close');
            }
        }]
    });
});
/*------------------------------------修改样式------2016-10-9---xxl----后期要加载文件中*/
 $('head').append('<style>.tree-hit.tree-expanded{background:url(/Public/admin/easyui/themes/icons/icon.png) no-repeat -28px -164px!important;}.tree-hit.tree-collapsed{background:url(/Public/admin/easyui/themes/icons/icon.png) no-repeat 0 -164px!important}#nav {margin: 0;}.tree-node {border-bottom: 1px solid #ccc;padding: 10px 15px;}.tree-icon.tree-file{background:url(/Public/admin/easyui/themes/icons/icon.png) no-repeat -40px -196px!important;width:25px}.tree-icon.tree-folder{background:url(/Public/admin/easyui/themes/icons/icon.png) no-repeat 0 -196px!important;width:25px;}.tree-indent.tree-join,.tree-indent.tree-line{background:none!important;}.tree-node-selected .tree-icon.tree-folder{background:url(/Public/admin/easyui/themes/icons/icon.png) no-repeat 0px -236px!important}.tree-node-selected .tree-icon.tree-file{background:url(/Public/admin/easyui/themes/icons/icon.png) no-repeat -40px -236px!important}.tree-node .tree-indent+.tree-indent{display: none;}.dn{display: none;}.navone-x {height:45px;overflow: hidden;}.navone-x span{ border: 1px solid #1da0d0;border-left: 1px solid #4dc4f0;border-right: 1px solid #1da0d0;color: #fff;cursor: pointer; display: block;float: left;height: 45px;line-height: 45px;margin: -1px;padding: 0 10px;font-size:14px;}.navone-x span.active{background: #16A0D3;}.logo{width:180px;border-right: 1px solid #1da0d0;}.layout-north{background:#33B5E5}.navone-x span:hover,.right-arrow-down:hover{background:#50C0E9;}.tree-node-selected {background: #33B5E5;border-radius:0px;position: relative;}.tree-node-selected:after{content:"";  position: absolute;top: 9.5px;right: -1px;width: 7px;height: 21px;background: url(/Public/admin/easyui/themes/icons/icon.png) -40px -356px no-repeat;}.tree-node-hover{background:#F1F1F1,border-radius:0px;}.tabs-wrap ul.tabs{height:34px}.tabs-loading{top:37px;}.nav{padding-right: 0;}.right-arrow-down{float:right;background:#16A0D3;position: relative;display: block;cursor: pointer;padding: 0 15px;height: 45px;border-left: 1px solid #4dc4f0;}.right-arrow-down i{display: block;width: 20px;   height: 45px; background: url(/Public/admin/easyui/themes/icons/icon.png) -199px -305px no-repeat;}.drop-box {display: none;    position: absolute;top: 45px;right: 1px;}.drop-box .arrow{ position:absolute; display:block; top:0; right:15px; width:21px; height:11px; text-indent:-999999px; background:url(/Public/admin/easyui/themes/icons/icon.png) no-repeat 0 -356px; }    .drop-box .drop-item{ margin-top:10px; padding:10px; border:1px solid #b1b1b1; background:#fff; box-shadow:0 0 4px 0 rgba(0, 0, 0, 0.2); }    .drop-box .drop-item li{line-height:28px;} .drop-box .drop-item li a{ padding:8px 15px; color:#222;height:12px; font-size:12px; line-height:12px; text-align:center; white-space:nowrap; } .drop-box .drop-item li a:hover{ color:#fff; text-decoration:none; background:#55afeb; }.right-arrow-down:hover .drop-box {display:block}.layout-panel{overflow:visible;}.panel.layout-panel.layout-panel-west,.panel.layout-panel.layout-panel-center,.panel.layout-expand.layout-expand-west{top:45px!important;}.window .window-body.messager-body{position: relative; padding: 20px 10px;}.messager-icon{    position: absolute;    top: 50%;    margin-top: -17px;left: 13px;}.messager-icon+div{padding:15px 0px 15px 50px;}.datagrid-row-selected{background:#33B5E5!important;}</style>');
 /*------------------------------------循环数据输出------2016-10-9---xxl*/
var oneNav=$('<div class="navone-x " style="float:left"></div>');
//var dataArry=['主导航','权限','临时'];
//.forEach(function(val){//示例
$.ajax({url:ThinkPHP['MODULE'] + '/Index/getNav',
     type : 'POST',
     success :succData
});
function succData (data) {
    var dataArry=data;
    var leng_=dataArry.length;
    var leng_i=1;
 for(var i=0;i<leng_;i++){
    var newDiv=$('<div class="nav-list dn"></div>');
oneNav.append('<span><i style="display: block;float: left;width: 20px;height: 42px;margin-right: 5px;margin-top: 2px;" class="'+dataArry[i].simg+'"></i>'+dataArry[i].text+'</span>');
if(!!!dataArry[i].children)leng_i++;
newDiv.tree({
   //url : ThinkPHP['MODULE'] + '/Index/getNav',
   data:dataArry[i].children,
    lines : true,
    animate : true,
    onLoadSuccess : function (node, data) {
       var _this = this;
       if (data) {
           $(data).each(function () {
            $(_this).tree('collapseAll');
               if (this.state == 'closed') {
                   //$(_this).tree('expandAll');
               }
           })
       } else {
           $('#nav').tree('remove', node.target);
       } 
       $(_this).tree('collapseAll');
       leng_i++;
    },
    onLoadError:function(){
       
    },
    onClick : function (node)
    {

        var tabs = $('#tabs');
        var _this=this;
        //有链接才能打开选项卡
        if (node.url)
        {
            //判断选项卡是否存在
            if (tabs.tabs('exists', node.text))
            {
                tabs.tabs('select', node.text)
            } else {
                //添加选项卡
                tabs.tabs('add', {
                    title : node.text,
                    closable : true,
                    iconCls : node.iconCls,
                    href : ThinkPHP['MODULE'] + '/' + node.url
                });
            }
        }
        if(!$(_this).tree('isLeaf',node.target)){
            $(_this).tree('toggle',node.target);
            console.log();
            $(node.target).parent().siblings().children('div').each(function(){
            $(_this).tree('collapse',this);
            });
            var no=$(_this).tree('getChecked');
            $(node.target).removeClass('tree-node-selected');
        }
    }
});
$('#nav').append(newDiv);
};
$('#nav').children().tree('collapseAll');
function settim(){
    if(leng_i>=leng_){
        setTimeout(function(){ $('#nav').find('.tree-root-first').trigger('click');},500);
    }else{
        setTimeout(settim,500);
    }
}
settim();
//$('#nav').children().each(function(){
   // $(this).find('.tree-root-first').trigger('click');
//})
$('#nav').children().eq(0).removeClass('dn');//默认第一个显示
oneNav.children().eq(0).addClass('active');//默认第一个选中若是需要刷新还是原来选中，记录cookie即可
$('.logo').after(oneNav);
oneNav.children().click(function(){
    $(this).addClass('active').siblings().removeClass('active');
    $('#nav').children().eq($(this).index()).removeClass('dn').siblings().addClass('dn');
});
}



/*-----------------------------------数据循环输出完毕-------------*/
$('#tabs').tabs({
    fit : true,
    border : false,
    tabHeight: 35,
    onLoad : function () {
        //非火狐浏览器，移出掉loading...
        if (navigator.userAgent.indexOf('Firefox') <= 0) {
            $('.tabs-loading').remove();
        }
    },
    onContextMenu : function (e, title, index) {
        e.preventDefault();

        var _this = this;
        var menu = $('#menu');

        menu.menu('show', {
            top : e.pageY,
            left : e.pageX
        });


        if (index == 0)
        {
            menu.menu('disableItem', $('.closecur')[0]);
        } else {
            menu.menu('enableItem', $('.closecur')[0]);
        }

        menu.menu({
            onClick : function (item)
            {
                var tablist = $(_this).tabs('tabs');

                switch (item.text)
                {
                    case '关闭' :
                        $(_this).tabs('close', index);
                        break;
                    case '关闭所有' :
                        for (var i = tablist.length; i > 0 ; i --)
                        {
                            $(_this).tabs('close', i);
                        }
                        break;
                    case '关闭其他所有' :
                        for (var i = tablist.length; i > 0 ; i --)
                        {
                            if (i != index)
                            {
                                $(_this).tabs('close', i);
                            }
                        }
                        $(_this).tabs('select', 1);
                        break;
                }
            }
        });
    }
});


//火狐渲染机制导致加载时会有短暂的混乱
//通过单独加上遮罩来解决这个不好的体验
//if (navigator.userAgent.indexOf('Firefox') > 0) {
    $.parser.onComplete = function () {
        $('.tabs-loading').hide();
    };
//}


//重置排序扩展
$.extend($.fn.datagrid.methods, {
    resetSort: function(jq, param){
        return jq.each(function(){
            var state = $.data(this, 'datagrid');
            var opts = state.options;
            var dc = state.dc;
            var header = dc.header1.add(dc.header2);
            header.find('div.datagrid-cell').removeClass('datagrid-sort-asc datagrid-sort-desc');
            param = param || {};
            opts.sortName = param.sortName;
            opts.sortOrder = param.sortOrder || 'asc';
            //if (opts.sortName){
            //    var names = opts.sortName.split(',');
            //    var orders = opts.sortOrder.split(',');
            //    for(var i=0; i<names.length; i++){
            //        var col = $(this).datagrid('getColumnOption', names[i]);
            //        header.find('div.'+col.cellClass).addClass('datagrid-sort-'+orders[i]);
            //    }
            //}
        })
    }
});
//获取时间格式
function   formatDate(now)   {
    var   year=now.getFullYear();
    var   month=now.getMonth()+1;
    var   date=now.getDate();
    var   hour=now.getHours();
    var   minute=now.getMinutes();
    var   second=now.getSeconds();
    return   year+"-"+formatDateTime(month)+"-"+formatDateTime(date)+"   "+formatDateTime(hour)+":"+formatDateTime(minute)+":"+formatDateTime(second);
}
function  formatDateTime(val) {
    return val>=10?val:'0'+val;
}