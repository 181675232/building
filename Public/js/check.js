//$(function(){
//	$('form input[type=submit]').click(function(){
//		$.ajax({
//			type:'POST',
//			url:'user.php',
//			data:$('form').serialize(),
//			success:function(response,status,xhr){
//				alert(response);
//			}
//		});
//	});
//});

//判断正整数  
function checkNum(num)  
{  
     var re = /^[1-9]+[0-9]*]*$/;
     if (re.test(num))  
     {  
        return false;
     }else{
	 	return true;
	 }  
}  

function manager(){
	$('input[name=code]');
	if($('input[name=name]').val() == '' || $('input[name=password]').val().length < 6){		
		$('#msgtip').html('<span style="color:red">用户名或密码输入有误！</span>');
		return false;
	}
	var test;
	$.ajax({
		type:'POST',
		url:'/Admin/Public/login',
		data:$('form').serialize(),		
		async:false,
		success:function(response,status,xhr){
			if(response == 1){
				test = 1;
			}else{
				$('#msgtip').html('<span style="color:red">'+response+'</span>');
				$('#txtCode').attr("src","/admin/public/scode/"+Math.random());
			}
		},
	});
	
	if(!test){
		return false;
	}	
}

function order(id,ord,tab){
	if(checkNum(ord)){
		alert('请输入数字！');
		exit;
	}
	$.get('/Admin/'+tab+'/ajaxstate/id/'+id+'/ord/'+ord,function(data){
		if(data == 0){
			alert('没有任何修改！');
		}else{
			location.replace('/Admin/'+tab);
		}
	});

}

function check_mobile(){
	var phone = $("#mobile").val();
	var length=phone.length;
	if (length==0){
		$("#mobile_notice").html("手机号码不能为空！");
		$("#hid1").val("0"); 
		return;
	}
	var a=/^1[3|5|7|8|][0-9]{9}$/; 
	var b=a.test($("#mobile").val());
	if(!b){
		$("#mobile_notice").html("手机号码不正确！");
		$("#hid1").val("0"); 
		return;
	}else{ 
		$.get("?c=member&m=ajax&mobile="+phone,function(data,textState){
			if(data==0){
				$("#mobile_notice").html("<span style='color:green'>恭喜您！该手机号未被注册！</span>");
				$("#hid1").val("1");
				return;
			}else{
				$("#mobile_notice").html("该手机号码已被注册！");	
				$("#hid1").val("0");
				return;			 
			}
		});
	}
}

function check_code(val){
	$.get("?c=member&m=code&val="+val,function(data,textState){
		if(data=='1'){
			$("#code_notice").html("<span style='color:green'>正确</span>");
			return;
		}else{
			$("#code_notice").html(data);	
			return;	 
		}
	});	
}
function check_password(val){

	if(val.length >= 6){
		$("#password_notice").html("<span style='color:green'>正确</span>");
		return;
	}else{
		$("#password_notice").html("密码必须大于6位！");
		return;
	}
}
function  checkdate(n){ 
	var checkb = document.getElementById("checkb").getElementsByTagName("input");;
		
	var checkedCount=0; 
	for(var i=0;i<checkb.length;i++){ 
		if(checkb[i].checked){ 
			  
			checkedCount++; 
		
		} 
	} 
	if(checkedCount>n){ 
	
		alert("擅长项目不能超过6个！"); 
		
		return false; 
	
	} 
}

function getSecond(){
	
	if($("#hid1").val()=="0"){
		$("#mobile_notice").html("请填写符合要求的手机号码在发送！");
	}else{
		add();//$("#div1").hide();$("#div2").show();
		$.get("?c=member&m=yzm&mobile="+$("#mobile").val(),function(data){
			$("#code_notice").html(data);	
			return;
		});
	}
}
var timerc=60; //全局时间变量（秒数）
function add(){ //加时函数 
  if(timerc >0){ //如果不到5分钟
	  timerc--; //时间变量自增1
	  $("#div2").html(Number(parseInt(timerc%60/10)).toString()+(timerc%10)+"秒后重新获取");
	  $("#div2").show();
	  $("#div1").hide();
	  setTimeout("add()", 1000); //设置1000毫秒以后执行一次本函数
  }else{
	  timerc=60;
	  $("#div2").hide();
	  $("#div1").show();
  };
};