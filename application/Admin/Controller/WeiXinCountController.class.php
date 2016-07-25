<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class WeiXinCountController extends AdminbaseController{
	protected $users_model,$role_model;
	
	function _initialize() {
		parent::_initialize();
		$this->users_model = D("Common/Users");
		$this->role_model = D("Common/Role");
	}
	function index(){
		$count=$this->users_model->where(array("user_type"=>1))->count();
		$page = $this->page($count, 10);

		$list = $this->users_model->limit($page->firstRow . ',' . $page->listRows)->order("create_time DESC")->select();
		foreach($list as $k=>$v){
			$count = $this->GetOperateCount(2,$v['userid']);
			$list[$k]['count'] = $count;
			$ucounts = $this->GetOperateCount(4,$v['userid']);
			$list[$k]['ucounts'] = $ucounts;
			
			$weixincount = D('weixincount')->where('userid=%d',array($v['id']))->order('createtime desc')->find();
			$list[$k]['pass_num'] = $weixincount['pass_num'];
			$list[$k]['push_num'] = $weixincount['push_num'];
			$pass_pre = round($weixincount['pass_num']/$count,2);
			$push_pre = round($weixincount['push_num']/$count,2);
			$list[$k]['pass_pre'] = $pass_pre;
			$list[$k]['push_pre'] = $push_pre;
		}
		
		$allcountlist['allcounts'] = $this->GetOperateCount();
		$allcountlist['todaycounts'] = $this->GetOperateCount(2);
		$allcountlist['machcounts'] = $this->GetOperateCount(3);
		
		$this->assign("allcountlist",$allcountlist);
		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$list);
		$this->display();
	}
	/**
	 *获取操作个数
	 *$type:1-默认所有,2-今天所有,3-机器操作所有,4-用户所有
	 *$userid:用户id
	 */
	protected function GetOperateCount($type='1',$userid){
		$map['status'] = 1;
		if($type == 2){
			$map["updatetime"] = array('gt',strtotime(date("Y-m-d",time())));
			$map2["updatetime"] = array('elt',strtotime(date("Y-m-d 23:59:59",time())));
			$map['_complex'] = $map2;
			if($userid > 0){
				$map['userid'] = $userid;
			}
			
		}else if($type == 3){
			$map['userid'] = 0;
		}else if($type == 4 && $userid > 0){
			$map['userid'] = $userid;
		}
		$operatecounts = D('mobile')->where($map)->count();
		return $operatecounts;
	}
	
	public function add(){
		$users = $this->getusers();
		
		$this->assign("users",$users);
		$this->display();
	}
	
	protected function getusers(){
		$users = D('Users')->alias("ul")->where(array("user_type"=>1))->order("create_time DESC")->select();
		return $users;
	}
	
	public function Changedata(){
		$userid = I('userid');
		$data['userid'] = I('userid');
		
		$name = I('name');
		$val = I('val');
		if($name != '' && $val != ''){
			$data[$name] = $val;
		}
		
		$map['userid'] = $userid;
		$map["createtime"] = array('gt',strtotime(date("Y-m-d",time())));
		$map2["createtime"] = array('elt',strtotime(date("Y-m-d 23:59:59",time())));
		$map['_complex'] = $map2;
		
		$countinfo = D('weixincount')->where($map)->find();
		if(!empty($countinfo)){
			$data['status'] = 2;
			$data['modifytime'] = time();
			$result=D('weixincount')->where($map)->save($data);
		}else{
			$data['status'] = 1;
			$data['createtime'] = time();
			$result=D('weixincount')->add($data);
		}
		
		if($result){
			$this->ajaxReturn(array('result'=>1));
		}else{
			$this->ajaxReturn(array('result'=>0));
		}
	}
	
	public function SaveData(){
		$model = D('weixincount');
		$id = I('id');
		$data = $model->create();
		
		if($id>0){
			$data['status'] = 2;
			$data['modifytime'] = time();
			$result = $model->where('id=%d',array($id))->save($data);
		}else{
			$data['status'] = 1;
			$data['createtime'] = time();
			$result = $model->add($data);
		}
		
		if($result){
			$this->success('保存成功',U('WeiXinCount/index'));
		}else{
			$this->error('保存失败');
		}
		
	}
	
	
}