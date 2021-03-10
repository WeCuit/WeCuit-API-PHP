<?php
class SearchModel extends Model
{
    function getInfo($key)
    {
        $this->db->escape($key);
        $sql = "SELECT * FROM cuit_info WHERE title LIKE '%{$key}%' OR content LIKE '%{$key}%'";
        
        $q = $this->db->query($sql);
        if(!$q)throw new cuitException("MySQL Query Error");
        
        $ret = array();
        while($r = $this->db->fetch($q))
        {
            if('html' == $r['type'])
            {
                $r['content'] = strip_tags($r['content']);
                if(false !== ($pos = mb_strpos($r['content'], PARAM['keyword'])))
                {
                    $start = $pos - 3;
                    if($start < 0)$start = 0;
                    $r['content'] = mb_substr($r['content'], $start, strlen($key) + 2);
                    $r['content'] = str_replace($key, "<span style='color:blue'>{$key}</span>", $r['content']);
                
                    // print_r($r);
                }
                $r['api'] = "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}/api/Search/getDetail?id=";
            }else if('img' == $r['type'])
            {
                $r['content'] = json_decode($r['content'], true);
            }
            $ret[] = $r;
        }
        $this->db->close();
        echo json_encode(
            array(
                'status' => 2000,
                'errorCode' => 2000,
                'list' => $ret
            )
        );
    }
    public function getDetail($id)
    {
        $this->db->escape($id);
        $sql = "SELECT * FROM cuit_info WHERE id={$id}";
        
        $r = $this->db->getRow($sql);
        if(!$r)throw new cuitException("MySQL Query Error");
        $this->db->close();
        echo $r['content'];
    }
}