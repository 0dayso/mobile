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


Create Table

CREATE TABLE `mbl_weixi` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '微信id',
  `mobile` char(12) NOT NULL DEFAULT '' COMMENT '微信手机号',
  `weixi` char(50) NOT NULL DEFAULT '' COMMENT '微信号',
  `pwd` char(50) NOT NULL DEFAULT '' COMMENT '微信密码',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `status` bigint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `type` bigint(1) NOT NULL DEFAULT '0' COMMENT '类型',
  `email` char(50) NOT NULL DEFAULT '' COMMENT '邮件',
  `emailpwd` char(50) NOT NULL DEFAULT '' COMMENT '邮箱密码',
  `weixiname` char(50) NOT NULL DEFAULT '' COMMENT '微信昵称',
  PRIMARY KEY (`id`,`mobile`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8

    public function saveweixi(){
        $mobile=I('pn');
        if($mboile){
            $wexiexists=D('weixi')->where('mobile=%s',array($mobile))->find();
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
                $data['status']=1;
                $data['createtime']=time();
                $data=D('weixi')->create();
                $result=D('weixi')->add($data);
                if($result){
                    echo 1;
                    exit();
                }else{
                    echo 0;
                    exit();
                }

            }else{
                $data['updatetime']=time();
                $data=D('weixi')->create();
                $result=D('weixi')->where("mobile='%s'",array($mobile))->save($data);
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

