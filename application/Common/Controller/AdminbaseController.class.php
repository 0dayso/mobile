<?php

/**
 * 后台Controller
 */
namespace Common\Controller;
use Common\Controller\AppframeController;

class AdminbaseController extends AppframeController {
	
	public function __construct() {
		$admintpl_path=C("SP_ADMIN_TMPL_PATH").C("SP_ADMIN_DEFAULT_THEME")."/";
		C("TMPL_ACTION_SUCCESS",$admintpl_path.C("SP_ADMIN_TMPL_ACTION_SUCCESS"));
		C("TMPL_ACTION_ERROR",$admintpl_path.C("SP_ADMIN_TMPL_ACTION_ERROR"));
		parent::__construct();
		$time=time();
		$this->assign("js_debug",APP_DEBUG?"?v=$time":"");
	}

    function _initialize(){
       parent::_initialize();
       $this->load_app_admin_menu_lang();
    	if(isset($_SESSION['ADMIN_ID'])){
    		$users_obj= M("Users");
    		$id=$_SESSION['ADMIN_ID'];
    		$user=$users_obj->where("id=$id")->find();
    		if(!$this->check_access($id)){
    			$this->error("您没有访问权限！");
    			exit();
    		}
    		$this->assign("admin",$user);
    	}else{
    		//$this->error("您还没有登录！",U("admin/public/login"));
    		if(IS_AJAX){
    			$this->error("您还没有登录！",U("admin/public/login"));
    		}else{
    			header("Location:".U("admin/public/login"));
    			exit();
    		}
    		
    	}
    }
    
    /**
     * 初始化后台菜单
     */
    public function initMenu() {
        $Menu = F("Menu");
        if (!$Menu) {
            $Menu=D("Common/Menu")->menu_cache();
        }
        return $Menu;
    }

    /**
     * 消息提示
     * @param type $message
     * @param type $jumpUrl
     * @param type $ajax 
     */
    public function success($message = '', $jumpUrl = '', $ajax = false) {
        parent::success($message, $jumpUrl, $ajax);
    }

