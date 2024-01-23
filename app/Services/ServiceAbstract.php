<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Exception;
use PDO;

//抽象處理者
abstract class ServiceAbstract
{
    private $verifyResult; // 驗證結果
    private $processResult; // 驗證結果
    private $cache; // 是否cache
    private $cacheKey;
    private $cacheTime;
    protected $params; // 參數
    protected $message; // 回傳訊息
    protected $data; // 回傳資料

    public function __construct()
    {
        // ini_set('memory_limit', '-1');
        $this->processResult = false;
        $this->verifyResult = false;
        $this->cache = false;
        $this->params = [];
        $this->message = '';
        $this->data = [];
    }

    protected function validator($params, $rule, $message = null)
    {
        return Validator::make($params, $rule);
    }

    // 驗證參數
    abstract public function verify($param);

    // 主要邏輯
    abstract protected function process();

    // 取得驗證結果
    public function getVerifyResult()
    {
        return $this->verifyResult;
    }

    // 設定驗證結果
    private function setVerifyResult(bool $result)
    {
        $this->verifyResult = $result;
    }

    // 取得處理結果
    public function getProcessResult()
    {
        return $this->processResult;
    }

    // 設定處理結果
    private function setProcessResult(bool $result)
    {
        $this->processResult = $result;
    }

    // 取得參數
    public function getParams()
    {
        return $this->params;
    }

    // 設定參數
    private function setParams($params)
    {
        $this->params = $params;
    }

    // 取得訊息
    public function getMessage()
    {
        return $this->message;
    }

    // 設定訊息
    protected function setMessage(string $message)
    {
        $this->message = $message;
    }

    // 取得資料
    public function getData()
    {
        return $this->data;
    }

    // 設定資訊
    protected function setData(array $data)
    {
        $this->data = $data;
    }

    // 設定redis cache
    public function setCacheSetting(string $key, int $cacheTime, $cache = true)
    {
        $this->cache = $cache;
        $this->cacheKey = $key;
        $this->cacheTime = $cacheTime;
    }

    // 設定redis
    private function setRedis()
    {
        if (!$this->cache || empty($this->data)) {
            return false;
        }

        if (env('APP_ENV') == 'production') {
            Redis::setex($this->cacheKey, $this->cacheTime, json_encode($this->data));
        };

        return true;
    }

    // 強制更新redis
    public function cacheUpdate()
    {
        return $this->setRedis();
    }

    // 主程序
    final public function handle($params = [])
    {
        $verify = $this->verify($params);

        if (!isset($verify['result'])) {
            throw new Exception($verify['message'], 4001);
        }

        if (!$verify['result']) {
            throw new Exception($verify['message'], $verify['code'] ?? 4444);
        }

        $this->setVerifyResult(true);

        foreach ($verify['addParams'] ?? [] as $key => $value) {
            $params[$key] = $value;
        }

        $this->setParams($params);

        if ($this->cache && !empty($redis = Redis::get($this->cacheKey))) {
            $this->setProcessResult(true);
            $this->setData(json_decode($redis, true));
            return;
        }

        $process = $this->process();
        if (!empty($process['message'])) {
            $this->setMessage($process['message']);
        }

        if (!isset($process['result']) || !isset($process['data'])) {
            throw new Exception($this->getMessage(), $process['code'] ?? 4001);
        }

        if ($process['result']) {
            $this->setProcessResult(true);
            $this->setData($process['data']);
            $this->setRedis();
        }
    }
}
