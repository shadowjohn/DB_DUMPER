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
 
  function updateSQL($table,$fields_data,$WHERE_SQL)
  {
    global $pdo;
    $datas=ARRAY();
    $question_marks=ARRAY();
    $m_mix_SQL=array();
    foreach($fields_data as $k=>$v)
    {
       array_push($datas,$v);
       array_push($question_marks,'?');
       array_push($m_mix_SQL,sprintf("`%s`=?",$k));
    }            
    $SQL=sprintf("
              UPDATE `{$table}` 
                  SET %s 
                WHERE 
                  %s",@implode(',',$m_mix_SQL),$WHERE_SQL); 
    $q = $pdo->prepare($SQL);
    for($i=0,$totals=count($question_marks);$i<$totals;$i++)
    {
         $q->bindParam(($i+1), $datas[$i]);
    }
    $q->execute();                      
  }
 
  function deleteSQL($table,$WHERE_SQL)
  {
    global $pdo;
    $SQL=sprintf("DELETE FROM `{$table}` WHERE %s",$WHERE_SQL);
    $pdo->query($SQL) or die("刪除 {$table} 失敗:{$SQL}");
  }
  function insertSQL($table,$fields_data)
  {
     global $pdo;
     $fields=ARRAY();
     $datas=ARRAY();
     $question_marks=ARRAY();
     foreach($fields_data as $k=>$v)
     {
        array_push($fields,$k);
        array_push($datas,$v);
        array_push($question_marks,'?');
     }
     $SQL = sprintf("
                INSERT INTO `{$table}`
                    (`%s`)
                    values
                    (%s)",
                    @implode("`,`",$fields),
                    @implode(",",$question_marks)
                  );
     $q = $pdo->prepare($SQL);
     for($i=0,$totals=count($question_marks);$i<$totals;$i++)
     {
          $q->bindParam(($i+1), $datas[$i]);
     }
     $q->execute(); 
     return $pdo->lastInsertId();      
  } 
  function insertSQLPDO($pdo,$table,$fields_data)
  {
     $fields=ARRAY();
     $datas=ARRAY();
     $question_marks=ARRAY();
     foreach($fields_data as $k=>$v)
     {
        array_push($fields,$k);
        array_push($datas,$v);
        array_push($question_marks,'?');
     }
     $SQL = sprintf("
                INSERT INTO `{$table}`
                    (`%s`)
                    values
                    (%s)",
                    @implode("`,`",$fields),
                    @implode(",",$question_marks)
                  );
     $q = $pdo->prepare($SQL);
     for($i=0,$totals=count($question_marks);$i<$totals;$i++)
     {
          $q->bindParam(($i+1), $datas[$i]);
     }
     $q->execute(); 
     return $pdo->lastInsertId();      
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
  function selectSQL_SAFE_KEY($SQL,$data_arra,$field_name)
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
    $ra = pdo_resulttoassoc($q);
    $output=ARRAY();
    for($i=0,$max_i=count($ra);$i<$max_i;$i++)
    {
      $output[$ra[$i][$field_name]] = $ra[$i];
    }
    return $output; 
  }       
  function selectSQL_KEY($SQL,$field_name)
  {
    global $pdo;   
    $res=$pdo->query($SQL) or die("查詢失敗:{$SQL}");
    $ra = pdo_resulttoassoc($res);
    $output=ARRAY();
    for($i=0,$max_i=count($ra);$i<$max_i;$i++)
    {
      $output[$ra[$i][$field_name]] = $ra[$i];
    }
    return $output; 
  }     
  function fb_date($datetime)
  {
    //類似 facebook的時間轉換方式
    //傳入日期　格式如 2011-01-19 04:12:12 
    //就會回傳 facebook 的幾秒、幾分鐘、幾小時的那種
    $week_array=array('星期一','星期二','星期三','星期四','星期五','星期六','星期日');
    $timestamp=strtotime($datetime);
    $distance=(time()-$timestamp);
    /*echo time();
    echo "<br>";
    echo $timestamp;
    echo "<br>";  
    echo $distance;
    echo "<br>";*/
    if($distance<=59)
    {
      return sprintf("%d %s",$distance,__("秒前")); 
    }
    else if($distance>=60 && $distance<59*60)
    {
      return sprintf("%d %s",floor($distance/60),__("分鐘前"));
    }
    else if($distance>=60*60 && $distance<60*60*24)
    {      
      return sprintf("%d %s",floor($distance/60/60),__("小時前"));
    }
    else if($distance>=60*60*24 && $distance<59*60*24*7)
    {      
      return sprintf("%s %s",__($week_array[date('N',$timestamp)]),date('H:i',$timestamp));
    }
    else
    {      
      return sprintf("%s",date("Y/m/d H:i",$timestamp));
    }
  }
  function curl_file_get_contents($url,$posts){    
    if($posts!='')
    {
      $ch = curl_init();
      curl_setopt($ch,CURLOPT_URL,$url);
      curl_setopt($ch,CURLOPT_POST, 1);
      curl_setopt($ch,CURLOPT_POSTFIELDS,$posts);
      curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
      curl_setopt($ch,CURLOPT_REFERER,$url);
      curl_setopt($ch,CURLOPT_FOLLOWLOCATION, 1); 
      curl_setopt($ch,CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)');
      curl_setopt($ch,CURLOPT_TIMEOUT_MS, 1200);
      $data = curl_exec($ch);          
    }
    else
    {            
      $ch = curl_init($url);
      ob_start();
      curl_exec($ch);
      $data=ob_get_contents();
      ob_end_clean();                
    }                        
    curl_close($ch);    
    return $data;
  }  
  /*function curl_file_get_contents($durl){
     $ch = curl_init();
     curl_setopt($ch, CURLOPT_URL, $durl);
     curl_setopt($ch, CURLOPT_TIMEOUT, 5);
     curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)');
     curl_setopt($ch, CURLOPT_REFERER,_REFERER_);
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     $r = curl_exec($ch);
     curl_close($ch);
     return $r;
  }*/
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

  function deslashes(&$s)
  {    
    if(is_array($s)){
        foreach($s as $k=>$v){
        deslashes($s[$k]);        
      }    
    }
    elseif(is_string($s)){
        $s=stripslashes($s);    
    }
  }
  function user_agent(){
    return trim($_SERVER['HTTP_USER_AGENT']);
  }
  function ip(){
      $a=array();    
    if(!empty($_SERVER['REMOTE_ADDR'])){
        $a[]=$_SERVER['REMOTE_ADDR'];    
    }
       if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){        
      $a[]=preg_replace('[^A-Z0-9.]','',$_SERVER['HTTP_X_FORWARDED_FOR']);    
    }
    return implode('-',$a);
  }
  function valid_email($s){
      return preg_match('/^[^@]+@([a-z0-9]+\\.)+[a-z0-9]+$/i',$s);
  }        
  function getBK_NAME($id)
  {
    return pdo_get_field_from_id('board_kind',$id,'cname');
  }
  function getBK_EngNAME($id)
  {
    return pdo_get_field_from_id('board_kind',$id,'ename');
  }
  function getBK_Content($id)
  {
    return pdo_get_field_from_id('board_kind',$id,'content');
  }
  //將內文nl2br後的script裡的<br />去除
  function nl2br_script_br($string)
  {  
    // remove any carriage returns (mysql)
    $string = str_replace("\r", '', $string);
    // replace any newlines that aren't preceded by a > with a <br />
    $string = preg_replace('/(?<!>)\n/', "<br />\n", $string);
    return $string;
  }
  function englishMonth($input)
  {
    $arrayMonth=array('','January','February','March','April','May','June','July','August','September','October','November','December');
    return $arrayMonth[(int)$input];
  }
  function chineseMonth($input)
  {
    $arrayMonth=array('','一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月');
    return $arrayMonth[(int)$input];
  }
  function getNickName($USER_ID){
    $SQL="
                    SELECT 
                      `nickname` FROM `baccount` 
                    WHERE 
                      1=1
                      AND `USER_ID`='{$USER_ID}' LIMIT 0,1";   
    $ra=selectSQL($SQL);
    if(count($ra)!=0)
    {
      return $ra[0]['nickname'];
    }
    else
    {
      return $USER_ID;
    }
  } 
  
  function maketree($array,$id,$rid,$cut_title,$link=null,$full_title=null,$style='normal',$div='tree_a',$start_rid='0')
  {
    //需跟dtree一起使用
    //傳入二維array
    //指定id (0)
    //指定rid (1)
    //指定title (2)
    //指定link
    //配給style
    //div write out
    //起始值變配，預設為-1，dtree已被我改成0
    $output="";
    $output.="<div class='dtree' id=".$div." name=".$div.">\n";
    switch($style)
    {    
      case 'normal':
        $output.="<center><a href='javascript: ".$div.".openAll();'>全部展開</a> | <a href='javascript: ".$div.".closeAll();'>全部關掉</a></center>";
        $output.="<script type='text/javascript'>";
        $output.="var ".$div." = new dTree('".$div."');";
        $counter=count($array);
        for($i=0;$i<$counter;$i++)
        {
          if($i==0)
          {
            $array[$i][$rid]=$start_rid;
          }          
          $output.=$div.".add(".$array[$i][$id].",".$array[$i][$rid].",'".$array[$i][$cut_title]."'";
          if(trim($array[$i][$link])!=''||trim($array[$i][$link])!=null) //超連結
          {
           $output.=",'".$array[$i][$link]."'";
          }
          else
          {
            $output.=",null";
          }
          if(trim($array[$i][$full_title])!=''||trim($array[$i][$full_title])!=null) 
          {
           $output.=",'".$array[$i][$full_title]."'";
          }          
          else
          {
            $output.=",null";
          }
          $output.=");";
        }    
        break;
    }  
    $output.="document.getElementById('".$div."').innerHTML=$div;";     
    $output.="</script>";
    $output.="</div>";      
    return $output;
  }
  function resulttoarray($result){  //把sql查出來的傳，放入array中，回傳為array    
    $got = ARRAY();
    if(mysql_num_rows($result) == 0)
      return $got;
    mysql_data_seek($result, 0);  
    while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
      array_push($got, $row);
    }  
    return $got;

  }
  function resulttoarray_adodb($result){  //把sql查出來的傳，放入array中，回傳為array    
    $got = ARRAY(); 
    global $ADODB_FETCH_MODE;     
    $tmp=$ADODB_FETCH_MODE;
    $ADODB_FETCH_MODE= ADODB_FETCH_NUM;     
 
    if($result->FieldCount() == 0)
      return $got;
        
    $k=0;

    while ($ar = $result->FetchRow() )
    {

      // 秀出所有欄位，$FieldCount() 會傳回欄位總數
        /*for ($i=0, $max=$result->FieldCount(); $i < $max; $i++)
      {
          //array_push($got, $result->fields[$i]);
          //$got[$k][$i]=$result->fields[$i];
          f
          echo "test";
        }
        // 移至下一筆記錄
      $k++;*/
      static $kk=0;
      foreach($ar as $index=>$value)
      {
        $got[$k][$kk]=$value;
        $kk++;      
      }                  
      //$result->MoveNext();
        $k++;
    }
    $ADODB_FETCH_MODE=$tmp;
    return $got;
    
  }  
  function showArrayStyleT($array,$mode,$title){  //陣列，模式（table or dot)，標題，傳入陣列，使用模式，標題，標題用  ,  來分格
    switch($mode){
      case "dot":
          $temp="";
          $titlearray=explode(',',$title);
          $nums_titlearray=count($titlearray);
          for($i=0;$i<$nums_titlearray;$i++)
          {
            $temp.=$titlearray[$i].",";
          }
          if(strlen($temp)!=0)
          {
            $temp=substr($temp,0,strlen($temp)-1);
          }
          echo $temp."<br>";
          $temp="";
          $nums_array=count($array);
          for($i=0;$i<$nums_array;$i++)
          {
        $temp='';
            $nums_array_i=count($array[$i]);
            for($j=0;$j<$nums_array_i;$j++)
            {
              $temp.=$array[$i][$j].",";
            }
            if(strlen($temp)!=0)
            {
              $temp=substr($temp,0,strlen($temp)-1);
            }
            echo $temp."<br>";
          }
          break;
      case "table":
          $temp="";
          $titlearray=explode(',',$title);
          echo "<table border=1><tr>";
          $nums_titlearray=count($titlearray);
          for($i=0;$i<$nums_titlearray;$i++)
          {
            echo "<td>".$titlearray[$i]."</td>";
          }
          echo "</tr>";
          $nums_array=count($array);
          for($i=0;$i<$nums_array;$i++){
            echo "<tr>";
            $nums_array_i=count($array[$i]);
            for($j=0;$j<$nums_array_i;$j++)
            {
              echo "<td>".$array[$i][$j]."</td>";
            }
            echo "</tr>";
          }
          echo "</tr></table>";
          break;
      case "table_no_padding":
          $temp="";
          $titlearray=explode(',',$title);
          echo "<table border=1 cellspacing=0 cellpadding=0 style='width:950px;overflow:auto;'><tr>";
          $nums_titlearray=count($titlearray);
          for($i=0;$i<$nums_titlearray;$i++)
          {
            echo "<th>".$titlearray[$i]."</th>";
          }
          echo "</tr>";
          $nums_array=count($array);
          for($i=0;$i<$nums_array;$i++){
            echo "<tr>";
            $nums_array_i=count($array[$i]);
            for($j=0;$j<$nums_array_i;$j++)
            {
              echo "<td align='left'>".$array[$i][$j]."</td>";
            }
            echo "</tr>";
          }
          echo "</tr></table>";
          break;          
      case "table_noborder_notitlewords":
          $temp="";
          global $id;
          //$titlearray=explode(',',$title);
          ob_start();
          getgoogle_i18n("translateme",$title);
          $google=ob_get_contents();
          ob_end_clean();       
          echo "
          <!--table border=0 width=100%>
            <tr>
              <td>
                ".$google."
              </td>
              <td>
              <button onClick=\"makeRequest('sound.php','blog_id={$id}&contents='+encodeURIComponent(document.getElementsByName('translateme[]')[document.getElementById('nowview').value].innerHTML),'soundplay');\">".__('語音教學')."</button>
              </td>
            </tr>
          </table-->
          <table border=0 width=100% >";
          //"<tr bgcolor=#F3F3F3>";
          //for($i=0;$i<count($titlearray);$i++)
          //{
//            echo "<td><div class=style6>&nbsp;".$titlearray[$i]."</div></td>";
//          }
//          echo "</tr>";
          $nums_array=count($array);
          for($i=0;$i<$nums_array;$i++){
            echo "<tr>";
            $nums_array_i=count($array[$i]);
            for($j=0;$j<$nums_array_i;$j++)
            {
              
              echo "<td align='left'>";
              echo "";
              echo "<div class=style6><span name=\"translateme[]\" id=\"translateme_{$title}\">&nbsp;".$array[$i][$j]."</span></div></td>";
            }
            echo "</tr>";
          }
          echo "</tr></table>";
          break;
      case "table_todolist_table":
          $temp="";
          $titlearray=explode(',',$title);
          echo "<table border=1 width=90% cellspacing=0 cellpadding=0><tr>";
          $nums_titlearray=count($titlearray);
          for($i=0;$i<$nums_titlearray;$i++)
          {
            switch($i)
            {
                  case '0':
                      echo "<td align=center width=8% valign=center>".$titlearray[$i]."</td>";
                      break;
                  case '1':
                      echo "<td align=center width=72% valign=center>".$titlearray[$i]."</td>";
                      break;
                  case '2':
                      echo "<td align=center width=10% valign=center>".$titlearray[$i]."<a href=\"javascript:;\" onClick=\"reloadtable('&sort=important');\"><img width=30 height=18  border='0' src='images/sort-desc.png'></a></td>";
                      break;
                  case '3':
                      echo "<td align=center width=10% valign=center>".$titlearray[$i]."<a href=\"javascript:;\" onClick=\"reloadtable('&sort=status');\"><img width=30 height=18 border='0' src='images/sort-desc.png'></a></td>";
                      break;
               }
          }
          echo "</tr>";
          $nums_array=count($array);
          for($i=0;$i<$nums_array;$i++)
          {
            echo "<tr>";
            $nums_array_i=count($array[$i]);
            for($j=0;$j<$nums_array_i-1;$j++)
            {
                switch($j)
              {
                case "0":
                case "2":
                case "3":
                        echo "<td align=center>".$array[$i][$j]."</td>";
                      break;
                default:
                      echo "<td align=left>".$array[$i][$j]."</td>";
              }
            }
            echo "</tr>";
          }
          echo "</tr></table>";
          break;
      case "table_for_question":
          $temp="";
          $titlearray=explode(',',$title);
          echo "<table border=1><tr>";
          $titlewidth=array('1%','1%','25%','1%','35%','40%');
          $nums_titlearray=count($titlearray);
          for($i=0;$i<$nums_titlearray;$i++)
          {
            echo "<td width='".$titlewidth[$i]."'><center>".$titlearray[$i]."</center></td>";
          }
          echo "</tr>";
          $nums_array=count($array);
          for($i=0;$i<$nums_array;$i++){
            echo "<tr>";
            $nums_array_i=count($array[$i]);
            for($j=0;$j<$nums_array_i;$j++)
            {
              echo "<td>".$array[$i][$j]."</td>";
            }
            echo "</tr>";
          }
          echo "</tr></table>";
          break;
      case "table_for_man_select":
          $temp="";
          $titlearray=explode(',',$title);
          echo "<table border=1><tr>";
          $nums_titlearray=count($titlearray);
          for($i=0;$i<$nums_titlearray;$i++)
          {
            echo "<td>".$titlearray[$i]."</td>";
          }
          echo "</tr>";
          $nums_array=count($array);
          for($i=0;$i<$nums_array;$i++){
            echo "<tr>";
            $nums_array_i=count($array[$i]);
            for($j=0;$j<$nums_array_i;$j++)
            {
              if($j==$nums_array_i-1)
              {
                echo "<td><input type=checkbox id=manselect name=manselect value=".$array[$i][$j]."></td>";
              }
              else
              {
                echo "<td>".$array[$i][$j]."</td>";
              }
            }
            echo "</tr>";
          }
          echo "</tr></table>";
          break;
      case "autoneweditfixselect":

                $temp="";
          $titlearray=explode(',',$title);
          echo "<table border=1><tr>";
          $nums_titlearray=count($titlearray);
          for($i=0;$i<$nums_titlearray;$i++)
          {
            echo "<td bgcolor=#ff9543>".$titlearray[$i]."</td>";
          }
          echo "</tr>";
          $nums_array=count($array);
          for($i=0;$i<$nums_array;$i++){
            echo "<tr>";
            $nums_array_i=count($array[$i]);
            for($j=0;$j<$nums_array_i;$j++)
            {
              echo "<td>".$array[$i][$j]."</td>";
            }
              //模式選擇
              echo "<td>";
              echo "<select id=s".$i." name=s".$i." >";
              echo "<option value=".$array[$i][0]."_PK_mode>PK</option>";
              echo "<option value=".$array[$i][0]."_text_mode selected>TEXT</option>";
              echo "<option value=".$array[$i][0]."_password_mode>PASSWORD</option>";
              echo "<option value=".$array[$i][0]."_textarea_mode>TEXTAREA</option>";
              echo "<option value=".$array[$i][0]."_radio_mode>RADIO</option>";
              echo "<option value=".$array[$i][0]."_checkbox_mode>CHECKBOX</option>";
              echo "<option value=".$array[$i][0]."_hidden_mode>HIDDEN</option>";
              echo "<option value=".$array[$i][0]."_manualdate_mode>手動日期建檔</option>";
              echo "<option value=".$array[$i][0]."_manualtime_mode>手動時間建檔</option>";
              echo "<option value=".$array[$i][0]."_autodate_mode>自動日期建檔</option>";
              echo "<option value=".$array[$i][0]."_autotime_mode>自動時間建檔</option>";
              echo "<option value=".$array[$i][0]."_autowebEDITOR_mode>WEB-EDITOR</option>";
              echo "</select>";
              echo "</td>";
            echo "</tr>";
          }
          echo "</tr></table>";
          //echo "<input type=hidden id=mode name=mode value=startcreate>";//操作模式
          //echo "<br>步驟三，進階設定：<br>";
          break;
      case "table_for_photo":
          global $USER_ID;
          $temp="";
          $titlearray=explode(',',$title);
          echo "<table border=1  class=listing id=sortable><thead><tr>";
          $nums_titlearray=count($titlearray);
          for($i=0;$i<$nums_titlearray;$i++)
          {
            echo "<th><font size=2>".$titlearray[$i]."</font><br></th>";
          }
          echo "</thead></tr><tbody>";
          $nums_array=count($array);
          for($i=0;$i<$nums_array;$i++){
            echo "<tr>";            
            $nums_array_i=count($array[$i]);
            for($j=0;$j<$nums_array_i;$j++)
            {
              if($j==1)
              {
//http://3wa.myvnc.com/photo/index.php?mode=select&id=4
                echo "<td><center><a href='index.php?uid=".urlencode($USER_ID)."&mode=select&id=".$array[$i][0]."'><font size=2>".$array[$i][$j]."</font></a></center></td>";
              }
              else
              {
                echo "<td><center><font size=2>".$array[$i][$j]."</font></center></td>";
              }
          
            }
            echo "<td><center><button onclick=\"if(confirm('".__('你確定要編輯這筆嗎')."?')){location.href='?uid=".urlencode($USER_ID)."&mode=edit&id=".$array[$i][0]."';}\";>".__('編輯')."</button></center></td>";  
            echo "<td><center><button onclick=\"if(confirm('".__('你確定要刪除這筆嗎')."?')){location.href='?uid=".urlencode($USER_ID)."&mode=delete&id=".$array[$i][0]."';}\";>".__('刪除')."</button></center></td>";                        
            echo "</tr>";            
          }                    
          echo "</tr></tbody></table>";
          break;  
      case "table_for_video":
          global $USER_ID;
          $temp="";
          $titlearray=explode(',',$title);
          echo "<table border=1  class=listing id=sortable><thead><tr>";
          $nums_titlearray=count($titlearray);
          for($i=0;$i<$nums_titlearray;$i++)
          {
            echo "<th><font size=2>".$titlearray[$i]."</font><br></th>";
          }
          echo "</thead></tr><tbody>";
          $nums_array=count($array);
          for($i=0;$i<$nums_array;$i++){
            echo "<tr>";         
            $nums_array_i=count($array[$i]);   
            for($j=0;$j<$nums_array_i;$j++)
            {
              if($j==1)
              {
//http://3wa.myvnc.com/photo/index.php?mode=select&id=4
                echo "<td align=center><a href=index.php?uid=".urlencode($USER_ID)."&mode=select&id=".$array[$i][0]."><font size=2>".$array[$i][$j]."</font></a></td>\n";
              }
              else
              {
                echo "<td align=center><font size=2>".$array[$i][$j]."</font></center></td>\n";
              }
          
            }
            echo "<td align=center><button onClick=\"location.href='?uid=".urlencode($USER_ID)."&mode=edit&id=".$array[$i][0]."';\">".__('編輯')."</button></td>\n";
            echo "<td align=center><button onclick=\"if(confirm('".__('你確定要刪除這筆嗎')."?')){location.href='?uid=".urlencode($USER_ID)."&mode=delete&id=".$array[$i][0]."';}\";>".__('刪除')."</button></td>\n";                        
            echo "</tr>\n";            
          }                    
          echo "</tr></tbody></table>";
          break;  
      case "userlog":
          $temp="";
          $titlearray=explode(',',$title);
          echo "<table border=1 width=100%><tr>";
          $nums_titlearray=count($titlearray);
          for($i=0;$i<$nums_titlearray;$i++)
          {
            echo "<td>".$titlearray[$i]."</td>";
          }
          echo "</tr>";
          $nums_array=count($array);
          for($i=0;$i<$nums_array;$i++){
            echo "<tr>";
            echo "<td><a href='javascript:;' onClick=\"divuse('".urlencode($array[$i][0])."','view');flyLayer.style.display='block';\">".$array[$i][0]."</a></td>";              
            echo "<td align=center>".$array[$i][1]."</td>";
            echo "<td><center><a width=500 height=400 href='javascript:;' onClick=\"divuse('".urlencode($array[$i][0])."','edit');flyLayer.style.display='inline';\">定義</a></center></td>";
            echo "</tr>";
          }
          echo "</tr></table>";
          break;               
      case "table_talk_board_kind":
          global $USER_ID;
          $temp="";
          $titlearray=explode(',',$title);
          echo "<table border=1 align=center><tr>";
          $nums_titlearray=count($titlearray);
          for($i=0;$i<$nums_titlearray;$i++)
          {
            echo "<td align=center>".$titlearray[$i]."</td>";
          }
          echo "</tr>";
          $nums_array=count($array);
          for($i=0;$i<$nums_array;$i++){
          echo "<tr    onmouseover=\"this.style.backgroundColor='#222222';\"
                      onmouseout=\"this.style.backgroundColor='';\">";
            $nums_array_i=count($array[$i]);  
            for($j=0;$j<$nums_array_i-1;$j++)
            {
              echo "<td align=center>&nbsp;".$array[$i][$j]."</td>";              
            }
          ?>
            <td align=center><input type=button value=修改 onClick="if(confirm('你確定要修改嗎?!')){location.href='?mode=edit&id=<?=$array[$i][0];?>&uid=<?=urlencode($USER_ID);?>';}"></td>
             
              <td align=center>&nbsp;
              <?
              if($array[$i][5]=='0')
              {
            ?> 
                <input type=button value='刪除' onClick="if(confirm('你確定要刪除嗎?!')){location.href='?mode=del_action&id=<?=$array[$i][0]?>&uid=<?=urlencode($USER_ID);?>';}">
            <?
              }
            ?>
              </td>
             
            <td align=center>&nbsp;
            <?
            if($array[$i][5]=='1')
              {                  
            ?>
              <input type=button value='還原' onClick="if(confirm('你確定要原還嗎?!')){location.href='?mode=resume_action&id=<?=$array[$i][0]?>&uid=<?=urlencode($USER_ID);?>';}">
            <?
              }
            ?>
            </td>
            <?
            echo "</tr>";
          }
          echo "</tr></table>";
          break;     
      case "table_talk_board_1.0":
          global $USER_ID;
          $temp="";
          $titlearray=explode(',',$title);
          $title_width_array=array('50%','20%','10%','10%','10%');
          $title_align_array=array('left','center','center','center','center');
          echo "<table border=1 align=center width=80% cellspacing=0 cellpadding=5><tr>";
          $nums_titlearray=count($titlearray);
          for($i=0;$i<$nums_titlearray;$i++)
          {
            echo "<td align=center style='font-size:12px;' width=".$title_width_array[$i].">".$titlearray[$i]."</td>";
          }
          echo "</tr>";
          $nums_array=count($array);
          for($i=0;$i<$nums_array;$i++){
          echo "<tr onmouseover=\"this.style.backgroundColor='#222222';\"
                      onmouseout=\"this.style.backgroundColor='';\">";
            $nums_array_i=count($array[$i]);  
            for($j=1;$j<$nums_array_i;$j++)
            {
              echo "<td align=".$title_align_array[$j-1].">&nbsp;".$array[$i][$j]."</td>\n";          
            }

            echo "</tr>";
          }
          echo "</table>";
          break;                   
    }
  }
  function array_orderby()
  {
    /*Sample      
    The sorted array is now in the return value of the function instead of being passed by reference. 
    $data[] = array('volume' => 67, 'edition' => 2);
    $data[] = array('volume' => 86, 'edition' => 1);
    $data[] = array('volume' => 85, 'edition' => 6);
    $data[] = array('volume' => 98, 'edition' => 2);
    $data[] = array('volume' => 86, 'edition' => 6);
    $data[] = array('volume' => 67, 'edition' => 7);
    
    // Pass the array, followed by the column names and sort flags
    $sorted = array_orderby($data, 'volume', SORT_DESC, 'edition', SORT_ASC);
    */
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
      if (is_string($field)) {
        $tmp = array();
        foreach ($data as $key => $row)
          $tmp[$key] = $row[$field];
        $args[$n] = $tmp;
      }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
  }  
    //以後排序用這支
    function array_sort($array, $on, $order='SORT_DESC')
    {
      $new_array = array();
      $sortable_array = array();
 
      if (count($array) > 0) {
          foreach ($array as $k => $v) {
              if (is_array($v)) {
                  foreach ($v as $k2 => $v2) {
                      if ($k2 == $on) {
                          $sortable_array[$k] = $v2;
                      }
                  }
              } else {
                  $sortable_array[$k] = $v;
              }
          }
 
          switch($order)
          {
              case 'SORT_ASC':   
                  //echo "ASC";
                  asort($sortable_array);
              break;
              case 'SORT_DESC':
                  //echo "DESC";
                  arsort($sortable_array);
              break;
          }
 
          foreach($sortable_array as $k => $v) {
              $new_array[] = $array[$k];
          }
      }
      return $new_array;
    }   
