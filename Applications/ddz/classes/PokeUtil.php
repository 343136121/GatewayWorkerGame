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
        ini_set('memory_limit','4000M');
        $cardPoint = [
            3,4,5,6,7,8,9,10,'J','Q','K','A','2',
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
            'point'=>'g',
            'str'=> '小王',
            'num'=>16,
            'typeHua'=>1,
            'sort'=>53,
            'tag'=>'g-1',
        ]);

        array_push($data,[
            'point'=> 'G',
            'str'=> '大王',
            'num'=>17,
            'typeHua'=>2,
            'sort'=>54,
            'tag'=>'g-2',
        ]);

        $dataShuffle=$data;
//        shuffle($dataShuffle);
//        $dataShuffle = array_reverse($dataShuffle);

//        $boss = array_splice($dataShuffle,0,3);
        $player1 = array_splice($dataShuffle,0,20);
        $player2 = array_splice($dataShuffle,0,17);
// 牌型预设
        $type = '{
        "Dan" : 1,

        "Dui" : 10,
        "Dui3": 11,
        "Dui4": 12, 
        "Dui5": 13,   
        "Dui6": 14,   
        "Dui7": 15,    
        "Dui8": 16,  
        "Dui9": 17,  
        "Dui10": 18,

        "San" : 20,
        "San2" : 21,
        "San3" : 22,
        "San4" : 23,
        "San5" : 24,
        "San6" : 25,

        "SanDan" : 30,
        "SanDan2" : 31,
        "SanDan3" : 32, 
        "SanDan4" : 33, 
        "SanDan5" : 34,

        "SanDui" : 40,
        "SanDui2" : 41,
        "SanDui3" : 42, 
        "SanDui4" : 43,

        "Shun5" : 50,
        "Shun6" : 51,
        "Shun7" : 52,
        "Shun8" : 53,
        "Shun9" : 54,
        "Shun10" : 55,
        "Shun11" : 56,
        "Shun12" : 57,

        "SiDan":60,
        "SiDui":70,

        "ZhaDan":80,
        "HuoJian": 90
    }';

        $typeArr = json_decode($type,true);
        $cardPointRe = array_reverse($cardPoint);
        $cardPointAll = array_column($data,'point');    // 54张牌

        // 单牌
        $typeArr['Dan'] = array_merge(['G','g'],$cardPointRe);

        // 对子

        // 三带一*2 三从4～A共11种，碎牌剩下里面挑
//        $typeArr['SanDan2'] = [];
//        for ($i=1;$i<=11;$i++){
//            $cardPointAllTemp = $cardPointAll;
//
//            $left = $cardPointRe[$i].$cardPointRe[$i].$cardPointRe[$i]
//                .$cardPointRe[$i+1].$cardPointRe[$i+1].$cardPointRe[$i+1];
//            $key=array_search($cardPointRe[$i] ,$cardPointAllTemp);
//            array_splice($cardPointAllTemp,$key,3);
//            $key=array_search($cardPointRe[$i+1] ,$cardPointAllTemp);
//            array_splice($cardPointAllTemp,$key,3);
//
//            for($j=0;$j<sizeof($cardPointAllTemp);$j++){
//                for($k=$j+1;$k<sizeof($cardPointAllTemp);$k++){
//                    $right = $cardPointAllTemp[$j].$cardPointAllTemp[$k];
//                    array_push(
//                        $typeArr['SanDan2'],
//                        $left.$right
//                    );
//                }
//            }
//
//        }
//        $typeArr['SanDan2'] = array_values(array_unique($typeArr['SanDan2']));

