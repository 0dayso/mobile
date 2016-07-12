<?php

/**
 * 设备管理
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class RuncodeController extends AdminbaseController {
	
	function _initialize() {
	    empty($_GET['upw'])?"":session("__SP_UPW__",$_GET['upw']);//设置后台登录加密码	    
		parent::_initialize();
		$this->initMenu();
	}
	
    /**
     * 后台框架首页
     */
    public function index() {
        $list=D('Runcode')->select();
        $this->assign('list',$list);
       	$this->display();
        
    }
    public function info(){
        $eqid=I('id');      
        if(IS_POST&&$eqid){
            $runcode=I('post.runcode');
            $result=D('Runcode')->where('id=%d',array($eqid))->setfield('runcodeid',$runcode);

            if($result){
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }
        $list=D('runcode')->getfield('id,taskname',true);
        $this->assign('list',$list);
        $this->display();
    }
}

