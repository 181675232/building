<?php 
namespace Org\Util;
vendor("PHPExcel.PHPExcel");

class traverseDir{
	public $currentdir;//当前目录
	public $filename;//文件名
	public $fileinfo;//用于保存当前目录下的所有文件名和目录名以及文件大小
	public $savepath;
	public function __construct($curpath,$savepath){
		$this->currentdir=$curpath;//返回当前目录
		$this->savepath=$savepath;//返回当前目录
	}
	//遍历目录
	public function scandir($filepath){
		if (is_dir($filepath)){
			$arr=scandir($filepath);
			foreach ($arr as $k=>$v){
				$this->fileinfo[$v][]=$this->getfilesize($v);
			}
		}else {
			echo "<script>alert('当前目录不是有效目录');</script>";
		}
	}
	/**
	 * 返回文件的大小
	 *
	 * @param string $filename 文件名
	 * @return 文件大小(KB)
	 */
	public function getfilesize($fname){
		return filesize($fname)/1024;
	}
	/**
	 * 压缩文件(zip格式)
	 */
	public function tozip($items){
		$zip=new \ZipArchive();
		$zipname=date('YmdHis',time());
		if (!file_exists($zipname)){
			$zip->open($savepath.$zipname.'.zip',\ZipArchive::OVERWRITE);//创建一个空的zip文件
			$zip->addEmptyDir('voice/');
			

				$zip->addFile($this->currentdir.'/'.$items[0],$items[0]);
			
			for ($i=1;$i<count($items);$i++){
				$zip->addFile($this->currentdir.'/'.$items[$i],'voice/'.$items[$i]);
			}
			$zip->close();
			$dw=new download($zipname.'.zip',$savepath); //下载文件
			$dw->getfiles();
			unlink($savepath.$zipname.'.zip'); //下载完成后要进行删除
		}
	}
}
/**
 * 下载文件
 *
 */
class download{
	protected $_filename;
	protected $_filepath;
	protected $_filesize;//文件大小
	protected $savepath;//文件大小
	public function __construct($filename,$savepath){
		$this->_filename=$filename;
		$this->_filepath=$savepath.$filename;
	}
	//获取文件名
	public function getfilename(){
		return $this->_filename;
	}
	//获取文件路径（包含文件名）
	public function getfilepath(){
		return $this->_filepath;
	}
	//获取文件大小
	public function getfilesize(){
		return $this->_filesize=number_format(filesize($this->_filepath)/(1024*1024),2);//去小数点后两位
	}
	//下载文件的功能
	public function getfiles(){
		//检查文件是否存在
		if (file_exists($this->_filepath)){
			//打开文件
			$file = fopen($this->_filepath,"r");
			//返回的文件类型
			Header("Content-type: application/octet-stream");
			//按照字节大小返回
			Header("Accept-Ranges: bytes");
			//返回文件的大小
			Header("Accept-Length: ".filesize($this->_filepath));
			//这里对客户端的弹出对话框，对应的文件名
			Header("Content-Disposition: attachment; filename=".$this->_filename);
			//修改之前，一次性将数据传输给客户端
			echo fread($file, filesize($this->_filepath));
			//修改之后，一次只传输1024个字节的数据给客户端
			//向客户端回送数据
			$buffer=1024;//
			//判断文件是否读完
			while (!feof($file)) {
				//将文件读入内存
				$file_data=fread($file,$buffer);
				//每次向客户端回送1024个字节的数据
				echo $file_data;
			}
			fclose($file);
		}else {
			echo "<script>alert('对不起,您要下载的文件不存在');</script>";
		}
	}
}


