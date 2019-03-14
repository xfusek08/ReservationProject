
$(document).ready(function(){
  $('body').on('click', '.adm-newresconn-conn .reservation', function(){
    var res = $(this);
    DateSelect($("#datepicker"), res.attr('date'), function(){
      SelectTerm(res.attr('termpk'));
    });
  });
  $('body').on('mouseover', '.adm-newresconn-conn .reservation', function(){
    $('.adm-dayterms-view-term[pk="' + $(this).attr('termpk') + '"]').addClass('selhover');
  });
  $('body').on('mouseout', '.adm-newresconn-conn .reservation', function(){
    $('.adm-dayterms-view-term[pk="' + $(this).attr('termpk') + '"]').removeClass('selhover');
  });
});

// deleme samostatnym dotazem, bez casoveho omezeni ..
function GetNewReservations()
{
  SendAjaxRequest(
    'type=getnewres',
    true,
    function(response){
      var reload = false;
      var rescount = 0;
      $('.adm-newresconn-conn .reservation').each(function(i){
        if ($(response).find('reservation[respk="' + $(this).attr('pk') + '"]').length == 0)
        {
          var reselem = $(this);
          rescount++;
          setTimeout(function(){
            rescount--;
            reselem.slideToggle(300, function(){
              reselem.mouseout();
              reselem.remove();
              $('.adm-newresconn-caption .newrescount').text(rescount);
            });

            if ($(response).find('reservation[fromdate="' + reselem.attr('date') + '"]').length == 0)
            {
              var td = GetCalenDayTDByDate(StrToDate(reselem.attr('date')), '.hasnew');
              if (td != null)
              {
                td.animate({
                  backgroundColor: 'initial'
                }, 300, "swing", function(){
                  $(this).removeClass('hasnew');
                });
              }
            }
            $('.adm-dayterms-view-term[pk="' + reselem.attr('termpk') + '"] .newtag').animate({
              opacity: 0.0
            }),300, 'swing', function(){
              $(this).remove;
            };
          },500);
        }
      });
      $(response).find('reservation').each(function(i){
        rescount++;
        if ($('.adm-newresconn-conn .reservation[pk="' + $(this).attr('respk') + '"]').length == 0)
        {
          var html = ReservationXMLtoDivHTML($(this));
          var res = $(html).css({background: 'rgb(245,245,100)'}).hide();
          res.appendTo('.adm-newresconn-conn');
          setTimeout(function(){
            res.toggle( "slide", {direction: 'right'}, function(){
              res.mouseover(function(){
                res.css({background: 'rgba(220,250,255,1)'});
              });
              res.mouseout(function(){
                res.css({background: 'rgb(245,245,100)'});
              });
            });
          } , 150 + i * 70);           
          var td = GetCalenDayTDByDate(StrToDate($(this).attr('fromdate')), ':not(hasnew)');
          if (td != null)
          {
            td.animate({
              backgroundColor: 'rgb(250,250,0)'
            }, 300, "swing", function(){
              $(this).addClass('hasnew');
            });
          }
          var actterm = $('.adm-dayterms-view-term[pk="' + $(this).attr('termpk') + '"]');
          if (actterm.find('.adm-dayterms-view-term-content > div:not(.reservation)').length > 0)
          {
            actterm.find('.adm-dayterms-view-term-content').empty();
            var html = '<div class="reservation">';   
            html += '<table>';                        
            html += '<tr>';                        
            html += '<td>Voucher:</td><td>' + $(this).attr('vouchernum') + '</td>';                        
            html += '</tr>';              
            html += '<tr>';                        
            html += '<td>Jméno:</td><td>' + $(this).attr('firstname') + ' ' + $(this).attr('lastname') + '</td>';                        
            html += '</tr>';              
            html += '</table>'; 
            html += '</div>';
            $(html).appendTo(actterm.find('.adm-dayterms-view-term-content'));
            reload = true;
          }
          AppendNewTagToTerm($(this).attr('termpk'), true);
          $('body').on('mouseover', '.adm-dayterms-view-term[pk="' + res.attr('termpk') + '"]', function(){
            res.mouseover();
          });
          $('body').on('mouseout', '.adm-dayterms-view-term[pk="' + res.attr('termpk') + '"]', function(){
            res.mouseout();
          });
        }
      });      
      $('.adm-newresconn-caption .newrescount').text(rescount);
      if (reload)
      {
        setTimeout(function(){ReloadData();}, 300);
      }
    }
  );  
}
function AppendNewTagToTerm(pk, animate)
{
  var term = $('.adm-dayterms-view-term[pk="' + pk + '"]');
  if (term.length == 0) return;
  
  var newtag = $('<div class="newtag">Nové</div>');
  newtag.css({opacity: 0.0});
  newtag.appendTo(term);
  newtag.css({
    left: term.offset().left + term.width() - newtag.width() - 5,
    top: term.offset().top + 5
  });
  if (animate)
  {
    newtag.animate({opacity: 1.0}, 300, 'swing');
  }
  else
  {
    newtag.css({opacity: 1.0});
  }
}
function GetCalenDayTDByDate(date, selector)
{
  var td = null;
  $('#datepicker').find(
      'table tbody td' +
      '[data-month="' + date.getMonth() + '"]' +
      '[data-year="' + date.getFullYear() + '"]' + selector).each(function(){
    if ($(this).find('a').text() == date.getDate())
    {
      td = $(this);
    }    
  });
  return td;  
}
function ReservationXMLtoDivHTML(reservation)
{
  var html = 
   '<div class="reservation selhover" pk="' + reservation.attr('respk') + '" termpk="' + reservation.attr('termpk') + '" date="' + reservation.attr('fromdate') + '">' +
     '<div class="term">' + reservation.attr('fromdate') + ', ' + reservation.attr('fromtime') + ' (' + reservation.attr('dayname') + ')'+ '</div>' +
     '<table>' +
       '<tr><td>Voucher:</td><td>' + reservation.attr('vouchernum') + '</td></tr>'+
       '<tr><td>Jméno:</td><td>' + reservation.attr('firstname') +' '+ reservation.attr('lastname') + '</td></tr>'+
     '</table>' +
     '<div class="created"><span>Vytvořeno:</span><span>' + reservation.attr('created') + '</span></div>' +            
   '</div>';
   return html;
}