<?php

class TermDeatailStates
{
  const Overwiew = 0;
  const Editterm = 1;
  const Delterm = 2;
  const NewReservation = 3;
  const EditReservation = 4;
  const MoveReservationPre = 5;  
  const MoveReservationPost = 6;  
  const DeleteReservation = 7;
  const AttachFreeReservation = 8;
}

class TermDetail extends ContentDetail
{
  public $PK = 0;
  public $TimeStamp = 0;
  public $DateTimeStr = '';
  public $StateNum = 0;
  public $StateStr = ''; 
  public $Initialized = false; 
  public $Reservation = null;
  public $FormEditTime = 0;
  public $FormEditStateNum = 0;
  public $TimeEditMsg = '';
  
  public $State = TermDeatailStates::Overwiew;
  
  public function __construct($pk)
  {
    parent::__construct("Termín: ");
    $this->PK = $pk;
    $this->Init();    
  }
  
  public function Init()
  {    
    unset($this->Actions);
    $this->Actions = array();
    $this->Initialized = false;
    $SQL = 'select rstrm_dtfrom, rstrm_istate from rs_term where rstrm_pk = ?';
    $fields = null;
    
    if(!MyDatabase::RunQuery($fields, $SQL, false, $this->PK))
    {
      Logging::WriteLog(LogType::Error, "TermDetail->Init: Failed to load data.");
      return false;
    }
    if (!$fields)
    {
      Logging::WriteLog(LogType::Error, "Reservation->Init: no data found.");
      return false;      
    }
    
    $this->TimeStamp = strtotime($fields[0][0]);// rstrm_dtfrom 
    $this->DateTimeStr = date("d.m.Y H:i", $this->TimeStamp);
    $this->StateNum = intval($fields[0][1]); // rstrm_istate
    $this->FormEditTime = $this->TimeStamp;
    $this->FormEditStateNum = $this->StateNum;
    
    switch($this->StateNum)
    {
      case 0: 
          $this->StateStr = "Volno"; 
        break;
      case 1: 
          $this->StateStr = "Reservováno"; 
        break;
      case 2: 
          $this->StateStr = "Skryté"; 
        break;
    }
    $this->Caption = "Termín: " . date("H:i, d.m.Y ", $this->TimeStamp) . ' (' . GetCzechDayName(date('w', $this->TimeStamp)) . ')';
    
    $reservation = new Reservation($this->PK, true);
    if (!$reservation->Initialized)
    { 
      if ($reservation->LoadError)
      {
        $this->AddAlert('red', 'Selhalo načítání rezervace.');
        return false;
      }
      $this->AddAction('newres', 'Vytvořit rezervaci');
    }
    else
    {
      $this->Reservation = $reservation;
      if ($this->Reservation->IsNew)
      {
        $this->Reservation->IsNew = false;
        if (!$this->Reservation->SaveToDB())
        {
          return false;
        }
      }
      $this->AddAction('editres', 'Upravit rezervaci');
      $this->AddAction('moveres', 'Přesunout rezervaci');
      $this->AddAction('delres', 'Odstranit rezervaci');
    }    
    
    $this->AddAction('editterm', 'Upravit termín');
    $this->AddAction('delterm', 'Odstranit termín');
    $this->Initialized = true;
    return true;
  }
  
  protected function ActionClicked($actionIdent)
  {
    $valid = false;
    foreach ($this->Actions as $actAction)
    {
      if ($actionIdent == $actAction->Ident)
      {
        $valid = true;
        break;
      }
    }
    
    if (!$valid) { return; }
    
    if ($actionIdent == 'editterm')
    {
      $this->State = TermDeatailStates::Editterm;            
    }
    else if ($actionIdent == 'delterm')
    {
      $this->State = TermDeatailStates::Delterm;            
    }
    else if ($actionIdent == 'newres')
    {
      $this->State = TermDeatailStates::NewReservation;            
    }
    else if ($actionIdent == 'editres')
    {
      $this->State = TermDeatailStates::EditReservation;            
    }
    else if ($actionIdent == 'moveres')
    {
      $this->State = TermDeatailStates::MoveReservationPre;            
    }
    else if ($actionIdent == 'delres')
    {
      $this->State = TermDeatailStates::DeleteReservation;            
    }
  }

