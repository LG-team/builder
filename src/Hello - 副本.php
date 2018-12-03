<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace think\hello;
use think\Controller;
use think\facade\Request;use app\admin\model\Menu;
use think\facade\Env;
defined('VIEW_PATH') or define('VIEW_PATH', __DIR__ . '/view/');
class Hello extends Controller
{
		protected $template;
 
	   /**
     * 架构方法 设置参数
     * @access public
     * @param  array $config 配置参数
     */
    public function __construct($options=[])
    {
		parent::__construct();
        	$this->options = $options;
			if(strpos(Request::controller(),".")){
			// api/admin.test/index  表名 api_test  模型名 ApiTest
		  $this->model=(ucfirst(Request::module()).ucfirst(substr(Request::controller(),strrpos(Request::controller(),'.')+1)));
		 
		}
		else {
		$this->model=Request::controller();
	 
		}
		$this->table_token=$this->createTableToken2($this->model,2);
    }
 
	 public  function test()
    {
        return $this->template;
    }
    public   function world()
    {
	//	return $this->fetch('common@builder/table', $this->build());
		// return parent::fetch('common@builder/table',$this->build());
         return parent::fetch(VIEW_PATH.'table.html',$this->build());
      //  return 'Hello World!' ;
    }
	
	    /**
     * 编译表格数据
     * @author 蔡伟明 <314013107@qq.com>
     */
    final protected function build(){
		$options=$this->options;
		//$table=$this->table;
		//$table=(in_array($this->request->module(),['admin'])) ? $table :$this->request->module().'_'.$table;

		    // 处理页面标题
        if (!isset($options['page_title'])) {
            $location = get_location('', false, false);
            if ($location) {
                $curr_location = end($location);
                $options['page_title'] = $curr_location['title'];
            }
        }
	  $options['primary_key']=isset($options['primary_key'])?$options['primary_key']:'id';
	   //格式重置
	   	foreach ($options['fields'] as &$value) {
         $column=['name','title','type'];
		 foreach ($column as $k=>$v){
		   if(isset($value[$k])) {
			   $value[$v]=$value[$k];
		       unset($value[$k]);
		   }
		 }

		  }
	  if($options['pageType']=='table')	{
		$btns=['top_buttons','right_buttons'];	// 编译顶部按钮
		foreach ($btns as $key=>$btn) {
			 if (isset($options[$btn])){
			foreach ($options[$btn] as $ke=>$button) {
			if(is_array($button)) $options[$btn][$ke]=$button;
			 else {
			if(strpos($button,",")) 
			{
			$bbs= explode(',',$button);
			 foreach ( $bbs as $k=>$b){
				  $options[$btn][$k] =$this->kbtn($b,$btn);
			 }
			}
			else{
			$options[$btn][$ke] =$this->kbtn($button,$btn);
			}
			 }

            }
			 }
		}
  
		  $options['_js_files'][]  = 'editable_js';
          $options['_css_files'][] = 'editable_css';
	   }
		if($options['pageType']=='form' && isset($options['fields'])){
		if(!isset($options['info']))	$options['info'] = model($this->model)->get(input($options['primary_key']));
			//编辑页data info
			if(isset($options['info'])) $options['fields']=$this->setFormValue($options['fields'],$options['info']);
			if(isset($options['triggers'])){
	   $options['trigger'] = [];    // 需要触发的表单项名
       $options['field_hide']      = '';    // 需要隐藏的表单项
       $options['field_values']    = '';    // 触发表单项的值
       $options['field_clear']     = [];    // 字段清除
	   $triggers= $options['triggers'];
				foreach ($triggers as $key=>$trigger){
					 foreach ($trigger as $k=> $item) {
					$options['trigger'][$key][] = [(string)$k, $item];
					$options['field_hide'].=$item.',';
					$options['field_values'] .= $k.',';
					$options['field_clear'][$key]= 0;
					 }
			}
		// 处理需要隐藏的表单项，去除最后一个逗号
        if ($options['field_hide'] != '') {
            $options['field_hide'] = rtrim($options['field_hide'], ',');
        }
		   if ($options['field_values'] != '') {
           $options['field_values'] = explode(',', $options['field_values']);
           $options['field_values'] = array_filter($options['field_values'], 'strlen');
           $options['field_values'] = implode(',', array_unique($options['field_values']));
        }
		}
	    $options['method']=isset($options['method'])?$options['method']:'POST';
		$options['ajax_submit']=isset($options['ajax_submit'])?$options['ajax_submit']:'ajax-post';

		$options['btn_hide'] =isset($options['btn_hide'])?(is_array($options['btn_hide']) ? $options['btn_hide'] : explode(',', $options['btn_hide'])):[];
		}
				      // 支持函数处理的参数
	
       $options['post_url'] =isset($options['post_url'])?trim($post_url): Request::url(true);
	   $options['table_token']=$this->table_token;

		  //缺省设置
	     foreach ($options['fields'] as &$value) {
		 if(isset($value['sources'])){
		   $value['source']=str_replace( '"',"'",json_encode($value['sources']));
		  }
		 $value['title']=isset($value['title'])?$value['title']:$value['name'];
		 $value['value']=isset($value['value'])?$value['value']:'';
		  }
		
		
		   
	  // 处理js和css合并的参数 
	    foreach ($options['fields'] as &$item) {// 判断是否为分组
                    if (isset($item['type']) && $item['type'] == 'group') {
                        foreach ($item['options'] as &$group) {
                            foreach ($group as $key => $value) {
                                if ($group[$key]['type'] != '') {
                              $resource[]=     $this->loadMinify($group[$key]['type']);
                                }
                            }
                        }
                    } else {
                        if (isset($item['type']) && $item['type'] != '') {
                         $resource[]=       $this->loadMinify($item['type']);
                        }
                    }
                }

	foreach ($resource as &$item) {
	foreach ($item as $k=>$i) {
		$options[$k][]=$item[$k];
	 }
	 }
	isset($options['_js_files']) &&  $options['_js_files']=array_unique($options['_js_files']);
	isset( $options['_css_files']) && $options['_css_files']=  array_unique($options['_css_files']);
	//dump($options);exit;
	return $options;
	}
	