function record_sort($records, $field, $reverse=false)  //排序用的　，array, 第幾欄,正or否
{
   $hash = array();

   foreach($records as $record)
   {
       $hash[$record[$field]] = $record;
   }

   ($reverse)? krsort($hash) : ksort($hash);

   $records = array();

   foreach($hash as $record)
   {
       $records []= $record;
   }

   return $records;
}


function my_array_unique($somearray){ //去除array重複值  傳入array，回傳出新的array
    $tmparr = array_unique($somearray);
    $i=0;
    foreach ($tmparr as $v) {
        $newarr[$i] = $v;
         $i++;
    }
     return $newarr;
  }
  function fullchinesedate($date){ //傳入20061201回傳2006年12月01日
    return date('Y年m月d',strtotime($date));
  }
  function fulldotdate($date){ //傳入20061201回傳2006.12.01
    return date('Y.m.d',strtotime($date));
  }
  function fullslashdate($date){ //傳入20061201回傳2006-12-01
    return date('Y-m-d',strtotime($date));
  }
  function shortime($longtime){
    $temp="";
    if(strlen(trim($longtime))==5)
    {
      $temp=str_replace(":","",trim($longtime));
      return $temp."00";
    }
    else if(strlen(trim($longtime))==8)
    {
      $temp=str_replace(":","",trim($longtime));
      return $temp;
    }
    return $longtime;
  }
  function longtime($shorttime){  //傳入123412，回傳12:34:12
    if(strlen($shorttime)==6)
    {
      return substr($shorttime,0,2).":".substr($shorttime,2,2).":".substr($shorttime,4,2);
    }
    else
    {
      return $shorttime;
    }
  }
  //加密與解密
  function enPWD_string( $str, $key ) {
    $str = base64_encode($str);
    $key = base64_encode($key); 
    $xored = "";
    for ($i=0,$max_i=strlen($str);$i<$max_i;$i++) {
      $a = ord(substr($str,$i,1));      
      for ($j=0,$max_j=strlen($key);$j<$max_j;$j++) {      
        $k = ord(substr($key,$j,1));
        $a = $a ^ $k;
      }
      $xored = sprintf("%s%s",$xored,chr($a));
    }       
    return base64_encode($xored);
  }
  function dePWD_string( $str, $key ) {
    $str = base64_decode($str);    
    $key = base64_encode($key);
    $xored = "";
    for ($i=0,$max_i=strlen($str);$i<$max_i;$i++) {
      $a = ord(substr($str,$i,1));
      for ($j=strlen($key)-1;$j>=0;$j--) {    
        $k = ord(substr($key,$j,1));
        $a = $a ^ $k;
      }
      $xored = sprintf("%s%s",$xored,chr($a));
    }   
    $xored = base64_decode($xored);
    return $xored;
  }
  function encoded($ses) //加密
  {
    $code=1234567890;
    $temp=($ses ^ $code);
    return $temp;
  }//end of encoded function

  function decoded($str) //解密
  {
     $code=1234567890;
     $temp=($str ^ $code);
     return $temp;
  }//end of decoded function
  function delete_row_array($array,$rows)  //刪除一個陣列裡的列，  3,4,6  刪掉3、4、6列，回傳新的array
  {
    $del_field=explode(',',$rows); //先產生要刪的表
    for($i=0,$nums_del_field=count($del_field);$i<$nums_del_field;$i++)
    {
      unset($array[$del_field[$i]]);
    }
    $new_array=array_values($array);
    return $new_array;
  }
