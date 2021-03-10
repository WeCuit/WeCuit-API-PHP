<?php

// application/models/UserModel.class.php
class WwwModel extends Model
{

    public function __construct()
    {
        // echo "WwwModel\r\n";
    }

    public function getNews($id = null)
    {
        $id = $id ? $id : 1;
        if(file_exists(CACHE_PATH . "news/{$id}.json"))
            include CACHE_PATH . "news/{$id}.json";
        else
            echo "{}";
    }

}