<?php
namespace Api\Controller;
use Think\Controller;
class TongluyunController  extends Controller{


	function index(){
		$sul=M("options")->where("option_name='tlymsg'")->find();
		$this->ajaxreturn($sul,'xml');
	}

}

?>