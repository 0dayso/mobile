<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class TongluyunController extends AdminbaseController{
	protected $options_model;
	
	function _initialize() {
		parent::_initialize();
		$this->options_model = D("Common/Options");
	}

	function index(){
		$sul=M("options")->where("option_name='tlymsg' or option_name='tlynmb'")->getfield("option_name,option_value");		
		$data['tlymsg']=$sul["tlymsg"];
		$data['nmb']=$sul["tlynmb"];
		$this->assign("data",$data);
		$this->display();
	}

	function update(){
		if(if($_POST)){
			$map['option_name']=I("request.name");
			
			$data['option_value']=I("request.value");
			$sul1=M("options")->where($map)->find();
			if(!$sul1){
				$data['option_name']=I("request.name");
				M("options")->add($data);
				
			}else{
				M("options")->where($map)->save($data);
			}
			
		}
	}
	function addmsg(){
		$sul=M("options")->where("option_name='tlymsg'")->getfield("option_value");
		echo $sul;		
	}

}

?>