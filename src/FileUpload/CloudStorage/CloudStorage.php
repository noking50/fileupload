<?php

namespace Noking50\FileUpload\CloudStorage;

use InvalidArgumentException;

/**
 * 儲存體
 * 
 * @package App\Classes\FileUpload\CloudStorage
 */
class CloudStorage {

    /**
     * 儲存使用過的儲存體服務物件
     *
     * @var array 
     */
    protected $storages = array();

    /**
     * Construct
     */
    public function __construct() {
        
    }

    /**
     * 取得儲存體服務
     * 
     * @param string $name 儲存體服務名稱
     * @return mix App\Classes\FileUpload\CloudStorageXXXXX
     * @throws InvalidArgumentException
     */
    public function storage($name = null) {
        if (is_null($name)) {
            $name = config('fileupload.default_storage', 'local');
        }

        if (!isset($this->storages[$name])) {
            $classname = __NAMESPACE__ . '\CloudStorage' . ucfirst($name);
            if (!class_exists($classname)) {
                throw new InvalidArgumentException('cloud storage ' . $classname . ' not found.');
            }

            $storage = new $classname();
            $this->storages[$name] = $storage;
        }
        return $this->storages[$name];
    }

    /**
     * 呼叫預設儲存體服務的函式
     * 
     * @param string $method
     * @param mixed $parameters
     * @return mix
     */
    public function __call($method, $parameters) {
        return $this->storage()->$method(...$parameters);
    }

}
