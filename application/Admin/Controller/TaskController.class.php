<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class TaskController extends AdminbaseController{
	protected $navcat_model;
    public static $numbpage=0;

	function _initialize() {
		parent::_initialize();
		$this->navcat_model =D("Common/NavCat");
	}

	public function index(){
		$list=D('runcode')->select();
		$this->assign('list',$list);
		$this->display();
	}
	public function add(){
		if(IS_POST){
			$data['taskname']=I('taskname');			
			$data['alterip']=I('alterip');
			$data['addtime']=time();

			$data['mustt']=serialize($_POST['mustt']);
			$data['mingle']=serialize($_POST['mingle']);

			$data['weixicut']=$_POST['weixicut'];
			
			$register['onmoble']=$_POST['onmoble'];
			$register['pwd']=$_POST['setpwd'];
			$register['photo']=$_POST['photo'];
			$register['nickename']=$_POST['nickename'];

			$data['onmoble']=serialize($register);
			$runcode=D('runcode');			
			$data=$runcode->create($data);
			if($data){
				$sult=$runcode->add($data);
				if($sult){
					$this->success('添加成功');
				}else{
					$this->error('增加错误'.$runcode->getError());
				}
				
			}else{
				$this->error('增加错误'.$runcode->getError());
			}

			exit();
		}
		$list=D('instruct')->select();
        $this->assign('instruct',$list);		
        $this->display();
	}
	public function mobile(){
		$this->display();
	}

}

?>