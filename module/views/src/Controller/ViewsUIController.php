<?php

namespace Hunter\views\Controller;

use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response\JsonResponse;
use NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder;
use NilPortugues\Sql\QueryBuilder\Syntax\OrderBy;
use Hunter\Core\Utility\StringConverter;
use Hunter\Core\Serialization\Yaml;

/**
 * Class ViewsUI.
 *
 * @package Hunter\views\Controller
 */
class ViewsUIController {

  /**
  * @var StringConverter
  */
 protected $string;

  /**
   * InstallCommand constructor.
   */
  public function __construct() {
      $this->string = new StringConverter();
      $this->builder = new GenericBuilder();
  }

  /**
   * views_list.
   *
   * @return string
   *   Return views_list string.
   */
  public function views_list() {
    $list = views_get_all();
    return view('/admin/views-list.html', array('list' => $list));
  }

  /**
   * views_add_view.
   *
   * @return string
   *   Return views_add_view string.
   */
  public function views_add_view() {
    $tables = _views_get_tables();
    return view('/admin/views-add.html', array('tables' => $tables));
  }

  /**
   * views_settings.
   *
   * @return string
   *   Return views_settings string.
   */
public function views_settings(ServerRequest $request) {
    if ($parms = $request->getParsedBody()) {
      variable_set('views_show_sql', $parms['views_show_sql']);
      return true;
    }

    $form['views_show_sql'] = array(
      '#type' => 'checkboxes',
      '#title' => '显示SQL查询',
      '#required' => TRUE,
      '#default_value' => variable_get('views_show_sql', 0),
      '#options' => array('1' => '启用'),
      '#attributes' => array('lay-skin' => 'primary', 'value' => '1'),
    );
    $form['save'] = array(
     '#type' => 'submit',
     '#value' => t('Save'),
     '#attributes' => array('lay-submit' => '', 'lay-filter' => 'ViewsConfigUpdate'),
    );

    return view('/admin/views-settings.html', array('form' => $form));
  }

  /**
   * views_view_edit.
   *
   * @return string
   *   Return views_view_edit string.
   */
  public function views_view_edit($view) {
    $view_machine_name = 'views_view_final_'.$view;
    $view_config = variable_get($view_machine_name);
    $tables = _views_get_tables();
    $phptojs = phptojs()->put(
      array(
        'edit_tables' => $tables,
        'edit_view' => $view_config
      )
    );
    return view('/admin/views-add.html', array('phptojs' => $phptojs));
  }

  /**
   * views_view_edit.
   *
   * @return string
   *   Return views_view_edit string.
   */
  public function views_view_copy($view) {
    $view_machine_name = 'views_view_final_'.$view;
    $view_config = variable_get($view_machine_name);
    if(!empty($view_config['view_name'])){
      $view_config['view_name'] = 'New '.$view_config['view_name'];
      $view_config['view_machine_name'] = $this->string->createMachineName($view_config['view_name']);
      $view_config['view_template'] = '';
    }
    $tables = _views_get_tables();
    $phptojs = phptojs()->put(
      array(
        'edit_tables' => $tables,
        'edit_view' => $view_config
      )
    );
    return view('/admin/views-add.html', array('phptojs' => $phptojs));
  }

  /**
   * views_view_export.
   *
   * @return string
   *   Return views_view_export string.
   */
  public function views_view_export($view) {
    $view_machine_name = 'views_view_final_'.$view;
    $view_config = variable_get($view_machine_name);
    $export_yml = Yaml::encode($view_config);
    hunter_download('sites/tmp/views/views_view_'.$view.'.yml', $export_yml);
  }

  /**
   * views_view_import.
   *
   * @return string
   *   Return views_view_import string.
   */
  public function views_view_import(ServerRequest $request) {
    if($parms = $request->getParsedBody()){
      $import_yml = Yaml::decode($parms['views_import']);
      if(is_array($import_yml)){
        if(!empty($parms['new_name'])){
          $import_yml['view_name'] = $parms['new_name'];
        }
        $this->views_view_save($import_yml);
        return true;
      }
    }

    $form['views_import'] = array(
      '#type' => 'textarea',
      '#title' => '粘贴你的配置',
      '#required' => TRUE,
      '#attributes' => array('id' => 'views_import', 'rows' => 20),
    );
    $form['new_name'] = array(
      '#type' => 'textfield',
      '#title' => '新名称',
      '#maxlength' => 255,
    );
    $form['save'] = array(
     '#type' => 'submit',
     '#value' => t('导入'),
     '#attributes' => array('lay-submit' => '', 'lay-filter' => 'ViewsImport'),
    );

    return view('/admin/views-import.html', array('form' => $form));
  }

