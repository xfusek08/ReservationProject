$(document).ready(function(){
  $(".adm-dayterms-tools-newbt").click(function(e){
    if (ClearContent(e))
    {
      BuildTermsForm($("#datepicker").datepicker('getDate'));      
    }
  });
  $('.adm-dayterms-view').on("click", ".adm-dayterms-view-term", function(event){
    SelectTerm($(this).attr('pk'));
  });  
  $('body').on('click', '.search-textbox img', function(e){
    var searchtext = $('.search-textbox input').val();
    if (searchtext != '')
    {
      if (ClearContent(e))
      {
        BuildSearchReservation(searchtext);  
      }
    }
  });
  $('body').on('focusin', '.search-textbox input', function(){
    $(this).bind('keydown', function(e){
      if (e.keyCode == 13) // enter
      {
        var searchtext = $(this).val();
        if (searchtext != '')
        {
          if (ClearContent(e))
          {
            BuildSearchReservation(searchtext)  
          }
        }
      }
    });
  });
  $('body').on('focusout', '.search-textbox input', function(){
    $(this).unbind('keydown');
  });
  $('body').on('click', '.conndetail-inhtml .reservations .reservation', function(){
    var res= $(this);
    DateSelect($("#datepicker"), res.attr('date'), function(){
      SelectTerm(res.attr('termpk'));
    });          
  });        
  
  setInterval(GetNewReservations, 10000);
});
function SelectTerm(pk)
{
  var term = $('.adm-dayterms-view .adm-dayterms-view-term[pk="' + pk + '"]');
  if (term.length > 0)
  {
    if (ClearContent(event))
    {
      var pre = $('.adm-dayterms-view .adm-dayterms-view-term.termsel');
      pre.removeClass("termsel");
      pre.css('-webkit-box-shadow', '0 1px 2px 0px rgb(100,100,100)');
      pre.css('-moz-box-shadow', '0 1px 2px 0px rgb(100,100,100)');
      pre.css('box-shadow', '0 1px 2px 0px rgb(100,100,100)');
      
      var newreselem = $('.adm-newresconn-conn .reservation[termpk="' + pk + '"]');
      
      term.addClass("termsel");
      term.css('-webkit-box-shadow', '0 0 0px 2px red inset');
      term.css('-moz-box-shadow', '0 0 0px 2px red inset');
      term.css('box-shadow', '0 0 0px 2px red inset');
      
      newreselem.css('-webkit-box-shadow', '0 0 0px 2px red inset');
      newreselem.css('-moz-box-shadow', '0 0 0px 2px red inset');
      newreselem.css('box-shadow', '0 0 0px 2px red inset');
      
      BuildTermDetail(term);
    }
  }
  else return false;
}
function ReloadData()
{
  var seltermpk = $('.adm-dayterms-view-term.termsel').attr('pk');
  var selDate = $("#datepicker").datepicker('getDate');
  LoadMonthOverview(selDate, true, function(){    
    LoadFreeReservations();
    GetNewReservations();
    SelectTerm(seltermpk);
  });  
}
function ClearContent(event) // volana kdyz se opouzti stranka --- zatim nereseno
{
  var success = true; 
  if ($(".adm-day-conn .checkbeforeclose").length > 0)
  {
    // nahradit vlastnim dialog. oknem
    success = confirm("Přejete si opravdu opustit neuložený formulář ?");
  }
  
  if (success)
  {
    $(".adm-day-conn").empty();   
  }
  else
  {
    if (event)
    {
      event.stopPropagation();
    }
  }
  
  return success;
}

