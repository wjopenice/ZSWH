<?php
use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Http\Request;
class ExportController extends Controller
{
    public $user;
	public $session;
	public $pdb;

    public function initialize() {
    	include APP_PATH."/core/Session.php";
        include BASE_PATH."/vendor/PHPExcel/PHPExcel.php";
		$this->session = new \app\core\Session();
        if($this->session->has("username")){
            $this->user = $this->session->get("username");
			include APP_PATH."/core/Pdb.php";
    		$this->pdb = new \app\core\Pdb();
        }else{
            header("location:/login/alogin");
        }
    }

  function exportExcel($expTitle,$expCellName,$expTableData){

        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称

        $fileName = $expTitle.date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory;
        \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);

        $objPHPExcel = new \PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
        }
        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
            }
        }
        /* $out1 = ob_get_contents();
         file_put_contents(__DIR__.'/ob.html', json_encode($out1));
         //清除缓冲区
         ob_end_clean() ;*/
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    public function expuserAction(){
        $xlsName  = "APP用户";
        $xlsCell  = array(
            array('id','ID'),
            array('mobile_num','手机号'),
            array('nick_name','昵称'),
            array('sex','性别'),
            array('lock_status','锁定状态'),
            array('type','用户分类'),
            array('competence','用户分组'),
            array('user_level','用户等级'),
            array('last_activity_date','最近登陆时间'),
            array('register_ip','注册IP')
        );

        $xlsData=$this->pdb->action("select * from zsuser order by id asc");
        foreach ($xlsData as $key=>$value)
        {
            if($value['lock_status']==1)
            {
                $xlsData[$key]['lock_status']="OFF";
            }
            else{
                $xlsData[$key]['lock_status']="ON";
            }
            $xlsData[$key]['last_activity_date']=date("Y-m-d H:i:s",$value['last_activity_date']);
        }
        $this->exportExcel($xlsName,$xlsCell,$xlsData);
    }


}
