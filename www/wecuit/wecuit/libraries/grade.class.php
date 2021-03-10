<?php
class Grade
{

    /**
     * 比较成绩变化情况
     * 
     * @return add 新增的成绩
     */
    public static function handleGrade($oldGrade, $newGrade)
    {
        $oldGrade = json_decode($oldGrade['grade'], true);
        if (empty($oldGrade)) $oldGrade = [];
        $add = [];
        foreach ($newGrade as $key1 => $value1) {
            foreach ($value1 as $key2 => $value2) {
                if (!isset($oldGrade[$key1][$key2])) {
                    $oldGrade[$key1][$key2] = $add[$key1][$key2] = $value2;
                }
            }
        }
        return $add;
    }

}
