<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class TongluyunController extends AdminbaseController{
	protected $navcat_model;
    public static $numbpage=0;

	function _initialize() {
		parent::_initialize();
		$this->navcat_model =D("Common/NavCat");
	}
	function index(){
		if($_POST){
			$sul=M("option")->where("option_name='tlymsg'")->find();

			if(!$sul){
				$data['option_name']="tlymsg";
				$data['option_value']="哥我是张丹";
				M("option")->add($data);	
			}else{
				$map['option_name']="tlymsg";
				$data['option_value']=I("request.name");
				M("option")->where($map)->save($data);
			}
		}else{
			$this->display();
		}

		
	}
	function addmsg(){
		$sul=M("option")->where("option_name='tlymsg'")->getfield("option_value");
		echo $sul;
		
	}

}

?>