<?php
require_once '../PHP/Settings.php';
require_once '../PHP/Logs.php';
require_once '../PHP/Database.php';
require_once '../PHP/Reservation.php';
require_once '../PHP/MyMails.php';
require_once '../PHP/AjaxXMLFunctions.php';

if (isset($_POST['ajax']))
{
  if ($_POST['type'] === 'getterms')  
  {    
    GetTermsXML();    
  }
  die;
}
?>
<!doctype html>
<html>
  <head>
  <title title="Zážitky Horácko - Rezervace">ZH - Rezervace</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Language" content='cs' />              
    <meta name="description" content="Zážitky - Horácko: Zážitkové dárky nejen z Vysočiny" />
    <meta name="keywords" content="zážitky, Horácko, Vysočina, Žďár, Žďár nad Sázavou, Kolátorová" />
    <meta name="generator" content="zazitky-horacko" />
    
    <!--
    <link rel="stylesheet" type="text/css" href="../css/style.css"/>
    <link rel="stylesheet" type="text/css" href="../css/CalendarStyle_Client.css"/>
    -->
    
    <link rel="stylesheet" type="text/css" href="../css/style.min.css"/>
    <link rel="stylesheet" type="text/css" href="../css/CalendarStyle_Client.min.css"/>
    
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.12.4.min.js"></script>
    
    <!--
    <script type="text/javascript" charset="UTF-8" src="../jscripts/jQuerry-ui_1.11.4.js"></script>
    <script type="text/javascript" charset="UTF-8" src="../jscripts/ClientResCalendar.js"></script>
    <script type="text/javascript" charset="UTF-8" src="../jscripts/utils.js"></script>
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.12.4.min.js"></script>
    -->
    
    <script type="text/javascript" charset="UTF-8" src="../jscripts/jQuerry-ui_1.11.4.min.js"></script>
    <script type="text/javascript" charset="UTF-8" src="../jscripts/ClientResCalendar.min.js"></script>
    <script type="text/javascript" charset="UTF-8" src="../jscripts/utils.min.js"></script>
  </head>
  <body>    
    <div id="wrap">

      <!--@imp+(logotop)-->
      <div id="header">
        <a href="index.php" alt="hlavní nabídka" title="hlavní nabídka">
          <img src="../images/zh.png" alt="hlavička" title="hlavička" border="0" height="176" width="965" />
        </a>
      </div> <!-- konec header -->
      <div style="clear: both;"> </div>
      <div id="top"> </div>
      <!--@imp-(logotop)-->

      <!--@imp+(menuleft)-->
      <div id="contentt">
        <div class="left">
          <ul>
            <li><a href="../index.html">nabídka</a></li>
            <li><a href="../menu/about.html">o nás</a></li>
            <li><a href="../menu/instructions.html">jak nakupovat</a></li>
            <li><a href="../menu/certificates.html">dárkové certifikáty</a></li>
            <li><a href="../menu/conditions.html">podmínky nákupu</a></li>
            <li><a href="../menu/contacts.html">kontakt</a></li>
            <li><a href="../menu/reservation.php" id="selected">Rezervace termínů</a></li>
            <li><a href="../menu/photogallery.html">fotogalerie</a></li>
            <li><a href="../menu/references.html">komentáře</a></li>
          </ul>  

          <div class="ikonky">
            <a href="index.php"><img src="../images/domu.png" alt="domů" title="domů" height="40" width="50" /></a>
            <a href="mailto:info@zazitky-horacko.cz"><img src="../images/mail.png" alt="napiště nám" title="napište nám" height="35" width="50" /></a>
          </div>
          
          <div id="toplist">
            <a href="http://www.toplist.cz/stat/1231829" target="_top"><img 
                src="http://toplist.cz/count.asp?id=1231829&logo=btn" border="0" alt="TOPlist" width="80" height="15"/></a>
            <!--<a href="http://www.toplist.cz/" target="_top"><img
            src="http://toplist.cz/count.asp?id=1231829&logo=mc" border="0" alt="TOPlist" width="88" height="60"/></a> 
            -->
          </div>

        </div> <!-- konec menu left -->
        <!--@imp-(menuleft)-->


        <!-- main -->
        <div class="main">
          <h2>Rezervace :</h2>
          <?php
          require '../PHP/ClientReservation.php';
          ?>
        </div> <!-- konec main -->

        <!--@imp+(menuright)-->
        <!--@imp-(menuright)-->

        <!--@imp+(pageend)-->
        <div style="clear: both;"> </div>

      </div>
      <div id="bottom"> </div>
      <div id="footer">
        <!--
        Designed by <a href="http://www.free-css-templates.com/">Free CSS Templates</a>
        -->
      </div> <!-- konec div.contentt -->

    </div>  <!-- wrap -->
    <!--@imp-(pageend)-->

  </body>  
</html>
