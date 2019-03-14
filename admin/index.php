<?php
session_start();
require_once '../PHP/Settings.php';
require_once '../PHP/Logs.php';
require_once '../PHP/Database.php';
require_once '../PHP/ContentDetail.php';
require_once '../PHP/NewTermsForm.php';
require_once '../PHP/Reservation.php';
require_once '../PHP/TermDetail.php';
require_once '../PHP/AjaxXMLFunctions.php';

$IsSigned = isset($_SESSION['logged']);

if (isset($_POST['ajax']) && $IsSigned)
{
  if ($_POST['type'] === 'getterms')
  {
    GetTermsXML(true);
  }
  else if ($_POST['type'] === 'getreservations')
  {
    GetReservationsXML();
  }
  else if ($_POST['type'] === 'getnewtermsform')
  {
    GetNewTermsForm();
  }
  else if ($_POST['type'] === 'newtermsform')
  {
    NewTermsForm();
  }
  else if ($_POST['type'] === 'gettermdetail')
  {
    GetTermDetail();
  }
  else if ($_POST['type'] === 'contentdetail')
  {
    ProcessContentDetail();
  }
  else if ($_POST['type'] === 'getfreeres')
  {
    GetFreeRes();
  }
  else if ($_POST['type'] === 'getfreeres')
  {
    GetFreeRes();
  }
  else if ($_POST['type'] === 'setnavigation')
  {
    SetNavigation($_POST['date']);
  }
  else if ($_POST['type'] === 'getnavigation')
  {
    GetNavigation();
  }
  else if ($_POST['type'] === 'getnewres')
  {     
    GetNewRes();
  }
  else if ($_POST['type'] === 'reservationsearch')
  {     
    ReservationSearch();
  }
  die;
}
?>
<!doctype html>
<html>
  <head>
    <title>Správce rezervací</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Language" content='cs' />                  

    <!--
    <link rel="stylesheet" href="resources/styles/adminstyle.css" type="text/css" media="screen" />  
    <link rel="stylesheet" href="resources/styles/CalendarStyle_admin.css" type="text/css" media="screen" />  
    <link rel="stylesheet" href="resources/styles/TimepickerStyle.css" type="text/css" media="screen" />  
    -->
    <link rel="stylesheet" href="resources/styles/ReservationAdminStyles.min.css" type="text/css" media="screen" />  
    
    
    <!-- 
    <script type="text/javascript" src="http://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
    -->

    <script type="text/javascript" src="http://code.jquery.com/jquery-1.12.4.min.js"></script>

    <script type="text/javascript" src="../jscripts/jQuerry-ui_1.11.4.min.js"></script>
    <script type="text/javascript" src="../jscripts/jquery.timepicker.min.js"></script>
    <script type="text/javascript" charset="UTF-8" src="../jscripts/jQuery-animate-shadow.min.js"></script>
    <script type="text/javascript" charset="UTF-8" src="../jscripts/utils.min.js"></script>
  </head>
  <body>  
    <?php
    if ($IsSigned)
    {
      require 'main.php';
    }
    else
    {
      require 'login.php';
    }
    ?>        

  </body>    
</html>