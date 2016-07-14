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
    

}

