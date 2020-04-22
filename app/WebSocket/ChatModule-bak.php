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
    private $users = [];
    private $uid = 0;
    private $fdBind = [];

    private $username;

    /**
     * @OnOpen()
     * @param Request $request
     * @param int     $thatFd
     */
    public function onOpen(Request $request, int $thatFd): void
    {

        //server()->push($fd,"{$frame->fd} 用户群发信息：$msg");
//        server()->push($request->getFd(), "Opened, welcome!(FD: $fd)");
//
//        $fullClass = Session::current()->getParserClass();
//        $className = basename($fullClass);
//
//        $help = <<<TXT
//Message data parser: $className
//Send message example:
//
//```json
//{
//    "cmd": "home.index",
//    "data": "hello"
//}
//```
//
//Description:
//
//- cmd `home.index` => App\WebSocket\Chat\HomeController::index()
//TXT;

        //server()->push($fd, $help);
    }

    /**
     * @onMessage
     * @param Server $server
     * @param int     $fd
     */
    public function onMessage(Server $server, \Swoole\WebSocket\Frame $frame): void
    {
        //var_dump(Session::current()->getData());
        //echo "原始接受数据\n";
        //var_dump(gettype($frame->data));
        //$fullData = json_decode($frame->data, true);
        //echo "接受数据\n";
        //var_dump($fullData);
//        if ($fullData['router'] == 'home.bind') {
            $this->uid = $this->uid + 1;
            $uid = $this->uid;
            echo "用户uid\n";
            var_dump($uid);
//            $this->fdBind[$frame->fd] = $uid;
//            $this->users[$uid] = [
//                'username' => $fullData['data'],
//            ];
//            server()->pageEach(function ($fd) use ($frame, $uid) {
//                //var_dump($this->users[$uid]);
//                $msg = $this->users[$uid]['username'] . ' 已进入大厅';
//                server()->push($fd, $this->result(0, $msg));
//            }, 100);
//        } else {
//            $msg = $fullData['data'];
//            if (!empty(server()->pageEach(function () {}))) {
//                // 向所有连接发消息
//                server()->pageEach(function ($fd) use ($frame, $msg) {
//                    echo "fdbind打印\n";
//                    //var_dump($this->fdBind);
//                    $uid = $this->fdBind[$fd];
//                    echo '2';
//                    server()->push($fd, $this->result($uid, $msg, ($frame->fd == $fd)));
//                }, 100);
//            }
//        }
        //echo "====在线用户====\n";
        //var_dump($this->users);
    }

    /**
     * @OnClose
     * @param Server $server
     * @param int $fd
     */
    public function onClose(server $server, int $nowFd): void
    {
        if (!empty(server()->pageEach(function(){}))) {
//            server()->pageEach(function($fd) use($nowFd) {
//                server()->push($fd,"{$nowFd} 用户退出群聊");
//            }, 100);
        }

    }

    private function result(int $uid = 0, $msg, $me=false) : string
    {
        $username = !$uid || !isset($this->users[$uid]) ? '' :$this->users[$uid]['username'];
        return json_encode([
            'system' => !boolval($uid),
            'data' => [
                'username' => $username,
                'message' => $msg,
                'date' => date('m/d H:s:i',time())
            ],
            'me' => $me,
        ]);
    }

}