	private function kbtn($type = '',$pos = '', $table='')
	{
		$method =($pos=='right_buttons')? 'ajax-get':'ajax-post';
		$table_token=$this->table_token;
        switch ($type) {
            // 新增按钮
            case 'add':
                // 默认属性
                $btn_attribute = [
                    'title' => '新增',
                    'icon'  => 'fa fa-plus-circle',
                    'class' => 'btn btn-primary',
                    'href'  => $this->getDefaultUrl($type )
                ];
                break;
            case 'edit':
                // 默认属性
                $btn_attribute = [
                    'title' => '编辑',
                    'icon'  => 'fa fa-pencil',
                    'class' => 'btn btn-primary '.$method.' _pop table-refresh',
					'target-form' => 'id',
                    'href'  => $this->getDefaultUrl($type )
                ];
                break;

            // 启用按钮
            case 'enable':
                // 默认属性
                $btn_attribute = [
                    'title'       => '启用',
                    'icon'        => 'fa fa-check-circle-o',
                    'class'       => 'btn btn-success  '.$method.'  confirm table_act',
                    'target-form' => 'ids',
                    'href'        => $this->getDefaultUrl($type, ['_t' => $table_token])
                ];
                break;

            // 禁用按钮
            case 'disable':
                // 默认属性
                $btn_attribute = [
                    'title'       => '禁用',
                    'icon'        => 'fa fa-ban',
                    'class'       => 'btn btn-warning  '.$method.'  confirm table_act',
                    'target-form' => 'ids',
                    'href'        => $this->getDefaultUrl($type, ['_t' => $table_token])
                ];
                break;

            // 返回按钮
            case 'back':
                // 默认属性
                $btn_attribute = [
                    'title' => '返回',
                    'icon'  => 'fa fa-reply',
                    'class' => 'btn btn-info',
                    'href'  => 'javascript:history.back(-1);'
                ];
                break;

            // 删除按钮(不可恢复)
            case 'delete':
                // 默认属性
                $btn_attribute = [
                    'title'       => '删除',
                    'icon'        => 'fa fa-times-circle-o',
                    'class'       => 'btn btn-danger remove  '.$method.'  confirm table_act',
                    'target-form' => 'ids',
                    'href'        => $this->getDefaultUrl($type, ['_t' => $table_token])
                ];
                break;

            // 自定义按钮
            default:
                // 默认属性
                $btn_attribute = [
                    'title'       => '定义按钮',
                    'class'       => 'btn btn-default',
                    'target-form' => 'ids',
                    'href'        => 'javascript:void(0);'
                ];
                break;
        }

		return $btn_attribute;
}


