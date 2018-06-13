<?php

namespace Noking50\FileUpload\CloudStorage;

use cURL;
use Cache;
use Carbon\Carbon;

/**
 * 儲存體服務 Softlayer
 * 
 * @package App\Classes\FileUpload\CloudStorage
 */
class CloudStorageSoftlayer implements CloudStorageInterface {

    /**
     * 存取認證
     * 
     * @return anlutro\cURL\Response
     */
    public function auth() {
        $request = cURL::newRequest('get', config('fileupload.storage.softlayer.url_auth'));
        $request->setHeader('X-Auth-User', config('fileupload.storage.softlayer.user'));
        $request->setHeader('X-Auth-Key', config('fileupload.storage.softlayer.key'));
        $request->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $response = $request->send();

        $expiresAt = Carbon::now()->addMinutes(120);
        Cache::put('X-Auth-Token', $response->getHeader('X-Auth-Token'), $expiresAt);
        Cache::put('X-Storage-Url', $response->getHeader('X-Storage-Url'), $expiresAt);

        return $response;
    }

    /**
     * 上傳檔案
     * 
     * @param string $file_path 來源檔案絕對路徑
     * @param string $dest_path 目標檔案相對路徑
     * @return void
     */
    public function upload($file_path, $dest_path) {
        if (!Cache::has('X-Auth-Token')) {
            $this->auth();
        }
        $file = file_get_contents($file_path);
        $request = cURL::newRawRequest('put', Cache::get('X-Storage-Url') . '/' . config('fileupload.storage.softlayer.container') . '/' . rawurlencode($dest_path), $file);
        $request->setHeader('X-Auth-Token', Cache::get('X-Auth-Token'));
        $request->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $response = $request->send();
        if ($response->info['http_code'] == '403') {
            $this->auth();
            $file = file_get_contents($file_path);
            $request = cURL::newRawRequest('put', Cache::get('X-Storage-Url') . '/' . config('fileupload.storage.softlayer.container') . '/' . rawurlencode($dest_path), $file);
            $request->setHeader('X-Auth-Token', Cache::get('X-Auth-Token'));
            $request->setOption(CURLOPT_SSL_VERIFYPEER, false);
            $response = $request->send();
        }
        return $response;
    }

    /**
     * 移動檔案
     * 
     * @param string $org_path 來源檔案相對路徑
     * @param string $dest_path 目標檔案相對路徑
     * @return void
     */
    public function move($org_path, $dest_path) {
        if (!Cache::has('X-Auth-Token')) {
            $this->auth();
        }
        $request = cURL::newRawRequest('put', Cache::get('X-Storage-Url') . '/' . config('fileupload.storage.softlayer.container') . '/' . rawurlencode($dest_path));
        $request->setHeader('X-Auth-Token', Cache::get('X-Auth-Token'));
        $request->setHeader('X-Copy-From', config('fileupload.storage.softlayer.container') . '/' . rawurlencode($org_path));
        $request->setHeader('Content-Length', '0');
        $request->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $response = $request->send();
        if ($response->info['http_code'] == '403') {
            $this->auth();
            $request = cURL::newRawRequest('put', Cache::get('X-Storage-Url') . '/' . config('fileupload.storage.softlayer.container') . '/' . rawurlencode($dest_path));
            $request->setHeader('X-Auth-Token', Cache::get('X-Auth-Token'));
            $request->setHeader('X-Copy-From', config('fileupload.storage.softlayer.container') . '/' . rawurlencode($org_path));
            $request->setHeader('Content-Length', '0');
            $request->setOption(CURLOPT_SSL_VERIFYPEER, false);
            $response = $request->send();
        }
        $this->delete($org_path);

        return $response;
    }

    /**
     * 刪除檔案
     * 
     * @param string $file_path 檔案相對路徑
     * @return void
     */
    public function delete($file_path) {
        if (!Cache::has('X-Auth-Token')) {
            $this->auth();
        }
        $request = cURL::newRawRequest('delete', Cache::get('X-Storage-Url') . '/' . config('fileupload.storage.softlayer.container') . '/' . rawurlencode($file_path));
        $request->setHeader('X-Auth-Token', Cache::get('X-Auth-Token'));
        $request->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $response = $request->send();
        if ($response->info['http_code'] == '403') {
            $this->auth();
            $request = cURL::newRawRequest('delete', Cache::get('X-Storage-Url') . '/' . config('fileupload.storage.softlayer.container') . '/' . rawurlencode($file_path));
            $request->setHeader('X-Auth-Token', Cache::get('X-Auth-Token'));
            $request->setOption(CURLOPT_SSL_VERIFYPEER, false);
            $response = $request->send();
        }
        return $response;
    }

    /**
     * 檔案是否存在
     * 
     * @param string $file_path 檔案相對路徑
     * @return boolean
     */
    public function fileExists($file_path) {
        if (!Cache::has('X-Auth-Token')) {
            $this->auth();
        }
        $request = cURL::newRawRequest('get', Cache::get('X-Storage-Url') . '/' . config('fileupload.storage.softlayer.container') . '/' . rawurlencode($file_path));
        $request->setHeader('X-Auth-Token', Cache::get('X-Auth-Token'));
        $request->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $response = $request->send();
        if ($response->info['http_code'] == '200') {
            return true;
        }

        return false;
    }

    /**
     * 取得檔案相對路徑，包含原始檔與縮圖檔
     * 
     * @param array $fileinfo 檔案info陣列
     * @return array
     */
    public function getfile($fileinfo) {
        if (!Cache::has('X-Auth-Token')) {
            $this->auth();
        }
        $path = $fileinfo['dir'] . '/' . $fileinfo['id'];
        $request = cURL::newRawRequest('get', Cache::get('X-Storage-Url') . '/' . config('fileupload.storage.softlayer.container') . '/' . '?marker=' . $path . '&end_marker=' . ($path . 'z'));
        $request->setHeader('X-Auth-Token', Cache::get('X-Auth-Token'));
        $request->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $response = $request->send();
        if ($response->info['http_code'] == '403') {
            $this->auth();
            $request = cURL::newRawRequest('get', Cache::get('X-Storage-Url') . '/' . config('fileupload.storage.softlayer.container') . '/' . '?marker=' . $path . '&end_marker=' . ($path . 'z'));
            $request->setHeader('X-Auth-Token', Cache::get('X-Auth-Token'));
            $request->setOption(CURLOPT_SSL_VERIFYPEER, false);
            $response = $request->send();
        }
        $files = explode("\n", $response->body);
        foreach (array_keys($files) as $v){
            if(substr($files[$v], (strrpos($files[$v], '/') + 1), strlen($fileinfo['id'])) != $fileinfo['id']
                    || !ends_with($files[$v], '.' . $fileinfo['ext'])){
                unset($files[$v]);
            }
        }

        return $files;
    }

    /**
     * 儲存體檔案網址的根路徑
     * 
     * @return string
     */
    public function rootUrl() {
        return rtrim(config('fileupload.storage.softlayer.url') . config('fileupload.storage.softlayer.container'), '/') . '/';
    }

}
