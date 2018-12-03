<?php
// +----------------------------------------------------------------------
// | 海豚PHP框架 [ DolphinPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2017 河源市卓锐科技有限公司 [ http://www.zrthink.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://dolphinphp.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

 
namespace think\hello\table;
use think\hello\Hello;
use think\Exception;


use app\admin\model\Menu;
 
use app\user\model\Role;
use Cache;

/**
 * 表格构建器
 * @package app\common\builder\table
 * @author 蔡伟明 <314013107@qq.com>
 */
class Builder extends Hello
{
    /**
     * @var string 当前模型名称
     */
    private $_module = '';

    /**
     * @var string 当前控制器名称
     */
    private $_controller = '';

    /**
     * @var string 当前操作名称
     */
    private $_action = '';

    /**
     * @var string 数据表名
     */
    private $_table_name = '';

    /**
     * @var string 插件名称
     */
    private $_plugin_name = '';

    /**
     * @var string 模板路径
     */
    private $_template = '';

    /**
     * @var array 要替换的右侧按钮内容
     */
    private $_replace_right_buttons = [];

    /**
     * @var bool 有分页数据
     */
    private $_has_pages = true;

    /**
     * @var array 存储字段筛选选项
     */
    private $_filter_options = [];

    /***
     * @var array 存储字段筛选列表
     */
    private $_filter_list = [];

    /**
     * @var array 存储字段筛选类型
     */
    private $_filter_type = [];

    /**
     * @var array 列名
     */
    private $_field_name = [];

    /**
     * @var array 存储搜索框数据
     */
    private $_search = [];

    /**
     * @var array 顶部下拉菜单默认选项集合
     */
    private $_select_list_default = [];

    /**
     * @var array 行class
     */
    private $_tr_class = [];

    /**
     * @var int 前缀模式:0-不含表前缀，1-含表前缀，2-使用模型
     */
    private $_prefix = 1;

    /**
     * @var mixed 表格原始数据
     */
    private $data;

    /**
     * @var array 使用原始数据的字段
     */
    protected $rawField = [];

    /**
     * @var array 模板变量
     */
    private $_vars = [
	    'quickEdit'=>'',
		'post_url'=>'',
		'table_token'=>'',
	    'base_layout'      => VIEW_PATH.'view/layout.html',
        'page_title'         => '',       // 页面标题
        'page_tips'          => '',       // 页面提示
        'tips_type'          => '',       // 提示类型
        'tab_nav'            => [],       // 页面Tab导航
        'hide_checkbox'      => false,    // 是否隐藏第一列多选
        'extra_html'         => '',       // 额外HTML代码
        'extra_js'           => '',       // 额外JS代码
        'extra_css'          => '',       // 额外CSS代码
        'order_columns'      => [],       // 需要排序的列表头
        'filter_columns'     => [],       // 需要筛选功能的列表头
        'filter_map'         => [],       // 字段筛选的排序条件
        '_field_display'     => [],       // 字段筛选的默认选项
        '_filter_content'    => [],       // 字段筛选的默认选中值
        '_filter'            => [],       // 字段筛选的默认字段名
        'top_buttons'        => [],       // 顶部栏按钮
        'right_buttons'      => [],       // 表格右侧按钮
        'search'             => [],       // 搜索参数
        'search_button'      => false,    // 搜索按钮
        'columns'            => [],       // 表格列集合
        'pages'              => '',       // 分页数据
        'row_list'           => [],       // 表格数据列表
        '_page_info'         => '',       // 分页信息
        'primary_key'        => 'id',     // 表格主键名称
        '_table'             => '',       // 表名
        'js_list'            => [],       // js文件名
        'css_list'           => [],       // css文件名
        'validate'           => '',       // 快速编辑的验证器名
        '_js_files'          => [],       // js文件
        '_css_files'         => [],       // css文件
        '_select_list'       => [],       // 顶部下拉菜单列表
        '_filter_time'       => [],       // 时间段筛选
        'empty_tips'         => '暂无数据', // 没有数据时的提示信息
        '_search_area'       => [],       // 搜索区域
        '_search_area_url'   => '',       // 搜索区域url
        '_search_area_op'    => '',       // 搜索区域匹配方式
        'base_layout'      => VIEW_PATH.'view/layout.html',
		'tableClass'      => '',// 
		 
    ];