  public function BuildInHTML()
  {
    $reshtml = '';
    switch($this->State)
    {
      case TermDeatailStates::EditReservation:
          $reshtml = $this->BuildResForm();
        break;      
      case TermDeatailStates::NewReservation:
          if ($this->Reservation == null)
          {
            $this->Reservation = new Reservation($this->PK, true);
            $this->Reservation->IsNew = false;
          }
          $reshtml = $this->BuildResForm();
        break;      
      case TermDeatailStates::DeleteReservation:
          $reshtml = $this->BuildDelResForm();
        break;      
      case TermDeatailStates::Delterm:
          $reshtml = $this->BuildDelTermForm();
        break;      
      case TermDeatailStates::Editterm:
          $reshtml = $this->BuildEditTermForm();
        break;      
      case TermDeatailStates::MoveReservationPre:
          $reshtml = $this->BuildMoveForm();
        break; 
      case TermDeatailStates::MoveReservationPost:
          $reshtml = $this->BuildPostMoveForm();
        break; 
      case TermDeatailStates::AttachFreeReservation:
          $reshtml = $this->BuildAttachForm();
        break; 
      default:
          $reshtml = $this->BuildOewview();
        break;
    }    
    return $reshtml;
  }
  
  protected function FormSubmit() 
  {
    if (isset($_POST['c_strono']) || !isset($_POST['c_submit']))
    {
      $this->State = TermDeatailStates::Overwiew; 
      $this->Refresh = true;
      if ($this->Reservation != null)
      {
        $this->Reservation->Init(); // znovu se nacte z db 
      }
    }
    
    // delete term
    if (
      $this->State == TermDeatailStates::Delterm && 
      $_POST['formtype'] == 'delterm')
    {
      $this->DeleteTermFromDB();
      return;
    }
    
    // edit term
    if (
      $this->State == TermDeatailStates::Editterm && 
      $_POST['formtype'] == 'termedit')
    {
      if ($this->UpdateTermToDB($_POST['newtime'], isset($_POST['visible'])))
      {
        $this->State = TermDeatailStates::Overwiew;
        $this->AddAlert('green', 'Uloženo');
        $this->Refresh = true;
      }
      return;      
    }
    
    // delete reservation 
    if (
      $this->State == TermDeatailStates::DeleteReservation && 
      $_POST['formtype'] == 'delres')
    {
      if (!$this->Reservation->DeleteFromDB())
      {
        $this->AddAlert('red', 'Selhalo mazání z databáze.');
      }
      else
      {
        $this->AddAlert('green', 'Rezervace smazána.');
        $this->Refresh = true;
        $this->Init();
        $this->Reservation = null;
        if (isset($_POST['setinvisible']))
        {          
          $this->FormEditTime = $this->TimeStamp;
          $this->FormEditStateNum = 2;
          if (!$this->SaveSelfToDB())
          {
            $this->AddAlert('red', 'Nepodařilo se skrýt termín.');
          }
        }
      }        
      $this->State = TermDeatailStates::Overwiew;          
      return;
    }
    
    // edit/new reservation
    if (
      ($this->State == TermDeatailStates::EditReservation || $this->State == TermDeatailStates::NewReservation)&& 
      $_POST['formtype'] == 'ResEditForm' &&
      $this->Reservation != null)
    {
      // doslo potvrzeni upravy rezervace
      $this->Reservation->VoucherNum = $_POST['vouchernum'];
      $this->Reservation->ClFirstName = $_POST['firstname'];
      $this->Reservation->ClLastName = $_POST['lastname'];
      $this->Reservation->ClEmail = $_POST['email'];
      $this->Reservation->ClTelNum = $_POST['telnum'];
      $this->Reservation->ClAddress = $_POST['address'];
      $this->Reservation->Text = $_POST['text'];

      if ($this->Reservation->Validate())
      {
        if (!$this->Reservation->SaveToDB())
        {
          if ($this->Reservation->SaveDBMessage != '')
          {
            $this->AddAlert('red', $this->Reservation->SaveDBMessage);
            $this->Reservation->SaveDBMessage = '';
          }
          else
          {
            $this->AddAlert('red', 'Selhal zápis do databáze.');
          }
        }
        else
        {
          if ($this->Reservation->Init())
          {
            $this->AddAlert('green', 'Rezervace uložena.');
            $this->Refresh = true;
            $this->Init();
            $this->State = TermDeatailStates::Overwiew;            
          }
        }
      }
    }
    
    // move reservation
    if (
      $this->State == TermDeatailStates::MoveReservationPre && 
      $_POST['formtype'] == 'movereservation' &&
      $this->Reservation != null)
    {
      $this->Reservation->NewTermPK = intval($_POST['newterm']);
      $this->State = TermDeatailStates::MoveReservationPost;
    }
    // move reservation confirm
    if (
      $this->State == TermDeatailStates::MoveReservationPost && 
      $_POST['formtype'] == 'confirmMove' &&
      $this->Reservation != null)
    {
      if ($this->SaveReservation())
      {
        $this->AddAlert('green', 'Rezervace přesunuta.');
        $this->FocusTermPK = $this->Reservation->NewTermPK;
        $this->Refresh = true;
        if (isset($_POST['setinvisible']))
        {          
          $this->FormEditTime = $this->TimeStamp;
          $this->FormEditStateNum = 2;
          if (!$this->SaveSelfToDB())
          {
            $this->AddAlert('red', 'Nepodařilo se skrýt termín.');
          }
        }
      }       
      else
      {
        if ($this->Reservation->SaveDBMessage != '')
        {
          $this->AddAlert('red', $this->Reservation->SaveDBMessage);
        }
        $this->AddAlert('red', 'Při přesunu došlo k chybě.');
        $this->Reservation->SaveDBMessage = '';
      }
      $this->Init();
      $this->State = TermDeatailStates::Overwiew;
    }
    // free res attach
    if (
      $this->State == TermDeatailStates::Overwiew && 
      $_POST['formtype'] == 'freeresattach' &&
      $this->Reservation == null)
    {
      $this->Reservation = new Reservation(intval($_POST['reservation']));
      $this->StateNum = 1;
      $this->State = TermDeatailStates::AttachFreeReservation;      
    }
    if (
      $this->State == TermDeatailStates::AttachFreeReservation && 
      $_POST['formtype'] == 'attachconfirm' &&
      $this->Reservation != null)
    {
      if ($this->AttachReservation($this->Reservation->PK))
      {
        $this->State = TermDeatailStates::Overwiew;
        $this->Init();        
        $this->AddAlert('green', 'Rezervace úspěšně přiřazena.');
        $this->Refresh = true;
      }
      else
      {
        $this->AddAlert('red', 'Při přesunu došlo k chybě.');
      }
    }
  }
  
