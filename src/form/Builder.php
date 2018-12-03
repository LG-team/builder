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

 
namespace think\hello\form;
use think\hello\Hello;
use think\Exception;

/**
 * 表单构建器
 * @package app\common\builder\type
 * @author 蔡伟明 <314013107@qq.com>
 */
class Builder extends Hello
{
    /**
     * @var string 模板路径
     */
    private $_template = '';

    /**
     * @var array 模板变量
     */
    private $_vars = [
        'page_title'      => '',    // 页面标题
        'page_tips'       => '',    // 页面提示
        'tips_type'       => '',    // 提示类型
        'btn_hide'        => [],    // 要隐藏的按钮
        'btn_title'       => [],    // 按钮标题
        'form_items'      => [],    // 表单项目
        'tab_nav'         => [],    // 页面Tab导航
        'post_url'        => '',    // 表单提交地址
        'form_data'       => [],    // 表单数据
        'extra_html'      => '',    // 额外HTML代码
        'extra_js'        => '',    // 额外JS代码
        'extra_css'       => '',    // 额外CSS代码
        'ajax_submit'     => true,  // 是否ajax提交
        'hide_header'     => false, // 是否隐藏表单头部标题
        'header_title'    => '',    // 表单头部标题
        'js_list'         => [],    // 需要引入的js文件名
        'css_list'        => [],    // 需要引入的css文件名
        'field_triggers'  => [],    // 需要触发的表单项名
        'field_hide'      => '',    // 需要隐藏的表单项
        'field_values'    => '',    // 触发表单项的值
        'field_clear'     => [],    // 字段清除
        '_js_files'       => [],    // 需要加载的js（合并输出）
        '_js_init'        => [],    // 初始化的js（合并输出）
        '_css_files'      => [],    // 需要加载的css（合并输出）
        '_layout'         => [],    // 布局参数
        'btn_extra'       => [],    // 额外按钮
        'submit_confirm'  => false, // 提交确认
        'extend_js_list'  => [],    // 扩展表单项js列表
        'extend_css_list' => [],    // 扩展表单项css列表
        '_method'         => 'post',// 表单提交方式
        'empty_tips'      => '暂无数据',// 没有表单项时的提示信息
		'_icon'=>1,
		'base_layout'      => VIEW_PATH.'view/layout.html',// 没有表单项时的提示信息
	 
    ];

    /**
     * @var bool 是否组合分组
     */
    private $_is_group = false;

    /**
     * 初始化
     * @author 蔡伟明 <314013107@qq.com>
     */
    public function initialize()
    {
		parent::initialize();
		$this->_vars['isModal']=$this->request->param('_modal');
		$this->_template   =VIEW_PATH.'form/form.html';
        $this->_vars['post_url'] = $this->request->url(true);
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

        if (is_array($name)) {
            $this->_vars = array_merge($this->_vars, $name);
        } else {
            $this->_vars[$name] = $value;
        }
        return $this;
    }


 
 

    /**
     * 设置触发
     * @param string $trigger 需要触发的表单项名，目前支持select（单选类型）、text、radio三种
     * @param string $values 触发的值
     * @param string $show 触发后要显示的表单项名，目前不支持普通联动、范围、拖动排序、静态文本
     * @param bool $clear 是否清除值
     * @author 蔡伟明 <314013107@qq.com>
     * @return $this
     */
    public function setTrigger($trigger = '', $values = '', $show = '', $clear = true)
    {
        if (!empty($trigger)) {
            if (is_array($trigger)) {
                foreach ($trigger as $item) {
                    $this->_vars['field_hide']   .= $item[2].',';
                    $this->_vars['field_values'] .= $item[1].',';
                    $this->_vars['field_triggers'][$item[0]][] = [(string)$item[1], $item[2]];
                    $this->_vars['field_clear'][$item[0]] = isset($item[3]) ? ($item[3] === true ? 1 : 0) : 1;
                }
            } else {
                $this->_vars['field_hide']   .= $show.',';
                $this->_vars['field_values'] .= (string)$values.',';
                $this->_vars['field_triggers'][$trigger][] = [(string)$values, $show];
                $this->_vars['field_clear'][$trigger] = $clear === true ? 1 : 0;
            }
        }
		$this->jsVars['triggers']=$this->_vars['field_triggers'];
		$this->jsVars['field_hide']=$this->_vars['field_hide'];
		$this->jsVars['field_values']=$this->_vars['field_values'];
		$this->jsVars['field_clear']=$this->_vars['field_clear'];
		
        return $this;
    }

