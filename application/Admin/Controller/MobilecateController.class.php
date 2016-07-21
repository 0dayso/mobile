<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class MobilecateController extends AdminbaseController{
	protected $navcat_model;
    public static $numbpage=0;

	function _initialize() {
		parent::_initialize();
		$this->navcat_model =D("Common/NavCat");
	}
	public function index(){
		$count=D('mobilecate')->count();
		$Page = new \Think\Page($count,13);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$Page->setConfig('first','第一页');
		$Page->setConfig('last','末页');
        $show = $Page->show();// 分页显示输出
		
		$data=D('mobilecate')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($data as $k=>$v){
			$userinfo = $this->Getuserbyid($v['authorid']);
			$data[$k]['username'] = $userinfo['user_login'];
		}
		//print_r($data);exit;
		
		$this->assign('count',$count);
		$this->assign('data',$data);
		$this->assign('page',$show);
		$this->display();
	}
	
	public function add(){        
        $this->display();
    }
	
	public function edit(){  
		$id = I('id');
		$data = D('mobilecate')->where('id=%d',array($id))->find();
		
		$this->assign('data',$data);
        $this->display();
    }
	
	public function savedata(){
		$id = I('id');
		$data=array(
					'cate_name'=>I('cate_name'),
					'authorid' => session("ADMIN_ID")
					);
		
		if($id > 0){
			$data['modifytime'] = time();
			$result=D('mobilecate')->where('id=%d',array($id))->save($data);
		}else{
			$data['createtime'] = time();
			$result=D('mobilecate')->add($data);
		}
		if($result){
			$this->success('保存成功',U('Mobilecate/index'));
		}else{
			$this->error('保存失败');
		}
	}
	
	public function accredit(){
		$id = I('id');
		$data = D('mobilecate')->where('id=%d',array($id))->find();
		
		$map['id'] = array('neq',1);
		$map['user_status'] = 1;
		$userslist = D('users')->field('id,user_login')->where($map)->select();
		
		$roleslist = $this->GetRoles();
		
		$this->assign('roleslist',$roleslist);
		$this->assign('userslist',$userslist);
		$this->assign('data',$data);
		$this->display();
	}
	
	protected function GetRoles(){
		$data = D('Role')->where('id>1')->field('id,name')->order(array("listorder" => "asc", "id" => "desc"))->select();
		return $data;
	}
	/**
	 *根据角色id获取用户列表
	 */
	public function GetUserByRole(){
		$roleid = I('roleid');
		if($roleid > 0){
			$map['role_id'] = $roleid;
		}
		$roleuser = D('RoleUser')->field('role_id,user_id')->where($map)->select();
		foreach($roleuser as $k=>$v){
			$userinfo = $this->GetUserById($v['user_id']);
			$roleuser[$k]['user_login'] = $userinfo['user_login'];
		}
		echo json_encode($roleuser);
	}
	
	public function saveaccredit(){
		$id = I('id');
		$accredit = I('accredit');
		$data['accredit'] = implode(',',$accredit);
		$data['modifytime'] = time();
		$result=D('mobilecate')->where('id=%d',array($id))->save($data);
	
		if($result){
			$this->success('保存成功',U('Mobilecate/index'));
		}else{
			$this->error('保存失败');
		}
	}
	
	public function delete(){
		$id = I('id');
		$result = D('mobilecate')->where('id=%d',array($id))->delete();
        if($result){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
    }
	
	
}

?>