  private function BuildAttachForm()
  {
    $res = '<form method="post">'.
              '<input type="hidden" name="formtype" value="attachconfirm"/>'.
              '<div class="moveguide actiontopform checkbeforeclose">'.
                '<div>Přiřadit rezervaci na tento termín</div>'.
                '<div class="buttons">'.
                  '<input type="submit" name="c_submit" value="Potvrdit" />'.
                  '<input type="submit" name="c_storno" value="Vrátit" />'.
                '</div>'.
              '</div>'.
            '</form>';
    $res .= $this->BuildOewview();
    return $res;
  }
  
  private function BuildPostMoveForm()
  {
    $res = '<form method="post">'.
              '<input type="hidden" name="formtype" value="confirmMove"/>'.
              '<div class="moveguide actiontopform checkbeforeclose">'.
                '<div>Rezervace bude přesunuta.</div>'.
                '<div>Nastavit tento termín jako skrytý:<input type="checkbox" name="setinvisible"></div>'.
                '<div class="buttons">'.
                  '<input type="submit" name="c_submit" value="Potvrdit" />'.
                  '<input type="submit" name="c_storno" value="Vrátit" />'.
                '</div>'.
              '</div>'.
            '</form>';
    return $res;
  }
  
  // aktivije script pro virtualni presun rezervace a vrati pk noveho volneho terminu nebo informaci, ze ma odpojit rezervaci od terminu
  private function BuildMoveForm() 
  {
    $res = '<form method="post">'.
              '<input type="hidden" name="formtype" value="cancelmove"/>'.
              '<div class="moveguide actiontopform">'.
                '<div>Přesuňte rezervaci přetaženímn na termín nebo mezi volné rezervace.</div>'.
                '<div class="buttons">'.
                  '<input type="submit" name="c_storno" value="Zrušit" />'.
                '</div>'.
              '</div>'.
            '</form>';
    $res .= '<div class="hidden scriptinit" style="display: none;" scriptname="movereservation">'. 
              '<div dataname="pk" value="' . $this->Reservation->PK . '" />'.
              '<div dataname="voucher" value="' . $this->Reservation->VoucherNum . '" />'.
              '<div dataname="firstname" value="' . $this->Reservation->ClFirstName . '" />'.
              '<div dataname="lastname" value="' . $this->Reservation->ClLastName . '" />'.
           '</div>';
    $res .= $this->BuildOewview();
    return $res;
  }
  
