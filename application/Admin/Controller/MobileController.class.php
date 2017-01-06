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
		if(I('type')){
			$map['type'] =I('type');			
		}else{
			$map['type'] =2;	
		}
		
		$count=M('mobile')->where("status=0")->group('type')->getField("type,count('type') as cut",true);

		$mobile=M('applemobile')->group('isshow')->getField("isshow,count('isshow') as cut",true);
	
		/*
		$Page = new \Think\Page($count,13);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$Page->setConfig('first','第一页');
		$Page->setConfig('last','末页');
        $show = $Page->show();// 分页显示输出
	/*
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
		
		//$this->assign('allcount',$allcount);
		$this->assign('data',$data);
		$this->assign('page',$show);
		*/
		$this->assign("mobile",$mobile);
		$this->assign('count',$count);
		$this->display();
	}

	public function addajax(){

	
	}

	public function mobilesum(){
		
	
		$allcount=M('mobile')->count();

		$sum['count']=$allcount;//M('mobilesum')->cache(60)->getfield('cont',true);
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
		$data['url']=U('Mobile/uniqiddata');
		try{	
			
			//$sql="SELECT id,STATUS FROM mbl_mobile GROUP BY mobile HAVING COUNT(*)>1 and status=0  ORDER BY id DESC";
			
			if(!S('data')){				
				$result=M('mobiledel')->select();
			}else{
				$result=S('data');
				S('data',null);
			}
			
			if($result){
				
				//for ($i=0;$i<3;$i++) {
					$map['mobile']=$result[0]['mobile'];
					
					$sul=M('mobile')->where($map)->order('status desc,createtime asc')->getfield('id',true);
					if(count($sul)>1){
						for($j=1;$j<count($sul);$j++){
							$map1['id']=$sul[$j];						
							$sultt=M('mobile')->where($map1)->delete();	
							if($sultt){
								array_shift($result);
							}
							
						}						
					}else{
						array_shift($result);
					}	
					
				//}
				S('data',null);
				S('data',$result);
				
				$data['status']=1;
			}else{
				$data['status']=0;
			}
			
		}catch(Exception $ex){
			$data['status']=0;
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
    //增加查询归属地
    public function delguangdong(){
    	// $mobile='15818618500';
       
         // $url='https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel='.$mobile;
	    // $jsul=$this->HTTP_GET($url);
	    // var_dump($jsul);
	    // exit();

    	set_time_limit(0);
    	$map['status']=0;
    	$map['type']=2;
    	$map['province']='';
    	$data=D('Mobile')->field('id,mobile')->where($map)->select();
    	for($i=0; $i<count($data); $i++){
    		   $mobile=trim($data[$i]['mobile']);
    		   $id=$data[$i]['id'];
    		   $url='https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel='.$mobile;
    		   // echo $url;
			    $jsul=$this->HTTP_GET($url);
			    $t=substr($jsul,20,strlen(trim($jsul))-21);
			    $ati= mb_convert_encoding($t,"UTF-8", "GBK");			  
			    $at=explode(',',str_ireplace("'","",$ati));
				//$at=json_decode('('.trim($ati).')',true);

				$pr=explode(':',$at[1]);
				$data1['province']=$pr[1];
				$cn=explode(':',$at[6]);
				$data1['catName']=$cn[1];

			    if($data1['province']=='广东'){
					$data1['status']=1;//删除广东用户
				}
	
				$where['id']=$id;
				
				$sul=D('Mobile')->where($where)->save($data1);

				unset($data1);
				if($sul){
					echo $mobile."<br/>";
				}
    		//$this->deldata($data[$i]['id'],$data[$i]['mobile']);
    	}    	
    }

    public function mobilead(){
    	$this->display();
    }
    public $countt=0;
    public function setorder(){

    	$map['status']=0;
    	$map['type']=2;
    	if(I('province')){
    		$map['province']=trim(I('province')); 
    	}else{
    		$map['province']='';
    	}
    	// $data=D('Mobile')->where($map)->limit(10)->order("id desc")->select();
    	
    	if(S('omobile')==false||count(S('omobile'))<2){    		
    		S('omobile',null);
    		$data=D('Mobile')->field('id,mobile')->where($map)->limit(100)->order("id desc")->select();    		
    		S('omobile',$data);
    	}else{
    	
    		$data=S('omobile');
    	}
    	
    	$return= array_shift($data);
    	S('omobile',$data);
    	unset($data);
    	return $return;
    }

    public function setorderapple(){

    	$map['status']=0;
    	$map['type']=2;
    
    	// $data=D('Mobile')->where($map)->limit(10)->order("id desc")->select();
    	
    	if(S('aomobile')==false||count(S('omobile'))<2){    		
    		S('aomobile',null);
    		$data=D('Mobile')->field('id,mobile')->where($map)->limit(100)->order("id desc")->select();    		
    		S('aomobile',$data);
    	}else{
    	
    		$data=S('aomobile');
    	}
    	
    	$return= array_shift($data);
    	S('aomobile',$data);
    	unset($data);
    	return $return;
    }

     //增加查询归属地
    public function onedelguangdong(){

    	set_time_limit(0);


   		$data=$this->setorder();
 
    	//$data=D('Mobile')->where($map)->find();

    	if($data){
    		   $mobile=trim($data['mobile']);
    		  
    		   $id=$data['id'];

    		   $t=$this->semobile($mobile);

    		   if(stripos($t,'302 Found')>0){
    		   	
    		   		$t=$this->semobile($mobile);
    		   }
    		

			    $ati= mb_convert_encoding($t,"UTF-8", "GBK");
			     			  
			    $at=explode(',',str_ireplace("'","",$ati));
				//$at=json_decode('('.trim($ati).')',true);

				$pr=explode(':',$at[1]);
				$data1['address']=$pr[1];
				$cn=explode(':',$at[6]);
				$data1['catName']=$cn[1];

				if(empty($data1['province'])){
					$data1['province']='没';
				}
			    if($data1['province']=='广东'){
					//$data1['status']=1;//删除广东用户
					$where['id']=$id;
					$sul=D('applemobile')->where($where)->delete();//删除广东数据

					unset($t);
					unset($ati);
					unset($at);
					unset($cn);
					unset($data1);

					$retrun['status']=1;
					$retrun['msg']='数据检查完成';
					$retrun['mobile']=$mobile;
					$retrun['province']=$pr[1];
					unset($pr);
					$this->ajaxreturn($retrun);
					exit();
				}
				
				$where['id']=$id;
				$sul=D('applemobile')->where($where)->save($data1);
				unset($t);
				unset($ati);
				unset($at);
				unset($cn);
				unset($data1);
				if($sul){
					$retrun['status']=1;
					$retrun['msg']='数据检查完成';
					$retrun['mobile']=$mobile;
					$retrun['province']=$pr[1];
					unset($pr);
					$this->ajaxreturn($retrun);
					exit();
				}else{
					$retrun['status']=2;
					$retrun['msg']='修改失败';
					$retrun['mobile']=$mobile;
					$retrun['province']=$pr[1];	
					unset($pr);			
					$this->ajaxreturn($retrun);
					exit();
				}
				

    		//$this->deldata($data[$i]['id'],$data[$i]['mobile']);
		}else{
			$retrun['status']=3;
			$retrun['msg']='数据检查完成';
			$retrun['mobile']='没有数据';
			$retrun['province']="没有数据";
			$this->ajaxreturn($retrun);
			exit();
		}
    	    	
    }

     //增加查询归属地
    public function onedelguangdongapple(){

    	set_time_limit(0);


   		$data=$this->setorderapple();
 
    	//$data=D('Mobile')->where($map)->find();

    	if($data){
    		   $mobile=trim($data['mobile']);
    		  
    		   $id=$data['mid'];

    		   $t=$this->semobile($mobile);

    		   if(stripos($t,'302 Found')>0){
    		   	
    		   		$t=$this->semobile($mobile);
    		   }
    		

			    $ati= mb_convert_encoding($t,"UTF-8", "GBK");
			     			  
			    $at=explode(',',str_ireplace("'","",$ati));
				//$at=json_decode('('.trim($ati).')',true);

				$pr=explode(':',$at[1]);
				$data1['province']=$pr[1];
				$cn=explode(':',$at[6]);
				$data1['catName']=$cn[1];

				if(empty($data1['province'])){
					$data1['province']='没';
				}
			    if($data1['province']=='广东'){
					$data1['status']=1;//删除广东用户
				}
				
				$where['id']=$id;
				$sul=D('Mobile')->where($where)->save($data1);
				unset($t);
				unset($ati);
				unset($at);
				unset($cn);
				unset($data1);
				if($sul){
					$retrun['status']=1;
					$retrun['msg']='数据检查完成';
					$retrun['mobile']=$mobile;
					$retrun['province']=$pr[1];
					unset($pr);
					$this->ajaxreturn($retrun);
					exit();
				}else{
					$retrun['status']=2;
					$retrun['msg']='修改失败';
					$retrun['mobile']=$mobile;
					$retrun['province']=$pr[1];	
					unset($pr);			
					$this->ajaxreturn($retrun);
					exit();
				}
				

    		//$this->deldata($data[$i]['id'],$data[$i]['mobile']);
		}else{
			$retrun['status']=3;
			$retrun['msg']='数据检查完成';
			$retrun['mobile']='没有数据';
			$retrun['province']="没有数据";
			$this->ajaxreturn($retrun);
			exit();
		}
    	    	
    }

    public function semobile($mobile){
    	$url='https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel='.$mobile;
    		   // echo $url;
			    $jsul=$this->HTTP_GET($url);			    
			    $t=substr($jsul,20,strlen(trim($jsul))-21);
		return $t;
    }


    public function deldata($id,$mobile){
    	if($id&&$mobile){
	    	$url='https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel='.$mobile;	
	    	echo $url;	
	    	$jsul=$this->HTTP_GET($url);
	    	dump($jsul);
	    	exit();
	    	$tj=json_decode($jsul);
			if($jsul){

				if($tj['province']=='广东'){
					$data1['status']=1;
					$data1['province']=$tj['province'];
					$data1['catName']=$tj['catName'];
				
				}else{
					$data1['province']=$tj['province'];
					$data1['catName']=$tj['catName'];
				}
				$where['id']=$id;
				$sul=D('Mobile')->where($where)->save($data1);

			}
		}

   }

    private function HTTP_POST($url, $param) {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }

        $strPOST = http_build_query($param);

        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        return $sContent;
    }

     private function HTTP_GET($url) {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }
       	curl_setopt($oCurl, CURLOPT_URL, $url);
       	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
       	curl_setopt($oCurl, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($oCurl, CURLOPT_HEADER, 0);

        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        return $sContent;
    }


    public function addexl(){
    	$this->display();
    }

	

}

?>