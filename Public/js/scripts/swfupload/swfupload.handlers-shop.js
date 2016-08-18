$(function () {
    //初始化绑定默认的属性
    $.swfUpLoadDefaults = $.swfUpLoadDefaults || {};
    $.swfUpLoadDefaults.property = {
        single: true, //是否单文件
        water: false, //是否加水印
        thumbnail: false, //是否生成缩略图
        sendurl: null, //发送地址
        filetypes: "*.jpg;*.jpge;*.png;*.gif;", //文件类型
        filesize: "2048", //文件大小
        btntext: "浏览...", //上传按钮的文字
        btnwidth: 48, //上传按钮的宽度
        btnheight: 28, //上传按钮的高度
        flashurl: null //FLASH上传控件相对地址
    };
    //初始化SWFUpload上传控件
    $.fn.InitSWFUpload = function (p) {
        p = $.extend({}, $.swfUpLoadDefaults.property, p || {});
        //创建上传按钮
        var parentObj = $(this);
        var parentBtnId = "upload_span_" + Math.floor(Math.random() * 1000 + 1);
        parentObj.append('<div class="upload-btn"><span id="' + parentBtnId + '"></span></div>');
        //初始化属性
        var btnAction = SWFUpload.BUTTON_ACTION.SELECT_FILES; //多文件上传

        p.sendurl += "/Admin/Public/upload?State=1";
        if (p.single) {
            btnAction = SWFUpload.BUTTON_ACTION.SELECT_FILE; //单文件上传
        }
        if (p.water) {
            p.sendurl += "&IsWater=1";
        }
        if (p.thumbnail) {
            p.sendurl += "&IsThumbnail=1";
        }

        //初始化上传控件
        var swfu = new SWFUpload({
            post_params: { "ASPSESSID": "NONE" },
            upload_url: p.sendurl, //上传地址
            file_size_limit: p.filesize, //文件大小
            file_types: p.filetypes, //文件类型
            file_types_description: "JPG Images",
            file_upload_limit: "0", //一次能上传的文件数量

            file_queue_error_handler: fileQueueError,
            file_dialog_complete_handler: fileDialogComplete,
            upload_progress_handler: uploadProgress,
            upload_error_handler: uploadError,
            upload_success_handler: uploadSuccess,
            upload_complete_handler: uploadComplete,

            button_placeholder_id: parentBtnId, //指定一个dom元素
            button_width: p.btnwidth, //上传按钮的宽度
            button_height: p.btnheight, //上传按钮的高度
            button_text: '<span class="btnText">' + p.btntext + '</span>', //上传按钮的文字
            button_text_style: ".btnText{font-family:Microsoft YaHei;font-size:12px;line-height:28px;color:#333333;text-align:center;}", //按钮样式
            button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT, //背景透明
            button_action: btnAction, //单文件或多文件上传
            button_cursor: SWFUpload.CURSOR.HAND, //指针手形
            flash_url: p.flashurl, //Flash路径
            custom_settings: {
                "upload_target": parentObj,
                "button_action": btnAction
            },
            debug: false
        });
    }
	//简单
	$.fn.InitSWFeUpload = function (p) {
        p = $.extend({}, $.swfUpLoadDefaults.property, p || {});
        //创建上传按钮
        var parentObj = $(this);
        var parentBtnId = "upload_span_" + Math.floor(Math.random() * 1000 + 1);
        parentObj.append('<div class="upload-btn"><span id="' + parentBtnId + '"></span></div>');
        //初始化属性
        var btnAction = SWFUpload.BUTTON_ACTION.SELECT_FILES; //多文件上传

        p.sendurl += "/Admin/Public/upload?State=1";
        if (p.single) {
            btnAction = SWFUpload.BUTTON_ACTION.SELECT_FILE; //单文件上传
        }
        if (p.water) {
            p.sendurl += "&IsWater=1";
        }
        if (p.thumbnail) {
            p.sendurl += "&IsThumbnail=1";
        }

        //初始化上传控件
        var swfu = new SWFUpload({
            post_params: { "ASPSESSID": "NONE" },
            upload_url: p.sendurl, //上传地址
            file_size_limit: p.filesize, //文件大小
            file_types: p.filetypes, //文件类型
            file_types_description: "JPG Images",
            file_upload_limit: "0", //一次能上传的文件数量

            file_queue_error_handler: fileQueueError,
            file_dialog_complete_handler: fileDialogComplete,
            upload_progress_handler: uploadProgress,
            upload_error_handler: uploadError,
            upload_success_handler: uploadeSuccess,
            upload_complete_handler: uploadComplete,

            button_placeholder_id: parentBtnId, //指定一个dom元素
            button_width: p.btnwidth, //上传按钮的宽度
            button_height: p.btnheight, //上传按钮的高度
            button_text: '<span class="btnText">' + p.btntext + '</span>', //上传按钮的文字
            button_text_style: ".btnText{font-family:Microsoft YaHei;font-size:12px;line-height:28px;color:#333333;text-align:center;}", //按钮样式
            button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT, //背景透明
            button_action: btnAction, //单文件或多文件上传
            button_cursor: SWFUpload.CURSOR.HAND, //指针手形
            flash_url: p.flashurl, //Flash路径
            custom_settings: {
                "upload_target": parentObj,
                "button_action": btnAction
            },
            debug: false
        });
    }

    //商品选择
    

    //商品选择结束
});

