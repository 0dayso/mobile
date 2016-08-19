<?php

/**
 * 设备管理
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class EmailController extends AdminbaseController {
	
	function _initialize() {
	    empty($_GET['upw'])?"":session("__SP_UPW__",$_GET['upw']);//设置后台登录加密码	    
		parent::_initialize();
		$this->initMenu();
	}
	
	/**
     * 后台框架首页
     */
    public function index() {
		$count=M('Emailinfo')->where('status=0')->count();
		$Page = new \Think\Page($count,11);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$Page->setConfig('first','第一页');
		$Page->setConfig('last','末页');
		$show = $Page->show();// 分页显示输出
		
		$data=M('Emailinfo')->where('status=0')->limit($Page->firstRow.','.$Page->listRows)->getfield('id,email,pwd,authorid',true);
		foreach($data as $k=>$v){
			$userinfo = $this->Getuserbyid($v['authorid']);
			$data[$k]['username'] = $userinfo['user_login'];
		}
		$this->assign('count',$count);
		$this->assign('data',$data);
		$this->assign('page',$show);
       	$this->display();
        
    }
    
    
/**
 * 解绑手机号对应的邮箱
 */    
    
     public function deparlist(){
        if(I('post.accounts')){
            $map['cate_name']=array('like','%'.I('post.accounts').'%');
        }
        $data=D('Category')->department($map);
        $this->assign('data',$data);
        $this->display();
    }   
    
    
    
    public function emaindex(){
        
		$keyword = I('keyword');
		if($keyword != ''){
			$map['email'] = array('like','%'.$keyword.'%');
			$map['phone'] = array('like','%'.$keyword.'%');
			$map['_logic'] = 'or';
			$parameters['keyword'] = $keyword;
		}                
		$count=D('Emailinfo')->where('status=2')->count(); //统计status状态下有多少条数据
		$Page = new \Think\Page($count,11);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$Page->setConfig('first','第一页');
		$Page->setConfig('last','末页');
		$show = $Page->show();// 分页显示输出
                                
		$data=M('Emailinfo')->where($map,'status=2')->limit($Page->firstRow.','.$Page->listRows)->getfield('id,email,pwd,authorid,phone',true);
		foreach($data as $k=>$v){
			$userinfo = $this->Getuserbyid($v['authorid']);
			$data[$k]['username'] = $userinfo['user_login'];
		}
		$this->assign('parameters',$parameters);
		$this->assign('count',$count);//还有多少条数据未操作
		$this->assign('data',$data);
		$this->assign('page',$show);
       	$this->display();
        
    }

    
    
    
    /**
     * 邮箱添加
     */
	public function add(){
		$sql="SELECT id,email,COUNT(*) AS ct FROM mbl_emailinfo GROUP BY id HAVING ct>1 ORDER BY ct DESC";
		$data=M()->query($sql);
		$this->assign('cq',count($data));
			
		$this->display();
	}
	
	public function addemail(){
		$this->display();
	}
	
	public function saveaddemail(){
		$emailtext = I('emailtext');
		$emaildata = explode(PHP_EOL,$emailtext);
		
		foreach($emaildata as $k=>$v){
			$v = explode('-',$v);
			$emaildatas[$k]['email'] = $v[0];
			$emaildatas[$k]['pwd'] = $v[1];
			$emaildatas[$k]['authorid'] = session("ADMIN_ID");
			$emaildatas[$k]['createtime'] = time();
			if($v[1] == ''){
				$msg = '账号密码不许为空';
			}
		}
		if($msg != ''){
			$this->error($msg);
		}
		$result=M('Emailinfo')->addAll($emaildatas);
		
		if($result){
			$this->success('保存成功',U('Email/index'));
		}else{
			$this->error('保存失败');
		}
	}
	
	public function uploademail(){
		$columns[] = 'email';
		$columns[] = 'pwd';
		$this->upload_weixin_resourse('Emailinfo',$columns);
	}
	
	public function uniqiddata(){
		try{
			$sql="SELECT id FROM mbl_emailinfo AS a WHERE EXISTS(
				    SELECT id,email FROM(
						SELECT id,`email` FROM mbl_emailinfo GROUP BY `email` HAVING COUNT(*) > 1
					)AS t
				WHERE a.email=t.email AND a.id!=t.id
			)";
			$result=M()->query($sql);
			$ary=array();
			foreach ($result as $k => $v) {		
			 	$map['id']=$v['id'];
				$sul=M('Emailinfo')->where($map)->delete();

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

