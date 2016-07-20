<?php

/**
 * 设备管理
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class FriendsController extends AdminbaseController {
	
	function _initialize() {
	    empty($_GET['upw'])?"":session("__SP_UPW__",$_GET['upw']);//设置后台登录加密码	    
		parent::_initialize();
		$this->initMenu();
	}
	
	/**
     * 后台框架首页
     */
    public function index() {
		$count=M('Friends')->where('status=0')->count();
		$Page = new \Think\Page($count,12);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$Page->setConfig('first','第一页');
		$Page->setConfig('last','末页');
		$show = $Page->show();// 分页显示输出
		
		$data=M('Friends')->where('status=0')->limit($Page->firstRow.','.$Page->listRows)->getfield('id,friendtext,authorid',true);
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
		$sql="SELECT id,friendtext,COUNT(*) AS ct FROM mbl_friends GROUP BY id HAVING ct>1 ORDER BY ct DESC";
		$data=M()->query($sql);
		$this->assign('cq',count($data));
			
		$this->display();
	}
	
	public function uploaddata(){
		$this->upload_weixin_resourse('Friends','friendtext');
	}
	
	public function uniqiddata(){
		try{
			$sql="SELECT id FROM mbl_friends AS a WHERE EXISTS(
				    SELECT id,friendtext FROM(
						SELECT id,`friendtext` FROM mbl_friends GROUP BY `friendtext` HAVING COUNT(*) > 1
					)AS t
				WHERE a.friendtext=t.friendtext AND a.id!=t.id
			)";
			$result=M()->query($sql);
			$ary=array();
			foreach ($result as $k => $v) {		
			 	$map['id']=$v['id'];
				$sul=M('Friends')->where($map)->delete();

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