function BuildTermsForm(date, tftype)
{
  //console.log(date);
  SendAjaxRequest(
    "type=getnewtermsform"+ 
    "&date=" + DateToStr(date) + 
    "&tftype=" + tftype,  
    true,
    function(response){
      BuildTermsFormFromXML(response);  
    }
  );
}
function BuildTermsFormFromXML(html)
{
  var elem = $(html);
  elem.appendTo(".adm-day-conn");
  
  elem.on("click", ".seltimebt", function(e){
    var timeinput = $(this).parent(".timeinput");
    CreateOnClickTimepicker(e, timeinput, $(this));
  });
  
  elem.on("click", ".removetimebt", function(){
    var row = elem.find('tr.datarow:last-child');        
    var cnt = parseInt(row.attr("cout")); 
    row.remove();
    if (cnt == 2)
    {
      $(this).remove();
    }
  });

  elem.on("click", ".addtimebt", function(){
    var datarow = elem.find('tr.datarow:last-child').clone();        
    var count = parseInt(datarow.attr("cout")) + 1; 
    datarow.attr("cout", count);
    datarow.find("td:first-child").text(count + '.');

    datarow.find('input[type="text"]').attr("name", 'tt' + count);
    datarow.find('input[type="checkbox"]').attr("name", 'tv' + count);

    datarow.find('input[type="text"]').val('');
    datarow.find('input[type="checkbox"]').prop('checked', true);

    datarow.find('.seltimebt.wait').removeClass("wait");

    datarow.find('td').eq(3).remove();

    if (count == 2 )
    {
      $('<div class="removetimebt">Odebrat</div>').appendTo(".newtermsform-options");          
    }
    datarow.appendTo(elem.find("table"));
  });
  
  elem.on("submit", "form", function(e){
    e.preventDefault();
  });

  elem.on("click", "input[type=submit]", function(e){
    OnClickAjaxSubmit(e, 'newtermsform', $(this), function(result){
      elem.remove();
      if (result == 'succes')
      {
        RaiseAnnouncement('Termíny úspěšně vytvořeny.', 'green');
        ReloadData();    
      }      
      else if (result == 'dbfail')
      {
        RaiseAnnouncement('Termíny se nepodařilo vytvořit.', 'red');        
      }
      else
      {
        BuildTermsFormFromXML(result);
      }
    });
  });
}
function CreateOnClickTimepicker(e, timeinput, bt)
{
  e.preventDefault();        
  if (!bt.is(".wait"))
  {
    timeinput.find("input[type=text]").timepicker({
      timeFormat: 'H:i',
      scrollDefault: 'now'
    }).on('hideTimepicker', function(){
      $(this).timepicker('remove');
      bt.addClass("wait");
      setTimeout(function(){
        bt.removeClass("wait");  
      },400);
    }).on('showTimepicker', function(){
      $(".ui-timepicker-wrapper").css({
        left: $(this).position().left - 4,
      });            
    }).timepicker('show');
  }
}

function BuildTermDetail(term)
{
  SendAjaxRequest(
    "type=gettermdetail"+ 
    "&pk=" + term.attr('pk'),
    true,
    function(response)
    {
      BuildContentDetailfromXML(response);      
    }  
  );  
}

