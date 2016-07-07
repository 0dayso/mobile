<?php

/**
 * apimobile
 */
namespace Api\Controller;
use Think\Controller;
class MobileController extends Controller {
	//显示之前就修改状态
    public function index() {
    	$data=M('mobile')->field('id,mobile')->where('status=%d',0)->find();
    	if($data){
    		$info['status']=1;
    		$result=M('mobile')->where('id=%d',$data['id'])->save($info);
    		if($result){
    			echo $data['mobile'];
    		}else{
    			echo 0;
    		}
    		return;
    	}    	
    	echo 0;
    }
    //显示一个手机号码
    public function mboile(){
    	$data=M('mobile')->field('id,mobile')->where('status=%d',0)->find();
    	if($data){
    		echo $data['mobile'];
    	}else{
    		echo 0;
    	}
    }
    //修改 ajax手机状态
    public function ajaxmobile(){
    	$mobile=I('get.mobile');
    	if($mobile){
    		$info['status']=1;
    		$result=M('mobile')->where('id=%d',$data['id'])->save($info);	
    		echo 1;
    	}else{
    		echo 0;
    	}
    }
}