//==================================以下是上传时处理事件===================================
//当选择文件对话框关闭消失时发生
function fileQueueError(file, errorCode, message) {
    try {
        switch (errorCode) {
            case SWFUpload.errorCode_QUEUE_LIMIT_EXCEEDED:
                alert("你选择的文件太多！");
                break;
            case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
                alert(file.name + "文件太小！");
                break;
            case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
                alert(file.name + "文件太大！");
                break;
            case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
                alert(file.name + "文件类型出错！");
                break;
            default:
                if (file !== null) {
                    alert("出现未知错误！");
                }
                break;
        }

    } catch (ex) {
        this.debug(ex);
    }
}
//当选择文件对话框关闭，所有文件已经处理完成时发生
function fileDialogComplete(numFilesSelected, numFilesQueued) {
    try {
        if (numFilesQueued > 0) {
            //如果是单文件上传，把旧的文件地址传过去
            if (this.customSettings.button_action == SWFUpload.BUTTON_ACTION.SELECT_FILE) {
                this.setPostParams({
                    "DelFilePath": $(this.customSettings.upload_target).siblings(".upload-path").val()
                });
            }
            this.startUpload();
            createHtmlProgress(this); //创建进度条
        }
    } catch (ex) {
        this.debug(ex);
    }
}
//flash定时触发，定时更新页面中的UI元素达到及时显示上传进度的效果
function uploadProgress(file, bytesLoaded) {
    try {
        var percent = Math.ceil((bytesLoaded / file.size) * 100);
        var progressObj = $(this.customSettings.upload_target).children(".upload-progress");
        progressObj.children(".txt").html(file.name);
        progressObj.find(".bar b").width(percent + "%");
    } catch (ex) {
        this.debug(ex);
    }
}
//上传被终止或者没有成功完成时触发
function uploadError(file, errorCode, message) {
    var progress;
    try {
        switch (errorCode) {
            case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
                try {
                    var progressObj = $(this.customSettings.upload_target).children(".upload-progress");
                    progressObj.children(".txt").html("上传被取消：Cancelled");
                }
                catch (ex1) {
                    this.debug(ex1);
                }
                break;
            case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
                try {
                    var progressObj = $(this.customSettings.upload_target).children(".upload-progress");
                    progressObj.children(".txt").html("上传被停止：Stopped");
                }
                catch (ex2) {
                    this.debug(ex2);
                }
            case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
                alert(message + "SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED");
                break;
            default:
                alert(message + "未知！");
                break;
        }
    } catch (ex3) {
        this.debug(ex3);
    }
}
//文件上传的处理已经完成且服务端返回了200的HTTP状态时触发
function uploadSuccess(file, serverData) {
    try {
        var jsonstr = eval('(' + serverData + ')');
        if (jsonstr.status == '0') {
            var progressObj = $(this.customSettings.upload_target).children(".upload-progress");
            progressObj.children(".txt").html(jsonstr.msg);
        }
        if (jsonstr.status == '1') {
            //如果是单文件上传，则赋值相应的表单
            if (this.customSettings.button_action == SWFUpload.BUTTON_ACTION.SELECT_FILE) {
                $(this.customSettings.upload_target).siblings(".upload-name").val(jsonstr.name);
                $(this.customSettings.upload_target).siblings(".upload-path").val(jsonstr.path);
                $(this.customSettings.upload_target).siblings(".upload-size").val(jsonstr.size);
				$(this.customSettings.upload_target).siblings(".upload-img").attr('src',jsonstr.path);
            } else {
                addImage($(this.customSettings.upload_target), jsonstr.path, jsonstr.thumb);
            }
            var progressObj = $(this.customSettings.upload_target).children(".upload-progress");
            progressObj.children(".txt").html("上传成功：" + file.name);
        }
    } catch (ex) {
        this.debug(ex);
    }
}