    /**
     * 初始化
     * @author 蔡伟明 <314013107@qq.com>
     */
    public function initialize()
    {
		parent::initialize();

		
		
		
        $this->_module     = $this->request->module();
        $this->_controller =parse_name($this->request->controller());
        $this->_action     = $this->request->action();
		//模块controller  admin.controller
        $this->_table_name = strtolower($this->_module.'_'.substr($this->_controller,strrpos($this->_controller,'.')+1));
		$this->_template   =VIEW_PATH.'table/table_bootstrap.html';
        // 默认加载快速编辑所需js和css
       // $this->_vars['_js_files'][]  = 'editable_js';
        //$this->_vars['_css_files'][] = 'editable_css';
		$this->_vars['isModal']=$this->request->param('_modal');
    }

    /**
     * 模板变量赋值
     * @param mixed $name 要显示的模板变量
     * @param string $value 变量的值
     * @author 蔡伟明 <314013107@qq.com>
     * @return $this
     */
    public function assign($name, $value = '')
    {
		if(isset($name['items'])){
			$name['items']=$this->reFields($name['items']);
			unset($name['items']);
		}
        if (is_array($name)) {
            $this->_vars = array_merge($this->_vars, $name);
        } else {
            $this->_vars[$name] = $value;
        }
        return $this;
    }


    /**
     * 替换右侧按钮
     * @param array $map 条件，格式为：['字段名' => '字段值', '字段名' => '字段值'....]
     * @param string $content 要替换的内容
     * @param null $target 要替换的目标按钮
     * @author 蔡伟明 <314013107@qq.com>
     * @return $this
     */
    public function replaceRightButton($map = [], $content = '', $target = null)
    {
        if (!empty($map)) {
            $maps   = [];
            $target = is_string($target) ? explode(',', $target) : $target;
            if (is_callable($map)) {
                $maps[] = [$map, $content, $target];
            } else {
                foreach ($map as $key => $value) {
                    if (is_array($value)) {
                        $op = strtolower($value[0]);
                        switch ($op) {
                            case '=':  $op = 'eq';  break;
                            case '<>': $op = 'neq'; break;
                            case '>':  $op = 'gt';  break;
                            case '<':  $op = 'lt';  break;
                            case '>=': $op = 'egt'; break;
                            case '<=': $op = 'elt'; break;
                            case 'in':
                            case 'not in':
                            case 'between':
                            case 'not between':
                                $value[1] = is_array($value[1]) ? $value[1] : explode(',', $value[1]); break;
                        }
                        $maps[] = [$key, $op, $value[1]];
                    } else {
                        $maps[] = [$key, 'eq', $value];
                    }
                }
            }

            $this->_replace_right_buttons[] = [
                'maps'    => $maps,
                'content' => $content,
                'target'  => $target
            ];
        }
        return $this;
    }


    /**
     * 获取默认url
     * @param string $type 按钮类型：add/enable/disable/delete
     * @param array $params 参数
     * @author 蔡伟明 <314013107@qq.com>
     * @return string
     */
    private function getDefaultUrl($type = '', $params = [])
    {
        $url = $this->_module.'/'.$this->_controller.'/'.$type;
        $MenuModel = new Menu();
        $menu  = $MenuModel->where('url_value', $url)->find();
        if ($menu['params'] != '') {
            $url_params = explode('&', trim($menu['params'], '&'));
            if (!empty($url_params)) {
                foreach ($url_params as $item) {
                    list($key, $value) = explode('=', $item);
                    $params[$key] = $value;
                }
            }
        }

        if (!empty($params) && config('url_common_param')) {
            $params = array_filter($params, function($v){return $v !== '';});
        }

        return $menu['url_type'] == 'module_home' ? home_url($url, $params) : url($url, $params);
    }