  /**
   * views_view_save.
   *
   * @return string
   *   Return views_view_save string.
   */
  public function views_view_save($parms) {
    if($parms) {
      if(isset($parms['view_filters']) && !empty($parms['view_filters'])){
        foreach ($parms['view_filters'] as $key => $item) {
          $parms['view_filters'][$key]['exposed'] = $parms['view_filters'][$key]['exposed'] == 'false' ? false : true;
        }
      }
      if(isset($parms['view_sorts']) && !empty($parms['view_sorts'])){
        foreach ($parms['view_sorts'] as $key => $item) {
          $parms['view_sorts'][$key]['exposed'] = $parms['view_sorts'][$key]['exposed'] == 'false' ? false : true;
        }
      }
      $view_machine_name = $this->string->createMachineName($parms['view_name']);
      variable_set('views_view_'.$parms['type'].'_'.$view_machine_name, $parms);
    }
  }

  /**
   * views_view_delete.
   *
   * @return string
   *   Return views_view_delete string.
   */
  public function views_view_delete($view) {
    $view_machine_name = 'views_view_final_'.$view;
    variable_del($view_machine_name);
    return true;
  }

  /**
   * api_get_tables.
   *
   * @return string
   *   Return api_get_tables string.
   */
  public function api_get_machine_name(ServerRequest $request) {
    if($parms = $request->getParsedBody()){
      $machine_name = $this->string->createMachineName($parms['name']);
      return new JsonResponse($machine_name);
    }
    return new JsonResponse(false);
  }

  /**
   * api_get_tables.
   *
   * @return string
   *   Return api_get_tables string.
   */
  public function api_get_tables() {
    $tables = _views_get_tables();
    return new JsonResponse($tables);
  }

  /**
   * api_get_filter_ops.
   *
   * @return string
   *   Return api_get_filter_ops string.
   */
  public function api_get_filter_ops() {
    $operators['string'] = array(
      'equals' => array(
        'title' => 'Is equal to',
        'lable' => '=',
      ),
      'notEquals' => array(
        'title' => 'Is not equal to',
        'lable' => '!=',
      ),
      'like' => array(
        'title' => 'Like',
        'lable' => 'like',
      ),
      'notLike' => array(
        'title' => 'Not Like',
        'lable' => 'not like',
      ),
      'isNull' => array(
        'title' => 'Is empty (NULL)',
        'lable' => 'empty',
      ),
      'isNotNull' => array(
        'title' => 'Is not empty (NOT NULL)',
        'lable' => 'not empty',
      ),
    );
    $operators['number'] = array(
      'lessThan' => array(
        'title' => 'Is less than',
        'lable' => '<',
      ),
      'lessThanOrEqual' => array(
        'title' => 'Is less than or equal to',
        'lable' => '<=',
      ),
      'equals' => array(
        'title' => 'Is equal to',
        'lable' => '=',
      ),
      'notEquals' => array(
        'title' => 'Is not equal to',
        'lable' => '!=',
      ),
      'greaterThanOrEqual' => array(
        'title' => 'Is greater than or equal to',
        'lable' => '>=',
      ),
      'greaterThan' => array(
        'title' => 'Is greater than',
        'lable' => '>',
      ),
      'in' => array(
        'title' => 'Is in',
        'lable' => 'in',
      ),
      'notIn' => array(
        'title' => 'Is not in',
        'lable' => 'not in',
      ),
      'between' => array(
        'title' => 'Is between',
        'lable' => 'between',
      ),
      'notBetween' => array(
        'title' => 'Is not between',
        'lable' => 'not between',
      ),
    );
    $operators['yes-no'] = array(
      'equals' => array(
        'title' => 'Is equal to',
        'lable' => '=',
      ),
      'notEquals' => array(
        'title' => 'Is not equal to',
        'lable' => '!=',
      ),
    );

    return new JsonResponse($operators);
  }

