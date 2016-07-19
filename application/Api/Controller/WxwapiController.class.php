<?php

namespace Api\Controller;
use Think\Controller;
class WxwapiController extends Controller {

    public function index() {         
       
    }

/**
 * sign()
 * 获取个性签名语录
 */
    public function sign(){
        $data=  D('sign')->field('id,signname')->where('status=0')->limit(1)->find();
        if($data){
            $parame['status']=1;
            $parame['updatetime']=time();
            $parame['number']=array('exp','number+1');
            $result=D('sign')->where('id=%d',array($data['id']))->save($parame);
            if($result){
                echo $data['signname'];    
            }else{
                echo 0;
            }
            
        }else{
            echo 0;
        }        
        exit();
        //$this->ajaxReturn($data,'xml');
    }



/**
 * Circle()
 * 获取朋友圈语录
 */
    public function circle(){
        $info = D('friends')->field('id,friendtext')->where('status=0')->limit(1)->find();
        if($info){
            $parame['status']=1;
            $parame['updatetime']=time();
            $parame['number']=array('exp','number+1');
            $result=D('friends')->where('id=%d',array($info['id']))->save($parame);
            if($result){
                echo $info['friendtext'];
            }else{
                echo 0;
            }
        }else{
            echo 0;
        }
        exit();
    }

    /**
 * 获取邮箱用户和密码
 * email()
 */
    public function emailinfo(){
        $info = D('emailinfo')->field('id,email,pwd')->where('status=0')->limit(1)->find();
        if($info){
            $parame['status']=1;
            $parame['updatetime']=time();
            $parame['number']=array('exp','number+1');
            $result=D('emailinfo')->where('id=%d',array($info['id']))->save($parame);
            if($result){
                echo $info['email'].','.$info['pwd'];
            }else{
                echo 0;
            }
        }else{
            echo 0;
        }
        exit();
    }
    


/**
 * mobilestatus()
 * 手机号码被封异常帐号
 */
    public function mobilestatus(){
        $mobile=I('pn');
        $cdkey=I('cdkey');
        if(!$mobile){
            echo '0';
        }        
        $info = D('weixi')->where("mobile='%s'",array($mobile))->find();
        if($info){            
            $parame['status']=2;
            $parame['updatetime']=time();            
            $result=D('weixi')->where('id=%d',array($info['id']))->save($parame);            
            if($result){
                echo $info['mobile'];
            }else{
                echo 0;
            }
        }else{
            $parame['status']=2;
            $parame['updatetime']=time();   
            $parame['mobile']=$mobile;   
            $parame['createtime']=time();               
            $parame['cdkey']=$cdkey;   
            $result=D('weixi')->add($parame);            
            if($result){
                echo $parame['mobile'];
            }else{
                echo 0;
            }
        }
        exit();
    }


    /*
    *得到原密码
    */
    public function getpwd(){
        $mobile=I('pn');        
        $info = D('weixi')->where("mobile='%s'",array($mobile))->getfield('pwd'); 
        if($info){
            echo $info;
        }else{
            echo 0;
        }   
        exit();
    }



}

