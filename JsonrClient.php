<?php

/**
 *
 * @author shijianhang<772910474@qq.com>
 * @date 2019-10-18 8:02 PM
 */
class JsonrClient extends Client{

    /**
     * 发送rpc请求
     * @param array $req
     * @return rpc执行结果
     */
    public function sendRequest(array $req){
        $url = "http://{$this->host}:{$this->port}"; // server的http url
        // curl -i -H 'content-type: application/json' -d '{"args":["shi"],"attachments":{},"clazz":"net.jkcode.jksoa.rpc.example.ISimpleService","id":105333247373737984,"methodSignature":"echo","version":1}' http://192.168.61.237:9080
        if(Referer::$DEBUG)
            echo "curl -i -H 'content-type: application/json' -d '".json_encode($req)."' $url\n";

        $reqId = $req['id']; // 请求id
        $req = json_encode($req); // json化请求

        // 准备curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        // 发送http请求+获得响应
        $resp = curl_exec($ch);
        curl_close($ch);

        // 解析响应
        if ($resp === FALSE) {
            $err = 'errno='. curl_errno($ch). ', err=' . curl_error($ch);
            throw new Exception('Unable to curl ' . $url . ' :' . $err);
        }
        if(Referer::$DEBUG)
            echo "response: $resp\n";

        $resp = json_decode($resp,true); // json化响应
        $errNo = json_last_error();
        if($errNo !== JSON_ERROR_NONE){
            $err = 'errno='. $errNo. ', err=' . json_last_error_msg();
            throw new Exception('Unable to json decode response content: '.$err.' :'.$resp);
        }

        // 检查请求id
        if ($resp['requestId'] != $reqId)
            throw new Exception('Incorrect response id (request id: '.$reqId.', response id: '.$resp['requestId'].')');

        // 有异常则抛异常
        if (isset($resp['exception'])) {
            $err = isset($resp['exception']['message']) ? $resp['exception']['message'] : '';
            throw new Exception('Response error: '.$err);
        }

        // 返回成功结果
        return $resp['value'];
    }
}
