<?php
namespace Admin\Controller;

class OrderController extends CommonController {
	public function index(){
		//接收查询数据
		$table = M('order'); // 实例化User对象
		if (I('get.keyword')){
			$keyword = I('get.keyword');
			$data['t_order.orderid'] = array('like',"%{$keyword}%");
		}
		if (I('get.verify')){		
			$data['t_order.state'] = I('get.verify');
			$this->assign('verify',I('get.verify'));
		}
		$starttime=strtotime(I('get.starttime'));
		$stoptime=strtotime(I('get.stoptime').' 23:59:59');
		if($starttime && $stoptime){
			$data['t_order.addtime'] = array(array('egt',$starttime),array('elt',$stoptime));
		}
		
		$count = $table->where($data)->count();
		$Page       = new \Think\Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$table=$table->field("t_order.*,t_user.phone,t_user_coupons.title,t_user_coupons.man,t_user_coupons.jian")
		->join('left join t_user on t_user.id = t_order.uid')
		->join('left join t_user_coupons on t_user_coupons.id = t_order.coupons')
		->where($data)->order('t_order.addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('data',$table);	
		$show       = $Page->show();// 分页显示输出
		$this->assign('page',$show);// 赋值分页输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		if($action == 'export'){
			if(!$table){
				$this->error('没有搜索结果，无法导出数据');
			}
			$this->goods_export($table);
		}
		$this->display(); // 输出模板			
	}
	
	public function edit(){
		$hua['hui']=I('get.hui');
		$this->assign('hua',$hua);
		$id = I('get.id');
		$table=M('order');
		if (IS_POST){
			$data       = $_POST;
			if ($table->save($data)){
				alertLocation('修改成功！', '/Admin/Horder');
			}else {
				$this->error('修改失败！');
			}
		}
		$order=$table->where("t_order.id = $id")->find();
		$this->assign($order);
		$this->display();
	}
	
	public function state(){
		$data = I('get.');			
		$table = M('Order');
		if ($table->save($data)){
			$this->redirect("/Admin/Order");
		}else {
			$this->error('没有任何修改！');
		}
	}
	
	public function delete(){		
		$post = implode(',',$_POST['id']);	
		$table = M('order');
		$data = $table->delete($post);
		if ($data){
			echo '删除成功！';
		}else {
			echo '删除失败！';
		}
	}
	
	public function EditOrderRemark(){
		$table = M('service_request');
		$where['ID'] = I('post.order_no');
		$where['NOTE'] = I('post.remark');
		if ($table->save($where)){
			$data['status'] = 1;
			$data['msg'] = '提示：修改成功！';		
			echo json_encode($data);
		}else {
			$data['msg'] = '提示：没有任何修改！';
			echo json_encode($data);
		}
	}
	
	public  function ajax(){
		$cid=I('post.cid');
		$price=I('post.price');
	    $goods=M('goods');
		$goods=$goods->where("id='{$cid}'")->find();
		$order=M('order');
		$order=$order->where("cid='{$cid}'")->sum('price');
		$sheng=$goods['sum']-$order;
		if($sheng<=0){
			echo 3;
		}elseif($price>$sheng){
			echo 2;
		}elseif($goods['sum']<=$price){
			echo 1;
		}else{
			echo 0;
		}	
		
	  }
	 public function chan(){
	 	$cid=I('get.cid');
	 	$data=time();
	 	$goods=M('goods');
	 	$goods=$goods->where("id='{$cid}'")->find();
	 	if($data<$goods['startime']){
	 		echo 0;
	 	 }elseif($data>$goods['stoptime']){
	 	 	echo 1;
	 	  }
	    } 
	  
	public function EditRealAmount(){
		$table = M('service_request_bid');		
		$request_id = I('post.order_no');
		$net_total = $table->where("REQUEST_ID = $request_id and (BID_STATUS = 2 or BID_STATUS =4)")->find();
		$where['DISCOUNT'] = I('post.real_amount');
		$where['NET_TOTAL'] = $net_total['total_before_discount'] - I('post.real_amount');
		if ($table->where("REQUEST_ID = $request_id and (BID_STATUS = 2 or BID_STATUS =4)")->save($where)){
			$data['status'] = 1;
			$data['msg'] = '提示：修改成功！';
			echo json_encode($data);
		}else {
			$data['msg'] = '提示：没有任何修改！';
			echo json_encode($data);
		}
	}
	