function delete_field_array($array,$fields)  //刪除一個陣列裡的某些欄位，  3,4,6  刪掉3、4、6欄，回傳新的array
{
  $del_field=explode(',',$fields); //先產生要刪的表
  //先作檢查
  for($i=0,$nums_del_field=count($del_field);$i<$nums_del_field;$i++)
  {
      $nums_array=count($array[0]);
      if($del_field[$i]>($nums_array-1))
      {
        //echo "！！！錯誤，傳入值比陣列還大...!";
        //exit(1);
        return $array;
      }
  }

  sort($del_field); //由小到大排序
  $del_fields=my_array_unique($del_field); //去除重覆性
  $nums_array=count($array);
  for($i=0;$i<$nums_array;$i++)
  {
    $temp=0;
    $nums_array_i=count($array[$i]);
    for($j=0;$j<$nums_array_i;$j++)
    {
      $check=0;
      $nums_del_fields=count($del_fields);
      for($k=0;$k<$nums_del_fields;$k++)
      {
        if($del_fields[$k]==$j)
        $check=1;
      }
      if($check==0)
      {
        $okarray[$i][$temp++]=$array[$i][$j];
      }
    }

  }
  return $okarray;
}
   function before_after($str,$mode)
   {
    $temparray=explode("_",$str);
    $temp="";
    $nums_temparray=count($temparray);
    if($nums_temparray>0)
    {
         switch($mode){
        case "before":
                for($i=0;$i<$nums_temparray-2;$i++)
                {
                    $temp.=$temparray[$i]."_";
                }
                if(strlen($temp)>0)
                {
                    $temp=substr($temp,0,-1);
                }
                return $temp;
                break;
        case "after":    return $temparray[$nums_temparray-2]."_".$temparray[$nums_temparray-1];
                break;
         }
    }
    else
    {
        return "囧";
    }
   }
  //羽山之CSS分頁
  //Patch 1.0
  //DATE 2007-8-1
  //Time 12:00
  //$pagelist代表一頁要show幾筆
  //＄style代表未來要規換的版型
  //字用 , 分格，為table header
  //如果同一頁要用二個以上的 show cut,就要設不同的 $divstring id
  function showcutarray($array,$pagelist,$words,$style,$divstring='div_cut')
  {
    switch($style)
    {
         case "normal":
              $nums_array=count($array);
              $total_cut_page=ceil($nums_array/$pagelist);
              for($i=0;$i<$total_cut_page;$i++)
              {
                for($j=$i*$pagelist;$j<$i*$pagelist+$pagelist;$j++)
                {
                  $cut[$i][$j%$pagelist]=$array[$j];
                  //echo $i.".".$j%$pagelist."<br>";
                }
              }
              echo "<table border=1><tr><td align=center>";
              for($i=0;$i<$total_cut_page;$i++)
              {
                //先顯示第一頁                
                echo "<div id=\"".$divstring."\" name=\"".$divstring."\" style=\"display:";
                if($i==0){echo "inline;";}else{echo "none;";}
                echo "\">";
                showArrayStyleT($cut[$i],'table',$words);
                echo "</div>";
                              
              }
              for($i=0;$i<$total_cut_page;$i++)
              {
                echo "<a href='javascript:;' onClick=";
                ?>
                "for(i=0;i<<?=$total_cut_page;?>;i++)
                 {
                   document.getElementsByName('<?=$divstring;?>')[i].style.display='none';
                 }
                 document.getElementsByName('<?=$divstring;?>')[<?=$i;?>].style.display='inline';"
                <?                
                echo ">".$i."</a> ";
              }
              echo "</td>";
              echo "</tr>";
              echo "</table>";
              break;    
         case "old_normal":
              $nums_array=count($array);
              $total_cut_page=ceil($nums_array/$pagelist);
              for($i=0;$i<$total_cut_page;$i++)
              {
                for($j=$i*$pagelist;$j<$i*$pagelist+$pagelist;$j++)
                {
                  $cut[$i][$j%$pagelist]=$array[$j];
                  //echo $i.".".$j%$pagelist."<br>";
                }
              }
              echo "<table border=1><tr><td align=center>";
              for($i=0;$i<$total_cut_page;$i++)
              {
                //先顯示第一頁                
                echo "<div id=\"".$divstring."\" name=\"".$divstring."\" style=\"display:";
                if($i==0){echo "inline;";}else{echo "none;";}
                echo "\">";
                showArrayStyleT($cut[$i],'table',$words);
                echo "</div>";
                              
              }
              for($i=0;$i<$total_cut_page;$i++)
              {
                echo "<a href='javascript:;' onClick=";
                ?>
                "for(i=0;i<<?=$total_cut_page;?>;i++)
                 {
                   document.getElementsByName('<?=$divstring;?>')[i].style.display='none';
                 }
                 document.getElementsByName('<?=$divstring;?>')[<?=$i;?>].style.display='inline';"
                <?                
                echo ">".$i."</a> ";
              }
              echo "</td>";
              echo "</tr>";
              echo "</table>";
              break;
    
      case "blog_reply":             
              $nums_array=count($array);
              $total_cut_page=ceil($nums_array/$pagelist);
              //for($i=0;$i<$total_cut_page;$i++)$pagelist
              for($i=0;$i<$total_cut_page;$i++)
              {
                for($j=$i*$pagelist;$j<$i*$pagelist+$pagelist;$j++)
                {
                  $cut[$i][($j%$pagelist)]=$array[$j];
                  //echo $i.".".$j%$pagelist."<br>";                  
                }
              }
              echo "<table border='0' width='100%'><tr><td align='left'>";
              for($i=0;$i<$total_cut_page;$i++)
              {
                //先顯示第一頁                
                echo "<div id=\"".$divstring."\" name=\"".$divstring."\" style=\"display:";
                if($i==0){echo "inline;";}else{echo "none;";}
                echo "\">";
                //showArrayStyleT($cut[$i],'table',$words);
                  
                $nums_cut_i=count($cut[$i]);
                if($i==$total_cut_page-1)
                {
                  $try=ceil($nums_array%$pagelist);
                  $nums_cut_i=($try==0)?$nums_cut_i:$try;
                }  
                for($k=0;$k<$nums_cut_i;$k++)
                {
                ?>
                <div style="margin:2;color:#aaaaaa;padding-top:0em;background:#000000;border:1px;border-style:dashed;">
                  <font color="#8f8f8f"><?=$cut[$i][$k][3];?>  <?=fulldotdate($cut[$i][$k][4]);?> <?=longtime($cut[$i][$k][5]);?> <?=__('回覆');?>：</font>
                    <?
                      if($cut[$i][$k][6]=='fixOK')
                      {
                        echo "【<a href='{$base_url}/blog/blog.php?mode=del_reply&nextpage=".base64_encode($_SERVER['QUERY_STRING'])."&del_reply_id=".$cut[$i][$k][0]."'>".__('刪除')."</a>】";
                      }
                    ?>
                    <br>
                  <div style="color:orange;margin-left:15px;">
                    <?=nl2br($cut[$i][$k][1]);?>
                  </div>                
                </div>
                <br>
                <?
                }
                    
                echo "</div>";               
              }
              ?>
              <p align=right>
              <?
              for($i=0;$i<$total_cut_page;$i++)
              {
                echo "<a href='javascript:;' onClick=";
                ?>
                "for(i=0;i<<?=$total_cut_page;?>;i++)
                 {
                   document.getElementsByName('<?=$divstring;?>')[i].style.display='none';
                 }
                 document.getElementsByName('<?=$divstring;?>')[<?=$i;?>].style.display='inline';"
                <?                
                echo ">".($i+1)."</a> ";
              }
              ?>
              </p>
              <?              
              echo "</td>";
              echo "</tr>";
              echo "</table>";
            break;
      case "normal_noborder_notitlewords":
              //起始值              
              echo "<script>nowpage=0;</script>";
              $nums_array=count($array);
              $total_cut_page=ceil($nums_array/$pagelist);
              //for($i=0;$i<$total_cut_page;$i++)
               for($i=0;$i<$total_cut_page;$i++) 
              {
                for($j=$i*$pagelist;$j<$i*$pagelist+$pagelist;$j++)
                {
                  $cut[$i][$j%$pagelist]=$array[$j];
                  //echo $i.".".$j%$pagelist."<br>";
                }
              }
              echo "<table border=0 width=100%><tr><td align=center>";
              for($i=0;$i<$total_cut_page;$i++)
              {
                //先顯示第一頁                
                echo "<div id=\"".$divstring."\" name=\"".$divstring."\" style=\"display:";
                if($i==0){echo "inline;";}else{echo "none;";}
                echo " \">";
                showArrayStyleT($cut[$i],'table_noborder_notitlewords',$i);
                echo "</div>";
                              
              }              









              echo "<center>";
              echo "<a href='#showtitle' onClick=";
                ?>
                "for(i=0;i<<?=$total_cut_page;?>;i++)
                 {
                   document.getElementsByName('<?=$divstring;?>')[i].style.display='none';
                   document.getElementsByName('whati')[i].color='#fff';
                 }
                 nowpage=0;
                 document.getElementsByName('<?=$divstring;?>')[0].style.display='inline';
                 document.getElementsByName('whati')[nowpage].color='#FF0000';
                 if(nowpage==0){document.getElementById('forprevious').style.display='none';}else{document.getElementById('forprevious').style.display='inline';}
                 if(nowpage==<?=$total_cut_page;?>-1){document.getElementById('fornext').style.display='none';}else{document.getElementById('fornext').style.display='inline';}
                 document.getElementById('showpage').innerHTML='第 '+(nowpage+1)+' 頁';
                 document.getElementById('nowview').value=nowpage;
                 "

                <?                
                echo ">首頁</a>";



              echo "&nbsp;&nbsp;";

//放入上十頁-start
              echo "<span id=upten >上十頁</span>";
//放入上十頁-end

              echo "&nbsp;&nbsp;";
              echo "";


              echo "<a href='#showtitle' id=forprevious name=forprevious onClick=";
                ?>
                "if(nowpage-1>=0){for(i=0;i<<?=$total_cut_page;?>;i++)
                 {
                   document.getElementsByName('<?=$divstring;?>')[i].style.display='none';
                   document.getElementsByName('whati')[i].color='#fff';
                 }                 
                 document.getElementsByName('<?=$divstring;?>')[--nowpage].style.display='inline';
                 document.getElementsByName('whati')[nowpage].color='#FF0000';
                 }
                 if(nowpage==0){document.getElementById('forprevious').style.display='none';}else{document.getElementById('forprevious').style.display='inline';}
                 if(nowpage==<?=$total_cut_page;?>-1){document.getElementById('fornext').style.display='none';}else{document.getElementById('fornext').style.display='inline';}
                 document.getElementById('showpage').innerHTML='第 '+(nowpage+1)+' 頁';
                 document.getElementById('nowview').value=nowpage;
                 "
                <?                
                echo ">上一頁</a>";


              echo "";
              echo "&nbsp;&nbsp;";



              for($i=0;$i<$total_cut_page;$i++)
              {
                echo "<a href='#showtitle' onClick=";
                ?>
                "for(i=0;i<<?=$total_cut_page;?>;i++)
                 {                   
                   document.getElementsByName('<?=$divstring;?>')[i].style.display='none';
                   document.getElementsByName('whati')[i].color='#fff';
                 }
                 nowpage=<?=$i;?>;
                 document.getElementsByName('<?=$divstring;?>')[<?=$i;?>].style.display='inline';
                 document.getElementsByName('whati')[nowpage].color='#FF0000';
                 if(nowpage==0){document.getElementById('forprevious').style.display='none';}else{document.getElementById('forprevious').style.display='inline';}
                 if(nowpage==<?=$total_cut_page;?>-1){document.getElementById('fornext').style.display='none';}else{document.getElementById('fornext').style.display='inline';}
                 document.getElementById('showpage').innerHTML='第 '+(nowpage+1)+' 頁';;
                 //numbers[nowpage].innerHTML='<font id=whati name=whati[] size=2>'+(nowpage+1)+'</font>';
                 "
                <?                
                echo "><font id=whati name=whati size=2><u>".($i+1)."</u></font></a> ";
              }











              echo "<a href='#showtitle' id=fornext name=fornext onClick=";
                ?>
                "if(nowpage+1<=<?=$total_cut_page;?>-1){for(i=0;i<<?=$total_cut_page;?>;i++)
                 {
                   document.getElementsByName('<?=$divstring;?>')[i].style.display='none';
                   document.getElementsByName('whati')[i].color='#fff';
                 }                 
                 document.getElementsByName('<?=$divstring;?>')[++nowpage].style.display='inline';
                 document.getElementsByName('whati')[nowpage].color='#FF0000';
                 }
                 if(nowpage==0){document.getElementById('forprevious').style.display='none';}else{document.getElementById('forprevious').style.display='inline';}
                 if(nowpage==<?=$total_cut_page;?>-1){document.getElementById('fornext').style.display='none';}else{document.getElementById('fornext').style.display='inline';}
                 document.getElementById('showpage').innerHTML='第 '+(nowpage+1)+' 頁';
                 document.getElementById('nowview').value=nowpage;
                 "
                <?                
                echo ">下一頁</a>";


              echo "";
              echo "&nbsp;&nbsp;";

