<?php
class CollegeModel extends Model{
    // 取辅导员列表
    public function getCounselorList($college)
    {
        $this->db->escape($college);
        $sql = "SELECT info_id as cid, title as name FROM `cuit_term_relationships`,`cuit_info`,`cuit_terms`,`cuit_colleges` 
        WHERE  `cuit_colleges`.slug='{$college}' AND `cuit_terms`.slug='counselor' AND
                `cuit_term_relationships`.object_id=`cuit_info`.info_id AND
                `cuit_term_relationships`.term_id=`cuit_terms`.term_id AND
                `cuit_term_relationships`.college_id=`cuit_colleges`.college_id";
        $list = $this->db->getAll($sql);
        $this->closeDB();
        // print_r($list);
        return $list;
    }

    public function getCounselorInfo($id)
    {
        $this->db->escape($id);
        $sql = "SELECT `content`, `type` FROM `cuit_info` WHERE `info_id`='{$id}'";
        $row = $this->db->getRow($sql);
        $this->closeDB();
        // print_r($row);
        return $row;
    }

    public function getIntroduce($college)
    {
        $this->db->escape($college);
        $sql = "SELECT title, content, type FROM `cuit_term_relationships`,`cuit_info`,`cuit_terms`,`cuit_colleges` 
        WHERE  `cuit_colleges`.slug='{$college}' AND `cuit_terms`.slug='college_introduce' AND
                `cuit_term_relationships`.object_id=`cuit_info`.info_id AND
                `cuit_term_relationships`.term_id=`cuit_terms`.term_id AND
                `cuit_term_relationships`.college_id=`cuit_colleges`.college_id";
        $list = $this->db->getRow($sql);
        $this->closeDB();
        // print_r($list);
        return $list;
    }
}