	public function OrderCancel(){
		$table = M('service_request');
		$where['ID'] = I('post.order_no');
		$where['REQUEST_STATUS_ID'] = I('post.check_revert');
		if ($table->save($where)){
			$data['status'] = 1;
			$data['msg'] = '提示：取消成功！';
			echo json_encode($data);
		}else {
			$data['msg'] = '提示：已经是取消状态！';
			echo json_encode($data);
		}
	}
	
	//导出数据方法
	protected function goods_export($table=array())
	{
		//print_r($goods_list);exit;
		$goods_list = $table;
		$data = array();
		foreach ($goods_list as $k=>$goods_info){
			$data[$k][id] = $goods_info['id'];
			$data[$k][username] = $goods_info['username'];
			$data[$k][title] = $goods_info['title'];
			$data[$k][price] = $goods_info['price'];
			$data[$k][uname]  = $goods_info['uname'];
			$data[$k][dname]  = $goods_info['dname'];
			$data[$k][email]  = $goods_info['email'];
			$data[$k][addtime]  =date("Y-m-d,H:i:s",$goods_info['addtime']);
			$state  = $goods_info['state'];
			$data[$k][state]  = $goods_info['state'];
			if ($state=="2"){
				$data[$k][state]='已撤销';
			}elseif($state=="3"){
				$data[$k][state]='已通过';
			}elseif($state=="4"){
				$data[$k][state]='未通过';
			}elseif($state=="5"){
				$data[$k][state]='已回访';
			}elseif($state=="6"){
				$data[$k][state]='已回收';
			}else{
				$data[$k][state]='审核中';
			}
	
		}
		foreach ($data as $field=>$v){
			if($field == 'id'){
				$headArr[]='订单编号';
			}
	
			if($field == 'username'){
				$headArr[]='客户姓名';
			}
	
			if($field == 'title'){
				$headArr[]='产品名称';
			}
	
			if($field == 'price'){
				$headArr[]='金额';
			}
	
			if($field == 'uname'){
				$headArr[]='提交人';
			}
	
			if($field == 'dname'){
				$headArr[]='员工编号';
			}
	
			if($field == 'email'){
				$headArr[]='客户邮箱';
			}
			if($field == 'addtime'){
				$headArr[]='提交时间';
			}
	
			if($field == 'state'){
				$headArr[]='状态';
			}
		}
	
		$filename="goods_list";
	
		$this->getExcel($filename,$headArr,$data);
	}
	
	
	private  function getExcel($fileName,$headArr,$data){
		//导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
		import("Org.Util.PHPExcel");
		import("Org.Util.PHPExcel.Writer.Excel5");
		import("Org.Util.PHPExcel.IOFactory.php");
	
		$date = date("Y_m_d",time());
		$fileName .= "_{$date}.xls";
	
		//创建PHPExcel对象，注意，不能少了\
		$objPHPExcel = new \PHPExcel();
		$objProps = $objPHPExcel->getProperties();
	
		//设置表头
		$key = ord("A");
		//print_r($headArr);exit;
		foreach($headArr as $v){
			$colum = chr($key);
			$objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
			$objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
			$key += 1;
		}
	
		$column = 2;
		$objActSheet = $objPHPExcel->getActiveSheet();
	
		//print_r($data);exit;
		foreach($data as $key => $rows){ //行写入
			$span = ord("A");
			foreach($rows as $keyName=>$value){// 列写入
				$j = chr($span);
				$objActSheet->setCellValue($j.$column, $value);
				$span++;
			}
			$column++;
		}
	
		$fileName = iconv("utf-8", "gb2312", $fileName);
	
		//重命名表
		//$objPHPExcel->getActiveSheet()->setTitle('test');
		//设置活动单指数到第一个表,所以Excel打开这是第一个表
		$objPHPExcel->setActiveSheetIndex(0);
		ob_end_clean();//清除缓冲区,避免乱码
		header('Content-Type: application/vnd.ms-excel');
		header("Content-Disposition: attachment;filename=\"$fileName\"");
		header('Cache-Control: max-age=0');
	
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output'); //文件通过浏览器下载
		exit;
	}
	
	
	
} 