<?php
  require 'inc/config.php';  
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
  <title>羽山好棒棒 http://3wa.tw 資料結構匯出機 - V1.3</title> 
  <link href="css/normalize.css" rel="stylesheet" type="text/css"> 
  <script src="inc/jquery-1.8.3.min.js" type="text/javascript"></script>  
  <script src="inc/php.default-min-min.js" type="text/javascript"></script>
  <script src="inc/include.js" type="text/javascript"></script>
  <style>
    table{
      border:1px solid #000;
    }
    table td{
      border:1px solid #000;
      padding:2px;
      font-size:18px;
    }
    table th{
      background-color:orange;
      font-weight:bold;
      font-size:24px;
    }
    .db_setting{
      display:none;
    }
    .db_main_fieldset{
      display:none;
    }
    .db_table_fieldset{
      width:90%;
      display:none;
    }
    .table_list_class{
      width: 90%;
    }
    .table_list_class .center{
      text-align:left;
    }
    .table_list_class .center{
      text-align:left;
    }
  </style>
  <script language="javascript">
    function init_db_control(){
      var tmp = "";
      switch($("#db_new_or_old").val())
      {
        case 'ADD':
          tmp = "<input type='button' id='db_test_btn' value='測試'>";
          tmp += "&nbsp;&nbsp;&nbsp;";
          tmp += "<input type='button' id='db_save_btn' value='儲存'>";
          break;
        default:
          tmp = "<input type='button' id='db_test_btn' value='測試'>";
          tmp += "&nbsp;&nbsp;&nbsp;";
          tmp += "<input type='button' id='db_save_btn' value='儲存'>";
          tmp += "&nbsp;&nbsp;&nbsp;";
          tmp += "<input type='button' id='db_del_btn' value='刪除'>";          
          break;
      }
      tmp += "&nbsp;&nbsp;&nbsp;";
      tmp += "<input type='button' id='db_link_btn' value='連線'>";
      $("#db_control_div").html(tmp);
      $("#db_test_btn").unbind("click");
      $("#db_test_btn").click(function(){
        var m = new Object();
        m['DB_KIND']=$("#db_kind").val();
        switch(m['DB_KIND'])
        {
          case 'mysql':
          case 'mssql':
          case 'postgresql':
            m['DB_IP']=$("#db_ip").val();
            m['DB_ID']=$("#db_id").val();
            m['DB_PWD']=$("#db_pwd").val();
            break;
          case 'oracle':
            m['DB_IP']=$("#db_ip").val();
            m['DB_ID']=$("#db_id").val();
            m['DB_PWD']=$("#db_pwd").val();
            m['DB_SERVICENAME']=$("#db_servicename").val();
            break;
          case 'sqlite':
            m['DB_PLACE']=$("#db_place").attr('src');                        
            break;
        }
        checkDBLink(m,true);
      });
      $("#db_place").unbind("change");
      $("#db_place").change(function(){        
        $(this).attr('src',$(this).val());
      });
      $("#db_save_btn").unbind("click");
      $("#db_save_btn").click(function(){
        //儲存
        var m=new Object();
        m['DB_TITLE']=$("#db_title").val();
        m['DB_KIND']=$("#db_kind").val();
        switch(m['DB_KIND'])
        {
          case 'mysql':
          case 'mssql':
          case 'postgresql':
            m['DB_IP']=$("#db_ip").val();
            m['DB_ID']=$("#db_id").val();
            m['DB_PWD']=$("#db_pwd").val();
            break;
          case 'oracle':
            m['DB_IP']=$("#db_ip").val();
            m['DB_ID']=$("#db_id").val();
            m['DB_PWD']=$("#db_pwd").val();
            m['DB_SERVICENAME']=$("#db_servicename").val();
            break;
          case 'sqlite':
            m['DB_PLACE']=$("#db_place").attr('src');            
            break;
        }        
        
        var tmp = json_decode(myAjax("api.php?mode=saveDB_Setting",m),true);
        if(tmp['STATUS']=="TRUE")
        {
          alert('儲存成功!');
          init_LoadDBLINK();
          setTimeout(function(){          
            $("#db_new_or_old").val(m['DB_TITLE']);
            db_load(m['DB_TITLE']);
          },1000);
        }
        else
        {
          alert('儲存失敗...'+tmp['REASON']);
        }
      });
      $("#db_del_btn").unbind("click");
      $("#db_del_btn").click(function(){
        //刪除
        if(confirm("你確定要刪除 " + $("#db_title").val() + " 嗎？")==false)
        {
          return;
        }
        var m=new Object();
        m['DB_TITLE']=$("#db_title").val();
        var tmp = json_decode(myAjax("api.php?mode=delDB_Setting",m),true);
        if(tmp['STATUS']=="TRUE")
        {
          alert('刪除成功!');
          init_LoadDBLINK();
          $(".db_setting").hide();          
        }
        else
        {
          alert('刪除失敗...'+tmp['REASON']);
        }
      });
      $("#db_link_btn").unbind("click");
      $("#db_link_btn").click(function(){
        //進行連線
        var m = getFormData();
        if(checkDBLink(m,false)==true)
        {
          $(".db_main_fieldset").fadeIn();
          getDBList(m);
          $(".db_table_fieldset").show();
          $("#db_table_div").html('');
        }
        else
        {
          alert('連線資訊錯誤...~_~');
        }
      });
    }
    function getFormData(){
      //取得表單連線資料
      var m = new Object();        
      m['DB_TITLE']=$("#db_title").val();
      m['DB_KIND']=$("#db_kind").val();
      m['DB_IP']=$("#db_ip").val();
      m['DB_ID']=$("#db_id").val();
      m['DB_PWD']=$("#db_pwd").val();
      m['DB_PLACE']=$("#db_place").attr('src');  
      m['DB_SERVICENAME']=$("#db_servicename").val();
      return m;
    }
    function checkDBLink($connection_data,is_need_alert){
      var tmp = json_decode(myAjax("api.php?mode=checkDB_link",$connection_data),true);
      if(tmp['STATUS']=="TRUE")
      {
        if(is_need_alert) alert('連線正確');
        return true;
      }
      else
      {
        if(is_need_alert) alert('測試失敗...'+tmp['REASON']);
        return false;
      }
    }
    function init_LoadDBLINK(){
      var tmp = myAjax("api.php?mode=loadDBLinks",""); 
      $("#db_new_or_old").html(tmp);
      $("#db_title").val('');
    }
    function db_load(title){
      switch(title)
      {
        case '':
          break;
        case 'ADD':
          $("#db_kind").val('');
          $("#db_kind").trigger('change');
          break;
        default:
          var m = new Object();
          m['DB_TITLE'] = title;
          var tmp = json_decode(myAjax("api.php?mode=db_load",m),true);
          //alert(tmp['DB_KIND']);            
          //$("#db_kind").val(tmp['DB_KIND']);
          
          $("#db_kind option")
             .removeAttr('selected')
             .filter("[value='"+tmp['DB_KIND']+"']")
                 .attr('selected', true);      
          $("#db_kind").trigger('change');
          switch(tmp['DB_KIND'])
          {
            case 'mysql':
            case 'mssql':
            case 'postgresql':
              $("#db_title").val(tmp['DB_TITLE']);
              $("#db_ip").val(tmp['DB_IP']);
              $("#db_id").val(tmp['DB_ID']);
              $("#db_pwd").val(tmp['DB_PWD']);              
              break;
            case 'oracle':
              $("#db_title").val(tmp['DB_TITLE']);
              $("#db_ip").val(tmp['DB_IP']);
              $("#db_id").val(tmp['DB_ID']);
              $("#db_pwd").val(tmp['DB_PWD']);
              $("#db_servicename").val(tmp['DB_SERVICENAME']);              
              break;
            case 'sqlite':
              $("#db_title").val(tmp['DB_TITLE']);
              $("#db_place").val(tmp['DB_PLACE']);
              $("#db_place").attr('src',tmp['DB_PLACE']);
              $("#db_place_span").html(tmp['DB_PLACE']);
              break;
          }
          break;
      }
    }
    function getDBList(m){
      //取得資料庫列表
      var data = myAjax("api.php?mode=getDBList",m);                  
      var jdata = json_decode(data,true);
      var h = "請選擇資料庫：<select id='db_list_select'></select>&nbsp;&nbsp;<input type='button' id='db_list_btn' value='選擇'>";
      $("#db_choice_div").html(h);
      var tmp = '';
      //tmp += "<option value=''>--請選擇--</option>";
      for(var i=0,max_i=count(jdata);i<max_i;i++)
      {
        tmp += sprintf("<option value='%s'>%s</option>",jdata[i]['DB_NAME'],jdata[i]['DB_NAME']);
      }
      
      $("#db_list_select").html(tmp);      
      $("#db_list_btn").unbind("click");
      $("#db_list_btn").click(function(){
        $("#db_table_div").html('');
        $("#db_table_output_div").html('');
        var db = $("#db_list_select").val();        
        if(db!='')
        {
          getTableLists(m,db);
        }
      });
    }
    function getTableLists(m,db){
      m['DB_SELECT']=db;
      var data = myAjax("api.php?mode=getTABLEList",m);            
      var jdata = json_decode(data,true);
      var tmp = "<h3>資料表列表：共 "+count(jdata)+" 個資料表</h3>";
      tmp += "<table class='table_list_class' border='1' cellpadding='0' cellspacing='0'>";
      tmp += "  <tr>";
      tmp += "    <th style='width:80px;'>選擇<input type='checkbox' id='checkbox_all_table'></th>";
      tmp += "    <th style='width:80px;'>項次</th>";
      tmp += "    <th>表名</th>";
      tmp += "    <th>說明</th>";
      tmp += "  </tr>";
      for(var i=0,max_i = count(jdata);i<max_i;i++)
      {
        tmp+= "<tr>";
        tmp+= sprintf("  <td><input type='checkbox' name='table_choice[]' value='%s'></td>",jdata[i]['TABLE_NAME']);
        tmp+= sprintf("  <td>%d</td>",(i+1));
        tmp+= sprintf("  <td class='center'>%s</td>",jdata[i]['TABLE_NAME']);
        tmp+= sprintf("  <td class='center'>%s</td>",jdata[i]['TABLE_COMMENT']);
        tmp+= "</tr>";
      }
      tmp += "</table>";
      tmp += "<br><br>";
      tmp += "<input type='button' id='dump_table_schema_btn' value='匯出資料結構'>"; 
      $("#db_table_div").html(tmp);
      $("#checkbox_all_table").unbind("click");
      $("#checkbox_all_table").click(function(){
        $("input[name='table_choice[]']").prop('checked',$(this).prop('checked'));
      });
      $("#dump_table_schema_btn").unbind("click");
      $("#dump_table_schema_btn").click(function(){
        var tables = trim(implode(",",getCheckBox_val('table_choice[]')));
        if(tables=="")
        {
          alert('請先選擇要匯出的資料表...');
          return;
        }
        var DBDATA = getFormData();
        DBDATA['DB_SELECT'] = $("#db_list_select").val();        
        DBDATA['TABLES_SELECT'] = tables;        
        var data = myAjax("api.php?mode=dump_table_schema",DBDATA);        
        $("#db_table_output_div").html(data);
      });
    }        
    $(document).ready(function(){
      $("#db_new_or_old").unbind("change");
      $("#db_new_or_old").change(function(){
        $(".db_main_fieldset").hide();
        $("#db_table_output_div").html('');   
        $(".db_setting").fadeIn("slow");
        $("#db_title").val('');                
        db_load($("#db_new_or_old").val());
        $(".db_table_fieldset").hide();        
      });
      $("#db_kind").unbind("change");
      $("#db_kind").change(function(){                
        var m = new Object();
        m['DB_KIND']=$("#db_kind").val();
        var tmp = myAjax("api.php?mode=getDBKIND_SELECT",m);
        $("#db_data_div").html(tmp);        
        init_db_control();
      });
      //載入DB列表
      init_LoadDBLINK();
    });
  </script>
</head>
<body>
  <center>
    <div style="width:500px;">
      <fieldset>
        <legend>請先輸入資料庫資訊：</legend>
        <div style="text-align:left;margin-left:20px;">
          資料庫連線：
            <select id="db_new_or_old"></select>
        </div>
        <hr width="80%" color="blue">
        <div class="db_setting" style="text-align:left;margin-left:20px;">
          連線　名稱：<input type="text" id="db_title" placeholder="請自定連線名稱">
          <br>
          資料庫種類：
          <select id="db_kind">
            <option value=''>--請選擇--</option>
            <option value='mysql'>MySQL</option>
            <option value='mssql'>MSSQL</option>
            <option value='postgresql'>PostgreSQL</option>
            <option value='oracle'>Oracle</option>
            <option value='sqlite'>SQLite</option>
          </select>
          <div id="db_data_div"></div>
          <div id="db_control_div"></div>
        </div>
      </fieldset>
    </div>
    <fieldset class="db_table_fieldset">
      <legend>資料庫操作：</legend>
      <div id="db_choice_div"></div>
      <div id="db_table_div"></div>
      <div id="db_table_output_div"></div>
    </fieldset>    
  </center>
</body>
</html>