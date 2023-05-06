<?php

namespace App\Consts;

use App\Entity\Video;

class Consts
{

    public static $categoryList = [
        "手描き" => 3,
        "人力" => 2,
        "MMD" => 1,
        "切り抜き" => 0,
        "その他" => 99
    ];
    /*
    * カテゴリ設定
    */
    public static function setCategoryByConsts(Video $newVideo)
    {
        $setCategoryList = [];

        foreach(self::$categoryList as $categoryName => $categoryNo){

            $inTitle = strstr($newVideo->getTitle(), $categoryName);
            $inDescription = strstr($newVideo->getDescription(), $categoryName);

            if($inTitle || $inDescription){
                $setCategoryList[] = $categoryNo;
            }
        }
        
        //どのカテゴリにも引っ掛からなかったらその他にしておく
        if($setCategoryList == []){
            $setCategoryList[] = self::$categoryList["その他"];
            
        }

        $newVideo->setCategory(json_encode(["category_list" => $setCategoryList]));

        return $newVideo;
    }


    /*
    * 参加メンバー設定
    */
    public static function setMemberByConsts(array $liverList, Video $newVideo)
    {
        $checkGetterStrList = [
            "getName",
            "getTwitterId",
            "getChannelId"
        ];

        $setMemberList = [];
        foreach($liverList as $liver){
            $inTitle = false;
            $inDescription = false;
            
            //名前のスペース抜きで最初に検索
            $nameNotInSpace = str_replace(" ", "", $liver->getName());
            $inTitle = strstr($newVideo->getTitle(), $nameNotInSpace);
            $inDescription = strstr($newVideo->getDescription(), $nameNotInSpace);
            if($inTitle || $inDescription){
                $setMemberList[] = (int)$liver->getId();
                break;
            }

            //DBの情報から検索
            foreach($checkGetterStrList as $getter){
                //""ならスキップ
                if($liver->$getter() ==  ""){
                    continue;
                }

                $inTitle = strstr($newVideo->getTitle(), $liver->$getter());
                $inDescription = strstr($newVideo->getDescription(), $liver->$getter());
                if($inTitle || $inDescription){
                    $setMemberList[] = (int)$liver->getId();
                    break;
                }
            }

        }

        $newVideo->setMember(json_encode(["member_list" => $setMemberList]));

        return $newVideo;
    }
    
}
