<?php

/**
 * 验证码处理
 */
namespace Api\Controller;
use Think\Controller;
class WxwapiController extends Controller {

    public function index() {         
       
    }
    public function sign(){
        $data['str']=  "这鬼天气，晒成狗了";
        echo $data['str'];
        //$this->ajaxReturn($data,'xml');
    }


    

}

