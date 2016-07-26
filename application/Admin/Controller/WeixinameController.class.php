<?php

/**
 * 设备管理
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class WeixinameController extends AdminbaseController {
	
	function _initialize() {
	    empty($_GET['upw'])?"":session("__SP_UPW__",$_GET['upw']);//设置后台登录加密码	    
		parent::_initialize();
		$this->initMenu();
	}
	
    /**
     * 后台框架首页
     */
    public function index() {
		$count=M('weixiname')->where('status=0')->count();
		$Page = new \Think\Page($count,12);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$Page->setConfig('first','第一页');
		$Page->setConfig('last','末页');
        $show = $Page->show();// 分页显示输出
		
		$data=M('weixiname')->where('status=0')->limit($Page->firstRow.','.$Page->listRows)->getfield('id,weixiname,authorid',true);
		foreach($data as $k=>$v){
			$userinfo = $this->Getuserbyid($v['authorid']);
			$data[$k]['username'] = $userinfo['user_login'];
		}
		$this->assign('count',$count);
		$this->assign('data',$data);
		$this->assign('page',$show);
       	$this->display();
        
    }
	public function add(){
		$sql="SELECT id,weixiname,COUNT(*) AS ct FROM mbl_weixiname GROUP BY id HAVING ct>1 ORDER BY ct DESC";
		$data=M()->query($sql);
		$this->assign('cq',count($data));
			
		$this->display();
	}
	
	public function addweixin(){
		$this->display();
	}
	
	public function saveaddweixin(){
		$weixiname = I('weixiname');
		$weixinamedata = explode(',',$weixiname);
		foreach($weixinamedata as $k=>$v){
			$weixinamedatas[$k]['weixiname'] = $v;
			$weixinamedatas[$k]['authorid'] = session("ADMIN_ID");
		}
		
		$result=M('weixiname')->addAll($weixinamedatas);
		
		if($result){
			$this->success('保存成功',U('Weixiname/index'));
		}else{
			$this->error('保存失败');
		}
	}
	
	public function uploadweixin(){
		$this->upload_weixin_resourse('weixiname','weixiname');
	}
	
	public function uniqiddata(){
		try{
			$sql="SELECT id FROM mbl_weixiname AS a WHERE EXISTS(
				    SELECT id,weixiname FROM(
						SELECT id,`weixiname` FROM mbl_weixiname GROUP BY `weixiname` HAVING COUNT(*) > 1
					)AS t
				WHERE a.weixiname=t.weixiname AND a.id!=t.id
			)";
			$result=M()->query($sql);
			$ary=array();
			foreach ($result as $k => $v) {		
			 	$map['id']=$v['id'];
				$sul=M('weixiname')->where($map)->delete();

			}
		}catch(Exception $ex){
			$this->error('请重新删除数据'.$ex);
		}
	   if($result){
			$this->success('已成功');
		}else{
			$this->success('已删除');
		}
	}

}

