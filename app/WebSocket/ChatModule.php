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
    public const UPLOADDIR = 'public'.DIRECTORY_SEPARATOR.'atta'.DIRECTORY_SEPARATOR.'imgs'.DIRECTORY_SEPARATOR;

    private $username;
    private $uid;

    private const REDISUSER = 'user:';  // hash

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
     * @OnClose
     * @param Server $server
     * @param int $fd
     */
    public function onClose(server $server, int $nowFd): void
    {
        $clientInfo = server()->getSwooleServer()->getClientInfo($nowFd);
        $uid = $clientInfo['uid'] ?? 0;
        $userInfo = self::getUserInfo($uid);
        if (!empty($userInfo) && !empty(server()->pageEach(function(){}))) {
            server()->pageEach(function($fd) use($nowFd, $userInfo) {
                server()->push($fd,$this->result("{$userInfo['username']} 用户退出群聊", true));
            }, 100);
        }

    }

    /**
     * 获取用户信息[redis]
     * @param int $uid
     * @return array
     */
    public static function getUserInfo(int $uid) : array
    {
        $key = self::REDISUSER . $uid;
        if (Redis::exists($key)) {
            return Redis::hGetAll($key);
        } else {
            return [];
        }
    }

    /**
     * @param array $msg
     * @param bool $system
     * @param bool $me
     * @param string $type (text|img)
     * @return string
     */
//    private function result(array $data=[], bool $system = false, bool $me=false, string $type="text") : string
//    {
//        $result = [
//            'system' => $system,
//            'data' => [],
//            'type' => $type,
//            'me' => $me,
//        ];
//
//        $defaultData = ['username' => '', 'msg' => '', 'date' => ''];
//
//        $result['data'] = array_merge($defaultData, $data);
//        return json_encode($result);
//    }

}
