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
    public function index(){
		$list=D('instruct')->select();
        $this->assign('list',$list);
        $this->display();
    }
    public function directivelist(){
        $count=M('instruct')->count();
		$Page = new \Think\Page($count,12);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$Page->setConfig('first','第一页');
		$Page->setConfig('last','末页');
		$show = $Page->show();// 分页显示输出
		
		$list=D('instruct')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $k=>$v){
			$userinfo = $this->Getuserbyid($v['authorid']);
			$list[$k]['username'] = $userinfo['user_login'];
		}
        $this->assign('list',$list);
		$this->assign('page',$show);
        $this->display('index');
    }
    public function add(){
        if(!IS_POST){
            $this->display();
            exit();
        }
        $driectivemodel=D('instruct');
        $data=$driectivemodel->create();   
		if($data['parame'] != ''){
			$parame = explode('|',$data['parame']);
			foreach($parame as $k=>$v){
				$parame[$k] = explode(':',$v);
				foreach($parame[$k] as $k1=>$v1){
					if($k1 == 0 && strpos($v1,'-') > 0){
						$parame[$k]['column'] = explode('-',$v1);
						$parame_data[$k]['column']['name'] = $parame[$k]['column']['0'];
						$parame_data[$k]['column']['text_name'] = $parame[$k]['column']['1'];
					}else if($k1 == 0 && (strpos($v1,'-') < 0 || strpos($v1,'-') == '')){
						$parame_data[$k]['column']['name'] = $v1;
					}else if($k1 == 1 && strpos($v1,',') > 0){
						$parame_data[$k]['vals'] = explode(',',$v1);
					}
				}
			}
			
			$data['parame'] = serialize($parame_data);
		}
		$data['authorid'] = session("ADMIN_ID");
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
    public function edit(){
		$id=intval(I('id'));
        $data = D('instruct')->where('id=%d',array($id))->find();
		
		$data['parame'] = unserialize($data['parame']);
		foreach($data['parame'] as $k=>$v){
			$data['parame'][$k]['column'] = implode($v['column'],'-');
			$data['parame'][$k]['vals'] = implode($v['vals'],',');
			if(!empty($v['vals'])){
				$data['parame'][$k] = implode($data['parame'][$k],':');
			}else{
				$data['parame'][$k] = $data['parame'][$k]['column'];
			}
		}
		
		$data['parame'] = implode($data['parame'],'|');
		
		$this->assign('data',$data);
        $this->display();
    }
	
	public function savedata(){
        $id=intval(I('id'));
		$driectivemodel=D('instruct');
		
        $data=$driectivemodel->create();  
		if($data['parame'] != ''){
			$parame = explode('|',$data['parame']);
			foreach($parame as $k=>$v){
				$parame[$k] = explode(':',$v);
				foreach($parame[$k] as $k1=>$v1){
					if($k1 == 0 && strpos($v1,'-') > 0){
						$parame[$k]['column'] = explode('-',$v1);
						$parame_data[$k]['column']['name'] = $parame[$k]['column']['0'];
						$parame_data[$k]['column']['text_name'] = $parame[$k]['column']['1'];
					}else if($k1 == 0 && (strpos($v1,'-') < 0 || strpos($v1,'-') == '')){
						$parame_data[$k]['column']['name'] = $v1;
					}else if($k1 == 1 && strpos($v1,',') > 0){
						$parame_data[$k]['vals'] = explode(',',$v1);
					}
				}
			}
			
			$data['parame'] = serialize($parame_data);
		}
		$data['authorid'] = session("ADMIN_ID");
        if($data){
            $result=$driectivemodel->where('id=%d',array($id))->save($data);            

            if($result){
                $this->success('保存成功');                
            }else{
                $this->error('保存失败'.$driectivemodel->getError());
            }
        }else{            
             $this->error('保存失败'.$driectivemodel->getError());
        }
    }
	
	public function delete(){
		$id=intval(I('id'));
		if($id){
			$result=D('instruct')->where('id=%d',array($id))->delete();
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