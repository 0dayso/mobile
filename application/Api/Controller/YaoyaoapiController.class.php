<?php

namespace Api\Controller;
use Think\Controller;
class YaoyaoapiController extends Controller {

    public function index() {         
       if(I("id")){
            $map['phoneid']="wx".I('id');
            $find=D("wxyaoyao")->where($map)->find();
            if($find>0){
                $now=I("now");
                if($find["jgtime"]<time()){
                    $data['jgtime']=time();
                    $sult=D("wxyaoyao")->where($map)->save($data);
                    if($sult){
                        echo 1;
                        exit();
                    }
                }

            }else{
                $data['id']="wx".I("id");    
                $data['jgtime']=time();
                $sult=D("wxyaoyao")->where($map)->add($data);
                if($sult){
                    echo 1;
                     exit();
                }
            }
       }
       echo 0;
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
  * getip()
  * 获取IP地址
  */ 
    
    public function getip(){
        if(getenv('HTTP_CLIENT_IP')) {
            $onlineip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR')) {
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR')) {
            $onlineip = getenv('REMOTE_ADDR');
        } else {
            $onlineip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
        }
        if(!$onlineip){
            echo 0;
            exit();
        }
        $info = D('record_ip')->field('id,last_login_ip')->where("last_login_ip='".$onlineip."'")->find();

       if(!$info){

           $parame['updatetime']=time();
           $parame['number']=array('exp','number+1');
           $parame['last_login_ip']=$onlineip;
           $result=D('record_ip')->add($parame);
           if($result){
               echo $onlineip;
           }else{
               echo 0;
           }
       }else{
           echo 0;
       }
       exit();
        
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
    

    //修改邮箱状态，保存解绑后的手机号码到邮箱管理
    public function emailphone(){
        $email=I('email'); 
        $phone=I('phone'); 
        $info = D('emailinfo')->field('id,email')->where("email='%s'",$email)->find();
        if($info){
            $parame['status']=2;            
            $parame['phone']=$phone;
            $result=D('emailinfo')->where('id=%d',$info['id'])->save($parame);
           
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




    /*
    *随机生成微信号
     */
    public function whacthao($length = 6){
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $wxhao='';
        for ( $i = 0; $i < $length; $i++ ) {              
            $wxhao .= $chars[ mt_rand(0, strlen($chars) - 1) ];                        
        } 
        return $wxhao;  
    }
    

     //设备运行状态
    public function setmobsbzt(){
        $cdkey=I('get.cdkey');
        $run=I('get.run');
        $nmb=I('get.nmb');       
        if($cdkey){
            $data['run']=$run;
            $data['nmb']=$nmb;      
            $result=M('equictive')->where("cdkey='%s'",$cdkey)->save($data); 
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
    
        
    

}

