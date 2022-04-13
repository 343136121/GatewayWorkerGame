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

        // 告知自己的座位信息
        Gateway::sendToClient($client_id,json_encode([
            'type'=>'sit',
            'status'=>1,
            'info'=>'成功',
            'data'=>[
                'seat'=>$seatEmpty
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
//        PokeUtil::writeFile(print_r($dataArr,true));
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

            $seatStart = mt_rand(1,3);
            // 插入一条游戏数据，返回game_id，前端存储下
            $game_id = $db->insert('game')->cols([
                'room_id'=>$room_id,
                'poke_player1'=>json_encode($init['player1']),
                'poke_player2'=>json_encode($init['player2']),
                'poke_player3'=>json_encode($init['player3']),
                'poke_boss'=>json_encode($init['boss']),
                'seat_start'=>$seatStart,
            ])->query();

            Gateway::sendToGroup($room_id,json_encode([
                'type'=>'start',
                'status'=>1,
                'info'=>'成功',
                'data'=>[
                    'player1'=>$init['player1'],
                    'player2'=>$init['player2'],
                    'player3'=>$init['player3'],
                    'boss'=>$init['boss'],
//                    'seat'=>$seatStart,
                    'seat' => 3,
                    'game_id'=>$game_id
                ]
            ]));
        }

    }

    public static function jiao($client_id,$dataArr){
        $room_id = $dataArr['room_id'];
        $game_id = $dataArr['game_id'];
        $room_seat_id = $dataArr['room_seat_id'];

        $db = Db::instance('app_ddz');
        $seat = $db->select('*')->from('room_seat')
            ->where("id = {$room_seat_id}")
            ->row();

        $game_log_id = $db->insert('game_log')->cols([
            'game_id'=>$game_id,
            'room_seat_id'=>$room_seat_id,
            'type'=>1,
            'value'=>$dataArr['jiao']
        ])->query();

        // 通知大家下一轮
        Gateway::sendToGroup($room_id,json_encode([
            'type'=> $dataArr['jiao'] == 1?'jiao_over':'jiao',
            'status'=>1,
            'info'=>'成功',
            'data'=>[
                'seatNext' => $dataArr['jiao'] == 1? $seat['seat'] :(($seat['seat']+1)%3 == 0 ? 3 : ($seat['seat']+1)%3)
            ]
        ]));
    }

    public static function chupai($client_id,$dataArr){
        $room_id = $dataArr['room_id'];
        $game_id = $dataArr['game_id'];
        $room_seat_id = $dataArr['room_seat_id'];
        $seatChupai = $dataArr['seatChupai'];
        $pokeOut = $dataArr['pokeOut'];
        $pokeHand = $dataArr['pokeHand'];
        $checkedPokeOut = $dataArr['checkedPokeOut'];

        $db = Db::instance('app_ddz');
        $seat = $db->select('*')->from('room_seat')
            ->where("id = {$room_seat_id}")
            ->row();

//        $game_log_id = $db->insert('game_log')->cols([
//            'game_id'=>$game_id,
//            'room_seat_id'=>$room_seat_id,
//            'type'=>1,
//            'value'=>$checkedPokeOut
//        ])->query();

        Gateway::sendToGroup($room_id,json_encode([
            'type'=> 'chupai',
            'status'=>1,
            'info'=>'成功',
            'data'=>[
                'type'=>'chupai',
                'seatChupai'=> $seat['seat'],
                'seatNext' => (($seat['seat']+1)%3 == 0 ? 3 : ($seat['seat']+1)%3),
                'pokeOut'=>$pokeOut,
                'pokeHand'=>$pokeHand,
                'checkedPokeOut'=>$checkedPokeOut
            ]
        ]));
    }

}