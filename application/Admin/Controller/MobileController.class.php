<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class MobileController extends AdminbaseController{
	protected $navcat_model;
    public static $numbpage=0;

	function _initialize() {
		parent::_initialize();
		$this->navcat_model =D("Common/NavCat");
	}
	public function index(){
		$count=M('mobile')->where('status=0')->count();
		$Page = new \Think\Page($count,13);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$Page->setConfig('first','第一页');
		$Page->setConfig('last','末页');
        $show = $Page->show();// 分页显示输出
		
		$data=M('mobile')->where('status=0')->limit($Page->firstRow.','.$Page->listRows)->getfield('id,mobile',true);
		
		$this->assign('count',$count);
		$this->assign('data',$data);
		$this->assign('page',$show);
		$this->display();
	}

	public function update(){
		$id=I('id');

		if(empty($id)){
			$data['status']=0;
			$data['msg']=$id;	
			$this->ajaxreturn($data);
		}
		$data['status']=1;		
		$data['updatetime']=time();
		$data['userid']=session('ADMIN_ID');

		$data1=M('mobile')->where('id='.$id)->save($data);
		if($data1){
			$data['status']=1;	
			$this->ajaxreturn($data);
		}
		$data['status']=0;	
		$this->ajaxreturn($data);
	}
	
	public function add(){
		$sql="SELECT id,mobile,COUNT(*) AS ct FROM mbl_mobile GROUP BY mobile HAVING ct>1 ORDER BY ct DESC";
		$data=M()->query($sql);
		$this->assign('cq',count($data));
			
		$this->display();
	}
	
	public function uploadmobile(){
		$this->upload_weixin_resourse('mobile','mobile');
	}
	
	public function Cleardata(){
		$sql = "truncate table mbl_mobile";
		$result=M()->execute($sql);
		
		if($result == 0){
			$this->success('清除成功');
		}else{
			$this->error('清除失败');
		}
	}
	/**
	 *备份数据库表
	 */
	public function backups(){
		$status = I('status');
		if($status == -1){
			$map['status'] = 0;
			$filename = "par_mobile";
		}else{
			$filename = "allmobile";
		}
		$data = D('Mobile')->where($map)->getField('id,mobile');
		foreach($data as $k=>$v){
			$datas .= $v."\r\n";
		}
		
		$filepath = "./data/".$filename.".txt";
		$file = fopen($filepath,'w');
		$result = fwrite($file,$datas);
		fclose($file);
		if($result > 0){
			Header( "Content-type:   application/octet-stream ");
			header( "Content-Disposition:   attachment;   filename=".$filepath);
			echo $datas;
			exit();
			
			//$this->success('备份成功');
		}else{
			$this->error('备份失败');
		}
		/*Header( "Content-type:   application/octet-stream "); 
		Header( "Accept-Ranges:   bytes "); 
		header( "Content-Disposition:   attachment;   filename=test.txt "); 
		header( "Expires:   0 "); 
		header( "Cache-Control:   must-revalidate,   post-check=0,   pre-check=0 "); 
		header( "Pragma:   public "); 
		echo "测试/r/n";
		echo "测试/r/n";

		echo "输入的内容为文本文件的内容。";*/
	}
	
	public function testadd(){
		$path='D:\WWW\mobile\public\uploads\201605305760fe811f2dc.txt';

		$data=$this->fileinfo($path);
		$rul=$this->mobileaddall($data);
		$this->success('上传成功！');   
	}

	public function uniqiddata(){
		try{
			$sql="SELECT id FROM mbl_mobile AS a WHERE EXISTS(
				    SELECT id,mobile FROM(
						SELECT id,`mobile` FROM mbl_mobile GROUP BY `mobile` HAVING COUNT(*) > 1
					)AS t
				WHERE a.mobile=t.mobile AND a.id!=t.id
			)";
			$result=M()->query($sql);
			$ary=array();
			foreach ($result as $k => $v) {		
			 	$map['id']=$v['id'];
				$sul=M('mobile')->where($map)->delete();

			}
		}catch(Exception $ex){
			$this->error('请重新删除数据'.$ex);
		}
	   if($result){
			$this->success('已成功');
		}else{
			$this->success('已删除');
		}
	}

	public function nameinfo(){
		echo "dfsdfd";
	}


    public function mobileweixi(){
    	
    	$path='D:\WWW\mobile\public\mobile.txt';
    	$path1='D:\WWW\mobile\public\mobile1.txt';
		if(!file_exists($path)){
			return '文件路径错误';
		}
		$handle = @fopen($path, "r");
		$arydata=array();
		$i=0;
		$j=0;
		if ($handle) {
		    while (!feof($handle)) {
		    	
		        $buffer = fgets($handle, 4096);
		        if($i%4==0 and $i>0){
			        if($j%2==0){
				  			$arydata[]="podus20165\r\n";			
				  	}else{
				  			$arydata[]="lgrdym\r\n";
				  	}
				  	$j++;
			    }		
		        $arydata[]=$buffer;
		        $i++;
		    }
		    fclose($handle);
		}
		var_dump($arydata);
		$myfile = fopen($path1, "w") or die("Unable to open file!");
		foreach ($arydata as $key => $value) {
			// if($key%4==0 and $key>0){
			    //     if($key%2==0){
				  	// 		//$arydata[]='podus20165\r\n';
				  	// 		fwrite($myfile,"podus20165\r\n");			
				  	// }else{
				  	// 		//$arydata[]='lgrdym\n';
				  	// 		fwrite($myfile, "lgrdym\n");
				  	// }
			    //}		
			  fwrite($myfile, $value);
		}

		fclose($myfile);
		
    }


	

}

?>