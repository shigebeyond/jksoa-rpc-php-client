<?php

require_once 'Client.php';
require_once 'JsonrClient.php';

/**
 * 服务引用者
 * @author shijianhang<772910474@qq.com>
 * @date 2019-10-18 6:30 PM
 */
class Referer
{
    // 调试
    static $DEBUG = false;

    // jsonc协议
    const PROTOCOL_JSONC = 'jsonc';

    /**
     * 服务引用者
     * @var array
     */
    protected static $referers = array();

    /**
     * 添加服务提供者
     * @param $providerUrl 服务提供者的url
     */
    public static function addProvider($providerUrl){
        // 解析url
        $parts = parse_url($providerUrl);
        $serviceId = trim($parts['path'], '/');
        $protocol = isset($parts['scheme']) ? $parts['scheme'] : static::PROTOCOL_JSONC; // 协议

        // 解析参数
        $params = array();
        parse_str($parts['query'], $params);
        $version = isset($params['version']) ? $params['version'] : 0; // 版本

        // 获得服务引用者
        if(!isset(static::$referers[$serviceId]))
            static::$referers[$serviceId] = new Referer($serviceId);

        static::$referers[$serviceId]->addClient($protocol, $parts['host'], $parts['port'], $version);
    }

    /**
     * 获得服务引用
     * @param $serviceId 服务标识, 即完整类名
     * @return Referer
     */
    public static function getRefer($serviceId){
        if(!isset(static::$referers[$serviceId]))
            throw new Exception("No refer for service[$serviceId]");

        return static::$referers[$serviceId];
    }

    /**
     * 服务类名
     */
    protected $clazz;

    /**
     * rpc客户端连接
     */
    protected $clients = [];

    public function __construct($clazz)
    {
        $this->clazz = $clazz;
    }

    /**
     * 添加rpc客户端连接
     * @param $protocol
     * @param $host
     * @param $port
     * @param $version
     * @return
     */
    protected function addClient($protocol, $host, $port, $version){
        $class = ucfirst($protocol).'Client';
        $client = new $class($host, $port, $version);
        $this->clients[] = $client;
        return $client;
    }

    /**
     * 随机选择客户端连接
     * @param  $req
     * @return Client
     */
    protected function selectClient(array $req){
        if(empty($this->clients))
            throw new Exception("No client connection for service[{$this->clazz}]");

        $n = count($this->clients);
        $i = mt_rand(0, $n - 1);
        return $this->clients[$i];
    }

    /**
     *
     * @param $name
     * @param $args
     * @return bool
     */
    public function __call($name, array $args)
    {
        // 检查方法名
        if (!is_scalar($name))
            throw new Exception('Method name has no scalar value');

        // 构建prc请求
        $req = array(
            'clazz' => $this->clazz,
            'methodSignature' => $name,
            'args' => $args,
            'id' => $this->generateReqId()
        );
        // curl -i -H 'content-type: application/json' -d '{"args":["shi"],"attachments":{},"clazz":"net.jkcode.jksoa.rpc.example.ISimpleService","id":105333247373737984,"methodSignature":"echo","version":1}' http://192.168.61.237:9080
        // echo "curl -i -H 'content-type: application/json' -d '".json_encode($request)."' ";

        // 选一个客户端连接
        $client = $this->selectClient($req);

        // 发送rpc请求
        return $client->sendRequest($req);
    }

    /**
     * 生成新的请求id
     * @return int
     */
    protected function generateReqId()
    {
        srand((double)microtime() * 1000000);
        $rand_number = rand();
        return $rand_number;
    }

}

/*
Referer::$DEBUG = true;
// 添加服务提供者
Referer::addProvider('jsonr://192.168.61.237:9080/net.jkcode.jksoa.rpc.example.ISimpleService?weight=1');
// 获得远程服务引用
$service = Referer::getRefer('net.jkcode.jksoa.rpc.example.ISimpleService');
// 调用远程服务方法
echo $service->echo('shi');
*/
