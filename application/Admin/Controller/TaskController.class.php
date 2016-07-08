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
		$this->display();
	}
	public function add(){
		$list=D('instruct')->select();
        $this->assign('instruct',$list);		
        $this->display();
	}
	public function mobile(){
		$this->display();
	}

}

?>