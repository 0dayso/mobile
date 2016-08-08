<?php

/**
 * 设备管理
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class UserCateController extends AdminbaseController {
	
	function _initialize() {
	    empty($_GET['upw'])?"":session("__SP_UPW__",$_GET['upw']);//设置后台登录加密码	    
		parent::_initialize();
		$this->initMenu();
	}
	
	/**
     * 后台框架首页
     */
    public function index() {
		$count=M('UserCate')->count();
		$Page = new \Think\Page($count,11);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$Page->setConfig('first','第一页');
		$Page->setConfig('last','末页');
		$show = $Page->show();// 分页显示输出
		
		$data=M('UserCate')->limit($Page->firstRow.','.$Page->listRows)->getfield('id,cate_name,pid,status,authorid',true);
		foreach($data as $k=>$v){
			$userinfo = $this->Getuserbyid($v['authorid']);
			$data[$k]['username'] = $userinfo['user_login'];
		}
		$this->assign('count',$count);
		$this->assign('data',$data);
		$this->assign('page',$show);
       	$this->display();
        
    }
	
	public function edit(){
		$id = I('id');
		$pid = I('pid');
		if($id > 0){
			$data = D('UserCate')->where('id=%d',array($id))->find();
			$this->assign('data',$data);
		}
		$this->assign('pid',$pid);	
		$this->display();
	}
	
	
	public function savedata(){
		$id = I('id');
		$data['cate_name'] = I('cate_name');
		$data['status'] = I('status');
		if($id > 0){
			$data['modifytime'] = time();
			$result=D('UserCate')->where('id=%d',array($id))->save($data);
		}else{
			$data['createtime'] = time();
			$data['authorid'] = session("ADMIN_ID");
			$result=D('UserCate')->add($data);
		}
		
		if($result){
			$this->success('保存成功',U('UserCate/index'));
		}else{
			$this->error('保存失败');
		}
	}
	
	public function delete(){
		$id = I('id');
		$result = D('UserCate')->where('id=%d',array($id))->delete();
        if($result){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
    }
}