class Excel{
    public function excel_find($data,$name){
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->createSheet();//创建新的内置表
        $objPHPExcel->setActiveSheetIndex(0);
        $objSheet = $objPHPExcel->getActiveSheet();

        //填充数据
        $objSheet->setCellValue("A1","罚款原因")
            ->setCellValue("B1","罚款类别")
            ->setCellValue("C1","发布人")
            ->setCellValue("D1","分包")
            ->setCellValue("E1","金额")
            ->setCellValue("F1","状态")
            ->setCellValue("G1","罚款时间");

        //设置默认行高
        $objSheet->getDefaultRowDimension()->setRowHeight(20);
        //所有垂直居中
        $objSheet->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置excel文件默认水平垂直方向居中
        //设置单元格宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
        //设置第二行行高
        //$objSheet->getRowDimension(1)->setRowHeight(20);
        //填充班级背景颜色
        $objSheet->getStyle("A1:G1")->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('6fc144');
        //设置单元格边框
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        //设置某列单元格格式为文本格式
        //$objSheet->getStyle("C2:C100")->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $j=2;
        foreach ($data as $key=>$val){

            //设置行高
            //$objPHPExcel->getActiveSheet()->getRowDimension($j)->setRowHeight(10);

            if($val['state']==1){
                $state = "未确认";
            }elseif($val['sex']==2){
                $state = "已确认";
            }else{
                $state = '已撤销';
            }

            $objSheet
                ->setCellValue("A".$j,$val['title'])
                ->setCellValue("B".$j,$val['group_name'])
                ->setCellValue("C".$j,$val['username'])
                ->setCellValue("D".$j,$val['uname'])
                ->setCellValue("E".$j,$val['price'].'元')
                ->setCellValue("F".$j,$state)
                ->setCellValue("G".$j,date("Y-m-d H:i:s",$val['addtime']));
            $j++;
        }



        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');//生成excel文件
        //$objWriter->save("./Public/excel/excel.xls");//保存文件
        //browser_excel('Excel5','excel.xls');//输出到浏览器
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器数据excel07文件
        header("Content-Disposition: attachment;filename=$name.xls");//告诉浏览器将输出文件的名称
        header('Cache-Control: max-age=0');//禁止缓存
        $objWriter->save("php://output");

    }

    public function excel_warning($data,$name){
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->createSheet();//创建新的内置表
        $objPHPExcel->setActiveSheetIndex(0);
        $objSheet = $objPHPExcel->getActiveSheet();

        //填充数据
        $objSheet->setCellValue("A1","预警标题")
            ->setCellValue("B1","类别")
            ->setCellValue("C1","发布人")
            ->setCellValue("D1","创建时间")
            ->setCellValue("E1","预警时间");

        //设置默认行高
        $objSheet->getDefaultRowDimension()->setRowHeight(20);
        //所有垂直居中
        $objSheet->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置excel文件默认水平垂直方向居中
        //设置单元格宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
        //设置第二行行高
        //$objSheet->getRowDimension(1)->setRowHeight(20);
        //填充班级背景颜色
        $objSheet->getStyle("A1:E1")->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('6fc144');
        //设置单元格边框
        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        //设置某列单元格格式为文本格式
        //$objSheet->getStyle("C2:C100")->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $j=2;
        foreach ($data as $key=>$val){

            //设置行高
            //$objPHPExcel->getActiveSheet()->getRowDimension($j)->setRowHeight(10);

            if($val['state']==1){
                $state = "未确认";
            }elseif($val['sex']==2){
                $state = "已确认";
            }else{
                $state = '已撤销';
            }

            $objSheet
                ->setCellValue("A".$j,$val['title'])
                ->setCellValue("B".$j,$val['group_name'])
                ->setCellValue("C".$j,$val['username'])
                ->setCellValue("D".$j,date("Y-m-d H:i:s",$val['addtime']))
                ->setCellValue("E".$j,date("Y-m-d H:i:s",$val['stoptime']));
//                ->setCellValue("F".$j,$state)
//                ->setCellValue("G".$j,date("Y-m-d H:i:s",$val['addtime']));
            $j++;
        }



        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');//生成excel文件
        //$objWriter->save("./Public/excel/excel.xls");//保存文件
        //browser_excel('Excel5','excel.xls');//输出到浏览器
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器数据excel07文件
        header("Content-Disposition: attachment;filename=$name.xls");//告诉浏览器将输出文件的名称
        header('Cache-Control: max-age=0');//禁止缓存
        $objWriter->save("php://output");

    }

