<?php
namespace backend\models;

class Token{
    protected static $secretKey = 'dasfjdlkfgjmdkmslzdkmkelsdasddfFSDFSDkdfjpasdDFASDAdfsjt';



    public static function encode(int $id){

        $num = date('d');
        $idStr = base64_encode($id);
        $substr = base64_encode(substr_replace(self::$secretKey,$idStr,$num,'1'));
        return $substr;
    }

    public static function decode(String $string){
        $num = date('d');
        $newStr = base64_decode($string);
        $lenth =  strlen($newStr) - strlen(self::$secretKey) + 1;
        $idBase = substr($newStr,$num,$lenth);
        $id = base64_decode($idBase);
        return $id;
    }



}