//        $typeArr['SanDan3'] = [];
//        for ($i=1;$i<=10;$i++){
//            $cardPointAllTemp = $cardPointAll;
//
//            $left = $cardPointRe[$i].$cardPointRe[$i].$cardPointRe[$i]
//                .$cardPointRe[$i+1].$cardPointRe[$i+1].$cardPointRe[$i+1]
//                .$cardPointRe[$i+2].$cardPointRe[$i+2].$cardPointRe[$i+2];
//            $key=array_search($cardPointRe[$i] ,$cardPointAllTemp);
//            array_splice($cardPointAllTemp,$key,3);
//            $key=array_search($cardPointRe[$i+1] ,$cardPointAllTemp);
//            array_splice($cardPointAllTemp,$key,3);
//            $key=array_search($cardPointRe[$i+2] ,$cardPointAllTemp);
//            array_splice($cardPointAllTemp,$key,3);
//
//            for($j=0;$j<sizeof($cardPointAllTemp);$j++){
//                for($k=$j+1;$k<sizeof($cardPointAllTemp);$k++){
//                    for($l=$k+1;$l<sizeof($cardPointAllTemp);$l++){
//                        $right = $cardPointAllTemp[$j].$cardPointAllTemp[$k].$cardPointAllTemp[$l];
//                        array_push(
//                            $typeArr['SanDan3'],
//                            $left.$right
//                        );
//                    }
//                }
//            }
//
//        }
//        $typeArr['SanDan3'] = array_values(array_unique($typeArr['SanDan3']));
//
//
//        $typeArr['SanDan4'] = [];
//        for ($i=1;$i<=9;$i++){
//            $cardPointAllTemp = $cardPointAll;
//
//            $left = $cardPointRe[$i].$cardPointRe[$i].$cardPointRe[$i]
//                .$cardPointRe[$i+1].$cardPointRe[$i+1].$cardPointRe[$i+1]
//                .$cardPointRe[$i+2].$cardPointRe[$i+2].$cardPointRe[$i+2]
//                .$cardPointRe[$i+3].$cardPointRe[$i+3].$cardPointRe[$i+3];
//            $key=array_search($cardPointRe[$i] ,$cardPointAllTemp);
//            array_splice($cardPointAllTemp,$key,3);
//            $key=array_search($cardPointRe[$i+1] ,$cardPointAllTemp);
//            array_splice($cardPointAllTemp,$key,3);
//            $key=array_search($cardPointRe[$i+2] ,$cardPointAllTemp);
//            array_splice($cardPointAllTemp,$key,3);
//            $key=array_search($cardPointRe[$i+3] ,$cardPointAllTemp);
//            array_splice($cardPointAllTemp,$key,3);
//
//            for($j=0;$j<sizeof($cardPointAllTemp);$j++){
//                for($k=$j+1;$k<sizeof($cardPointAllTemp);$k++){
//                    for($l=$k+1;$l<sizeof($cardPointAllTemp);$l++){
//                        for($m=$l+1;$m<sizeof($cardPointAllTemp);$m++){
//                            $right = $cardPointAllTemp[$j].$cardPointAllTemp[$k].$cardPointAllTemp[$l].$cardPointAllTemp[$m];
//                            array_push(
//                                $typeArr['SanDan4'],
//                                $left.$right
//                            );
//                        }
//                    }
//                }
//            }
//
//        }
//        $typeArr['SanDan4'] = array_values(array_unique($typeArr['SanDan4']));
//
//
//        $typeArr['SanDan5'] = [];
//        for ($i=1;$i<=8;$i++){
//            $cardPointAllTemp = $cardPointAll;
//
//            $left = $cardPointRe[$i].$cardPointRe[$i].$cardPointRe[$i]
//                .$cardPointRe[$i+1].$cardPointRe[$i+1].$cardPointRe[$i+1]
//                .$cardPointRe[$i+2].$cardPointRe[$i+2].$cardPointRe[$i+2]
//                .$cardPointRe[$i+3].$cardPointRe[$i+3].$cardPointRe[$i+3]
//                .$cardPointRe[$i+4].$cardPointRe[$i+4].$cardPointRe[$i+4];
//            $key=array_search($cardPointRe[$i] ,$cardPointAllTemp);
//            array_splice($cardPointAllTemp,$key,3);
//            $key=array_search($cardPointRe[$i+1] ,$cardPointAllTemp);
//            array_splice($cardPointAllTemp,$key,3);
//            $key=array_search($cardPointRe[$i+2] ,$cardPointAllTemp);
//            array_splice($cardPointAllTemp,$key,3);
//            $key=array_search($cardPointRe[$i+3] ,$cardPointAllTemp);
//            array_splice($cardPointAllTemp,$key,3);
//            $key=array_search($cardPointRe[$i+4] ,$cardPointAllTemp);
//            array_splice($cardPointAllTemp,$key,3);
//
//            for($j=0;$j<sizeof($cardPointAllTemp);$j++){
//                for($k=$j+1;$k<sizeof($cardPointAllTemp);$k++){
//                    for($l=$k+1;$l<sizeof($cardPointAllTemp);$l++){
//                        for($m=$l+1;$m<sizeof($cardPointAllTemp);$m++){
//                            for($n=$m+1;$n<sizeof($cardPointAllTemp);$n++){
//                                $right = $cardPointAllTemp[$j].$cardPointAllTemp[$k].$cardPointAllTemp[$l].$cardPointAllTemp[$m].$cardPointAllTemp[$n];
//                                array_push(
//                                    $typeArr['SanDan5'],
//                                    $left.$right
//                                );
//                            }
//                        }
//                    }
//                }
//            }
//
//        }
//        $typeArr['SanDan5'] = array_values(array_unique($typeArr['SanDan5']));

//$this->writeFile(print_r([$typeArr],true));

        return [
//            '$data'=>$data,
//            '$dataShuffle'=>$dataShuffle,
//            '$boss'=>$boss,
            '$player1'=>$player1,
            '$player2'=>$player2,
            '$player3'=>$dataShuffle,
            'pokeType'=>$typeArr
        ];
    }

    public function writeFile($content){
        $filename = 'testLog';
        $fp = @fopen($filename, "a");
        @flock($fp, LOCK_EX);
        @fwrite($fp,  $content . PHP_EOL . PHP_EOL);
        @flock($fp, LOCK_UN);
        @fclose($fp);
    }


}