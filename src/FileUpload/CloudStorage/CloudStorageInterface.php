<?php

namespace Noking50\FileUpload\CloudStorage;

/**
 * 儲存體服務實作介面
 * 
 * @package App\Classes\FileUpload\CloudStorage
 */
interface CloudStorageInterface {

    /**
     * 存取認證
     * 
     * @return void
     */
    public function auth();

    /**
     * 上傳檔案
     * 
     * @param string $file_path 來源檔案絕對路徑
     * @param string $dest_path 目標檔案相對路徑
     * @return void
     */
    public function upload($file_path, $dest_path);

    /**
     * 移動檔案
     * 
     * @param string $org_path 來源檔案相對路徑
     * @param string $dest_path 目標檔案相對路徑
     * @return void
     */
    public function move($org_path, $dest_path);

    /**
     * 刪除檔案
     * 
     * @param string $file_path 檔案相對路徑
     * @return void
     */
    public function delete($file_path);

    /**
     * 檔案是否存在
     * 
     * @param string $file_path 檔案相對路徑
     * @return boolean
     */
    public function fileExists($file_path);

    /**
     * 取得檔案相對路徑，包含原始檔與縮圖檔
     * 
     * @param array $fileinfo 檔案info陣列
     * @return array
     */
    public function getFile($fileinfo);

    /**
     * 儲存體檔案網址的根路徑
     * 
     * @return string
     */
    public function rootUrl();
}
