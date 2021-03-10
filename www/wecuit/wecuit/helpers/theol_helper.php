<?php

function courseListHandle($html){
    $html = preg_replace("/<script[\s\S]*?<\/script>/i", "", $html);
    $html = str2UTF8($html);
    $html = str_replace("gbk", "UTF-8", $html);
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $dom->normalize();

    $xpath = new DOMXPath($dom);
    $ele = $xpath->query('//*[@id="table2"]/tr');
    
    $list = array();
    for($i=1; $i<$ele->length; $i++){
        $item = $ele->item($i);
        preg_match("/courseId=(\d+)/i", $item->childNodes->item(0)->childNodes->item(1)->attributes->item(1)->textContent, $courseId);
        
        $e = array(
            'course' => trim($item->childNodes->item(0)->textContent), // 课程名称
            'college' => trim($item->childNodes->item(2)->textContent), // 开课院系
            'teacher' => trim($item->childNodes->item(4)->textContent), // 主讲教师
            'courseId' => $courseId[1]
        );
        $list[] = $e;
    }

    return $list;
}

function folderListHandle(string $html)
{
    $html = preg_replace("/<script[\s\S]*?<\/script>/i", "", $html);
    $html = str2UTF8($html);
    $html = str_replace("gbk", "UTF-8", $html);
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $dom->normalize();

    $xpath = new DOMXPath($dom);
    $items = $xpath->query('/html/body/div/form[2]/table/tr/td/..');
    $dir = array();

    for ($i=0; $i < $items->length; $i++) { 
        $item = $items->item($i);
        $temp = array();
        if(0 == $item->attributes->length){
            // 目录
            $temp['type'] = 'folder';
            $temp['text'] = $item->firstChild->lastChild->textContent;
            $href = $item->firstChild->lastChild->attributes->item(0)->value;
            preg_match("/folderid=(\d+)&lid=(\d+)/i", $href, $ids);
            $temp['id'] = $ids[1];
            $temp['lid'] = $ids[2];
            $dir['folder'][] = $temp;
            
        }else{
            // 文件
            $temp['type'] = 'file';
            $type_dict = array(
                'word' => 'doc',
                'powerpoint' => 'ppt'
            );
            // tr
    
            $img = $item->childNodes->item(0)->childNodes->item(0)->attributes->item(1)->value;
            preg_match("/\/(\w+)\./", $img, $type);
            $type = $type[1];
            $temp['suffix'] = isset($type_dict[$type])?$type_dict[$type]:$type;
            
            $a = $item->childNodes->item(0)->childNodes->item(2);
            $temp['text'] = $a->textContent;
            $href = $a->attributes->item(0)->value;
            preg_match("/fileid=(\d+)&resid=(\d+)&lid=(\d+)/i", $href, $ids);
            $temp['id'] = $ids[1];
            $temp['resId'] = $ids[2];
            $temp['lid'] = $ids[3];
            $temp['view'] = $item->childNodes->item(2)->firstChild->textContent;
            $temp['download'] = $item->childNodes->item(4)->firstChild->textContent;
            $dir['file'][] = $temp;
    
        }
    }
    return $dir;
}
function xml2json($xml){
    
    $ret = json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA));
    print_r($ret);
}

function getFileType(string $html){
    $html = preg_replace("/<script[\s\S]*?<\/script>/i", "", $html);
    $html = str2UTF8($html);
    $html = str_replace("gbk", "UTF-8", $html);
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $dom->normalize();

    $xpath = new DOMXPath($dom);
    $items = $xpath->query('/html/body/div/table/tr[12]/td');
    $contentType = trim($items->item(0)->textContent);
    if(false !== strpos($contentType, "application/")){
        $contentType = substr($contentType, strpos($contentType, "application/"));
        return $contentType;
    }else{
        return false;
    }
}

function dirTreeHandle($json){
    $newJson = array(
        'id' => $json['@attributes']['id'],
        'text' => $json['content']['name'],
    );
    if(isset($json['@attributes']['state']) && 'open' == $json['@attributes']['state'])
        $newJson['open'] = true;
    if(isset($json['item']) && isset($json['item'][0])){
        $itemLen = count($json['item']);
        for ($i=0; $i < $itemLen; $i++) { 
            $newJson['childMenus'][$i] = dirTreeHandle($json['item'][$i]);
        }
    }else if(isset($json['item'])){
        $newJson['childMenus'][] = dirTreeHandle($json['item']);
    }
    return $newJson;
}

function str2UTF8($str)
{
    $encoding = mb_detect_encoding($str, array('ASCII', 'UTF-8', 'GBK', 'GB2312', 'BIG5'));
    if ($encoding != 'UTF-8') {
        return mb_convert_encoding($str, 'UTF-8', $encoding);
    }
    return $str;
}