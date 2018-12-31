<?php

namespace Hunter\admin\Controller;

use Zend\Diactoros\ServerRequest;
use Psr\Http\Message\UploadedFileInterface;
use Zend\Diactoros\Response\JsonResponse;
use Hunter\Core\Utility\StringConverter;
use Overtrue\Pinyin\Pinyin;
use Hunter\Core\Utility\Unicode;

/**
 * Class AdminController.
 *
 * @package Hunter\admin\Controller
 */
class AdminController {
  /**
   * admin_login.
   *
   * @return string
   *   Return admin_login string.
   */
  public function admin_login(ServerRequest $request) {
    $session = session()->get('admin');

    if(is_object($session)){
      return redirect('/admin/index');
    }else{
      if ($parms = $request->getParsedBody()) {
        $user = get_user_byname($parms['username']);

        if($user){
          if(isset($user->role) && array_key_exists(1, $user->role)){
            if (hunter_password_verify($parms['password'], $user->password)) {
              db_update('user')
                ->fields(array(
                  'accessed' => time(),
                ))
                ->condition('uid', $user->uid)
                ->execute();
              session()->set('admin', $user);
              session()->set('curuser', $user);
              return new JsonResponse(array('code' => 0, 'msg' => '登录成功'));
            } else {
              return new JsonResponse(array('code' => 1, 'msg' => '密码错误'));
            }
          }else {
            return new JsonResponse(array('code' => 1, 'msg' => '无权限访问'));
          }
        }else{
          return new JsonResponse(array('code' => 1, 'msg' => '用户不存在'));
        }
      }
      return view('/admin/login.html');
    }
  }

  /**
   * admin_index.
   *
   * @return string
   *   Return admin_index string.
   */
  public function admin_index() {
      if (!$session = session()->get('admin')) {
        return redirect('/admin/login');
      }

      return view('/admin/index.html', array('user' => (array) $session));
  }

  /**
   * admin_logout.
   *
   * @return string
   *   Return admin_logout string.
   */
  public function admin_logout() {
    session()->delete('admin');
    return redirect('/admin/login');
  }

  /**
   * configs.
   *
   * @return string
   *   Return configs string.
   */
  public function admin_configs() {
      if (!$session = session()->get('admin')) {
        return redirect('/admin/login');
      }

      $size_options = array();
      if(variable_get('image_size_options')){
        $size_options = formatTextareaValue(variable_get('image_size_options'));
      }

      return view('/admin/configs.html', array('user' => $session, 'size_options' => $size_options));
  }

  /**
   * admin_database_backup.
   *
   * @return string
   *   Return admin_database_backup string.
   */
  public function admin_database_backup() {
    $result  = db_query('SHOW TABLE STATUS')->fetchAll();
    $list = array();
    foreach($result as $row){
      $list[] = $row;
    }

    return view('/admin/backup.html', array('list' => $list));
  }

  /**
   * admin_database_backup.
   *
   * @return string
   *   Return admin_database_backup string.
   */
  public function admin_database_export(ServerRequest $request) {
    $time = date('Ymd-h-i-s', time());
    if ($parms = $request->getParsedBody()) {
      hunter_backup('sites/backup/backup_'.$time, $parms['tables']);
    }

    return '你至少需少选择一个备份的表!';
  }

  /**
   * admin_site_config.
   *
   * @return string
   *   Return admin_site_config string.
   */
  public function admin_site_config(ServerRequest $request) {
    global $app;
    $allforms = array();

    foreach ($app->getModuleHandle()->getImplementations('config_form') as $module_name) {
      $config_form = $app->getModuleHandle()->invoke($module_name, 'config_form');

      $name = '';
      if(!isset($config_form['form_id'])){
        $config_form['form_id'] = $module_name.'_config_form';
      }

      if(isset($config_form['name'])){
        $name = t($config_form['name']);
        unset($config_form['name']);
      }else {
        $name = t(ucwords($module_name).' Config');
      }

      $config_form['save'] = array(
       '#type' => 'submit',
       '#value' => t('提交'),
       '#attributes' => array('lay-submit' => '', 'lay-filter' => 'site_configAdd'),
      );

      $allforms[] = array(
        'name' => $name,
        'fields' => $config_form
      );
    }

    if ($parms = $request->getParsedBody()) {
      unset($parms['form_id']);
      unset($parms['_csrf_token_uuid']);
      unset($parms['_csrf_token']);

      foreach($parms as $key => $value){
        variable_set($key, $value);
      }

      return true;
    }

    return view('/admin/site-config.html', array('allforms' => $allforms));
  }

  /**
   * api_get_machine_name.
   *
   * @return string
   *   Return api_get_machine_name string.
   */
  public function api_get_machine_name(ServerRequest $request, Pinyin $pinyin) {
    if($parms = $request->getParsedBody()){
      $machine_name = Unicode::strtolower($pinyin->permalink($parms['name']));
      return new JsonResponse($machine_name);
    }
    return new JsonResponse(false);
  }

}
