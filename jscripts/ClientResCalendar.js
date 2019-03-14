var TermsXMLHandle = '<months></months>';
var drawmonth = 0;
/*
document.onkeydown = function(e){
  if ((e.which || e.keyCode) == 116 || ((e.which || e.keyCode) == 82 && ctrlKeyDown))
  {
    e.stopPropagation();
    window.location.href = window.location.href;
  } else if ((e.which || e.keyCode) == 17) {
      // Pressing  only Ctrl
      ctrlKeyDown = true;
  }
};
*/
$(document).ready(function ()
{
  drawmonth = (new Date()).getMonth();
  InitDatePicker();
  LoadTerms(new Date(), true, function ()
  {
    if ($(".termchoose").attr("termpk").length > 0)
    {
      if (($(".termchoose").attr("termpk")) > 0)
      {
        var day = $(TermsXMLHandle).find('term[pk="' + $(".termchoose").attr("termpk") + '"]').closest("day").attr("date");
        if (day)
        {
          DateSelect($("#datepicker"), day);
          SelectTerm($('.daytermview'), $(".termchoose").attr("termpk"));
        }
      }
    }
  });

  setInterval(function ()
  {
    LoadTerms($('#datepicker').datepicker('getDate'), true);
  }, 30000); // 30s

  $('.res-form-requrie').on("keyup", 'input[type=text]', function ()
  {
    $(this).addClass("res-form-fadeoutcolor");

    setTimeout(function ()
    {
      $(this).closest(".res-form-requrie").removeClass("res-form-requrie");
      $(this).removeClass("res-form-fadeoutcolor");
    }, 400);
  });
});

function LoadTerms(date, asynch, CallBack)
{
  var month = date.getMonth(); // pocitame 0 - 11

  var DateFrom = new Date(date.getFullYear(), month - 1, 1);
  var DateTo = new Date(date.getFullYear(), month + 3, 1);

  //console.log("From: " + DateToStr(DateFrom));
  //console.log("To:" + DateToStr(DateTo));

  $.ajax({
    url: location.protocol + '//' + location.host + location.pathname,
    type: "POST",
    async: asynch,
    data: "ajax=true" +
      "&type=getterms" +
      "&fromdate=" + DateToStr(DateFrom) +
      "&todate=" + DateToStr(DateTo),
    success: function (xml)
    {
      //console.log(xml);
      //TermsXMLHandle = '<days></days>';

      var termHandleObj = $(TermsXMLHandle);

      $(xml).find("month").each(function ()
      {
        //console.log($(this).html());

        var handlemonth = termHandleObj.find('month[monthnum="' + $(this).attr('monthnum') + '"]');

        //console.log(handlemonth.html());

        if (handlemonth.length > 0)
        {
          //console.log("existuje");
          //console.log(termHandleObj.find('month[monthnum="' + $(this).attr('monthnum') + '"]'));
          handlemonth.html($(this).html());
        }
        else
        {
          //console.log("neexistuje");
          $('<month monthnum="' + $(this).attr("monthnum") + '">' + $(this).html() + '</month>').appendTo(termHandleObj);
        }
      });

      TermsXMLHandle = '<months>' + termHandleObj.html() + '</months>';
      //console.log(TermsXMLHandle);

      if (asynch)
      {
        $('#datepicker').datepicker('refresh');
      }
      if (typeof(CallBack) == 'function')
      {
        CallBack();
      }
    }
  });
}

function InitDatePicker()
{
  $.datepicker.regional['cs'] = {
    closeText: 'Cerrar',
    prevText: '<',
    nextText: '>',
    currentText: 'Hoy',
    monthNames: ['Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen', 'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec'],
    monthNamesShort: ['Le', 'Ún', 'Bř', 'Du', 'Kv', 'Čn', 'Čc', 'Sr', 'Zá', 'Ří', 'Li', 'Pr'],
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
    numberOfMonths: 2,
    stepMonths: 1
  };
  $.datepicker.setDefaults($.datepicker.regional['cs']);

  $("#datepicker").datepicker({
    beforeShowDay: function (date)
    {
      result = new Array(false, '');
      LoadDayData(result, $(this), date);
      return result;
    },
    onSelect: function (datestr, datepicker)
    {
      DateSelect($(this), datestr);
      datepicker.drawMonth = drawmonth;
      $('.daytermview').find('.dtr-content').css({
        maxHeight: ($('.ui-datepicker-group table tbody').outerHeight() - 10) + 'px'
      });
    },
    onChangeMonthYear: function (year, month)
    {
      $('.daytermview').hide();
      $("input[name=c_selterm]").remove();
      var date = new Date(year, month - 1, 1);
      $(this).datepicker('setDate', date);
      LoadTerms(date, true);
      drawmonth = month - 1;
    }
  }).find(".ui-state-active").removeClass("ui-state-active");

}

function LoadDayData(result, datepicker, date)
{
  var day = null;
  day = $(TermsXMLHandle).find('day[date="' + DateToStr(date) + '"]');
  var nowdate = new Date();
  nowdate.setHours(0, 0, 0, 0);
  if (day.length > 0 && date >= nowdate) // na dnesek je uz pozde ? 
  {
    result[0] = true;
  }
}

function DateSelect(datepic, date)
{
  datepic.datepicker('setDate', date);

  var DateTermWiew = $('.daytermview');
  DateTermWiew.css('display', 'table-cell');

  if (DateTermWiew.attr("date") != date)
  {

    $("input[name=c_selterm]").remove();

    DateTermWiew.attr("date", date);
    DateTermWiew.find('.dtr-conn .dtr-header').text($.datepicker.formatDate('DD', StrToDate(date)) + ' ' + date);

    var DateTermContent = DateTermWiew.find('.dtr-conn .dtr-content');
    DateTermContent.empty();
    var elem = '';

    $(TermsXMLHandle).find('day[date="' + date + '"]').find("term").each(function ()
    {
      elem = '<div class="dtr-day-term" pk="' + $(this).attr("pk") + '">' + '' + $(this).attr('time') + '</div>';    // pridat data
      $(elem).appendTo(DateTermContent);
    });

    DateTermContent.on('click', '.dtr-day-term', function ()
    {
      SelectTerm(DateTermContent, $(this).attr('pk'));
    });
  }
}

function SelectTerm(DateTermContent, pk)
{
  DateTermContent.find('.dtr-state-selected').removeClass('dtr-state-selected');
  DateTermContent.find('.dtr-day-term[pk=' + pk + ']').addClass('dtr-state-selected');
  $("input[name=c_selterm]").remove();
  var html = '<input type="hidden" name="c_selterm" value="' + pk + '"/>';
  $(html).appendTo($("form"));
}

function CheckLoadedMonth(dpick, month)
{
  if (!($(TermsXMLHandle).find('month[monthnum="' + month + '"]').length > 0))
  {
    var spinner = $('<div class="spinner"><img class="img-spinner" src="resources/images/ajax-loader.gif"/></div>');
    spinner.css({
      top: (dpick.position().top + dpick.outerHeight() / 2 - 2) + 'px',
      left: (dpick.position().left + dpick.outerWidth() / 2 - 23) + 'px'});
    spinner.appendTo(dpick.find(".ui-datepicker"));
  }
}

