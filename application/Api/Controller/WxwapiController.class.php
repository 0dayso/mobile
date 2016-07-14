<?php

/**
 * 获取个性签名
 */
namespace Api\Controller;
use Think\Controller;
class WxwapiController extends Controller {

    public function index() {         
       
    }
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


    

}

