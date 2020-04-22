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

use App\Model\Entity\TestUser;
use Swoft\Session\Session;
use Swoft\WebSocket\Server\Annotation\Mapping\MessageMapping;
use Swoft\WebSocket\Server\Annotation\Mapping\WsController;
use Swoft\WebSocket\Server\Message\Message;
use Swoft\WebSocket\Server\Message\Request;

/**
 * Class HomeController
 *
 * @WsController()
 */
class HomeController
{
    /**
     * Message command is: 'home.index'
     *
     * @return void
     * @MessageMapping()
     */
    public function index(): void
    {
        Session::current()->push('hi, this is home.index');
    }

    /**
     * Message command is: 'home.echo'
     *
     * @param string $data
     * @MessageMapping()
     */
    public function msg(Request $req): void
    {

        //var_dump(Session::current());

//        TestUser::new([
//        'username' => 'swoft_user',
//        'num' => 1,
//        ])->save()

        //$result = TestUser::where(['id' => 2])->toSql();

//        $result = TestUser::new();
//        $result->setUsername('xxxxxxxxxx');
//        $result->setNum(1);

        //$result = TestUser::where('username', 'xxxxxxxxxx')->delete();

        //$result = TestUser::where(['id' => 2])->update(['num' => 9]);

        //$result = TestUser::where([['id', '=', 1]])->getModels(['id', 'username']);

        //$result = TestUser::forPage(1, 10)->get(['id', 'username'])->keyBy('id');
        //Session::current()->push('(home.echo)结果: ' . gettype($result));
    }

    /**
     * Message command is: 'home.ar'
     *
     * @param string $data
     * @MessageMapping("ar")
     *
     * @return string
     */
    public function autoReply(string $data): string
    {
        return '(home.ar)Recv: ' . $data;
    }

    /**
     * Message command is: 'help'
     *
     * @param string $data
     * @MessageMapping("help", root=true)
     *
     * @return string
     */
    public function help(string $data): string
    {
        return '(home.ar)Recv: ' . $data;
    }
}