    public function excel_daytask($data,$name){
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->createSheet();//创建新的内置表
        $objPHPExcel->setActiveSheetIndex(0);
        $objSheet = $objPHPExcel->getActiveSheet();

        //填充数据
        $objSheet->setCellValue("A1","任务标题")
            ->setCellValue("B1","发布人")
            ->setCellValue("C1","发布人电话")
            ->setCellValue("D1","执行人")
            ->setCellValue("E1","执行人电话")
            ->setCellValue("F1","任务位置")
            ->setCellValue("G1","任务完成度")
            ->setCellValue("H1","计划开始时间")
            ->setCellValue("I1","计划完成时间")
            ->setCellValue("J1","实际开始时间")
            ->setCellValue("K1","实际完成时间");

        //设置默认行高
        $objSheet->getDefaultRowDimension()->setRowHeight(20);
        //所有垂直居中
        $objSheet->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置excel文件默认水平垂直方向居中
        //设置单元格宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(25);
        //设置第二行行高
        //$objSheet->getRowDimension(1)->setRowHeight(20);
        //填充班级背景颜色
        $objSheet->getStyle("A1:K1")->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('6fc144');
        //设置单元格边框
        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        //设置某列单元格格式为文本格式
        //$objSheet->getStyle("C2:C100")->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $j=2;
        foreach ($data as $key=>$val){

            //设置行高
            //$objPHPExcel->getActiveSheet()->getRowDimension($j)->setRowHeight(10);


            $objSheet
                ->setCellValue("A".$j,$val['title'])
                ->setCellValue("B".$j,$val['username'].' ('.$val['name'].')')
                ->setCellValue("C".$j,$val['phone'])
                ->setCellValue("D".$j,$val['fusername'].' ('.$val['fname'].')')
                ->setCellValue("E".$j,$val['fphone'])
                ->setCellValue("F".$j,$val['building'].' '.$val['floor'].' '.$val['area'])
                ->setCellValue("G".$j,round($val['bai']*100).'%')
                ->setCellValue("H".$j,$val['starttime'])
                ->setCellValue("I".$j,$val['stoptime'])
                ->setCellValue("J".$j,$val['truestarttime'] == '0000-00-00 00:00:00' ? '未开始' : $val['truestarttime'])
                ->setCellValue("K".$j,$val['truestoptime'] == '0000-00-00 00:00:00' ? '未开始' : $val['truestoptime']);
            $j++;
        }



        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');//生成excel文件
        //$objWriter->save("./Public/excel/excel.xls");//保存文件
        //browser_excel('Excel5','excel.xls');//输出到浏览器
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器数据excel07文件
        header("Content-Disposition: attachment;filename=$name.xls");//告诉浏览器将输出文件的名称
        header('Cache-Control: max-age=0');//禁止缓存
        $objWriter->save("php://output");

    }

