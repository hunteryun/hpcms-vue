<?php

namespace Hunter\safe_upload\Controller;

use Zend\Diactoros\ServerRequest;
use Psr\Http\Message\UploadedFileInterface;
use Zend\Diactoros\Response\JsonResponse;
use Rundiz\Upload\Upload;
use Gregwar\Image\Image;

/**
 * Class Upload.
 *
 * @package Hunter\safe_upload\Controller
 */
class UploadController {
  /**
   * safe_upload.
   *
   * @return string
   *   Return safe_upload string.
   */
  public function safe_upload(ServerRequest $request) {
    global $safe_key;
    $result = t('Upload Failed');
    $notvalidate = false;
    $allowed_file = array();
    $uploadedFiles = $request->getUploadedFiles();
    $parms = $request->getParsedBody();
    $query_parms = $request->getQueryParams();

    if($request->getUri()->getPath() == '/public/safe/upload'){
      if(!isset($query_parms['safe_key']) || $query_parms['safe_key'] != $safe_key){
        return new JsonResponse(array('code' => 1, 'msg' => $result, "data" => []));
      }
    }

    if(!isset($parms['accept']) || empty($parms['accept'])) {
      $parms['accept'] = 'images';
    }
    if(isset($parms['notvalidate']) && $parms['notvalidate'] == 'yes') {
      $notvalidate = TRUE;
    }

    switch ($parms['accept'])
    {
    case 'images':
      if(!isset($parms['exts']) || empty($parms['exts'])){
        $parms['exts'] = 'jpg|png|gif|bmp|jpeg';
      }
      if(!empty(array_diff(explode('|', $parms['exts']), array('jpg','png','gif','bmp','jpeg','tif')))){
        return new JsonResponse(array('code' => 1, 'msg' => $result, "data" => []));
      }
      $allowed_file = explode('|', $parms['exts']);
      break;
    case 'file':
      if(!isset($parms['exts']) || empty($parms['exts'])){
        $parms['exts'] = 'doc|pdf|txt|xls|zip|rar';
      }
      if(!empty(array_diff(explode('|', $parms['exts']), array('doc','pdf','txt','xls','zip','rar','7z','obj','pmd','vmd','mp3','mp4','jpg','jpeg','png','gif','bmp','xml','dat')))){
        return new JsonResponse(array('code' => 1, 'msg' => $result, "data" => []));
      }
      $allowed_file = explode('|', $parms['exts']);
      break;
    case 'video':
      if(!isset($parms['exts']) || empty($parms['exts'])){
        $parms['exts'] = 'mp4|flv|mpg|3gp';
      }
      if(!empty(array_diff(explode('|', $parms['exts']), array('rm','rmvb','wmv','avi','mp4','3gp','mkv','flv','mpg')))){
        return new JsonResponse(array('code' => 1, 'msg' => $result, "data" => []));
      }
      $allowed_file = explode('|', $parms['exts']);
      break;
    case 'audio':
      if(!isset($parms['exts']) || empty($parms['exts'])){
        $parms['exts'] = 'mp3|wma|wav';
      }
      if(!empty(array_diff(explode('|', $parms['exts']), array('wav','mp3','ogg','wma','aac')))){
        return new JsonResponse(array('code' => 1, 'msg' => $result, "data" => []));
      }
      $allowed_file = explode('|', $parms['exts']);
      break;
    default:
      return new JsonResponse(array('code' => 1, 'msg' => $result, "data" => []));
    }

    if (!empty($uploadedFiles)) {
      foreach ($uploadedFiles as $key => $value) {
        if ($value instanceof UploadedFileInterface || is_array($value)) {
          $result = $this->safe_upload_file($key, $value, $allowed_file, $notvalidate, $query_parms);
        }
      }
    }
    return $result;
  }

