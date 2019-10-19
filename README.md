# jksoa-rpc-php-client

`jksoa-rpc-php-client` is a php rpc client for [jksoa](https://github.com/shigebeyond/jksoa)

Only support `jsonr` rpc protocol.

## usage

```
Referer::$DEBUG = true;
// 添加服务提供者
Referer::addProvider('jsonr://192.168.61.237:9080/net.jkcode.jksoa.rpc.example.ISimpleService?weight=1');
// 获得远程服务引用
$service = Referer::getRefer('net.jkcode.jksoa.rpc.example.ISimpleService');
// 调用远程服务方法
echo $service->echo('shi');
```

## implement

For `jsonr` rpc protocol, php client implement just convert method invocation into http request, same as the follow http request command:

```
curl -d '{"args":["shi"],"clazz":"net.jkcode.jksoa.rpc.example.ISimpleService","id":105333247373737984,"methodSignature":"echo","version":1}' http://192.168.61.237:9080
```

## todo

1. support zookeeper subscriber to learn about the changing providers.
2. support load balance.
3. support long connection.
4. support other protocol.
