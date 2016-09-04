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
		$map['status'] = 0;
		if(I('type') > 0){
			$map['type'] = I('type');
		}		
		$count=M('mobile')->where($map)->count();

		$Page = new \Think\Page($count,13);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$Page->setConfig('first','第一页');
		$Page->setConfig('last','末页');
        $show = $Page->show();// 分页显示输出
		
		$data=M('mobile')->where($map)->limit($Page->firstRow.','.$Page->listRows)->getfield('id,mobile,cate_id,authorid',true);
		foreach($data as $k=>$v){
			$cateinfo = $this->GetCatebyid($v['cate_id']);
			$data[$k]['cate_name'] = $cateinfo['cate_name'];
			$userinfo = $this->Getuserbyid($v['authorid']);
			$data[$k]['username'] = $userinfo['user_login'];
		}
		// $countlist = M('mobile')->group('type')->getField('type,count(*)',true);
		// $uselist = M('mobile')->where('type=2')->group('status')->getField('status,count(*)',true);
		// $allcount=M('mobile')->count();
		
		// $this->assign('countlist',$countlist);
		// $this->assign('uselist',$uselist);
		$this->assign('count',$count);
		//$this->assign('allcount',$allcount);
		$this->assign('data',$data);
		$this->assign('page',$show);
		$this->display();
	}

	public function mobilesum(){
		$sum=M('mobilesum')->getfield('cont',true);
		$sum['status']=1;
		$this->ajaxreturn($sum);
	}
	
	protected function GetCatebyid($id){
		$cateinfo = D('Mobilecate')->field('id,cate_name')->where('id=%d',array($id))->find();
		return $cateinfo;
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
		
		$filesnames = scandir('./public/uploads/mobile',1);

		foreach($filesnames as $k=>$v){
			if($v == '..' || $v == '.'){
				unset($filesnames[$k]);
				unset($v);
			}			
		}
		
		foreach($filesnames as $k=>$v){			
			$encode = $this->check_utf8($v);
			if(!$encode){
				$files_names['filename'] = iconv('gb2312','utf-8',$v);
			}else{
				$files_names['filename'] = $v;
			}
			
			$modifytime = filemtime('./public/uploads/mobile/'.$v);
			$files_names['filepath'] = date('Y-m-d H:i:s',$modifytime);
			$files_names['path'] = './public/uploads/mobile/'.$v;
			$fileinfo[$modifytime] = $files_names;
		}
		
		foreach($fileinfo as $k=>$v){
			$sort[$k] = $k;
		}

		array_multisort($sort,SORT_DESC,SORT_NUMERIC,$fileinfo);
		
		
		$this->assign('fileinfo',$fileinfo);
		$this->display();
	}
	function cqmobile(){
		$sql="SELECT id,mobile,COUNT(*) AS ct FROM mbl_mobile GROUP BY mobile HAVING ct>1 ORDER BY ct DESC";
		$data=M()->query($sql);		
		$this->ajaxreturn(count($data));
	}
	public function addmobile(){
		$this->display();
	}
	
	public function saveaddmobile(){
		$mobile = I('mobile');
		$mobiledata = explode(PHP_EOL,$mobile);
		
		foreach($mobiledata as $k=>$v){
			$mobiledatas[$k]['mobile'] = $v;
			$mobiledatas[$k]['authorid'] = session("ADMIN_ID");
			$mobiledatas[$k]['createtime'] = time();
		}
		
		$result=M('mobile')->addAll($mobiledatas);
		
		if($result){
			$this->success('保存成功',U('Mobile/index'));
		}else{
			$this->error('保存失败');
		}
	}
	/*
	 *设置手机分类
	 */
	public function mobilecate(){
		$id = I('id');
		$data = D('mobile')->where('id=%d',array($id))->field('id,mobile,cate_id')->find();
		$catelist=D('mobilecate')->field('id,cate_name')->select();
		
		$this->assign('data',$data);
		$this->assign('catelist',$catelist);
		$this->display();
	}
	/*
	 *设置手机分类保存
	 */
	public function savemobilecate(){
		$id = I('id');
		$cate_id = I('cate_id');
		$data['cate_id'] = $cate_id;
		$result = D('mobile')->where('id=%d',array($id))->save($data);
		if($result){
			$this->success('设置成功',U('Mobile/index'));
		}else{
			$this->error('设置失败');
		}
	}
	
	public function upload_mobile(){
		$this->upload_weixin_resourse('mobile','mobile');
	}
	
	public function downloadtxt(){
		$filepath = I('path');
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename=".basename($filepath));  
		
		readfile($filepath);
		exit();
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
			header("Cache-Control: no-cache, must-revalidate");
			header( 'Pragma: no-cache' );
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
		$data['status']=0;
		$data['url']=U('Mobile/uploadmobile');
		try{
			$sql="SELECT id FROM mbl_mobile AS a WHERE EXISTS(
				    SELECT id,mobile FROM(
						SELECT id,`mobile` FROM mbl_mobile GROUP BY `mobile` HAVING COUNT(*) > 1
					)AS t
				WHERE a.mobile=t.mobile AND a.id!=t.id
			)";
			//$sql="SELECT id,STATUS FROM mbl_mobile GROUP BY mobile HAVING COUNT(*)>1 and status=0  ORDER BY id DESC";
			$result=M()->query($sql);
			$count=count($result)>200?200:count($result);
			/*
			if($count==0){
				$sql="SELECT id FROM mbl_mobile AS a WHERE EXISTS(
				    SELECT id,mobile FROM(
						SELECT id,`mobile` FROM mbl_mobile GROUP BY `mobile` HAVING COUNT(*) > 1
					)AS t
				WHERE a.mobile=t.mobile AND a.id!=t.id)";
				$result=M()->query($sql);	
				$count=count($result)>200?200:count($result);			
			}
			*/

			for($i=0;$i<$count;$i++){
				$map['id']=$result[$i]['id'];
				$sul=M('mobile')->where($map)->delete();
			}

			$data['status']=1;
			/*
			foreach ($result as $k => $v) {					 	
			}
			*/
		}catch(Exception $ex){
			$this->ajaxreturn($data);
		}
		$this->ajaxreturn($data);
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