    /**
     * 创建表名Token
     * @param string $table 表名
     * @param int $prefix 前缀类型：0使用Db类(不添加表前缀)，1使用Db类(添加表前缀)，2使用模型
     * @author 蔡伟明 <314013107@qq.com>
     * @return bool|string
     */
    private function createTableToken($table = '', $prefix = 1)
    {
        $data = [
            'table'      => $table, // 表名或模型名
            'prefix'     => $prefix,
            'module'     => $this->_module,
            'controller' => $this->_controller,
            'action'     => $this->_action,
        ];

        $table_token = substr(sha1($this->_module.'-'.$this->_controller.'-'.$this->_action.'-'.$table), 0, 8);
        session($table_token, $data);
        return $table_token;
    }

 
    private function formTypes(){
	$publicParam=['name','title','type'=>'text'];
	$types=['text','switch','textedit','image','icon',
	'select'=>['options'],//格式
	'byte','datetime'
	];
		  foreach ($types as $key=>$params) {
			  if(is_array($params)) $types[$key]=array_merge($publicParam,$params);
			  else {
			  $types[$params]=$publicParam;
			  unset($types[$key]);
			  }
		  }
		  return $types;
   }
	    /**
     * 编译字段数据格式编译 field 默认key顺序['name','title','type'];
     * @author 蔡伟明 <314013107@qq.com>
     */
	private function reFields($fields,$group=false,$column=['name','type'])
	{
		 //格式重置
		$column_num=count($column);
	   	foreach ($fields as $key=>$value) {
		if(AssociativeOrIndexArray($value)<1){
		$fields[$key]=array_merge(array_combine($column,array_slice($value,0,2)),array_slice($value,2));    
		}
		if(!isset($fields[$key]['type'])) $fields[$key]['type']='text';
		}
          $types=$this->formTypes();
 
		 foreach ($fields as $key=>$params) {
		  if(isset($fields[$key]['default'])) $fields[$key]['value']=$fields[$key]['default']; 
		  $fields[$key]['title']=lang($fields[$key]['name']);//取语言文件
		  
	      foreach ($types[$params['type']] as $k=>$name) {
		if(!isset($fields[$key][$k])){
		   if(!is_numeric($k)) $fields[$key][$k]=$name;
		 }
           }
		 }
		
		 
     foreach ($fields as $key=>$params) {
		 if(isset($fields[$key]['options'])){
		 $fields[$key]['source']=str_replace( '"',"'",json_encode($fields[$key]['options']));
		 }
		if(in_array($fields[$key]['type'],['radio','select','checkbox']) && !isset($fields[$key]['options']) || (isset($fields[$key]['options']) && !$fields[$key]['options'])){
	  // throw new Exception($fields[$key]['name'].'['.$fields[$key]['title'].']类型为'.$fields[$key]['type'].'的options未设置', 8001);
		}
       }
	   
     $this->_vars['table_items'] = $fields;
	}
    /**
     * 加载模板输出
     * @param string $template 模板文件名
     * @param array  $vars     模板输出变量
     * @param array  $replace  模板替换
     * @param array  $config   模板参数
     * @author 蔡伟明 <314013107@qq.com>
     * @return mixed
     */
    public function fetch($template = '', $vars = [], $replace = [], $config = [])
    {

        if ($template != '') {
            $this->_template = $template;
        }
        
		$btns=['topBtns','rightBtns'];	// 编译按钮
	 
		foreach ($btns as &$pos) {
			if (isset($this->_vars[$pos]) && $this->_vars[$pos]){
			if (!is_array($this->_vars[$pos]) ) $this->_vars[$pos]=[$this->_vars[$pos]]; 
			foreach ($this->_vars[$pos] as $key=>$button) {
			if(!isset($button['href'])){
			$buttons=is_array($button) ? $button :explode(',',$button);
			foreach ($buttons as &$type){
				unset($this->_vars[$pos][$key]);
				 $this->_vars[$pos][] =$this->kbtn($type,$pos);
			 }
				
			}
            }
		
		

		}
		}
		
		if (isset($this->_vars['rightBtns']) && $this->_vars['rightBtns']) array_push($this->_vars['table_items'],['name'=>'_btns','title'=>'操作','type'=>'btn']);
		if(!$this->_vars['post_url'])  $this->_vars['post_url'] = $this->request->url(true);

		        // 处理页面标题
        if ($this->_vars['page_title'] == '') {
            $location = get_location('', false, false);
            if ($location) {
                $curr_location = end($location);
                $this->_vars['page_title'] = $curr_location['title'];
            }
        }
	   if(isset($this->_vars['quickEdit'])) $this->jsVars['quick_edit_url']= $this->_vars['quickEdit'];
		$this->_vars['jsVars']= $this->jsVars;
        if (!empty($vars)) {
            $this->_vars = array_merge($this->_vars, $vars);
        }
 
//dump($this->_vars);exit;
        // 实例化视图并渲染
        return parent::fetch($this->_template, $this->_vars, $replace, $config);
    }
}