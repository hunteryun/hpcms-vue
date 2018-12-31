<?php

namespace Hunter\role\Controller;

use Zend\Diactoros\ServerRequest;

/**
 * Class role.
 *
 * @package Hunter\role\Controller
 */
class RoleController {
  /**
   * role_list.
   *
   * @return string
   *   Return role_list string.
   */
  public function role_list() {
    $role_list = get_all_role();

    return view('/admin/role-list.html', array('roles' => $role_list));
  }

  /**
   * role_add.
   *
   * @return string
   *   Return role_add string.
   */
  public function role_add(ServerRequest $request) {
    if ($parms = $request->getParsedBody()) {
      $user = session()->get('admin');

      $rid = db_insert('role')
        ->fields(array(
          'name' => $parms['name'],
          'weight' => $parms['weight'],
        ))
        ->execute();

      return hunter_form_submit($parms, 'role', $rid);
    }

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => '角色名称',
      '#maxlength' => 60,
    );
    $form['weight'] = array(
      '#type' => 'textfield',
      '#title' => '权重',
      '#maxlength' => 60,
    );
    $form['save'] = array(
     '#type' => 'submit',
     '#value' => t('提交'),
     '#attributes' => array('lay-submit' => '', 'lay-filter' => 'roleAdd'),
    );

    return view('/admin/role-add.html', array('form' => $form));
  }

  /**
   * role_edit.
   *
   * @return string
   *   Return role_edit string.
   */
  public function role_edit($rid) {
      $role = get_role_byid($rid);

      $form['name'] = array(
        '#type' => 'textfield',
        '#title' => '角色名称',
        '#default_value' => $role->name,
        '#maxlength' => 60,
      );
      $form['weight'] = array(
        '#type' => 'textfield',
        '#title' => '权重',
        '#default_value' => $role->weight,
        '#maxlength' => 60,
      );

      $form['rid'] = array(
        '#type' => 'hidden',
        '#value' => $rid,
      );
      $form['save'] = array(
       '#type' => 'submit',
       '#value' => t('提交'),
       '#attributes' => array('lay-submit' => '', 'lay-filter' => 'roleUpdate'),
      );

      return view('/admin/role-edit.html', array('form' => $form, 'role' => $role, 'rid' => $rid));
  }

  /**
   * role_update.
   *
   * @return string
   *   Return role_update string.
   */
  public function role_update(ServerRequest $request) {
    if ($parms = $request->getParsedBody()) {
      $rid = $parms['rid'];
      $user = session()->get('admin');

       db_update('role')
         ->fields(array(
           'name' => $parms['name'],
           'weight' => $parms['weight'],
         ))
         ->condition('rid', $rid)
         ->execute();

       return hunter_form_submit($parms, 'role', true);
     }
     return false;
  }

  /**
   * role_del.
   *
   * @return string
   *   Return role_del string.
   */
  public function role_del($rid) {
    $result = db_delete('role')
            ->condition('rid', $rid)
            ->execute();

    if ($result) {
      return true;
    }

    return false;
  }

}
