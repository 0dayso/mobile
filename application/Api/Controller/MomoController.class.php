<?php

/**
 * 验证码处理
 */
namespace Api\Controller;
use Think\Controller;
class MomoController extends Controller {

    public function index() {
       
       if(I("param.content")){
       		$conent=I("param.content");
       }else{
       		 $this->ajaxreturn($count['content']="没有数据");
       		 exit();
       }
       $map['content']=array('like' =>"%". $conent."%" );
       $map['status']=1;
       $map['type']=I('type')?1:I('type');
       $result=M("msgreply")->where($map)->getField("reply");

       $count['content']=$result;

       $this->ajaxreturn($count.M()->getLastSql());
    }
    

}

