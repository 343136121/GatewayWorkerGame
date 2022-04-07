<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

use classes\PokeUtil;
use GatewayWorker\Lib\Db;
use classes\Game;
/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */

//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
// 先做斗地主
class Events {
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     *
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id) {
//        // 向当前client_id发送数据
//        Gateway::sendToClient($client_id, "Hello $client_id\r\n");
//        // 向所有人发送
//        Gateway::sendToAll("$client_id login\r\n");

        $db = Db::instance('app_ddz');
        $res = $db->select('*')->from('game')
//            ->where()
            ->query();

        $data = [
            '$res'=>$res
        ];
        Gateway::sendToClient($client_id, json_encode($data));
    }

    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $message 具体消息
     */

    public static function onMessage($client_id, $message) {
        // 游戏开始，记录每位玩家id，并生成随机牌河栈，发牌并数组形式存储每位玩家的牌，记住当前局数、巡数、当前出牌的玩家

        // 摸一张牌，判断当前玩家能否杠、胡。

        // 出一张牌，判断其他玩家能否鸣牌、胡牌

        $dataArr = json_decode($message, true);
        if (!isset($dataArr['type']) || empty($dataArr['type'])) {
            return false;
        }

        /*
         * 考虑使用邀请码来定位某一个房间
         * 首先通过bindUid 绑定用户和clientid，测试就直接使用clientid
         * 数据库判断是否能坐下
         * 坐下则加入group joinGroup。离开就leaveGroup
         * 三方 都按开始 则改变房间或游戏状态
         * 生成游戏记录 确定玩家座次 入数据库
         * 分别给每位玩家发牌。分别给对应位置的人发消息，其中有牌组
         * 每次出牌方，包含牌型大小轮次等 发送给后端，再由后端发给其他人
         * 当一方手牌为0时发送获胜请求，后端再群发
         *
         *
         */
        switch ($dataArr['type']){
            case 'ping':
                break;
            case 'ready':
                Game::ready($client_id,$dataArr);
                break;

            case 'sit':
                // 可用定时脚本检测掉线用户?
                Game::sit($client_id);
                break;
        }


    }

    /**
     * 当用户断开连接时触发
     * @param int $client_id 连接id
     */
    public static function onClose($client_id) {


    }
}
