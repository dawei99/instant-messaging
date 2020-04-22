<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\WebSocket;

use App\WebSocket\Chat\HomeController;
use Swoft\Http\Message\Request;
use Swoft\Redis\Redis;
use Swoft\Session\Session;
use Swoft\WebSocket\Server\Annotation\Mapping\OnOpen;
use Swoft\WebSocket\Server\Annotation\Mapping\OnClose;
use Swoft\WebSocket\Server\Annotation\Mapping\WsModule;
use Swoft\WebSocket\Server\MessageParser\JsonParser;
use Swoft\WebSocket\Server\MessageParser\RawTextParser;
use Swoft\WebSocket\Server\Annotation\Mapping\OnMessage;
use App\WebSocket\Chat\MyJsonParser;
use Swoole\WebSocket\Server;
use function basename;
use function server;


/**
 * Class ChatModule
 *
 * @WsModule(
 *     "/chat",
 *     messageParser=MyJsonParser::class,
 *     controllers={HomeController::class}
 * )
 */
class ChatModule
{
    private $username;
    private $uid;

    /**
     * @OnOpen()
     * @param Request $request
     * @param int     $thatFd
     */
    public function onOpen(Request $request, int $thatFd): void
    {
        //server()->getSwooleServer()->uid = isset(server()->getSwooleServer()->uid) ? server()->getSwooleServer()->uid+1 : 1;
        //server()->push($fd, $help);
    }

    /**
     * @onMessage
     * @param Server $server
     * @param int     $fd
     */
    public function onMessage(Server $server, \Swoole\WebSocket\Frame $frame): void
    {
        $fullData = json_decode($frame->data, true);
        if ($fullData['router'] == 'home.bind') {
            $this->username = $fullData['data'];
            if (is_array(Redis::sMembers('chat:uids')) && !in_array($this->uid, Redis::sMembers('chat:uids'))) {
                // 生成用户ID并加入在线队列
                //$this->uid = Redis::incr('chat:incr_uid');
                //Redis::sAdd('chat:uids', strval($this->uid));
                // 通知所有用户
                server()->pageEach(function ($fd) use ($frame) {
                    $msg = $this->username . ' 已进入大厅';
                    server()->push($fd, $this->result($msg, true));
                }, 100);
            }
        } else {
            $msg = $fullData['data'];
            if (!empty(server()->pageEach(function () {}))) {
                // 向所有连接发消息
                server()->pageEach(function ($fd) use ($frame, $msg) {
                    server()->push($fd, $this->result($msg, false, ($frame->fd == $fd)));
                }, 100);
            }
        }
    }

    /**
     * @OnClose
     * @param Server $server
     * @param int $fd
     */
    public function onClose(server $server, int $nowFd): void
    {
        if (!empty(server()->pageEach(function(){}))) {
            server()->pageEach(function($fd) use($nowFd) {
                //Redis::sRem('chat:uids', strval($this->uid));
                server()->push($fd,$this->result("{$this->username} 用户退出群聊", true));
            }, 100);
        }

    }

    private function result(string $msg, bool $system = false, bool $me=false) : string
    {
        return json_encode([
            'system' => $system,
            'data' => [
                'username' => $this->username,
                'message' => $msg,
                'date' => date('m/d H:s:i',time())
            ],
            'me' => $me,
        ]);
    }

}
