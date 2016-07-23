<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class MobileaddController extends AdminbaseController{
	protected $navcat_model;
    public static $numbpage=0;

	function _initialize() {
		parent::_initialize();
		$this->navcat_model =D("Common/NavCat");
	}
	public function index(){
		$GLOBALS['z']=$GLOBALS['z']+1;

		M()->startTrans();
		$count=M('mobile')->where('status=0 and type=2')->count();            

        $nub=rand(1,$count);
        // if($count>$GLOBALS['z']){
        // 	$GLOBALS['z']=1;
        // }
        if($count<$nub+5){
        	$nub=$count;
        }
        $isallsave=true;
        $nub=1;

		$data=M('mobile')->where('status=0 and type=2 and isshow=0')->limit(5)->lock(true)->getfield('id,mobile',true);
		foreach ($data as $key => $value) {
			$t=M('mobile')->where("id=%d",array($key))->setField('isshow',1);	
			if(!$t){
			    $isallsave=false;
			}
		}
		M()->commit();

		if(!$isallsave){
			M()->rollback();
			$data='';
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
			exit();
		}
		$data['status']=0;	
		$this->ajaxreturn($data);
		exit();
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