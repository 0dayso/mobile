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
		//->where('isshow=0')

		$count=M('mobilewoman')->where('sex=1 and isshow=0')->count();
		// $mobilest=M('users')->where('id=%d',session('ADMIN_ID'))->getField('mobilest');
		// if($mobilest!=1){
		// 	$data=M('mobile')->where("ffid=%d and status=0 and type=2 and isshow>0 ",session('ADMIN_ID'))->getfield('id,mobile,status',true);
		// 	$this->assign('count',$count);
		// 	$this->assign('data',$data);
		// 	$this->display();
		// 	exit();
		// }


		//$count=M('mobile')->where("status=0 and type=2 and province='江苏'")->count();
		// $counts=M('mobile')->where('status=0 and type=2 and isshow=0')->count();
		// if($counts==0){
		// 	$t=M('mobile')->where("status=0 and type=2 and isshow=1")->setField('isshow',0);	
		// }
		M()->startTrans();
        $isallsave=true;

   //      if(S('apdata')){

   //      }else{
   //      	$data=M('mobile')->where("status=0 and type=2 and isshow=0 and province='江苏'")->limit(5)->lock(true)->getfield('id,mobile,status',true);	
   //      	//$data=M('mobile')->where("status=0 and type=2 and isshow=0 and province='江苏'")->limit(5)->lock(true)->getfield('id,mobile,status',true);	
			// S('apdata',$data);	
   //      } 	
        $data=M('mobilewoman')->where('sex=1 and isshow=0')->limit(5)->lock(true)->getfield('mid as id,mobile,status',true);


       	/*
		if(!$data&&$count>0){
			$counts=M('mobile')->where('status=0 and type=2 and isshow=0')->count();
			if($counts<=0){
				//$t=M('mobile')->where("status=0 and type=2 and isshow=1")->setField('isshow',0);	
			}
		}
		*/
		$aryid=array();
		foreach ($data as $key => $value) {	
			/*	
			$moibledata['isshow']=array('exp',"isshow+1");
			$moibledata['ffid']=session('ADMIN_ID');
			$moibledata['showtime']=time();			
			$t=M('mobile')->where("id=%d",$value['id'])->save($moibledata);
			if(!$t){				
			  // $isallsave=false;
			   unset($data[$key]);
			   //braek;
			}
			*/
			
			$sulap=M('mobilewoman')->where("mid=%d",$value['id'])->setInc('isshow');
			if(!$sulap){
				//$isallsave=false;
				unset($data[$key]);
				//braek;
			}
			$aryid[]=$value['id'];
		}
	
		$this->assign('aryid',implode(',', $aryid));
		
		if(!$isallsave){
			M()->rollback();
			$data='';
		}else{
			M()->commit();
		}

		$this->assign('count',$count);
		$this->assign('data',$data);
		$this->display();
	}

	function newmobile(){
		$this->display();
	}
	//缓存数据增加数据
	function setorder(){
		if(I('province')){
    		$map['province']=trim(I('province')); 
    	}
    	$map['status']=0;
    	$map['type']=2;
    	$map['adds']=0;
    
    	if(count(S('omobile'))<2||S('omobile')==false){
    		S('omobile',null);
    		//$data=D('Mobile')->field('id,mobile')->where($map)->limit(30)->order("id desc")->select();
    		$data=M('mobile')->where($map)->limit(500)->select();
    		
    		S('omobile',$data);
    	}else{
    		$data=S('omobile');
    	}
    	$return= array_shift($data);
    	S('omobile',$data);
    	return $return;
    }
	//增加数据到apple查询数据
	function appleajaxmobile(){

		$resul['status']=0;
		$resul['msg']='没有得到数据';
		M()->startTrans();
	
		$data=$this->setorder();

		if($data){
			 $param['mid']=$data['id'];
			 $param['mobile']=$data['mobile'];
			 $sul=M('applemobile')->add($param);
			 if($sul){
			 	if(empty($data['id'])){
			 		M()->rollback();
				 	$resul['moible']=$param['mobile'];
				 	$resul['status']=3;
				 	$resul['msg']='复制失败';
			 	}
			 	$usul=M('mobile')->where('id=%d',$data['id'])->setField('adds',1);
			 	if($usul){
			 		M()->commit();
				 	$resul['mobile']=$param['mobile'];
				 	$resul['status']=1;
				 	$resul['msg']='复制成功';
			 	}else{
			 		M()->rollback();
				 	$resul['mobile']=$param['mobile'];
				 	$resul['status']=2;
				 	$resul['msg']='复制失败';
			 	}
			 	unset($usul);
			 
			 }else{
			 	M()->rollback();
			 	$resul['mobile']=$param['mobile'];
			 	$resul['status']=3;
			 	$resul['msg']='复制失败';
			 }
		}else{
			M()->rollback();
			$resul['mobile']=$param['mobile'];
			$resul['status']=4;
			$resul['msg']='没有数据';
		}

		unset($data);
		$this->ajaxreturn($resul);
	}

	//检查是否还有数据没有处理就不能点刷新
	function reloadmobile(){
		$id=I('post.id');
		if($id){
			$where['id']=array('in',$id);
			$where['status']=3;
			$data=M('mobilewoman')->where($where)->limit(5)->lock(true)->getfield('mid,mobile',true);

			if($data){
				$this->assign('data',$data);
				$data['status']=0;
				$this->ajaxreturn($data);
				exit();
			}else{
				$this->assign('data',$data);
				$data['status']=1;
				$this->ajaxreturn($data);
				exit();
			}
		}else{
			$this->assign('data',$data);
			$data['status']=2;
			$this->ajaxreturn($data);
			exit();
		}


	}


	//修改状态
	public function update(){
		$id=I('id');

		if(empty($id)){
			$data['status']=0;
			$data['msg']=$id;	
			$this->ajaxreturn($data);
		}
		/*
		$sdata=M('mobilewoman')->where('id=%d',$id)->find();
		if($sdata['status']==1){
			$data['status']=2;	
			$data['name']=$sdata['userid'];
			$this->ajaxreturn($data);
			exit();
		}
		*/
		$data['status']=1;		
		$data['updatetime']=time();
		//$data['userid']=session('ADMIN_ID');
		//$data1=M('mobile')->where('id='.$id)->save($data);		
		//$paramalter['isshow']=array("exp","isshow+1");
		$paramalter['updatetime']=tiem();
		$paramalter['status']=3;//已添加
		$sulap=M('mobilewoman')->where("mid=%d",$id)->save($paramalter);
		
		if($sulap){
			$data['status']=1;	
		}else{
			$data['status']=0;	
		}
		$this->ajaxreturn($data);
		exit();

		/*
		if($data1){
			//用户加1
			$data['status']=1;	
			$ummap['uid']=session('ADMIN_ID');
			$ummap['now']= strtotime(date('Y-m-d', time()));
			$umdata=M('usermobile')->where($ummap)->find();
			if($umdata){
				M('usermobile')->where($ummap)->setInc('count');	
			}else{
				$ummap['count']=1;
				M('usermobile')->add($ummap);	
			}
			//删除已经操作过的数据
			$paramalter['isshow']=array("exp","isshow+1");
			$sulap=M('applemobile')->where("mid=%d",$id)->save($paramalter);

			if($sulap){
				M()->commit();	
			}else{
				M()->rollback();
			}
			$this->ajaxreturn($data);
			exit();
		}
		M()->rollback();
		$data['status']=0;	

		$this->ajaxreturn($data);
		exit();
		*/
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

	//上传文件信息
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
				$path=$info['file']['savepath'].$info['file']['savename'];
		
				$data=$this->fileinfo($path);
				if(count($data)<1){
					$this->error('数据处理错误');
				}
				$rul=$this->mobileaddall($data);
				if($rul){
					$this->success('上传成功！');   
				}else{
					$this->error('数据添加错误');
				}			  	

			}
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

    //作已废处理-作为已加入
    public function mobilecancel(){
    	$id=I('aryid');	
    	
		if(empty($id)){
			$data['status']=0;
			$data['msg']=$id;	
			$this->ajaxreturn($data);
			exit();
		}
		

		M()->startTrans();
		$data['status']=4;		
		$data['updatetime']=time();
		$data['userid']=session('ADMIN_ID');
		$aryid=explode(',', $id);


		foreach ($aryid as $k => $v) {	
			$tmap['status']=4;
			$tmap['id']=$v;
			$cuont=M('mobilewoman')->where($tmap)->count();
			if($counts<=0){
				$data1=M('mobilewoman')->where('id=%d',$v)->save($data);	
			}	
		}

		if($t){
			M()->commit();
			$data['status']=1;	
			$this->ajaxreturn($data);
			exit();
		}
		if($data1){
			M()->commit();
			$data['status']=1;	
			$this->ajaxreturn($data);
			exit();
		}
		M()->rollback();
		$data['status']=0;	
		$this->ajaxreturn($data);
		exit();
    }
}

?>