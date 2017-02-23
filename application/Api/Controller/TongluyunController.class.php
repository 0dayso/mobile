<?php
namespace Api\Controller;
use Think\Controller;
class TongluyunController  extends Controller{

	function index(){
		$sul=M("options")->where("option_name='tlymsg' or option_name='tlynmb'")->getfield("option_name,option_value");
		$this->ajaxreturn($sul,'xml');
	}

	/**
	微信通讯录已经增加使用
	**/
	function typeyi(){
		$id=I('get.id');
		$sex=I('get.sex');//性别
		if(!$id){
			$result['status']=0;
			$result['msg']="不存在有效数据";
		}
		$data['type']=11;//已使用的数据
		$data['updatetime']=time();
		$data['sex']=$sex;

		$where['mid']=$id;
		$sul=M("applemobile")->where($where)->save($data);
		if($sul){
			$result['status']=1;
			$result['msg']="修改成功";
		}
		$this->ajaxreturn($result);
	}


}

?>