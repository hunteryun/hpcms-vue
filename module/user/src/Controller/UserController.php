<?php

namespace Hunter\user\Controller;

use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class user.
 *
 * @package Hunter\user\Controller
 */
class UserController {
  /**
   * user_list.
   *
   * @return string
   *   Return user_list string.
   */
  public function user_list(ServerRequest $request) {
    $user_list = get_all_user();

    $roles = get_all_role();
    $role_options = array();
    foreach ($roles as $item) {
      $role_options[$item->rid] = $item->name;
    }

    $form['username'] = array(
      '#type' => 'textfield',
      '#title' => '用户名',
      '#maxlength' => 255,
    );
    $form['password'] = array(
      '#type' => 'textfield',
      '#title' => '密码',
      '#maxlength' => 255,
    );
    $form['nickname'] = array(
      '#type' => 'textfield',
      '#title' => '昵称',
      '#maxlength' => 255,
    );
    $form['email'] = array(
      '#type' => 'textfield',
      '#title' => '邮箱',
      '#maxlength' => 255,
    );
    $form['avatar'] = array(
      '#type' => 'image',
      '#title' => '头像',
      '#attributes' => array('id' => 'avatar'),
    );
    $form['status'] = array(
      '#type' => 'radios',
      '#title' => '状态',
      '#default_value' => 'active',
      '#options' => array(
        '1' => '正常',
        '0' => '锁定',
      ),
    );
    $form['role'] = array(
      '#type' => 'checkboxes',
      '#title' => '角色',
      '#options' => $role_options,
    );

    $form['save'] = array(
     '#type' => 'submit',
     '#value' => t('提交'),
     '#attributes' => array('lay-submit' => '', 'lay-filter' => 'userAdd'),
    );
    $form['form_id'] = 'userAdd';

    return view('/admin/user-list.html', array('users' => $user_list, 'form' => $form));
  }

  /**
   * user_add.
   *
   * @return string
   *   Return user_add string.
   */
  public function user_add(ServerRequest $request) {
    if ($parms = $request->getParsedBody()) {
      $uid = db_insert('user')
        ->fields(array(
          'username' => $parms['username'],
          'nickname' => $parms['nickname'],
          'email' => $parms['email'],
          'avatar' => $parms['avatar'],
          'password' => hunter_password_hash($parms['password']),
          'status' => $parms['status'],
          'created' => time(),
          'updated' => time(),
        ))
        ->execute();

      if($uid){
        if(!empty($parms['role'])){
          foreach(array_keys($parms['role']) as $rid){
            db_insert('user_role')
              ->fields(array(
                'uid' => $uid,
                'rid' => $rid,
              ))
              ->execute();
          }
        }
      }

      return hunter_form_submit($parms, 'user', $uid);
    }

    $roles = get_all_role();
    $role_options = array();
    foreach ($roles as $item) {
      $role_options[$item->rid] = $item->name;
    }

    $form['username'] = array(
      '#type' => 'textfield',
      '#title' => '用户名',
      '#maxlength' => 255,
    );
    $form['password'] = array(
      '#type' => 'textfield',
      '#title' => '密码',
      '#maxlength' => 255,
    );
    $form['nickname'] = array(
      '#type' => 'textfield',
      '#title' => '昵称',
      '#maxlength' => 255,
    );
    $form['email'] = array(
      '#type' => 'textfield',
      '#title' => '邮箱',
      '#maxlength' => 255,
    );
    $form['avatar'] = array(
      '#type' => 'image',
      '#title' => '头像',
      '#attributes' => array('id' => 'avatar'),
    );
    $form['status'] = array(
      '#type' => 'radios',
      '#title' => '状态',
      '#default_value' => 'active',
      '#options' => array(
        '1' => '正常',
        '0' => '锁定',
      ),
    );
    $form['role'] = array(
      '#type' => 'checkboxes',
      '#title' => '角色',
      '#options' => $role_options,
    );

    $form['save'] = array(
     '#type' => 'submit',
     '#value' => t('提交'),
     '#attributes' => array('lay-submit' => '', 'lay-filter' => 'userAdd'),
    );

    return view('/admin/user-add.html', array('form' => $form));
  }

