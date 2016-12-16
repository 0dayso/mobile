<?php

/**
 * 验证码处理
 */
namespace Api\Controller;
use Think\Controller;
class MomoController extends Controller {

    public function index() {
       $conent=I("content");
       $map['content']=array('like' =>"%". $conent."%" );
       $map['status']=1;
       $map['type']=I('type')?1:I('type');

       $count['content']=M("msgreply")->where($map)->getField("reply");

       $this->ajaxreturn($count);
    }
    

}