function BuildContentDetailfromXML(xml)
{ 
  $(".adm-day-conn").empty();
  var xmlobj = $(xml);  
  xmlobj.find('alert').each(function(){
    RaiseAnnouncement($(this).attr('text'), $(this).attr('color'));
  });
  
  if (xmlobj.attr('unsett') == 'true')
  {
    ReloadData();
    return;
  }
  var elemhtml = 
    '<div class="conndetail">'+
      '<div class="conndetail-caption">' + xmlobj.attr('caption') +
        '<div class="conndetail-menu" title="možnosti"><img src="../images/menu.png"/></div>'+
      '</div>'+
      '<div class="conndetail-inhtml">' + xmlobj.find("inhtml").html() + '</div>'+
    '</div>';
  
  var contentDetail = $(elemhtml).appendTo('.adm-day-conn');
  contentDetail.on("click", ".conndetail-menu", function(event){
    event.stopPropagation();
    $(this).find("img").animate({height: "-=2px", marginTop: "+=1px", marginRight: "+=1px"}, 100, function(){
      $(this).animate({height: "+=2px", marginTop: "-=1px", marginRight: "-=1px"}, 100);
    });
    if ($('.conndetail-actions').length > 0)
    {
      $('.conndetail-actions').stop().slideToggle(200, "swing", function(){
        $(this).remove();    
      });
    }
    else
    {
      var menuhtml = '<div class="conndetail-actions">';
      xmlobj.find('action').each(function(){
        menuhtml += '<div class="conndetail-actions-action" ident="' + $(this).attr('ident') + '">' + $(this).attr('cap') + '</div>';
      });
      menuhtml += '</div>';

      var menuobj = $(menuhtml);

      menuobj.appendTo('body');
      menuobj.css({
        top: $(this).position().top + $(this).outerHeight() - 5,
        left: ($(this).position().left - menuobj.width() + $(this).outerWidth()) + "px"
      });
      menuobj.slideToggle(200, "swing"); 

      menuobj.on("click", ".conndetail-actions-action", function(event){
        event.stopPropagation();
        ContentDetailActionClick($(this), contentDetail);
        HideAcitons(menuobj)
      });

      $('html').click(function(){
        HideAcitons(menuobj)
      });
    }
  });  
  
  if (contentDetail.find('form').length > 0)
  {
    contentDetail.on("click", ".seltimebt", function(e){
      var timeinput = $(this).parent(".timeinput");
      CreateOnClickTimepicker(e, timeinput, $(this));
    });
      
    contentDetail.find('form').submit(function(e){e.preventDefault()});
    
    contentDetail.on('click', 'input[type=submit]', function(e){
      OnClickAjaxSubmit(e, 'contentdetail&event=formsubmit', $(this), function(result){  
        BuildContentDetailfromXML(result);    
      });
    });
  }
  if (contentDetail.find('.scriptinit').length > 0)
  {
    if (contentDetail.find('.scriptinit').attr('scriptname') == 'movereservation')
    {
      EnableResGrab(contentDetail);
    }
  }  
  if (xmlobj.attr('focusterm'))
  {
    SelectTerm(xmlobj.attr('focusterm'));
  }
  if (xmlobj.attr('refresh') == 'refresh')
  {
    ReloadData();
  }
  FreeTermDroppable();
  GetNewReservations();
}
function HideAcitons(menuobj)
{
  menuobj.stop().slideToggle(200, "swing", function(){
    menuobj.remove();    
  });           
}
function ContentDetailActionClick(action, contentDetail)
{
  SendAjaxRequest(
    "type=contentdetail"+ 
    "&event=actionclick"+ 
    "&ident=" + action.attr('ident'),
    true,
    function(response)
    {
      BuildContentDetailfromXML(response);
    }
  );
}
function BuildSearchReservation(searchtext)
{ 
  var html = 
    '<div class="conndetail">'+
      /*'<div class="conndetail-caption">'+
        'Vyhledat: ' + searchtext + 
        '<div class="conndetail-menu">'+
          '<img src="../images/cross.png" />'+
        '</div>'+
      '</div>'+*/
      '<div class="conndetail-inhtml">'+
        '<div class="timelimit">'+
          'Od: <input class="inputdatepicker" type="text" name="fromdate"/> '+
          'Do: <input class="inputdatepicker" type="text" name="todate"> '+
          'Nalezeno: <span class="searchcount"></span>'+
          '<img src="../images/cross.png" />'+
        '</div>' +
        '<div class="reservations"></div>'+
      '</div>' +
    '</div>';
  var elem = $(html).appendTo('.adm-day-conn');
  elem.find('.timelimit img').height(15);
  elem.on('mouseover', '.timelimit img', function(){
    $(this).attr('src', '../images/crossActive.png');
  });
  elem.on('mouseout', '.timelimit img', function(){
    $(this).attr('src', '../images/cross.png');
  });
  elem.on('click', '.timelimit img', function(){
    $('.search-textbox input[type=text]').val('');
    elem.remove();
  });
  elem.find('.conndetail-inhtml').css({padding: 0});
  elem.find('.conndetail-inhtml .reservations').css({
    maxHeight: $('.adm-day-conn').height() - $('.conndetail-inhtml .timelimit').outerHeight() - 2
  });
  elem.find('input[name=fromdate]').datepicker({
    beforeShowDay: function (date){
      var result = new Array(true, '');      
      var todate = StrToDate($('.conndetail-inhtml input[name=todate]').val());
      if (!isNaN(todate))
      {
        if (date > todate)
        {
          result[0] = false;                
        }
      }
      return result;
    }
  });
  elem.find('input[name=todate]').datepicker({
    beforeShowDay: function (date){
      var result = new Array(true, '');      
      var fromdate = StrToDate($('.conndetail-inhtml input[name=fromdate]').val());
      if (!isNaN(fromdate))
      {
        if (date < fromdate)
        {
          result[0] = false;                
        }
      }
      return result;
    }
  });
  
  $('.conndetail-inhtml').on('change', '.inputdatepicker', function(){
    SearchReservations(elem, searchtext);
  });
  $('.conndetail-inhtml input[name=fromdate]').datepicker('setDate', new Date());
  SearchReservations(elem, searchtext);
}
function SearchReservations(elem, searchtext)
{
  var count = 0;
  SendAjaxRequest(
    "type=reservationsearch"+ 
    "&searchtext=" + searchtext +
    "&fromdate=" + $('.conndetail-inhtml input[name=fromdate]').val() + 
    "&todate=" + $('.conndetail-inhtml input[name=todate]').val(),
    true,
    function(response){
      //console.log(response);  
      $('.conndetail-inhtml .reservations').empty();
      if ($(response).find('reservation').length == 0)
      {
        $('<div class="noresfound">Žádné odpovídající rezervace</div>').appendTo(elem.find('.conndetail-inhtml .reservations'));        
      }
      else
      {
        //count = BuildSearch1(elem, response);
        //count = BuildSearch2(elem, response);
        count = BuildSearch3(elem, response);
      }
      $('.conndetail-inhtml .searchcount').text(count);
    }
  );
}
/*
function BuildSearch1(elem, response)
{
  var count = 0;
  var inhtml = 
    '<table>'+
      '<thead>'+
        '<tr>'+
          '<th>Datum</th>'+
          '<th>Čas</th>'+
          '<th>Jméno a příjmení</th>'+
          '<th>Vytvořeno</th>'+
        '</tr>'+
      '</thead>'+
      '<tbody>';
  $(response).find('reservation').each(function(){          
    inhtml += 
      '<tr'+
          ' class="reservation"'+
          ' pk="' + $(this).attr('respk') + '"'+
          ' termpk="' + $(this).attr('termpk') + '"'+
          ' date="' + $(this).attr('fromdate') + '"'+
      '>'+
        '<td>' + $(this).attr('fromdate') + '</td>'+
        '<td>' + $(this).attr('fromtime') + '</td>'+
        '<td>' + $(this).attr('firstname') +' '+ $(this).attr('lastname')  + '</td>'+
        '<td>' + $(this).attr('created') + '</td>'+
      '</tr>';
    count++;
  });

  inhtml += '</tbody></table>';
  $(inhtml).appendTo(elem.find('.conndetail-inhtml .reservations'));
  
  return count;
}
function BuildSearch2(elem, response)
{
  var count = 0;
  $(response).find('reservation').each(function(){
    count++;
    var inhtml = ReservationXMLtoDivHTML($(this));  
    $(inhtml).appendTo(elem.find('.conndetail-inhtml .reservations'));
  });
  return count;
}
*/
function BuildSearch3(elem, response)
{
  var count = 0;
  var actdatestr = '';
  
  $(response).find('reservation').each(function(){       
    var inhtml = '';
    if (actdatestr != $(this).attr('fromdate'))
    {
      inhtml += '<div class="daycap">' + $(this).attr('fromdate') + '</div>';     
      actdatestr = $(this).attr('fromdate');
    }
    inhtml += 
      '<table'+
          ' class="reservation"'+
          ' pk="' + $(this).attr('respk') + '"'+
          ' termpk="' + $(this).attr('termpk') + '"'+
          ' date="' + $(this).attr('fromdate') + '"'+
      '>'+
        //'<div>' + $(this).attr('fromdate') + '</div>'+
        '<td width="50" style="padding-left: 30px;">' + $(this).attr('fromtime') + '</td>'+
        '<td width="100%">' + $(this).attr('firstname') +' '+ $(this).attr('lastname')  + '</td>'+
        '<td>Vytvořeno: ' + $(this).attr('created') + '</td>'+
      '</table>';
    $(inhtml).appendTo(elem.find('.conndetail-inhtml .reservations'));
    count++;
  });
  return count;
}
