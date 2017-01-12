<?php

/**
 * apimobile
 */
namespace Api\Controller;
use Think\Controller;
class MobilebookController extends Controller {
    
	//显示之前就修改状态
    public function index() {
        M()->startTrans();        
    	$data=M('mobilebook')->field('mid,mobile,username')->where('type=1 and isshow=0')->lock(true)->find();
        try {
        
            if($data){
                $t=M('mobilebook')->where("mid=%d",$data['mid'])->setField('isshow',1);
                if(strlen($data['username'])>1){
                   $data['username']= substr( $data['username'], 0,3);
                }

                M()->commit();
                if(!$t){
                    M()->rollback();
                    echo 0;
                    exit();
                }
            }

        } catch (\Exception $e) {
            M()->rollback();
            echo 0;
            exit();
        }
        
        $this->ajaxreturn($data,"xml");
        exit();
    }

    //手机导入通讯录数据接口
    public function phonemobile(){
        $row=I("REQUEST.row");
        if(!$row){
            $row=1;
        }
         M()->startTrans();      
         try {
             $data=M('mobilebook')->field('mid,mobile,username')->where('isshow=0')->limit($row)->lock(true)->select();
            
            foreach ($data as $k => $vl) {
                $alter['type']=1;
                $alter['isshow']=array("exp","isshow+1");
                $t=M('mobilebook')->where("mid=%d",$vl['mid'])->save($alter);
                if(!$t){
                    M()->rollback();
                    echo 0;
                    exit();
                }
            }
            M()->commit();
        } catch (\Exception $e) {
            M()->rollback();
            echo 0;
            exit();
        }finally{          
            $this->ajaxreturn($data);
            exit();
        }
    }


    //检测通过接口
    public function phonecheck(){
        if(I("REQUEST.mobile")){

            try {
                $map['mobile']=I("REQUEST.mobile");
                
                $booksul=M("mobilebook")->where("mobile=%s",I("REQUEST.mobile"))->find();

                if($booksul){
                    $data['sex']=1;//性别为男;
                    $data['type']=1;
                    $data['mid']=$booksul['mid'];
                    $data['username']=$booksul['username'];
                    $altsul=M('applemobile')->add($data);
                }
                //$altsul=M('applemobile')->where($map)->save($data);
            } catch (\Exception $e) {
                
            }finally{
               
            }
           
        }
    }



}

