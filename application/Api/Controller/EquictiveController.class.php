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
        	exit();
        }
        $device=D('equictive')->where("cdkey='%s'",array($deviceid))->find();  

        if($device){
        	$result=D('equictive')->alias('eq')->join('mbl_runcode as rc on eq.runcodeid=rc.id')->where("cdkey='%s'",array($deviceid))->find();
            if($result){           
                $parame['alterip']=$result['alterip'];
                $parame['weixicut']=$result['weixicut']; 
                $parame['onmoble']=$result['onmoble'];

                $data['parame']=implode($parame,',');        

                $data['mingle']=unserialize($result['mingle']);
                $data['mustt']=unserialize($result['mustt']);

                $data['paramegc']=unserialize($result['parame']);

                if($result['runcodeid']>0){
                    $this->ajaxReturn($data,'xml');
                }else{
                    echo 3;
                }

            }else{
                echo 0;
                exit();
            }
        	
        }else{
        	$data['cdkey']=$deviceid;
        	$data['status']=1;
        	$data['version']=I('vn');
        	$result=D('equictive')->add($data);
        	echo 2;
        	exit();
        }

    }

    

}