  private function BuildEditTermForm()
  {
    $res = '<form method="post">'.
          '<input type="hidden" name="formtype" value="termedit"/>'.
          '<div class="actiontopform timeedit checkbeforeclose">'.
            '<div>'.
              'Nový čas:'.
              '<div class="timeinput" >'.
                '<input type="text" size="1" name="newtime" value="' . date('H:i', $this->FormEditTime) . '" maxlength="5"/>'.
                '<button class="seltimebt"><img src="../images/clock.png"/></button>'.
              '</div>'. $this->TimeEditMsg;
    if ($this->StateNum != 1)
    {
      $checked = '';
      if ($this->FormEditStateNum != 2)
      {
        $checked = 'checked="checked"';      
      }
      $res .= ' Viditelné:<input type="checkbox" ' . $checked . ' name="visible"/>';
    }
    $res .= '</div>'.
            '<div class="buttons">'.
              '<input type="submit" name="c_submit" value="Uložit" />'.
              '<input type="submit" name="c_storno" value="Zrušit" />'.
            '</div>'.
          '</div>'.
        '</form>';
    $res .= $this->BuildOewview();
    return $res;
  }
  
  private function BuildDelTermForm()
  {
    $res = '';
    $message = ''; 

    if ($this->Reservation != null)
    {
      $message = 'Opravdu si přejete odstranit tento termín včetě přiřazené rezervace?';
    }
    else
    {
      $message = 'Opravdu si přejete odstranit tento termín?';
    }
    
    $res = '<form method="post">'.
              '<input type="hidden" name="formtype" value="delterm"/>'.
              '<div class="delalert actiontopform checkbeforeclose">'.
                '<div>' . $message . '</div>'.
                '<div class="buttons">'.
                  '<input type="submit" name="c_submit" value="Odstranit" />'.
                  '<input type="submit" name="c_storno" value="Zrušit" />'.
                '</div>'.
              '</div>'.
            '</form>';
    
    $res .= $this->BuildOewview();
    return $res;
  }
  
