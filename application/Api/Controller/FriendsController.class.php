<?php
namespace Api\Controller;
use Think\Controller;
class FriendsController  extends Controller{

	function index(){
		$sul=M("options")->where("option_name='tlymsg' or option_name='tlynmb' or option_name='towmsg'")->getfield("option_name,option_value");
		$this->ajaxreturn($sul,'xml');
	}

	//定时朋友圈发接口
	function friendnew(){
		$mobile=I("get.mobile");		
		$cdkey=I("get.id");

		$where["mobile"]=$mobile;
		$sulm=M("weixi")->where($where)->find();	

		if(!$sulm){
			$data['mobile']=$mobile;
			$data['cdkey']=$cdkey;
			$data['createtime']=time();
			$data['type']=3;
			$retrun=M("weixi")->add($data);
		}	
		$map["fm.mobile"]=$mobile;
		$map["fm.starttime"]=array("lt",time());
		$map["fm.sendnum"]=0;
		
		if(S("friendsmsg".$mobile)){
			$tmp=S("friendsmsg".$mobile);
			$sul=array_shift($tmp);
			S("friendsmsg".$mobile,$tmp,3600);
		}else{
			$sult=M("friendmsg")->alias("fm")->field("f.*,fm.*")->join("__FRIENDS__ as f on f.id=fm.frdid","left")->where($map)->limit(5)->order("fm.level desc")->select();
		
			foreach ($sult as $key => $vt) {
				$param['sendnum']=array("exp","sendnum+1");
				$param['sendtime']=time();
				$map1['id']=$vt['id'];
				$tsul=M("friendmsg")->where($map1)->save($param);//设置已发送
				if(!$tsul){
					unset($sult[$key]);
				}
			}
			if($sult){
				$sul=array_shift($sult);
				S("friendsmsg".$mobile,$sult,3600);
			}else{
				$sul=$sult;
			}
			
			
		}

		


		$pam['cdkey']=$mobile;
		$pam['mobile']=$cdkey;
		$pam['sendtime']=time();
		$pam['status']=2;
		$pam['num']=1;
		$pam['type']=1;				
		M("friendssend")->add($pam);

		if($sul){
			$param['sendnum']=array("exp","sendnum+1");
			$param['sendtime']=time();
			$map1['id']=$sul['id'];

			$tsul=M("friendmsg")->where($map1)->save($param);//设置已发送

			//处理图片
			$imgart=json_decode($sul['smete'],true);
			$mapt["id"]=$sul['type'];
			$mapt["status"]=1;
			$typimg=M("friendstype")->where($mapt)->find();
			if($typimg&&!empty($typimg["images"])){			
				$imgt=json_decode($typimg["images"],true);
				array_push($imgart,$imgt[0]);				
			}	
			if($imgart){
				foreach ($imgart as $key => $vo) {
					$sul['imga'.$key]=$vo['url'];
					$sul['ximga'.$key]=substr($vo['url'],strripos($vo['url'],".",1)+1);
				}	
			}
			
			$sul['imagnum']=count($imgart);//图片个数据
		
			if($typimg['name']){
				$sul['friendtext']="【".$typimg['name']."】".$sul["friendtext"];				
			}
			//设置已发送
			$data['data']=$sul;
			$data['status']=1;

			$this->ajaxreturn($data,'xml');
		}else{
			$data['status']=0;
			$data['msg']="没有朋友圈信息";
			$this->ajaxreturn($data,'xml');
		}
	}

	function friendone(){
		$id=I("get.id");
		if(!$id){
			$friends['status']=0;
			$this->ajaxreturn($friends,'xml');
			exit();
		}
		if(!S("friends".$id)){
			$sul=M("friendsone")->field("id,msg friendtext,img")->select();
			$data['data']=$sul;
			if($sul){
				S("friends".$id,$data,3600);
			}
		}		

		if(S("friends".$id)){
			$aryfrends=S("friends".$id);
			$friends=array_shift($aryfrends['data']);
			S("friends".$id,$aryfrends,3600);
		}

		if($friends){
			$friends['status']=1;			
			//处理图片
			$imgart=json_decode($friends['img'],true);
			$mapt["id"]=$sul['type'];
			$mapt["status"]=1;	
			if($imgart){
				foreach ($imgart as $key => $vo) {
					$friends['imga'.$key]=$vo['url'];
					$friends['ximga'.$key]=substr($vo['url'],strripos($vo['url'],".",1)+1);
				}	
			}			
			$friends['imagnum']=count($imgart);//图片个数据
		}else{
			$friends['status']=0;
		}

		$this->ajaxreturn($friends,'xml');
	}

	/**
	微信通讯录已经增加使用
	**/
	function typeyi(){
		$id=I('get.id');
		$sex=I('get.sex');//性别
		$result['status']=0;
		$result['msg']="修改失败";
		if(!$id){
			$result['status']=0;
			$result['msg']="不存在有效数据";
		}
		$data['type']=11;//已使用的数据
		$data['updatetime']=time();
		$data['sex']=$sex;

		$where['mid']=$id;
		try{
			$sul=M("applemobile")->where($where)->save($data);	

			if($sul){
				$result['status']=1;
				$result['msg']="修改成功";
			}
		}catch (\Exception $e) {
			$result['status']=0;
			$result['msg']="不存在有效数据";
		}
		
		$this->ajaxreturn($result);
	}

}

?>