<?php
namespace Admin\Controller;

class LogController extends CommonController {
	
	public function index(){
		
		$table = M('vehicle_maintenance_log'); // 实例化User对象
		
		$count      = $table->count();// 查询满足要求的总记录数
		$Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show       = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$data = $table->field('owner.PHONE,vehicle_maintenance_log.ID,vehicle_maintenance_log.SERVICE_DATE,vehicle_maintenance_log.SERVICE_TYPE_ID,vehicle_model_type.MODEL_TYPE,vehicle_model_year.MODEL_YEAR,vehicle_model.MODEL,vehicle_mfg.MFG_ORIG,vehicle_mfg.MFG')
		->join('left join owner_vehicle on owner_vehicle.ID = vehicle_maintenance_log.VEHICLE_ID')
		->join('left join owner on owner_vehicle.OWNER_ID = owner.ID')		
		->join("left join vehicle_model_type ON vehicle_model_type.ID = owner_vehicle.VEHICLE_MODEL_TYPE_ID")
		->join("left join vehicle_model_year ON vehicle_model_year.ID = vehicle_model_type.MODEL_YEAR_ID")
		->join("left join vehicle_model ON vehicle_model.ID = vehicle_model_year.MODEL_ID")
		->join("left join vehicle_mfg ON vehicle_mfg.ID = vehicle_model.MFG_ID")
		->limit($Page->firstRow.','.$Page->listRows)->order('vehicle_maintenance_log.SERVICE_DATE desc')->select();
		if ($data){
			foreach ($data as $key => $val){
				$res[$key]['id'] = $val['id'];
				$res[$key]['phone'] = $val['phone'];
				$res[$key]['title'] = $val['mfg'].' '.$val['model'].' '.$val['model_year'].' '.$val['model_type'];
				$res[$key]['service_date'] = $val['service_date'];
				$service_type = M('service_type');
				$map['ID'] = array('in',$val['service_type_id']);
				$type = $service_type->where($map)->getField('title',true);
				$res[$key]['service_type'] = implode(',', $type);
			}
		}
		$this->assign('data',$res);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		$this->display(); // 输出模板			
	}	
	
	public function add(){
		if (IS_POST){
			$table = M('Vehicle_mfg');
			if ($table->add(I('post.'))){
				alertLocation('添加成功！', '/Admin/Mfg');
			}else {
				$this->error('添加失败！');
			}		
		}
		$this->display();
	}
	
	public function edit(){
		$id = I('get.id');
		if (IS_POST){
			$table = M('Vehicle_mfg');
			if ($table->save(I('post.'))){
				alertBack('修改成功！');
			}else {
				$this->error('没有任何修改！');
			}			
		}
		$table = M('Vehicle_mfg');
		$data = $table->where("id = $id")->find();
		$this->assign($data);
		$this->display('');
	}
	
//	public function state(){
//		$data = I('get.');			
//		$user = M('User');
//
//		if ($user->save($data)){
//			$this->redirect("/Admin/User/show");
//		}else {
//			$this->error('没有任何修改！');
//		}
//	}

	
	public function delete(){		
		$post = implode(',',$_POST['id']);	
		$table = M('vehicle_maintenance_log');
		$data = $table->delete($post);
		if ($data){
			echo '删除成功！';
		}else {
			echo '删除失败！';
		}
	}

	
} 