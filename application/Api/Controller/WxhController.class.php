<?php
namespace Api\Controller;
use Think\Controller;
/**
*微信62数据处理
*@author zharty
*@since 1.0
*/
class WxhController extends Controller{
	/**	
	*返回单个不重复的62数据
	*@access public
	*@since 1.0
	*@return json
	*/
	public function wx62data(){
		$data['code']=0;
		M()->startTrans();
		try{
			$sul=M("wxid_show")->field("wid,wxid,wxpwd,wx62")->where(array('status'=>1))->lock(true)->find();//查询一个微信号
			if($sul){
				$e=M("wxid_show")->where('wid=%d',$sul['wid'])->setInc('status'); //更改显示状态加1
				if($e){
					$dat['code']=1;
					M()->commit();
					$data['data']=$sul;
				}else{
					$dat['code']=4;	
					M()->rollback();
				}				
			}else{
				$data['code']=3;
				M()->rollback();	
			}			
		}catch(\excption $t){	
			M()->rollback();	
			$data['code']=2;
		}
		$this->ajaxReturn($data);
	}

}
?>