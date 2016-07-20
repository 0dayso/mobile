<?php

/**
 * 设备管理
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class EquipmentController extends AdminbaseController {
	
	function _initialize() {
	    empty($_GET['upw'])?"":session("__SP_UPW__",$_GET['upw']);//设置后台登录加密码	    
		parent::_initialize();
		$this->initMenu();
	}
	
    /**
     * 后台框架首页
     */
    public function index() {
    	if (C('LANG_SWITCH_ON',null,false)){
    		$this->load_menu_lang();
    	}
        $this->assign("SUBMENU_CONFIG", D("Common/Menu")->menu_json());
       	$this->display();
        
    }
    public function mobile(){
        $cate_id = I('cate_id');
		$keyword = I('keyword');
		if($cate_id != 0){
			$map['cate_id'] = $cate_id;
			$parameters['cate_id'] = $cate_id;
		}
		if($keyword != ''){
			$map1['cdkey'] = array('like','%'.$keyword.'%');
			$map1['alias'] = array('like','%'.$keyword.'%');
			$map1['_logic'] = 'or';
			$map['_complex'] = $map1;
			$parameters['keyword'] = $keyword;
		}
		
		$count=D('equictive')->where($map)->count();
		$Page = new \Think\Page($count,7,$parameters);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$Page->setConfig('first','第一页');
		$Page->setConfig('last','末页');
		$show = $Page->show();// 分页显示输出
		
		$list=D('equictive')->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $k=>$v){
			$cate_name = D('equiact')->where('id=%d',array($v['cate_id']))->getField('cate_name');
			$list[$k]['cate_name'] = $cate_name;
			
			$weiximap['cdkey'] = $v['cdkey'];
			$numb = D('weixi')->where($weiximap)->count('cdkey');
			$list[$k]['numb'] = $numb;
			$userinfo = $this->Getuserbyid($v['authorid']);
			$list[$k]['username'] = $userinfo['user_login'];
		}
		$aryi=D('runcode')->getField('id,taskname',true);
		
		$equiact = D('equiact')->select();
		
		$this->assign('parameters',$parameters);
		$this->assign('equiact',$equiact);
		$this->assign('aryi',$aryi);
        $this->assign('list',$list);
		$this->assign('page',$show);
        $this->display();
    }
	
	 public function mobilecate(){
		$id = I('id');
		$data = D('equictive')->where('id=%d',array($id))->find();
		$list = D('equiact')->select();
		
		$this->assign('list',$list);
        $this->assign('data',$data);
        $this->display();
    }
	
	public function savemobile(){
		$id = I('id');
		$data=array(
					'cate_id'=>I('cate_id')
					);
		if($id > 0){
			$result=D('equictive')->where('id=%d',array($id))->save($data);
		}
		
		if($result){
			$this->success('保存成功',U('Equipment/mobile'));
		}else{
			$this->error('保存失败');
		}
	}
	
	public function savemobileajax(){
		$id = I('id');
		$data=array(
					'alias'=>I('alias')
					);
		if($id > 0){
			$result=D('equictive')->where('id=%d',array($id))->save($data);
		}
		
		if($result){
			$this->ajaxReturn(array('result'=>1));
		}else{
			$this->ajaxReturn(array('result'=>0));
		}
	}

    public function act(){        
        $data = D('equiact')->select();
		foreach($data as $k=>$v){
			$userinfo = $this->Getuserbyid($v['authorid']);
			$data[$k]['username'] = $userinfo['user_login'];
		}
		
		$this->assign('list',$data);
        $this->display();
    }
	
	public function actinfo(){
		$id = I('id');
		$data = D('equiact')->where('id=%d',array($id))->find();
		$list=D('equictive')->where('cate_id=%d',array($id))->select();
       
		
		 $this->assign('list',$list);
		$this->assign('data',$data);
		$this->display();
	}
	
	public function actadd(){        
        $this->display();
    }
	
	public function actedit(){  
		$id = I('id');
		$data = D('equiact')->where('id=%d',array($id))->find();
		
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
			$data['createtime'] = time();
			$result=D('equiact')->where('id=%d',array($id))->save($data);
		}else{
			$data['modifytime'] = time();
			$result=D('equiact')->add($data);
		}
		if($result){
			$this->success('保存成功',U('Equipment/act'));
		}else{
			$this->error('保存失败');
		}
	}
	
	public function actdel(){
		$id = I('id');
		$result = D('equiact')->where('id=%d',array($id))->delete();
        if($result){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
    }
	
    public function info(){
        $eqid=I('id');        
        if(IS_POST&&$eqid){
            $runcode=I('post.runcode');
            $result=D('equictive')->where('id=%d',array($eqid))->setfield('runcodeid',$runcode);

            if($result){
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }
		
        $list=D('runcode')->getfield('id,taskname',true);
		$data = D('equictive')->where('id=%d',array($eqid))->find();
		
		$this->assign('data',$data);
        $this->assign('list',$list);
        $this->display();
    }
    
    private function load_menu_lang(){
    	$apps=sp_scan_dir(SPAPP."*",GLOB_ONLYDIR);
    	$error_menus=array();
    	foreach ($apps as $app){
    		if(is_dir(SPAPP.$app)){
    			$admin_menu_lang_file=SPAPP.$app."/Lang/".LANG_SET."/admin_menu.php";
    			if(is_file($admin_menu_lang_file)){
    				$lang=include $admin_menu_lang_file;
    				L($lang);
    			}
    		}
    	}
    }

}

