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
		foreach($list as $k=>$v){
			if($v['checkequ'] == 1){
				$equiact_name = D('equiact')->where('id=%d',array($v['equiact_id']))->getfield('cate_name');
				$list[$k]['equiact_name'] = $equiact_name;
			}else if($v['checkequ'] == 2){
				$equictive_name = D('equictive')->where('id=%d',array($v['equiact_id']))->getfield('cdkey');
				$list[$k]['equictive_name'] = $equictive_name;
			}
			$userinfo = $this->Getuserbyid($v['authorid']);
			$list[$k]['username'] = $userinfo['user_login'];
		}
		
        $this->assign('list',$list);
       	$this->display();
        
    }
    public function info(){
        $eqid=I('id');      
        if(IS_POST&&$eqid){
			$equiact_id=I('post.equiact_id');
			$equictive_id=I('post.equictive_id');
			$checkequ=I('post.checkequ');
			
			$data['taskname'] = I('post.taskname');
			$data['checkequ'] = I('post.checkequ');
			if($data['checkequ'] ==1 && $equiact_id > 0){
				$data['equiact_id'] = I('post.equiact_id');
			}
			if($data['checkequ'] ==2 && $equictive_id > 0){
				$data['equiact_id'] = I('post.equictive_id');
			}
            $result=D('Runcode')->where('id=%d',array($eqid))->save($data);
			
            if($result){
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }
		
		$equictive=D('equictive')->getfield('id,cdkey,alias',true);
		$equiact=D('equiact')->getfield('id,id,cate_name',true);
        $data=D('runcode')->field('id,equiact_id,checkequ,taskname')->where('id=%d',array($eqid))->find();
		
        $this->assign('data',$data);
		$this->assign('equiact',$equiact);
		$this->assign('equictive',$equictive);
        $this->display();
    }
}