//简单 文件上传的处理已经完成且服务端返回了200的HTTP状态时触发
function uploadeSuccess(file, serverData){
	try {
		var jsonstr = eval('(' + serverData + ')');
		if (jsonstr.status == '0') {
			var progressObj = $(this.customSettings.upload_target).children(".upload-progress");
			progressObj.children(".txt").html(jsonstr.msg);
		}
		if (jsonstr.status == '1') {
			//如果是单文件上传，则赋值相应的表单
			if (this.customSettings.button_action == SWFUpload.BUTTON_ACTION.SELECT_FILE) {
				$(this.customSettings.upload_target).siblings(".upload-name").val(jsonstr.name);
				$(this.customSettings.upload_target).siblings(".upload-path").val(jsonstr.path);
				$(this.customSettings.upload_target).siblings(".upload-size").val(jsonstr.size);
				$(this.customSettings.upload_target).siblings(".upload-img").attr('src', jsonstr.path);
			}
			else {
				addeImage($(this.customSettings.upload_target), jsonstr.path, jsonstr.thumb);
			}
			var progressObj = $(this.customSettings.upload_target).children(".upload-progress");
			progressObj.children(".txt").html("上传成功：" + file.name);
		}
	} 
	catch (ex) {
		this.debug(ex);
	}
}
	
//当上传队列中的文件已完成时，无论成功(uoloadSuccess触发)或失败(uploadError触发)，此事件都会被触发
function uploadComplete(file) {
    try {
        if (this.getStats().files_queued > 0) {
            this.startUpload();
        } else {
            var progressObj = $(this.customSettings.upload_target).children(".upload-progress");
            progressObj.children(".txt").html("全部上传成功");
            progressObj.remove();
        }
    } catch (ex) {
        this.debug(ex);
    }
}

//==================================以上是上传时处理事件===================================
//创建上传进度条
function createHtmlProgress(swfuInstance) {
    //判断显示进度的DIV是否存在，不存在则创建
    var targetObj = $(swfuInstance.customSettings.upload_target);
    var fileProgressObj = $('<div class="upload-progress"></div>').appendTo(targetObj);
    var progressText = $('<span class="txt">正在上传，请稍候...</span>').appendTo(fileProgressObj);
    var progressBar = $('<span class="bar"><b></b></span>').appendTo(fileProgressObj);
    var progressCancel = $('<a class="close" title="取消上传">关闭</a>').appendTo(fileProgressObj);
    //绑定点击事件
    progressCancel.click(function () {
        swfuInstance.stopUpload();
        fileProgressObj.remove();
    });
}