  /**
   * api_ge_template_content.
   *
   * @return string
   *   Return api_ge_template_content string.
   */
  public function api_ge_template_content(ServerRequest $request) {
    if($parms = $request->getParsedBody()){
      if(file_exists($parms['file_name'])){
        $template_content = file_get_contents($parms['file_name']);
      }
      return new JsonResponse($template_content);
    }
    return new JsonResponse(false);
  }

  /**
   * api_get_query_result.
   *
   * @return string
   *   Return api_get_query_result string.
   */
  public function api_get_query_result(ServerRequest $request, GenericBuilder $builder) {
    if($parms = $request->getParsedBody()){
      $result = views_get_view($parms, true);

      if($result == false){
        return new JsonResponse('Empty Content !');
      }

      return new JsonResponse($result);
    }
    return new JsonResponse('Select Error !');
  }

  /**
   * api_save_view.
   *
   * @return string
   *   Return api_save_view string.
   */
  public function api_save_view(ServerRequest $request) {
    if($parms = $request->getParsedBody()){
      $parms = $this->build_views_query($parms);
      if(is_string($parms['view_query'])){
        $this->views_view_save($parms);
      }

      if($parms['type'] == 'temp'){
        if($parms['json_export'] == 'true'){
          return new JsonResponse('json_export');
        }else {
          return new JsonResponse($parms['view_template']);
        }
      }

      return new JsonResponse(true);
    }

    return new JsonResponse(false);
  }

  /**
   * api_get_templates.
   *
   * @return string
   *   Return api_get_templates string.
   */
  public function api_get_templates(ServerRequest $request) {
    $parms = $request->getParsedBody();
    if(isset($parms['rescan'])){
      $pattern = '/^' . preg_quote('views-view-', '/') . '.*' . preg_quote('.html', '/') . '$/';
      $alltemplates = array_merge(file_scan('module', $pattern, array('minDepth'=>2)), file_scan('theme', $pattern, array('minDepth'=>2)));

      if(!empty($alltemplates)){
        foreach ($alltemplates as $template) {
          if(file_exists($template['file'])){
            variable_set($template['basename'], $template['file']);
          }
        }
      }
    }

    $list = views_get_templates();

    if(!empty($list)){
      foreach ($list as $name => $value) {
        if(!file_exists($value)){
          unset($list[$name]);
          variable_del($name);
        }
      }
    }

    return new JsonResponse($list);
  }

  /**
   * api_get_permissions.
   *
   * @return string
   *   Return api_get_permissions string.
   */
  public function api_get_permissions(ServerRequest $request) {
    global $app;
    $permissions = array();
    foreach ($app->getPermissionsList() as $key => $item) {
      $permissions[] = array(
        'id' => $key,
        'name' => $item['title']
      );
    }
    return new JsonResponse($permissions);
  }

  /**
   * api_get_views_setting.
   *
   * @return string
   *   Return api_get_views_setting string.
   */
  public function api_get_views_setting(ServerRequest $request) {
    $setting['views_show_sql'] = variable_get('views_show_sql', 0);
    return new JsonResponse($setting);
  }

