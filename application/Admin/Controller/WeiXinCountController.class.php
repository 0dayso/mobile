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
			$count = $this->GetOperateCount(2,$v['id']);
			$list[$k]['count'] = $count;
			$ucounts = $this->GetOperateCount(4,$v['id']);
			$list[$k]['ucounts'] = $ucounts;
			
			$weixincount = D('weixincount')->where('userid=%d',array($v['id']))->order('createtime desc')->find();
			$list[$k]['pass_num'] = $weixincount['pass_num'];
			$list[$k]['push_num'] = $weixincount['push_num'];
			$pass_pre = round($weixincount['pass_num']/$count,2)*100;
			$pass_pre .= '%';
			$push_pre = round($weixincount['push_num']/$weixincount['pass_num'],2)*100;
			$push_pre .= '%';
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
	
	public function countinfo(){
		$userid = I('userid');
		$map['userid'] = $userid;
		$count = D('weixincount')->where($map)->count();
		$page = $this->page($count, 10);

		$list = D('weixincount')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order("createtime DESC")->select();
		foreach($list as $k=>$v){
			$userinfo = $this->Getuserbyid($v['userid']);
			$list[$k]['username'] = $userinfo['user_login'];
		}
		$param = $this->getcountparam($userid,$count);
		
		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$list);
		$this->assign("param",$param);
		$this->display();
	}
	
	protected function getcountparam($userid,$count){
		$ucounts = $this->GetOperateCount(4,$userid);
		$param['ucounts'] = $ucounts;
		$operatedays = D('mobile')->field("mobile,FROM_UNIXTIME(updatetime,'%Y-%m-%d') modifytime")->where('status=1 and userid='.$userid)->group("FROM_UNIXTIME(updatetime,'%Y-%m-%d')")->select();
		$days = count($operatedays);
		$param['ucounts_avg'] = round($ucounts/$days,2);
		
		$pass_sum = $this->getsum($userid,'pass_num');
		$push_sum = $this->getsum($userid,'push_num');
		$param['pass_sum'] = $pass_sum;
		$param['push_sum'] = $push_sum;
		$param['pass_avg'] = round($pass_sum/$count,2);
		$param['push_avg'] = round($push_sum/$count,2);
		$param['userid'] = $userid;
		$paramuserinfo = $this->Getuserbyid($userid);
		$param['username'] = $paramuserinfo['user_login'];
		return $param;
	}
	
	public function countchart(){
		$userid = I('userid');
		$map['userid'] = $userid;
		$count = D('weixincount')->where($map)->count();
		
		$param = $this->getcountparam($userid,$count);
		$years = $this->getyears();
		
		$this->assign("years",$years);
		$this->assign("param",$param);
		$this->display();
	}
	
	/**
	*获取报表
	*/
	public function Getchart(){
		$userid = I('userid');
		$sum_ruleid = I('sum_ruleid');
		$cur_time = I('permenu_id');
        $data = $this->Getsumgroup($userid,$sum_ruleid,$year_num,$cur_time);
		
		$this->ajaxReturn($data);
	}
	
	
	/**
	 *根据用户id获取每月通过数/推送数
	 *$userid:用户id
	 *$sum_ruleid:筛选条件-1按天，2按月
	 *$year_num:统计年数，默认最近3年
	 *$cur_year：统计年份，默认当前年份
	 */
	protected function Getsumgroup($userid,$sum_ruleid=2,$year_num,$cur_time){
		if(!$cur_time){
			$cur_time = date('Y',time());
		}
		if($sum_ruleid == 1 && $cur_time != ''){
			$cur_time = date('Y-m',strtotime($cur_time));
		}
		
		$map['userid'] = $userid;
		if($sum_ruleid == 1){
			$group = "FROM_UNIXTIME(createtime,'%Y-%m-%d')";
			$map2["FROM_UNIXTIME(createtime,'%Y-%m')"] = $cur_time;
			$map2['_logic'] = 'and';
			$map['_complex'] = $map2;
			$date_rule = 'j';
		}else{
			$group = "FROM_UNIXTIME(createtime,'%Y-%m')";
			$map2["FROM_UNIXTIME(createtime,'%Y')"] = $cur_time;
			$map2['_logic'] = 'and';
			$map['_complex'] = $map2;
			$date_rule = 'n';
		}
		
		$field = "createtime,sum(pass_num) pass_sum,sum(push_num) push_sum";
		$data = D('weixincount')->where($map)->group($group)->getField($field,true);
		
		foreach($data as $k=>$v){
			if(isset($v['createtime'])){
				$data[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
			}
			$datas[date($date_rule,$k)] = $data[$k];
		}
		
		$datalist['sum_ruleid'] = $sum_ruleid;
		$datalist['list'] = $datas;
		return $datalist;
	}
	
	protected function getyears(){
		$year_num = 10;
		$cur_year = date('Y',time());
		for($i=0;$i<$year_num;$i++){
		   $year[] = $cur_year-$i; 
		}
		return $year;
	}
	
	protected function getsum($userid,$colname){
		$sum = D('weixincount')->where('userid=%d',array($userid))->getField('SUM('.$colname.')');
		return $sum;
	}
	
}