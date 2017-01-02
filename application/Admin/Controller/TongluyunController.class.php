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
				M("options")->add($data);	
			}

			$sul1=M("options")->where("option_name='tlynmb'")->find();
			if(!$sul1){
				$data['option_name']="tlynmb";
				$data['option_value']="5";
				M("options")->add($data);	
			}

			$map['option_name']="tlymsg";
			$data['option_value']=I("request.name");
			M("options")->where($map)->save($data);

			$map['option_name']="tlynmb";
			$data['option_value']=I("request.nmb");
			M("options")->where($map)->save($data);
			
			$this->success("修改成功");
		}else{
			$sul=M("options")->where("option_name='tlymsg' or option_name='tlynmb'")->getfield("option_name,option_value");		
			$data['tlymsg']=$sul["tlymsg"];
			$data['nmb']=$sul["tlynmb"];

			$this->assign("data",$data);

			$this->display();
		}
	}
	function addmsg(){
		$sul=M("options")->where("option_name='tlymsg'")->getfield("option_value");
		echo $sul;
		
	}

}

?>