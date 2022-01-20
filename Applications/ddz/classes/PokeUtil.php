<?php

namespace classes;

class PokeUtil{
    public $hua = [
        4=>'黑桃',
        3=>'红桃',
        2=>'梅花',
        1=>'方块',
    ];

    public $huaTag = [
        4=>'heitao-',
        3=>'hongtao-',
        2=>'meihua-',
        1=>'fangkuai-',
    ];

    public function Init(){
        $cardPoint = [
            3,4,5,6,7,8,9,10,'J','Q','K','A','2',
//            'G1','G2',  //鬼牌只有两张，额外push
        ];

        $data = [];

        foreach ($cardPoint as $key=> $value){
            for($indexHua=1;$indexHua<5;$indexHua++){
                array_push($data,[
                    'point'=>$value,
                    'str'=>$this->hua[$indexHua].$value,
                    'num'=>$key+3,
                    'typeHua'=>$indexHua,
                    'sort'=>($key)*4+$indexHua,
                    'tag'=>$this->huaTag[$indexHua].$value
                ]);
            }
        }

        array_push($data,[
            'point'=>'小王',
            'str'=> 'G1',
            'num'=>16,
            'typeHua'=>1,
            'sort'=>53,
            'tag'=>'g-1',
        ]);

        array_push($data,[
            'point'=> '大王',
            'str'=> 'G2',
            'num'=>17,
            'typeHua'=>2,
            'sort'=>54,
            'tag'=>'g-2',
        ]);

        $dataShuffle=$data;
        shuffle($dataShuffle);

        $boss = array_splice($dataShuffle,0,3);
        $player1 = array_splice($dataShuffle,0,17);
        $player2 = array_splice($dataShuffle,0,17);

        return [
//            '$data'=>$data,
//            '$dataShuffle'=>$dataShuffle,
            '$boss'=>$boss,
            '$player1'=>$player1,
            '$player2'=>$player2,
            '$player3'=>$dataShuffle
        ];
    }
}