//放入下十頁-start
            //  echo "<span id=downten >下十頁</span>";
//放入下十頁-end

              echo "&nbsp;&nbsp;";




              echo "<a href='#showtitle' onClick=";
                ?>
                "for(i=0;i<<?=$total_cut_page;?>;i++)
                 {
                   document.getElementsByName('<?=$divstring;?>')[i].style.display='none';
                   document.getElementsByName('whati')[i].color='#fff';
                 }
                 nowpage=<?=$total_cut_page;?>-1;
                 document.getElementsByName('<?=$divstring;?>')[<?=$total_cut_page;?>-1].style.display='inline';
                 document.getElementsByName('whati')[nowpage].color='#FF0000';
                 if(nowpage==0){document.getElementById('forprevious').style.display='none';}else{document.getElementById('forprevious').style.display='inline';}
                 if(nowpage==<?=$total_cut_page;?>-1){document.getElementById('fornext').style.display='none';}else{document.getElementById('fornext').style.display='inline';}
                 document.getElementById('showpage').innerHTML='第 '+(nowpage+1)+' 頁';
                 document.getElementById('nowview').value=nowpage;
                 "
                <?                
                echo ">最末頁</a>";

                



//顯示總共幾頁
                echo " (總共有...".$total_cut_page."頁)";
//顯示總共幾頁

                echo "<br>";




              echo "<br>";
              echo "<span id=showpage name=showpage class=style6>第 1 頁</span>";
              echo "</center>";
             echo "</td>";
              echo "</tr>";
              echo "</table>";
              echo "<script>
                 if(nowpage==0){document.getElementById('forprevious').style.display='none';}else{document.getElementById('forprevious').style.display='inline';}
                 if(nowpage==".$total_cut_page."-1){document.getElementById('fornext').style.display='none';}else{document.getElementById('fornext').style.display='inline';}
                 document.getElementById('showpage').innerHTML='第 '+(nowpage+1)+' 頁';
                 //document.getElementsByName('whati')[nowpage].color='#FF0000';
                 if(nowpage<10){document.getElementById('upten').style.display='none';}
                    </script>";
              break;
      case "normal_noborder_notitlewords_onClick_ShowImages":
              //起始值              
              echo "<script>nowpage=0;</script>";
              $nums_array=count($array);
              $total_cut_page=ceil($nums_array/$pagelist);
              //for($i=0;$i<$total_cut_page;$i++)
               for($i=0;$i<$total_cut_page;$i++) 
              {
                for($j=$i*$pagelist;$j<$i*$pagelist+$pagelist;$j++)
                {
                  $cut[$i][$j%$pagelist]=$array[$j];
                  //echo $i.".".$j%$pagelist."<br>";
                }
              }
              echo "<table border=0 width=100%><tr><td align=center>";
              for($i=0;$i<$total_cut_page;$i++)
              {
                //先顯示第一頁                
                echo "<div id=\"".$divstring."\" name=\"".$divstring."\" style=\"display:";
                if($i==0){echo "inline;";}else{echo "none;";}
                echo " \">";
                showArrayStyleT($cut[$i],'table_noborder_notitlewords',$i);
                echo "</div>";
                              
              }              









              echo "<center>";
              echo "<a href='#showtitle' onClick=";
                ?>            
                "for(i=0;i<<?=$total_cut_page;?>;i++)
                 {
                   document.getElementsByName('<?=$divstring;?>')[i].style.display='none';
                   document.getElementsByName('whati')[i].color='#fff';
                 }            
                 nowpage=0; 
                 document.getElementsByName('<?=$divstring;?>')[0].style.display='inline';
                 document.getElementsByName('whati')[nowpage].color='#FF0000';
                 if(nowpage==0){document.getElementById('forprevious').style.display='none';}else{document.getElementById('forprevious').style.display='inline';}
                 if(nowpage==<?=$total_cut_page;?>-1){document.getElementById('fornext').style.display='none';}else{document.getElementById('fornext').style.display='inline';}
                 document.getElementById('showpage').innerHTML='<?=__('第');?> '+(nowpage+1)+' <?=__('頁');?>';
                 for(i=0;i<document.getElementsByName('images['+nowpage+']').length;i++)
                 {
                   if(document.getElementsByName('images['+nowpage+']')[i].style.display!='inline')
                   {
                     document.getElementsByName('images['+nowpage+']')[i].src=document.getElementsByName('images['+nowpage+']')[i].alt;
                     document.getElementsByName('images['+nowpage+']')[i].style.display='inline';
                   }
                 }     
                 if(nowpage<<?=$total_cut_page;?>-1)
                 {
                   for(i=0;i<document.getElementsByName('images['+(nowpage+1)+']').length;i++)
                   {
                     if(document.getElementsByName('images['+(nowpage+1)+']')[i].style.display!='inline')
                     {
                       document.getElementsByName('images['+(nowpage+1)+']')[i].src=document.getElementsByName('images['+(nowpage+1)+']')[i].alt;
                       document.getElementsByName('images['+(nowpage+1)+']')[i].style.display='inline';
                     }
                   }                   
                 } 
                 document.getElementById('nowview').value=nowpage;             
                 "

                <?                
                echo ">".__('首頁')."</a>";



              echo "&nbsp;&nbsp;";

//放入上十頁-start
              echo "<span id=upten >".__('上十頁')."</span>";
//放入上十頁-end

              echo "&nbsp;&nbsp;";
              echo "";


              echo "<a href='#showtitle' id=forprevious name=forprevious onClick=";
                ?>
                "if(nowpage-1>=0){for(i=0;i<<?=$total_cut_page;?>;i++)
                 {
                   document.getElementsByName('<?=$divstring;?>')[i].style.display='none';
                   document.getElementsByName('whati')[i].color='#fff';
                 }                 
                 document.getElementsByName('<?=$divstring;?>')[--nowpage].style.display='inline';
                 document.getElementsByName('whati')[nowpage].color='#FF0000';
                 }
                 if(nowpage==0){document.getElementById('forprevious').style.display='none';}else{document.getElementById('forprevious').style.display='inline';}
                 if(nowpage==<?=$total_cut_page;?>-1){document.getElementById('fornext').style.display='none';}else{document.getElementById('fornext').style.display='inline';}
                 document.getElementById('showpage').innerHTML='<?=__('第');?> '+(nowpage+1)+' <?=__('頁');?>';
                 for(i=0;i<document.getElementsByName('images['+nowpage+']').length;i++)
                 {
                   if(document.getElementsByName('images['+nowpage+']')[i].style.display!='inline')
                   {
                     document.getElementsByName('images['+nowpage+']')[i].src=document.getElementsByName('images['+nowpage+']')[i].alt;
                     document.getElementsByName('images['+nowpage+']')[i].style.display='inline';
                   }
                 }     
                 if(nowpage<<?=$total_cut_page;?>-1)
                 {
                   for(i=0;i<document.getElementsByName('images['+(nowpage+1)+']').length;i++)
                   {
                     if(document.getElementsByName('images['+(nowpage+1)+']')[i].style.display!='inline')
                     {
                       document.getElementsByName('images['+(nowpage+1)+']')[i].src=document.getElementsByName('images['+(nowpage+1)+']')[i].alt;
                       document.getElementsByName('images['+(nowpage+1)+']')[i].style.display='inline';
                     }
                   }                   
                 }   
                 document.getElementById('nowview').value=nowpage; 
                 "
                <?                
                echo ">".__('上一頁')."</a>";


              echo "";
              echo "&nbsp;&nbsp;";



              for($i=0;$i<$total_cut_page;$i++)
              {
                echo "<a href='#showtitle' onClick=";
                ?>
                "for(i=0;i<<?=$total_cut_page;?>;i++)
                 {                   
                   document.getElementsByName('<?=$divstring;?>')[i].style.display='none';
                   document.getElementsByName('whati')[i].color='#fff';
                 }
                 nowpage=<?=$i;?>;
                 document.getElementsByName('<?=$divstring;?>')[<?=$i;?>].style.display='inline';
                 document.getElementsByName('whati')[nowpage].color='#FF0000';
                 if(nowpage==0){document.getElementById('forprevious').style.display='none';}else{document.getElementById('forprevious').style.display='inline';}
                 if(nowpage==<?=$total_cut_page;?>-1){document.getElementById('fornext').style.display='none';}else{document.getElementById('fornext').style.display='inline';}
                 document.getElementById('showpage').innerHTML='<?=__('第');?> '+(nowpage+1)+' <?=__('頁');?>';;
                 
                 for(i=0;i<document.getElementsByName('images['+nowpage+']').length;i++)
                 {
                   if(document.getElementsByName('images['+nowpage+']')[i].style.display!='inline')
                   {
                     document.getElementsByName('images['+nowpage+']')[i].src=document.getElementsByName('images['+nowpage+']')[i].alt;
                     document.getElementsByName('images['+nowpage+']')[i].style.display='inline';
                   }
                 }     
                 if(nowpage<<?=$total_cut_page;?>-1)
                 {
                   for(i=0;i<document.getElementsByName('images['+(nowpage+1)+']').length;i++)
                   {
                     if(document.getElementsByName('images['+(nowpage+1)+']')[i].style.display!='inline')
                     {
                       document.getElementsByName('images['+(nowpage+1)+']')[i].src=document.getElementsByName('images['+(nowpage+1)+']')[i].alt;
                       document.getElementsByName('images['+(nowpage+1)+']')[i].style.display='inline';
                     }
                   }                   
                 }  
                 document.getElementById('nowview').value=nowpage;                  
                 "
                <?                
                echo "><font id=whati name=whati size=2><u>".($i+1)."</u></font></a> ";
              }











              echo "<a href='#showtitle' id=fornext name=fornext onClick=";
                ?>
                "if(nowpage+1<=<?=$total_cut_page;?>-1){for(i=0;i<<?=$total_cut_page;?>;i++)
                 {
                   document.getElementsByName('<?=$divstring;?>')[i].style.display='none';
                   document.getElementsByName('whati')[i].color='#fff';
                 }                 
                 document.getElementsByName('<?=$divstring;?>')[++nowpage].style.display='inline';
                 document.getElementsByName('whati')[nowpage].color='#FF0000';
                 }
                 if(nowpage==0){document.getElementById('forprevious').style.display='none';}else{document.getElementById('forprevious').style.display='inline';}
                 if(nowpage==<?=$total_cut_page;?>-1){document.getElementById('fornext').style.display='none';}else{document.getElementById('fornext').style.display='inline';}
                 document.getElementById('showpage').innerHTML='<?=__('第');?> '+(nowpage+1)+' <?=__('頁');?>';
                 for(i=0;i<document.getElementsByName('images['+nowpage+']').length;i++)
                 {
                   if(document.getElementsByName('images['+nowpage+']')[i].style.display!='inline')
                   {
                     document.getElementsByName('images['+nowpage+']')[i].src=document.getElementsByName('images['+nowpage+']')[i].alt;
                     document.getElementsByName('images['+nowpage+']')[i].style.display='inline';
                   }
                 }     
                 if(nowpage<<?=$total_cut_page;?>-1)
                 {
                   for(i=0;i<document.getElementsByName('images['+(nowpage+1)+']').length;i++)
                   {
                     if(document.getElementsByName('images['+(nowpage+1)+']')[i].style.display!='inline')
                     {
                       document.getElementsByName('images['+(nowpage+1)+']')[i].src=document.getElementsByName('images['+(nowpage+1)+']')[i].alt;
                       document.getElementsByName('images['+(nowpage+1)+']')[i].style.display='inline';
                     }
                   }                   
                 }  
                 document.getElementById('nowview').value=nowpage;                 
                 "
                <?                
                echo ">".__('下一頁')."</a>";


              echo "";
              echo "&nbsp;&nbsp;";

//放入下十頁-start
            //  echo "<span id=downten >下十頁</span>";
//放入下十頁-end

              echo "&nbsp;&nbsp;";




              echo "<a href='#showtitle' onClick=";
                ?>
                "for(i=0;i<<?=$total_cut_page;?>;i++)
                 {
                   document.getElementsByName('<?=$divstring;?>')[i].style.display='none';
                   document.getElementsByName('whati')[i].color='#fff';
                 }
                 nowpage=<?=$total_cut_page;?>-1;
                 document.getElementsByName('<?=$divstring;?>')[<?=$total_cut_page;?>-1].style.display='inline';
                 document.getElementsByName('whati')[nowpage].color='#FF0000';
                 if(nowpage==0){document.getElementById('forprevious').style.display='none';}else{document.getElementById('forprevious').style.display='inline';}
                 if(nowpage==<?=$total_cut_page;?>-1){document.getElementById('fornext').style.display='none';}else{document.getElementById('fornext').style.display='inline';}
                 document.getElementById('showpage').innerHTML='<?=__('第');?> '+(nowpage+1)+' <?=__('頁');?>';
                 for(i=0;i<document.getElementsByName('images['+nowpage+']').length;i++)
                 {
                   if(document.getElementsByName('images['+nowpage+']')[i].style.display!='inline')
                   {
                     document.getElementsByName('images['+nowpage+']')[i].src=document.getElementsByName('images['+nowpage+']')[i].alt;
                     document.getElementsByName('images['+nowpage+']')[i].style.display='inline';
                   }
                 }     
                 if(nowpage<<?=$total_cut_page;?>-1)
                 {
                   for(i=0;i<document.getElementsByName('images['+(nowpage+1)+']').length;i++)
                   {
                     if(document.getElementsByName('images['+(nowpage+1)+']')[i].style.display!='inline')
                     {
                       document.getElementsByName('images['+(nowpage+1)+']')[i].src=document.getElementsByName('images['+(nowpage+1)+']')[i].alt;
                       document.getElementsByName('images['+(nowpage+1)+']')[i].style.display='inline';
                     }
                   }                   
                 }   
                 document.getElementById('nowview').value=nowpage;                  
                 "
                <?                
                echo ">".__('最末頁')."</a>";

                



//顯示總共幾頁
                echo " (".__('總共有')."...".$total_cut_page."".__('頁').")";
