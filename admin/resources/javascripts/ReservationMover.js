var FreeResConn = null;

$(document).ready(function(){
  BuildFreeResConn();
  LoadFreeReservations();
});

var Reservations = [];
var FreeResConn = null;
var Freeresconnheight = 250;
var Minimized  = true;
var SelBcgrColor = "rgb(200,255,200)";

function BuildFreeResConn()
{    
  FreeResConn = $('.adm-freeresconn');
  var newheight = $(window).outerHeight() - $('.adm-topheader').outerHeight();
  if (newheight < 500) newheight = 500;
  $('.adm-leftpanel-intable').height(newheight);
  SetUpConnHeight();
  FreeResConn.width($('.adm-upconn').width());
  $(window).resize(function(){
    var newheight = $(window).outerHeight() - $('.adm-topheader').outerHeight();
    if (newheight < 500) newheight = 500;
    $('.adm-leftpanel-intable').height(newheight);
    SetUpConnHeight();
    $('.adm-upconn').width($(window).outerWidth() - $('.adm-leftpanel').outerWidth());
    FreeResConn.width($('.adm-upconn').width());
  });
  FreeResConn.on('click', '.freeresconn-caption', ToggleUpDown);
};
function SetUpConnHeight()
{
  var newheight = 0;
  if (Minimized)
  {
    FreeResConn.find('.freeresconn-conn').height(0);
    newheight = $('.adm-leftpanel-intable').outerHeight() - FreeResConn.find('.freeresconn-caption').outerHeight() - 31;
  }
  else
  {
    FreeResConn.find('.freeresconn-conn').height(Freeresconnheight - FreeResConn.find('.freeresconn-caption').outerHeight());
    newheight = $('.adm-leftpanel-intable').outerHeight() - Freeresconnheight - 31;
  }

  $('.adm-upconn > div .adm-newresconn-conn').css({height: newheight+ 'px'});
  $('.adm-upconn > div .adm-day-conn').css({height: newheight + 'px'});
  $('.adm-upconn').height(newheight);
}
function ToggleUpDown()
{
  var offsetanim = 0;
  if (Minimized)
  {
    offsetanim = Freeresconnheight - FreeResConn.find('.freeresconn-caption').outerHeight();
    FreeResConn.find('.freeresconn-conn').animate({
      height: '+=' + offsetanim + 'px'
    }, 250, "swing");
    $('.adm-upconn > div .adm-day-conn').animate({
      height: '-=' + offsetanim + 'px'
    }, 250, "swing");
    $('.adm-upconn > div .adm-newresconn-conn').animate({
      height: '-=' + offsetanim + 'px'
    }, 250, "swing");
    $('.conndetail-inhtml .reservations').animate({
      maxHeight: '-=' + offsetanim + 'px'
    }, 250, "swing");
    $('.adm-upconn').stop().animate({
        height: '-=' + offsetanim + 'px'
      },
      250, "swing", function(){
        FreeResConn.find('.freeresconn-caption').find('.maxminbt img').attr('src', '../images/Down.png');
        Minimized = false;
        SetUpConnHeight();
      }
    ); 
  }
  else
  {
    offsetanim = Freeresconnheight - FreeResConn.find('.freeresconn-caption').outerHeight();
    FreeResConn.find('.freeresconn-conn').animate({
      height: '-=' + offsetanim + 'px'
    }, 250, "swing");
    $('.adm-upconn > div .adm-day-conn').animate({
      height: '+=' + offsetanim + 'px'
    }, 250, "swing");
    $('.adm-upconn > div .adm-newresconn-conn').animate({
      height: '+=' + offsetanim + 'px'
    }, 250, "swing");
    $('.conndetail-inhtml .reservations').animate({
      maxHeight: '+=' + offsetanim + 'px'
    }, 250, "swing");
    $('.adm-upconn').stop().animate({
      height: '+=' + offsetanim + 'px'
      },
      250, "swing", function(){
        FreeResConn.find('.freeresconn-caption').find('.maxminbt img').attr('src', '../images/Up.png');
        Minimized = true;
        SetUpConnHeight();
      }
    );
  }
};

function EnableResGrab(termdetail)
{
  var ReservationDetail = termdetail.find(".termdetail-reservation");
  
  ReservationDetail.css({
    border: "2px dashed black",
    cursor: "pointer"
  }).draggable({
    revert : function(){
      var draggable = $(this);
      setTimeout(function(){
        draggable.css('visibility', 'visible');            
      }, 300);
      return true;
    },
    helper: "clone",            
    cursorAt: { left: 5, top: 10 },
    revertDuration: 300,
    scroll: false,
    scope: "termres",
    start: function(event, ui){   
      $(this).css('visibility', 'hidden');
    },
    stop: function(event, ui){
    }
  });
  CreateResMoveDropper();
}

