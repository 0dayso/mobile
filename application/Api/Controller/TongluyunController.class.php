<?php
namespace Api\Controller;
use Think\Controller;
class TongluyunController  extends Controller{

	function index(){
		$sul=M("options")->where("option_name='tlymsg' or option_name='tlynmb' or option_name='towmsg'")->getfield("option_name,option_value");
		$this->ajaxreturn($sul,'xml');
	}

	/**
	微信通讯录已经增加使用
	**/
	function typeyi(){
		$id=I('get.id');
		$sex=I('get.sex');//性别
		$result['status']=0;
		$result['msg']="修改失败";
		if(!$id){
			$result['status']=0;
			$result['msg']="不存在有效数据";
		}
		$data['type']=11;//已使用的数据
		$data['updatetime']=time();
		$data['sex']=$sex;

		$where['mid']=$id;
		try{
			$sul=M("applemobile")->where($where)->save($data);	

			if($sul){
				$result['status']=1;
				$result['msg']="修改成功";
			}
		}catch (\Exception $e) {
			$result['status']=0;
			$result['msg']="不存在有效数据";
		}
		
		$this->ajaxreturn($result);
	}

	function seachman(){
		$mobile=I("get.mobile");
		$sex=I("get.sex");
		$mid=I("get.mid");

		$data["mobile"]=$mobile;
		$data["sex"]=$sex;
		$data['mid']=$mid;
		$data["createtime"]=time();
		$data["status"]=1;

		$sul=M("mobilewoman")->add($data);
		if($sul){
			$result['status']=1;
			$result['msg']="增加成功";			
		}else{
			$result['status']=0;
			$result['msg']="数据有误";
		}
		$this->ajaxreturn($result);


	}

}

?>