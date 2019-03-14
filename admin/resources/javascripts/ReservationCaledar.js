var TermMonthdata = '<months></months>';
var PrevDate = 0;
var ctrlKeyDown = false;
var changeonyearfunc = null;

document.addEventListener('DOMContentLoaded', function (e){
  document.body.addEventListener('keydown', function (e){
    if ((e.which || e.keyCode) == 116 || ((e.which || e.keyCode) == 82 && ctrlKeyDown))
    {
      e.stopPropagation();
      e.preventDefault();
      // Pressing F5 or Ctrl+R
      SendAjaxRequest(
        'type=setnavigation'+
        '&date=' + DateToStr($('#datepicker').datepicker("getDate")),
        false, 
        function(response)
        {
          if (response == 'succes')
            window.location.reload()        
        }
      );
    } else if ((e.which || e.keyCode) == 17) {
        // Pressing  only Ctrl
        ctrlKeyDown = true;
    }
  }, false);
}, false);

document.addEventListener('DOMContentLoaded', function (e){
  document.body.addEventListener('keyup', function (e){
    if ((e.which || e.keyCode) == 17) 
        ctrlKeyDown = false;
  }, false);
}, false);

$(document).ready(function () {  
  InitDatePicker();
  LoadActNavigation();  
});

function LoadActNavigation()
{
  SendAjaxRequest(
    'type=getnavigation',    
    true, 
    function(response)
    {
      var LoadDate = new Date();
      var datestr = $(response).find('date').text();
      var termpk = $(response).find('temrpk').text();
      if (datestr != '')
      {
        LoadDate = StrToDate(datestr);
      }  
      LoadMonthOverview(LoadDate, true, function(){
        SelectTerm(termpk);
        GetNewReservations();
      });    //nacte 3 mesice prehledu
   }
  );
}
function InitDatePicker()
{
  PrevDate = new Date();
  
  $.datepicker.regional['cs'] = {
    closeText: 'Cerrar',
    prevText: '<',
    nextText: '>',
    currentText: 'Hoy',
    monthNames: ['Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen', 'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec'],
    monthNamesShort: ['Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen', 'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec'],
    dayNames: ['Neděle', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota'],
    dayNamesShort: ['Ne', 'Po', 'Út', 'St', 'Čt', 'Pá', 'So', ],
    dayNamesMin: ['Ne', 'Po', 'Út', 'St', 'Čt', 'Pá', 'So'],
    weekHeader: 'Sm',
    dateFormat: 'dd.mm.yy',
    firstDay: 1,
    isRTL: true,
    showMonthAfterYear: false,
    yearSuffix: '',
    showOtherMonths: true,
    selectOtherMonths: true,
    numberOfMonths: 1,
    changeMonth: true,
    changeYear: true,
    showButtonPanel: false
  };
  
  $.datepicker.setDefaults($.datepicker.regional['cs']);
  
  $("#datepicker").datepicker({
    /*BeforeLoadDays: function(date){
      result = new Array(true, '');      
      LoadDayData(result, $(this), date);      
      return result;
    },*/
    onSelect: function(date){ 
      DateSelect($(this), date);
    },
    afterShow: function(input, inst, td){
      AfterLoadDays($(this));
      $(this).find('.ui-datepicker-title').append('<button class="today">Dnes</button>');
    },
    onChangeMonthYear: function(year, month){ 
      var dtpic = $(this).datepicker('getDate');
      LoadMonthData(true, new Date(year, month - 2, 1), new Date(year, month, 1), function(){
        LoadMonthReservationData(true, new Date(year, month - 2, 1), new Date(year, month, 1), function(){
          if (dtpic && dtpic.getMonth() + 1 == month && dtpic.getFullYear() == year)
            LoadTermsDayView(DateToStr(dtpic));
          else
            LoadTermsDayView('');
          if (typeof(changeonyearfunc) == 'function')
          {
            changeonyearfunc();
          }
        });
      });
    }
  }).on('click', 'button.today', function(){
    DateSelect($('#datepicker'), DateToStr(new Date()));
  });  
}
function AfterLoadDays(calendar)
{
  calendar.find("table td").each(function(){
    var day = parseInt($(this).find("*").text());
    var month = parseInt($(this).attr('data-month'));
    var year = parseInt($(this).attr('data-year'));
    var date = new Date(year, month, day);
    
    var dayelem = $(TermMonthdata).find('day[date="' + DateToStr(date) + '"]');
    
    var FreeTermNum = 0;
    var InvisibleNum = 0;
    var ReservationNum = 0;
    
    var html =
      '<div class="termdata">';

    if (dayelem.length > 0)
    {
      FreeTermNum = dayelem.find('term[state=0]').size();
      InvisibleNum = dayelem.find('term[state=2]').size();
      ReservationNum = dayelem.find('term[state=1]').size();
      
      if (parseInt(FreeTermNum) > 0)
      {
        html = html +
          '<div class="freetermnum">' + FreeTermNum + '</div>';
          //'<div class="freetermnum"></div>';
      }
      else 
      {
        html = html + '<div></div>';        
      }

      if (parseInt(InvisibleNum) > 0)
      {
        html = html +
          '<div class="invisiblenum">' + InvisibleNum + '</div>';
          //'<div class="invisiblenum"></div>';
      }
      else
      {
        html = html + '<div></div>';                
      }
    
      if (parseInt(ReservationNum) > 0)
      {
        html = html +
          '<div class="reservationnum">' + ReservationNum + '</div>';
          //'<div class="reservationnum"></div>';
      }
      else 
      {
        html = html + '<div></div>';        
      }      
    }
    
    html = html + '</div>';    
    
    $(html).appendTo($(this));
    
    if ($('.adm-newresconn-conn .reservation[date="' + DateToStr(date) + '"]').length > 0)
    {
      $(this).css({
        backgroundColor: 'rgb(250,250,0)'
      }).addClass('hasnew');
    }
  });
}
function DateSelect(datepic, date, CallBack)
{ 
  if (ClearContent())
  {
    // toto kostrbate rezeni mam kvuli tomu, ze pokud preskakujeme na jinej mesic a rok tak se automaticky zavola event 
    // onChangeMonthYear a ten si najde potrebna data, pokud je nema, a nasledne zavola LoadTermsDayView 
    // takze nepotrebujeme volat 2x 
    var loadterms = 
      datepic.datepicker('getDate').getMonth() == StrToDate(date).getMonth() &&
      datepic.datepicker('getDate').getFullYear() == StrToDate(date).getFullYear();
    
    if (!loadterms)
    {
      changeonyearfunc = function(){
        if (typeof(CallBack) == 'function')
          CallBack();
        changeonyearfunc = null;
      }; 
      datepic.datepicker('setDate', StrToDate(date));
    }
    datepic.datepicker('setDate', StrToDate(date));
    if (loadterms)
      LoadTermsDayView(date, CallBack);
  }
  else
  {
    datepic.datepicker('setDate', DateToStr(PrevDate));
  }
  
  PrevDate = StrToDate(date);
}
function LoadMonthData(asynch, fromdate, todate, CallBack)
{
  SendAjaxRequest(
    "type=getterms"+ 
    "&fromdate=" + DateToStr(fromdate) +  
    "&todate=" + DateToStr(todate),
    asynch,
    function(xml)
    {
      var termHandleObj = $(TermMonthdata);
      $(xml).find("month").each(function(){   
        var handlemonth = termHandleObj.find('month[monthnum="' + $(this).attr('monthnum') + '"]');
        if (handlemonth.length > 0)
        {
          handlemonth.html($(this).html());
        }
        else
        {
          $('<month monthnum="' + $(this).attr("monthnum") + '">' + $(this).html() + '</month>').appendTo(termHandleObj);
        }
      });
      
      TermMonthdata = '<months>' + termHandleObj.html() + '</months>';
      if (asynch)
      {
        $('#datepicker').datepicker('refresh');
      }
      if (typeof(CallBack) == 'function')
      {
        CallBack(xml);
      }
    }  
  );    
}
function LoadMonthReservationData(asynch, fromdate, todate, CallBack)
{
  SendAjaxRequest(
    "type=getreservations"+ 
    "&fromdate=" + DateToStr(fromdate) +  
    "&todate=" + DateToStr(todate),
    asynch, 
    function(xml)
    {
      var termHandleObj = $(TermMonthdata);
      $(xml).find("reservation").each(function(){   
        var termObj = termHandleObj.find('term[pk="' + $(this).attr('termpk') + '"]');
        termObj.empty();
        $(this).appendTo(termObj);
      });
      TermMonthdata = '<months>' + termHandleObj.html() + '</months>';
      if (typeof(CallBack) == 'function')
      {
        CallBack(xml);
      }
    }
  );

}
function LoadMonthOverview(date, asynch, CallBack)
{
  var month = date.getMonth(); // pocitame 0 - 11

  var DateFrom = new Date(date.getFullYear(), month - 1, 1);
  var DateTo = new Date(date.getFullYear(), month + 2, 0);
  LoadMonthData(asynch, DateFrom, DateTo, function(){
    $('#datepicker').datepicker('setDate', date);
    LoadMonthReservationData(asynch, DateFrom, DateTo, function(){
      LoadTermsDayView(DateToStr(date), CallBack);  
    });    
  });
}

function LoadTermsDayView(date, CallBack)
{
  $('.adm-dayterms-view').empty();
  var DayObjHanled = $(TermMonthdata).find('day[date="' + date + '"]');
  var html = '';
  
  if (DayObjHanled.length == 0)
  {
    html = '<div class="adm-dayterms-view-nodata">Žádná data</div>';
    $(html).appendTo('.adm-dayterms-view');
  }
  else
  {
    DayObjHanled.find("term").each(function(){
      html = '<div class="adm-dayterms-view-term" pk="' + $(this).attr('pk') + '">';
      html += '<div class="adm-dayterms-view-term-time">' + $(this).attr('time') + '</div>';
      
      html += '<div class="adm-dayterms-view-term-content">';
      if ($(this).attr('state') == '0')
      {
        html += '<div class="free">Volno</div>';
      }
      else if ($(this).attr('state') == '1')
      {
        var res = $(this).find("reservation");
        html += '<div class="ontermreservation">';                        
        
        html += '<table>';                        
        html += '<tr>';                        
        html += '<td>Voucher:</td><td>' + res.attr('vouchernum') + '</td>';                        
        html += '</tr>';              
        html += '<tr>';                        
        html += '<td>Jméno:</td><td>' + res.attr('firstname') + ' ' + res.attr('lastname') + '</td>';                        
        html += '</tr>';              
        html += '</table>'; 
        html += '</div>';
      }
      else if ($(this).attr('state') == '2')
      {
        var res = $(this).find("reservation");
        html += '<div class="invisible">Skryté</div>';
      }
      html += '</div>';
      //html +='<div class="menu"><img src="../images/menu.png" /></div>';
      html += '</div>';      
      $(html).appendTo('.adm-dayterms-view');
      if ($('.adm-newresconn-conn .reservation[termpk="' + $(this).attr('pk') + '"]').length > 0)
      {
        AppendNewTagToTerm($(this).attr('pk'), false);
      }
    });  
  } 
  CreateDroppables();
  //GetNewReservations();
  if (CallBack && typeof(CallBack) == "function")
  {
    CallBack();
  }
}