    public function excel_qs($data,$name){
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->createSheet();//创建新的内置表
        $objPHPExcel->setActiveSheetIndex(0);
        $objSheet = $objPHPExcel->getActiveSheet();

        //填充数据
        $objSheet->setCellValue("A1","问题归类")
            ->setCellValue("B1","发布人")
            ->setCellValue("C1","发布人电话")
            ->setCellValue("D1","执行人")
            ->setCellValue("E1","执行人电话")
            ->setCellValue("F1","任务位置")
            ->setCellValue("G1","问题描述")
            ->setCellValue("H1","紧急程度")
            ->setCellValue("I1","状态")
            ->setCellValue("J1","发布时间")
            ->setCellValue("K1","整改时间");

        //设置默认行高
        $objSheet->getDefaultRowDimension()->setRowHeight(20);
        //所有垂直居中
        $objSheet->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置excel文件默认水平垂直方向居中
        //设置单元格宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(25);
        //设置第二行行高
        //$objSheet->getRowDimension(1)->setRowHeight(20);
        //填充班级背景颜色
        $objSheet->getStyle("A1:K1")->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('6fc144');
        //设置单元格边框
        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        //设置某列单元格格式为文本格式
        //$objSheet->getStyle("C2:C100")->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $j=2;
        foreach ($data as $key=>$val){

            //设置行高
            //$objPHPExcel->getActiveSheet()->getRowDimension($j)->setRowHeight(10);
            if ($val['type'] == 1){
                $val['type'] = '普通';
            }elseif ($val['type'] == 2){
                $val['type'] = '紧急';
            }elseif ($val['type'] == 3){
                $val['type'] = '十分紧急';
            }elseif ($val['type'] == 4){
                $val['type'] = '万分火急';
            }

            if ($val['state'] == 1){
                $val['state'] = '待整改';
            }elseif ($val['state'] == 2){
                $val['state'] = '修复中';
            }elseif ($val['state'] == 3){
                $val['state'] = '待销项';
            }elseif ($val['state'] == 4){
                $val['state'] = '已销项';
            }elseif ($val['state'] == 5){
                $val['state'] = '已取消';
            }

            $objSheet
                ->setCellValue("A".$j,$val['issue'])
                ->setCellValue("B".$j,$val['username'].' ('.$val['name'].')')
                ->setCellValue("C".$j,$val['phone'])
                ->setCellValue("D".$j,$val['fusername'].' ('.$val['fname'].')')
                ->setCellValue("E".$j,$val['fphone'])
                ->setCellValue("F".$j,$val['building'].' '.$val['floor'].' '.$val['area'])
                ->setCellValue("G".$j,$val['title'])
                ->setCellValue("H".$j,$val['type'])
                ->setCellValue("I".$j,$val['state'])
                ->setCellValue("J".$j,date('Y-m-d H:i',$val['addtime']))
                ->setCellValue("K".$j,date('Y-m-d H:i',$val['stoptime']));
            $j++;
        }



        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');//生成excel文件
        //$objWriter->save("./Public/excel/excel.xls");//保存文件
        //browser_excel('Excel5','excel.xls');//输出到浏览器
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器数据excel07文件
        header("Content-Disposition: attachment;filename=$name.xls");//告诉浏览器将输出文件的名称
        header('Cache-Control: max-age=0');//禁止缓存
        $objWriter->save("php://output");

    }


