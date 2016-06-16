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
		$numbpage=M('mobile')->where('id=1')->getfield('number');
		$data=M('mobile')->where('status=0')->limit($numbpage*3,3)->getfield('id,mobile',true);

		if($count<$numbpage*3){
			$rsl=M('mobile')->where('id=1')->setField('number',0);
		}else{
			$rsl=M('mobile')->where('id=1')->setInc('number',1);	
		}	

		$this->assign('count',$count);
		$this->assign('data',$data);
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

	public function fileinfo($path){
		$path=getcwd().str_replace('/','\\',$path);
		//$path='D:\WWW\mobile\public\uploads\20160530\5760fe811f2dc.txt';
		if(!file_exists($path)){
			return '文件路径错误';
		}
		$handle = @fopen($path, "r");
		$arydata=array();
		if ($handle) {
		    while (!feof($handle)) {
		        $buffer = fgets($handle, 4096);
		        $arydata[]=$buffer;
		    }
		    fclose($handle);
		}

		return $arydata;
	}
	public function mobileaddall($data){
		$ary=array();
		foreach ($data as $k => $v) {
			if(!empty($v)){
				$t['mobile']=$v;
				$t['updatetime']=time();
				$t['creaetetime']=time();
				$ary[]=$t;
			}

		}
		$result=M('mobile')->addAll($ary);
		return $result;
	}
	public function add(){
		if(IS_POST){
			$config = array(    
			'maxSize'    =>    3145728, 	
			'rootPath'	 =>		'.',
			'savePath'   =>    '/public/uploads/',    
			'saveName'   =>    array('uniqid',''),    
			'exts'       =>    array('jpg', 'gif', 'png', 'jpeg','txt'),    
			'autoSub'    =>    true,    
			'subName'    =>    array('date','Ymd'),
			);
			$upload = new \Think\Upload($config);// 实例化上传类
			$info   =   $upload->upload();
			if(!$info) {// 上传错误提示错误信息       			  
			  	$this->error($upload->getError());    
			}else{// 上传成功        
				$path=$info['mobilefile']['savepath'].$info['mobilefile']['savename'];
				$data=$this->fileinfo($path);
				$rul=$this->mobileaddall($data);
			  	$this->success('上传成功！');    			}
		}
		$sql="SELECT id,mobile,COUNT(*) AS ct FROM mbl_mobile GROUP BY mobile HAVING ct>1 ORDER BY ct DESC";
		$data=M()->query($sql);
		$this->assign('cq',count($data));
			
		$this->display();
	}

	public function testadd(){
		$path='D:\WWW\mobile\public\uploads\201605305760fe811f2dc.txt';

		$data=$this->fileinfo($path);
		$rul=$this->mobileaddall($data);
		$this->success('上传成功！');   
	}

	public function uniqiddata(){
		try{
			$sql="DELETE  FROM mbl_mobile WHERE id IN(
	SELECT id FROM (SELECT id FROM `mbl_mobile` a WHERE a.mobile IN (
	    SELECT `mobile` FROM mbl_mobile GROUP BY `mobile` HAVING COUNT(*) > 1 
	) AND a.id NOT IN(
	    SELECT `id` FROM mbl_mobile GROUP BY `mobile` HAVING COUNT(*) > 1 
	)) AS t)";		
			$result=M()->execute($sql);
		}catch(Exception $ex){
			$this->error('请重新删除数据'.$ex);
		}
	   if($result){
			$this->success('已成功');
		}else{
			$this->success('已删除');
		}
	}

	

}

?>