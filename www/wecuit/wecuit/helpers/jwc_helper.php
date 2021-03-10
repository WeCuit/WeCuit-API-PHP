<?php
function LAB_ListHtml2json($html){
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $dom->normalize();

    $xpath = new DOMXPath($dom);

    // form
    // /html/body/div[1]/div/table/tbody/tr/td/form
    $form = $xpath->query('/html/body/div[1]/div/table/tr/td/form/input[@type="text"]|/html/body/div[1]/div/table/tr/td/form/select');
    $formInfo = array();
    for ($i=0; $i < $form->length; $i++) {
        $item = $form->item($i);
        if("input" == $item->tagName){
            $name = $item->attributes->item(1)->nodeValue;
            $value = $item->attributes->item(4)->nodeValue;
            $formInfo[$name] = $value;
        }else if("select" == $item->tagName)
        {
            $name = $item->attributes->item(0)->nodeValue;
            $formInfo[$name] = array();
            $child = $item->childNodes;
            for ($j=0; $j < $child->length; $j++) { 
                $option = $child->item($j);
                $option_text = $option->nodeValue;
                $attribute_1 = $option->attributes->item(0);
                if("selected" == $attribute_1->name)
                {
                    // 选中项
                    $option_value = $option->attributes->item(1)->nodeValue;
                    $formInfo["{$name}_index"] = $j;
                }else{
                    $option_value = $attribute_1->nodeValue;
                }
                $formInfo[$name][] = array(
                    'id' => $option_value,
                    'text' => $option_text
                );
            }
        }
    }

    // list
    $items = $xpath->query("/html/body/div[2]/table/tr/td/table/tbody/tr");
    if(false !== strpos($items->item(0)->textContent, "没有符合查询条件的记录")){
        return array(
            'form' => $formInfo,
            'list' => []
        );
    }
    $list = array();
    for ($i=0; $i < $items->length; $i++) {
        $temp = array();
        $tr = $items->item($i);

        // 院系
        $yx = $tr->firstChild->firstChild;
        $yx_link = $yx->attributes->item(0)->value;
        $yx_link = str2UTF8(urldecode(substr($yx_link, 10)));

        $temp["name"] = $yx->textContent;
        $temp["link"] = $yx_link;

        // 校区|地点
        $places = $xpath->query("table/tr", $tr->lastChild);
        for ($j=0; $j < $places->length; $j++) { 
            $place = $places->item($j);
            $xq = $place->firstChild->firstChild;
            $xq_link = $xq->attributes->item(0)->value;
            $xq_link = str2UTF8(urldecode(substr($xq_link, 10)));

            $place_temp = array(
                "name" => $xq->textContent,
                "link" => $xq_link
            );
            $labs = $xpath->query("table/tr/td[@valign=\"middle\"]/font/a", $place->lastChild);
            for ($k=0; $k < $labs->length; $k++) { 
                $lab_a = $labs->item($k);
                
                $lab_link = $lab_a->attributes->item(0)->value;
                $lab_link = str2UTF8(urldecode(substr($lab_link, 10)));
                $lab_style = $lab_a->firstChild->attributes ? $lab_a->firstChild->attributes->item(0)->value: '';
                
                $place_temp['lab'][] = array(
                    "name" => $lab_a->textContent,
                    "link" => $lab_link,
                    "style" => $lab_style
                );
            }
            $temp['place'][] = $place_temp;
        }
        
        $list[] = $temp;
    }
    return array(
        'form' => $formInfo,
        'list' => $list
    );
}

function LAB_DetailHtml2json($html){
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $dom->normalize();

    $xpath = new DOMXPath($dom);
    
    // form
    //                     /html/body/div[1]/div/table/tr/td/form/input[3]
    $form = $xpath->query('/html/body/div[1]/div/table/tr/td/form/input[@type="text"]|/html/body/div[1]/div/table/tr/td/form/select');
    $formInfo = array();
    for ($i=0; $i < $form->length; $i++) {
        $item = $form->item($i);
        if("input" == $item->tagName){
            $name = $item->attributes->item(1)->nodeValue;
            $value = $item->attributes->item(4)->nodeValue;
            $formInfo[$name] = $value;
        }else if("select" == $item->tagName)
        {
            $name = $item->attributes->item(0)->nodeValue;
            $formInfo[$name] = array();
            $child = $item->childNodes;
            for ($j=0; $j < $child->length; $j++) { 
                $option = $child->item($j);
                $option_text = $option->nodeValue;
                $attribute_1 = $option->attributes->item(0);
                if("selected" == $attribute_1->name)
                {
                    // 选中项
                    $option_value = $option->attributes->item(1)->nodeValue;
                    $formInfo["{$name}_index"] = $j;
                }else{
                    $option_value = $attribute_1->nodeValue;
                }
                $formInfo[$name][] = array(
                    'id' => $option_value,
                    'text' => $option_text
                );
            }
        }
    }

    // detail
    $trs = $xpath->query('//*[@id="wjTA"]/tbody/tr[@valign="bottom"]');

    $list = array();
    for ($i=0; $i < $trs->length; $i++) { 
        $tr = $trs->item($i);
        $tds = $tr->childNodes;

        $name = $tds->item(0)->textContent;
        for ($j=2; $j < $tds->length; $j+=2) { 
            $td = $tds->item($j);
            $plan = $td->childNodes;

            // 星期 $j / 2 , 节次 $name
            $data = LAB_DetailHtml2json_ps($plan);
            if($data)
            $list[$j / 2 - 1][$name] = $data; 
        }
    }
    return array(
        'form' => $formInfo,
        'list' => $list
    );
}
function LAB_DetailHtml2json_ps(DOMNodeList $plan){
    $temp = [];
    if(1 === $plan->length && 0 == strlen(trim($plan->item(0)->textContent, " \t\n\r\0\x0B\r\n　")))return null;

    // p标签
    for ($k=0; $k < $plan->length; $k++) { 
        $item = $plan->item($k);
        if(0 < strlen(trim($item->textContent)))
        $temp[] = trim($item->textContent);
    }
    return $temp;
}
function str2GBK($str)
{
    $encoding = mb_detect_encoding($str, array('ASCII', 'UTF-8', 'GBK', 'GB2312', 'BIG5'));
    if ($encoding != 'GBK') {
        return mb_convert_encoding($str, 'GBK', $encoding);
    }
    return $str;
}
function str2UTF8($str)
{
    // 编码处理
    $encoding = mb_detect_encoding($str, array("ASCII", 'UTF-8', "GB2312", "GBK", 'BIG5'));
    // 如果字符串的编码格式不为UTF_8就转换编码格式
    if ($encoding != 'UTF-8') {
        return mb_convert_encoding($str, 'UTF-8', $encoding);
    }
    return $str;
}
