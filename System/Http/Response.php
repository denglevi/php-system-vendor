<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/19 0019
 * Time: 16:38
 */

namespace System\Http;


use System\Context;

class Response
{

    private $_ended = false;
    private $_html = '';

    public function __construct()
    {
    }

    public function header($key, $value)
    {
        header("{$key}:{$value}");

        return $this;
    }

    public function status($statusCode)
    {
        if ($this->_ended) return $this;
        $reason = StatusCode::getStatusReason($statusCode);
        header("HTTP/1.1 $statusCode $reason", true, $statusCode);

        return $this;
    }

    /**
     * 设置页面要跳转到的 Url。
     *
     * @param string $redirect
     * @param string 跳转前要提示给用户的消息。 只有输出为 JSON 格式时有效。
     * @return Response
     */
    public function redirect($redirect, $message = null)
    {
        if (!$this->_ended) {
            if ($this->isJson()) {
                $this->_json['redirect'] = $redirect;
                if ($message !== null) $this->_json['message'] = $message;
            } else {
                $this->status(301);
                $this->header('Location', $redirect);
            }
            $this->end();
        }
        return $this;
    }

    public function endRedirect($url)
    {
        $this->redirect($url)->end();
    }

    public function write($html = null)
    {
        $this->_html = $html;
        echo $this->_html;
        return $this;
    }

    /**
     * 发送 Http 响应体，并结束请求处理。
     * end 操作后将向客户端浏览器发送 HTML 内容， 并销毁 $request|$response 对象。
     * 如果开启了 KeepAlive 则连接将会保持， 服务器会等待下一次请求。 否则会切断连接。
     *
     */
    public function end($html = null)
    {
        if ($this->_ended) return $this;
        if ($html === null && $this->_json !== null) {
            if ($this->_jsonAttribute) foreach ($this->_jsonAttribute as $key => $value) $this->_json[$key] = $value;
            if (($controller = Context::controller()) && $controller->debug()) {
                $option = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
                $this->_json['_took'] = round(1000 * (microtime(true) - Context::request()->server()['REQUEST_TIME_FLOAT']), 3);
            } else {
                $option = JSON_UNESCAPED_UNICODE;
            }
            $html = json_encode($this->_json, $option);
        }

        $this->write($html);
        if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
        $this->_ended = true;
    }

    public function isJsonRedirect($url)
    {

    }

    // -------------------------------------------------------------------- json response
    private $_json = null;
    private $_jsonAttribute = null;

    /**
     * @param null $value
     * @return $this|bool
     * @desc 获取或设置输出内容是否是 JSON 格式
     */
    public function isJson($value = null)
    {
        if (0 === func_num_args()) {
            return $this->_json !== null;
        } else {
            if ($this->_json === null && $value) {
                $this->_json = ['result' => 0, 'message' => ''];
                $this->_jsonAttribute = [];
            };
            return $this;
        }
    }

    /**
     * 设置 JSON 结果中的 result|message|data 属性值。
     *
     * @param int 结果码。 0 表示操作成功， 其他表示出现错误。
     * @param string 成功或错误时对应的消息文本。
     * @param mixed 结果数据
     * @return Response
     */
    public function json($result, $message = null, $data = null)
    {
        $this->isJson(true)->_json['result'] = $result;
        if ($message !== null) $this->_json['message'] = $message;
        if ($data !== null) $this->_json['data'] = $data;
        return $this;
    }

    /**
     * 设置 JSON 结果中的 result|message|data 属性值并结束响应。
     *
     * @param int 结果码。 0 表示操作成功， 其他表示出现错误。
     * @param string 成功或错误时对应的消息文本。
     * @param mixed 结果数据
     * @return Response
     */
    public function endJson($result, $message = null, $data = null)
    {
        return $this->json($result, $message, $data)->end();
    }

    /**
     * 设置 JSON 结果中的 data 属性值。
     *
     * @param mixed 结果数据
     * @param string 要提示给用户的消息。
     * @return Response
     */
    public function jsonData($data, $message = null)
    {
        return $this->json(0, $message, $data);
    }

    /**
     * 设置 JSON 结果中的 data 属性值并结束响应。
     *
     * @param mixed 结果数据
     * @param string 要提示给用户的消息。
     * @return Response
     */
    public function endJsonData($data, $message = null)
    {
        return $this->jsonData($data, $message)->end();
    }

    /**
     * 设置 JSON 结果中顶层(与 result|message|data 同级)属性。
     *
     * @param {string|array} 如果该参数为数组， 则批量添加数组内的键值对到顶层属性； 否则将 name-value 对添加到顶层属性。
     * @param mixed $value
     * @return Response
     */
    public function jsonAttribute($name, $value = null)
    {
        $this->isJson(true);

        if (is_array($name)) {
            $this->_jsonAttribute = array_merge($this->_jsonAttribute, $name);
        } else if ($name = strval($name)) {
            if (1 === func_num_args()) {
                return isset($this->_jsonAttribute[$name]) ? $this->_jsonAttribute[$name] : null;
            } else {
                $this->_jsonAttribute[$name] = $value;
            }
        }
        return $this;
    }

    /**
     * 设置 JSON 结果中的 redirect 属性值。 客户端在看到该属性非空时会自动执行跳转（需与 util.getJsonResponse 方法结合）。
     *
     * @param string 要跳转到的 Url。
     * @param string 跳转前要提示给用户的消息。
     * @return Response
     */
    public function jsonRedirect($redirect, $message = null)
    {
        return $this->isJson(true)->redirect($redirect, $message);
    }

    /**
     * 设置 JSON 结果中的 redirect 属性值并结束响应。 客户端在看到该属性非空时会自动执行跳转（需与 util.getJsonResponse 方法结合）。
     *
     * @param string 要跳转到的 Url。
     * @param string 跳转前要提示给用户的消息。
     * @return Response
     */
    public function endJsonRedirect($redirect, $message = null)
    {
        return $this->isJson(true)->redirect($redirect, $message)->end();
    }
}