//======================================图片相册处理事件======================================
//添加图片相册
function addImage(targetObj, originalSrc, thumbSrc) {
    //插入到相册UL里面
    var newLi = $('<li>'
	+ '<input type="hidden" name="user_simg[]" value="' +originalSrc+ '" />'
   // + '<input type="hidden" name="user_simg[]" value="0|' + originalSrc + '|' + thumbSrc + '" />'
    + '<input type="hidden" name="user_desc[]" value="" />'
    + '<div class="img-box" onclick="setFocusImg(this);">'
    + '<img src="' + thumbSrc + '" bigsrc="' + originalSrc + '" />'
    + '<span class="remark"><i>暂无描述...</i></span>'
    + '</div>'
    + '<a href="javascript:;" onclick="setRemark(this);">描述</a>'
    + '<a href="javascript:;" onclick="delImg(this);">删除</a>'
    + '</li>');
    newLi.appendTo(targetObj.siblings(".photo-list").children("ul"));

    //默认第一个为相册封面
    var focusPhotoObj = targetObj.siblings(".focus-photo");
    if (focusPhotoObj.val() == "") {
        focusPhotoObj.val(thumbSrc);
        newLi.children(".img-box").addClass("selected");
    }
}

//添加无属性图片
function addeImage(targetObj, originalSrc, thumbSrc) {
    //插入到相册UL里面
     if(targetObj.siblings(".photo-list1").children('ul').find('li').eq(0).find('input').size()>0){
       var val=targetObj.siblings(".photo-list1").children('ul').find('li').eq(0).find('input').val();
       targetObj.siblings(".photo-list1").children('ul').find('li').eq(0).find('input').val((val!=''?(val+','):'')+originalSrc);
    }else{
     targetObj.siblings(".photo-list1").children('ul').find('li').eq(0).html('<input type="hidden" name="user_simg[]" value="' +originalSrc+ '" />')
    }
    var newLi = $('<li>'
//	+ '<input type="hidden" name="user_simg[]" value="' +originalSrc+ '" />'
   // + '<input type="hidden" name="user_simg[]" value="0|' + originalSrc + '|' + thumbSrc + '" />'
   // + '<input type="hidden" name="user_desc[]" value="" />'
    + '<div class="img-box" onclick="setFocusImg(this);">'
    + '<img src="' + thumbSrc + '" bigsrc="' + originalSrc + '" />'
    + '</div>'
    + '<a href="javascript:;" onclick="delImg(this);">×</a>'
    + '</li>');
    newLi.appendTo(targetObj.siblings(".photo-list1").children("ul"));

    //默认第一个为相册封面
    var focusPhotoObj = targetObj.siblings(".focus-photo");
    if (focusPhotoObj.val() == "") {
        focusPhotoObj.val(thumbSrc);
        newLi.children(".img-box").addClass("selected");
    }
}

//设置相册封面
function setFocusImg(obj) {
    var src=$(obj).find('img').attr('bigsrc');
      var html='<div class="imgin" style=" display:none;position: fixed;  top: 20%;  height: 60%;  width: 50%;  left: 25%;  z-index: 10;"><div style="    position: relative;    height: 0;  width:600px;margin:0 auto;  max-width: 100%;"> <em  onclick="close__()" style="    position: absolute;  cursor: pointer;    display: block;    right: -20px;    top: -20px;    width: 40px;    height: 40px;    background-color: #FFAC0F;    font-size: 42px;    text-align: center;    border-radius: 50%;    font-style: normal;    line-height: 35px;">×</em></div><img style="width:600px;max-width:100%;display:block;margin:0 auto;" src=""/></div><div class="zc" style="    display: block;    position: fixed;display:none; top:0;left:0;   width: 100%;    height: 100%;    background-color: #333;    z-index: 8;    opacity: .8;"></div>';
    $('body').append(html);
    $('.imgin').find('img').attr('src',src);
    $('.imgin,.zc').fadeIn();
    var focusPhotoObj = $(obj).parents(".photo-list").siblings(".focus-photo");
    focusPhotoObj.val($(obj).children("img").eq(0).attr("src"));
    $(obj).parent().siblings().children(".img-box").removeClass("selected");
    $(obj).addClass("selected");
}

