<?php
function GetTermsXML($admin = false)
{
  $DateFrom = $_POST['fromdate'];  
  $DateTo = date('d.m.Y' , strtotime($_POST['todate']));
  
  $response = '';
  
  $SQL = 
    'select '.
    '    rstrm_pk, '.
    '    rstrm_dtfrom,'.
    '    rstrm_istate'.
    '  from'.
    '    rs_term'.
    '  where'.
    '    rstrm_dtfrom >= ? and'.
    '    rstrm_dtfrom <= ?';
  
  if (!$admin)
  {
    $SQL .= ' and rstrm_istate = 0';
  }
  
  $SQL .=  ' order by rstrm_dtfrom;';
  
  $fields = null;
  
  if (!MyDatabase::RunQuery($fields, $SQL, false, array($DateFrom, $DateTo)))
  {
    echo("fail");
    return;
  }
  
  if (!$fields)
  {
    return;
  }
  
  $actDay = '';
  $newDay = true;
  $newMonth = true;
  $actDayNum = '';
  $actMonthNum = '';
  $actTermTime = 0; 
  
  $response = '<respxml>';
    $response .= '<moths>';
      for($i = 0; $i < count($fields); $i++)
      {
        $actTermTime = strtotime($fields[$i][1]);
        
        $actMonthNum = intval(date('m', $actTermTime));
        $actDay = date('d.m.Y', $actTermTime);                
        $actDayNum = date('d', $actTermTime);
        $actDay = date('d.m.Y', $actTermTime);                
        
        if ($newMonth)
        {
          $response .= '<month monthnum="' . $actMonthNum . '">';
        }
        
        if ($newDay)
        {
          $response .= '<day date="' . $actDay . '">';
        }
        
        $response .= '<term pk="' . $fields[$i][0] . '" time="' . date('H:i', $actTermTime) . '" state="' . $fields[$i][2] . '" />';
        
        if (($i + 1) < count($fields))
        {          
          $newDay = (date('d', strtotime($fields[$i + 1][1]))) != $actDayNum;
          $newMonth = intval((date('m', strtotime($fields[$i + 1][1])))) != $actMonthNum;
        }
        else 
        {
          $newDay = true;
          $newMonth = true;
        }
        
        if ($newDay)
        {
          $response .= '</day>';
        }

        if ($newMonth)
        {
          $response .= '</month>';
        }
        
      }
    $response .= '</moths>';
  $response .= '</respxml>';
  
  echo($response);
} 

