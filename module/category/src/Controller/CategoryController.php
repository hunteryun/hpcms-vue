<?php

namespace Hunter\category\Controller;

use Hunter\Core\Category\Tree;
use Zend\Diactoros\ServerRequest;
use Hunter\Core\Utility\StringConverter;

/**
 * Class CategoryController.
 *
 * @package Hunter\category\Controller
 */
class CategoryController {
  /**
   * category_list.
   *
   * @return string
   *   Return category_list string.
   */
  public function category_list(Tree $tree) {
    $cats_list = '';
    $category_list = get_subcats_byid(0, true, true, true);

    $str = "<tr>
      <td>\$a->cid</td>
      <td style='text-align:left;padding-left:10px;'>\$spacer \$a->name</td>
      <td>\$a->link</td>
      <td>\$a->weight</td>
      <td>\$a->status</td>
      <td>
        <a href='javascript:;' onclick='categoryAdd(\$a->cid);' class='layui-btn layui-btn-xs'> 添加子类</a>
        <a href='javascript:;' onclick='categoryEdit(\$a->cid);' class='layui-btn layui-btn-normal layui-btn-xs'> 编辑</a>
        <a href='javascript:;' onclick='categoryDel(\$a->cid);' class='layui-btn layui-btn-danger layui-btn-xs ajax-delete'> 删除</a>
      </td>
     </tr>";

    if($category_list){
      $cats_list = $tree->init($category_list)->get_tree(0,$str);
    }

    return view('/admin/category-list.html', array('cats_list' => $cats_list));
  }