//设置图片描述
function setRemark(obj) {
    var parentObj = $(obj); //父对象
    var hidRemarkObj = parentObj.prevAll("input[name='user_desc[]']").eq(0); //取得隐藏值
    var m = $.dialog({
        lock: true,
        max: false,
        min: false,
        padding: 0,
        title: "图片描述",
        content: '<textarea id="ImageRemark" style="margin:10px 0;font-size:12px;padding:3px;color:#000;border:1px #d2d2d2 solid;vertical-align:middle;width:300px;height:50px;">' + hidRemarkObj.val() + '</textarea>',
        button: [{
            name: '批量描述',
            callback: function () {
                var remarkObj = $('#ImageRemark', parent.document);
                if (remarkObj.val() == "") {
                    $.dialog.alert('总该写点什么吧？', function () {
                        remarkObj.focus();
                    }, m);
                    return false;
                }
                parentObj.parent().parent().find("li input[name='user_desc[]']").val(remarkObj.val());
                parentObj.parent().parent().find("li .img-box .remark i").html(remarkObj.val());
            }
        }, {
            name: '单张描述',
            callback: function () {
                var remarkObj = $('#ImageRemark', parent.document);
                if (remarkObj.val() == "") {
                    $.dialog.alert('总该写点什么吧？', function () {
                        remarkObj.focus();
                    }, m);
                    return false;
                }
                hidRemarkObj.val(remarkObj.val());
                parentObj.siblings(".img-box").children(".remark").children("i").html(remarkObj.val());
            },
            focus: true
        }]
    });
}
//删除图片LI节点
function delImg(obj) {
    var parentObj = $(obj).parent().parent();
    var index_= $(obj).parent().index()-1;
    var val=parentObj.parent().children().eq(0).find('input').val();
    var aval=val.split(',');
    aval.splice(index_,1);
    parentObj.parent().children().eq(0).find('input').val(aval.join(','));
    var focusPhotoObj = parentObj.parent().siblings(".focus-photo");
    var smallImg = $(obj).siblings(".img-box").children("img").attr("src");
    $(obj).parent().remove(); //删除的LI节点
    //检查是否为封面
    if (focusPhotoObj.val() == smallImg) {
        focusPhotoObj.val("");
        var firtImgBox = parentObj.find("li .img-box").eq(0); //取第一张做为封面
        firtImgBox.addClass("selected");
        focusPhotoObj.val(firtImgBox.children("img").attr("src")); //重新给封面的隐藏域赋值
    }
}
function close__(){
    $('.imgin,.zc').fadeOut(500);
    setTimeout(function(){
        $('.imgin,.zc').remove();
    },510)

}