  /**
   * build views query.
   */
   public function build_views_query(&$view, $vars = array(), $from_exposed = false) {
     if($view['json_export'] == 'false' && !$from_exposed){
       if(!empty($view['view_template'])){
         if(!empty($view['template_content']) && $view['overwrit_template'] == 'true' && $view['type'] == 'final'){
           if (!is_dir(dirname($view['view_template']))){
             mkdir(dirname($view['view_template']), 0755, true);
           }

           file_put_contents($view['view_template'], $view['template_content']);
         }elseif (!empty($view['template_content']) && $view['type'] == 'temp') {
           $view['view_template'] = 'sites/cache/views/views_view_cache_'.$view['view_machine_name'];
           if (!is_dir(dirname($view['view_template']))){
             mkdir(dirname($view['view_template']), 0755, true);
           }

           file_put_contents($view['view_template'], $view['template_content']);
         }elseif (!empty($view['template_content']) && $view['type'] == 'final' && $view['overwrit_template'] == 'false') {
           $view['view_template'] = 'theme/'. $GLOBALS['default_theme'].'/views/'.basename($view['view_template']);
           if (!is_dir(dirname($view['view_template']))){
             mkdir(dirname($view['view_template']), 0755, true);
           }

           file_put_contents($view['view_template'], $view['template_content']);
         }
       }else {
         if(!empty($view['template_content']) && $view['type'] == 'final'){
           $view_machine_name = $this->string->createMachineName($view['view_machine_name']);
           $view['view_template'] = 'theme/'. $GLOBALS['default_theme'].'/views/views-view-'.$view_machine_name.'.html';
           if (!is_dir(dirname($view['view_template']))){
             mkdir(dirname($view['view_template']), 0755, true);
           }

           file_put_contents($view['view_template'], $view['template_content']);
         }elseif (!empty($view['template_content']) && $view['type'] == 'temp') {
           $view['view_template'] = 'sites/cache/views/views_view_cache_'.$view['view_machine_name'];
           if (!is_dir(dirname($view['view_template']))){
             mkdir(dirname($view['view_template']), 0755, true);
           }
           file_put_contents($view['view_template'], $view['template_content']);
         }
       }
     }

     $tables = _views_get_tables();
     $lfields = $rfields = $lsorts = $rsorts = array();

     foreach ($view['view_fields'] as $key => $field) {
       if(substr($field,0,strrpos($field,'.')) == $view['view_table']){
         $lfields[] = str_replace($view['view_table'].'.','',$field);
       }else {
         $rfields[] = str_replace($view['view_relation_table'].'.','',$field);
       }
     }

     if(!empty($view['view_sorts'])){
       foreach ($view['view_sorts'] as $sort) {
         if(substr($sort['field'],0,strrpos($sort['field'],'.')) == $view['view_table']){
           $sort['field'] = str_replace($view['view_table'].'.','',$sort['field']);
           $lsorts[] = $sort;
         }else {
           $sort['field'] = str_replace($view['view_relation_table'].'.','',$sort['field']);
           $rsorts[] = $sort;
         }
       }
     }

     $query = $this->builder->select()->setTable($view['view_table']);
     $query->setColumns($lfields);

     if(!empty($lsorts)){
       foreach ($lsorts as $lsort) {
         if($from_exposed || $lsort['exposed'] == 'false'){
           if($lsort['exposed'] == 'true' && isset($vars['sort_by']) && $vars['sort_by'] == $lsort['field'] && isset($vars['sort_order'])){
             $lsort['value'] = strtolower($vars['sort_order']);
           }
           if($lsort['value'] == 'desc'){
             $query->orderBy($lsort['field'], OrderBy::DESC);
           }else {
             $query->orderBy($lsort['field'], OrderBy::ASC);
           }
         }
       }
     }

     if($rfields){
       $query->innerJoin(
         $view['view_relation_table'], //join table
         $tables[$view['view_table']]['relationship'][$view['view_relation_table']]['left']['field'], //origin table field used to join
         $tables[$view['view_table']]['relationship'][$view['view_relation_table']]['right']['field'], //join column
         $rfields
       );
     }

     if(!empty($view['view_filters'])){
       foreach ($view['view_filters'] as $filter) {
         if($from_exposed || $filter['exposed'] == 'false'){
           if($filter['exposed'] == 'true' && isset($vars[$filter['exposed_setting']['identifier']]) && !empty($vars[$filter['exposed_setting']['identifier']])){
             $filter['value'] = $vars[$filter['exposed_setting']['identifier']];
           }
           $op = $filter['op'];
           if(strpos($filter['value'],'-') !== false && ($filter['op'] == 'between' || $filter['op'] == 'notBetween')){
             $v = explode('-', $filter['value']);
             $query->where()
             ->$op(substr($filter['field'], strrpos($filter['field'],'.')+1), $v[0], $v[1]);
           }elseif($filter['op'] == 'in' || $filter['op'] == 'notIn') {
             $v = explode(',', $filter['value']);
             $query->where()
             ->$op(substr($filter['field'], strrpos($filter['field'],'.')+1), $v);
           }else {
             $query->where()
             ->$op(substr($filter['field'], strrpos($filter['field'],'.')+1), $filter['value']);
           }
         }
       }
     }

     if(!empty($rsorts)){
       foreach ($rsorts as $rsort) {
         if($from_exposed || $rsort['exposed'] == 'false'){
           if($rsort['exposed'] == 'true' && isset($vars['sort_by']) && $vars['sort_by'] == $rsort['field'] && isset($vars['sort_order'])){
             $rsort['value'] = strtolower($vars['sort_order']);
           }
           if($rsort['value'] == 'desc'){
             $query->orderBy($rsort['field'], OrderBy::DESC, $view['view_relation_table']);
           }else {
             $query->orderBy($rsort['field'], OrderBy::ASC, $view['view_relation_table']);
           }
         }
       }
     }

     $view['view_query'] = $this->builder->write($query);
     $view['view_query_values'] = $this->builder->getValues();
     return $view;
   }

