<?php

namespace classes;

use GatewayWorker\Lib\Db;
use \GatewayWorker\Lib\Gateway;

class Game{

    public static function sit($client_id){
        $room_id = 1;

        $db = Db::instance('app_ddz');
        $db->beginTrans();

        $room = $db->select('*')->from('room')
            ->where('id = :id')
            ->bindValues(['id'=>$room_id])
            ->row();

        if(empty($room)){
            Gateway::sendToClient($client_id,json_encode(['type'=>'sit','status'=>-1]));
            die;
        }

        if($room['player_count'] >= 3){
            Gateway::sendToClient($client_id,json_encode([
                'type'=>'sit',
                'status'=>-1,
                'info'=>'已满'
            ]));
            die;
        }

        $resUpdate = $db->query("UPDATE `room` SET `player_count` = `player_count` + 1 WHERE ID={$room['id']} and player_count<3");

        $seatEmpty = $db->select('*')->from('room_seat')
            ->where('status = 0')
            ->row();
        if(empty($seatEmpty)){
            Gateway::sendToClient($client_id,json_encode([
                'type'=>'sit',
                'status'=>-1,
                'info'=>'座位已满'
            ]));
            die;
        }

        $resUpdateSeat = $db->update('room_seat')->cols([
            'user_id'=>(int)$client_id,
            'client_id'=>$client_id,
            'status'=>1
        ])->where("ID={$seatEmpty['id']}")->query();
        $seatEmpty['user_id'] = (int)$client_id;
        $seatEmpty['client_id'] = $client_id;
        $seatEmpty['status'] = 1;


        $db->commitTrans();

        // 加入组
        Gateway::joinGroup($client_id,$room['id']);

        // 告知自己的座位号
        Gateway::sendToClient($client_id,json_encode([
            'type'=>'sit',
            'status'=>1,
            'info'=>'成功',
            'data'=>[
                'seat'=>$seatEmpty['seat']
            ]
        ]));

        // 通知所有人座位信息
        $seats = $db->select('*')->from('room_seat')
            ->where('room_id = :room_id')
            ->bindValues(['room_id'=>$room['id']])
            ->orderByASC(['seat'])
            ->query();

        Gateway::sendToGroup($room['id'],json_encode([
            'type'=>'seats',
            'status'=>1,
            'info'=>'成功',
            'data'=>[
                'seats'=>$seats,
                'room_id'=>$room['id']
            ]
        ]));
    }

    // 准备与开始游戏
    public static function ready($client_id,$dataArr){
        PokeUtil::writeFile(print_r($dataArr,true));
        $room_id = $dataArr['room_id'];
        $seat = $dataArr['seat'];

        $db = Db::instance('app_ddz');

        $res = $db->update('room_seat')->cols([
            'status'=>2
        ])->where("room_id={$room_id} and seat={$seat}")->query();

        // 使前台的准备按钮发生变化
        Gateway::sendToClient($client_id,json_encode([
            'type'=>'ready',
            'status'=>1,
            'info'=>'成功'
        ]));

        // 如果全都准备，则发牌，开始游戏
        $seats = $db->select('*')->from('room_seat')
            ->where('room_id = :room_id')
            ->bindValues(['room_id'=>$room_id])
            ->orderByASC(['seat'])
            ->query();

        $count_ready = 0;
        foreach ($seats as $value){
            if($value['status'] == 2){
                $count_ready ++ ;
            }
        }
        if($count_ready >=3){
            $pokeUtil = new PokeUtil();
            $init = $pokeUtil->Init();

            Gateway::sendToGroup($room_id,json_encode([
                'type'=>'start',
                'status'=>1,
                'info'=>'成功',
                'data'=>[
                    'player1'=>$init['player1'],
                    'player2'=>$init['player2'],
                    'player3'=>$init['player2'],
                    'boss'=>$init['boss'],
                ]
            ]));
        }

    }


}