//顯示總共幾頁

                echo "<br>";




              echo "<br>";
              echo "<span id=showpage name=showpage class=style6>".__('第')." 1 ".__('頁')."</span>";
              echo "</center>";
             echo "</td>";
              echo "</tr>";
              echo "</table>";
              echo "<script language='javascript'>
                 if(nowpage==0){document.getElementById('forprevious').style.display='none';}else{document.getElementById('forprevious').style.display='inline';}
                 if(nowpage==".$total_cut_page."-1){document.getElementById('fornext').style.display='none';}else{document.getElementById('fornext').style.display='inline';}
                 document.getElementById('showpage').innerHTML='".__('第')." '+(nowpage+1)+' ".__('頁')."';
                 document.getElementsByName('whati')[nowpage].color='#FF0000';
                 if(nowpage<10){document.getElementById('upten').style.display='none';}
                 for(i=0;i<document.getElementsByName('images['+nowpage+']').length;i++)
                 {
                   if(document.getElementsByName('images['+nowpage+']')[i].style.display!='inline')
                   {
                     document.getElementsByName('images['+nowpage+']')[i].src=document.getElementsByName('images['+nowpage+']')[i].alt;
                     document.getElementsByName('images['+nowpage+']')[i].style.display='inline';
                   }
                 }     
                 if(nowpage<".$total_cut_page."-1)
                 {
                   for(i=0;i<document.getElementsByName('images['+(nowpage+1)+']').length;i++)
                   {
                     if(document.getElementsByName('images['+(nowpage+1)+']')[i].style.display!='inline')
                     {
                       document.getElementsByName('images['+(nowpage+1)+']')[i].src=document.getElementsByName('images['+(nowpage+1)+']')[i].alt;
                       document.getElementsByName('images['+(nowpage+1)+']')[i].style.display='inline';
                     }
                   }                   
                 }   
                 document.getElementById('nowview').value=nowpage;                  
                    </script>";
              break;              
      case "normal_noborder_notitlewords_for_tenpage":
              //起始值
              echo "<script>nowpage=0;now_tenpage=0;</script>";
              $nums_array=count($array);
              $total_cut_page=ceil($nums_array/$pagelist);
              //for($i=0;$i<$total_cut_page;$i++)
               for($i=0;$i<$total_cut_page;$i++) 
              {
                for($j=$i*$pagelist;$j<$i*$pagelist+$pagelist;$j++)
                {
                  $cut[$i][$j%$pagelist]=$array[$j];
                  //echo $i.".".$j%$pagelist."<br>";
                }
              }
              echo "<table border=0 width=100%><tr><td align=center>";
              for($i=0;$i<$total_cut_page;$i++)
              {
                //先顯示第一頁                
                echo "<div id=\"".$divstring."\" name=\"".$divstring."\" style=\"display:";
                if($i==0){echo "inline;";}else{echo "none;";}
                echo "\">";
                showArrayStyleT($cut[$i],'table_noborder_notitlewords',$words);
                echo "</div>";
                              
              }              









              echo "<center><span class=style6>";
              echo "<a href='#showtitle' onClick=";
                ?>
                "for(i=0;i<<?=$total_cut_page;?>;i++)
                 {
                   document.getElementsByName('<?=$divstring;?>')[i].style.display='none';
                   document.getElementsByName('whati')[i].color='#666666';
                 }
                 nowpage=0;
                 document.getElementsByName('<?=$divstring;?>')[0].style.display='inline';
                 document.getElementsByName('whati')[nowpage].color='#FF0000';
//                 if(nowpage==0){document.getElementById('forprevious').style.display='none';}else{document.getElementById('forprevious').style.display='inline';}
//                 if(nowpage==<?=$total_cut_page;?>-1){document.getElementById('fornext').style.display='none';}else{document.getElementById('fornext').style.display='inline';}
                 document.getElementById('showpage').innerHTML='第 '+(nowpage+1)+' 頁';;

                      for(i=0;i<<?=ceil($total_cut_page/10);?>;i++)
                      {
                        tenpage[i].style.display='none';
                      }
                      tenpage[0].style.display='inline';
                      document.getElementById('upten').style.display='none';
                      if(<?=ceil($total_cut_page/10);?>-1!=0)
                      {
                        downten.style.display='inline';
                      }
                      now_tenpage=0;
                 "

                <?                
                echo ">首頁</a>";



              echo "&nbsp;&nbsp;";

//放入上十頁-start
              echo "<a href='#showtitle' id=upten name=upten onClick=";
            ?>
                 "
                      for(i=0;i<<?=ceil($total_cut_page/10);?>;i++)
                      {
                        tenpage[i].style.display='none';
                      }                        
                      tenpage[now_tenpage-1].style.display='inline';
                      now_tenpage--;

                 for(i=0;i<<?=$total_cut_page;?>;i++)
                 {
                   document.getElementsByName('<?=$divstring;?>')[i].style.display='none';
                   document.getElementsByName('whati')[i].color='#666666';
                 }
                 
                  if(nowpage-10<0)
                  {
                    nowpage=0;                    
                  }
                  else
                  {
                    nowpage-=10;
                  }
                 //nowpage=<?=$total_cut_page;?>-1;
                 for(i=0;i<<?=$total_cut_page;?>;i++)
                 {
                   document.getElementsByName('<?=$divstring;?>')[i].style.display='none';
                   document.getElementsByName('whati')[i].color='#666666';
                 }
                 document.getElementsByName('<?=$divstring;?>')[nowpage].style.display='inline';
                 document.getElementsByName('whati')[nowpage].color='#FF0000';
//                 if(nowpage==0){document.getElementById('forprevious').style.display='none';}else{document.getElementById('forprevious').style.display='inline';}
//                 if(nowpage==<?=$total_cut_page;?>-1){document.getElementById('fornext').style.display='none';}else{document.getElementById('fornext').style.display='inline';}
                 document.getElementById('showpage').innerHTML='第 '+(nowpage+1)+' 頁';;                 

                if(now_tenpage+1==<?=ceil($total_cut_page/10);?>)
                {
                  downten.style.display='none';
                }
                else
                {   
                  downten.style.display='inline';
                }
                if(now_tenpage==0)
                {
                  document.getElementById('upten').style.display='none';
                }
                else
                {
                  document.getElementById('upten').style.display='inline';
                }
                
                 "
                <?
                   echo ">上十頁</a>";
//放入上十頁-end

              echo "&nbsp;&nbsp;";
              echo "";


/*              echo "<a href='#showtitle' id=forprevious name=forprevious onClick=";
                ?>
                "if(nowpage-1>=0){for(i=0;i<<?=$total_cut_page;?>;i++)
                 {
                   document.getElementsByName('<?=$divstring;?>')[i].style.display='none';
                   document.getElementsByName('whati')[i].color='#666666';
                 }                 
                 document.getElementsByName('<?=$divstring;?>')[--nowpage].style.display='inline';
                 document.getElementsByName('whati')[nowpage].color='#FF0000';
                 }
//                 if(nowpage==0){document.getElementById('forprevious').style.display='none';}else{document.getElementById('forprevious').style.display='inline';}
//                 if(nowpage==<?=$total_cut_page;?>-1){document.getElementById('fornext').style.display='none';}else{document.getElementById('fornext').style.display='inline';}
                 document.getElementById('showpage').innerHTML='第 '+(nowpage+1)+' 頁';;
                 "
                <?                
                echo ">上一頁</a>";


              echo "";
              echo "&nbsp;&nbsp;";
*/


              for($i=0;$i<$total_cut_page;$i++)
              {
                if($i%10==0&&$i!=0)
                {
                  echo "</span>";
                }
                if($i%10==0)
                {
                  echo "<span id=tenpage ";
                  if($i==0){echo "style=\"display:inline;\"";}else{echo "style=\"display:none;\"";}
                  echo " >";
                    
                }
                echo "<a href='#showtitle' onClick=";
                ?>
                "for(i=0;i<<?=$total_cut_page;?>;i++)
                 {                   
                   document.getElementsByName('<?=$divstring;?>')[i].style.display='none';
                   document.getElementsByName('whati')[i].color='#666666';
                 }
                 nowpage=<?=$i;?>;
                 document.getElementsByName('<?=$divstring;?>')[<?=$i;?>].style.display='inline';
                 document.getElementsByName('whati')[nowpage].color='#FF0000';
//                 if(nowpage==0){document.getElementById('forprevious').style.display='none';}else{document.getElementById('forprevious').style.display='inline';}
//                 if(nowpage==<?=$total_cut_page;?>-1){document.getElementById('fornext').style.display='none';}else{document.getElementById('fornext').style.display='inline';}
                 document.getElementById('showpage').innerHTML='第 '+(nowpage+1)+' 頁';;
                 //numbers[nowpage].innerHTML='<font id=whati name=whati[] size=2>'+(nowpage+1)+'</font>';
                 "
                <?                
                echo "><font id=whati name=whati[] size=2><u>".($i+1)."</u></font></a> ";

              }
              echo "</span>";









/*
              echo "<a href='#showtitle' id=fornext name=fornext onClick=";
                ?>
                "if(nowpage+1<=<?=$total_cut_page;?>-1){for(i=0;i<<?=$total_cut_page;?>;i++)
                 {
                   document.getElementsByName('<?=$divstring;?>')[i].style.display='none';
                   document.getElementsByName('whati')[i].color='#666666';
                 }                 
                 document.getElementsByName('<?=$divstring;?>')[++nowpage].style.display='inline';
                 document.getElementsByName('whati')[nowpage].color='#FF0000';
                 }
//                 if(nowpage==0){document.getElementById('forprevious').style.display='none';}else{document.getElementById('forprevious').style.display='inline';}
//                 if(nowpage==<?=$total_cut_page;?>-1){document.getElementById('fornext').style.display='none';}else{document.getElementById('fornext').style.display='inline';}
                 document.getElementById('showpage').innerHTML='第 '+(nowpage+1)+' 頁';;
                 "
                <?                
                echo ">下一頁</a>";

*/
              echo "";
              echo "&nbsp;&nbsp;";

//放入下十頁-start
              echo "<a href='#showtitle' id=downten name=downten onClick=";
            ?>
                 "
                      for(i=0;i<<?=ceil($total_cut_page/10);?>;i++)
                      {
                        tenpage[i].style.display='none';
                      }                        
                      tenpage[now_tenpage+1].style.display='inline';
                      now_tenpage++;

                 for(i=0;i<<?=$total_cut_page;?>;i++)
                 {
                   document.getElementsByName('<?=$divstring;?>')[i].style.display='none';
                   document.getElementsByName('whati')[i].color='#666666';
                 }
                 
                  if(nowpage+10><?=$total_cut_page;?>)
                  {
                    nowpage=<?=$total_cut_page;?>-1;                    
                  }
                  else
                  {
                    nowpage+=10;
                  }
                 //nowpage=<?=$total_cut_page;?>-1;
                 for(i=0;i<<?=$total_cut_page;?>;i++)
                 {
                   document.getElementsByName('<?=$divstring;?>')[i].style.display='none';
                   document.getElementsByName('whati')[i].color='#666666';
                 }
                 document.getElementsByName('<?=$divstring;?>')[nowpage].style.display='inline';
                 document.getElementsByName('whati')[nowpage].color='#FF0000';
//                 if(nowpage==0){document.getElementById('forprevious').style.display='none';}else{document.getElementById('forprevious').style.display='inline';}
//                 if(nowpage==<?=$total_cut_page;?>-1){document.getElementById('fornext').style.display='none';}else{document.getElementById('fornext').style.display='inline';}
                 document.getElementById('showpage').innerHTML='第 '+(nowpage+1)+' 頁';;                 

                if(now_tenpage+1==<?=ceil($total_cut_page/10);?>)
                {
                  downten.style.display='none';
                }
                else
                {   
                  downten.style.display='inline';
                }
                if(now_tenpage==0)
                {
                  document.getElementById('upten').style.display='none';
                }
                else
                {
                  document.getElementById('upten').style.display='inline';
                }
                
                 "
                <?
                   echo ">下十頁</a>";
//放入下十頁-end

              echo "&nbsp;&nbsp;";




              echo "<a href='#showtitle' onClick=";
                ?>
                "for(i=0;i<<?=$total_cut_page;?>;i++)
                 {
                   document.getElementsByName('<?=$divstring;?>')[i].style.display='none';
                   document.getElementsByName('whati')[i].color='#666666';
                 }
                 nowpage=<?=$total_cut_page;?>-1;
                 document.getElementsByName('<?=$divstring;?>')[<?=$total_cut_page;?>-1].style.display='inline';
                 document.getElementsByName('whati')[nowpage].color='#FF0000';
//                 if(nowpage==0){document.getElementById('forprevious').style.display='none';}else{document.getElementById('forprevious').style.display='inline';}
//                 if(nowpage==<?=$total_cut_page;?>-1){document.getElementById('fornext').style.display='none';}else{document.getElementById('fornext').style.display='inline';}
                 document.getElementById('showpage').innerHTML='第 '+(nowpage+1)+' 頁';;



                      for(i=0;i<<?=ceil($total_cut_page/10);?>;i++)
                      {
                        tenpage[i].style.display='none';
                      }
                      tenpage[<?=ceil($total_cut_page/10);?>-1].style.display='inline';
                      downten.style.display='none';
                      if(<?=ceil($total_cut_page/10);?>-1!=0)
                      {
                        document.getElementById('upten').style.display='inline';
                      }
                      now_tenpage=<?=ceil($total_cut_page/10);?>-1;


                 "
                <?                
                echo ">最末頁</a>";

                



//顯示總共幾頁
                echo " (總共有...".$total_cut_page."頁)";
//顯示總共幾頁

                echo "<br>";











              echo "<br>";
              echo "<span id=showpage name=showpage class=style6>第 1 頁</span>";
              echo "</span></center>";
              echo "</td>";
              echo "</tr>";
              echo "</table>";
              echo "<script>
//                 if(nowpage==0){document.getElementById('forprevious').style.display='none';}else{document.getElementById('forprevious').style.display='inline';}
//                 if(nowpage==".$total_cut_page."-1){document.getElementById('fornext').style.display='none';}else{document.getElementById('fornext').style.display='inline';}
                 document.getElementById('showpage').innerHTML='第 '+(nowpage+1)+' 頁';
                 document.getElementsByName('whati')[nowpage].color='#FF0000';
                 if(nowpage<10){document.getElementById('upten').style.display='none';}
                      if(".ceil($total_cut_page/10)."-1!=0)
                      {
                        downten.style.display='inline';
                      }
                      else
                      {
                        downten.style.display='none';
                      }

                    </script>";
              break;
    }    
  }
  /*
  //分頁的範例
  for($i=0;$i<20;$i++)
  {
    for($j=0;$j<10;$j++)
    {
      $a[$i][$j]=($i+$j);      
    }
  }
  //有限列$a了
  showArrayStyleT($a,'table','table');
  showcutarray($a,8,'囧','normal');
  //分頁的範例
  */
  
  ///////////////////////////////GD驗證////////////////////////////////
