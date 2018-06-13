<?php

namespace Noking50\FileUpload;

use Noking50\FileUpload\CloudStorage\CloudStorage;
use File;
use Image;

/**
 * 檔案上傳
 * 
 * @package App\Classes\FileUpload
 */
class FileUpload {

    /**
     * 儲存體物件
     * 
     * @var App\Classes\FileUpload\CloudStorage\CloudStorage
     */
    protected $storage_manager;

    /**
     * Construct
     */
    public function __construct() {
        $this->storage_manager = new CloudStorage();
    }

    /**
     * 驗證檔案分類是否合法
     * 
     * @param string $category 檔案分類名稱
     * @return boolean
     */
    public function isValidCategory($category) {
        return in_array(strtolower($category), config('fileupload.accept_dir', []));
    }

    /**
     * 驗證檔案附檔名是否合法
     * 
     * @param string $file_name 檔案名稱
     * @param string $file_ext 合法的附檔名，以 | 分隔
     * @return boolean
     */
    public function isValidFileExt($file_name, $file_ext) {
        $file_ext_arr = explode('|', strtolower($file_ext));
        $valid_ext = config('fileupload.accept_ext', '');
        $valid_ext_arr = [];
        $all_valid_ext_arr = explode('|', $valid_ext);
        foreach ($file_ext_arr as $k => $v) {
            if (in_array($v, $all_valid_ext_arr)) {
                $valid_ext_arr[] = $v;
            }
        }
        if (count($valid_ext_arr) > 0) {
            $valid_ext = implode('|', $valid_ext_arr);
        }

        if (preg_match('/^(.*)\.(' . $valid_ext . ')$/i', $file_name)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 驗證檔案大小是否合法
     * 
     * @param integer $size 檔案大小 單位byte
     * @param integer $max_size 最大合法檔案大小 單位byte
     * @return boolean
     */
    public function isValidFileSize($size, $max_size) {
        return (intval($size) <= intval($max_size));
    }

    /**
     * 取得上傳檔案暫存實體根目錄
     * 
     * @return string
     */
    public function getRootDirTmp() {
        return rtrim(public_path(config('fileupload.root_dir_tmp')), '/') . '/';
    }

    /**
     * 取得上傳檔案暫存網址根路徑
     * 
     * @return string
     */
    public function getRootUrlTmp() {
        return rtrim(asset(config('fileupload.root_dir_tmp')), '/') . '/';
    }

    /**
     * 儲存預設縮圖
     * 
     * @param array $fileinfo 檔案info陣列
     * @return void
     */
    public function saveThumb($fileinfo) {
        $path_src = $this->getRootDirTmp() . $fileinfo['dir'] . '/' . $fileinfo['id'] . '.' . $fileinfo['ext'];
        $path_dest = $this->getRootDirTmp() . $fileinfo['dir'] . '/' . $fileinfo['id'] . '_thumb.' . $fileinfo['ext'];

        // resize gif will destroy animate, so use copy
        if ($fileinfo['ext'] == 'gif') {
            File::copy($path_src, $path_dest);
            return;
        }

        $img = Image::make($path_src)->orientate();
        $img->resize(config('fileupload.thumb.width'), config('fileupload.thumb.height'), function($constraint) {
            $constraint->aspectRatio();
        });
        $img->save($path_dest, config('fileupload.quality', 75));
        $img->destroy();
    }

    /**
     * 儲存縮圖
     * 
     * @param array $fileinfo 檔案info陣列
     * @return void
     */
    public function saveResize($fileinfo) {
        $path_src = $this->getRootDirTmp() . $fileinfo['dir'] . '/' . $fileinfo['id'] . '.' . $fileinfo['ext'];

        // resize gif will destroy animate, so use copy
        if ($fileinfo['ext'] == 'gif') {
            foreach ($fileinfo['scale'] as $k => $v) {
                $wh = explode('_', $v);
                if ($wh[0] <= 0 && $wh[1] <= 0) {
                    continue;
                }
                $path_dest = $this->getRootDirTmp() . $fileinfo['dir'] . '/' . $fileinfo['id'] . '_' . $v . '.' . $fileinfo['ext'];

                File::copy($path_src, $path_dest);
            }
            return;
        }

        $img = Image::make($path_src)->orientate();
        $img->backup();
        foreach ($fileinfo['scale'] as $k => $v) {
            $wh = explode('_', $v);
            $path_dest = $this->getRootDirTmp() . $fileinfo['dir'] . '/' . $fileinfo['id'] . '_' . $v . '.' . $fileinfo['ext'];

            if ($wh[0] <= 0) {
                if ($wh[1] <= 0) {
                    continue;
                } else {
                    $img->heighten($wh[1], function($constraint) {
                        $constraint->upsize();
                    });
                }
            } else {
                if ($wh[1] <= 0) {
                    $img->widen($wh[0], function($constraint) {
                        $constraint->upsize();
                    });
                } else {
                    $img->fit($wh[0], $wh[1], function($constraint) {
                        $constraint->upsize();
                    });
                }
            }

            $img->save($path_dest, config('fileupload.quality', 75));
            $img->reset();
        }

        $img->destroy();
    }

    /**
     * 取得儲存體服務
     * 
     * @param string $name 儲存體服務名稱
     * @return mix App\Classes\FileUpload\CloudStorageXXXXX
     */
    public function storage($name = null) {
        return $this->storage_manager->storage($name);
    }

    /**
     * 取得檔案網址的根路徑
     * 
     * @return string
     */
    public function getRootUrl() {
        return $this->storage_manager->rootUrl();
    }

    /**
     * 取得檔案網址，包含原檔案與縮圖
     * 
     * @param string $json  json字串
     * @return array
     */
    public function getFiles($json) {
        $files = json_decode($json, true);
        $file_list = array();
        if (is_array($files)) {
            foreach ($files as $k => $file) {
                $file_data = $file;
                $file_data['name'] = $file['name'];
                $file_data['url'] = $this->getRootUrl() . $file['dir'] . '/' . $file['id'] . '.' . $file['ext'];
                $file_data['url_thumb'] = $this->getRootUrl() . $file['dir'] . '/' . $file['id'] . '_thumb.' . $file['ext'];
                $scale = $file['scale'];
                if (!is_array($scale)) {
                    $scale = array_filter(explode(',', $scale));
                }
                if (is_array($scale)) {
                    foreach ($scale as $kk => $vv) {
                        $file_data['url_scale_' . $kk] = $this->getRootUrl() . $file['dir'] . '/' . $file['id'] . '_' . $vv . '.' . $file['ext'];
                    }
                }
                $file_list[] = $file_data;
            }
        }

        return $file_list;
    }

    /**
     * 處理一般檔案上傳
     * 
     * @param string|array|null $obj_new 更新後檔案 json字串或陣列，資料刪除時此參數為null或空陣列
     * @param string|array|null $obj_old 更新前檔案 json字串或陣列，資料新增時此參數為null或空陣列
     */
    public function handleFile($obj_new, $obj_old = null) {
        if (!is_array($obj_new)) {
            $obj_new = json_decode($obj_new, true);
            if (!is_array($obj_new)) {
                $obj_new = array();
            }
        }
        if (!is_array($obj_old)) {
            $obj_old = json_decode($obj_old, true);
            if (!is_array($obj_old)) {
                $obj_old = array();
            }
        }

        $files_new = [];
        $files_old = [];
        foreach ($obj_new as $k => $v) {
            if (!isset($v['dir']) || !isset($v['id'])) {
                continue;
            }
            $files_new[$v['dir'] . '_' . $v['id']] = $v;
        }
        foreach ($obj_old as $k => $v) {
            if (!isset($v['dir']) || !isset($v['id'])) {
                continue;
            }
            $files_old[$v['dir'] . '_' . $v['id']] = $v;
        }

        $files_add = array_diff_key($files_new, $files_old);
        $files_del = array_diff_key($files_old, $files_new);

        foreach ($files_add as $k => $v) {
            $file_path = $this->getRootDirTmp() . $v['dir'];
            $files = File::glob($file_path . '/' . $v['id'] . '*.' . $v['ext']);
            if (is_array($files)) {
                foreach ($files as $kk => $vv) {
                    if (File::isFile($vv)) {
                        $this->storage_manager->upload($vv, $v['dir'] . '/' . File::basename($vv));
                    }
                }
            }
        }
        foreach ($files_del as $k => $v) {
            $files = $this->storage_manager->getfile($v);
            foreach ($files as $kk => $vv) {
                if ($this->storage_manager->fileExists($vv)) {
                    $this->storage_manager->delete($vv);
                }
            }
        }
    }

    /**
     * 處理編輯器檔案上傳
     * 
     * @param string|array|null $obj_new 更新後檔案 json字串或陣列，資料刪除時此參數為null或空陣列
     * @param string|array|null $obj_old 更新前檔案 json字串或陣列，資料新增時此參數為null或空陣列
     */
    public function handleEditor($obj_new, $obj_old = null) {
        if (!is_array($obj_new)) {
            $obj_new = json_decode($obj_new, true);
            if (!is_array($obj_new)) {
                $obj_new = array();
            }
        }
        if (!is_array($obj_old)) {
            $obj_old = json_decode($obj_old, true);
            if (!is_array($obj_old)) {
                $obj_old = array();
            }
        }

        $this->handleFile($this->parseEditorFiles($obj_new), $this->parseEditorFiles($obj_old));
    }

    /**
     * 找出編輯器上傳檔案資料
     * 
     * @param array $arr 編輯器資料
     * @return array
     */
    private function parseEditorFiles($arr) {
        $files = [];
        foreach ($arr as $k => $v) {
            if (isset($v['cell'])) {
                foreach ($v['cell'] as $kk => $vv) {
                    if (isset($vv['item'])) {
                        foreach ($vv['item'] as $kkk => $vvv) {
                            if (isset($vvv['type']) && $vvv['type'] == 'pic') {
                                if (isset($vvv['file']) && is_array($vvv['file'])) {
                                    $files[] = $vvv['file'];
                                }
                                if (isset($vvv['url_file']) && is_array($vvv['url_file'])) {
                                    $files[] = $vvv['url_file'];
                                }
                            }
                        }
                    }
                }
            }
        }
        return $files;
    }

}
