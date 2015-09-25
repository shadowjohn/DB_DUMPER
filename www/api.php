<?php
  require 'inc/config.php';  
  $GETS_STRING="mode";
  $GETS = getGET_POST($GETS_STRING,'GET');

  switch($GETS['mode'])
  {
    case 'db_load':
        $POSTS_STRING="DB_TITLE";
        $POSTS = getGET_POST($POSTS_STRING,'POST');
        echo json_encode($DB_JDATA[$POSTS['DB_TITLE']],true);
        exit();
      break;
    case 'loadDBLinks':
        ?>
        <option value=''>--請選擇--</option>
        <option value='ADD'>建立新連線</option>
        <?php
        foreach($DB_JDATA as $k=>$v)
        {
          ?>
          <option value="<?=jsAddSlashes($k);?>">[<?=$v['DB_KIND'];?>] <?=jsAddSlashes($k);?></option>
          <?php
        }
        exit();
      break;
    case 'getDBKIND_SELECT':
        $POSTS_STRING="DB_KIND";
        $POSTS = getGET_POST($POSTS_STRING,'POST');
        switch($POSTS['DB_KIND'])
        {
          case 'mysql':
          case 'mssql':
          case 'postgresql':
            ?>            
            ＩＰ位置：<input type="text" id="db_ip"><br>
            登入帳號：<input type="text" id="db_id"><br>
            登入密碼：<input type="password" id="db_pwd"><br>            
            <?php
            break;                    
          case 'oracle':
            ?>            
            ＩＰ位置：<input type="text" id="db_ip"><br>
            SERVICE NAME：<input type="text" id="db_servicename"><br>
            登入帳號：<input type="text" id="db_id"><br>
            登入密碼：<input type="password" id="db_pwd"><br>            
            <?php            
            break;
          case 'sqlite':
            ?>
            檔案位置：<span id="db_place_span"></span><input type="file" id="db_place"><br>
            <?php
            break;
        }        
        exit();
      break;
    case 'saveDB_Setting':
        //儲存連線資訊
        $POSTS_STRING="DB_KIND,DB_TITLE,DB_IP,DB_ID,DB_PWD,DB_PLACE,DB_SERVICENAME";
        $POSTS = getGET_POST($POSTS_STRING,'POST');
        $DB_JDATA[$POSTS['DB_TITLE']]=$POSTS;
        @mkdir("C:\\temp",0777);
        file_put_contents($DB_PATH,json_format(json_encode($DB_JDATA,true)));
        $OUTPUT = ARRAY();
        $OUTPUT['STATUS']="TRUE";
        echo json_encode($OUTPUT,true);
        exit();
      break;
    case 'delDB_Setting':
        $POSTS_STRING="DB_TITLE";
        $POSTS = getGET_POST($POSTS_STRING,'POST');
        unset($DB_JDATA[$POSTS['DB_TITLE']]);
        @mkdir("C:\\temp",0777);
        file_put_contents($DB_PATH,json_format(json_encode($DB_JDATA,true)));
        $OUTPUT = ARRAY();
        $OUTPUT['STATUS']="TRUE";
        echo json_encode($OUTPUT,true);
        exit();
      break;
    case 'checkDB_link':
        $POSTS_STRING="DB_KIND,DB_IP,DB_ID,DB_PWD,DB_PLACE,DB_SERVICENAME";
        $POSTS = getGET_POST($POSTS_STRING,'POST');
        $OUTPUT = ARRAY();
        $pdo = "";
        $DB_KIND = "";
        $C = "";
        $tns="";
        switch(strtolower($POSTS['DB_KIND']))
        {
          case 'mysql':            
            $DB_KIND = "mysql";
            $C = "host={$POSTS['DB_IP']}";
            break;
          case 'mssql':
            $DB_KIND = "sqlsrv";
            $C = "server={$POSTS['DB_IP']}";
            break;
          case 'postgresql': 
            $DB_KIND = "pgsql";
            $C = "host={$POSTS['DB_IP']}";
            break;         
          case 'oracle':
$tns = "
    (DESCRIPTION =
        (ADDRESS = (PROTOCOL = TCP)(HOST = {$POSTS['DB_IP']})(PORT = 1521))
        (CONNECT_DATA =
            (SERVER = DEDICATED)
            (SERVICE_NAME = {$POSTS['DB_SERVICENAME']})
        )
    )
";          
            $DB_KIND = "oci:dbname={$tns}";
            break; 
          case 'sqlite':
            $DB_KIND = "sqlite:{$POSTS['DB_PLACE']}";
            break;
        }
        switch($POSTS['DB_KIND'])
        {
          case 'mysql':
          case 'mssql':
          case 'postgresql':
            try{
              $pdo = new PDO("{$DB_KIND}:dbname=postgres;{$C}",$POSTS['DB_ID'],$POSTS['DB_PWD']);
              $OUTPUT['STATUS']="TRUE";
            }catch(PDOException $Exception){              
              $OUTPUT['STATUS']="FALSE";
              $OUTPUT['REASON']=print_r($Exception,true);                
            }                      
            break;
          case 'oracle':          
            try{
              $pdo = new PDO("{$DB_KIND};charset=UTF8",$POSTS['DB_ID'],$POSTS['DB_PWD']);
              $OUTPUT['STATUS']="TRUE";
            }catch(PDOException $Exception){              
              $OUTPUT['STATUS']="FALSE";
              $OUTPUT['REASON']=print_r($Exception,true);                
            }
            break;
          case 'sqlite':
            try{
              $pdo = new PDO("sqlite:{$POSTS['DB_PLACE']}");
              $pdo->setAttribute(PDO::ATTR_ERRMODE, 
                            PDO::ERRMODE_EXCEPTION);              
              $OUTPUT['STATUS']="TRUE";              
            }catch(Exception $Exception){
              $OUTPUT['STATUS']="FALSE";
              $OUTPUT['REASON']=print_r($Exception,true);                
            } 
            break;
        }
        echo json_encode($OUTPUT,true);
        exit();
      break;
    case 'getDBList':
        //取得資料表
        $POSTS_STRING="DB_KIND,DB_IP,DB_ID,DB_PWD,DB_PLACE,DB_SERVICENAME";
        $POSTS = getGET_POST($POSTS_STRING,'POST');
        $OUTPUT = ARRAY();
        $pdo = "";
        $DB_KIND = "";
        $C = "";
        switch($POSTS['DB_KIND'])
        {
          case 'mysql':            
            $DB_KIND = "mysql";
            $C = "host={$POSTS['DB_IP']}";
            break;
          case 'mssql':
            $DB_KIND = "sqlsrv";
            $C = "server={$POSTS['DB_IP']}";
            break;
          case 'postgresql': 
            $DB_KIND = "pgsql";
            $C = "host={$POSTS['DB_IP']}";
            break;         
          case 'oracle':
            $DB_KIND = "oci";
$tns = "
    (DESCRIPTION =
        (ADDRESS = (PROTOCOL = TCP)(HOST = {$POSTS['DB_IP']})(PORT = 1521))
        (CONNECT_DATA =
            (SERVER = DEDICATED)
            (SERVICE_NAME = {$POSTS['DB_SERVICENAME']})
        )
    )
";          
            $DB_KIND = "oci:dbname={$tns}";
            break; 
        }
        $OUTPUT=ARRAY();                
        switch($POSTS['DB_KIND'])
        {
          case 'mysql':
            $pdo = new PDO("{$DB_KIND}:{$C}",$POSTS['DB_ID'],$POSTS['DB_PWD']);
            $SQL = "show databases";
            $ra = selectSQL($SQL);                  
            for($i=0,$max_i=count($ra);$i<$max_i;$i++)
            {        
              $d = ARRAY();          
              $d['DB_NAME'] = $ra[$i]['Database'];
              array_push($OUTPUT,$d);
            }                                    
            break;
          case 'mssql':
            $pdo = new PDO("{$DB_KIND}:{$C}",$POSTS['DB_ID'],$POSTS['DB_PWD']);
            $SQL = "SELECT * FROM [sys].[databases]";
            $ra = selectSQL($SQL);                    
            for($i=0,$max_i=count($ra);$i<$max_i;$i++)
            {              
              $d = ARRAY();    
              $d['DB_NAME'] = $ra[$i]['name'];
              array_push($OUTPUT,$d);
            }                                
            break;
          case 'postgresql':
            
            $pdo = new PDO("{$DB_KIND}:dbname=postgres;{$C}",$POSTS['DB_ID'],$POSTS['DB_PWD']);
            $SQL="SELECT datname FROM pg_database";            
            $ra = selectSQL($SQL);                                          
            for($i=0,$max_i=count($ra);$i<$max_i;$i++)
            {         
              $d = ARRAY();         
              $d['DB_NAME'] = $ra[$i]['datname'];
              array_push($OUTPUT,$d);
            }          
            break;
          case 'oracle':          
            $pdo = new PDO("{$DB_KIND};charset=UTF8",$POSTS['DB_ID'],$POSTS['DB_PWD']);
            $SQL="SELECT NAME FROM v\$database";
            $ra = selectSQL($SQL);                              
            for($i=0,$max_i=count($ra);$i<$max_i;$i++)
            {         
              $d = ARRAY();         
              $d['DB_NAME'] = $ra[$i]['NAME'];
              array_push($OUTPUT,$d);
            }                       
            array_push($OUTPUT,$d);      
            break;
          case 'sqlite':
            $pdo = new PDO("sqlite:{$POSTS['DB_PLACE']}");
            $d = ARRAY();         
            $d['DB_NAME'] = mainname($POSTS['DB_PLACE']);
            array_push($OUTPUT,$d);  
            break;
        }
        echo json_encode($OUTPUT,true);
        exit();
      break;
    case 'getTABLEList':
        $POSTS_STRING="DB_KIND,DB_IP,DB_ID,DB_PWD,DB_PLACE,DB_SELECT,DB_SERVICENAME";
        $POSTS = getGET_POST($POSTS_STRING,'POST');
        $OUTPUT = ARRAY();
        $pdo = "";
        $DB_KIND = "";
        $C = "";
        switch($POSTS['DB_KIND'])
        {
          case 'mysql':            
            $DB_KIND = "mysql";
            $C = "host={$POSTS['DB_IP']}";
            break;
          case 'mssql':
            $DB_KIND = "sqlsrv";
            $C = "server={$POSTS['DB_IP']}";
            break;
          case 'postgresql': 
            $DB_KIND = "pgsql";
            $C = "host={$POSTS['DB_IP']}";
            break;         
          case 'oracle':
            $DB_KIND = "oci";
$tns = "
    (DESCRIPTION =
        (ADDRESS = (PROTOCOL = TCP)(HOST = {$POSTS['DB_IP']})(PORT = 1521))
        (CONNECT_DATA =
            (SERVER = DEDICATED)
            (SERVICE_NAME = {$POSTS['DB_SERVICENAME']})
        )
    )
";          
            $DB_KIND = "oci:dbname={$tns}";
            break; 
        }
        $OUTPUT=ARRAY();        
        switch($POSTS['DB_KIND'])
        {
          case 'mysql':
            $pdo = new PDO("{$DB_KIND}:{$C}",$POSTS['DB_ID'],$POSTS['DB_PWD']);
            $SQL = "SET NAMES UTF8";
            selectSQL($SQL);         
            $SQL= sprintf("show table status from `%s`",$POSTS['DB_SELECT']);
            $ra = selectSQL($SQL);            
            $d = ARRAY();
            
            for($i=0,$max_i=count($ra);$i<$max_i;$i++)
            {              
              $d['TABLE_NAME'] = $ra[$i]['Name'];
              $d['TABLE_COMMENT'] = $ra[$i]['Comment'];
              array_push($OUTPUT,$d);
            }                                    
            break;
          case 'mssql':
            $pdo = new PDO("{$DB_KIND}:{$C}",$POSTS['DB_ID'],$POSTS['DB_PWD']);
            $SQL=sprintf("USE [%s]",$POSTS['DB_SELECT']);
            selectSQL($SQL);    
            $SQL="SELECT [name] as [Name] FROM [sys].[tables]";
            $ra = selectSQL($SQL);    
            $SQL=sprintf("SELECT [objname] as [name],cast(value as varchar(255)) as [des] FROM fn_listextendedproperty(NULL,'user','dbo','table',default,default,default) ");    
            $ro_tables_des=selectSQL($SQL);            
            //print_r($ro_tables_des);
            for($i=0;$i<count($ra);$i++)
            {
              $ra[$i]['Comment']="";
              for($j=0;$j<count($ro_tables_des);$j++)
              {
                if($ra[$i]['Name']==$ro_tables_des[$j]['name'])
                {
                  $ra[$i]['Comment']=$ro_tables_des[$j]['des'];
                  break;          
                }
              }
            }
            
                                                     
            for($i=0,$max_i=count($ra);$i<$max_i;$i++)
            {              
              $d = ARRAY();
              $d['TABLE_NAME'] = $ra[$i]['Name'];
              $d['TABLE_COMMENT'] = $ra[$i]['Comment'];
              array_push($OUTPUT,$d);
            }   
            break;
          case 'postgresql':
            $pdo = new PDO("{$DB_KIND}:dbname={$POSTS['DB_SELECT']};{$C}",$POSTS['DB_ID'],$POSTS['DB_PWD']);            
            $SQL= "SELECT table_schema as \"Comment\",table_name AS \"Name\"
                        FROM 
                          information_schema.tables
                        WHERE 
                          1=1 
                          AND table_schema = 'public'
                        ORDER BY 
                          table_schema,table_name";
            $ra = selectSQL($SQL);                                             
            for($i=0,$max_i=count($ra);$i<$max_i;$i++)
            {              
              $d = ARRAY();
              $d['TABLE_NAME'] = $ra[$i]['Name'];
              $d['TABLE_COMMENT'] = $ra[$i]['Comment'];
              array_push($OUTPUT,$d);
            }                                                                                          
            break;
          case 'oracle':          
            $pdo = new PDO("{$DB_KIND};charset=UTF8",$POSTS['DB_ID'],$POSTS['DB_PWD']);
            $SQL= sprintf("SELECT 
                      			user_tables.table_name as \"TABLE_NAME\",
                      			COALESCE(all_tab_comments.COMMENTS,' ') as \"TABLE_COMMENT\"
                      		FROM 
                      			user_tables 
                      				left outer join all_tab_comments
                      					on 
                                  1=1 
                                  AND user_tables.table_name=all_tab_comments.TABLE_NAME
                                  AND all_tab_comments.TABLE_TYPE='TABLE'                                  
                                ");
            $ra = selectSQL($SQL);                                             
            for($i=0,$max_i=count($ra);$i<$max_i;$i++)
            {              
              $d = ARRAY();
              $d['TABLE_NAME'] = $ra[$i]['TABLE_NAME'];
              $d['TABLE_COMMENT'] = $ra[$i]['TABLE_COMMENT'];
              array_push($OUTPUT,$d);
            }   
            break;
          case 'sqlite':
            $pdo = new PDO("sqlite:{$POSTS['DB_PLACE']}");
            $SQL= sprintf("
              SELECT 
                name AS TABLE_NAME,
                name AS TABLE_COMMENT
              FROM sqlite_master
              WHERE 
                1=1
                AND type='table'
                AND name!='sqlite_sequence'
            ");
            $ra = selectSQL($SQL);                                             
            for($i=0,$max_i=count($ra);$i<$max_i;$i++)
            {              
              $d = ARRAY();
              $d['TABLE_NAME'] = $ra[$i]['TABLE_NAME'];
              $d['TABLE_COMMENT'] = $ra[$i]['TABLE_COMMENT'];
              array_push($OUTPUT,$d);
            }   
            break;
        }
        echo json_encode($OUTPUT,true);
        exit();
      break;
    case 'dump_table_schema':
        $POSTS_STRING="DB_KIND,DB_IP,DB_ID,DB_PWD,DB_PLACE,DB_SELECT,TABLES_SELECT,DB_SERVICENAME";
        $POSTS = getGET_POST($POSTS_STRING,'POST');
        $OUTPUT = ARRAY();
        $pdo = "";
        $DB_KIND = "";
        $C = "";
        switch($POSTS['DB_KIND'])
        {
          case 'mysql':            
            $DB_KIND = "mysql";
            $C = "host={$POSTS['DB_IP']}";
            break;
          case 'mssql':
            $DB_KIND = "sqlsrv";
            $C = "server={$POSTS['DB_IP']}";
            break;
          case 'postgresql': 
            $DB_KIND = "pgsql";
            $C = "host={$POSTS['DB_IP']}";
            break;         
          case 'oracle':
            $DB_KIND = "oci";
$tns = "
    (DESCRIPTION =
        (ADDRESS = (PROTOCOL = TCP)(HOST = {$POSTS['DB_IP']})(PORT = 1521))
        (CONNECT_DATA =
            (SERVER = DEDICATED)
            (SERVICE_NAME = {$POSTS['DB_SERVICENAME']})
        )
    )
";          
            $DB_KIND = "oci:dbname={$tns}";
            break; 
        }
        $OUTPUT=ARRAY();       
        $POSTS['selectAll'] = explode(",",$POSTS['TABLES_SELECT']);         
        switch($POSTS['DB_KIND'])
        {
          case 'mysql':
              $pdo = new PDO("{$DB_KIND}:{$C}",$POSTS['DB_ID'],$POSTS['DB_PWD']);
              $SQL = "SET NAMES UTF8";
              selectSQL($SQL);         
              $SQL= sprintf("show table status from `%s`",$POSTS['DB_SELECT']);
              $ra = selectSQL($SQL);            
              $d = ARRAY();
              $data_base_tmp_data="";
              ob_start();
              for($i=0,$max_i=count($POSTS['selectAll']);$i<$max_i;$i++)
              {    
                $SQL=sprintf("show table status from `%s` where name= ? ",$POSTS['DB_SELECT']);
                $res_object=selectSQL_SAFE($SQL,ARRAY($POSTS['selectAll'][$i]));
                $SQL=sprintf("SHOW FULL FIELDS FROM `%s`.`%s`",$POSTS['DB_SELECT'],$POSTS['selectAll'][$i]);
                $res_explain_object=selectSQL($SQL);           
                if(count($res_object)!=0)
                {
                  
                  echo $res_object[0]['Name']."　　".$res_object[0]['Comment'];              
                  ?>
                  <br>
                  <table border="1" cellpadding="5" cellspacing="0" width="90%">
                  <tr>
                    <th width="20%">欄位名稱(英)</th>
                    <th width="20%">欄位名稱(中)</th>
                    <th width="20%">型態</th>
                    <th>相關參數</th>
                  </tr>
                  <?
                  for($j=0,$max_j=count($res_explain_object);$j<$max_j;$j++)
                  {
                    ?>
                    <tr>
                    <td><?=$res_explain_object[$j]['Field'];?></td>
                    <td><?=$res_explain_object[$j]['Comment'];?></td>
                    <td><?=$res_explain_object[$j]['Type'];?></td>                
                    <td style="text-align:left;"><?=(trim($res_explain_object[$j]['Key'])=="")?"":"Key：{$res_explain_object[$j]['Key']}";?>
                        <?=(trim($res_explain_object[$j]['Default'])=="")?"":"<br>Default：{$res_explain_object[$j]['Default']}";?>                    
                        <?=(trim($res_explain_object[$j]['Extra'])=="")?"":"<br>Extra：{$res_explain_object[$j]['Extra']}";?>
                        &nbsp;
                    </td>
                    </tr>
                    <?
                  }     
                  ?>
                  </table>
                  <br>
                  <?         
                  
                }              
                
              }
              $data_base_tmp_data=ob_get_contents();
              ob_end_clean();
              echo $data_base_tmp_data;
              exit();                                   
            break;
          case 'mssql':
            $pdo = new PDO("{$DB_KIND}:{$C}",$POSTS['DB_ID'],$POSTS['DB_PWD']);
            $data_base_tmp_data="";
            $SQL="USE [{$POSTS['DB_SELECT']}]";
            selectSQL($SQL);
            
            $SQL=sprintf("SELECT [name] FROM [sys].[tables]");
            $ro_tables=selectSQL($SQL);
            $SQL=sprintf("SELECT [objname] as [name],cast(value as varchar(255)) as [des] FROM fn_listextendedproperty(NULL,'user','dbo','table',default,default,default) ");    
            $ro_tables_des=selectSQL($SQL);            
            //print_r($ro_tables_des);
            for($i=0;$i<count($ro_tables);$i++)
            {
              $ro_tables[$i]['des']="";
              for($j=0;$j<count($ro_tables_des);$j++)
              {
                if($ro_tables[$i]['name']==$ro_tables_des[$j]['name'])
                {
                  $ro_tables[$i]['des']=$ro_tables_des[$j]['des'];
                  break;          
                }
              }
            }
             
            ob_start();  
            for($i=0,$max_i=count($POSTS['selectAll']);$i<$max_i;$i++)
            {         
              $SQL=sprintf("SELECT 
                              * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%s' ",$POSTS['selectAll'][$i]);              
              $ro=selectSQL($SQL);
              $SQL=sprintf("SELECT objname as name,CAST(value as varchar(255)) as [des] FROM fn_listextendedproperty(NULL,'user','dbo','table','%s','column',default)",$POSTS['selectAll'][$i]);
              $ro_des=selectSQL($SQL);

              for($k=0,$max_k=count($ro);$k<$max_k;$k++)
              { 
                $ro[$k]['des']="";                 
                for($j=0,$max_j=count($ro_des);$j<$max_j;$j++)
                {                    
                  if($ro_des[$j]['name']==$ro[$k]['COLUMN_NAME'])
                  {                    
                    $ro[$k]['des']=$ro_des[$j]['des'];                   
                    break;
                  }
                }
              }
              $des="";
              
              
              for($j=0,$max_j=count($ro_tables);$j<$max_j;$j++)
              {
                if($ro_tables[$j]['name']==$POSTS['selectAll'][$i])
                {
                  $des=$ro_tables[$j]['des'];
                  break;
                }
              }
              $pkkey="";
              $SQL=sprintf("sp_pkeys @table_name='%s'",$POSTS['selectAll'][$i]);              
              $ro_pkkey=selectSQL($SQL);
              if(count($ro_pkkey)!="")
              {
                $pkkey=$ro_pkkey[0]['COLUMN_NAME'];
              }
              $others="";                                
              ?>             
                <?=$POSTS['selectAll'][$i];?>　<?=$des;?>
                <br>
                <table border="1" cellpadding="5" cellspacing="0" width="90%">
                  <tr>
                    <th width="20%">欄位名稱(英)</th>
                    <th width="20%">欄位名稱(中)</th>
                    <th width="20%">型態</th>
                    <th>相關參數</th>
                  </tr>
                  <?
                  //pre_print_r($ro);
                  for($j=0,$max_j=count($ro);$j<$max_j;$j++)
                  {
                    $others="";
                    if($ro[$j]['COLUMN_NAME']==$pkkey)
                    {
                      $others.="KEY：PRI\n";
                      if($ro[$j]['NUMERIC_PRECISION']!="")
                      {
                        $others.="EXTRA：auto_increment\n";
                      }                        
                    }
                    $others=nl2br(trim($others));
                    ?>
                    <tr>
                      <td><?=$ro[$j]['COLUMN_NAME'];?>&nbsp;</td>
                      <td><?=$ro[$j]['des'];?>&nbsp;</td>
                      <td><?=$ro[$j]['DATA_TYPE'];?>
                        <?
                        switch($ro[$j]['DATA_TYPE'])
                        {
                          case 'int':
                          case 'text':
                          case 'datetime':
                          case 'date':                            
                            break;
                          default:
                          ?>
                          (<?=$ro[$j]['CHARACTER_MAXIMUM_LENGTH'];?>)
                          <?
                            break;
                        }
                        ?>
                      &nbsp;</td>
                      <td style="text-align:left;">
                         <?=$others;?>
                      &nbsp;</td>
                    </tr>
                    <?
                  }
                  ?>
                </table> 
                <br>              
              <? 
            }
            $data_base_tmp_data = ob_get_contents();
            ob_end_clean();
            echo $data_base_tmp_data;
            exit();
            break;
          case 'postgresql':
            $pdo = new PDO("{$DB_KIND}:dbname={$POSTS['DB_SELECT']};{$C}",$POSTS['DB_ID'],$POSTS['DB_PWD']);
            $data_base_tmp_data="";
            for($i=0,$max_i=count($POSTS['selectAll']);$i<$max_i;$i++)
            { 
              $SQL=sprintf("select table_name as \"Name\"
                                   from 
                                      INFORMATION_SCHEMA.COLUMNS 
                                   where 
                                      table_name = '%s'; ",$POSTS['selectAll'][$i]);
                                                     
              $res_object=selectSQL($SQL);
              
                
              $SQL=sprintf("select \"A\".\"column_name\" as \"Field\",
                                    \"A\".\"data_type\"
                                        ||
                                          case when \"A\".\"character_maximum_length\" is null then ''                                              
                                          else ' (' || \"A\".\"character_maximum_length\" || ')'
                                        end                                             
                                     as \"Type\",
                                    \"A\".\"column_default\" as \"Default\",
                                    coalesce(\"B\".\"description\",'') AS \"Comment\"
                                     from 
                                        INFORMATION_SCHEMA.COLUMNS AS \"A\" left  join
                                        (
                                          select * from pg_description
                                          join pg_class on pg_description.objoid = pg_class.oid
                                          join pg_namespace on pg_class.relnamespace = pg_namespace.oid
                                        ) AS \"B\"

                                     ON                                            
                                        \"A\".\"table_name\"=\"B\".\"relname\"
                                        AND \"A\".\"ordinal_position\"=\"B\".\"objsubid\"
                                     WHERE 
                                        1=1   
                                        AND \"A\".\"table_name\" = '%s'; ",$POSTS['selectAll'][$i]);                
              $res_explain_object=selectSQL($SQL);
              if(count($res_object)!=0)
              {  
                ob_start();                
                echo $res_object[0]['Name']."　　".$res_object[0]['Comment'];              
                ?>
                <br>
                <table border="1" cellpadding="5" cellspacing="0" width="90%">
                  <tr>
                    <th width="20%">欄位名稱(英)</th>
                    <th width="20%">欄位名稱(中)</th>
                    <th width="20%">型態</th>
                    <th>相關參數</th>
                  </tr>
                  <?
                  for($j=0,$max_j=count($res_explain_object);$j<$max_j;$j++)
                  {
                    ?>
                    <tr>
                    <td><?=$res_explain_object[$j]['Field'];?></td>
                    <td><?=$res_explain_object[$j]['Comment'];?></td>
                    <td><?=$res_explain_object[$j]['Type'];?></td>                
                    <td style="text-align:left;"><?=(trim($res_explain_object[$j]['Key'])=="")?"":"Key：{$res_explain_object[$j]['Key']}";?>
                        <?=(trim($res_explain_object[$j]['Default'])=="")?"":"<br>Default：{$res_explain_object[$j]['Default']}";?>                    
                        <?=(trim($res_explain_object[$j]['Extra'])=="")?"":"<br>Extra：{$res_explain_object[$j]['Extra']}";?>
                        &nbsp;
                    </td>
                    </tr>
                    <?
                  }     
                 ?>
                 </table>
                 <br>                  
                 <?         
                 $data_base_tmp_data.=ob_get_contents();
                  
                }
                ob_end_clean();                                
              } 
              echo $data_base_tmp_data;
              exit();                            
            break;
          case 'oracle':                    
            $pdo = new PDO("{$DB_KIND};charset=UTF8",$POSTS['DB_ID'],$POSTS['DB_PWD']);
            //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           
            $data_base_tmp_data="";
            
            ob_start(); 
             
            for($i=0,$max_i=count($POSTS['selectAll']);$i<$max_i;$i++)
            { 
              $SQL=sprintf("
                            SELECT DISTINCT
                        			user_tables.table_name as \"Name\",
                        			COALESCE(all_tab_comments.COMMENTS,' ') as \"Comment\"
                        		FROM 
                        			user_tables 
                        				left outer join all_tab_comments
                        					on 
                                    1=1 
                                    AND user_tables.table_name='%s'
                                    AND user_tables.table_name=all_tab_comments.TABLE_NAME
                                    AND all_tab_comments.TABLE_TYPE='TABLE'
                            WHERE
                               user_tables.table_name='%s'    
                                  ",$POSTS['selectAll'][$i],$POSTS['selectAll'][$i]);
                                           
              $rtable=selectSQL($SQL);
              
              
                
              $SQL=sprintf("SELECT DISTINCT
                          			all_tab_cols.COLUMN_NAME \"Field\",
                          			all_tab_cols.nullable,
                          			all_tab_cols.data_type || case when all_tab_cols.data_type = 'NUMBER' and all_tab_cols.data_precision is not null then '(' || all_tab_cols.data_precision || ',' || all_tab_cols.data_scale || ')'
                          										when all_tab_cols.data_type like '%%CHAR%%' then '(' || all_tab_cols.data_length || ')'
                          										else null
                          							 end \"Type\",
                          			COALESCE(user_col_comments.Comments ,' ') \"Comment\"                
                          		FROM  all_tab_cols
                          			LEFT JOIN user_col_comments
                          				on all_tab_cols.TABLE_NAME=user_col_comments.TABLE_NAME
                                    AND user_col_comments.COLUMN_NAME=all_tab_cols.COLUMN_NAME                         		
                          		WHERE 
                          			all_tab_cols.TABLE_NAME='%s'
                          		",$POSTS['selectAll'][$i]);                
              $res_explain_object=selectSQL($SQL);
              $SQL=sprintf("SELECT 
                                COLUMN_NAME ,
                                DATA_DEFAULT \"Default\"
                              FROM 
                                all_tab_cols 
                              WHERE 
                          			TABLE_NAME='%s'
                          		",$POSTS['selectAll'][$i]);
              $ro=selectSQL($SQL); 
              $SQL=sprintf("SELECT cols.table_name, cols.column_name, cols.position, cons.status, cons.owner
                                FROM all_constraints cons, all_cons_columns cols
                                WHERE cols.table_name = '%s'
                                AND cons.constraint_type = 'P'
                                AND cons.constraint_name = cols.constraint_name
                                AND cons.owner = cols.owner
                                ORDER BY cols.table_name, cols.position
                          		",$POSTS['selectAll'][$i]);
              $ro_find_PK=selectSQL($SQL);                 
              for($j=0,$max_j=count($res_explain_object);$j<$max_j;$j++)
              {
                for($k=0,$max_k=count($ro);$k<$max_k;$k++)
                {
                  if($ro[$k]['COLUMN_NAME']==$res_explain_object[$j]['Field'])
                  {
                    $res_explain_object[$j]['Default']=$ro[$k]['Default'];
                  }
                }
                for($k=0,$max_k=count($ro_find_PK);$k<$max_k;$k++)
                {
                  $res_explain_object[$j]['Key']='';
                  if($ro_find_PK[$k]['COLUMN_NAME']==$res_explain_object[$j]['Field'])
                  {
                     $res_explain_object[$j]['Key']='PK';
                  }
                }
                $others="";       
                  
              }              
              if(count($rtable)!=0)
              {  
                              
                echo $rtable[0]['Name']."　　".$rtable[0]['Comment'];              
                ?>
                <br>
                <table border="1" cellpadding="5" cellspacing="0" width="90%">
                  <tr>
                    <th width="20%">欄位名稱(英)</th>
                    <th width="20%">欄位名稱(中)</th>
                    <th width="20%">型態</th>
                    <th>相關參數</th>
                  </tr>
                  <?
                  for($j=0,$max_j=count($res_explain_object);$j<$max_j;$j++)
                  {
                    ?>
                    <tr>
                    <td><?=$res_explain_object[$j]['Field'];?></td>
                    <td><?=$res_explain_object[$j]['Comment'];?></td>
                    <td><?=$res_explain_object[$j]['Type'];?></td>                
                    <td style="text-align:left;"><?=(trim($res_explain_object[$j]['Key'])=="")?"":"Key：{$res_explain_object[$j]['Key']}";?>
                        <?=(trim($res_explain_object[$j]['Default'])=="")?"":"<br>Default：{$res_explain_object[$j]['Default']}";?>                    
                        <?=(trim($res_explain_object[$j]['Extra'])=="")?"":"<br>Extra：{$res_explain_object[$j]['Extra']}";?>
                        &nbsp;
                    </td>
                    </tr>
                    <?
                  }     
                 ?>
                 </table>
                 <br>                  
                 <?         
                 
                  
                }
                                               
              } 
              $data_base_tmp_data=ob_get_contents();
              ob_end_clean(); 
              echo $data_base_tmp_data;
              exit();                                   
            break;
          case 'sqlite':
            $pdo = new PDO("sqlite:{$POSTS['DB_PLACE']}");
            $data_base_tmp_data="";
            
            ob_start(); 
             
            for($i=0,$max_i=count($POSTS['selectAll']);$i<$max_i;$i++)
            { 
              $SQL=sprintf("PRAGMA table_info(%s);",$POSTS['selectAll'][$i]);
              $res_object = selectSQL($SQL);
              $SQL=sprintf("
              SELECT COUNT(*) AS COUNTER                 
              FROM sqlite_master
              WHERE 
                1=1
                AND type='table'
                AND name='sqlite_sequence'
              ");
              $ra_count_sqlite_sequence = selectSQL($SQL);
              $res_explain_object=ARRAY();
              if($ra_count_sqlite_sequence[0]['COUNTER']>=1)
              {
                $SQL=sprintf("SELECT * FROM sqlite_sequence WHERE name='%s'",$POSTS['selectAll'][$i]);
                $res_explain_object = selectSQL($SQL);
              }
              if(count($res_object)!=0)
              {                
                echo $POSTS['selectAll'][$i];              
                ?>
                <br>
                <table border="1" cellpadding="5" cellspacing="0" width="90%">
                <tr>
                  <th width="20%">欄位名稱(英)</th>
                  <th width="20%">欄位名稱(中)</th>
                  <th width="20%">型態</th>
                  <th>相關參數</th>
                </tr>
                <?
                for($j=0,$max_j=count($res_object);$j<$max_j;$j++)
                { 
                ?>
                <tr>
                  <td><?=$res_object[$j]['name'];?></td>
                  <td><?=$res_object[$j]['name'];?></td>
                  <td><?=$res_object[$j]['type'];?></td>
                  <td style="text-align:left;"><?=(trim($res_object[$j]['pk'])=="0")?"":"Key：{$res_object[$j]['name']}";?>
                        <?=(trim($res_object[$j]['dflt_value'])=="")?"":"<br>Default：{$res_object[$j]['dflt_value']}";?>
                        <?php
                        if(count($res_explain_object)!=0 && $res_object[$j]['pk']=='1'){
                          echo "<br>Extra：auto_increment";
                        }
                        ?>
                        &nbsp;</td>
                </tr>
                <?php
                }
                ?>
                </table>
                <?php
              }                           
            }
            $data_base_tmp_data=ob_get_contents();
            ob_end_clean(); 
            echo $data_base_tmp_data;
            exit();                             
            break;
        }
      exit();
      break;
  }