  /**
   * safe_upload.
   *
   * @return string
   *   Return safe_upload string.
   */
  private function safe_upload_file($field_name, $value, $allowed_file = array('gif', 'jpg', 'jpeg', 'png'), $notvalidate = FALSE, $query_parms = array()) {
    global $auto_image_compress, $image_style, $base_url;
    $module = '';
    if(empty($module)) {
      $module = substr($field_name,0,strpos($field_name,'-'));
    }

    $result = array();
    $msg = '';
    $Upload = new Upload($field_name);
    $move_dir = 'sites/upload/'.$field_name.'/';
    $Upload->move_uploaded_to = $move_dir;
    if (!is_dir($move_dir)){
      mkdir($move_dir, 0755, true);
    }
    // Allowed for gif, jpg, png
    $Upload->allowed_file_extensions = $allowed_file;
    // Max file size is 900KB.
    //$Upload->max_file_size = 900000;
    // You can name the uploaded file to new name or leave this to use its default name. Do not included extension into it.
    $Upload->new_file_name = is_array($value) ? 'new_'.$field_name : hunter_rename($value->getClientFilename());
    // Overwrite existing file? true = yes, false = no
    $Upload->overwrite = false;
    // Web safe file name is English, number, dash, underscore.
    $Upload->web_safe_file_name = true;
    // Scan for embedded php or perl language?
    $Upload->security_scan = true;
    // If you upload multiple files, do you want it to be stopped if error occur? (Set to false will skip the error files).
    $Upload->stop_on_failed_upload_multiple = false;

    if($notvalidate){
      $Upload->file_extensions_mime_types = array();
    }

    // Begins upload
    $upload_result = $Upload->upload();
    // Get the uploaded file's data.
    $uploaded_data = $Upload->getUploadedData();

    //Create upload dir
    $thumb_group = array();

    if(empty($image_style) || (!isset($query_parms['style']) || !isset($image_style[$query_parms['style']]))){
      $thumb_group['thumb']['thumb_move_dir'] = 'sites/thumb/'.$field_name.'/';
      $thumb_group['thumb']['width'] = '120';
      $thumb_group['thumb']['height'] = '120';
      $thumb_group['thumb']['method'] = 'resize';
      $thumb_group['thumb']['background'] = '#FFFFFF';
      if (!is_dir($thumb_group['thumb']['thumb_move_dir'])){
        mkdir($thumb_group['thumb']['thumb_move_dir'], 0755, true);
      }
    }else {
      if(is_array($image_style[$query_parms['style']])) {
        foreach ($image_style[$query_parms['style']] as $dir => $thumb_size) {
          $thumb_group[$dir]['thumb_move_dir'] = 'sites/thumb/'.$dir.'/'.$field_name.'/';
          $size = explode('*',$thumb_size['size']);
          $thumb_group[$dir]['width'] = $size[0];
          $thumb_group[$dir]['height'] = $size[1];
          $thumb_group[$dir]['method'] = $thumb_size['method'];
          $thumb_group[$dir]['background'] = $thumb_size['background'];
          if (!is_dir($thumb_group[$dir]['thumb_move_dir'])){
            mkdir($thumb_group[$dir]['thumb_move_dir'], 0755, true);
          }
        }
      }else {
        $thumb_group[$query_parms['style']]['thumb_move_dir'] = 'sites/thumb/'.$query_parms['style'].'/'.$field_name.'/';
        $size = explode('*',$image_style[$query_parms['style']]);
        $thumb_group[$query_parms['style']]['width'] = $size[0];
        $thumb_group[$query_parms['style']]['height'] = $size[1];
        $thumb_group[$query_parms['style']]['method'] = 'resize';
        $thumb_group[$query_parms['style']]['background'] = '#FFFFFF';
        if (!is_dir($thumb_group[$query_parms['style']]['thumb_move_dir'])){
          mkdir($thumb_group[$query_parms['style']]['thumb_move_dir'], 0755, true);
        }
      }
    }

    if (is_array($uploaded_data) && !empty($uploaded_data)) {
      foreach ($uploaded_data as $key => $item) {
        $url = $move_dir.$uploaded_data[$key]['new_name'];
        if(in_array(strtolower($uploaded_data[$key]['extension']), array('jpg','jpeg','png'))){
          foreach ($thumb_group as $thumb_key => $thumb_item) {
            $thumburl = $thumb_item['thumb_move_dir'].$Upload->new_file_name.'.'.$uploaded_data[0]['extension'];
            $method = $thumb_item['method'];
            Image::open($url)
                 ->$method($thumb_item['width'], $thumb_item['height'], $thumb_item['background'])
                 ->save($thumburl);

            if(isset($query_parms['compress']) && ($query_parms['compress'] == 'yes' || $query_parms['fullpath'] == 'true')){
              if(module_exists('image_compress') && $auto_image_compress['enable']){
                hunter_compress_image($url, $url, $auto_image_compress['quality']);
              }
            }
          }
        }

        $uploaded_data[$key]['full_path_new_name'] = base_path().$url;
        $uploaded_data[$key]['src'] = base_path().$url;
        $uploaded_data[$key]['base_url'] = $base_url;
        if(isset($query_parms['fullpath']) && ($query_parms['fullpath'] == 'yes' || $query_parms['fullpath'] == 'true')){
          $uploaded_data[$key]['src'] = $base_url.$uploaded_data[$key]['src'];
        }
      }
    }

    if ($upload_result === true) {
        $code = 0;
        $msg .= 'success';
    }

    // To check for the errors.
    if (is_array($Upload->error_messages) && !empty($Upload->error_messages)) {
        $code = 1;
        foreach ($Upload->error_messages as $error_message) {
            $msg .= $error_message."\n";
        }
    }

    $result = array(
      'code'=> $code,
      'msg' => $msg,
      'data' => count($uploaded_data) == 1 ? reset($uploaded_data) : $uploaded_data
    );

    return $result;
  }

}
