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
		$Page = new \Think\Page($count,13,$parameters);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$Page->setConfig('first','第一页');
		$Page->setConfig('last','末页');
		$show = $Page->show();// 分页显示输出
		
    	$list=D('weixi')->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
		
    	$this->assign('list',$list);
		$this->assign('parameters',$parameters);
		$this->assign('page',$show);
       	$this->display();        
    }


}