  /**
     * 根据表单项类型，加载不同js和css文件，并合并
     * @param string $type 表单项类型
     * @author 蔡伟明 <314013107@qq.com>
     */
    private function loadMinify($type='')
    {
			
        if ($type != '') {
			$return=[];
           switch ($type) {
                case 'colorpicker':
                   $return=['_js_files'=>'colorpicker_js','_css_files'=>'colorpicker_css','_js_init'=>'colorpicker'];
                    break;
               
               case 'ckeditor':
				$return=['_ckeditor'=>'1','_js_init'=>'ckeditor'];
                    break;
                case 'date':
                case 'daterange':
				 $return=['_js_files'=>'datepicker_js','_css_files'=>'datepicker_css','_js_init'=>'datepicker'];
                    break;
                case 'datetime':
                case 'time':
				$return=['_js_files'=>'datetimepicker_js','_css_files'=>'datetimepicker_css','_js_init'=>'datetimepicker'];
                    break;
                case 'editormd':
				$return=['_js_files'=>'editormd_js','_editormd'=>'1'];
                    break;
                case 'images':
				$return=['_js_files'=>'jqueryui_js' ];
                case 'file':
                case 'files':
                case 'image':
				$return=['_js_files'=>'webuploader_js','_css_files'=>'webuploader_css'];
                    break;
                case 'icon':
				$return=['_icon'=>'1'];
                    break;
                case 'jcrop':
				$return=['_js_files'=>'jcrop_js','_css_files'=>'jcrop_css'];
                    break;
                case 'linkage':
                case 'linkages':
                case 'select':
                case 'select2':
				$return=['_js_files'=>'select2_js','_css_files'=>'select2_css','_js_init'=>'select2'];
                    break;
                case 'masked':
				$return=['_js_files'=>'masked_inputs_js' ];
                    break;
                case 'range':
				$return=['_js_files'=>'rangeslider_js','_css_files'=>'rangeslider_css','_js_init'=>'rangeslider'];
                    break;
                case 'sort':
				$return=['_js_files'=>'nestable_js','_css_files'=>'nestable_css' ];
                    break;
                case 'tags':
				$return=['_js_files'=>'tags_js','_css_files'=>'tags_css','_js_init'=>'tags-inputs'];
                    break;
                case 'ueditor':
				$return=['_ueditor'=>'1' ];
                    break;
                case 'wangeditor':
				$return=['_js_files'=>'wangeditor_js','_css_files'=>'wangeditor_css' ];
                   break;
                case 'summernote':
				$return=['_js_files'=>'summernote_js','_css_files'=>'summernote_css','_js_init'=>'summernote'];
                   
                   break;
				default: 
            }
		return $return;
        }
    }

