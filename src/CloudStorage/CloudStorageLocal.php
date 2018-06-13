<?php

namespace Noking50\FileUpload\CloudStorage;

use File;

/**
 * 儲存體服務 本地端
 * 
 * @package App\Classes\FileUpload\CloudStorage
 */
class CloudStorageLocal implements CloudStorageInterface {

    /**
     * 存取認證
     * 
     * @return void
     */
    public function auth() {
        
    }

    /**
     * 上傳檔案
     * 
     * @param string $file_path 來源檔案絕對路徑
     * @param string $dest_path 目標檔案相對路徑
     * @return void
     */
    public function upload($file_path, $dest_path) {
        $full_path = $this->getRootDir() . $dest_path;
        $dir = dirname($full_path);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0777, true);
        }

        File::move($file_path, $full_path);
    }

    /**
     * 移動檔案
     * 
     * @param string $org_path 來源檔案相對路徑
     * @param string $dest_path 目標檔案相對路徑
     * @return void
     */
    public function move($org_path, $dest_path) {
        File::move($this->getRootDir() . $org_path, $this->getRootDir() . $dest_path);
    }

    /**
     * 刪除檔案
     * 
     * @param string $file_path 檔案相對路徑
     * @return void
     */
    public function delete($file_path) {
        File::delete($this->getRootDir() . $file_path);
    }

    /**
     * 檔案是否存在
     * 
     * @param string $file_path 檔案相對路徑
     * @return boolean
     */
    public function fileExists($file_path) {
        return File::exists($this->getRootDir() . $file_path);
    }

    /**
     * 取得檔案相對路徑，包含原始檔與縮圖檔
     * 
     * @param array $fileinfo 檔案info陣列
     * @return array
     */
    public function getFile($fileinfo) {
        $rootDir = $this->getRootDir();
        $files = File::glob($rootDir . $fileinfo['dir'] . '/' . $fileinfo['id'] . '*.' . $fileinfo['ext']);
        foreach ($files as $k => $v) {
            $files[$k] = substr($v, strlen($rootDir));
        }

        return $files;
    }

    /**
     * 儲存體檔案網址的根路徑
     * 
     * @return string
     */
    public function rootUrl() {
        if (\App::runningInConsole()) {
            return rtrim(Config::get('app.url'), '/') . '/' . config('fileupload.root_dir') . '/';
        } else {
            return rtrim(asset(config('fileupload.root_dir')), '/') . '/';
        }
    }

    ##
    
    /**
     * 本地端檔案的實體根路徑
     * 
     * @return string
     */
    public function getRootDir() {
        return rtrim(public_path(config('fileupload.root_dir')), '/') . '/';
    }

}
