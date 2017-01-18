<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class MobileExlController extends AdminbaseController{
	protected $navcat_model;
    public static $numbpage=0;

    public function index(){
    	$str=S("addext");
    	$this->assign("count",count(S("adddaata".$str.session(ADMIN_ID)))-1);
    	$this->display();
    }

    public function upload_mobile(){
    	set_time_limit(0);
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

    public function dumpdata(){
  
    	set_time_limit(0);
    	ob_end_clean();
    	vendor ( 'download-xlsx' );

         $dataAry[0][0]="手机号码";
         $dataAry[0][1]="姓名";

        $starttime=strtotime(date("Y-m-d",time()));
        //$map['ori.addtime']=array(array("gt",$starttime),array("lt",time()),'and');

        $order=M('mobilename')->field("mobile,username")->select();
        $dataAry=array_merge($dataAry,$order);

    	export_csv($dataAry,"dumpexl");
    }

    public function dumptxt(){

    }

    public function mobileclose(){
    	$sul=M("mobilename")->where('1')->delete();
    	if($sul){
    		$this->success("已清空数据");
    	}else{
    		$this->error("已没有数据");
    	}
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
				$str="txt";
			}else{
				$data=$this->fileexl($path,$info["file"]["ext"]);
				$str="xls";
				//$data=$data['data'];
			}

			if($data){
				S("adddaata".$str.session(ADMIN_ID),null);
				S("adddaata".$str.session(ADMIN_ID),$data);
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
		if(!S("adddaataxls".session(ADMIN_ID))){
			$entry['status']=3;
			$this->ajaxreturn($entry);
			exit();
		}

		$str="xls";
		$dataary=S("adddaata".$str.session(ADMIN_ID));
		$data=$dataary['data'];
		if($data){
			$entry = array_shift($data);
			$entry['status']=0;
		}
		$dataary['data']=$data;
		S("adddaata".$str.session(ADMIN_ID),$dataary);
		

		$entry['count']=count($data);

		if(!$dataary){
			$entry['status']=2;
			$this->ajaxreturn($entry);
			exit();
		}
	
		if($entry){

			$mobile=$entry["mobile"];


			$url='http://sj.apidata.cn/?mobile='.$mobile;//15818618500
			$jsul=HTTP_GET($url);
		
			$jsondata=json_decode($jsul,true);
			if($jsondata['status']==1){
				
				$jsont['province']=$jsondata['data']['province'];				
				$jsont['types']=$jsondata['data']['province'];

				$data1['province']=$jsondata['data']['city'];
				$data1['catName']=$jsondata['data']['province'];//json_encode($jsont);

				$parame['province']=$jsondata['data']['city'];
				$parame['catName']=$jsondata['data']['province'];
			}
			/*是否是广东人*/
			/*
			 $url='https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel='.$mobile;
			 $url='http://sj.apidata.cn/?mobile='.$mobile;//15818618500
	    		   // echo $url;
		    $jsul=HTTP_GET($url);
		    $t=substr($jsul,20,strlen(trim($jsul))-21);
		    $ati= mb_convert_encoding($t,"UTF-8", "GBK");			  
		    $at=explode(',',str_ireplace("'","",$ati));
			//$at=json_decode('('.trim($ati).')',true);

			$pr=explode(':',$at[1]);
			$data1['province']=$pr[1];
			$cn=explode(':',$at[6]);
			$data1['catName']=$cn[1];
			*/

			
			

		    if($data1['province']=='广州'){
				$data1['status']=1;//删除广东用户
			}
			/*是否是广东人*/
			
			$parame['mobile']=$entry["mobile"];
			$parame['username']=$entry["name"];
			$parame['createtime']=time();
			$parame['updatetime']=time();
			try {

				if(count($parame['username'])>8){
					$parame['status']=1;
				}
				$sul=M("mobile")->add($parame);
				if($sul&&$data1['province']!='广州'){
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

	//增加一次增加百个手机号
	public function addmobile100(){
		
		if(!S("addextxls".session(ADMIN_ID))){
			$entry['status']=3;
			$this->ajaxreturn($entry);
			exit();
		}

		$str="xls";

		$dataary=S("adddaata".$str.session(ADMIN_ID));

		if(!$dataary){
			$entry['status']=2;
			$this->ajaxreturn($entry);
			exit();
		}

		$list=array();
		$data=$dataary['data'];
		if($data){
			for ($i=0; $i < 50; $i++) { 
				if($data){

					$entry = array_shift($data);
					$entry['status']=0;
					$list=$this->onemobile($entry);

				}
			}
			
		}

		$dataary['data']=$data;
		S("adddaata".$str.session(ADMIN_ID),$dataary);
		$list['mobile']="增加成功50条，";
		$list['name']=count($data)."条数据未增加，";

		$this->ajaxreturn($list);
	}


	public function onemobile($entry){
	
		if($entry){

			$mobile=$entry["mobile"];
			/*是否是广东人*/
			 $url='https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel='.$mobile;
	    		   // echo $url;
		    $jsul=HTTP_GET($url);
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
			/*是否是广东人*/
			$parame['catName']=$cn[1];
			$parame['province']=$pr[1];
			$parame['mobile']=$entry["mobile"];
			$parame['username']=$entry["name"];
			$parame['createtime']=time();
			$parame['updatetime']=time();
			try {

				if(count($parame['username'])>8){
					$parame['status']=1;
				}
				$sul=M("mobile")->add($parame);
				if($sul&&$data1['province']!='广东'){
					$para["mid"]=$sul;
					$para['username']=$entry["name"];
					$para['mobile']=$entry["mobile"];
					$info=M("mobilename")->add($para);
				}

			} catch (\Exception $e) {
				return $entry;				
			}
			
			if($sul&$info){
				$entry['status']=1;
			}

		}else{
			$entry['status']=2;
		}
		return $entry;
	}

	//不需要检测直接增加apple
	public function addapple(){
		if(S("adddaatatxe")){
			$entry['status']=3;
			$this->ajaxreturn($entry);
			exit();
		}
		$str="txt";
		$dataary=S("adddaata".$str.session(ADMIN_ID));
	

		$data=$dataary;
		if($data){
			$entry = array_shift($data);
		}
		$dataary=$data;
		S("adddaata".$str.session(ADMIN_ID),$dataary);
		
		$return["status"]=0;

		if($entry){
			$map['mobile']=$entry;
			$ndata=M("mobilename")->where($map)->find();
			if(!$ndata){
				$return["mobile"]=$entry;
				$this->ajaxreturn($return);
				exit();
			}

		
			$parame['updatetime']=time();
			$parame['type']=2;

			try {
				$where['id']=$ndata['mid'];
				$sul=M("mobile")->where($where)->save($parame);

				$adata['mobile']=$ndata['mobile'];
				$adata['mid']=$ndata['mid'];
				$adata['username']=$ndata['username'];

				M("applemobile")->add($adata);

				$return['status']=1;
				$return=array_merge($adata,$return);
			} catch (\Exception $e) {
				$return['status']=0;
				$this->ajaxreturn($return);
				exit();
			}
		}else{
			$return['status']=2;
		}
		$this->ajaxreturn($return);
	}

	//不需要检测直接增加apple
	public function addappletxt(){
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
			$ndata=M("mobile")->where($map)->find();

			if(!$ndata){
				$return["mobile"]=$entry;
				$this->ajaxreturn($return);
				exit();
			}

		
			$parame['updatetime']=time();
			$parame['type']=2;

			try {
				$where['id']=$ndata['id'];
				$sul=M("mobile")->where($where)->save($parame);

				$adata['mobile']=$ndata['mobile'];
				$adata['mid']=$ndata['id'];
				$adata['username']=$ndata['username'];

				M("applemobile")->add($adata);

				$return['status']=1;
				$return=array_merge($adata,$return);
			} catch (\Exception $e) {
				$return['status']=0;
				$this->ajaxreturn($return);
				exit();
			}
		}else{
			$return['status']=2;
		}
		$this->ajaxreturn($return);
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