$(function (){
setTimeout(function(){
var shoplist = [[]]; //生命一个数组用来放类型
var shoplistbak = [[]]; //生命一个数组用来放类型
//对类型进行循环绑定
var obj = '.boxwrap'; //共有的类型的类名
var className = 'selected'; //变化的class
var item = 'a';//子类
var objParents = 'dl';//操作元素的父级
var html_th = $('.ltable tr').eq(0).clone(); //列表的头部信息
var html_txt = $('.ltable tr').eq(1).clone(true); //列表的头部信息
var html_container = $('.ltable');
var html_inner = $('.ltable tr');
var inforname= $('.ltable tr').eq(1).find('.input.baifen').val();
$('.ltable tr').eq(1).find('.photo-list1 li').eq(0).html('<input type="hidden" name="user_simg[]" value="" />');
var arr = '';

$(obj).each(function () { //循环绑定事件，初始化数组值；
  arr = arr + '_';
    $(this).find(item).eq(0).addClass(className);
  $(this).on('click', item, function () {
    var index = $(this).closest(objParents).index();
    var $this=$(this);
    if ($(this).index() == 0) {
		$(this).parent().next().find('input').prop('checked',true);
		$(this).parent().next().siblings().find('input').prop('checked',false);
		$(this).addClass(className).siblings().removeClass(className);
    } else {
	$(this).parent().next().find('input').prop('checked',false);
      $(this).parent().find(item).eq(0).removeClass(className);
    }
   if($(this).parent().find(item+'.'+className).size()<=0){
    $(this).parent().find(item).eq(0).addClass(className);
	$(this).parent().next().find('input').prop('checked',true);
   }
    shopChange($this);
  });
  shoplist[0].push('');
  shoplistbak[0].push('');
});

function shopChange(obj) {
 showloading();
  var index = obj.closest(objParents).index() - $('.boxwrap').closest('dl').parent().find('dl').first().index();
  var index_ = obj.index();
  var index_val = obj.html();
  var id_=obj.parent().siblings().eq(index_).find('input').val();
  var he=index_val+'='+id_;
  var libak=shoplist.concat();
  if(obj.index()!=0){
      if(obj.hasClass(className)){
       select(index, he, libak);
    }else{
        delet(index,he,libak);
    }
  }else{
    kong(index,'', libak)
  }
   hideloading();
}
function select(d,v,a){
  var aa = [];
  var bb = '';
  var dd = true;
  var aaa;
  var ab=a.concat();
  var ac=a.concat();
 for(var i=0;i<ab.length;i++){
  if(ab[i][d] && ab[i][d]!='' && dd){//选个一个有值的
      bb=ab[i][d];
      dd=false;
      var kk=ab[i].join("_");
      var akk=kk.split("_");
      akk[d]=v;
  }
    if (!dd && ab[i][d] == bb) { //把这个值都选出来
      var kk=ab[i].join("_");
      var akk=kk.split("_");
      akk[d]=v;
      aa.push(akk);
    }
};
if(dd){
  var all=[];
  for(var j=0;j<ac.length;j++){
       var kk=ac[j].join("_");
      var akk=kk.split("_");
      akk[d]=v;
      all.push(akk);
    };
    aaa=all.concat();
    alter(all);
}else{
  addlist(aa);
   aaa=shoplist.concat(aa);
}
      shoplist=aaa.concat();
  //  console.log(shoplist);
}
//选择不使用时
function kong(d,v,a){
  var ab=a.concat();
  var appy=a.concat();
  var io='';
       for(var i=0;i<ab.length;i++){
        if(ab[i][d] && ab[i][d]!=''){//选个一个有值的
          io=ab[i][d];
         break;
      }
    }
        for(var i=0;i<appy.length;i++){
          if(appy[i][d]!=io &&  $('.ltable tr').size()>2){
            appy.splice(i,1);
            appy=typeof appy[0] !='object'?shoplistbak.concat():appy;
              $('.ltable tr').eq(i+1).prev().remove();
              $('.ltable tr').eq(i+1).remove();
            i--;
         }else{
          appy[i][d]=''; $('.ltable tr').eq(i+1).find('.input.baifen').val($('.ltable tr').eq(i+1).find('.input.baifen').val().replace(io.split('=')[0],''));
          $('.ltable tr').eq(i+1).prev().val(fuzhiPhp(appy[i]));
         }
          }
		 
  shoplist=typeof appy[0] !='object'?shoplistbak.concat():appy.concat();
}
// 在替换值
function alter(at){
  for (var i =0 ; i <=at.length - 1; i++) {
        $('.ltable tr').eq(i+1).find('.input.baifen').val(inforname+fuzhiHtml(at[i]));
        $('.ltable tr').eq(i+1).prev().val(fuzhiPhp(at[i]));
  }
}
//增加值
function addlist(arry){
  for (var i =0 ; i <=arry.length - 1; i++) {
    //a.find('.input.baifen').val(arry[i].join(''));
     $('.ltable tbody').append("<input type='hidden' name='tags[]' value='"+fuzhiPhp(arry[i])+"'>");
     $('.ltable tbody').append("<tr>"+html_txt.html()+"</tr>");

      
      $('.ltable tr').last().find('.input.baifen').val(inforname+fuzhiHtml(arry[i]));
      $('.ltable tr').last().find('td .btn-tools a.red').trigger('click');
      if($('.ltable tr').last().find(".upload-album").size()>0){
	    $('.ltable tr').last().find(".upload-album .upload-btn").remove();
	    $('.ltable tr').last().find(".upload-album").InitSWFeUpload({ btntext: "批量上传", btnwidth: 66, single: false, water: true, thumbnail: true, filesize: "2048", sendurl: "/Admin/Public/upload", flashurl: "/Public/js/scripts/swfupload/swfupload.swf", filetypes: "*.jpg;*.jpge;*.png;*.gif;" });
	    $('.ltable tr').last().find('.photo-list1 li').eq(0).html('<input type="hidden" name="user_simg[]" value="" />');
      }
  };
}
/*赋值>后台拿的数据*/
function fuzhiPhp(a){
		  	var appy__=[];
			for (var j = 0; j < a.length; j++) {
				var kk = a[j].split('=');
				appy__.push(kk[kk.length-1]);
			}
		  	return appy__.join('_')
		  }
/*赋值>前台显示的数据*/
function fuzhiHtml(a){
		  	var appy__=[];
			for (var j = 0; j < a.length; j++) {
				var kk = a[j].split('=');
				appy__.push(kk[0]);
			}
		  	return appy__.join(' ')
		  }
/*删除*/
function delet(id,val,arr){
  var appy=arr.concat();
  for(var i=0;i<appy.length;i++){
      if(appy[i][id]==val){
      if(!$(obj).eq(id).find(item).eq(0).hasClass(className)){
        appy.splice(i,1);
        appy=typeof appy[0] !='object'?shoplistbak.concat():appy;
       if( $('.ltable tr').size()>2){
          $('.ltable tr').eq(i+1).prev().remove();
          $('.ltable tr').eq(i+1).remove();
        }else{
         $('.ltable tr').eq(1).find('.input.baifen').val($('.ltable tr').eq(1).find('.input.baifen').val().replace(val.split('=')[0],' '));
          $('.ltable tr').eq(1).prev().val(fuzhiPhp(appy[i]));
       }
     }else{
      appy[i][id]=''; $('.ltable tr').eq(i+1).find('.input.baifen').val($('.ltable tr').eq(i+1).find('.input.baifen').val().replace(val.split('=')[0],' '));
        $('.ltable tr').eq(i+1).prev().val(fuzhiPhp(appy[i]));

    };
        i--;
      }
  }
 // console.log(appy);
  shoplist=typeof appy[0] !='object'?shoplistbak.concat():appy.concat();
}
function showloading(){
      var html='<div class="imgin" style=" display:none;position: fixed;  top: 30%;  height: 60%;  width: 50%;  left: 25%;  z-index: 10;"><img style="max-width:100%;display:block;margin:0 auto;" src="http://img.ui.cn/data/file/8/7/3/24378.gif?imageView2/2/q/90"/></div><div class="zc" style="    display: block;    position: fixed;display:none; top:0;left:0;   width: 100%;    height: 100%;    background-color: #fff;    z-index: 8;    opacity: .5;"></div>';
      $('body').append(html);
     $('.imgin,.zc').fadeIn(200);
}
function hideloading(){
   setTimeout(function(){
  $('.imgin,.zc').fadeOut(400);
        $('.imgin,.zc').remove();
    },210);   
   setTimeout(function(){
        $('.imgin,.zc').remove();
    },610);

}
},100)
})