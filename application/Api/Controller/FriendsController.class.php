<?php
namespace Api\Controller;
use Think\Controller;
class FriendsController  extends Controller{

	function index(){
		$sul=M("options")->where("option_name='tlymsg' or option_name='tlynmb' or option_name='towmsg'")->getfield("option_name,option_value");
		$this->ajaxreturn($sul,'xml');
	}

	function friendnew(){
		$mobile=I("get.mobile");		
		$cdkey=I("get.id");

		$map["mobile"]=$mobile;
		$sul=M("weixi")->where($map)->find();	

		if(!$sul){

			$data['mobile']=$mobile;
			$data['cdkey']=$cdkey;
			$data['createtime']=time();
			$data['type']=3;
			$retrun=M("weixi")->add($data);
		}	

		$sul=M("friends")->where($map)->find();

		if($sul){
			$imgart=json_decode($sul['smete'],true);
			foreach ($imgart as $key => $vo) {
				$sul['imga'.$key]=$vo['url'];
				$sul['ximga'.$key]=substr($vo['url'],strripos($vo['url'],".",1)+1);
			}
			$sul['imagnum']=count($imgart);
			$data['data']=$sul;
			$data['status']=1;

			$this->ajaxreturn($data,'xml');
		}else{
			$data['status']=0;
			$data['msg']="没有朋友圈信息";
			$this->ajaxreturn($data,'xml');
		}
		

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

}

?>