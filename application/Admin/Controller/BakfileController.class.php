<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class BakfileController extends AdminbaseController{
	protected $navcat_model;
    public static $numbpage=0;

	function _initialize() {
		parent::_initialize();
		$this->navcat_model =D("Common/NavCat");
	}
	public function index(){
		$count=M('bakweixi')->count();
		$Page = new \Think\Page($count,13);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$Page->setConfig('first','第一页');
		$Page->setConfig('last','末页');
        $show = $Page->show();// 分页显示输出
		
		$list=M('bakweixi')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $k=>$v){
			$equictive = $this->getequictive($v['equictiveid']);
			$list[$k]['cdkey'] = $equictive['cdkey'];
			$userinfo = $this->Getuserbyid($v['userid']);
			$list[$k]['username'] = $userinfo['user_login'];
			$author = $this->Getuserbyid($v['authorid']);
			$list[$k]['authorname'] = $author['user_login'];
		}
		
		
		
		$this->assign('count',$count);
		$this->assign('list',$list);
		$this->assign('page',$show);
		$this->display();
	}
	
	public function add(){
		$users = $this->getusers();
		$equictive = $this->getequictive();
		
		$this->assign("users",$users);
		$this->assign("equictive",$equictive);
		$this->display();
	}
	
	protected function getusers(){
		$users = D('Users')->where(array("user_type"=>1))->order("create_time DESC")->select();
		return $users;
	}
	protected function getequictive($id){
		if($id > 0){
			$quictive = D('equictive')->field('id,cdkey,alias')->where('id=%d',array($id))->find();
		}else{
			$quictive = D('equictive')->order("id DESC")->select();
		}
		return $quictive;
	}
	
	/*
	 *保存
	 */
	public function SaveData(){
		$id = I('id');
		$userid = I('userid');
		$equictiveid = I('equictiveid');
		if($userid > 0 || $userid != ''){
			$data['userid'] = $userid;
		}
		if($equictiveid > 0 || $equictiveid != ''){
			$data['equictiveid'] = $equictiveid;
		}
		
		$data = D('bakweixi')->create();
		$filedata = $this->uploadfile();
		if($filedata['name'] != ''){
			$data['name'] = $filedata['name'];
		}
		if($filedata['filesize'] != ''){
			$data['filesize'] = $filedata['filesize'];
		}
		$data['authorid'] = session("ADMIN_ID");
		
		if($id > 0){
			$data['updatetime'] = time();
			$result = D('bakweixi')->where('id=%d',array($id))->save($data);
		}else{
			$data['createtime'] = time();
			$result = D('bakweixi')->add($data);
		}
		
		if($result){
			$this->success('保存成功',U('Bakfile/index'));
		}else{
			$this->error('保存失败');
		}
	}
	
	public function uploadfile(){
		$file_image = $_FILES['file'];
		$config = array(    
			'maxSize'    =>    50*1024*1024, 	
			'rootPath'	 =>		'.',
			'savePath'   =>    '/public/uploads/bak/',    
			'saveName'   =>    array('uniqid',''),    
			'exts'       =>    array('bak'),    
			'autoSub'    =>    true,    
			'subName'    =>    array('date','Ymd'),
		);
		$upload = new \Think\Upload($config);// 实例化上传类
		
		if($file_image['name'] != ''){
			$info   =   $upload->upload();
			if(!$info) {// 上传错误提示错误信息       			  
				$this->error($upload->getError());    
			}else{
				$path=$info['file']['savepath'].$info['file']['savename'];
				$data['name'] = $info['file']['savename'];
				$data['filesize'] = $info['file']['size'];
			}
		}
		return $data;
	}
	
	public function edit(){
		$id=I('id');
		$data = D('bakweixi')->where('id=%d',array($id))->find();
		$users = $this->getusers();
		$equictive = $this->getequictive();
		
		$this->assign("users",$users);
		$this->assign("equictive",$equictive);
		$this->assign("data",$data);
		$this->display();
	}
	
	public function delete(){
		$id=I('id');
		$result = D('bakweixi')->where('id=%d',array($id))->delete();
		if($result){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
	
	
	
	

}

?>