/**
 * 利用 GD 動態生成登入驗證的圖片
 *
 * 鑒於每個GD版本出來的效果有一定的差別，請使用附件中的GD.dll，或者選用GD 2.0以上的版本
 *
 * 目前該類庫主要用於登入時生成附帶驗證碼圖片的功能，存儲驗證碼有 Cookies 和 Session 兩種，
 * 生成的圖片支援 PNG / JPG 等，還有可以設定驗證碼的長度，英文字元和數字混合等。
 *
 * @作者         Hessian(solarischan@21cn.com)
 * @版本         1.0
 * @版權所有     Hessian / NETiS
 * @使用授權     GPL（請各位保留Comment）
 * @特別鳴謝     waff（提供了非常特別輸出方式）
 * @開始         2003-11-05
 * @瀏覽         公開
 *
 * 更新記錄
 *
 * ver 1.0 2003-11-05
 * 一個用於生成驗證碼圖片的類庫已經初步完成。
 *
 */


    /**
     * 判斷是否使用 Session。
     *
     * @變量類型  布爾值
     * @開始      1.0
     * @最後修改  1.0
     * @瀏覽      公開
     * @可選值    true / false
     */
     $UseSession = true;

    /**
     * 瀏覽 Session 的 Handle。
     *
     * @變量類型  字元串
     * @開始      1.0
     * @最後修改  1.0
     * @瀏覽      內部
     * @可選值    無
     */
     $_SessionNum = "";

    /**
     * 驗證碼的長度。
     *
     * @變量類型  數字
     * @開始      1.0
     * @最後修改  1.0
     * @瀏覽      公開
     * @可選值    10進制的純數字
     */
     $CodeLength = 0;

    /**
     * 生成的驗證碼是否帶有英文字元。
     *
     * @變量類型  布爾值
     * @開始      1.0
     * @最後修改  1.0
     * @瀏覽      公開
     * @可選值    true / false
     */
     $CodeWithChar = false;

    /**
     * 生成圖片的類型。
     *
     * @變量類型  字元串
     * @開始      1.0
     * @最後修改  1.0
     * @瀏覽      公開
     * @可選值    PNG / JPEG / WBMP / XBM
     */
     $ImageType = "JPEG";

    /**
     * 生成圖片的寬度。
     *
     * @變量類型  10進制數字
     * @開始      1.0
     * @最後修改  1.0
     * @瀏覽      公開
     * @可選值    10進制的純數字
     */
     $ImageWidth = 120;//50

    /**
     * 生成圖片的高度。
     *
     * @變量類型  10進制數字
     * @開始      1.0
     * @最後修改  1.0
     * @瀏覽      公開
     * @可選值    10進制的純數字
     */
     $ImageHeight = 30;//30

    /**
     * 生成後的驗證碼。
     *
     * @變量類型  字元串
     * @開始      1.0
     * @最後修改  1.0
     * @瀏覽      公開
     * @可選值    無
     */

     $AuthResult ="";

    /**
     * 圖片中驗證碼的顏色。
     *
     * @變量類型  數組
     * @開始      1.0
     * @最後修改  1.0
     * @瀏覽      公開
     * @可選值    順序為 R，G，B, 例如：HTML顏色為 '000033' / array(0,0,51)
     */
     $FontColor = array(0, 0, 0);

    /**
     * 圖片的背景色。
     *
     * @變量類型  數組
     * @開始      1.0
     * @最後修改  1.0
     * @瀏覽      公開
     * @可選值    順序為 R，G，B, 例如：HTML顏色為'000033' / array(0,0,51)
     */
     $BGColor = array(0, 0, 0);

    /**
     * 設定背景是否需要透明（注意：只有 PNG 格式支援，如果使用 JPG 格式的話，必須禁止該選項）。
     *
     * @變量類型  布爾值
     * @開始      1.0
     * @最後修改  1.0
     * @瀏覽      公開
     * @可選值    true / false
     */
     $Transparent = false;
    /**
     * 設定是否生成帶噪點的背景。
     *
     * @變量類型  布爾值
     * @開始      1.0
     * @最後修改  1.0
     * @瀏覽      公開
     * @可選值    true / false
     */
     $NoiseBG = false;

    /**
     * 設定生成噪點的字元。
     *
     * @變量類型  字元串
     * @開始      1.0
     * @最後修改  1.0
     * @瀏覽      公開
     * @可選值    任意
     */
     $NoiseChar = "*";

    /**
     * 設定生成多少個噪點字元。
     *
     * @變量類型  10進制的純數字
     * @開始      1.0
     * @最後修改  1.0
     * @瀏覽      公開
     * @可選值    0 - 無限
     */
     $TotalNoiseChar = 50;

    /**
     * 驗證碼在圖片中的左邊距。
     *
     * @變量類型  10進制數字
     * @開始      1.0
     * @最後修改  1.0
     * @瀏覽      公開
     * @可選值    10進制的純數字，範圍：0 - 100
     */
     $JpegQuality = 80;

    /**
     * GenAuth 的構造函數
     *
     * 詳細說明
     * @形參
     * @開始      1.0
     * @最後修改  1.0
     * @瀏覽      公開
     * @返回值    無
     * @throws
     */
    function GenAuth()
    {
    } // 結束 GenAuth 的構造函數


    /**
     * 直接顯示圖片
     *
     * 詳細說明
     * @形參      字元串      $ImageType   設定顯示圖片的格式
     *            10進制數字  $ImageWidth  設定顯示圖片的高度
     *            10進制數字  $ImageHeight 設定顯示圖片的寬度
     * @開始      1.0
     * @最後修改  1.0
     * @瀏覽      公開
     * @返回值    無
     * @throws
     */
    function Show( $ImageType = "", $ImageWidth = "", $ImageHeight = "" )
    {
        global $UseSession;
        global $_SessionNum;
        global $CodeLength;
        global $CodeWithChar;
        global $ImageType;
        global $ImageWidth;
        global $ImageHeight;
        global $AuthResult;
        global $FontColor;
        global $BGColor;
        global $Transparent;
        global $NoiseBG;
        global $NoiseChar;
        global $TotalNoiseChar;
        global $JpegQuality;
        // 生成驗證碼
        if( $CodeWithChar )
            for( $i = 0; $i < $CodeLength; $i++ )
                $AuthResult .= dechex( rand( 1, 15 ) );
        else
            for( $i = 0; $i < $CodeLength; $i++ )
                $AuthResult .= rand( 1, 9 );

        // 檢查有沒有設定圖片的輸出格式，如果沒有，則使用類庫的預設值作為最終結果。
        if ( $ImageType == "" )
            $ImageType = $ImageType;

        // 檢查有沒有設定圖片的輸出寬度，如果沒有，則使用類庫的預設值作為最終結果。
        if ( $ImageWidth == "" )
            $ImageWidth = $ImageWidth;

        // 檢查有沒有設定圖片的輸出高度，如果沒有，則使用類庫的預設值作為最終結果。
        if ( $ImageHeight == "" )
            $ImageHeight = $ImageHeight;

        // 建立圖片流
        $im = imagecreate( $ImageWidth, $ImageHeight );

        // 取得背景色
        list ($bgR, $bgG, $bgB) = $BGColor;

        // 設定背景色
        $background_color = imagecolorallocate( $im, $bgR, $bgG, $bgB );

        // 取得文字顏色
        list ($fgR, $fgG, $fgB) = $FontColor;

        // 設定字型顏色
        $font_color = imagecolorallocate( $im, $fgR, $fgG, $fgB );

        // 檢查是否需要將背景色透明
        if ( $Transparent ) {
            ImageColorTransparent( $im, $background_color );
        }

        if( $NoiseBG )
        {
//            ImageRectangle($im, 0, 0, $ImageHeight - 1, $ImageWidth - 1, $background_color);//先成一黑色的矩形把圖片包圍

            //下面該生成雪花背景了，其實就是在圖片上生成一些符號
            for ( $i = 1; $i <= $TotalNoiseChar; $i++ )
                imageString( $im, 1, mt_rand( 1, $ImageWidth ), mt_rand( 1, $ImageHeight ), $NoiseChar, imageColorAllocate( $im, mt_rand( 200, 255 ), mt_rand( 200,255 ), mt_rand( 200,255 ) ) );
        }

        // 為了區別於背景，這裡的顏色不超過200，上面的不小於200
        for ( $i = 0; $i < strlen( $AuthResult ); $i++ ){
          //mt_rand(3,5)
            //imageString( $im, mt_rand(3,5), $i*$ImageWidth/strlen( $AuthResult )+mt_rand(1,5), mt_rand(1, $ImageHeight/2), $AuthResult[$i], imageColorAllocate( $im, mt_rand(0, 100), mt_rand(0, 150), mt_rand(0, 200) ) );
            $tt=imageColorAllocate( $im, mt_rand(150, 255), mt_rand(150, 255), mt_rand(100, 255) ); //字型顏色設定
            ImageTTFText ($im, 18, mt_rand(-45,45), 12+$i*$ImageWidth/strlen( $AuthResult )+mt_rand(1,5),$ImageHeight*5/7, $tt, "photo/DFFN_Y7.TTC",$AuthResult[$i]);
        }

        // 檢查輸出格式
        if ( $ImageType == "PNG" ) {
            header( "Content-type: image/png" );
            imagepng( $im );
        }

        // 檢查輸出格式
        if ( $ImageType == "JPEG" ) {
            header( "Content-type: image/jpeg" );
            imagejpeg( $im, null, $JpegQuality );
        }

        // 釋放圖片流
        imagedestroy( $im );

    } // 結束 Show 函數
  //自動產生分頁排序說明
  //版本1.0
  //開發者:羽山秋人
  //時間:2007414
  //第二版修正於:2007416
  //使用方法
  /* array_page(
        $totals_rows  $資料庫算出的總筆數,
                $page         $目前的頁碼常用
                $p            $每頁顯示的筆數
                $px           $每頁要顯示多少個【第 xx 頁】
                $new_Link     $跳頁用的網頁帶入值  ---> ?以後原本傳的值

                P.S:需自行在 SQL 語法最後加上 limit ".($page*$p).",".$p;
                P.S:$p、$px、$page 請加注在 檔案開頭 以上
          #mysql      
            $SQL_ROWS="SELECT COUNT(*) AS `COUNTER` FROM ({$SQL}) a";
            $totals_rows=mysql_result(mysql_query($SQL_ROWS),0,0);
            $SQL.=sprintf(" LIMIT %d,%d",($page*$p),$p);  
          
          #pdo
            $SQL_ROWS="SELECT COUNT(*) AS `COUNTER` FROM ({$SQL}) a";
            $ra_counts=selectSQL($SQL_ROWS); 
            $totals_rows=$ra_counts[0]['COUNTER'];
            $SQL.=sprintf(" LIMIT %d,%d",($page*$p),$p);  

          $p=10;  //每頁顯示５筆
          $px=5;   //每頁顯示跳頁用的５筆
            if(isset($page))
             {
              $page=$page;
            }
            else if(isset($_GET['page']))
            {
              $page=$_GET['page'];
            }
            else
            {
              $page=0;
            }                
  */            
  function array_page($totals_rows,$page,$p,$px,$new_Link,$mode='normal',$spandiv='')
  {
        //傳說中的分頁
    //$p=5; // 每頁顯示5筆
    //$px=5; //每頁限制最多5頁，超過就用「下5頁」上5頁
      global $base_url;
       switch($mode)
       {
      case 'normal':          
          //自動算幾頁
          $p=(int)$p;
          $page=(int)$page;
          $pageurl='';
          $totals_rows=(int)$totals_rows;
          $totals_page=ceil($totals_rows/$p);
          $pre_page =$page-1;    //上一頁
          $next_page=$page+1;    //下一頁                                 
          if($page==0)
          {
            $pageurl.=sprintf("%s | %s | ",__('首頁'),__('上一頁'));  
          } 
          else
          {
            $pageurl.=sprintf("
            <a href='%s'>%s</a>
             | 
            <a href='%s'>%s</a>
             | ",
             "?{$new_Link}&page=0",__('首頁'),
             "?{$new_Link}&page={$pre_page}",__('上一頁')
            );
          }
          if ($page==$totals_page-1 || $totals_page==0) {  //如果$GETS['page']==$pagenum　当前页 等于 总页数说明到了最后一页，　或　$pagenum==0　总条数等于０，就不显示连接
            $pageurl.=sprintf("%s | %s",__('下一頁'),__('最後頁'));
          } else {
            $pageurl.=sprintf("
            <a href='%s'>%s</a>
             | 
            <a href='%s'>%s</a>
            ",
                "?{$new_Link}&page={$next_page}",__('下一頁'),
                "?{$new_Link}&page=".($totals_page-1),__('最後頁')
            );
          }
          if($totals_page!=0)
          {
            $pageurl.=" | <select class='page_select'>";
            for($i=0;$i<$totals_page;$i++)
            {
              $v=$i+1;
              $pageurl.=sprintf("<option value='{$i}'>%s</option>",__("第 {$v} 頁"));
            }      
            $pageurl.="</select>";  
          }
          echo $pageurl;        
          ?>
          <script language="javascript">
            $(document).ready(function(){
              $(".page_select").val('<?=jsAddSlashes($page);?>');
              $(".page_select").change(function(){
                var page=parseInt($(this).val());
                location.href="?<?=$new_Link;?>&page="+page;
              });
            });
          </script>
          <?php          
        break;
      case 'ajax':
  
          $page_range_start=floor($page/$px)*$px;
          $page_range_end=$page_range_start+$px;
          //自動算幾頁
          $totals_page=ceil($totals_rows/$p);   
          if($page_range_end>$totals_page)
          {
            $page_range_end=$totals_page;
          }
          //echo $page_range_start;
          //echo "<br>";
          //echo $page_range_end;  
          //echo "<br>";   
                          
          if($page-($page%$px)>=$px)
          {
              echo "【<a href='javascript:;' onClick=\"makeRequest('?','".$new_Link."&page=".($page-$px)."','".$spandiv."');\">上".$px."頁</a>】　　　　　　　　　　　　　";
          }
          if(($page-$page%$px)<$totals_page-$px)
          {
            if(($page+$px)>=$totals_page) //修正加上page的頁碼超過最終頁碼 2007/4/16
            {
              $temp=$totals_page-1;
            }
            else
            {
              $temp=$page+$px;
            }
              echo "【<a href='javascript:;' onClick=\"makeRequest('?','".$new_Link."&page=".($temp)."','".$spandiv."');\">下".$px."頁</a>】";
          }
          echo "<br>";
          for($i=$page_range_start;$i<$page_range_end;$i++)        
          {
            if($page==$i)
              echo "【第 ".($i+1)." 頁】";
            else
              echo "【<a href='javascript:;' onClick=\"makeRequest('?','".$new_Link."&page=".$i."','".$spandiv."');\">第 ".($i+1)." 頁</a>】";
          }                
      echo "<br><div align=right>第【".($page+1)."】頁</div>";
          echo "合計共【".$totals_rows."】筆／共【".$totals_page."】頁";
          //分頁結束       
        break;
      case 'for_3wa':
          $page_range_start=floor($page/$px)*$px;
          $page_range_end=$page_range_start+$px;
          //自動算幾頁
          $totals_page=ceil($totals_rows/$p);
          if($page_range_end>$totals_page)
          {
            $page_range_end=$totals_page;
          }
          //echo $page_range_start;
          //echo "<br>";
          //echo $page_range_end;
          //echo "<br>";
          ?>
          <a href="?<?=$new_Link;?>&page=0"><img src="<?="{$base_url}";?>/pic/p1.gif" border="0"  align="absmiddle"></a>
          <?
          if($page-($page%$px)>=$px)
          {
              //echo "【<a href='?".$new_Link."&page=".($page-$px)."'>上".$px."頁</a>】　　>　　
              echo "<a href='?".$new_Link."&page=".($page-$px)."'><img src='{$base_url}/pic/p2.gif' border='0'  align='absmiddle'/>";
          }
  
          for($i=$page_range_start;$i<$page_range_end;$i++)
          {
            if($page==$i)
              echo "<span class='download_link'>".($i+1)."</span>&nbsp;";
            else
              echo "<a href='?".$new_Link."&page=".$i."' class='download_link'>".($i+1)."</a>";
            if($i!=$page_range_end-1)
            {
              echo "<span class='contact_top style1'>│</span>&nbsp;";
            }
          }
          if(($page-$page%$px)<$totals_page-$px)
          {
            if(($page+$px)>=$totals_page) //修正加上page的頁碼超過最終頁碼 2007/4/16
            {
              $temp=$totals_page-1;
            }
            else
            {
              $temp=$page+$px;
            }
            //echo "【<a href='?".$new_Link."&page=".($temp)."'>下".$px."頁</a>】";
            echo "<a href='?".$new_Link."&page=".($temp)."'><img src='{$base_url}/pic/p3.gif' border='0'  align='absmiddle'/></a>";
          }
          //echo "<br>";
          $lastpage=(($totals_page-1)==-1)?$totals_page:$totals_page-1;
          echo "&nbsp;<a href='?".$new_Link."&page={$lastpage}'><img src='{$base_url}/pic/p4.gif' border='0'  align='absmiddle'/></a>";
          //echo "<br><div align=right>第【".($page+1)."】頁</div>";
    //      echo "合計共【".$totals_rows."】筆／共【".$totals_page."】頁";
          //分頁結束
        break;
              
      } 
  }
  function get_include_contents($filename,$newsize_w,$file_name) { //取得include的內容
      if (is_file($filename)) {
          ob_start();
          include $filename;
          $contents = ob_get_contents();
          ob_end_clean();
          return $contents;
      }
      return false;
  }  
  function get_include_contents_3wa($filename) { //取得include的內容
      if (is_file($filename)) {
          ob_start();
          include $filename;
          $contents = ob_get_contents();
          ob_end_clean();
          return $contents;
      }
      return false;
  }
    function SortDataSet($aArray, $sField, $bDescending = false)  //排序
    {
        $bIsNumeric = IsNumeric($aArray);
        $aKeys = array_keys($aArray);
        $nSize = sizeof($aArray);
    
        for ($nIndex = 0; $nIndex < $nSize - 1; $nIndex++)
        {
            $nMinIndex = $nIndex;
            $objMinValue = $aArray[$aKeys[$nIndex]][$sField];
            $sKey = $aKeys[$nIndex];
    
            for ($nSortIndex = $nIndex + 1; $nSortIndex < $nSize; ++$nSortIndex)
            {
                if ($aArray[$aKeys[$nSortIndex]][$sField] < $objMinValue)
                {
                    $nMinIndex = $nSortIndex;
                    $sKey = $aKeys[$nSortIndex];
                    $objMinValue = $aArray[$aKeys[$nSortIndex]][$sField];
                }
            }
    
            $aKeys[$nMinIndex] = $aKeys[$nIndex];
            $aKeys[$nIndex] = $sKey;
        }
    
        $aReturn = array();
        for($nSortIndex = 0; $nSortIndex < $nSize; ++$nSortIndex)
        {
            $nIndex = $bDescending ? $nSize - $nSortIndex - 1: $nSortIndex;
            $aReturn[$aKeys[$nIndex]] = $aArray[$aKeys[$nIndex]];
        }
    
        return $bIsNumeric ? array_values($aReturn) : $aReturn;
    }
    function getgoogle_i18n($outspan,$divs)
    {
      ?>
      <select id=layout_lang_<?=$divs;?> name=layout_lang[] onChange="for(i=0;i<document.getElementsByName('layout_lang[]').length;i++){document.getElementsByName('layout_lang[]')[i].value=this.value;makeRequest_googleapi('http://<?=$_SERVER['SERVER_NAME'];?>/inc/googleapi/googletranslate.php','layout_lang='+this.value+'&contents='+encodeURIComponent(document.getElementsByName('<?=$outspan;?>[]')[i].innerHTML),'<?=$outspan;?>_'+i);}">
        <option value="zh_TW">繁體中文</option>
        <option value="zh_CN">简体中文</option>
        <option value="en">English</option>
        <option value="ja">日本語</option>
        <option value="fr">French</option>
        <option value="de">Deutsch</option>
        <option value="ko">韓語</option>
          <option value="bg">Български</option>
        <option value="el">Ελληνικά</option>
        <option value="hi">हिनदी</option>
        <option value="ru">Русский</option>
        <option value="it">Italiano</option>
        <option value="sr">Српски</option>
        <option value="vi">Từ ngữ Tiếng Việt</option>
      </select>
      <?
    }

    function IsNumeric($aArray)
    {
        $aKeys = array_keys($aArray);
        for ($nIndex = 0; $nIndex < sizeof($aKeys); $nIndex++)
        {
            if (!is_int($aKeys[$nIndex]) || ($aKeys[$nIndex] != $nIndex))
            {
                return false;
            }
        }
    
        return true;
    }        
    function isint( $mixed )
    {
      return ( preg_match( '/^\d*$/'  , $mixed) == 1 );
    }
    class cURL { 
    var $headers; 
    var $user_agent; 
    var $compression; 
    var $cookie_file; 
    var $proxy; 
    function cURL($cookies=TRUE,$cookie='/tmp/cookies.txt',$compression='gzip',$proxy='') { 
    $this->headers[] = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg'; 
    $this->headers[] = 'Connection: Keep-Alive'; 
    $this->headers[] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8'; 
    $this->user_agent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)'; 
    $this->compression=$compression; 
    $this->proxy=$proxy; 
    $this->cookies=$cookies; 
    if ($this->cookies == TRUE) $this->cookie($cookie); 
    } 
    function cookie($cookie_file) { 
    if (file_exists($cookie_file)) { 
    $this->cookie_file=$cookie_file; 
    } else { 
    fopen($cookie_file,'w') or $this->error('The cookie file could not be opened. Make sure this directory has the correct permissions'); 
    $this->cookie_file=$cookie_file; 
    @fclose($this->cookie_file); 
    } 
    } 
    function get($url) { 
    $process = curl_init($url); 
    curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers); 
    curl_setopt($process, CURLOPT_HEADER, 0); 
    curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent); 
    if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file); 
    if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file); 
    curl_setopt($process,CURLOPT_ENCODING , $this->compression); 
    curl_setopt($process, CURLOPT_TIMEOUT, 1);
    //if ($this->proxy) curl_setopt($cUrl, CURLOPT_PROXY, 'proxy_ip:proxy_port');
    if ($this->proxy) curl_setopt($process, CURLOPT_PROXY, $this->proxy);
    curl_setopt($process, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1); 
    $return = curl_exec($process); 
    curl_close($process); 
    return $return; 
    } 
    function post($url,$data) { 
    $process = curl_init($url); 
    curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers); 
    curl_setopt($process, CURLOPT_HEADER, 0); 
    curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent); 
    if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file); 
    if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file); 
    curl_setopt($process, CURLOPT_ENCODING , $this->compression); 
    curl_setopt($process, CURLOPT_TIMEOUT, 1);
    if ($this->proxy) curl_setopt($process, CURLOPT_PROXY, $this->proxy); 
    curl_setopt($process, CURLOPT_POSTFIELDS, $data); 
    curl_setopt($process, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1); 
    curl_setopt($process, CURLOPT_POST, 1); 
    $return = curl_exec($process); 
    curl_close($process); 
    return $return; 
    } 
    function error($error) { 
    echo "<center><div style='width:500px;border: 3px solid #FFEEFF; padding: 3px; background-color: #FFDDFF;font-family: verdana; font-size: 10px'><b>cURL Error</b><br>$error</div></center>"; 
    die; 
    } 
    }
  //網站加密方案 ----------------------------start
   
  function utf16urlencode($str)
  {
    $str = mb_convert_encoding($str, 'UTF-16', 'UTF-8');
    $out = '';
    $counts=mb_strlen($str, 'UTF-16');
    for ($i = 0; $i < $counts; $i++) 
    {
        $out .= '%u'.bin2hex(mb_substr($str, $i, 1, 'UTF-16'));
    }
    return $out;
  }
  function web_encode2raw($str)
  {            
    //$lang_strings=$_SERVER['HTTP_ACCEPT_LANGUAGE'];
    //$lang_array=explode(",",$lang_strings);
    //$dbcconv_lang=$lang_array[0];
    
    //if (preg_match("/cn/i",$dbcconv_lang))
    //{
    //  $str=dbcconv($str, 1);
    //}
           
    $str=utf16urlencode($str);
    echo "<script language=\"javascript\">document.write(unescape('{$str}'));</script>";
  }  
  //$str='%u5927%u5BB6%u597D%uFF0C<br />\u8FD9\u662F\u6D4B\u8BD5\u6587\u672C\uFF01';      
  //echo uni_decode($str); //    
  function uni_decode($s) {      
      preg_match_all('/\&\#([0-9]{2,5})\;/', $s, $html_uni);      
      preg_match_all('/[\\\%]u([0-9a-f]{4})/ie', $s, $js_uni);      
      $source = array_merge($html_uni[0], $js_uni[0]);      
      $js = array();      
      for($i=0;$i<count($js_uni[1]);$i++) {      
          $js[] = hexdec($js_uni[1][$i]);      
      }      
      $utf8 = array_merge($html_uni[1], $js);      
      $code = $s;      
      for($j=0;$j<count($utf8);$j++) {      
          $code = str_replace($source[$j], unicode2utf8($utf8[$j]), $code);      
      }      
      return $code;//$s;//preg_replace('/\\\u([0-9a-f]{4})/ie', "chr(hexdec('\\1'))",  $s);      
  }      
       
  function unicode2utf8($c) {      
      $str="";      
      if ($c < 0x80) {      
           $str.=chr($c);      
      } else if ($c < 0x800) {      
           $str.=chr(0xc0 | $c>>6);      
           $str.=chr(0x80 | $c & 0x3f);      
      } else if ($c < 0x10000) {      
           $str.=chr(0xe0 | $c>>12);      
           $str.=chr(0x80 | $c>>6 & 0x3f);      
           $str.=chr(0x80 | $c & 0x3f);      
      } else if ($c < 0x200000) {      
           $str.=chr(0xf0 | $c>>18);      
           $str.=chr(0x80 | $c>>12 & 0x3f);      
           $str.=chr(0x80 | $c>>6 & 0x3f);      
           $str.=chr(0x80 | $c & 0x3f);      
      }      
      return $str;      
  }   
  //網站加密方案 ----------------------------end
  function num_2_Chinese($Number=0)
  {
      $Chinese_Number=Array("零","一","二","三","四","五","六","七","八","九");
      $Number_array=explode('.',$Number);
      $tmps="";
      $Number=$Number_array[0];

      $tmps="";        
      if(strlen($Number_array[1])!=0)
      {
        $tmps.='點';
      }        
      for($i=0;$i<strlen($Number_array[1]);$i++)
      {
        $tmps.=$Chinese_Number[$Number_array[1][$i]];
      }              
      If(preg_match('/[[:digit:]]/i',$Number)):
        $zero = 0;
        //$Chinese_Number=Array("零","壹","貳","參","肆","伍","陸","柒","捌","玖");
        
        $Chinese_Name  =Array("","","拾","佰","仟","萬",
                                    "拾","佰","仟","億",
                                    "拾","佰","仟","兆",
                                    "拾","佰","仟","京",
                                    "拾","佰","仟","垓",
                                    "拾","佰","仟","禾予",
                                    "拾","佰","仟","穰",
                                    "拾","佰","仟","溝",
                                    "拾","佰","仟","澗",
                                    "拾","佰","仟","正",
                                    "拾","佰","仟","載",
                                    "拾","佰","仟","極",
                                    "拾","佰","仟","恒河砂",
                                    "拾","佰","仟","阿僧祇");
                                          
        $Number_Strlen =Strlen($Number);
        $Number = (String) $Number;        
        For($i=0 ; $i < $Number_Strlen ; $i++):
            Switch($Number[$i]):
              Case 0;
                  If ($zero == 0):
                    $j    = 0;
                    $check = 0;
                    For ($j=$i; $j<$Number_Strlen; $j++):
                        If ($Number[j] != '0'):
                          $check = 1;
                          break;
                        EndIf;
                    EndFor;
                    If ($check == 1 && $Number[Strlen($Number)-1]!='0'):
                            $Chinese.=$Chinese_Number[$Number[$i]];
                    EndIf;
                    $zero = 1;
                  Endif;
              Break;
              Case 1;                            
                  If ($zero == 1) $zero = 0;
                  If (!($Number_Strlen == 2 && $i == 0))
                    $Chinese.=$Chinese_Number[$Number[$i]];
              Break;
              Case 2;
              Case 3;
              Case 4;
              Case 5;
              Case 6;
              Case 7;
              Case 8;
              Case 9;
                  if ($zero == 1) $zero = 0;
                    $Chinese.=$Chinese_Number[$Number[$i]];
              Break;
            EndSwitch;
            $Chinese.=($Number[$i] != '0') ? $Chinese_Name[$Number_Strlen - $i] : "";
        EndFor;
      Else:
        $Chinese = '零'.$tmps;
      EndIf;
      
      Return ($Chinese) ? $Chinese.$tmps : '零'.$tmps;
  }
  function getFields_From_Id($table,$id,$field)
  {
    $SQL="
          SELECT DISTINCT `{$field}` 
            FROM `{$table}` 
              WHERE 
                1=1
                AND `id`='{$id}' 
              LIMIT 0,1;";
    $ra=selectSQL($SQL);
    if(count($ra)!=0)
    {
      return $ra[0][$field];
    }
    else
    {
      return "";
    }
  }
  function mysql_field_array( $query ) { 
      $field = mysql_num_fields( $query );
      $names=ARRAY();
      for ( $i = 0; $i < $field; $i++ ) {     
        array_push($names,mysql_field_name( $query, $i ));     
      }    
      return $names; 
  }
  function escape($str){
    $sublen=strlen($str);
    $reString="";
    for ($i=0;$i<$sublen;$i++){
      if(ord($str[$i])>=127){
          $tmpString=bin2hex(iconv("UTF-8","ucs-2",substr($str,$i,2)));    //此处GBK为目标代码的编码格式，请实际情况修改

          if (!preg_match("/WIN/",PHP_OS)){
              $tmpString=substr($tmpString,2,2).substr($tmpString,0,2);
          }
          $reString.="%u".$tmpString;
          $i++;
      } else {
          $reString.="%".dechex(ord($str[$i]));
      }
    }
    return $reString;
  }


  function unescape($str) {
    $str = rawurldecode($str);
    preg_match_all("/%u.{4}|&#x.{4};|&#d+;|.+/U",$str,$r);
    $ar = $r[0];
    foreach($ar as $k=>$v) {
      if(substr($v,0,2) == "%u")
               $ar[$k] = iconv("UCS-2","GBK",pack("H4",substr($v,-4)));
      elseif(substr($v,0,3) == "&#x")
               $ar[$k] = iconv("UCS-2","GBK",pack("H4",substr($v,3,-1)));
      elseif(substr($v,0,2) == "&#") {
               $ar[$k] = iconv("UCS-2","GBK",pack("n",substr($v,2,-1)));
      }
    }
    return join("",$ar);
  }
  /*function escape_old($url)
  {
    $url=str_replace("%2F", "/", urlencode($url));
    return $url;
  } */
  function in_array_like($referencia,$array){
    foreach($array as $ref){
      if (stristr($referencia,$ref)){         
        return true;
      }
    }
    return false;
  }
  function recurse_copy($src,$dst) 
  {
    $dir = opendir($src);
    mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                recurse_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
  }
  
  function fullToHalf($str, $encode='UTF-8') {
    //全型轉半型
      if ($encode != 'UTF8') {
          $str = mb_convert_encoding($str, 'UTF-8', $encode);
      }
      $ret='';
      for ($i=0; $i < strlen($str); $i++) {
          $s1 = $str[$i];
          // 判斷 $c 第八位是否為 1 (漢字）
          if( ($c=ord($s1)) & 0x80 ) { 
              $s2 = $str[++$i];
              $s3 = $str[++$i];
              $c = (($c & 0xF) << 12) | ((ord($s2) & 0x3F) << 6) | (ord($s3) & 0x3F);
              if ($c == 12288) {
                  $ret .= ' ';
              } elseif ($c > 65280 && $c < 65375) {
                  $c -= 65248;
                  $ret .= chr($c);
              } else {
                  $ret .= $s1 . $s2 . $s3;
              } 
          } else {
              $ret .= $str[$i];
          }
      }
      return ($encode== 'UTF-8' ? $ret : mb_convert_encoding($ret, $encode, 'UTF-8')); 
  }     
  function url_exists($url) {
    // Version 4.x supported
    $handle   = curl_init($url);
    if (false === $handle)
    {
        return false;
    }
    curl_setopt($handle, CURLOPT_HEADER, false);
    curl_setopt($handle, CURLOPT_FAILONERROR, true);  // this works
    curl_setopt($handle, CURLOPT_NOBODY, true);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);
    $connectable = curl_exec($handle);
    curl_close($handle);
    return $connectable;
  }
  function is_chinese($str){
    //檢查是否為中文
    //在gb2312编碼中,正規表為: '/['.chr(0xa1)."-".chr(0xff).']/'
    //在utf-8编碼中,正規表為: '/[\x{4e00}-\x{9fa5}]/u'
    //***********************************************
    //原创作者：易心 QQ 343931221
    //个人网站：www.ex123.net
    //作品由易心原创，转载请保留此版权信息。
    //http://exblog.ex123.net/html/blogview-81-4057_1.html
    //***********************************************    
    $pattern='/[\x{4e00}-\x{9fa5}]/u';
    return (preg_match($pattern,$str))? true:false;
  }
  /**
  * word-sensitive substring function with html tags awareness
  * @param text The text to cut
  * @param len The maximum length of the cut string
  * @returns string
  * 切字的，不知道好不好用  
  **/
  function mb_substrws( $text, $len=180 ) {  
      if( (mb_strlen($text) > $len) ) {  
          $whitespaceposition = mb_strpos($text," ",$len)-1;  
          if( $whitespaceposition > 0 ) {
              $chars = count_chars(mb_substr($text, 0, ($whitespaceposition+1)), 1);
              if ($chars[ord('<')] > $chars[ord('>')])
                  $whitespaceposition = mb_strpos($text,">",$whitespaceposition)-1;
              $text = mb_substr($text, 0, ($whitespaceposition+1));
          }
          // close unclosed html tags
          if( preg_match_all("|<([a-zA-Z]+)|",$text,$aBuffer) ) {
  
              if( !empty($aBuffer[1]) ) {
  
                  preg_match_all("|</([a-zA-Z]+)>|",$text,$aBuffer2);
  
                  if( count($aBuffer[1]) != count($aBuffer2[1]) ) {
  
                      foreach( $aBuffer[1] as $index => $tag ) {
  
                          if( empty($aBuffer2[1][$index]) || $aBuffer2[1][$index] != $tag)
                              $text .= '</'.$tag.'>';
                      }
                  }
              }
          }
      }
      return $text;
  }
  function get_codereference_array($MASTER)
  {
      $SQL=sprintf("SELECT DISTINCT `id`,`contents` FROM `%s` WHERE `MASTER`='%s' ORDER BY `sort` ASC",'codereference',$MASTER);
      $res=mysql_query($SQL) or die("查詢失敗:$SQL");
      $res_array=resulttoarray($res);
      return $res_array;
  }
  function get_codereference_single_array($MASTER)
  {
      $SQL=sprintf("SELECT DISTINCT `contents` FROM `%s` WHERE `MASTER`='%s' ORDER BY `sort` ASC",'codereference',$MASTER);
      $res=mysql_query($SQL) or die("查詢失敗:$SQL");
      $res_array=resulttoarray($res);
      $a=Array();
      for($i=0,$max=count($res_array);$i<$max;$i++)
      {
        $a[]=$res_array[$i][0];
      }
      return $a;
  }  
  function get_codereference_id($MASTER,$contents)
  {
      $SQL=sprintf("SELECT DISTINCT `id` FROM `%s` WHERE `MASTER`='%s' AND `contents`='%s'",'codereference',$MASTER,$contents);
      $res=mysql_query($SQL) or die("查詢失敗:$SQL");
      $res_array=resulttoarray($res);
      if(count($res_array)!=0)
      {
        return $res_array[0][0];
      }
      else
      {
        return "";
      }
  }
  function get_codereference_contents($id)
  {
      $SQL=sprintf("SELECT DISTINCT `contents` FROM `%s` WHERE `id`='%s'",'codereference',$id);
      $res=mysql_query($SQL) or die("查詢失敗:$SQL");
      $res_array=resulttoarray($res);
      if(count($res_array)!=0)
      {
        return $res_array[0][0];
      }
      else
      {
        return "";
      }
  }
  function get_field_from_id($table,$id,$fieldname)
  {
      $SQL="
          SELECT DISTINCT `{$fieldname}` 
            FROM `{$table}` 
              WHERE 
                1=1
                AND `id`='{$id}' 
              LIMIT 0,1;";
      $ra=selectSQL($SQL);
      if(count($ra)!=0)
      {
        return $ra[0][$fieldname];
      }
      else
      {
        return "";
      }              
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
  function location_reload()
  {
    ?>
    <script language="javascript">
      location.reload();
    </script>
    <?  
  }
  function location_href($input)
  {
    ?>
    <script language="javascript">
      location.href="<?=$input;?>";
    </script>
    <?
  }
  function location_replace($input)
  {
    ?>
    <script language="javascript">
      location.replace("<?=$input;?>");
    </script>
    <?
  }  
  function history_go()
  {
    ?>
    <script language="javascript">
      history.go(-1);
    </script>
    <?
  }
  function history_back(){
    ?>
    <script language="javascript">
      history.back();
    </script>
    <?php
  }
  
  function notempty($inputs)
  {
    return (trim($inputs)=='')?false:true;
  } 
  function return_not_empty_array($array)
  {  
    return array_values(array_filter($array,"notempty"));
  }
  function str_replace_once($search, $replace, $subject) {
    $firstChar = strpos($subject, $search);
    if($firstChar !== false) {
        $beforeStr = substr($subject,0,$firstChar);
        $afterStr = substr($subject, $firstChar + strlen($search));
        return $beforeStr.$replace.$afterStr;
    } else {
        return $subject;
    }
  }
  function file_get_contents_post($url,$posts)
  {
    $opts = stream_context_create(array (
      'http'=>array(
         'method'=>"POST",
         'header'=>"Content-type: application/x-www-form-urlencoded\r\nReferer:{$url}",
         'content'=>(is_array($posts))?http_build_query($posts):$posts
      )
    ));
    return file_get_contents("{$url}",false,$opts);
  }


  function is_string_like($data,$find_string){
/*
  is_string_like($data,$fine_string)

  $mystring = "Hi, this is good!";
  $searchthis = "%thi% goo%";

  $resp = string_like($mystring,$searchthis);


  if ($resp){
     echo "milike = VERDADERO";
  } else{
     echo "milike = FALSO";
  }

  Will print:
  milike = VERDADERO

  and so on...

  this is the function:
*/
    $tieneini=0;
    if($find_string=="") return 1;
    $vi = explode("%",$find_string);
    $offset=0;
    for($n=0,$max_n=count($vi);$n<$max_n;$n++){
        if($vi[$n]== ""){
            if($vi[0]== ""){
                   $tieneini = 1;
            }
        } else {
            $newoff=strpos($data,$vi[$n],$offset);
            if($newoff!==false){
                if(!$tieneini){
                    if($offset!=$newoff){
                        return false;
                    }
                }
                if($n==$max_n-1){
                    if($vi[$n] != substr($data,strlen($data)-strlen($vi[$n]), strlen($vi[$n]))){
                        return false;
                    }

                } else {
                    $offset = $newoff + strlen($vi[$n]);
                 }
            } else {
                return false;
            }
        }
    }
    return true;
  }
 
  function stripallslashes($string) {
    while(strchr($string,'\\')) {
        $string = stripslashes($string);
    }
    return $string;
  }

  function get_between_new($source, $beginning, $ending, $init_pos=0) {
      $beginning_pos = strpos($source, $beginning, $init_pos);
      $middle_pos = $beginning_pos + strlen($beginning);
      $ending_pos = strpos($source, $ending, $beginning_pos + 1);
      $middle = substr($source, $middle_pos, $ending_pos - $middle_pos);
      return $middle;
  }  
  //判斷字串是否為utf8
  function  is_utf8($str)  {
    $i=0;
    $len  =  strlen($str);

    for($i=0;$i<$len;$i++)  {
        $sbit  =  ord(substr($str,$i,1));
        if($sbit  <  128)  {
            //本字節為英文字符，不與理會
        }elseif($sbit  >  191  &&  $sbit  <  224)  {
            //第一字節為落於192~223的utf8的中文字(表示該中文為由2個字節所組成utf8中文字)，找下一個中文字
            $i++;
        }elseif($sbit  >  223  &&  $sbit  <  240)  {
            //第一字節為落於223~239的utf8的中文字(表示該中文為由3個字節所組成的utf8中文字)，找下一個中文字
            $i+=2;
        }elseif($sbit  >  239  &&  $sbit  <  248)  {
            //第一字節為落於240~247的utf8的中文字(表示該中文為由4個字節所組成的utf8中文字)，找下一個中文字
            $i+=3;
        }else{
            //第一字節為非的utf8的中文字
            return  0;
        }
    }
    //檢查完整個字串都沒問體，代表這個字串是utf8中文字
    return  1;
  }  
  function my_money_format($data,$n=0) {
    /*
    from : http://herolin.twbbs.org/entry/better-than-number-format-for-php
    傳入值為$data 就是你要轉換的數值，$n就是小數點後面的位數
    除了排除這個問題，在使用number_format時發現如果設定小數位數四位，
    如不足四數就會補零 。例如: 100000.12 會顯示  100,000.1200 ，
    所以小弟也順便調整，可以把後面的零給取消掉。
    在此提供給一樣遇到這問題的人一個方法(不一定是好方法，但一定是可行的方法)
    */
    $data1=number_format(substr($data,0,strrpos($data,".")==0?strlen($data):strrpos($data,".")));
    $data2=substr( strrchr( $data, "." ), 1 );
    if($data2==0) $data3="";
      else {
       if(strlen($data2)>$n) $data3=substr($data2,0,$n);
         else $data3=$data2;
      $data3=".".$data3;
      }
    return $data1;
  }
  function str_replace_deep($search, $replace, $subject)
  {
      if (is_array($subject))
      {
          foreach($subject as &$oneSubject)
              $oneSubject = str_replace_deep($search, $replace, $oneSubject);
          unset($oneSubject);
          return $subject;
      } else {
          return str_replace($search, $replace, $subject);
      }
  }
  
  
  
  function pdo_field_array($query_sql){
    global $pdo;
    $buff=array();
    $res=$pdo->query($query_sql);
    $cs=$res->columnCount();
    for($i=0;$i<$cs;$i++)
    {
      $meta=$res->getColumnMeta($i);
      array_push($buff,$meta['name']);
    }
    //print_r($buff);
    return $buff;
  }
  
  function pdo_resulttoarray($res){
    $g=array();
    $g=$res->fetchAll(PDO::FETCH_NUM);
    return $g;
  }
  
  function pdo_resulttoobject($res){
    $g = array();
    $g=$res->fetchAll(PDO::FETCH_OBJ);    
    return $g;
  }
  function pdo_resulttoassoc($res){    
      
    return $res->fetchAll(PDO::FETCH_ASSOC);    
  }  
  
  function subname($fname){
    //$pathinfo=pathinfo($fname);
    //$pathinfo['extension'];
    
    $m=explode(".",$fname);
    return end($m);
  } 
  function mainname($fname){
    $pathinfo=pathinfo($fname);
    return $pathinfo['filename'];           
  }
 
  function size_hum_read_v2($size)
  {
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
  }  
  function remove_html($content){
    $content = preg_replace("/<(.*?)>/", "", $content);
    $content = str_replace("&", "&amp;", $content);
    return $content;
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

 
  function big5toutf8($str)
  {
    return mb_convert_encoding($str, 'UTF-8','BIG5');
  }
  function utf8tobig5($str)
  {
    return mb_convert_encoding($str, 'BIG5', 'UTF-8');
  }

  function csv_to_array($input, $delimiter=',')
  {
      $header = null;
      $data = array();
      $csvData = str_getcsv($input, "\n");
     
      foreach($csvData as $csvLine){
          if(is_null($header)) $header = explode($delimiter, $csvLine);
          else{
             
              $items = explode($delimiter, $csvLine);
             
              for($n = 0, $m = count($header); $n < $m; $n++){
                  $prepareData[$header[$n]] = $items[$n];
              }
             
              $data[] = $prepareData;
          }
      } 
      return $data;
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
 
  //from http://ditio.net/2008/11/04/php-string-to-hex-and-hex-to-string-functions/
  function strToHex($string)
  {
    $hex='';
    for ($i=0; $i < strlen($string); $i++)
    {
      $hex .= dechex(ord($string[$i]));
    }
    return $hex;
  }
  function hexToStr($hex)
  {
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2)
    {
      $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;
  }
  function is_win()
  {
    return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');        
  }
  