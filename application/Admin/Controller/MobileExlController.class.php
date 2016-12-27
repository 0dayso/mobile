<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class MobileExlController extends AdminbaseController{
	protected $navcat_model;
    public static $numbpage=0;

    public function index(){
    	$this->display();
    }

    public function upload_mobile(){
    	$config = array(    
			'maxSize'    =>    2000*1024*1024, 	
			'rootPath'	 =>		'.',
			'savePath'   =>    '/public/uploads/',    
			'saveName'   =>    array('uniqid',''),    
			'exts'       =>    array('xls','xlsx','txt'),    
			'autoSub'    =>    true,    
			'subName'    =>    array('date','Ymd'),
		);
    
    	$this->uploadsexl($config);
    }

    /**
	 *$table:表名
	 *$column:字段名
	 */
	protected function uploadsexl($config){
	
		
		if($table == 'mobile'){
			$config['saveName'] = '';
			$config['subName'] = 'mobile';
		}
		//$config['saveName'] = iconv("utf-8", "utf-8", $config['saveName']);
		$upload = new \Think\Upload($config);// 实例化上传类
		$info   =   $upload->upload();

		//if(!$info) {// 上传错误提示错误信息       			  
		//	$this->error($upload->getError());    
		//}else{// 上传成功 
			$path=$info['file']['savepath'].$info['file']['savename'];
			//$path=$info['file']['savepath'].$info['file']['name'];
			if($info["file"]["ext"]=="txt"){
				$data=$this->filetxt($path);
			}else{
				$data=$this->fileexl($path,$info["file"]["ext"]);
			}

			if($data){
				S("adddaata".session(ADMIN_ID),null);
				S("adddaata".session(ADMIN_ID),$data);
				S("addext",$info["file"]["ext"]);
			}
			
			if($data){
				$this->success('上传成功！'); 
			}else{
				$this->success('上传错误！'); 
			}	
			exit();		   			
		//}
	}
	/*
	 *生成txt文件
	 */
	protected function leadin($errordatas,$filepath){
		$file = @fopen($filepath,'w');
		$result = fwrite($file,$errordatas);
		fclose($file);
	}
	
	protected function fileexl($path,$extend){

		    $column = array (
                'A' => 'mobile',
                'B' => 'name',
            );
            
            $dateCol = array (
                    'C' 
            );
            $res = importFormExcel ( $column, $dateCol,ROOTPATRH.$path,$extend );
           
           
		return $res;
	}

	protected function filetxt($path){
		if($this->check_utf8($path)){
			$path = iconv("utf-8", "gb2312", $path);
		}
		
		$path=getcwd().str_replace('/','\\',$path);
		if(!file_exists($path)){
			return '文件路径错误';
		}

		$handle = @fopen($path, "r");
		$arydata=array();
		if ($handle) {
		    while (!feof($handle)) {
		        $buffer = fgets($handle, 4096);

				$buffer = trim($buffer);
				$buffer = str_replace(' ','',$buffer);
		        $arydata[]=$buffer;
		    }
		    fclose($handle);
		}

		return $arydata;
	}

	//增加一条手机号
	public function addmobile(){
		if(S("addext")=="txt"){
			$entry['status']=3;
			$this->ajaxreturn($entry);
			exit();
		}


		$dataary=S("adddaata".session(ADMIN_ID));
		$data=$dataary['data'];
		if($data){
			$entry = array_shift($data);
			$entry['status']=0;
		}
		$dataary['data']=$data;
		S("adddaata".session(ADMIN_ID),$dataary);
	

		if(!$dataary){
			$entry['status']=2;
			$this->ajaxreturn($entry);
			exit();
		}
	
		if($entry){
			
			$parame['mobile']=$entry["mobile"];
			$parame['username']=$entry["name"];
			$parame['createtime']=time();
			$parame['updatetime']=time();
			try {

				if(count($parame['username'])>8){
					$parame['status']=1;
				}
				$sul=M("mobile")->add($parame);
				if($sul){
					$para["mid"]=$sul;
					$para['username']=$entry["name"];
					$para['mobile']=$entry["mobile"];
					$info=M("mobilename")->add($para);
				}

			} catch (\Exception $e) {
				$this->ajaxreturn($entry);
				exit();
			}
			
			if($sul&$info){
				$entry['status']=1;
			}

		}else{
			$entry['status']=2;
		}
		$this->ajaxreturn($entry);
	}

	//不需要检测直接增加apple
	public function addapple(){
		if(S("addext")!="txt"){
			$entry['status']=3;
			$this->ajaxreturn($entry);
			exit();
		}

		$dataary=S("adddaata".session(ADMIN_ID));
	

		$data=$dataary;
		if($data){
			$entry = array_shift($data);
		}
		$dataary=$data;
		S("adddaata".session(ADMIN_ID),$dataary);
		
		$return["status"]=0;

		if($entry){
			$map['mobile']=$entry;
			$ndata=M("mobilename")->where($map)->find();
			if(!$ndata){
				$return["mobile"]=$entry;
				$this->ajaxreturn($return);
				exit();
			}

			$parame['mobile']=$entry["mobile"];
			$parame['updatetime']=time();
			$parame['type']=2;

			try {
				$where['id']=$ndata['mid'];
				$sul=M("mobile")->where($where)->save($parame);

				$adata['mobile']=$ndata['mobile'];
				$adata['mid']=$ndata['mid'];
				$adata['username']=$ndata['username'];

				M("applemobile")->add($adata);

				$entry['status']=1;

			} catch (\Exception $e) {
				$this->ajaxreturn($entry);
				exit();
			}
			
		}else{
			$entry['status']=2;
		}
		$this->ajaxreturn($entry);
	}

	
	public function fileaddall($table,$data,$column){
		$ary=array();
		
		foreach ($data as $k => $v) {
			if(!empty($v)){
				if(is_array($column)){
					$v = explode(',',$v);
					foreach($column as $k1=>$v1){
						if($table == 'Sign'){
							$v[0] = substr($v[0],0,60);
						}
						$v[$k1] = trim($v[$k1]);
						$v[$k1] = str_replace(' ','',$v[$k1]);
						
						$one_da[$v1] = iconv("gb2312","utf-8",$v[$k1]);
						$one_da['authorid'] = session("ADMIN_ID");
					}
					$ary[] = $one_da;
				}else{
					if($table == 'Sign'){
						$v = substr($v,0,60);
					}
					$t[$column]=iconv("gb2312","utf-8",$v);
					$t['authorid'] = session("ADMIN_ID");
					$t['updatetime']=time();
					$t['createtime']=time();
					$ary[]=$t;
				}
			}
		}
		
		$result=M($table)->addAll($ary);
		return $result;
	}

}

?>