    /**
     * 模板显示
     * @param type $templateFile 指定要调用的模板文件
     * @param type $charset 输出编码
     * @param type $contentType 输出类型
     * @param string $content 输出内容
     * 此方法作用在于实现后台模板直接存放在各自项目目录下。例如Admin项目的后台模板，直接存放在Admin/Tpl/目录下
     */
    public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '') {
        parent::display($this->parseTemplate($templateFile), $charset, $contentType);
    }
    
    /**
     * 获取输出页面内容
     * 调用内置的模板引擎fetch方法，
     * @access protected
     * @param string $templateFile 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @param string $content 模板输出内容
     * @param string $prefix 模板缓存前缀*
     * @return string
     */
    public function fetch($templateFile='',$content='',$prefix=''){
        $templateFile = empty($content)?$this->parseTemplate($templateFile):'';
		return parent::fetch($templateFile,$content,$prefix);
    }
    
    /**
     * 自动定位模板文件
     * @access protected
     * @param string $template 模板文件规则
     * @return string
     */
    public function parseTemplate($template='') {
    	$tmpl_path=C("SP_ADMIN_TMPL_PATH");
    	define("SP_TMPL_PATH", $tmpl_path);
		// 获取当前主题名称
		$theme      =    C('SP_ADMIN_DEFAULT_THEME');
		
		if(is_file($template)) {
			// 获取当前主题的模版路径
			define('THEME_PATH',   $tmpl_path.$theme."/");
			return $template;
		}
		$depr       =   C('TMPL_FILE_DEPR');
		$template   =   str_replace(':', $depr, $template);
		
		// 获取当前模块
		$module   =  MODULE_NAME."/";
		if(strpos($template,'@')){ // 跨模块调用模版文件
			list($module,$template)  =   explode('@',$template);
		}
		// 获取当前主题的模版路径
		define('THEME_PATH',   $tmpl_path.$theme."/");
		
		// 分析模板文件规则
		if('' == $template) {
			// 如果模板文件名为空 按照默认规则定位
			$template = CONTROLLER_NAME . $depr . ACTION_NAME;
		}elseif(false === strpos($template, '/')){
			$template = CONTROLLER_NAME . $depr . $template;
		}
		
		C("TMPL_PARSE_STRING.__TMPL__",__ROOT__."/".THEME_PATH);
		
		C('SP_VIEW_PATH',$tmpl_path);
		C('DEFAULT_THEME',$theme);
		define("SP_CURRENT_THEME", $theme);
		
		$file = sp_add_template_file_suffix(THEME_PATH.$module.$template);
		$file= str_replace("//",'/',$file);
		if(!file_exists_case($file)) E(L('_TEMPLATE_NOT_EXIST_').':'.$file);
		return $file;
    }

    /**
     *  排序 排序字段为listorders数组 POST 排序字段为：listorder
     */
    protected function _listorders($model) {
        if (!is_object($model)) {
            return false;
        }
        $pk = $model->getPk(); //获取主键名称
        $ids = $_POST['listorders'];
        foreach ($ids as $key => $r) {
            $data['listorder'] = $r;
            $model->where(array($pk => $key))->save($data);
        }
        return true;
    }

    /**
     * 后台分页
     * 
     */
    protected function page($total_size = 1, $page_size = 0, $current_page = 1, $listRows = 6, $pageParam = '', $pageLink = '', $static = FALSE) {
        if ($page_size == 0) {
            $page_size = C("PAGE_LISTROWS");
        }
        
        if (empty($pageParam)) {
            $pageParam = C("VAR_PAGE");
        }
        
        $Page = new \Page($total_size, $page_size, $current_page, $listRows, $pageParam, $pageLink, $static);
        $Page->SetPager('Admin', '{first}{prev}&nbsp;{liststart}{list}{listend}&nbsp;{next}{last}', array("listlong" => "9", "first" => "首页", "last" => "尾页", "prev" => "上一页", "next" => "下一页", "list" => "*", "disabledclass" => ""));
        return $Page;
    }

    private function check_access($uid){
    	//如果用户角色是1，则无需判断
    	if($uid == 1){
    		return true;
    	}
    	
    	$rule=MODULE_NAME.CONTROLLER_NAME.ACTION_NAME;
    	$no_need_check_rules=array("AdminIndexindex","AdminMainindex");
    	
    	if( !in_array($rule,$no_need_check_rules) ){
    		return sp_auth_check($uid);
    	}else{
    		return true;
    	}
    }
    
    private function load_app_admin_menu_lang(){
    	if (C('LANG_SWITCH_ON',null,false)){
    		$admin_menu_lang_file=SPAPP.MODULE_NAME."/Lang/".LANG_SET."/admin_menu.php";
    		if(is_file($admin_menu_lang_file)){
    			$lang=include $admin_menu_lang_file;
    			L($lang);
    		}
    	}
    }
	
	/**
	 *$table:表名
	 *$column:字段名
	 */
	protected function upload_weixin_resourse($table,$column){
		$config = array(    
			'maxSize'    =>    3145728, 	
			'rootPath'	 =>		'.',
			'savePath'   =>    '/public/uploads/',    
			'saveName'   =>    array('uniqid',''),    
			'exts'       =>    array('jpg', 'gif', 'png', 'jpeg','txt'),    
			'autoSub'    =>    true,    
			'subName'    =>    array('date','Ymd'),
		);
		
		if($table == 'mobile'){
			$config['saveName'] = '';
			$config['subName'] = 'mobile';
		}
		//$config['saveName'] = iconv("utf-8", "utf-8", $config['saveName']);
		$upload = new \Think\Upload($config);// 实例化上传类
		$info   =   $upload->upload();
		if(!$info) {// 上传错误提示错误信息       			  
			$this->error($upload->getError());    
		}else{// 上传成功   
			if($table == 'mobile'){
				$path=$info['file']['savepath'].$info['file']['name'];
			}else{
				$path=$info['file']['savepath'].$info['file']['savename'];
			}
			
			$data=$this->fileinfo($path);
			$rul=$this->fileaddall($table,$data,$column);
			$this->success('上传成功！');    			
		}
	}
	
	protected function fileinfo($path){
		if($this->check_utf8($path)){
			$path = iconv("utf-8", "gb2312", $path);
		}
		
		$path=getcwd().str_replace('/','\\',$path);
		//$path='D:\WWW\mobile\public\uploads\20160530\5760fe811f2dc.txt';
		if(!file_exists($path)){
			return '文件路径错误';
		}
		
		$handle = @fopen($path, "r");
		$arydata=array();
		if ($handle) {
		    while (!feof($handle)) {
		        $buffer = fgets($handle, 4096);
				$buffer = trim($buffer);
				$buffer = str_replace(' ','',$buffer);
		        $arydata[]=$buffer;
		    }
		    fclose($handle);
		}
		
		return $arydata;
	}
	
	public function fileaddall($table,$data,$column){
		$ary=array();
		
		foreach ($data as $k => $v) {
			if(!empty($v)){
				if(is_array($column)){
					$v = explode(',',$v);
					foreach($column as $k1=>$v1){
						if($table == 'Sign'){
							$v[0] = substr($v[0],0,60);
						}
						$one_da[$v1] = iconv("gb2312","utf-8",$v[$k1]);
						$one_da['authorid'] = session("ADMIN_ID");
					}
					$ary[] = $one_da;
				}else{
					if($table == 'Sign'){
						$v = substr($v,0,60);
					}
					$t[$column]=iconv("gb2312","utf-8",$v);
					$t['authorid'] = session("ADMIN_ID");
					$t['updatetime']=time();
					$t['creaetetime']=time();
					$ary[]=$t;
				}
			}
		}
		$result=M($table)->addAll($ary);
		return $result;
	}
	
	/**
	 *备份数据库表
	 */
	public function backupsdbtable(){
		$status = I('status');
		// 备份数据库
		$host = C('DB_HOST');
		$user = C('DB_USER');
		$password = C('DB_PWD');
		$dbname = C('DB_NAME');
		$dbprefix = C('DB_PREFIX');
		// 这里的账号、密码、名称都是从页面传过来的
		if (!mysql_connect($host, $user, $password)) // 连接mysql数据库
			{
				echo '数据库连接失败，请核对后再试';
			exit;
		} 
		if (!mysql_select_db($dbname)) // 是否存在该数据库
			{
				echo '不存在数据库:' . $dbname . ',请核对后再试';
			exit;
		} 
		mysql_query("set names 'utf8'");
		$mysql = "set charset utf8;\r\n";
		
		$table = $dbprefix.'mobile';
		$q2 = mysql_query("show create table `$table`");
		$sql = mysql_fetch_array($q2);
		$mysql .= $sql['Create Table'] . ";\r\n";
		if($status == 0){
			$q3 = mysql_query("select * from `$table` where `status`=0");
		}else{
			$q3 = mysql_query("select * from `$table`");
		}
		
		while ($data = mysql_fetch_assoc($q3))
		{
			$keys = array_keys($data);
			$keys = array_map('addslashes', $keys);
			$keys = join('`,`', $keys);
			$keys = "`" . $keys . "`";
			$vals = array_values($data);
			$vals = array_map('addslashes', $vals);
			$vals = join("','", $vals);
			$vals = "'" . $vals . "'";
			$mysql .= "insert into `$table`($keys) values($vals);\r\n";
		} 
		/*
		$q1 = mysql_query("show tables");
		while ($t = mysql_fetch_array($q1))
		{
			//$table = $t[0];
			$q2 = mysql_query("show create table `$table`");
			$sql = mysql_fetch_array($q2);
			$mysql .= $sql['Create Table'] . ";\r\n";
			$q3 = mysql_query("select * from `$table`");
			while ($data = mysql_fetch_assoc($q3))
			{
				$keys = array_keys($data);
				$keys = array_map('addslashes', $keys);
				$keys = join('`,`', $keys);
				$keys = "`" . $keys . "`";
				$vals = array_values($data);
				$vals = array_map('addslashes', $vals);
				$vals = join("','", $vals);
				$vals = "'" . $vals . "'";
				$mysql .= "insert into `$table`($keys) values($vals);\r\n";
			} 
		} */
		 
		$filename = './data/'.$table. ".sql"; //存放路径，默认存放到项目最外层
		$fp = fopen($filename, 'w');
		fputs($fp, $mysql);
		fclose($fp);
		echo "数据备份成功";
	}
	
	protected function Getuserbyid($id){
		$userinfo = D('users')->field('id,user_login,user_nicename')->where('id=%d',array($id))->find();
		return $userinfo;
	}
	
	/*
	 *检测utf8编码
	 */
	protected function check_utf8($str){  
		$len = strlen($str);  
		for($i = 0; $i < $len; $i++){  
			$c = ord($str[$i]);  
			if ($c > 128) {  
				if (($c > 247)) return false;  
				elseif ($c > 239) $bytes = 4;  
				elseif ($c > 223) $bytes = 3;  
				elseif ($c > 191) $bytes = 2;  
				else return false;  
				if (($i + $bytes) > $len) return false;  
				while ($bytes > 1) {  
					$i++;  
					$b = ord($str[$i]);  
					if ($b < 128 || $b > 191) return false;  
					$bytes--;  
				}  
			}  
		}  
		return true;  
	}
}