function LoadFreeReservations()
{
  SendAjaxRequest(
    'type=getfreeres',
    true,
    function(response){
      FreeResConn.find('.freeresconn-conn').empty();
      var freerescounter = 0;
      var html = 
        '<div class="resconn">'+
          '<table class="header">'+
            '<tr>'+
              '<td>Číslo voucheru</td>'+
              '<td>Jméno a příjmení</td>'+
              '<td>E-mail</td>'+
              '<td>Telefon</td>'+
              '<td>Adresa</td>'+
              //'<td>Poznámka</td>'+
              '<td>Vytvořeno</td>'+
            '</tr>'+
          '</table>';
      $(response).find('reservation').each(function(){
        freerescounter++;
        html += '<div class="termdetail-reservation" pk="' + $(this).attr('respk') + '">'+
                  '<div class="termdetail-reservation-caption">Rezervace</div>'+
                  '<table>'+
                    '<tr>'+
                      '<td>' + $(this).attr('vouchernum') +'</td>'+
                      '<td>' + $(this).attr('firstname') + ' ' + $(this).attr('lastname') + '</td>'+
                      '<td>' + $(this).attr('email') +'</td>'+
                      '<td>' + $(this).attr('telnum') +'</td>'+
                      '<td>' + $(this).attr('address') +'</td>'+
                      '<td>' + $(this).attr('created') +'</td>'+
                    '</tr>'+
                  '</table>'+
                '</div>';
      });
      html += '</div>';
      FreeResConn.find('.rescount').text(freerescounter);
      var elem = $(html);
      if (freerescounter > 0)
      {
        elem.appendTo(FreeResConn.find('.freeresconn-conn'));
        elem.find(".header").css({
          marginLeft: elem.find(".termdetail-reservation-caption").outerWidth() + 10        
        }); 
        CalcFreeResContentWidth();
        elem.find('.termdetail-reservation').each(function(){
          $(this).draggable({
            helper: "clone",
            handle: ".termdetail-reservation-caption",
            cursorAt: { left: 5, top: 5 },
            revert: function(){
              $(this).stop().slideToggle(300, function(){
                $(this).css('visibility', 'visible');            
              });           
              return true;
            },
            revertDuration: 300,
            scroll: false,
            scope: "freeres",
            start: function(event, ui){ 
              $(this).css('visibility', 'hidden');
              $(this).stop().slideToggle(300);
            },
            stop: function(event, ui){
            }          
          });  
        });      
        CreateDroppables();
      }
    }
  );  
}
function CreateDroppables()
{
  $('.ui-droppable').droppable("destroy");
  // Vytvorit Dropable
  $('.adm-dayterms-view-term').each(function(){
    var free = $(this).find('.ontermreservation').length == 0;
    
    if (free)
    {
      var termDefHeight = $(this).height();
      $(this).droppable({
        tolerance: "pointer",
        scope: "freeres",
        over: function(event, ui){
          $(this).css({background: SelBcgrColor});
        },
        out: function(event, ui){
          $(this).css({background: "white"});
        },
        activate: function(event, ui){
          if (!$(this).hasClass('termsel'))
          {
            $(this).css('-webkit-box-shadow', '0 0 0 0 rgb(100,255,100) inset, 0 1px 2px 0px rgb(100,100,100)');
            $(this).css('-moz-box-shadow', '0 0 0 0 rgb(100,255,100) inset, 0 1px 2px 0px rgb(100,100,100)');
            $(this).css('box-shadow', '0 0 0 0 rgb(100,255,100) inset, 0 1px 2px 0px rgb(100,100,100)');

            $(this).animate({
              boxShadow : "0 0 3px 2px, 0 1px 2px 0px rgb(100,100,100)"
            }, "fast",function(){
              $(this).css('-webkit-box-shadow', '0 0 3px 2px rgb(100,255,100) inset, 0 1px 2px 0px rgb(100,100,100)');
              $(this).css('-moz-box-shadow', '0 0 3px 2px rgb(100,255,100) inset, 0 1px 2px 0px rgb(100,100,100)');
              $(this).css('box-shadow', '0 0 3px 2px rgb(100,255,100) inset, 0 1px 2px 0px rgb(100,100,100)');
            }); 
          }
        },
        deactivate: function(event, ui){
          if (!$(this).hasClass('termsel'))
          {
            $(this).animate({
              boxShadow : "0 0 0 0, 0 1px 2px 0px rgb(100,100,100)"
            }, "fast", function(){
              $(this).css('-webkit-box-shadow', '0 1px 2px 0px rgb(100,100,100)');
              $(this).css('-moz-box-shadow', '0 1px 2px 0px rgb(100,100,100)');
              $(this).css('box-shadow', '0 1px 2px 0px rgb(100,100,100)');
             });  
          }
        },
        drop: function(event, ui){
          var term = $(this);
          var respk = $(ui.draggable).attr('pk');
          setTimeout(function() {
            $(ui.helper).remove();
            $(ui.draggable).draggable("destroy").remove();      
            SendFreeResMoveRequest(term.attr('pk'), respk);
          }, 0);   
        }
      });
    }
  });  
  FreeTermDroppable();
}
function FreeTermDroppable()
{
  if ($('.termsel').find('.free').length > 0 || $('.termsel').find('.invisible').length > 0)
  {
    $('.conndetail').droppable({      
      tolerance: "pointer",
      scope: "freeres",
      over: function(event, ui){
        $(this).css({background: SelBcgrColor});
      },
      out: function(event, ui){
        $(this).css({background: "white"});
      },
      activate: function(event, ui){
        $(this).css('-webkit-box-shadow', '0 0 0 0 rgb(100,255,100) inset');
        $(this).css('-moz-box-shadow', '0 0 0 0 rgb(100,255,100) inset');
        $(this).css('box-shadow', '0 0 0 0 rgb(100,255,100) inset');

        $(this).animate({
          boxShadow : "0 0 3px 2px"
        }, "fast"); 
      },
      deactivate: function(event, ui){
        $(this).animate({
          boxShadow : "0 0 0 0"
        }, "fast");
      },
      drop: function(event, ui){
        var respk = $(ui.draggable).attr('pk');
        setTimeout(function() {
          $(ui.draggable).draggable("destroy").remove();      
          SendFreeResMoveRequest($('.termsel').attr('pk'), respk);
        }, 0);        
      }
    });        
  }  
}
function CreateResMoveDropper()
{
  $('.ui-droppable').droppable("destroy");
  // Vytvorit Dropable
  var SelBcgrColor = "rgb(200,255,200)";
  $('.adm-dayterms-view-term').each(function(){
    var free = $(this).find('.ontermreservation').length == 0;
    
    if (free)
    {
      var termDefHeight = $(this).height();
      $(this).droppable({
        tolerance: "pointer",
        scope: "termres",
        over: function(event, ui){
          $(this).css({background: SelBcgrColor});
        },
        out: function(event, ui){
          $(this).css({background: "white"});
        },
        activate: function(event, ui){
          var redcss = '';
          if ($(this).hasClass('termsel'))
          {
            redcss = ', 0 0 0px 2px red inset';
          }
          $(this).css('-webkit-box-shadow', '0 0 0 0 rgb(100,255,100) inset, 0 1px 2px 0px rgb(100,100,100)' + redcss);
          $(this).css('-moz-box-shadow', '0 0 0 0 rgb(100,255,100) inset, 0 1px 2px 0px rgb(100,100,100)' + redcss);
          $(this).css('box-shadow', '0 0 0 0 rgb(100,255,100) inset, 0 1px 2px 0px rgb(100,100,100)' + redcss);

          $(this).animate({
            boxShadow : "0 0 3px 2px, 0 1px 2px 0px rgb(100,100,100)" + redcss
          }, "fast",function(){
            $(this).css('-webkit-box-shadow', '0 0 3px 2px rgb(100,255,100) inset, 0 1px 2px 0px rgb(100,100,100)' + redcss);
            $(this).css('-moz-box-shadow', '0 0 3px 2px rgb(100,255,100) inset, 0 1px 2px 0px rgb(100,100,100)' + redcss);
            $(this).css('box-shadow', '0 0 3px 2px rgb(100,255,100) inset, 0 1px 2px 0px rgb(100,100,100)' + redcss);
          }); 
        },
        deactivate: function(event, ui){
          var redcss = '';
          if ($(this).hasClass('termsel'))
          {
            redcss = ', 0 0 0px 2px red inset';
          }
          $(this).animate({
            boxShadow : "0 0 0 0, 0 1px 2px 0px rgb(100,100,100)" + redcss
          }, "fast", function(){
            $(this).css('-webkit-box-shadow', '0 1px 2px 0px rgb(100,100,100)' + redcss);
            $(this).css('-moz-box-shadow', '0 1px 2px 0px rgb(100,100,100)' + redcss);
            $(this).css('box-shadow', '0 1px 2px 0px rgb(100,100,100)' + redcss);
           });  
        },
        drop: function(event, ui){
          var term = $(this);
          setTimeout(function() {
            $(ui.draggable).draggable("destroy").remove();      
            SendResMoveRequest(term.attr('pk'));
          }, 0);        
        }
      });
    }
  });
  $(FreeResConn).droppable({
    tolerance: "pointer",
    scope: "termres",
    over: function(event, ui){
      $(this).find('.freeresconn-conn').css({background: SelBcgrColor});
      if (Minimized)
      {
        ToggleUpDown();
      }    
    },
    out: function(event, ui){
      $(this).find('.freeresconn-conn').css({background: "white"});
    },
    activate: function(event, ui){
      $(this).find('.freeresconn-conn').css('-webkit-box-shadow', '0 0 0 0 rgb(100,255,100) inset');
      $(this).find('.freeresconn-conn').css('-moz-box-shadow', '0 0 0 0 rgb(100,255,100) inset');
      $(this).find('.freeresconn-conn').css('box-shadow', '0 0 0 0 rgb(100,255,100) inset');
      $(this).find('.freeresconn-conn').animate({
        boxShadow : "0 0 3px 2px"
      }, "fast");  
    },
    deactivate: function(event, ui){
      $(this).find('.freeresconn-conn').animate({
        boxShadow : "0 0 0 0"
      }, "fast");  
    },
    drop: function(event, ui){
      $(this).find('.freeresconn-conn').css({background: "white"});
      setTimeout(function() {
        $(ui.draggable).draggable("destroy").remove();    
        SendResMoveRequest(0);
      }, 0);
    }
  }); 
}
function SendResMoveRequest(newtermpk)
{
  SendAjaxRequest(
    'type=contentdetail'+
    '&event=formsubmit'+
    '&formtype=movereservation'+
    '&c_submit=ok'+
    '&newterm=' + newtermpk,
    true,
    function(response){
      BuildContentDetailfromXML(response);
    }
  );  
}
function SendFreeResMoveRequest(newtermpk, freerespk)
{
  if (!freerespk || !newtermpk) return;  
  if (freerespk <= 0 || newtermpk <= 0) return;
  
  SelectTerm(newtermpk);
  
  SendAjaxRequest(
    'type=contentdetail'+
    '&event=formsubmit'+
    '&formtype=freeresattach'+
    '&c_submit=ok'+
    '&reservation='+ freerespk,
    true,
    function(response){
      BuildContentDetailfromXML(response);
    }
  );  
}
function CalcFreeResContentWidth()
{
  var 
    width1 = 0,
    width2 = 0,
    width3 = 0,
    width4 = 0,
    width5 = 0,
    width6 = 0,
    width7 = 0;
  
  FreeResConn.find('td').each(function(){
    var thiswidth = $(this).width();
    if (thiswidth <= 200)
    {
      switch($(this).index())
      {
        case 0: if(thiswidth > width1) width1 = thiswidth; break;
        case 1: if(thiswidth > width2) width2 = thiswidth; break;
        case 2: if(thiswidth > width3) width3 = thiswidth; break;
        case 3: if(thiswidth > width4) width4 = thiswidth; break;
        case 4: if(thiswidth > width5) width5 = thiswidth; break;
        case 5: if(thiswidth > width6) width6 = thiswidth; break;
        case 6: if(thiswidth > width7) width7 = thiswidth; break;
      }
    }
  }); 
  FreeResConn.find('td').each(function(){
    switch($(this).index())
    {
      case 0: $(this).width(width1); break;
      case 1: $(this).width(width2); break;
      case 2: $(this).width(width3); break;
      case 3: $(this).width(width4); break;
      case 4: $(this).width(width5); break;
      case 5: $(this).width(width6); break;
      case 6: $(this).width(width7); break;
    }
  });  
  FreeResConn.find('.header').width(FreeResConn.find('.termdetail-reservation table').width());
  FreeResConn.find('.termdetail-reservation').width(
    FreeResConn.find('.termdetail-reservation table').width() + 
    FreeResConn.find('.termdetail-reservation-caption').width() + 30);
}