 /**
     * 设置表单项的值
     * @author 蔡伟明 <314013107@qq.com>
     */
    private function setFormValue($form_items,$form_data)
    {
        if ($form_data) {
            foreach ($form_items as &$item) {
				//if(!isset($item['title'])) $item['title']=$item['name'];
                // 判断是否为分组
                if ($item['type'] == 'group') {
                    foreach ($item['options'] as &$group) {
                        foreach ($group as $key => $value) {
                            // 针对日期范围特殊处理
                            switch ($value['type']) {
                                case 'daterange':
                                    if ($value['name_from'] == $value['name_to']) {
                                        list($group[$key]['value_from'], $group[$key]['value_to']) = $form_data[$value['id']];
                                    } else {
                                        $group[$key]['value_from'] =$form_data[$value['name_from']];
                                        $group[$key]['value_to']   = $form_data[$value['name_to']];
                                    }
                                    break;
                                case 'datetime':
                                case 'date':
                                case 'time':
                                    if (isset($form_data[$value['name']])) {
                                        $group[$key]['value'] = $form_data[$value['name']];
                                    } else {
                                        $group[$key]['value'] = isset($value['value']) ? $value['value'] : '';
                                    }

                                    if (is_numeric($group[$key]['value'])) {
                                        if ($value['type'] == 'datetime' || $value['type'] == 'time') {
                                            $group[$key]['value'] = format_moment($group[$key]['value'], $value['format']);
                                        } else {
                                            $group[$key]['value'] = format_date($group[$key]['value'], $value['format']);
                                        }
                                    }
                                    break;
                                case 'bmap':
                                    $group[$key]['address'] = $form_data[$value['name'].'_address'];
                                default:
                                    if (isset($form_data[$value['name']])) {
                                        $group[$key]['value'] = $this->_vars['form_data'][$value['name']];
                                    } else {
                                        $group[$key]['value'] = '';
                                    }
                            }
                            if ($group[$key]['type'] == 'static' && $group[$key]['hidden'] != '') {
                                $group[$key]['hidden'] = $form_data[$value['name']];
                            }
                        }
                    }
                } else {
                    // 针对日期范围特殊处理
                    switch ($item['type']) {
                        case 'daterange':
                            if ($item['name_from'] == $item['name_to']) {
                                list($item['value_from'], $item['value_to']) =$form_data[$item['id']];
                            } else {
                                $item['value_from'] = $form_data[$item['name_from']];
                                $item['value_to']   = $form_data[$item['name_to']];
                            }
                            break;
                        case 'datetime':
                        case 'date':
                        case 'time':
                            if (isset($form_data[$item['name']])) {
                                $item['value'] = $form_data[$item['name']];
                            } else {
                                $item['value'] = isset($item['value']) ? $item['value'] : '';
                            }

                            if (is_numeric($item['value'])) {
                                if ($item['type'] == 'datetime' || $item['type'] == 'time') {
                                    $item['value'] = format_moment($item['value'], $item['format']);
                                } else {
                                    $item['value'] = format_date($item['value'], $item['format']);
                                }
                            }
                            break;
                        case 'bmap':
                            $item['address'] = $form_data[$item['name'].'_address'];
                        default:
                            if (isset($form_data[$item['name']])) {
                                $item['value'] = $form_data[$item['name']];
                            } else {
                                $item['value'] = isset($item['value']) ? $item['value'] : '';
                            }

                    }
                 //   if ($item['type'] == 'static' && $item['hidden'] != '') {
                        //$item['hidden'] = $form_data[$item['name']];
                 //   }
					
					//if($item['name']=='btn' ) $item['value']="33";
                }
			

            }
        }

		return $form_items;
    }

	    /**
     * 创建表名Token
     * @param string $table 表名
     * @param int $prefix 前缀类型：0使用Db类(不添加表前缀)，1使用Db类(添加表前缀)，2使用模型
     * @author 蔡伟明 <314013107@qq.com>
     * @return bool|string
     */
    final protected function createTableToken2($table = '', $prefix = 1)
    {
        $data = [ 
		//(in_array($this->request->module(),['admin'])) ? $table :
            'table'      =>$table, // 表名或模型名
            'prefix'     => $prefix,
            'module'     => Request::module(),
            'controller' => Request::controller(),
            'action'     =>Request::action(),
        ];
        $table_token = substr(sha1(Request::module().'-'.Request::controller().'-'.Request::action().'-'.$table), 0, 8);
        session($table_token, $data);
        return $table_token;
    }
	    /**
     * 编译HTML属性
     * @param array $attr 要编译的数据
     * @author 蔡伟明 <314013107@qq.com>
     * @return array|string
     */
    private function compileHtmlAttr($attr = []) {
        $result = [];
        if ($attr) {
            foreach ($attr as $key => &$value) {
                if ($key == 'title') {
                    $value = trim(htmlspecialchars(strip_tags(trim($value))));
                } else {
                    $value = htmlspecialchars($value);
                }
                array_push($result, "$key=\"$value\"");
            }
        }
        return implode(' ', $result);
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
        $url = Request::module().'/'.Request::controller().'/'.$type;
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

        return  url($url, $params);
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
    public function fetchaa($template = '', $vars = [], $replace = [], $config = [])
    {
        $vars = array_merge($vars, self::$vars);
        return parent::fetch($template, $vars, $replace, $config);
    }
}