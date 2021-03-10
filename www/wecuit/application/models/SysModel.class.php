<?php

// application/models/SysModel.class.php
class SysModel extends Model
{

    // test
    public function getUsers()
    {
        $sql = "select * from $this->table";
        $users = $this->db->getAll($sql);
        $this->db->close();
        return $users;
    }

    public function test(){
        
    }

}
