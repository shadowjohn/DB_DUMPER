String.prototype.trim=function(){return this.replace(/(^\s*)|(\s*$)/g,"")};
function getWindowSize(){
  var myWidth = 0, myHeight = 0;
  if( typeof( window.innerWidth ) == 'number' ) {
    //Non-IE
    myWidth = window.innerWidth;
    myHeight = window.innerHeight;
  } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
    //IE 6+ in 'standards compliant mode'
    myWidth = document.documentElement.clientWidth;
    myHeight = document.documentElement.clientHeight;
  } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
    //IE 4 compatible
    myWidth = document.body.clientWidth;
    myHeight = document.body.clientHeight;      
  }
  var a=new Object();
  a['width']=myWidth;
  a['height']=myHeight;
  return a;
}
//我的ajax
function myAjax(url,postdata)
{
  var tmp = $.ajax({
      url: url,
      type: "POST",
      data: postdata,
      //crossDomain:true,
      async: false
   }).responseText;
  return tmp;
}
function myAjax_async(url,postdata,func)
{
  $.ajax({
      url: url,
      type: "POST",
      data: postdata,
      async: true,
      success: function(html){        
        func(html);        
      }
  });  
}
function getCheckBox_val(dom_name)
{
  //return array
  var arr=new Array();
  for(var i=0,max_i=$($("*[name='"+dom_name+"']")).size();i<max_i;i++)
  {
    if($($("*[name='"+dom_name+"']")[i]).prop('checked'))
    {
      array_push(arr,$($("*[name='"+dom_name+"']")[i]).val());
    }
  }
  return arr;
}
function my_ids_mix(ids)
{
  var m=new Array();
  m=explode(",",ids);
  var data=new Array();    
  for(i=0,max_i=m.length;i<max_i;i++)
  {
    array_push(data,m[i]+"="+encodeURIComponent($("#"+m[i]).val()));
  }
  return implode('&',data);
}  
function my_names_mix(indom)
{
  var m=new Array();
  var names=$(indom).find('*[req="group[]"]');    
  for(i=0,max=names.length;i<max;i++)
  {
    array_push(m,$(names[i]).attr('name')+"="+encodeURIComponent($(names[i]).val()));
  }
  return implode('&',m);
}
function basename(filepath)
{
  m=explode("/",filepath);
  mdata = explode("?",end(m));  
  return mdata[0];
}
function mainname(filepath)
{
  filepath = basename(filepath);
  mdata=explode(".",filepath);
  return mdata[0];
}
function subname(filepath)
{
  filepath = basename(filepath);
  m=explode(".",filepath);
  return end(m);
}