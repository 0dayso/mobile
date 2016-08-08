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
		//$page = $this->page($count, 10);

		//$list = $this->users_model->limit($page->firstRow . ',' . $page->listRows)->order("create_time DESC")->select();
		$list = $this->users_model->order("create_time DESC")->select();
		
		foreach($list as $k=>$v){
			$ccount = $this->GetOperateCount(2,$v['id']);
			$list[$k]['count'] = $ccount;
			$ucounts = $this->GetOperateCount(4,$v['id']);
			$list[$k]['ucounts'] = $ucounts;
			
			$cmap['userid'] = $v['id'];
			$cmap["createtime"] = array('gt',strtotime(date("Y-m-d",time())));
			$cmap2["createtime"] = array('elt',strtotime(date("Y-m-d 23:59:59",time())));
			$cmap['_complex'] = $cmap2;
			
			$weixincount = D('weixincount')->where($cmap)->order('createtime desc')->find();
			
			$list[$k]['pass_num'] = $weixincount['pass_num'];
			$list[$k]['push_num'] = $weixincount['push_num'];
			$pass_pre = round($weixincount['pass_num']/$ccount,2)*100;
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
		//$this->assign("page", $page->show('Admin'));
		$this->assign("count",$count);
		$this->assign("list",$list);
		$this->display();
	}
	/**
	 *获取操作个数
	 *$type:1-默认所有,2-今天所有,3-机器操作所有,4-用户所有,5-某个时间操作个数
	 *$userid:用户id
	 *$map_time:某个时间操作个数的条件
	 */
	protected function GetOperateCount($type='1',$userid,$map_time){
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
		}else if($type == 5){
			if($userid > 0){
				$map['userid'] = $userid;
			}
			$map = array_merge($map,$map_time);
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
		$typeid = I('typeid');
        $data = $this->Getsumgroup($userid,$sum_ruleid,$typeid,$year_num,$cur_time);
		
		$this->ajaxReturn($data);
	}
	/**
	 *根据用户id获取每月通过数/推送数
	 *$userid:用户id
	 *$sum_ruleid:时间筛选条件-1按天，2按月
	 *$typeid:筛选条件-1通过数，2通过率,3推送数,4推送率
	 *$year_num:统计年数，默认最近3年
	 *$cur_time：统计时间，默认当前年份
	 */
	protected function Getsumgroup($userid,$sum_ruleid=2,$typeid=1,$year_num,$cur_time){
		$datas = $this->Getsumgroupdata($userid,$sum_ruleid,$typeid,$year_num,$cur_time);
		$dataall = $this->Getsumgroupdata('',$sum_ruleid,$typeid,$year_num,$cur_time);
		
		foreach($datas as $k=>$v){
			if($sum_ruleid == 1){
				$count_map['userid'] = $userid;
				$count_map["FROM_UNIXTIME(createtime,'%Y-%m-%d')"] = date('Y-m-d',strtotime($v['createtime']));
				$day_count =  D('weixincount')->where($count_map)->count();
				
				$allcount_map["FROM_UNIXTIME(createtime,'%Y-%m-%d')"] = date('Y-m-d',strtotime($v['createtime']));
				$allday_count =  D('weixincount')->where($allcount_map)->count();
			
				$single_days = $day_count;
				$all_days = $allday_count;
			}else{
				$kktime = $this->mFristAndLastTime($k);
				$last_time = $kktime['lasttime'];
				$month_days = date('d',$last_time);
				
				$single_days = $month_days;
				$all_days = $month_days;
			}
			
			$datas[$k]['pass_avg'] = round($v['pass_sum']/$single_days,2);
			$datas[$k]['push_avg'] = round($v['push_sum']/$single_days,2);
			$datas[$k]['pass_pre_avg'] = round($v['pass_pre']/$single_days,2);
			$datas[$k]['push_pre_avg'] = round($v['push_pre']/$single_days,2);
			
			$datas[$k]['allpass_sum'] = $dataall[$k]['pass_sum'];
			$datas[$k]['allpush_sum'] = $dataall[$k]['push_sum'];
			$datas[$k]['allpass_pre'] = $dataall[$k]['pass_pre'];
			$datas[$k]['allpush_pre'] = $dataall[$k]['push_pre'];
			
			$datas[$k]['allpass_avg'] = round($dataall[$k]['pass_sum']/$all_days,2);
			$datas[$k]['allpush_avg'] = round($dataall[$k]['push_sum']/$all_days,2);
			$datas[$k]['allpass_pre_avg'] = round($dataall[$k]['pass_pre']/$all_days,2);
			$datas[$k]['allpush_pre_avg'] = round($dataall[$k]['push_pre']/$all_days,2);
		}
		
		$datalist['typeid'] = $typeid;
		$datalist['sum_ruleid'] = $sum_ruleid;
		$datalist['list'] = $datas;
		
		return $datalist;
	}
	/**
	 *根据用户id获取每月通过数/推送数
	 *$userid:用户id
	 *$sum_ruleid:时间筛选条件-1按天，2按月
	 *$typeid:筛选条件-1通过数，2通过率,3推送数,4推送率
	 *$year_num:统计年数，默认最近3年
	 *$cur_time：统计时间，默认当前年份
	 */
	protected function Getsumgroupdata($userid,$sum_ruleid=2,$typeid=1,$year_num,$cur_time){
		if(!$cur_time){
			$cur_time = date('Y',time());
		}
		if($sum_ruleid == 1 && $cur_time != ''){
			$cur_time = date('Y-m',strtotime($cur_time));
		}
		if($userid > 0){
			$map['userid'] = $userid;
		}
		
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
			$map_time["FROM_UNIXTIME(createtime,'%Y-%m')"] = date('Y-m',$v['createtime']);
			$OperateCount = $this->GetOperateCount(5,$userid,$map_time);
			$data[$k]['operate_count'] = $OperateCount;
			
			if($v['pass_sum'] < $OperateCount){
				$pass_pre = round($v['pass_sum']/$OperateCount,2)*100;
			}else{
				$pass_pre = 0;
			}
			if($v['pass_sum'] > $v['push_sum']){
				$push_pre = round($v['push_sum']/$v['pass_sum'],2)*100;
			}else{
				$push_pre = 0;
			}
			$data[$k]['pass_pre'] = $pass_pre;
			$data[$k]['push_pre'] = $push_pre;
			
			$datas[date($date_rule,$k)] = $data[$k];
		}
		
		return $datas;
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
	/**
	 *
	 * 获取指定年月的开始和结束时间戳
	 *
	 * @param int $time 当月任意时间戳
	 * @return array(开始时间,结束时间)
	 */
	protected function mFristAndLastTime($time=0){
		$time = $time ? $time : time();
		$y = date('Y', $time);
		$m = date('m', $time);
		$d = date('t', $time);
		return array("firsttime"=>mktime(0,0,0,$m,1,$y),"lasttime"=>mktime(23,59,59,$m,$d,$y));
	}
}