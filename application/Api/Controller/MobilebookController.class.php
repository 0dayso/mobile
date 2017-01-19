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
                   $data['username']=substr( $data['username'], 0,3);
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
            $data=M('mobilebook')->field('mid,mobile,username')->where('type=1 and isshow=0')->limit($row)->lock(true)->select();
            foreach ($data as $k => $vl) {
                $alter['type']=1;
                $alter['isshow']=array("exp","isshow+1");
                $t=M('mobilebook')->where("mid=%d",$vl['mid'])->save($alter);
                if(!$t){
                    M()->rollback();
                    echo 0;
                    exit();
                }
               // $data[$k]['username']=$vl['mobile'];
            }
            M()->commit();
        } catch (\Exception $e) {
            M()->rollback();

        }    
        $this->ajaxreturn($data);
    }
    //检测通过接口
    public function phonecheck(){
         $data['status']=0;
        if(I("REQUEST.mobile")){
                $map['mobile']=I("REQUEST.mobile");                
                $booksul=M("mobilebook")->where("mobile=%s",I("REQUEST.mobile"))->find();
                if($booksul){
                    $data['sex']=1;//性别为男;
                    $data['type']=1;
                    $data['mid']=$booksul['mid'];
                    $data['username']=$booksul['username'];
                    $data['mobile']=I("REQUEST.mobile");
                    $altsul=M('applemobile')->add($data);

                    if($altsul){
                        $data['status']=1;
                    }else{
                        $data['status']=2;
                    }
                    
                }
                //$altsul=M('applemobile')->where($map)->save($data);
            
           
        }
        
        $this->ajaxreturn($data);
    }

    public function adddata(){
        $datakey=I("REQUEST.book");
        $data=array();
        if($datakey){
            $datakey=str_replace("&quot;","\"",$datakey);
            $jsondata= json_decode($datakey,true);
            foreach ($jsondata as $key => $vl) {
                $booksul=M("mobilebook")->where("mobile=%s",$vl['phone'])->find();              
                $para['type']=1;
                $para['mid']=$booksul['mid'];
                $para['username']=$booksul['username'];
                $para['mobile']=$vl['phone'];
                $para['sex']=$vl['sex'];
                $para['wxid']=$vl['wxid'];
                $para['updatetime']=time();

                $data[]=$para;
                try {
                    $altsul=M('applemobile')->add($para);
                } catch (\Exception $e) {
                    
                }
                if($altsul){
                        $sul['sucess']++;
                }else{
                        $sul['error']++;
                }               
            }
        }
        $this->ajaxreturn($sul);               
    }

    public function addbook(){
        $datakey=I("REQUEST.book");
        $data=array();

        if($datakey){
            M()->startTrans();      
            $datakey=str_replace("&quot;","\"",$datakey);
            $jsondata= json_decode($datakey,true);
            foreach ($jsondata as $key => $vl) {
                $booksul=M("mobilebook")->where("mobilemd5='%s'",$vl['phone'])->find();              
                $para['type']=1;
                $para['mid']=$booksul['mid'];
                $para['username']=$booksul['username'];
                $para['mobile']=$booksul['mobile'];
                $para['phone']=$vl['phone'];
                $para['sex']=$vl['sex'];
                $para['wxid']=$vl['wxid'];
                $para['updatetime']=time();
                
                $data[]=$para;
                try {
                    $altsul=M('applemobile')->add($para);
                } catch (\Exception $e) {
                    
                }
                if($altsul){
                        $sul['sucess']++;
                }else{
                        $sul['error']++;
                }              
            }
            M()->commit();
        }
   
        $this->ajaxreturn($su);              
    }
}

