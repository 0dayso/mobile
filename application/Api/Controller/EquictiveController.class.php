<?php

/**
 * 验证码处理
 */
namespace Api\Controller;
use Think\Controller;
class EquictiveController extends Controller {

    public function index() {    	
    	
    }

    public function saveDeviceID(){
        $deviceid=I('cdkey');
        if(!$deviceid){
        	echo 0;
        	return;
        }
        $device=D('equictive')->where('cdkey=%s',array($deviceid))->find();  
        if($device){
        	echo 1;
        	return ;
        }else{
        	$data['cdkey']=$deviceid;
        	$data['status']=1;
        	$data['version']=I('vn');
        	$result=D('equictive')->add($data);
        	echo 2;
        	return;
        	
        }


    }

    

}

