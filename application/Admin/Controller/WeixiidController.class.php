<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class WeixiidController extends AdminbaseController{
	protected $navcat_model;
    public static $numbpage=0;

    public function index(){
    	$count=M("wxid_show")->group("status")->getField('status,COUNT(*) as count');
  
    	$this->assign("sum",$count);
    	$this->assign("count",count(S("wxdatatxt".session(ADMIN_ID)))-1);
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

        $post=I('get.num',0);

        $starttime=strtotime(date("Y-m-d",time()));
        //$map['ori.addtime']=array(array("gt",$starttime),array("lt",time()),'and');

        $order=M('mobilename')->field("mobile,username")->limit($post*40000,40000)->select();

        $dataAry=array_merge($dataAry,$order);

    	export_csv($dataAry,date("Y-m-d",time()).'('.($post*40000).'-'.(($post*40000)+40000).')');
    		/*
    		 array_chunk($order,floor(count($order)/40000));
        dump($order);
        $i=0;
        while ($order[$i]) {
    		$i++;
        }
	*/
        
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
				S("wxdata".$str.session(ADMIN_ID),null);
				S("wxdata".$str.session(ADMIN_ID),$data);
				S("wxext",$info["file"]["ext"]);
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
		if(S("wxdatatxt")){
			$entry['status']=3;
			$this->ajaxreturn($entry);
			exit();
		}
		$str="txt";
		$dataary=S("wxdata".$str.session(ADMIN_ID));
	

		$data=$dataary;
		if($data){
			$entry = array_shift($data);
		}
		$dataary=$data;
		S("wxdata".$str.session(ADMIN_ID),$dataary);
		$return["count"]=count($data);
		$return["status"]=0;

		if($entry){
			$wxdata=explode("----",$entry);

			$adata['wxid']=$wxdata[0];
			$adata['wxpwd']=$wxdata[1];
			$adata['wx62']=$wxdata[2];
			try {
				$adata['createtime']=time();
				$wxid=M("wxid")->add($adata);
			} catch (\Exception $e) {
				$return['status']=0;
				$return["mobile"]=$adata['wxid'];				
			}
			if($wxid){
				try {
					$return['status']=1;
					
						$adata['wid']=$wxid;
						$sut=M("wxid_show")->add($adata);
						if(!$sut){
							M("wxid")->delete($wxid);
							$return['status']=0;
						}									
					   $return["mobile"]=$adata['wxid'];
				} catch (\Exception $e) {
					M("wxid")->delete($wxid);
					$return['status']=0;
					$return["mobile"]=$adata['wxid'];				
				}
			}

		}else{
			$return['status']=2;
		}
		$this->ajaxreturn($return);
	}

	//-次增加1千条
	public function addtxt(){
		if(S("wxdatatxt")){
			$entry['status']=3;
			$this->ajaxreturn($entry);
			exit();
		}
		$str="txt";
		$dataary=S("wxdata".$str.session(ADMIN_ID));	
		$data=$dataary;
		$countnum=count($data)<100?count($data):100;
		$aryf=array();
		for ($i=0; $i <$countnum ; $i++) { 
			$entry = array_shift($data);
			$tm["wxid"]=$entry;
			$tm['createtime']=time();
			$aryf[]=$tm;
		}
		$dataary=$data;
		S("wxdata".$str.session(ADMIN_ID),$dataary);
		$return["count"]=count($data);
		$return["status"]=0;


		if($aryf){
			//$padata=array();			
			try {
				
				$count=M("wxid")->addAll($aryf);	
				$return['status']=1;
				$return['cont']=$count;
				$return['mobile']="成功加载".$count."条数据";				
	

			} catch (\Exception $e) {
				$return['status']=0;
				$return['mobile']="成功加载0条数据";
				
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