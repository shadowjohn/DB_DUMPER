<?php
  function array_htmlspecialchars(&$input)
  {
      if (is_array($input))
      {
          foreach ($input as $key => $value)
          {
              if (is_array($value)) $input[$key] = array_htmlspecialchars($value);
              else $input[$key] = htmlspecialchars($value);
          }
          return $input;
      }
      return htmlspecialchars($input);
  }
  
  function array_htmlspecialchars_decode(&$input)
  {
      if (is_array($input))
      {
          foreach ($input as $key => $value)
          {
              if (is_array($value)) $input[$key] = array_htmlspecialchars_decode($value);
              else $input[$key] = htmlspecialchars_decode($value);
          }
          return $input;
      }
      return htmlspecialchars_decode($input);
  }
  function getGET_POST($inputs,$mode)
  {
    $mode=strtoupper(trim($mode));
    $data=$GLOBALS['_'.$mode];
        
    $data=array_htmlspecialchars($data);
    array_walk_recursive($data, "trim");
    
    $keys=array_keys($data);
    $filters = explode(',',$inputs);
    foreach($keys as $k)
    {
      if(!in_array($k,$filters))
      {
        unset($data[$k]);
      }
    }    
    return $data;
  } 
 
  function selectSQL($SQL)
  {
    global $pdo;              
    $res=$pdo->query($SQL) or die("查詢失敗:{$SQL}");    
    return pdo_resulttoassoc($res);
  }
  function selectSQL_SAFE($SQL,$data_arr)
  {
    global $pdo;   
    //找有幾個問號
    $questions = word_appear_times('?',$SQL);
    $max_i=count($data_arr);
    if($questions!=$max_i)
    {
      echo "查詢條件無法匹配...:{$SQL} 
      <br>Questions:{$questions}
      <br>Arrays   :{$max_i}";
      exit();
    }
    $q = $pdo->prepare($SQL);
    for($i=0;$i<$max_i;$i++)
    {
      $q->bindParam(($i+1), $data_arr[$i]);
    }
    $q->execute() or die("查詢失敗:{$SQL}");   
    
    return pdo_resulttoassoc($q);
  }    

  function jsAddSlashes($str) {
    $pattern = array(
        "/\\\\/"  , "/\n/"    , "/\r/"    , "/\"/"    ,
        "/\'/"    , "/&/"     , "/</"     , "/>/"
    );
    $replace = array(
        "\\\\\\\\", "\\n"     , "\\r"     , "\\\""    ,
        "\\'"     , "\\x26"   , "\\x3C"   , "\\x3E"
    );
    return preg_replace($pattern, $replace, $str);
  }

  function pre_print_r($values)
  {    
    echo "<pre>";
    print_r($values);
    echo "</pre>";
  }       
  function alert($values)
  {
    ?>
    <script language="javascript">
      alert("<?=$values;?>");
    </script>
    <?
  }
  function pdo_resulttoassoc($res){     
    return $res->fetchAll(PDO::FETCH_ASSOC);    
  }  
  function subname($fname){  
    $m=explode(".",$fname);
    return end($m);
  } 
  function mainname($fname){
    $pathinfo=pathinfo($fname);
    return $pathinfo['filename'];           
  }
  /***************************************** 
  * 程式碼作者：umbrae 
  * 程式碼來源：http://tw.php.net/json_encode 
  * 程式碼說明：將JSON資料轉為可閱讀排版 
  ******************************************/  
  function json_format($json) {  
    $tab = "  ";  
    $new_json = "";  
    $indent_level = 0;  
    $in_string = false;  
    $json_obj = json_decode($json);  
    if(!$json_obj){  
        return false;  
    }  
    $json = json_encode($json_obj);  
    $len = strlen($json);  
    for($c = 0; $c < $len; $c++) {  
        $char = $json[$c];  
        switch($char) {  
            case '{':  
            case '[':  
                if(!$in_string) {  
                    $new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);  
                    $indent_level++;  
                } else {  
                    $new_json .= $char;  
                }  
            break;  
            case '}':  
            case ']':  
                if(!$in_string){  
                    $indent_level--;  
                    $new_json .= "\n" . str_repeat($tab, $indent_level) . $char;  
                } else {  
                    $new_json .= $char;  
                }  
            break;  
            case ',':  
                if(!$in_string){  
                    $new_json .= ",\n" . str_repeat($tab, $indent_level);  
                } else {  
                    $new_json .= $char;  
                }  
            break;  
            case ':':  
                if(!$in_string) {  
                    $new_json .= ": ";  
                } else {  
                    $new_json .= $char;  
                }  
            break;  
            case '"':  
                $in_string = !$in_string;  
            default:  
                $new_json .= $char;  
            break;  
        }  
    }  
    return $new_json;  
  }
  function print_table($ra,$fields='',$headers='',$classname='')
  {    
    $classname=($classname=='')?'':" class='{$classname}' ";
    if($fields==''||$fields=='*')
    {      

        $tmp="<table {$classname} border='1' cellspacing='0' cellpadding='0'>";
        $tmp.="<thead><tr>";
        foreach($ra[0] as $k=>$v)
        {
          $tmp.="<th>{$k}</th>";
        }
        $tmp.="</tr></thead>";
        $tmp.="<tbody>";
        for($i=0,$max_i=count($ra);$i<$max_i;$i++)
        {
          $tmp.="<tr>";
          foreach($ra[$i] as $k=>$v)
          {
            $tmp.="<td>{$v}</td>";
          }
          $tmp.="</tr>";
        }
        $tmp.="</tbody>";
        $tmp.="</table>";
        return $tmp;
    }
    else
    {
      $tmp="<table {$classname} border='1' cellspacing='0' cellpadding='0'>";
      $tmp.="<thead><tr>";
      foreach(explode(',',$headers) as $k=>$v)
      {
        $tmp.="<th>{$v}</th>";
      }
      $tmp.="</tr></thead>";
      $tmp.="<tbody>";
      $m_fields=explode(',',$fields);
      for($i=0,$max_i=count($ra);$i<$max_i;$i++)
      {
        $tmp.="<tr>";
        foreach($m_fields as $k)
        {
          $tmp.="<td>{$ra[$i][$k]}</td>";
        }
        $tmp.="</tr>";
      }
      $tmp.="</tbody>";
      $tmp.="</table>";
      return $tmp;
    }
  }
  function is_win()
  {
    return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');        
  }
  function word_appear_times($find_word,$input)
  {
    //找一個字串在另一個字串出現的次數
    $found_times=0;
    $len = strlen($find_word);
    for($i=0,$max_i=strlen($input)-$len;$i<=$max_i;$i++)
    {
      if(substr($input,$i,$len)==$find_word)
      {
        $found_times++;
      }
    }
    return $found_times;
  } 