  /**
   * category_add.
   *
   * @return string
   *   Return category_add string.
   */
  public function category_add(ServerRequest $request, StringConverter $string) {
    $arg = $request->getQueryParams();
    if ($parms = $request->getParsedBody()) {
      $cid = db_insert('category')
        ->fields(array(
          'name' => $parms['name'],
          'machine_name' => $string->createMachineName($parms['name']),
          'parentid' => $parms['pid'],
          'cover' => $parms['cover'],
          'description' => $parms['description'],
          'link' => $parms['link'],
          'status' => isset($parms['status']) ? 'yes' : 'no',
          'weight' => $parms['weight']
        ))
        ->execute();

      hunter_form_submit($parms, 'category', $cid);

      return redirect('/admin/category/list');
    }

    $cat_options = array('0' => '≡ 作为一级栏目 ≡');
    $cats = db_select('category', 'c')
      ->fields('c',array('cid','name'))
      ->groupBy('c.cid')
      ->execute()
      ->fetchAllAssoc('cid');

    foreach ($cats as $id => $item) {
      $cat_options[$id] = $item->name;
    }

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => '栏目名称',
    );
    $form['pid'] = array(
      '#type' => 'select',
      '#title' => '上级栏目',
      '#default_value' => $arg['cid'] ? $arg['cid'] : '',
      '#options' => $cat_options
    );
    $form['cover'] = array(
      '#type' => 'image',
      '#title' => '栏目图片',
      '#attributes' => array('id' => 'cover'),
    );
    $form['cover_preview'] = array(
      '#type' => 'markup',
      '#title' => '预览',
      '#hidden' => TRUE,
      '#markup' => '<img src="" id="cover-preview" width="200">',
    );
    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => '描述',
      '#rows' => '18',
    );
    $form['link'] = array(
      '#type' => 'textfield',
      '#title' => '链接',
    );
    $form['weight'] = array(
      '#type' => 'textfield',
      '#title' => '权重',
      '#maxlength' => 11,
    );
    $form['status'] = array(
      '#type' => 'checkboxes',
      '#title' => '启用状态',
      '#options' => array('0' => 'Yes|No'),
      '#attributes' => array('lay-skin' => 'switch', 'lay-text' => 'Yes|No', 'value' => 'yes'),
    );
    $form['save'] = array(
     '#type' => 'submit',
     '#value' => '保存',
     '#attributes' => array('lay-filter' => 'categoryAdd', 'lay-submit' => ''),
    );
    $form['form_id'] = 'category_add_form';

    return view('/admin/category-add.html', array('form' => $form, 'cats' => $cats, 'cid' => $arg['cid']));
  }

  /**
   * category_edit.
   *
   * @return string
   *   Return category_edit string.
   */
  public function category_edit($cid) {
    $category_info = db_select('category', 'c')
      ->fields('c')
      ->condition('cid', $cid)
      ->execute()
      ->fetchObject();

    $cat_options = array('0' => '≡ 作为一级栏目 ≡');
    $cats = db_select('category', 'c')
      ->fields('c',array('cid','name'))
      ->groupBy('c.cid')
      ->execute()
      ->fetchAllAssoc('cid');

    foreach ($cats as $id => $item) {
      $cat_options[$id] = $item->name;
    }

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => '栏目名称',
      '#default_value' => $category_info->name ? $category_info->name : '',
      '#maxlength' => 11,
    );
    $form['pid'] = array(
      '#type' => 'select',
      '#title' => '上级栏目',
      '#default_value' => $category_info->parentid ? $category_info->parentid : '',
      '#options' => $cat_options
    );
    $form['cover'] = array(
      '#type' => 'image',
      '#title' => '栏目图片',
      '#default_value' => $category_info->cover ? $category_info->cover : '',
      '#attributes' => array('id' => 'cover'),
    );
    if(!empty($category_info->cover)){
      $form['cover_preview'] = array(
        '#type' => 'img',
        '#title' => '预览',
        '#default_value' => $category_info->cover ? $category_info->cover : '',
        '#attributes' => array('id' => 'cover-preview', 'width' => '200'),
      );
    }else {
      $form['cover_preview'] = array(
        '#type' => 'markup',
        '#title' => '预览',
        '#hidden' => TRUE,
        '#markup' => '<img src="" id="cover-preview" width="200">',
      );
    }
    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => '描述',
      '#rows' => '18',
      '#default_value' => $category_info->description ? $category_info->description : '',
    );
    $form['link'] = array(
      '#type' => 'textfield',
      '#title' => '链接',
      '#default_value' => $category_info->link ? $category_info->link : '',
    );
    $form['weight'] = array(
      '#type' => 'textfield',
      '#title' => '权重',
      '#default_value' => $category_info->weight ? $category_info->weight : '',
      '#maxlength' => 11,
    );
    $form['status'] = array(
      '#type' => 'checkboxes',
      '#title' => '启用状态',
      '#options' => array('0' => 'Yes|No'),
      '#default_value' => $category_info->status ? $category_info->status : 'yes',
      '#attributes' => array('lay-skin' => 'switch', 'lay-text' => 'Yes|No', 'value' => 'yes'),
    );
    $form['cid'] = array(
      '#type' => 'hidden',
      '#value' => $category_info->cid ? $category_info->cid : '',
    );
    $form['save'] = array(
     '#type' => 'submit',
     '#value' => '保存',
     '#attributes' => array('lay-filter' => 'categoryEdit', 'lay-submit' => ''),
    );
    $form['form_id'] = 'category_edit_form';

    return view('/admin/category-edit.html', array('form' => $form, 'cats' => $cats, 'category_info' => $category_info));
  }

  /**
   * category_update.
   *
   * @return string
   *   Return category_update string.
   */
  public function category_update(ServerRequest $request, StringConverter $string) {
    if ($parms = $request->getParsedBody()) {
      db_update('category')
       ->fields(array(
         'name' => $parms['name'],
         'machine_name' => $string->createMachineName($parms['name']),
         'parentid' => $parms['pid'],
         'cover' => $parms['cover'],
         'description' => $parms['description'],
         'link' => $parms['link'],
         'status' => isset($parms['status']) ? 'yes' : 'no',
         'weight' => $parms['weight']
       ))
       ->condition('cid', $parms['cid'])
       ->execute();

     return hunter_form_submit($parms, 'category', $parms['cid']);
    }
    return false;
  }

  /**
   * category_del.
   *
   * @return string
   *   Return category_del string.
   */
  public function category_del($cid, $child = TRUE) {
    $result = db_delete('category')
            ->condition('cid', $cid)
            ->execute();

    $all_sub_cids = array_keys(get_subcats_byid($cid));
    $all_sub_cids[] = $cid;

    if($child){
      db_delete('category')
        ->condition('parentid', $cid)
        ->execute();
    }

    if ($result) {
      if(module_exists('seo')){
        if($child){
          seo_delete('category', $all_sub_cids);
        }else {
          seo_delete('category', $cid);
        }
      }

      return true;
    }

    return false;
  }

}
