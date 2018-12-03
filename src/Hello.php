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
use think\facade\Request; 
use think\facade\Env;
use think\Exception;
defined('VIEW_PATH') or define('VIEW_PATH', __DIR__ . '/'); 
class Hello extends Controller
{
		 /**
     * @var array 构建器数组
     * @author 蔡伟明 <314013107@qq.com>
     */
    protected static $builder = [];

    /**
     * @var array 模板参数变量
     */
    protected static $vars = [];

    /**
     * @var string 动作
     */
    protected static $action = '';

    /**
     * 初始化
     * @author 蔡伟明 <314013107@qq.com>
     */
    public function initialize()
    {
		$this->jsVars=[
		    'jcrop_upload_url'=>url("admin/attachment/upload", ["dir" => "images", "from" => "jcrop", "module" => request()->module()]),
            'editormd_upload_url'=>url("admin/attachment/upload", ["dir" => "images", "from" => "editormd", "module" => request()->module()]),
            'editormd_mudule_path'=> '__LIBS__/editormd/lib/',
            'ueditor_upload_url'=> url("admin/attachment/upload", ["dir" => "images", "from" => "ueditor", "module" => request()->module()]),
            'wangeditor_upload_url'=> url("admin/attachment/upload", ["dir" => "images", "from" => "wangeditor", "module" => request()->module()]),
            'wangeditor_emotions'=> "__LIBS__/wang-editor/emotions.data",
            'ckeditor_img_upload_url'=> url("admin/attachment/upload", ["dir" => "images", "from" => "ckeditor", "module" => request()->module()]),
            'WebUploader_swf'=> "__LIBS__/webuploader/Uploader.swf",
            'file_upload_url'=> url("admin/attachment/upload", ["dir" => "files", "module" => request()->module()]),
            'image_upload_url'=> url("admin/attachment/upload", ["dir" => "images", "module" => request()->module()]),
            'upload_check_url'=>url("admin/ajax/check"),
	       // 'triggers'=>'', 
           // 'field_hide'=>'', // 
           // 'field_values'=>'',	
           // '_field_clear'=>'',
            'theme_url'=> url("admin/ajax/setTheme"),
            'get_level_data'=> url("admin/ajax/getLevelData"),
            'quick_edit_url'=> url("quickEdit"),
            'validate'=>'',
            'validate_fields'=>'',
            'search_field'=>'' ,
            	  
		];
		
	}
	

    /**
     * 创建各种builder的入口
     * @param string $type 构建器名称，'Form', 'Table', 'View' 或其他自定义构建器
     * @param string $action 动作
     * @author 蔡伟明 <314013107@qq.com>
     * @return table\Builder|form\Builder|aside\Builder
     * @throws Exception
     */
    public static function make($type = '', $action = '')
    {
        if ($type == '') {
            throw new Exception('未指定构建器名称', 8001);
        } else {
            $type = strtolower($type);
        }
         $class = "\\think\\hello\\". $type ."\\Builder";
            if (!class_exists($class)) {
			 throw new \ErrorException("Not found {$type} Builder type!");
        }
        if ($action != '') {
            static::$action = $action;
        } else {
            static::$action = '';
        }
        return new $class;
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
        $url = $this->request->module().'/'.$this->request->controller().'/'.$type;

        if (!empty($params) && config('url_common_param')) {
            $params = array_filter($params, function($v){return $v !== '';});
        }

        return  url($url, $params);
    }
	 public function kbtn($type = '',$pos = '', $table='')
	{
		/*
		if(is_array($type)) {
			foreach ($type as $key=>$btn) {
				$this->kbtn($btn,$pos);
			}
		}*/
		$method =($pos=='rightBtns')? 'ajax-get':'ajax-post';
		$table_token='';
        switch ($type) {
            // 新增按钮
            case 'add':
                // 默认属性
                $btn_attribute = [
                    'title' => '新增',
                    'icon'  => 'fa fa-plus-circle',
                    'class' => 'btn-primary ajax-get _modal',
                    'href'  => $this->getDefaultUrl($type )
                ];
                break;
            case 'edit':
                // 默认属性
                $btn_attribute = [
                    'title' => '编辑',
                    'icon'  => 'fa fa-fw fa-pencil-alt',
                    'class' => 'btn btn-sm btn-light '.$method.' _modal table-refresh',
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
                    'icon'        => 'fa fa-fw fa-times',
                    'class'       => 'btn btn-sm btn-danger remove  '.$method.'  confirm table_act',
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
        $vars = array_merge($vars, self::$vars);
        return parent::fetch($template, $vars, $replace, $config);
    }
}