  private function BuildDelResForm()
  {
    $html = '<form method="post">'.
              '<input type="hidden" name="formtype" value="delres"/>'.
              '<div class="delalert actiontopform">'.
                '<div>Rezervace bude odstraněna.</div>'.
                '<div>Nastavit tento termín jako skrytý:<input type="checkbox" name="setinvisible"></div>'.
                '<div class="delalert-buttons">'.
                  '<input type="submit" name="c_submit" value="Odstranit" />'.
                  '<input type="submit" name="c_storno" value="Zrušit" />'.
                '</div>'.
              '</div>'.
            '</form>';
    $html .= $this->BuildOewview();
    return $html;
  }
  
  private function BuildOewview()
  {
    $html = '<div class="termdetail">';
    if ($this->StateNum == 0)
    {
      $html .= '<div class="termdetail-freeterm">Volno</div>';
    }
    else if ($this->StateNum == 2)
    {
      $html .= '<div class="termdetail-freeterm">Volno - skryté</div>';      
    }
    else if ($this->StateNum == 1)
    {
      $html .= '<div class="termdetail-reservation">';      
      $html .= '<div class="termdetail-reservation-caption">Rezervace</div>';      
      $html .= '<table>';      
      $html .= '<tr><td>Číslo voucheru:</td><td>' . $this->Reservation->VoucherNum . '</td></tr>';      
      $html .= '<tr><td>Jméno a příjmení:</td><td>' . $this->Reservation->ClFirstName . ' ' . $this->Reservation->ClLastName . '</td></tr>';      
      $html .= '<tr><td>E-mail:</td><td>' . $this->Reservation->ClEmail . '</td></tr>';      
      $html .= '<tr><td>Telefonní číslo:</td><td>' . $this->Reservation->ClTelNum . '</td></tr>';      
      $html .= '<tr><td>Adresa:</td><td>' . $this->Reservation->ClAddress . '</td></tr>';      
      $html .= '<tr><td>Vytvořeno:</td><td>' . $this->Reservation->CreateDTstr . '</td></tr>';      
      $html .= '<tr><td>Poznámka:</td><td><textarea readonly class="readolny">' . $this->Reservation->Text . '</textarea></td></tr>';      
      $html .= '</table>';      
      $html .= '</div>';      
    }
    
    $html .= '</div>';
    return $html;    
  }
  
