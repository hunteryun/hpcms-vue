<?php

namespace Hunter\ueditor\Controller;

use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response\JsonResponse;
use Hunter\ueditor\Uploader\UploadFile;
use Hunter\ueditor\Uploader\UploadScrawl;
use Hunter\ueditor\Uploader\UploadCatch;
use Hunter\ueditor\Uploader\Lists;

/**
 * Class ueditor.
 *
 * @package Hunter\ueditor\Controller
 */
class UeditorController {

  protected $ueditor_config;

  /**
   * Constructs a cors config.
   */
  public function __construct() {
    $this->ueditor_config = config('ueditor.ueditor')->get('upload');
  }

  /**
   * ueditor_server.
   *
   * @return string
   *   Return ueditor_server string.
   */
  public function ueditor_server(ServerRequest $request) {
    $query_parms = $request->getQueryParams();
    $action = $query_parms['action'];

    switch ($action) {
        case 'config':
            $result = $this->ueditor_config;
            break;
        case 'uploadimage':
            $upConfig = array(
                "pathFormat" => $this->ueditor_config['imagePathFormat'],
                "maxSize" => $this->ueditor_config['imageMaxSize'],
                "allowFiles" => $this->ueditor_config['imageAllowFiles'],
                'fieldName' => $this->ueditor_config['imageFieldName'],
            );
            $uploader = new UploadFile($upConfig, $request);
            $result = $uploader->upload();
            break;
        case 'uploadscrawl':
            $upConfig = array(
                "pathFormat" => $this->ueditor_config['scrawlPathFormat'],
                "maxSize" => $this->ueditor_config['scrawlMaxSize'],
                //   "allowFiles" => $this->ueditor_config['scrawlAllowFiles'],
                "oriName" => "scrawl.png",
                'fieldName' => $this->ueditor_config['scrawlFieldName'],
            );
            $uploader = new UploadScrawl($upConfig, $request);
            $result = $uploader->upload();
            break;
        case 'uploadvideo':
            $upConfig = array(
                "pathFormat" => $this->ueditor_config['videoPathFormat'],
                "maxSize" => $this->ueditor_config['videoMaxSize'],
                "allowFiles" => $this->ueditor_config['videoAllowFiles'],
                'fieldName' => $this->ueditor_config['videoFieldName'],
            );
            $uploader = new UploadFile($upConfig, $request);
            $result = $uploader->upload();
            break;
        case 'uploadfile':
        default:
            $upConfig = array(
                "pathFormat" => $this->ueditor_config['filePathFormat'],
                "maxSize" => $this->ueditor_config['fileMaxSize'],
                "allowFiles" => $this->ueditor_config['fileAllowFiles'],
                'fieldName' => $this->ueditor_config['fileFieldName'],
            );
            $uploader = new UploadFile($upConfig, $request);
            $result = $uploader->upload();

            break;

        /* 列出图片 */
        case 'listimage':
            $list = new Lists(
                $this->ueditor_config['imageManagerAllowFiles'],
                $this->ueditor_config['imageManagerListSize'],
                $this->ueditor_config['imageManagerListPath'],
                $request);
            $result = $list->getList();
            break;
        /* 列出文件 */
        case 'listfile':
            $list = new Lists(
                $this->ueditor_config['fileManagerAllowFiles'],
                $this->ueditor_config['fileManagerListSize'],
                $this->ueditor_config['fileManagerListPath'],
                $request);
            $result = $list->getList();
            break;

        /* 抓取远程文件 */
        case 'catchimage':
            $upConfig = array(
                "pathFormat" => $this->ueditor_config['catcherPathFormat'],
                "maxSize" => $this->ueditor_config['catcherMaxSize'],
                "allowFiles" => $this->ueditor_config['catcherAllowFiles'],
                "oriName" => "remote.png",
                'fieldName' => $this->ueditor_config['catcherFieldName'],
            );

            $post_parms = $request->getParsedBody();
            $sources = $post_parms[$upConfig['fieldName']];
            $list = [];
            foreach ($sources as $imgUrl) {
                $upConfig['imgUrl'] = $imgUrl;
                $uploader = new UploadCatch($upConfig, $request);
                $info = $uploader->upload();

                array_push($list, array(
                    "state" => $info["state"],
                    "url" => $info["url"],
                    "size" => $info["size"],
                    "title" => htmlspecialchars($info["title"]),
                    "original" => htmlspecialchars($info["original"]),
                    "source" => htmlspecialchars($imgUrl)
                ));
            }
            $result = [
                'state' => count($list) ? 'SUCCESS' : 'ERROR',
                'list' => $list
            ];
            break;
    }

    return new JsonResponse($result);
  }

}
