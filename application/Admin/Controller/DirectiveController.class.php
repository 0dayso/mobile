<?php

/**
 * 指令
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class DirectiveController extends AdminbaseController {
    /**
    *指令列表
    **/
    function index(){
        $list=D('instruct')->select();
        $this->assign('list',$list);
        $this->display();
    }
    function directivelist(){
        $list=D('instruct')->select();
        $this->assign('list',$list);
        $this->display('index');
    }
    function add(){
        if(!IS_POST){
            $this->display();
            exit();
        }
        $driectivemodel=D('instruct');
        $data=$driectivemodel->create();        
        if($data){
            $result=$driectivemodel->add($data);            

            if($result){
                $this->success('增加成功');                
            }else{
                $this->error('增添加失败'.$driectivemodel->getError());
            }
        }else{            
             $this->error('增添加失败'.$driectivemodel->getError());
        }
    }
    function upload(){
        
        $this->display();
    }

}