    /**
     * 添加触发
     * @param array $triggers 触发数组
     * @author 蔡伟明 <314013107@qq.com>
     * @return $this
     */
    public function addTrigger($triggers = [])
    {
        if (!empty($triggers)) {
            $this->setTrigger($triggers);
        }
        return $this;
    }

    /**
     * 添加表单项
     * 这个是addCheckbox等方法的别名方法，第一个参数传表单项类型，其余参数与各自方法中的参数一致
     * @param string $type 表单项类型
     * @param string $name 表单项名
     * @author 蔡伟明 <314013107@qq.com>
     * @return $this
     */
    public function addFormItem($args =[])
    {
	$publicParam=['name','title','type'=>isset($args['type'])?$args['type']:'text','tips','default','extra_class','extra_attr'];
	$types=['archive','archives','gallery','icon','gallery','password','tags','textarea',
	'masked'=>['format'],//格式
	'number'=>['min','max','step'], //最小值 最大值 步进值
	'radio'=>['options'=>['否','是'],'attr'],//属性
	'checkbox'=>['options','attr'],//复选框数据 属性attr(color-颜色(default/primary/info/success/warning/danger)，默认primary size-尺寸(sm,nm,lg)，默认sm shape-形状(rounded,square)，默认rounded)
	'switch'=>['attr'],  //属性
	'range'=>['options'],//范围
	'select'=>['options'], 
	'sort'=>['value'],  //拖拽排序
	'text'=>['group'],  //标签组，可以在文本框前后添加按钮或者文字
	'static'=>['hidden'],  //静态文本 需要提交的值
	 
	'ckeditor'=>['width'=>'100%','height'=>'400'],
	'editormd'=>['watch'=>true], //markdown编辑器 是否实时预览
	'summernote'=>['width'=>'100%','height'=>'400'],//编辑器
	'ueditor'=>['width'=>'100%','height'=>'400'], 
	'wangeditor'=>['width'=>'100%','height'=>'400'], 
	
	'colorpicker'=>['mode'=>'rgba'], //模式：默认为rgba(含透明度)，也可以是rgb
	'date'=>['format'=>'yyyy-mm-dd'], //日期格式
	'datetime'=>['format'=>'yyyy-mm-dd HH:mm:ss'], //日期格式
	'date'=>['format'=>'yyyy-mm-dd'], //日期格式
	'time'=>['format'=>'HH:mm:ss'], //日期格式
	'file'=>['size'=>config('upload_file_size') * 1024,'ext'=>config('upload_file_ext')], //文件大小，单位为kb 文件后缀
	'files'=>['size'=>config('upload_file_size') * 1024,'ext'=>config('upload_file_ext')],
	'image'=>['size'=>config('upload_image_size') * 1024,'ext'=>config('upload_image_ext'),'thumb','watermark'], //文件大小，单位为kb 0为不限制 文件后缀
	'images'=>['size'=>config('upload_image_size') * 1024,'ext'=>config('upload_image_ext'),'thumb','watermark'],  
	'jcrop'=>['thumb','watermark'], 
	'linkage'=>['options','ajax_url','next_items','param'], 
	'linkages'=>['table','level'=>2,'fields'], 
	'baiduMap'=>['ak','address','level'=>12],//APPKEY 默认坐标 默认地址 地图显示级别
	];
		  foreach ($types as $key=>$params) {
			  if(is_array($params)) $types[$key]=array_merge($publicParam,$params);
			  else $types[$params]=$publicParam;
		  }
		

      dump($args);
	  exit;
    $item_d=$types[$type];
	//foreach ($item_d as $key=>$params) {
		//echo $key;
	//}
 
    foreach ($item_d as $key=>$params) {
	 if(!is_numeric($key))$item[$key]=isset($args[$key])?$args[$key]:$item_d[$key];
	 else $item[$params]= isset($args[$params])?$args[$params]:'';
    }
	if(in_array($type,['radio','select','checkbox']) && !isset($item['options'])){
	 throw new Exception($args['name'].'['.$args['title'].']类型为'.$type.'的options未设置', 8001);
		}
		if(isset($item['default'])) $item['value']=$item['default']; 
	//dump($item);
	//	  exit;
	/*	$item = [
            'type'        => $type,
            'name'        => $args['name'],
            'title'       => $args['title'], 
            'tips'        => isset($args['tips'])?$args['tips']:'', 
            'value'       => isset($args['value'])?$args['value']:'', 
            'extra_class' => isset($args['extra_class'])?$args['extra_class']:'', 
        ];
*/
        if ($this->_is_group) {
            return $item;
        }

        $this->_vars['form_items'][] = $item;
        return $this;
		
		/*
        if ($type != '') {
            // 获取所有参数值
            $args = func_get_args();
            array_shift($args);

            // 判断是否有布局参数
            if (strpos($type, ':')) {
                list($type, $this->_vars['_layout'][$name]) = explode(':', $type);
            }

            $method = 'add'. ucfirst($type);
            call_user_func_array([$this, $method], $args);
        }
        return $this;
		*/
    }

  


  
    /**
     * 设置表单数据
     * @param array $form_data 表单数据
     * @author 蔡伟明 <314013107@qq.com>
     * @return $this
     */
    public function setFormData($form_data = [])
    {
        if (!empty($form_data)) {
            $this->_vars['form_data'] = $form_data;
        }
        return $this;
    }

 