function GetReservationsXML()
{
  $DateFrom = $_POST['fromdate'];
  $DateTo = $_POST['todate'];
  
  $response = '';
  
  $SQL = 
    'select '.
    '    rsres_pk, '.
    '    rsrtr_fterm,'.
    '    rstrm_dtfrom,'.
    '    rsres_vvouchernum,'.
    '    rsres_vclfirstname,'.
    '    rsres_vcllastname,'.
    '    rsres_vclemail,'.
    '    rsres_vcladdress,'.
    '    rsres_vtext,'.
    '    rsres_isnew'.
    '  from'.
    '    rs_term,'.
    '    rs_reservation,'.
    '    rs_restermrel'.
    '  where'.
    '    rstrm_pk = rsrtr_fterm and'.
    '    rsrtr_freservation = rsres_pk and'.
    '    rstrm_dtfrom >= ? and'.
    '    rstrm_dtfrom <= ?'.
    '  order by rstrm_dtfrom;';
  
  $fields = null;
  
  if (!MyDatabase::RunQuery($fields, $SQL, false, array($DateFrom, $DateTo)))
  {
    echo("fail");
    return;
  }
  $response = '<respxml>';
    $response .= '<reservations>';
      for($i = 0; $i < count($fields); $i++)
      {
        $response .= '<reservation';
        $response .= ' respk="' . $fields[$i][0] . '" ';
        $response .= ' termpk="' . $fields[$i][1] . '" ';
        $response .= ' fromdate="' . date('d.m.Y', strtotime($fields[$i][2])) . '" ';
        $response .= ' fromtime="' . date('H:i', strtotime($fields[$i][2])) . '" ';
        $response .= ' vouchernum="' . $fields[$i][3] . '" ';
        $response .= ' firstname="' . $fields[$i][4] . '" ';
        $response .= ' lastname="' . $fields[$i][5] . '" ';
        $response .= ' email="' . $fields[$i][6] . '" ';
        $response .= ' address="' . $fields[$i][7] . '" ';
        $response .= ' text="' . $fields[$i][8] . '" ';
        $response .= ' isnew="' . $fields[$i][9] . '" ';
        $response .= '/>';
      }
    $response .= '</reservations>';
  $response .= '</respxml>';
  echo($response);
}
function SeachForReservations($SQLWhere)
{
  $SQL = 
    'select '.
    '    rsres_pk, '.
    '    rsres_vvouchernum,'.
    '    rsres_vclfirstname,'.
    '    rsres_vcllastname,'.
    '    rsres_vclemail,'.
    '    rsres_vcltelnumber,'.
    '    rsres_vcladdress,'.
    '    rsres_vtext,'.
    '    rsres_dtcreated,'.
    '    rsres_isnew'.
    '  from'.
    '    rs_reservation'.
    '  where ' . $SQLWhere .
    '  order by rsres_dtcreated;';
    
  $fields = null;
  if (!MyDatabase::RunQuery($fields, $SQL, false))
  {
    return "fail";
  }

  $response = '<respxml>';
    $response .= '<freereservations>';
      for($i = 0; $i < count($fields); $i++)
      {
        $response .= '<reservation';
        $response .= ' respk="' . $fields[$i][0] . '" '; // rsres_pk
        $response .= ' vouchernum="' . $fields[$i][1] . '" '; // rsres_vvouchernum
        $response .= ' firstname="' . $fields[$i][2] . '" '; // rsres_vclfirstname
        $response .= ' lastname="' . $fields[$i][3] . '" '; // rsres_vcllastname
        $response .= ' email="' . $fields[$i][4] . '" '; // rsres_vclemail
        $response .= ' telnum="' . $fields[$i][5] . '" '; // rsres_vcltelnumber
        $response .= ' address="' . $fields[$i][6] . '" '; // rsres_vcladdress
        $response .= ' text="' . $fields[$i][7] . '" '; // rsres_vtext
        $response .= ' created="' . date('d.m.Y, H:i', strtotime($fields[$i][8])) . '" '; // rsres_dtcreated
        $response .= ' isnew="' . intval($fields[$i][9]) . '" '; // rsres_isnew
        $response .= '/>';
      }
    $response .= '</freereservations>';
  $response .= '</respxml>';
  return $response;  
}
function GetFreeRes()
{
  echo SeachForReservations('not exists (select 1 from rs_restermrel where rsrtr_freservation = rsres_pk)');
}
function GetNewRes()
{
  $SQL = 
    'select '.
    '    rsres_pk,'.
    '    rsres_vvouchernum,'.
    '    rsres_vclfirstname,'.
    '    rsres_vcllastname,'.
    '    rsres_vclemail,'.
    '    rsres_vcltelnumber,'.
    '    rsres_vcladdress,'.
    '    rsres_vtext,'.
    '    rsres_dtcreated,'.
    '    rsres_isnew,'.
    '    rstrm_pk,'.
    '    rstrm_dtfrom'.
    '  from'.
    '    rs_reservation,'.
    '    rs_term'.
    '  where' .
    '    rstrm_pk = (select rsrtr_fterm from rs_restermrel where rsrtr_freservation = rsres_pk) and'.
    '    rsres_isnew = \'1\''.
    '  order by rstrm_dtfrom;';
    
  $fields = null;
  if (!MyDatabase::RunQuery($fields, $SQL, false))
  {
    echo "fail";
    return;
  }

  $response = '<respxml>';
    $response .= '<newervations>';
      for($i = 0; $i < count($fields); $i++)
      {
        $response .= '<reservation';
        $response .= ' respk="' . $fields[$i][0] . '" '; // rsres_pk
        $response .= 'vouchernum="' . $fields[$i][1] . '" '; // rsres_vvouchernum
        $response .= 'firstname="' . $fields[$i][2] . '" '; // rsres_vclfirstname
        $response .= 'lastname="' . $fields[$i][3] . '" '; // rsres_vcllastname
        $response .= 'email="' . $fields[$i][4] . '" '; // rsres_vclemail
        $response .= 'telnum="' . $fields[$i][5] . '" '; // rsres_vcltelnumber
        $response .= 'address="' . $fields[$i][6] . '" '; // rsres_vcladdress
        $response .= 'text="' . $fields[$i][7] . '" '; // rsres_vtext
        $response .= 'created="' . date('d.m.Y, H:i', strtotime($fields[$i][8])) . '" '; // rsres_dtcreated
        $response .= 'isnew="' . intval($fields[$i][9]) . '" '; // rsres_isnew
        $response .= 'termpk="' . intval($fields[$i][10]) . '" '; // rstrm_pk
        $response .= 'dayname="' . GetCzechDayName(date('w', strtotime($fields[$i][11]))) . '" '; // rstrm_dtfrom
        $response .= 'fromdate="' . date('d.m.Y', strtotime($fields[$i][11])) . '" ';
        $response .= 'fromtime="' .  date('H:i', strtotime($fields[$i][11])) . '" ';
        $response .= '/>';
      }
    $response .= '</newreservations>';
  $response .= '</respxml>';
  echo $response;  
}
function ReservationSearch()
{
  $DateFrom = 0;
  $DateTo = 0;
  $SearchText = '';
  
  if (isset($_POST['fromdate']))
    if ($_POST['fromdate'] != '')
      $DateFrom = $_POST['fromdate'];
  
  if (isset($_POST['todate']))
    if ($_POST['todate'] != '')
      $DateTo = date('d.m.Y', strtotime("+1 day", strtotime($_POST['todate'])));
  
  
  if (isset($_POST['searchtext']))
    $SearchText = $_POST['searchtext'];
  
  $response = '';
  $searchwords = explode(" ", $SearchText);
  
  
  $params = array();
  $SQL = 
    'select * from'.
    '  (select '.
    '      rsres_pk,'.
    '      rsrtr_fterm,'.
    '      rstrm_dtfrom,'.
    '      rsres_vvouchernum,'.
    '      rsres_vclfirstname,'.
    '      rsres_vcllastname,'.
    '      rsres_vclemail,'.
    '      rsres_vcltelnumber,'.
    '      rsres_vcladdress,'.
    '      rsres_isnew,'.
    '      rsres_dtcreated,'.
    '      rsres_vvouchernum || rsres_vclfirstname || rsres_vcllastname as srtext'.
    '    from'.
    '      rs_term,'.
    '      rs_reservation,'.
    '      rs_restermrel'.
    '    where'.
    '      rstrm_pk = rsrtr_fterm and'.
    '      rsrtr_freservation = rsres_pk';
  if ($DateFrom != 0)
  {
    $SQL .=  
    '      and rstrm_dtfrom >= ?';
    $params[] = $DateFrom;
  }
  if ($DateTo != 0)
  {
    $SQL .=    
    '      and rstrm_dtfrom <= ?';
    $params[] = $DateTo;
  }  
  $SQL .=  
    '  )';  
  if (count($searchwords) > 0)
  {
    $SQL .=
    '  where';
    for($i = 0; $i < count($searchwords); $i++)
    {
      $SQL .= ' upper(srtext) like upper(?)';
      $params[] = '%'. $searchwords[$i] . '%';
      if ($i + 1 < count($searchwords))
        $SQL .= ' and ';
    }
  }
  $SQL .=
    '  order by rstrm_dtfrom desc;';
    
  $fields = null;
  if (!MyDatabase::RunQuery($fields, $SQL, false, $params))
  {
    echo("fail");
    return;
  }
  $response = '<respxml>';
    $response .= '<reservations>';
      for($i = 0; $i < count($fields); $i++)
      {
        $response .= '<reservation ';
        $response .= 'respk="' . $fields[$i][0] . '" '; // rsres_pk
        $response .= 'termpk="' . intval($fields[$i][1]) . '" '; // rsrtr_fterm
        $response .= 'dayname="' . GetCzechDayName(date('w', strtotime($fields[$i][2]))) . '" '; // rstrm_dtfrom
        $response .= 'fromdate="' . date('d.m.Y', strtotime($fields[$i][2])) . '" ';
        $response .= 'fromtime="' .  date('H:i', strtotime($fields[$i][2])) . '" ';
        $response .= 'vouchernum="' . $fields[$i][3] . '" '; // rsres_vvouchernum
        $response .= 'firstname="' . $fields[$i][4] . '" '; // rsres_vclfirstname
        $response .= 'lastname="' . $fields[$i][5] . '" '; // rsres_vcllastname
        $response .= 'email="' . $fields[$i][6] . '" '; // rsres_vclemail
        $response .= 'telnum="' . $fields[$i][7] . '" '; // rsres_vcltelnumber
        $response .= 'address="' . $fields[$i][8] . '" '; // rsres_vcladdress
        $response .= 'isnew="' . intval($fields[$i][9]) . '" '; // rsres_isnew
        $response .= 'created="' . date('d.m.Y, H:i', strtotime($fields[$i][10])) . '" '; // rsres_dtcreated
        $response .= '/>';
      }
    $response .= '</reservations>';
  $response .= '</respxml>';
  echo($response);
}
function GetNewTermsForm()
{
  $datestr = $_POST['date'];     
  $form = new NewTermsForm($datestr, $_POST['tftype']);
  echo $form->BuildInitHTML();  
  $_SESSION['newtermsform'] = serialize($form);
}
function NewTermsForm()
{
  if (isset($_SESSION['newtermsform']))
  {
    $form = unserialize($_SESSION['newtermsform']);
    echo $form->ProcessPostAsynchReq();   
    $_SESSION['newtermsform'] = serialize($form);
  }
}
function GetTermDetail()
{
  if (isset($_SESSION['ActConntentDetail']))
  {
    $term = unserialize($_SESSION['ActConntentDetail']);    
    echo $term->BuildResponse();
    unset($_SESSION['ActConntentDetail']);
    return;   
  }
  
  $term = new TermDetail($_POST['pk']);
  if (!$term->Initialized)
  {
    echo "fail";
  }
  else
  {
    echo $term->BuildResponse();
  }
  $_SESSION['ContentDetail'] = serialize($term);
}
function ProcessContentDetail()
{
  if (isset($_SESSION['ContentDetail']))
  {
    $term = unserialize($_SESSION['ContentDetail']);
    $term->ProcessRequest();
    echo $term->BuildResponse();
    $_SESSION['ContentDetail'] = serialize($term);
  }  
}
function SetNavigation($datestr)
{
  $_SESSION['actdate'] = $datestr;  
  if (isset($_SESSION['ContentDetail']))
    $_SESSION['ActConntentDetail'] = $_SESSION['ContentDetail']; 
  echo 'succes';  
}
function GetNavigation()
{
  $detailpk = '0';
  $date = '';
  
  if (isset($_SESSION['actdate']))
  {
    $date = $_SESSION['actdate'];
    unset($_SESSION['actdate']);
  }  
  
  if (isset($_SESSION['ActConntentDetail']))
  {
    $detailpk = unserialize($_SESSION['ActConntentDetail'])->PK;
  }  
  
  $res = 
    '<response>'.
      '<temrpk>' . $detailpk . '</temrpk>'.
      '<date>' . $date . '</date>'.
    '</response>';  
  echo($res);    
}
