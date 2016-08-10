<?php

/**
 * 验证码处理
 */
namespace Api\Controller;
use Think\Controller;
class EquictiveController extends Controller {

    public function index() {         
        $path=ROOTPATRH."\public\script\scriptfile\\";        
    	$filelist=scandir($path);  
        // $filename=I('n');      
        // $lfiletime=I('t')   ;        
        // $t=strtotime($lfiletime);
        // $fn=$path.$filename.'.lua';
        // $sftime=filemtime($fn);    
  
        // if($t<$sftime+60){
        //     echo 1;
        // }else{
        //     echo 0;
        // }       
        // exit();

        $aryt=array();        
        foreach ($filelist as $k => $v) {
            $nbt=strpos($v,'.');            
            if($nbt>0){
                $t=substr($v,0,strpos($v,'.'));
                $timenumb=date("M j H:i",filemtime($path.$v));  
                $aryt[$t]=$timenumb;
            } 
        }     
        $sftime=filemtime($path.$filename.'.lua');

        $this->ajaxReturn($aryt,'xml');        
    }
    public function filetime(){
        $path=ROOTPATRH."\public\script\scriptfile\\";        
        //$filelist=scandir($path);  
        $filename=I('n');      
        $lfiletime=I('t')   ;        
        $t=strtotime($lfiletime);
        $fn=$path.$filename.'.lua';
        $sftime=filemtime($fn);    
  
        if($t<$sftime+60){
            echo 1;
        }else{
            echo 0;
        }       
        exit();
    }

    public function saveDeviceID(){

        $deviceid=I('cdkey');
        if(!$deviceid){
        	echo 0;
        	exit();
        }
        $device=D('equictive')->where("cdkey='%s'",array($deviceid))->find();  

        if($device){
        	$result=D('equictive')->alias('eq')->join('mbl_runcode as rc on eq.runcodeid=rc.id')->where("cdkey='%s'",array($deviceid))->find();
            if($result){           
      
                $parame['alterip']=$result['alterip'];
                $parame['weixicut']=$result['weixicut']; 
                $parame['onmoble']=$result['onmoble'];

                $mingle=unserialize($result['mingle']);
                $mustt=unserialize($result['mustt']);
                if(is_array($mustt)){
                    $parame=array_merge($parame,$mustt);
                }
                if(is_array($mingle)){
                    $parame=array_merge($parame,$mingle);
                }             
                $data['parame']=implode($parame,',');                        

                $data['mingle']=$mingle;
                $data['mustt']=$mustt;

                $data['paramegc']=unserialize($result['parame']);

                if($result['runcodeid']>0){
                    $this->ajaxReturn($data,'xml');
                }else{
                    echo 3;
                }

            }else{
                echo 0;
                exit();
            }
        	
        }else{
        	$data['cdkey']=$deviceid;
        	$data['status']=1;
        	$data['version']=I('vn');
            $data['authorid'] = session("ADMIN_ID");
        	$result=D('equictive')->add($data);
        	echo 2;
        	exit();
        }

    }


}