    /**
     * 引入css或js文件
     * @param string $type 类型：css/js
     * @param string $files_name 文件名，多个用逗号隔开
     * @param string $module 指定模块
     * @author caiweiming <314013107@qq.com>
     */
    private function loadFile($type = '', $files_name = '', $module = '')
    {
        if ($files_name != '') {
            $module = $module == '' ? $this->request->module() : $module;
            if (!is_array($files_name)) {
                $files_name = explode(',', $files_name);
            }
            foreach ($files_name as $item) {
                if (strpos($item, '/')) {
                    $this->_vars[$type.'_list'][] = \think\facade\Env::get('root_path'). '/public/assets/'. $item.'.'.$type;
                } else {
                    $this->_vars[$type.'_list'][] = \think\facade\Env::get('root_path'). '/public/assets/'. $module .'/'.$type.'/'.$item.'.'.$type;
                }
            }
        }
    }

    private function formTypes(){
	$publicParam=['name','title','type','tips','default','extra_class','extra_attr'];
	$types=['hidden','archive','archives','gallery','icon','gallery','password','tags','textarea','table','checkbox_tree',
	'masked'=>['format'],//格式
	'number'=>['min','max','step'], //最小值 最大值 步进值
	'radio'=>['options'=>['否','是'],'attr'],//属性
	'checkbox'=>['options'=>['否','是'],'attr'],//复选框数据 属性attr(color-颜色(default/primary/info/success/warning/danger)，默认primary size-尺寸(sm,nm,lg)，默认sm shape-形状(rounded,square)，默认rounded)
	'switch'=>['options'=>['否','是'],'attr'],  //属性
	'range'=>['options'],//范围
	'select'=>['options'], 
	'sort'=>['value'],  //拖拽排序
	'text'=>['group'],  //标签组，可以在文本框前后添加按钮或者文字
	'static'=>['hidden'],  //静态文本 需要提交的值
	 
	'ckeditor'=>['width'=>'100%','height'=>'400'],
	'editormd'=>['watch'=>true], //markdown编辑器 是否实时预览
	'summernote'=>['width'=>'100%','height'=>'400'],//编辑器
	'ueditor'=>['width'=>'100%','height'=>'400'], 
	'wangeditor'=>['width'=>'100%','height'=>'400'], 
	
	'colorpicker'=>['mode'=>'rgba'], //模式：默认为rgba(含透明度)，也可以是rgb
	'date'=>['format'=>'yyyy-mm-dd'], //日期格式
	'datetime'=>['format'=>'yyyy-mm-dd HH:mm:ss'], //日期格式
	'date'=>['format'=>'yyyy-mm-dd'], //日期格式
	'time'=>['format'=>'HH:mm:ss'], //日期格式
	'file'=>['size'=>config('upload_file_size') * 1024,'ext'=>config('upload_file_ext')], //文件大小，单位为kb 文件后缀
	'files'=>['size'=>config('upload_file_size') * 1024,'ext'=>config('upload_file_ext')],
	'image'=>['size'=>config('upload_image_size') * 1024,'ext'=>config('upload_image_ext'),'thumb','watermark'], //文件大小，单位为kb 0为不限制 文件后缀
	'images'=>['size'=>config('upload_image_size') * 1024,'ext'=>config('upload_image_ext'),'thumb','watermark'],  
	'jcrop'=>['thumb','watermark'], 
	'linkage'=>['options','ajax_url','next_items','param'], 
	'linkages'=>['table','level'=>2,'fields'], 
	'baiduMap'=>['ak','address','level'=>12],//APPKEY 默认坐标 默认地址 地图显示级别
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
		//echo AssociativeOrIndexArray($fields);exit;
		 //格式重置
	   	foreach ($fields as $key=>$value) {
		if(AssociativeOrIndexArray($value)<1){
		$fields[$key]=array_merge(array_combine($column,array_slice($value,0,2)),array_slice($value,2));    
		}
		}
         $types=$this->formTypes();
		 foreach ($fields as $key=>$params) {
		  if(isset($fields[$key]['default'])) $fields[$key]['value']=$fields[$key]['default']; 
		  $fields[$key]['title']=isset($fields[$key]['title']) && $fields[$key]['title']?$fields[$key]['title']:lang($fields[$key]['name']);//取语言文件
		  if(!isset($types[$params['type']])) unset($fields[$key]);
	     else { foreach ($types[$params['type']] as $k=>$name) {
		   if(!is_numeric($k) && !isset($fields[$key][$k])) $fields[$key][$k]=$name;
           }
		 }
		 } 
     foreach ($fields as $key=>$params) {
		$this->loadMinify($fields[$key]['type']);
        $fields[$key]['tpl'] ='../vendor/kingang/builder/src/form/items/'.$fields[$key]['type'].'.html';
		if(in_array($fields[$key]['type'],['radio','select','checkbox','switch']) && !isset($fields[$key]['options'])) {
		$fields[$key]['options']= $types[$fields[$key]['type']]['options'];	
	   //throw new Exception($fields[$key]['name'].'['.$fields[$key]['title'].']类型为'.$fields[$key]['type'].'的options未设置', 8001);
		}
		 if(isset($fields[$key]['options']) && !is_array($fields[$key]['options']) && $fields[$key]['options']){
 
		 $Toptions=explode(',',$fields[$key]['options']);
		 IF(count($Toptions)>0){
		 foreach ($Toptions as $X=>$Y){
			 $YY=explode(':',$Y);
			 
			 $XX[$YY[0]]=$YY[1];
		 }
		 $fields[$key]['options']=$XX;
		 }
		 }
       }
	  return  $fields;
 // dump($fields);exit;

    //$this->_vars['form_items'] = $fields;
	}
	 

    /**
     * 根据表单项类型，加载不同js和css文件，并合并
     * @param string $type 表单项类型
     * @author 蔡伟明 <314013107@qq.com>
     */
    private function loadMinify($type = '',$form_items_key='')
    {
        if ($type != '') {
            switch ($type) {
                case 'colorpicker':
                    $this->_vars['_js_files'][]  = 'colorpicker_js';
                    $this->_vars['_css_files'][] = 'colorpicker_css';
                    $this->_vars['_js_init'][]   = 'colorpicker';
                    break;
                case 'ckeditor':
                    $this->_vars['_ckeditor']  = '1';
                    $this->_vars['_js_init'][] = 'ckeditor';
                    break;
                case 'date':
                case 'daterange':
                    $this->_vars['_js_files'][]  = 'datepicker_js';
                    $this->_vars['_css_files'][] = 'datepicker_css';
                    $this->_vars['_js_init'][]   = 'datepicker';
                    break;
                case 'datetime':
                case 'time':
                    $this->_vars['_js_files'][]  = 'datetimepicker_js';
                    $this->_vars['_css_files'][] = 'datetimepicker_css';
                    $this->_vars['_js_init'][]   = 'datetimepicker';
                    break;
                case 'editormd':
                    $this->_vars['_js_files'][] = 'editormd_js';
                    $this->_vars['_editormd']   = '1';
                    break;
                case 'images':
                    $this->_vars['_js_files'][]  = 'jqueryui_js';
                case 'file':
                case 'files':
                case 'image':
                    $this->_vars['_js_files'][]  = 'webuploader_js';
                    $this->_vars['_css_files'][] = 'webuploader_css';
                    break;
                case 'icon':
                    $this->_vars['_icon'] = '1';
                    break;
                case 'jcrop':
                    $this->_vars['_js_files'][]  = 'jcrop_js';
                    $this->_vars['_css_files'][] = 'jcrop_css';
                    break;
                case 'linkage':
                case 'linkages':
                case 'select':
                case 'select2':
                    $this->_vars['_js_files'][]  = 'select2_js';
                    $this->_vars['_css_files'][] = 'select2_css';
                    $this->_vars['_js_init'][]   = 'select2';
                    break;
                case 'masked':
                    $this->_vars['_js_files'][] = 'masked_inputs_js';
                    break;
                case 'range':
                    $this->_vars['_js_files'][]  = 'rangeslider_js';
                    $this->_vars['_css_files'][] = 'rangeslider_css';
                    $this->_vars['_js_init'][]   = 'rangeslider';
                    break;
                case 'sort':
                    $this->_vars['_js_files'][]  = 'nestable_js';
                    $this->_vars['_css_files'][] = 'nestable_css';
                    break;
                case 'tags':
                    $this->_vars['_js_files'][]  = 'tags_js';
                    $this->_vars['_css_files'][] = 'tags_css';
                    $this->_vars['_js_init'][]   = 'tags-inputs';
                    break;
                case 'ueditor':
                    $this->_vars['_ueditor'] = '1';
                    break;
                case 'wangeditor':
                    $this->_vars['_js_files'][]  = 'wangeditor_js';
                    $this->_vars['_css_files'][] = 'wangeditor_css';
                    break;
                case 'summernote':
                    $this->_vars['_js_files'][]  = 'summernote_js';
                    $this->_vars['_css_files'][] = 'summernote_css';
                    $this->_vars['_js_init'][]   = 'summernote';
                    break;
            }
        } else {
            if ($this->_vars['form_items']) {
			 
                foreach ($this->_vars['form_items'][$form_items_key] as &$item) {
                        if ($item['type'] != '') {
                            $this->loadMinify($item['type']);
                        }
                }
            }
        }
    }

    /**
     * 设置表单项的值
     * @author 蔡伟明 <314013107@qq.com>
     */
    private function setFormValue($form_items_key)
    {
        if ($this->_vars['form_data']) {
            foreach ($this->_vars['form_items'][$form_items_key] as &$item) {
                    // 针对日期范围特殊处理
                    switch ($item['type']) {
                        case 'daterange':
                            if ($item['name_from'] == $item['name_to']) {
                                list($item['value_from'], $item['value_to']) = $this->_vars['form_data'][$item['id']];
                            } else {
                                $item['value_from'] = $this->_vars['form_data'][$item['name_from']];
                                $item['value_to']   = $this->_vars['form_data'][$item['name_to']];
                            }
                            break;
                        case 'datetime':
                        case 'date':
                        case 'time':
                            if (isset($this->_vars['form_data'][$item['name']])) {
                                $item['value'] = $this->_vars['form_data'][$item['name']];
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
                            $item['address'] = $this->_vars['form_data'][$item['name'].'_address'];
                        default:
                            if (isset($this->_vars['form_data'][$item['name']])) {
                                $item['value'] = $this->_vars['form_data'][$item['name']];
                            } else {
                                $item['value'] = isset($item['value']) ? $item['value'] : '';
                            }

                    }
                    if ($item['type'] == 'static' && $item['hidden'] != '') {
                        $item['hidden'] = $this->_vars['form_data'][$item['name']];
                    }
        
			

            }
        }
			else {
 
            foreach ($this->_vars['form_items'][$form_items_key] as &$item) {
				$item['value'] = isset($item['value']) ? $item['value'] : '';
			}
		}
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
			if(isset($this->_vars['items'])){
			if(!isset($this->_vars['tab_nav']) || is_array($this->_vars['tab_nav'])){
             $this->_vars['items']=[$this->_vars['items']];
			}
			   
			foreach($this->_vars['items'] as $key=>$items){
				$this->_vars['form_items'][]=$this->reFields($items);
			}
			unset($this->_vars['items']);
		    }
        if (!empty($vars)) {
            $this->_vars['form_data'] = array_merge($this->_vars['form_data'], $vars);
        } 
		// 设置表单值
          foreach($this->_vars['form_items'] as $key=>$items){
				$this->setFormValue($key);
				// 处理不同表单类型加载不同js和css
                 $this->loadMinify('',$key);
			}
       
        


        if(isset( $this->_vars['triggers']))  $this->addTrigger($this->_vars['triggers']);
        // 处理页面标题
        if ($this->_vars['page_title'] == '') {
            $location = get_location('', false, false);
            if ($location) {
                $curr_location = end($location);
                $this->_vars['page_title'] = $curr_location['title'];
            }
        }

        // 另外设置模板
        if ($template != '') {
            $this->_template = $template;
        }

        // 处理需要隐藏的表单项，去除最后一个逗号
        if ($this->_vars['field_hide'] != '') {
            $this->_vars['field_hide'] = rtrim($this->_vars['field_hide'], ',');
        }
        if ($this->_vars['field_values'] != '') {
            $this->_vars['field_values'] = explode(',', $this->_vars['field_values']);
            $this->_vars['field_values'] = array_filter($this->_vars['field_values'], 'strlen');
            $this->_vars['field_values'] = implode(',', array_unique($this->_vars['field_values']));
        }

		$merge=['_js_files','_css_files','_js_init'];
        // 处理js和css合并的参数
        if (!empty($this->_vars['_js_files'])) {
            $this->_vars['_js_files'] = array_unique($this->_vars['_js_files']);
            sort($this->_vars['_js_files']);
        }
        if (!empty($this->_vars['_css_files'])) {
            $this->_vars['_css_files'] = array_unique($this->_vars['_css_files']);
            sort($this->_vars['_css_files']);
        }
        if (!empty($this->_vars['_js_init'])) {
            $this->_vars['_js_init'] = array_unique($this->_vars['_js_init']);
            sort($this->_vars['_js_init']);
            $this->_vars['_js_init'] = $this->_vars['_js_init'];
        }

 
        // 处理额外按钮
        $this->_vars['btn_extra'] = implode(' ', $this->_vars['btn_extra']);
		//  dump($this->_vars);exit;
          $this->_vars['jsVars']= $this->jsVars;
        // 实例化视图并渲染
        return parent::fetch($this->_template, $this->_vars, $replace, $config);
    }
}
