<?php

namespace Hunter\page\Controller;

use Zend\Diactoros\ServerRequest;

/**
 * Class page.
 *
 * @package Hunter\page\Controller
 */
class PageController {
  /**
   * page_list.
   *
   * @return string
   *   Return page_list string.
   */
  public function page_list(ServerRequest $request) {
    $parms = $request->getQueryParams();
    if(!isset($parms['page'])){
      $parms['page'] = 1;
    }
    $page_result = get_all_page($parms);

    return view('/admin/page-list.html', array('pages' => $page_result['list'], 'pager' => $page_result['pager']));
  }

  /**
   * page_add.
   *
   * @return string
   *   Return page_add string.
   */
  public function page_add(ServerRequest $request) {
    if ($parms = $request->getParsedBody()) {
      $user = session()->get('admin');

      $pid = db_insert('page')
        ->fields(array(
          'title' => clean($parms['title']),
          'content' => clean($parms['content']),
	        'status' => $parms['status'],
          'uid' => $user->uid,
          'created' => time(),
          'updated' => time(),
        ))
        ->execute();

      return hunter_form_submit($parms, 'page', $pid);
    }

    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => '标题',
      '#maxlength' => 255,
    );
    $form['content'] = array(
      '#type' => 'textarea',
      '#title' => '内容',
      '#required' => TRUE,
      '#attributes' => array('id' => 'content', 'lay-verify' => 'content_content'),
    );
    $form['status'] = array(
      '#type' => 'radios',
      '#title' => '状态',
      '#default_value' => '1',
      '#options' => array('1' => '发布', '0' => '草稿')
    );
    $form['save'] = array(
     '#type' => 'submit',
     '#value' => t('提交'),
     '#attributes' => array('lay-submit' => '', 'lay-filter' => 'pageAdd'),
    );

    return view('/admin/page-add.html', array('form' => $form));
  }

  /**
   * page_edit.
   *
   * @return string
   *   Return page_edit string.
   */
  public function page_edit($pid) {
      $page = get_page_byid($pid);

      $form['title'] = array(
        '#type' => 'textfield',
        '#title' => '标题',
        '#default_value' => $page->title,
        '#maxlength' => 255,
      );
      $form['content'] = array(
        '#type' => 'textarea',
        '#title' => '内容',
        '#default_value' => $page->content,
        '#required' => TRUE,
        '#attributes' => array('id' => 'content', 'lay-verify' => 'content_content'),
      );
      $form['status'] = array(
        '#type' => 'radios',
        '#title' => '状态',
        '#default_value' => $page->status !== null ? $page->status : '1',
        '#options' => array('1' => '发布', '0' => '草稿')
      );
      $form['pid'] = array(
        '#type' => 'hidden',
        '#value' => $pid,
      );

      $form['save'] = array(
       '#type' => 'submit',
       '#value' => t('提交'),
       '#attributes' => array('lay-submit' => '', 'lay-filter' => 'pageUpdate'),
      );

      return view('/admin/page-edit.html', array('form' => $form, 'page' => $page, 'pid' => $pid));
  }

  /**
   * page_update.
   *
   * @return string
   *   Return page_update string.
   */
  public function page_update(ServerRequest $request) {
    if ($parms = $request->getParsedBody()) {
      $pid = $parms['pid'];
      $user = session()->get('admin');

       db_update('page')
         ->fields(array(
           'title' => clean($parms['title']),
           'content' => clean($parms['content']),
	         'status' => $parms['status'],
           'uid' => $user->uid,
           'updated' => time(),
         ))
         ->condition('pid', $pid)
         ->execute();

       return hunter_form_submit($parms, 'page', true);
     }
     return false;
  }

  /**
   * page_del.
   *
   * @return string
   *   Return page_del string.
   */
  public function page_del($pid) {
    $result = db_delete('page')
            ->condition('pid', $pid)
            ->execute();

    if ($result) {
      return true;
    }

    return false;
  }

}
