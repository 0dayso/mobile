<?php

/**
 * 设备管理
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class WeixiController extends AdminbaseController {
	
	function _initialize() {
	    empty($_GET['upw'])?"":session("__SP_UPW__",$_GET['upw']);//设置后台登录加密码	    
		parent::_initialize();
		$this->initMenu();
	}
	
    /**
     * 后台框架首页
     */
    public function index() {
		$keyword = I('keyword');
		
		if($keyword != ''){
			$map['cdkey'] = array('like','%'.$keyword.'%');
			$map['mobile'] = array('like','%'.$keyword.'%');
			$map['weixi'] = array('like','%'.$keyword.'%');
			$map['_logic'] = 'or';
			$parameters['keyword'] = $keyword;
		}
		
		$count=D('weixi')->where($map)->count();
		$Page = new \Think\Page($count,10,$parameters);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$Page->setConfig('first','第一页');
		$Page->setConfig('last','末页');
		$show = $Page->show();// 分页显示输出
		
    	$list=D('weixi')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();
		foreach($list as $k=>$v){
			$userinfo = $this->Getuserbyid($v['authorid']);
			$list[$k]['username'] = $userinfo['user_login'];
			$where['cdkey'] = $v['cdkey'];
			$alias = D('equictive')->where($where)->getField('alias');
			$list[$k]['alias'] = $alias;
		}
		
    	$this->assign('list',$list);
		$this->assign('parameters',$parameters);
		$this->assign('page',$show);
       	$this->display();        
    }
	
	public function savemobileajax(){
		$id = I('id');
		$data['alias'] = I('alias');
		if($id > 0){
			$result=D('weixi')->where('id=%d',array($id))->save($data);
		}
		
		if($result){
			$this->ajaxReturn(array('result'=>1));
		}else{
			$this->ajaxReturn(array('result'=>0));
		}
	}

}

