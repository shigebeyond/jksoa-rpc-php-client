<?php

/**
 * rpc客户端
 * @author shijianhang<772910474@qq.com>
 * @date 2019-10-18 7:17 PM
 */
abstract class Client{

    /**
     * ip
     */
    protected $host = NULL;

    /**
     * 端口
     */
    protected $port = NULL;

    /**
     * 版本
     */
    protected $version = NULL;

    /**
     * 构造函数
     * @param $host
     * @param $port
     * @param int $version
     */
    public function __construct($host, $port, $version = 0)
    {
        $this->host = $host;
        $this->port = $port;
        $this->version = $version;
    }

    /**
     * 发送rpc请求
     * @param array $req
     * @return rpc执行结果
     */
    public abstract function sendRequest(array $req);
}
