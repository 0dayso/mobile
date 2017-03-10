<?php

/**
 * 设备管理
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class FriendsController extends AdminbaseController {
	
	function _initialize() {
	    empty($_GET['upw'])?"":session("__SP_UPW__",$_GET['upw']);//设置后台登录加密码	    
		parent::_initialize();
		$this->initMenu();
	}
	
	/**
     * 后台框架首页
     */
    public function index() {
		$count=M('Friends')->count();
		$Page = new \Think\Page($count,12);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$Page->setConfig('first','第一页');
		$Page->setConfig('last','末页');
		$show = $Page->show();// 分页显示输出
		
		$data=M('Friends')->order("id DESc")->limit($Page->firstRow.','.$Page->listRows)->field('id,friendtext,authorid,cdkey,createtime')->select();
		$arykey=M("equictive")->getfield("cdkey,alias",true);
		foreach($data as $k=>$v){
			$userinfo = $this->Getuserbyid($v['authorid']);
			$data[$k]['cdkey'] = $arykey[$v['cdkey']];
		}
		$this->assign('count',$count);
		$this->assign('data',$data);
		$this->assign('page',$show);
       	$this->display();
        
    }
	public function add(){
		$sql="SELECT id,friendtext,COUNT(*) AS ct FROM mbl_friends GROUP BY id HAVING ct>1 ORDER BY ct DESC";
		$data=M()->query($sql);
		$this->assign('cq',count($data));
			
		$this->display();
	}
	//删除朋友圈数据
	public function delfriends(){
		$id=I("get.id");
		if(!$id){
			$this->error("数据有误");
			exit();
		}
		$sul=M("friends")->delete($id);		
		if($sul){
			$sul=M("friendmsg")->where("frdid=%d",$id)->delete();
			if($sul){
				$this->success("数据删除成功");
			}
		}
		$this->error("数据有误");
	}

	public function friendmsgtime(){
		$id=I("get.id");	
		$data['status']=0;
		$sul=M("friendmsg")->where("frdid=%d",$id)->select();
		foreach ($sul as $key => $vo) {
			$sul[$key]['starttime']=date("Y-m-d H:i:s",$vo['starttime']);
		}
		if($sul){
			$data['data']=$sul;
			$data['status']=1;
		}
		$this->ajaxReturn($data);
	}
	
	public function addfriends(){
		if(IS_POST){
			$data["msg"]=I("post.friendtext");
			//图片转为json
			$ary=I("post.photos_url");
			$aryalt=I("post.photos_alt");	
			$smete=array();
			foreach ($ary as $key => $vo) {
				$smete[$key]['url']=$vo;
				$smete[$key]['alt']=$aryalt[$key];
			}	
			$data['img']=json_encode($smete);
			//end图片转为json
			$data['createtime']=time();

			$sul=M("friendsone")->add($data);
			if($sul){
				$this->success("增加成功");
			}else{
				$this->error("数据有误");
			}
		}else{
			$list=M("friendsone")->select();
			foreach ($list as $key => $vo) {
				$ary=json_decode($vo['img'],true);
				$list[$key]['images']=$ary;
			}
		
			$this->assign("list",$list);
			$this->display();
		}
		
	}
	public function delfriendmsg(){
		$id=I("get.id");
		$data['status']=0;
		$data['msg']="数据有误";
		if($id){
			$sul=M("friendsone")->delete($id);
			if($sul){
				$data['status']=1;
				$data['msg']="增加成功";
				$this->success("删除成功");
				exit();
			}		
		}
		$this->error("数据有误");
	}
	
	public function saveaddfriends(){
		$friendtext = I('friendtext');
		$frienddata = explode(PHP_EOL,$friendtext);
		foreach($frienddata as $k=>$v){
			$frienddatas[$k]['friendtext'] = $v;
			$frienddatas[$k]['authorid'] = session("ADMIN_ID");
			$frienddatas[$k]['createtime'] = time();
		}
		
		$result=M('Friends')->addAll($frienddatas);
		
		if($result){
			$this->success('保存成功',U('Friends/index'));
		}else{
			$this->error('保存失败');
		}
	}
	
	public function uploaddata(){
		$this->upload_weixin_resourse('Friends','friendtext');
	}
	
	public function uniqiddata(){
		try{
			$sql="SELECT id FROM mbl_friends AS a WHERE EXISTS(
				    SELECT id,friendtext FROM(
						SELECT id,`friendtext` FROM mbl_friends GROUP BY `friendtext` HAVING COUNT(*) > 1
					)AS t
				WHERE a.friendtext=t.friendtext AND a.id!=t.id
			)";
			$result=M()->query($sql);
			$ary=array();
			foreach ($result as $k => $v) {		
			 	$map['id']=$v['id'];
				$sul=M('Friends')->where($map)->delete();

			}
		}catch(Exception $ex){
			$this->error('请重新删除数据'.$ex);
		}
	   if($result){
			$this->success('已成功');
		}else{
			$this->success('已删除');
		}
	}
	//增加定时信息
	public function timingmsg(){
		if(IS_POST){	
			$data['authorid']=ADMIN_ID;//发布人				
			$data['type']=I("post.type");//类型			
			$data['friendtext']=I("post.friendtext");//内容
			$data['cdkey']=I("post.cdkey");//选择手机
			$data['createtime']=time();//创建时间
			$data['level']=I("post.level");//权限级别


		
			//图片转为json
			$ary=I("post.photos_url");
			$aryalt=I("post.photos_alt");	
			$smete=array();
			foreach ($ary as $key => $vo) {
				$smete[$key]['url']=$vo;
				$smete[$key]['alt']=$aryalt[$key];
			}	
			$data['smete']=json_encode($smete);
			//end图片转为json

			$data['status']=1;//发布信息
			$data['areatype']=I("post.areatype");//指定微信 整部手机微信，还是单个，还是1个	

			$data['mobile']=I("post.mobile");

			//得到手机号码
			if(I("post.areatype")==2){
				$where['cdkey']=I("post.cdkey");
				$where['type']=3;
				$wxary=M('weixi')->where($where)->getfield("mobile",true);
			}

			if(I("post.areatype")==1){
				$wxary=I("post.mobile");
			}

			if(count($wxary)<=0 or $wxary=="" or empty($wxary)){
				$this->error("没有选中微信号");
				exit();
			}
			
			//end得到手机号码			

			//设置得到时间
			$qtary=array();
			if(I("post.sendtype")==1){
				$qjstarttime=I("post.qjstarttime");
				$qjsendtime=I("post.qjsendtime");
				$qjsendtimesd=I("post.qjsendtimesd");

				
				$data['sendetime']=$qjsendtime;
				foreach ($qjsendtimesd as $kq => $vl) {
					if($vl==0){
						$vl=rand(9,22);						
					}
					$day=$this->count_days($qjstarttime,$qjsendtime);	
					for ($i=0; $i <=$day ; $i++) { 
						$tint=strtotime(date("Y-m-d",strtotime($qjstarttime)));
						$aryz=array();
						$aryz["starttime"]=$tint+($vl*3600)+(3600*24*$i);
						$tidn=strtotime(date("Y-m-d",strtotime($qjstarttime)));
						$aryz["endtime"]=$tint+(3600*24);
						$qtary[]=$aryz;
					}

				}
			}

			if(I("post.sendtype")==2){	
				
				

				foreach ($_POST as $kp => $vp) {					
					if(strpos($kp,"zdsendtime")!==false){					
						$dayinfo=I('post.'.$kp);
						$dayinfoa=I('post.t'.$kp);
						$data['sendstime']=I("post.zdsendtime0");
						foreach ($dayinfoa as $kd => $vd) {		

							if($vd==0){
								$vd=rand(8,20);						
							}
							$tint=strtotime(date("Y-m-d",strtotime($dayinfo)));
							$aryt=array();
							$aryt["starttime"]=$tint+($vd*3600);

							$tidn=strtotime(date("Y-m-d",strtotime($dayinfo)));
							$aryt["endtime"]=$tint+(3600*24);
							$qtary[]=$aryt;
							$data['sendetime']=$aryt["endtime"];
						}											
					}
				}
			}

			//end设置得到时间
			$data['sendtype']=I("post.sendtype");

			$map['sendtime']=strtotime(I("post.sendtime"));			   	
			$sul=M('friends')->add($data);
			if($sul){
				//增加信息表
				$datamsg=array();
				foreach ($wxary as $km=> $vm) {
					foreach ($qtary as $kq => $vq) {
						$art["mobile"]=$vm;
						$art["cdkey"]=$data['cdkey'];
						$art["createtime"]=time();
						$art['level']=$data['level'];
						$art['starttime']=$vq['starttime'];
						$art['endtime']=$vq['endtime'];
						$art['frdid']=$sul;
						$datamsg[]=$art;
					}
				}
				M("friendmsg")->addAll($datamsg);
				//end增加信息表

				$this->success("增加成功");
			}else{
				$this->error("数据有误");
			}
		}

		$equictive=M('equictive')->getfield("cdkey,alias",true);
		$this->assign("equictive",$equictive);

		$area=M('friendsarea')->getfield("id,area",true);
		$this->assign("area",$area);

		$ftype=M("friendstype")->getfield("id,name",true);
		$this->assign('ftype',$ftype);

		$this->display();
	}
	//时间天数两个数之间的天数
	public function count_days($a,$b){
	    $a_dt = getdate(strtotime($a));
	    $b_dt = getdate(strtotime($b));
	    $a_new = mktime(12, 0, 0, $a_dt['mon'], $a_dt['mday'], $a_dt['year']);
	    $b_new = mktime(12, 0, 0, $b_dt['mon'], $b_dt['mday'], $b_dt['year']);

		 $second1 = $a_new;//strtotime($day1);
	 	 $second2 =$b_new; //strtotime($day2);
	    
	  	if ($second1 < $second2) {
		    $tmp = $second2;
		    $second2 = $second1;
		    $second1 = $tmp;
		  }
		  return ($second1 - $second2) / 86400;
	}
	//信息分类
	public function cat(){
		if(IS_POST){
			$data['remark']=I("post.remark");
			$data['type']=1;
			$data['area']=I("post.area");
			$data['updatetime']=time();//修改时间
			$adres["province"]=I("post.s_province");
			$adres["city"]=I("post.s_city");
			$adres["county"]=I("post.s_county");
			if(!$adres["county"]){
				$this->error("数据有误");
			}

			$data['adrres']=json_encode($adres);
			$map["name"]=$adres["county"];
			$data['parent']=M("area")->where($map)->getfield("code");		

			$sul=M('friendsarea')->add($data);//增加分类信息
			if($sul){
				$this->success("增加成功");
			}else{
				$this->error("数据已经存在");
			}

		}
		$list=M("friendsarea")->field("id,area,remark")->select();
		$this->assign("list",$list);
		$this->display();
	}
	public function friendtype(){
		If(IS_POST){
			$data['name']=I("post.name");
			$data["type"]=1;
			$data["status"]=I("post.status");
			$data['remark']=I("post.remark");
			$imgary=I("post.photos_alt");
			$imgs=array();
			if(I("post.photos_url")){
				foreach (I("post.photos_url") as $key => $vo) {
					$imgs[$key]['url']=$vo;
					$imgs[$key]['alt']=$imgary[$key];
				}
			}

			$data['images']=json_encode($imgs);

			$sul=M("friendstype")->add($data);
			if($sul){
				$this->success("增加成功");
			}else{
				$this->error("数据已经存在");
			}
		}
		$list=M("friendstype")->field("id,name,status")->select();
		$this->assign("list",$list);
		$this->display();
	}

	public function friendarea(){	
		$map['name']=I("get.name");		
		try {
			$list=M("friendsarea")->field("mbl_friendsarea.id,area")->join("__AREA__  on __FRIENDSAREA__.parent=__AREA__.code","left")->where($map)->select();	
			if($list){
				$list['status']=1;
				$list['data']=$list;
				$this->ajaxReturn($list);
			}else{
				$list['status']=0;
				$this->ajaxReturn($list);
			}
		} catch (Exception $e) {
			$list['status']=0;
			$this->ajaxreturn($list);
		}	
		
	}

	public function showwxhao(){
		$map['cdkey']=I("get.name");
		$map['type']=3;
		$sul=M("weixi")->where($map)->getfield("mobile,weixiname");
		if($sul){
			$data['status']=1;
			$data['data']=$sul;
		}else{
			$data['status']=0;
			$data['msg']="没有微信号";
		}
		$this->ajaxreturn($data);
	}
}

