<?php
namespace Api\Controller;
use Think\Controller;
class TongluyunController extends AdminbaseController{


	function index(){
		$sul=M("options")->where("option_name='tlymsg'")->getfield("option_value");
		echo $sul;
		
	}

}

?>