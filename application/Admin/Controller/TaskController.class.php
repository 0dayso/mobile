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
		
		foreach ($list as $k => $v) {	
			$mustt_names = $this->getinstruct(unserialize($v['mustt']));
			$list[$k]['mustt'] = $mustt_names;
			$mingle_names = $this->getinstruct(unserialize($v['mingle']));
			$list[$k]['mingle'] = $mingle_names;
			//$list[$k]['mustt']=implode(unserialize($v['mustt']),',');
			//$list[$k]['mingle']=implode(unserialize($v['mingle']),',');
		}
		
		$this->assign('list',$list);
		$this->display();
	}
	
	protected function getinstruct($data){
		foreach($data as $k1=>$v1){
			$instruct_name = D('instruct')->where('id='.$v1)->getField('name');
			if($instruct_name != ''){
				$instruct_names .= $instruct_name.','; 
			}
		}
		$instruct_names = substr($instruct_names,0,-1);
		return $instruct_names;
	}
	
	public function taskinfo(){
		$id= I('id');
		$data = D('runcode')->where('id=%d',array($id))->find();
		$data['mustt'] = $this->getinstruct(unserialize($data['mustt']));
		$data['mingle'] = $this->getinstruct(unserialize($data['mingle']));
		$data['parame'] = unserialize($data['parame']);
		$instruct=D('instruct')->select();
		
		$this->assign('instruct',$instruct);
		$this->assign('data',$data);
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
			$data['onmoble']=$_POST['onmoble'];
			if(I('onmoble')=='132' and I('weixicut')=='121'){
				$data['weixicut']=123;
			}
			$parame['vpnuser']=I('vpnuser');
			$parame['vpnpwd']=I('vpnpwd');
			$parame['pwd']=$_POST['pwd'];
			$parame['photo']=$_POST['photo'];
			//$parame['nickename']=$_POST['nickename'];

			$data['parame']=serialize($parame);

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
	function delete(){
		$id=intval(I('id'));
		if($id){
			$result=D('runcode')->where('id=%d',array($id))->delete();
			if($result){
				$this->success('删除成功');
			}else{
				$this->error('删除失败');
			}
		}else{
			$this->error('删除失败');
		}
		
	}

}

?>