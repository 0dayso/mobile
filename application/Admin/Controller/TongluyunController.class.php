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
		if($_POST){
			$sul=M("options")->where("option_name='tlymsg'")->find();

			if(!$sul){
				$data['option_name']="tlymsg";
				$data['option_value']="哥我是张丹";
				M("option")->add($data);	
			}else{
				$map['option_name']="tlymsg";
				$data['option_value']=I("request.name");
				M("options")->where($map)->save($data);
			}
		}else{

			$this->display();
		}
	}
	function addmsg(){
		$sul=M("options")->where("option_name='tlymsg'")->getfield("option_value");
		echo $sul;
		
	}

}

?>