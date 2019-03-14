$(document).ready(function ()
{
  $('body').on("keyup", 'input[type=text].nval', function ()
  {
    $(this).addClass("fadeoutcolor");

    setTimeout(function ()
    {
      $(this).removeClass("fadeoutcolor");
    }, 400);
  });
});

function DateToStr(date)
{
  var d = date.getDate().toString();
  var m = (date.getMonth() + 1).toString();
  if (d.length == 1)
  {
    d = '0' + d;
  }
  if (m.length == 1)
  {
    m = '0' + m;
  }
  return d + '.' + m + '.' + date.getFullYear();
}
function StrToDate(str)
{
  var arr = str.split(".");
  var date = new Date(arr[2], arr[1] - 1, arr[0]);
  return date;
}
function SendAjaxRequest(data, asynch, callback)
{
  StartLoading();
  $.ajax({
    url: location.protocol + '//' + location.host + location.pathname,
    type: "POST",
    async: asynch,
    data: "ajax=true&" + data,
    success: function (html)
    {
      StopLoading();
      callback(html);
    }
  });
}
function SubmitForm(type, form, ProcFnc)
{
  SendAjaxRequest(
    "type=" + type +
    "&" + form.serialize(),
    true,
    ProcFnc
    );
}
function OnClickAjaxSubmit(event, type, button, ProcFnc)
{
  event.preventDefault();

  var
    self = button,
    form = button.closest("form"),
    tempElement = $("<input type='hidden'/>");

  tempElement
    .attr("name", button.attr('name'))
    .val(self.val())
    .appendTo(form);

  SubmitForm(type, form, ProcFnc);
}
function isOdd(num)
{
  return num % 2;
} // je liche ? 
AnnouncementCount = 0;
function RaiseAnnouncement(text, announcementcolor)
{
  ShowAnnouncement(text, announcementcolor);
}
function HideAnnouncement(announcement)
{
  announcement.animate({top: '+=' + ((announcement.outerHeight() * AnnouncementCount) + 20 + AnnouncementCount * 5) + 'px'}, 300, "swing", function ()
  {
    AnnouncementCount--;
    announcement.remove();
  });
  $('.announcement').animate({top: '+=' + (announcement.outerHeight() + AnnouncementCount * 5) + 'px'}, 300, "swing");
}
function ShowAnnouncement(text, announcementcolor)
{
  AnnouncementCount++;

  var color = '';

  switch (announcementcolor.toLowerCase())
  {
    case "red":
      color = "rgb(255,200,200)";
      break;
    case "green":
      color = "rgb(200,255,200)";
      break;
    case "white":
      color = "white";
      break;
    default:
      color = "white";
      //console.log("wrong paramter AnnouncementColor");
      break;
  }
  var announcement = $('<div class="announcement">' + text + '</div>');
  announcement.css({
    top: $(window).outerHeight() + 'px',
    background: color
  });

  announcement.appendTo('body');

  announcement.css({
    left: ($('.adm-content').offset().left + $('.adm-content').width() / 2 - announcement.width() / 2) + 'px'
  });

  announcement.animate({top: '-=' + ((announcement.outerHeight() * AnnouncementCount) + 20 + AnnouncementCount * 5) + 'px'}, (300 - AnnouncementCount * 50), "swing");
  setTimeout(function ()
  {
    HideAnnouncement(announcement);
  }, /*AnnouncementCount **/ 3000);
}
var loagingTimeout;
var loadingcounter = 0;
function StartLoading()
{
  if (loadingcounter == 0)
  {
    $('<div class="loading"><img src="../images/ajax-loader.gif " /></div>').appendTo('.adm-topheader');            
  }
  loadingcounter++;
}
function StopLoading()
{
  if (loadingcounter == 1)
  {
    //clearTimeout(loagingTimeout);
    $('.loading').remove();
  }
  loadingcounter--;
}