  /**
   * api_get_view.
   *
   * @return string
   *   Return api_get_view string.
   */
   public function api_get_view(ServerRequest $request, $vars) {
     $view = views_get_view_bypath(request_uri());
     $parms = $request->getQueryParams();
     if($view){
       if(isset($view['view_query']) && isset($view['view_template'])){
         if(!empty($view['view_filters'])){
           foreach ($view['view_filters'] as $filter) {
             if($filter['exposed'] == 'true' && isset($parms[$filter['exposed_setting']['identifier']]) && !empty($parms[$filter['exposed_setting']['identifier']])){
               $view = $this->build_views_query($view, $parms, true);
             }
           }
         }

         if(!empty($view['view_sorts'])){
           foreach ($view['view_sorts'] as $sort) {
             if($sort['exposed'] == 'true' && isset($parms['sort_by']) && $parms['sort_by'] == substr($sort['field'], strripos($sort['field'], '.')+1)){
               $view = $this->build_views_query($view, $parms, true);
             }
           }
         }

         if($view['has_pager'] && !empty($view['view_pager'])){
           $page = isset($_GET['page']) ? $_GET['page'] : 1;
           $offset = (int)$view['view_pager']['offset'];
           $number_perpage = 999999;

           if($view['view_pager']['type'] != 'display_all'){
             $offset = ((int)$page-1) * (int)$view['view_pager']['display'] + (int)$view['view_pager']['offset'];
             $pager['page'] = $page;
             $pager['size'] = (int)$view['view_pager']['display'];
             $pager['total'] = COUNT(db_query($view['view_query'], $view['view_query_values'])->fetchAll());
             $number_perpage = (int) $view['view_pager']['display'];

             theme()->getEnvironment()->addGlobal('pager', $pager);
           }

           $view['view_query'] = $view['view_query']. ' LIMIT ' . $offset . ', ' . $number_perpage;
         }

         if(!empty($view['view_query_values']) && !empty($vars)){
           foreach ($view['view_query_values'] as $key => $value) {
             if(strpos($value,':::') !== false){
               $v = explode(':::', $value);
               $v[0] = substr($v[0], strripos($v[0], '.')+1);
               $view['view_query_values'][$key] = $vars[$v[0]];
             }
           }
         }

         $result = db_query($view['view_query'], $view['view_query_values'])->fetchAll();
         if(!empty($result)) {
           $page_title = $view['view_name'];
           if(isset($view['view_title']) && !empty($view['view_title'])){
             if(module_exists('token')){
               $page_title = token_replace($view['view_title'], $result);
             }else {
               $page_title = $view['view_title'];
             }
           }
           theme()->getEnvironment()->addGlobal('page_title', $page_title);

           if($view['json_export'] == 'false'){
             $data = array('viewdata' => $result, 'parms' => $parms);
             if(count($result) == 1){
               $final_result = (array) reset($result);
               $data = array_merge($data, $final_result);
             }
             $output = theme()->render($view['view_template'], $data);
             return $output;
           }else{
             return new JsonResponse($result);
           }
         }else {
           return 'Empty Content !';
         }
       }
     }
     return false;
   }
}
