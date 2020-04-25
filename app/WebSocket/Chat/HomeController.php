<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\WebSocket\Chat;

use App\WebSocket\ChatModule;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Redis\Redis;
use Swoft\Session\Session;
use Swoft\WebSocket\Server\Message\Message;
use Swoft\WebSocket\Server\Message\Request;
use Swoft\WebSocket\Server\Annotation\Mapping\WsController;
use Swoft\WebSocket\Server\Annotation\Mapping\MessageMapping;

/**
 * Class HomeController
 *
 * @WsController()
 */
class HomeController
{
    private const REDISUSER = 'user:';  // hash
    private const TYPE_TEXT = 'text';
    private const TYPE_IMG = 'img';

    /**
     * Message command is: 'home.index'
     *
     * @return void
     * @MessageMapping()
     */
    public function index(): void
    {
        Session::current()->push('hi!');
    }

    /**
     * 用户注册uid
     * Message command is: 'home.bind'
     *
     * @return void
     * @MessageMapping()
     */
    public function bind(Request $req): void
    {
        $fd = $req->getFd();
        $message = $req->getMessage()->toArray();
        $clientInfo = server()->getSwooleServer()->getClientInfo($fd);
        $uid = $clientInfo['uid'] ?? 0;
        $username = $message['data'];
        $msg = $username . ' 已进入大厅';
        if (!$uid) {
            $uid = Redis::incr('chat:offset_uid');
            $info = [
                'username' => $username
            ];
            Redis::hMSet(self::REDISUSER . $uid, $info);
            server()->getSwooleServer()->bind($fd, $uid);
        }
        $result = [
            'username' => $username,
            'msg' => $msg,
            'system' => true,
            'date' => date('m/d H:s:i',time()),
        ];

        server()->pageEach(function ($fd) use($result) {
            server()->push($fd, $this->result($result));
        }, 100);
    }

    /**
     * Message command is: 'home.text'
     *
     * @param string $data
     * @MessageMapping()
     */
    public function msg(Request $req): void
    {
        $nowFd = $req->getFd();
        try {
            $message = $req->getMessage()->toArray();
            $clientInfo = server()->getSwooleServer()->getClientInfo($nowFd);
            if (!isset($message['ext']['type']) || !in_array($message['ext']['type'], [self::TYPE_TEXT, self::TYPE_IMG])) {
                throw new \Exception('消息类型不正确');
            }
            $type = $message['ext']['type'];
            $user = ChatModule::getUserInfo((int)$clientInfo['uid']);
            $result = [
                'username' => $user['username'],
                'system' => false,
                'type' => $type,
                'me' => false,
                'date' => date('m/d H:s:i', time()),
                'msg' => $message['data'],
            ];
            $result = array_merge($result, call_user_func_array([$this, $type], [$message]));

            if (!empty(server()->pageEach(function () {
            }))) {
                server()->pageEach(function ($fd) use ($req, $nowFd, $result) {
                    $result['me'] = $nowFd == $fd;
                    server()->push($fd, $this->result($result));
                }, 100);
            }
        } catch (\Exception $e) {
            server()->push($nowFd, $this->result([], 1, $e->getMessage()));
        }
    }

    /**
     * 字符处理
     * @param array $message
     * @return array
     */
    private function text(array $message) : array
    {
        return [];
    }

    /**
     * 图像处理
     *
     * @param string $data
     * @MessageMapping()
     */
    private function img(array $message): array
    {
        $data = $message['data'];
        $imageDir = ChatModule::UPLOADDIR;
        $imageName = time() . rand(1000,9999) . ".jpeg";
        file_put_contents($imageDir . $imageName, $data);
        $uri = \config('app.siteUri');
        $port = \Swoft::getBean('httpServer')->getPort();
        $host = 'http://'.$uri.':'.$port.'/chat-upload-img/';
        $result = [
            'msg' => $host . $imageName,
        ];

        return $result;
    }

    /**
     * 统一返回格式
     * @param array $data
     * @param int $error
     * @param string $errorMsg
     * @return array
     */
    private function result(array $data=[], int $error=0, string $errorMsg='') : string
    {
        $result = [
            'error' => $error,
            'data' => $data,
            'error_msg' => $errorMsg,
        ];
        return json_encode($result);
    }
}