  private function BuildResForm()
  {
    if ($this->Reservation == null) { return ''; } 
    
    $html = '<div class="termdetail checkbeforeclose">';
    $html .= '<form method="post">';
    $html .= '<input type="hidden" name="formtype" value="ResEditForm"/>';
    $html .= '<div class="termdetail-reservation">';      
    $html .= '<div class="termdetail-reservation-caption">';
    if ($this->State == TermDeatailStates::EditReservation)
    {
      $html .= 'Úprava rezervace';
    }
    else if ($this->State == TermDeatailStates::NewReservation)
    {
      $html .= 'Nová rezervace';
    }
    $html .= '</div>';      
    $html .= '<table>';      
    $html .= '<tr><td>Číslo voucheru:</td><td><input ';
    
    if ($this->Reservation->error_VoucherNum != '')
    {
      $html .= 'class="nval" ';      
    }
    
    $html .= 'type="text" name="vouchernum" value="' . $this->Reservation->VoucherNum . '" /></td></tr>';      
    $html .= '<tr><td>Jméno a příjmení:</td><td><input ';
    
    if ($this->Reservation->error_ClFirstName != '')
    {
      $html .= 'class="nval" ';      
    }
    
    $html .='type="text" name="firstname" value="' . $this->Reservation->ClFirstName . '" /> <input ';

    if ($this->Reservation->error_ClLastName != '')
    {
      $html .= 'class="nval" ';      
    }
    
    $html .= 'type="text" name="lastname" value="' . $this->Reservation->ClLastName . '" /></td></tr>';      
    $html .= '<tr><td>E-mail:</td><td><input ';

    if ($this->Reservation->error_ClEmail != '')
    {
      $html .= 'class="nval" ';      
    }

    $html .= 'type="text" name="email" value="' . $this->Reservation->ClEmail . '" /></td></tr>';      
    $html .= '<tr><td>Telefonní číslo:</td><td><input ';

    if ($this->Reservation->error_ClTelNum != '')
    {
      $html .= 'class="nval" ';      
    }

    $html .= 'type="text" name="telnum" value="' . $this->Reservation->ClTelNum . '" /></td></tr>';      
    $html .= '<tr><td>Adresa:</td><td><input type="text" name="address" value="' . $this->Reservation->ClAddress . '" /></td></tr>';      
    if ($this->State == TermDeatailStates::EditReservation)
    {
      $html .= '<tr><td>Vytvořeno:</td><td>' . $this->Reservation->CreateDTstr . '</td></tr>';      
    }
    $html .= '<tr><td>Poznámka:</td><td><textarea name="text">' . $this->Reservation->Text . '</textarea></td></tr>';      
    $html .= 
      '</table><div class="termdetail-reservation-buttonline">'.
        '<input type="submit" value="Uložit" name="c_submit" />'.
        '<input type="submit" value="Zrušit" name="c_storno" />'.
      '</div>';      
    if (!$this->Reservation->Valid && $this->Reservation->Validated)
    {
      $html .= '<hr/><div class="termdetail-reservation-errorstack">';
      $html .= '<div>' . $this->Reservation->error_VoucherNum . '</div>';
      $html .= '<div>' . $this->Reservation->error_ClFirstName . '</div>';
      $html .= '<div>' . $this->Reservation->error_ClLastName . '</div>';
      $html .= '<div>' . $this->Reservation->error_ClEmail . '</div>';
      $html .= '<div>' . $this->Reservation->error_ClTelNum . '</div>';
      $html .= '<div>' . $this->Reservation->SaveDBMessage . '</div>';
      $this->Reservation->SaveDBMessage = '';
    }
    $html .= '</form>';      
    $html .= '</div>';      
    
    $html .= '</div>';
    return $html;    
  }
  
  private function SaveReservation()
  {
    $res = false;
    if ($this->Reservation->Validate())
    {
      if (!$this->Reservation->SaveToDB())
      {
        if ($this->Reservation->SaveDBMessage != '')
        {
          $this->AddAlert('red', $this->Reservation->SaveDBMessage);
          $this->Reservation->SaveDBMessage = '';
        }
        else
        {
          $this->AddAlert('red', 'Selhal zápis do databáze.');
        }
      }
      else
      {
        $res = true;
      }
    }       
    return $res;
  }
          
  public function DeleteTermFromDB($ExternalTransaction = false)
  {
    $Succes = false;
    try
    {
      if (!$ExternalTransaction)
      {
        MyDatabase::$PDO->beginTransaction();
      }
  
      $Succes = true;
      if ($this->Reservation != null)
      {
        if(!$this->Reservation->DeleteFromDB(true))
        {
          Logging::WriteLog(LogType::Error, 'TermDetail->DeleteTermFromDB - reservation delete fail');
          $this->AddAlert('red', 'Selhalo mazání rezervace.');
          $Succes = false;
        }
      }
      
      if ($Succes)
      {
        $SQL = 'delete from rs_term where rstrm_pk = ?';
        $fields = null;
        if (!MyDatabase::RunQuery($fields, $SQL, true, $this->PK))
        {
          Logging::WriteLog(LogType::Error, 'TermDetail->DeleteTermFromDB - term delete fail.');
          $this->AddAlert('red', 'Selhalo mazání termínu.');
          $Succes = false;          
        }
      }

      if ($Succes && !$ExternalTransaction)
      {   
        if ($this->Reservation != null)
        {
          $this->AddAlert('green', 'Rezervace smazána.');
        }
        $this->AddAlert('green', 'Termín smazán.');
        $this->Unsett = true;
        
        MyDatabase::$PDO->commit();        
      }
      else if (!$ExternalTransaction)
      {
        Logging::WriteLog(LogType::Anouncement, "RollBack");
        MyDatabase::$PDO->rollBack(); 
      }
    }
    catch (PDOException $e)
    {
      Logging::WriteLog(LogType::Error, $e->getMessage());
      if (!$ExternalTransaction)
      {
        Logging::WriteLog(LogType::Anouncement, "RollBack");
        MyDatabase::$PDO->rollBack(); 
      }
      return false;
    }
    return $Succes;
  }
  
