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
    		exit();
    	}    	
    	echo 0;
        exit();
    }
    //显示一个手机号码
    public function mboile(){
    	$data=M('mobile')->field('id,mobile')->where('status=%d',0)->find();
    	if($data){
    		echo $data['mobile'];
    	}else{
    		echo 0;
    	}
        exit();
    }
    //修改 ajax手机状态
    public function setmobiletype(){
        $mobile=I('get.mobile');
        $type=I('get.st');
        if($mobile){
            if($type==1){
                $info['type']=1;
                $info['status']=1;
            }else{
                $info['type']=2;
            }
            $result=M('mobile')->where("mobile='%s'",$mobile)->save($info);               
            if($result){
                echo 1;
            }else{
                echo 0;
            }   
            
        }else{
            echo 0;
        }
        exit();
    }
    //显示一个手机号码检查是否存在
    public function getmboiletype(){
        $data=M('mobile')->field('id,mobile')->where('type=%d',0)->find();
        if($data){
            echo $data['mobile'];
        }else{
            echo 0;
        }
        exit();
    }
    public function wxname(){
        $data=D('weixiname')->field('id,weixiname')->where('status=%d',0)->find();
        if($data){
            $info['status']=1;
            $result=M('weixiname')->where('id=%d',$data['id'])->save($info);
            if($result){
                echo $data['weixiname'];
            }else{
                echo 0;
            }
            exit();
        }       
        echo 0;
        exit();
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
        exit();
    }

    public function saveweixi(){
        $mobile=I('pn');        
        if($mobile){
            $wexiexists=D('weixi')->where('mobile=%s',array($mobile))->find();
            if(I('cdkey')){
                $data['cdkey']=I('cdkey');
            }
            if(I('wx')){
                $data['weixi']=I('wx');
            }
            if(I('pn')){
                $data['mobile']=I('pn');
            }
            if(I('pwd')){
                $data['pwd']=I('pwd');
            }
            if(I('email')){
                $data['email']=I('email');
            }
            if(I('emailpwd')){
                $data['emailpwd']=I('emailpwd');
            }
            if(I('wn')){
                $data['weixiname']=I('wn');
            }
       
            if($wexiexists){
               $data['updatetime']=time();
                $data=D('weixi')->create($data);
                $result=D('weixi')->where("mobile='%s'",array($mobile))->save($data);
                if($result){
                    echo 1;
                    exit();
                }else{
                    echo 0;
                    exit();
                }
            }else{
                $data['status']=1;
                $data['createtime']=time();
                $data=D('weixi')->create($data);
                $result=D('weixi')->add($data);
                if($result){
                    echo 1;
                    exit();
                }else{
                    echo 0;
                    exit();
                }
            }            
            
        }else{
            echo 0;
            exit();
        }
    }
}

