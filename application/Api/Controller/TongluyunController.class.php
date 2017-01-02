<?php
namespace Api\Controller;
use Think\Controller;
class TongluyunController  extends Controller{

	function index(){
		$sul=M("options")->where("option_name='tlymsg' or option_name='tlynmb'")->getfield("option_name,option_value");
		$this->ajaxreturn($sul,'xml');
	}
}

?>