<?php

namespace Noking50\FileUpload\Controllers;

use Illuminate\Routing\Controller;
use Noking50\FileUpload\Facades\FileUpload;
use Request;
use Config;
use File;
use Log;

class FileUploadController extends Controller {

    public function index() {
        $response = array(
            'result' => 'no',
            'file' => null,
            'msg' => ''
        );

        $requestName = 'jqfuFile';
        $tmpFileInfo = isset($_FILES[$requestName]) ? $_FILES[$requestName] : null;

        if (!Request::hasFile($requestName)) {
            $response['msg'] = '上傳失敗：無檔案.';
            Log::error($response['msg']);
            return $response;
        }
        $file = Request::file($requestName);
        if ($file->getError() != UPLOAD_ERR_OK) {
            $response['msg'] = '上傳失敗：' . $file->getErrorMessage() . '.';
            Log::error($response['msg']);
            return $response;
        }

        $category = Request::input('category');
        if (is_null($category) || !FileUpload::isValidCategory($category)) {
            File::delete($file->getRealPath());
            $response["msg"] = "上傳失敗：上傳分類不合法.";
            Log::info($response["msg"], array('file' => $tmpFileInfo));
            return $response;
        } else {
            $category = strtolower($category);
        }

        $validFileExt = Request::input('fileExt');
        if (is_null($validFileExt) || !FileUpload::isValidFileExt($file->getClientOriginalName(), $validFileExt)) {
            File::delete($file->getRealPath());
            $response["msg"] = "上傳失敗：檔案類型不合法.";
            Log::info($response["msg"], array('file' => $tmpFileInfo));
            return $response;
        }

        $validFileSize = Request::input('fileSize', Config::get('fileupload.max_size'));
        if (is_null($validFileSize) || !FileUpload::isValidFileSize($file->getClientSize(), $validFileSize)) {
            File::delete($file->getRealPath());
            $response["msg"] = "上傳失敗：超過檔案大小上限.";
            Log::info($response["msg"], array('file' => $tmpFileInfo));
            return $response;
        }

        $isBtsEditor = (Request::input('uploadType', '') == "btseditor");
        $isImage = in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'gif']);

        $dtNow = new \DateTime();

        $fileName = substr($file->getClientOriginalName(), 0, -(strlen($file->getClientOriginalExtension()) + 1));
        $fileExt = strtolower($file->getClientOriginalExtension());
        $fileDir = $dtNow->format('Ym') . '/' . trim($category, '/');
        $fileScale = array();
        if (!is_null(Request::input('imgScale')) && trim(Request::input('imgScale')) != "") {
            $scales = explode(',', trim(Request::input('imgScale')));
            foreach ($scales as $v) {
                $wh = explode('_', $v);
                if (count($wh) == 2
                        && ( intval($wh[0]) > 0 || intval($wh[1]) > 0)) {
                    $fileScale[] = intval($wh[0]) . '_' . intval($wh[1]);
                }
            }
        }

        $fileId = md5($file->getClientOriginalName() . $dtNow->format('YmdHis') . microtime());
        $dirPath = FileUpload::getRootDirTmp() . $fileDir . '/';
        if (!File::exists($dirPath) || !File::isDirectory($dirPath)) {
            File::makeDirectory($dirPath, 0777, true, true);
        }
        $rename = 1;
        $originalFileId = $fileId;
        while (File::exists($dirPath . $fileId . '.' . $fileExt)) {
            $fileId = $originalFileId . '_' . $rename;
            $rename++;
        }
        $fileFullName = $fileId . '.' . $fileExt;

        $fileInfo = array(
            'id' => $fileId,
            'name' => $fileName,
            'ext' => $fileExt,
            'dir' => $fileDir,
            'scale' => $fileScale
        );

        //移動原始檔
        try {
            $file->move($dirPath, $fileFullName);
        } catch (\Exception $ex) {
            $response['msg'] = '檔案移動失敗：' . $ex->getMessage();
            Log::error($response['msg']);
            return $response;
        }

        //縮圖
        try {
            if ($isImage) {
                if ($isBtsEditor) {
                    $fileInfo['scale'] = array();
                }
                FileUpload::saveThumb($fileInfo);
                FileUpload::saveResize($fileInfo);
            } else {
                $fileInfo['scale'] = array();
            }
        } catch (\Exception $ex) {
            $response['msg'] = '檔案縮圖失敗：' . $ex->getMessage();
            Log::error($response['msg']);
            return $response;
        }

        $response["result"] = "ok";
        $response["file"] = $fileInfo;
        return $response;
    }

    public function delete() {return;
        $requestName = 'jqfuFile';
        $fileInfo = Request::input($requestName);

        if (!is_null($fileInfo) && is_array($fileInfo)) {
            $fileDir = isset($fileInfo['dir']) ? trim($fileInfo['dir']) : '';
            $fileId = isset($fileInfo['id']) ? trim($fileInfo['id']) : '';
            $fileExt = isset($fileInfo['ext']) ? trim($fileInfo['ext']) : '';
            if ($fileDir != '' && $fileId != '' && $fileExt != '') {
                $fileDir = str_replace('../', '', trim($fileDir, '/'));
                $fileId = str_replace('../', '', trim($fileId, '/'));
                $fileExt = str_replace('../', '', trim($fileExt, '/'));

                // file
                $tmpPath = FileUpload::getRootDir() . $fileDir . '/' . $fileId . '.' . $fileExt;
                if (File::exists($tmpPath) && File::isFile($tmpPath)) {
                    File::delete($tmpPath);
                }

                // thumb
                $tmpPath = FileUpload::getRootThumbDir() . $fileDir . '/' . $fileId . '.' . $fileExt;
                if (File::exists($tmpPath) && File::isFile($tmpPath)) {
                    File::delete($tmpPath);
                }

                if (isset($fileInfo['scale']) && is_array($fileInfo['scale'])) {
                    foreach ($fileInfo['scale'] as $k => $v) {
                        $tmpPath = FileUpload::getRootDir() . $fileDir . '/' . $fileId . '_' . $v . '.' . $fileExt;
                        if (File::exists($tmpPath) && File::isFile($tmpPath)) {
                            File::delete($tmpPath);
                        }
                    }
                }
            }
        }
    }

}

?>