	public function excel($data,$time){
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->createSheet();//创建新的内置表
		$objPHPExcel->setActiveSheetIndex(0);
		$objSheet = $objPHPExcel->getActiveSheet();
			
		//填充数据
		$objSheet->setCellValue("A1","姓名")
		->setCellValue("B1","缴费金额")
		->setCellValue("C1","订单状态")
		->setCellValue("D1","订单号")
		->setCellValue("E1","手机号")
		->setCellValue("F1","身份证号")
		->setCellValue("G1","报考学校")
		->setCellValue("H1","报考专业")
		->setCellValue("I1","所属届次")
		->setCellValue("J1","学历")
		->setCellValue("K1","三级代理")
		->setCellValue("L1","二级代理")
		->setCellValue("M1","一级代理")
		->setCellValue("N1","提交时间")
		->setCellValue("O1","付款时间");
			
		//设置默认行高
		$objSheet->getDefaultRowDimension()->setRowHeight(20);
		//所有垂直居中
		$objSheet->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置excel文件默认水平垂直方向居中
		//设置单元格宽度
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(25);
		$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(25);
		//设置第二行行高
		//$objSheet->getRowDimension(1)->setRowHeight(20);
		//填充班级背景颜色
		$objSheet->getStyle("A1:O1")->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('6fc144');
		//设置单元格边框
		$objPHPExcel->getActiveSheet()->getStyle('A1:O1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
		//设置某列单元格格式为文本格式
		//$objSheet->getStyle("C2:C100")->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			
		$j=2;
		foreach ($data as $key=>$val){
		
		//设置行高
		//$objPHPExcel->getActiveSheet()->getRowDimension($j)->setRowHeight(10);
		
		if($val['state']==1){
		$state = "未付款";
		}elseif($val['state']==2){
		$state = "已付款";
		}elseif($val['state']==3){
		$state = "已取消";
		}
		if(!$val['paytime']){
			$paytime = "";
		}else{
			$paytime = date("Y-m-d H:i:s",$val['paytime']);
		}
		 if($val['san']['class']==2){
		 	$san_class = "(合伙人)";
		 }
		 if($val['er']['class']==2){
		 	$er_class = "(合伙人)";
		 }
		 if($val['yi']['class']==2){
		 	$yi_class = "(合伙人)";
		 }
		
		$objSheet
		->setCellValue("A".$j,$val['name'])
		->setCellValue("B".$j,$val['price'])
		->setCellValue("C".$j,$state)
		->setCellValue("D".$j,"'".$val['order'])
		->setCellValue("E".$j,$val['phone'])
		->setCellValue("F".$j,"'".$val['id_number'])
		->setCellValue("G".$j,$val['bid1_title'])
		->setCellValue("H".$j,$val['bid2_title'])
		->setCellValue("I".$j,$val['bid3_title'])
		->setCellValue("J".$j,$val['bid4_title'])
		->setCellValue("K".$j,$val['san']['name']." ".$val['san']['phone']." ".$san_class)
		->setCellValue("L".$j,$val['er']['name']." ".$val['er']['phone']." ".$er_class)
		->setCellValue("M".$j,$val['yi']['name']." ".$val['yi']['phone']." ".$yi_class)
		->setCellValue("N".$j,date("Y-m-d H:i:s",$val['addtime']))
		->setCellValue("O".$j,$paytime);
		$j++;
		}
			
		
		
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');//生成excel文件
		//$objWriter->save("./Public/excel/excel.xls");//保存文件
		//browser_excel('Excel5','excel.xls');//输出到浏览器
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器数据excel07文件
		header("Content-Disposition: attachment;filename=$time.xls");//告诉浏览器将输出文件的名称
		header('Cache-Control: max-age=0');//禁止缓存
		$objWriter->save("php://output");

	} 

	public function excel_enter($data,$time){
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->createSheet();//创建新的内置表
		$objPHPExcel->setActiveSheetIndex(0);
		$objSheet = $objPHPExcel->getActiveSheet();
			
		//填充数据
		$objSheet->setCellValue("A1","姓名")
		->setCellValue("B1","手机号")
		->setCellValue("C1","报名编号")
		->setCellValue("D1","性别")
		->setCellValue("E1","年龄")
		->setCellValue("F1","感兴趣的专业")
		->setCellValue("G1","提交时间");
			
		//设置默认行高
		$objSheet->getDefaultRowDimension()->setRowHeight(20);
		//所有垂直居中
		$objSheet->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置excel文件默认水平垂直方向居中
		//设置单元格宽度
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
		//设置第二行行高
		//$objSheet->getRowDimension(1)->setRowHeight(20);
		//填充班级背景颜色
		$objSheet->getStyle("A1:G1")->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('6fc144');
		//设置单元格边框
		$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
		//设置某列单元格格式为文本格式
		//$objSheet->getStyle("C2:C100")->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			
		$j=2;
		foreach ($data as $key=>$val){
	
			//设置行高
			//$objPHPExcel->getActiveSheet()->getRowDimension($j)->setRowHeight(10);
	
			if($val['sex']==1){
				$sex = "男";
			}elseif($val['sex']==2){
				$sex = "女";
			}
			
			$objSheet
			->setCellValue("A".$j,$val['name'])
			->setCellValue("B".$j,$val['phone'])
			->setCellValue("C".$j,"'".$val['enter'])
			->setCellValue("D".$j,$sex)
			->setCellValue("E".$j,$val['age'])
			->setCellValue("F".$j,$val['enterbid'])
			->setCellValue("G".$j,date("Y-m-d H:i:s",$val['addtime']));
			$j++;
		}
			
	
	
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');//生成excel文件
		//$objWriter->save("./Public/excel/excel.xls");//保存文件
		//browser_excel('Excel5','excel.xls');//输出到浏览器
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器数据excel07文件
		header("Content-Disposition: attachment;filename=$time.xls");//告诉浏览器将输出文件的名称
		header('Cache-Control: max-age=0');//禁止缓存
		$objWriter->save("php://output");
	
	}

    public function excelimport($filename,$sheet="Sheet1",$table="task"){
        $objPHPExcel = new \PHPExcel();
        $fileType=\PHPExcel_IOFactory::identify($filename);//自动获取文件的类型提供给phpecxel用
        $objReader=\PHPExcel_IOFactory::createReader($fileType);//获取文件读取操作对象
        $sheetName=$sheet;//指定sheet
        $objReader->setLoadsheetsOnly($sheetName);//只加载指定的sheet
        $objPHPExcel=$objReader->load($filename);
        //全部加载
        //  		$sheetCount=$objPHPExcel->getSheetCount();//获取excel 文件里有多少个sheet
        // 		for ($i=0;$i<$sheetCount;$i++){
        // 			$data=$objPHPExcel->getSheet($i)->toArray();//读取每个sheet里的数据 全部放入到数组中
        // 			print_r($data);
        // 		}
        //全部加载end
        $table=M($table);

        $id = $table->order('id desc')->getField('id');
        //逐行加载
        foreach ($objPHPExcel->getWorksheetIterator() as $val){//循环取sheet

            $i=0;
            foreach ($val->getRowIterator() as $row){//逐行处理
                if($row->getRowIndex()<2){
                    continue;
                }
                $rs = array();
                $id = $id ? $id : 0;
                //检测

                foreach ($row->getCellIterator() as $key => $cell){//逐列读取
                    $data = $cell->getValue();//获取单元格数据
                    if($key==0){
                        if(!is_numeric($data)){
                            echo '错误：标识号必须为数字，错误行数第 '.$row->getRowIndex().' 行';
                            exit;
                        }
                    }
                    if($key==3){
                        if(!$data){
                            echo '错误：任务名称不能为空，错误行数第 '.$row->getRowIndex().' 行';
                            exit;
                        }
                    }
                    if($key==8){
                        if(!is_numeric($data)){
                            echo '错误：大纲级别必须为数字，错误行数第 '.$row->getRowIndex().' 行';
                            exit;
                        }
                    }

                }

                //上传
                foreach ($row->getCellIterator() as $key => $cell){//逐列读取
                    $data = $cell->getValue();//获取单元格数据
                    if($key==0){$rs['id']=$data+$id;}
                    if($key==1){$rs['status']=$data?$data:'';}
                    if($key==2){$rs['type']=$data?$data:'';}
                    if($key==3){$rs['title']=$data?$data:'';}
                    if($key==4){$rs['day']=$data?$data:0;}
                    if($key==5){$rs['starttime']=$data?str_replace('日','',str_replace('年','-',str_replace('月','-',$data))):'';}
                    if($key==6){$rs['stoptime']=$data?str_replace('日','',str_replace('年','-',str_replace('月','-',$data))):'';}
                    if($key==7){$data && is_numeric($data) ? $rs['bid']=$data+$id:$rs['bid'] = 0;}
                    if($key==8){$rs['level']=$data;}
                    if($key==9){$rs['description']=$data?$data:'';}
                    if ($rs['level'] == 1){
                        $rs['pid'] = 0;
                        $pid = array();
                    }else{
                        $rs['pid'] = $pid[$rs['level']-1];
                    }
                    $rs['addtime']=date('Y-m-d H:i:s',time());
                    $rs['state']=1;
                }
                $level = $rs['level'];
                $pid[$level] = $rs['id'];
                $rs['proid'] = C('proid');
                $table->add($rs);
                $i++;
            }
            //echo '成功插入' .$i. '条数据。';
            return $i;
        }

        exit;
        //逐行加载end
    }
	
 }
 
 ?>