  public function UpdateTermToDB($timestr, $isvisible)
  {
    if ($timestr == '')
    {        
      $this->TimeEditMsg = 'Čas nesmí být prázdný.';              
      return false;
    }
    if (!(bool)strtotime($timestr))
    {
      $this->TimeEditMsg = 'Čas musí být validního formátu.';              
      return false;
    }
    
    $time = strtotime($timestr);
    $date = strtotime(date('d.m.Y', $this->TimeStamp));
    $this->FormEditTime = strtotime('+' . date('H', $time) . ' hour +' . date('i', $time) . ' minutes', $date);
    
    if ($this->StateNum != 1)
    {
      if ($isvisible)
        $this->FormEditStateNum = 0;
      else
        $this->FormEditStateNum = 2;
    }
    return $this->SaveSelfToDB();
  }
  public function SaveSelfToDB()
  {
    $fields = null;
    
    if ($this->FormEditTime != $this->TimeStamp)
    {
      if (!MyDatabase::RunQuery($fields, 'select rstrm_pk from rs_term where rstrm_dtfrom = ?', false, date('d.m.Y H:i', $this->FormEditTime)))            
      {
        Logging::WriteLog(LogType::Error, 'TermDetail->SaveSelfToDB - select existing term db error.');
        $this->AddAlert('red', 'Selhalo ukládání');      
        return false;
      }      
      if ($fields)
      {
        $this->TimeEditMsg = 'Termín s tímto časem již existuje.';
        return false;
      }
    }

    $SQL = 'update rs_term set rstrm_dtfrom = ?, rstrm_istate = ? where rstrm_pk = ?';
    $fields = null;
    if (!MyDatabase::RunQuery($fields, $SQL, false, array(date('d.m.Y H:i', $this->FormEditTime), $this->FormEditStateNum, $this->PK)))
    {
      Logging::WriteLog(LogType::Error, 'TermDetail->SaveSelfToDB - time update failed.');
      $this->AddAlert('red', 'Selhalo ukládání');      
      return false;
    }    
    return $this->Init();    
  }

  private function AttachReservation($resPK)
  {
    $Succes = true;
    try
    {
      MyDatabase::$PDO->beginTransaction();
      
      $fields = null;

      if(!MyDatabase::RunQuery(
        $fields, 
        'insert into rs_restermrel(rsrtr_fterm, rsrtr_freservation) values(?, ?)', 
        true,
        array($this->PK, $resPK)))
      {
        Logging::WriteLog(LogType::Error, 'TermDeatail->AttachReservation - Isert relation failde.');
        $Succes = false;
      }  
      
      if ($Succes)
      {
        $fields = null;
        if (!MyDatabase::RunQuery(
          $fields, 
          'update rs_term set rstrm_istate = ? where rstrm_pk = ?', 
          true, array(1, $this->PK)))
        {
          Logging::WriteLog(LogType::Error, 'Reservation->UpdateNewTermState - term update failed.');
          $Succes = false;      
        }
      }

      if ($Succes)
      {        
        MyDatabase::$PDO->commit();        
      }
      else
      {
        Logging::WriteLog(LogType::Anouncement, "RollBack");
        MyDatabase::$PDO->rollBack();
        return false;
      }
    }
    catch (PDOException $e)
    {
      Logging::WriteLog(LogType::Error, $e->getMessage());
      Logging::WriteLog(LogType::Anouncement, "RollBack");
      MyDatabase::$PDO->rollBack(); 
      return false;
    }
    return true;
  } 
}