  /**
   * user_edit.
   *
   * @return string
   *   Return user_edit string.
   */
  public function user_edit($uid) {
      $user = get_user_byid($uid);
      $roles = get_all_role();
      $role_options = array();
      foreach ($roles as $item) {
        $role_options[$item->rid] = $item->name;
      }

      $form['username'] = array(
        '#type' => 'textfield',
        '#title' => '用户名',
        '#default_value' => $user->username,
        '#maxlength' => 255,
      );
      $form['password'] = array(
        '#type' => 'textfield',
        '#title' => '密码',
        '#maxlength' => 255,
        '#attributes' => array('placeholder' => t('no password no change')),
      );
      $form['nickname'] = array(
        '#type' => 'textfield',
        '#title' => '昵称',
        '#default_value' => $user->nickname,
        '#maxlength' => 255,
      );
      $form['email'] = array(
        '#type' => 'textfield',
        '#title' => '邮箱',
        '#default_value' => $user->email,
        '#maxlength' => 255,
      );
      $form['avatar'] = array(
        '#type' => 'image',
        '#title' => '头像',
        '#default_value' => $user->avatar,
        '#attributes' => array('id' => 'avatar'),
      );
      if(!empty($user->avatar)){
        $form['avatar_preview'] = array(
          '#type' => 'img',
          '#title' => '预览',
          '#default_value' => $user->avatar ? $user->avatar : '',
          '#attributes' => array('id' => 'avatar-preview', 'width' => '200'),
        );
      }
      $form['status'] = array(
        '#type' => 'radios',
        '#title' => '状态',
        '#default_value' => $user->status,
        '#options' => array(
          '1' => '正常',
          '0' => '锁定',
        ),
      );
      $form['role'] = array(
        '#type' => 'checkboxes',
        '#title' => '角色',
        '#default_value' => array_keys($user->role),
        '#options' => $role_options,
      );

      $form['uid'] = array(
        '#type' => 'hidden',
        '#value' => $uid,
      );

      $form['save'] = array(
       '#type' => 'submit',
       '#value' => t('提交'),
       '#attributes' => array('lay-submit' => '', 'lay-filter' => 'userUpdate'),
      );

      return view('/admin/user-edit.html', array('form' => $form, 'user' => $user, 'uid' => $uid));
  }

  /**
   * user_update.
   *
   * @return string
   *   Return user_update string.
   */
  public function user_update(ServerRequest $request) {
    if ($parms = $request->getParsedBody()) {
      $uid = $parms['uid'];
      $user = session()->get('admin');

      db_update('user')
       ->fields(array(
         'username' => $parms['username'],
         'nickname' => $parms['nickname'],
         'email' => $parms['email'],
         'avatar' => $parms['avatar'],
         'status' => $parms['status'],
         'updated' => time(),
       ))
       ->condition('uid', $uid)
       ->execute();

       if(!empty($parms['password'])){
         db_update('user')
          ->fields(array(
            'password' => hunter_password_hash($parms['password']),
          ))
          ->condition('uid', $uid)
          ->execute();
       }

       db_delete('user_role')
              ->condition('uid', $uid)
              ->execute();

       foreach(array_keys($parms['role']) as $rid){
          db_insert('user_role')
            ->fields(array(
              'uid' => $uid,
              'rid' => $rid,
            ))
            ->execute();
        }

        return hunter_form_submit($parms, 'user', true);
     }
     return false;
  }

  /**
   * user_del.
   *
   * @return string
   *   Return user_del string.
   */
  public function user_del($uid) {
    $result = db_delete('user')
            ->condition('uid', $uid)
            ->execute();

    if ($result) {
      return true;
    }

    return false;
  }

  /**
   * api user list.
   *
   * @return string
   *   Return api user list string.
   */
  public function api_user_list(ServerRequest $request) {
    $user_list = get_all_user();

    return new JsonResponse(array('code' => 0, 'msg' => 'success', 'data' => $user_list));
  }

  /**
   * api user id.
   *
   * @return string
   *   Return api user id string.
   */
  public function api_user_id(ServerRequest $request, $uid) {
    $user = get_user_byid($uid);

    return new JsonResponse(array('code' => 0, 'msg' => 'success', 'data' => $user));
  }

}
