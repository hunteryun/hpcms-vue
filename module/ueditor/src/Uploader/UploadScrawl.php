<?php

namespace Hunter\ueditor\Uploader;

use Hunter\ueditor\Uploader\Upload;

/**
 * Class UploadScrawl
 * 涂鸦上传
 * @package Hunter\ueditor\Uploader
 */
class UploadScrawl extends Upload {

    public function doUpload() {
        $post_parms = $this->request->getParsedBody();
        $base64Data = $post_parms['upfile'];
        $img = base64_decode($base64Data);
        if (!$img) {
            $this->stateInfo = $this->getStateInfo("ERROR_FILE_NOT_FOUND");
            return false;
        }

        $this->oriName = $this->config['oriName'];
        $this->fileSize = strlen($img);
        $this->fileType = $this->getFileExt();
        $this->fullName = $this->getFullName();
        $this->filePath = $this->getFilePath();
        $this->fileName = basename($this->filePath);
        $dirname = dirname($this->filePath);

        //检查文件大小是否超出限制
        if (!$this->checkSize()) {
            $this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
            return false;
        }

        //创建目录失败
        if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
            $this->stateInfo = $this->getStateInfo("ERROR_CREATE_DIR");
            return false;
        } else if (!is_writeable($dirname)) {
            $this->stateInfo = $this->getStateInfo("ERROR_DIR_NOT_WRITEABLE");
            return false;
        }

        //移动文件
        if (!(file_put_contents($this->filePath, $img) && file_exists($this->filePath))) { //移动失败
            $this->stateInfo = $this->getStateInfo("ERROR_WRITE_CONTENT");
        } else { //移动成功
            $this->stateInfo = $this->stateMap[0];
            return false;
        }
    }

    /**
     * 获取文件扩展名
     * @return string
     */
    protected function getFileExt() {
        return strtolower(strrchr($this->oriName, '.'));
    }
}
