<?php

class Reservation
{
  private $PK = 0;
  public $TermPK = 0;
  public $NewTermPK = 0;
  public $TermTimeStamp = 0;

  public function __get($property) {
    if (property_exists($this, $property)) {
      return $this->$property;
    }
  }
  
  //form data
  public $VoucherNum = '';
  public $ClFirstName = '';
  public $ClLastName = '';
  public $ClEmail = '';
  public $ClTelNum = '';
  public $ClAddress = '';
  public $Text = '';
  
  public $CreateDT = 0;
  public $IsNew = true;
  public $CreateDTstr = 0;
  
  // form data errors
  public $error_VoucherNum = '';
  public $error_ClFirstName = '';
  public $error_ClLastName = '';
  public $error_ClEmail = '';
  public $error_ClTelNum = '';
  
  //system - interni stavy
  public $Initialized = false; 
  public $Valid = false; 
  public $Validated = false; 
  public $LoadError = false; 
  public $SaveDBMessage = '';
  
  public function __construct($PK, $LoadFromTerm = false) 
  {
    if ($LoadFromTerm)
    {
      if ($PK > 0)
      {
        $this->TermPK = $PK;    
        $this->Init();
      }
    }
    else
    {
      if ($PK > 0)
      {
        $this->PK = $PK;    
        $this->Init();
      }
    }    
  }
  
  public function Init()
  {   
    $this->CleanData();
    
    $SearchPK = 0;
    $SQL =
      'select'.
      '    rsres_pk,'.
      '    coalesce((select rsrtr_fterm from rs_restermrel where rsrtr_freservation = rsres_pk),0) as termpk,'.
      '    rsres_vvouchernum,'.
      '    rsres_vclfirstname,'.
      '    rsres_vcllastname,'.
      '    rsres_vclemail,'.
      '    rsres_vcltelnumber,'.
      '    rsres_vcladdress,'.
      '    rsres_vtext,'.
      '    rsres_isnew,'.
      '    rsres_dtcreated,'.
      '    (select rstrm_dtfrom from rs_term where rstrm_pk = (select rsrtr_fterm from rs_restermrel where rsrtr_freservation = rsres_pk))'.
      '  from'.
      '    rs_reservation'.
      '  where';
    
    if ($this->PK > 0)
    {
      $SQL .= ' rsres_pk = ?;';
      $SearchPK = $this->PK;
    }
    else if ($this->TermPK > 0)
    {
      $SQL .= ' rsres_pk = (select rsrtr_freservation from rs_restermrel where rsrtr_fterm = ?);';
      $SearchPK = $this->TermPK;
    }
    else
    {
      Logging::WriteLog(LogType::Error, "Reservation->Init: No known defined pk.");
      $this->LoadError = true;
      return false;
    }
    
    $fields = null;
    
    if(!MyDatabase::RunQuery($fields, $SQL, false, $SearchPK))
    {
      Logging::WriteLog(LogType::Error, "Reservation->Init: Failed to load data.");
      $this->LoadError = true;
      return false;
    } 
    
    if (!$fields)
    {
      return false;
    }
    
    $this->PK =           intval($fields[0][0]); // rsres_pk
    $this->TermPK =       intval($fields[0][1]); // termpk
    $this->VoucherNum =   $fields[0][2]; // rsres_vvouchernum
    $this->ClFirstName =  $fields[0][3]; // rsres_vclfirstname
    $this->ClLastName =   $fields[0][4]; // rsres_vcllastname
    $this->ClEmail =      $fields[0][5]; // rsres_vclemail
    $this->ClTelNum =     $fields[0][6]; // rsres_vcltelnumber
    $this->ClAddress =    $fields[0][7]; // rsres_vcladdress
    $this->Text =         $fields[0][8]; // rsres_vtext
    $this->IsNew =        intval($fields[0][9]) == 1; // rsres_isnew
    $this->CreateDT =     strtotime($fields[0][10]); // rsres_dtcreated
    $this->TermTimeStamp =  strtotime($fields[0][11]);
    $this->CreateDTstr =  date("d.m.Y, H:i", $this->CreateDT);
    
    $this->NewTermPK = $this->TermPK;
    $this->Valid = true;
    $this->Initialized = true;
    return true;
  }
  
  public function Validate()
  {
    $this->error_VoucherNum = '';
    $this->error_ClFirstName = '';
    $this->error_ClLastName = '';
    $this->error_ClEmail = '';
    $this->error_ClTelNum = '';
    $this->LoadError = '';
    $this->SaveDBMessage = '';
    $this->Valid = true;
    
    if ($this->VoucherNum == '')
    {
      $this->error_VoucherNum = 'Číslo voucheru je povinný údaj.';      
      $this->Valid = false;
    }
    
    if ($this->ClFirstName == '')
    {
      $this->error_ClFirstName = 'Křestní jméno je povinný údaj.';      
      $this->Valid = false;
    }
    
    if ($this->ClLastName == '')
    {
      $this->error_ClLastName = 'Příjmení je povinný údaj.';      
      $this->Valid = false;
    }
    
    if ($this->ClTelNum == '')
    {
      $this->error_ClTelNum = 'Telefonní číslo je povinný údaj.';      
      $this->Valid = false;
    }
    
    if ($this->ClEmail == '')
    {
      $this->error_ClEmail = 'E-mail je povinný údaj.';      
      $this->Valid = false;
    }
    else if (!filter_var($this->ClEmail, FILTER_VALIDATE_EMAIL))
    {
      $this->error_ClEmail = 'E-mail musí být validního formátu.';      
      $this->Valid = false;
    }
    if ($this->NewTermPK > 0 && $this->NewTermPK != $this->TermPK)
    {
      $val = null;            
      if (!MyDatabase::GetOneValue($val, 'select rstrm_istate from rs_term where rstrm_pk = ?;', $this->NewTermPK))
      {
        Logging::WriteLog(LogType::Error, 'Reservation->Validate - ' . 'Select exists term fail.');
        $this->SaveDBMessage = 'Neplatý termín.';
        $this->Valid = false;
      }
      if ($val == 1)
      {
        $this->SaveDBMessage = 'Je nám líto, ale vybraný termín není již dostupný.';
        $this->Valid = false;
      }
    }
    
    $this->Validated = true;  
    return $this->Valid;
  }  
  
  public function CleanData()
  {
    $this->VoucherNum = '';
    $this->ClFirstName = '';
    $this->ClLastName = '';
    $this->ClEmail = '';
    $this->ClTelNum = '';
    $this->ClAddress = '';
    $this->Text = '';

    $this->CreateDT = 0;
    $this->IsNew = false;
    $this->CreateDTstr = 0;
    $this->error_VoucherNum = '';
    $this->error_ClFirstName = '';
    $this->error_ClLastName = '';
    $this->error_ClEmail = '';
    $this->error_ClTelNum = '';
    
    $this->LoadError = false; 
    $this->Valid = false; 
    $this->Initialized = false;
    $this->SaveDBMessage = '';
  }
  
  public function SaveToDB()
  {
    if (!$this->Valid)
    {
      Logging::WriteLog(LogType::Error, 'Reservation->SaveToDB - can not save to db when it\'s not valid.');
      return false;
    }
    $SQL = '';
    if ($this->PK > 0) // update
    {
      $Succes = true;
      try
      {
        MyDatabase::$PDO->beginTransaction();
        $SQL =
          'update'.
          '    rs_reservation'.
          '  set'.
          '    rsres_vvouchernum = ?,'.
          '    rsres_vclfirstname = ?,'.
          '    rsres_vcllastname = ?,'.
          '    rsres_vclemail = ?,'.
          '    rsres_vcltelnumber = ?,'.
          '    rsres_vcladdress = ?,'.
          '    rsres_vtext = ?,'.
          '    rsres_isnew = ?'.
          '  where'.
          '    rsres_pk = ?';

        $params = array(
            $this->VoucherNum,
            $this->ClFirstName,
            $this->ClLastName,
            $this->ClEmail,
            $this->ClTelNum,
            $this->ClAddress,
            $this->Text,
            BoolTo01Str($this->IsNew),
            $this->PK);

        $fields = null;

        if(!MyDatabase::RunQuery($fields, $SQL, true, $params))
        {
          Logging::WriteLog(LogType::Error, 'Reservation->SaveToDB - Update querry failed.');
          $Succes = false;
        }      
        
        if ($Succes)
        {
          $Succes = $this->UpdateNewTermState();
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
    }
    else if ($this->TermPK > 0) // insert s updatem terminu
    {
      try
      {
        MyDatabase::$PDO->beginTransaction();

        $Succes = true;

        $SQL = 
          'insert into rs_reservation ('.
          '    rsres_vvouchernum, '.
          '    rsres_vclfirstname, '.
          '    rsres_vcllastname, '.
          '    rsres_vclemail, '.
          '    rsres_vcltelnumber, '.
          '    rsres_vcladdress, '.
          '    rsres_vtext,'.
          '    rsres_isnew,'.
          '    rsres_dtcreated)'.
          '  values(?, ?, ?, ?, ?, ?, ?, ?, ?) returning rsres_pk;';

        $params = array(
          $this->VoucherNum,
          $this->ClFirstName,
          $this->ClLastName,
          $this->ClEmail,
          $this->ClTelNum,
          $this->ClAddress,
          $this->Text,
          BoolTo01Str($this->IsNew),
          date('d.m.Y H:i:s', time())
        );

        $fields = null;

        $Succes = MyDatabase::RunQuery($fields, $SQL, true, $params);

        if (!$Succes)
        {
          Logging::WriteLog(LogType::Error, "Reservation->SaveToDB - insert reservation failed.");  
          $Succes = false;
        }
        else
        {
          $this->PK = intval($fields[0][0]);          
          $this->NewTermPK = $this->TermPK;
          $this->TermPK = 0;
          $Succes = $this->UpdateNewTermState();
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
    } 
    else
    {
      Logging::WriteLog(LogType::Error, 'Reservation->SaveToDB - can not save to db - no term pk.');
      return false;      
    }
    
    return true;
  }
  
  public function DeleteFromDB($ExternalTransaction = false)
  {   
    try
    {
      if (!$ExternalTransaction)
      {
        MyDatabase::$PDO->beginTransaction();
      }

      $Succes = true;

      $SQL = 'delete from rs_reservation where rsres_pk = ?';
      
      $fields = null;

      if (!MyDatabase::RunQuery($fields, $SQL, true, $this->PK))
      {
        Logging::WriteLog(LogType::Error, "Reservation->DeleteFromDB - delete reservation failed.");  
        $Succes = false;
      }
      else
      {
        // term state update
        // tady se asi do budoucna ptat jestli udelat viditelny nebo neviditelny
        $SQL = 'update rs_term set rstrm_istate = 0 where rstrm_pk = ?;';
        $fields = null;

        if (!MyDatabase::RunQuery($fields, $SQL, true, $this->TermPK))
        {
          Logging::WriteLog(LogType::Error, "Reservation->SaveToDB - term state update failed.");            
          $Succes = false;
        }
      }

      if ($Succes && !$ExternalTransaction)
      {        
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
  private function UpdateNewTermState()
  {
    if ($this->NewTermPK == $this->TermPK)
    {
      return true;
    }
    $fields = null;
    if (!MyDatabase::RunQuery($fields, 'delete from rs_restermrel where rsrtr_freservation = ?', true, $this->PK))
    {
      Logging::WriteLog(LogType::Error, 'Reservation->UpdateNewTermState - search reltab failed.');
      return false;
    }
    $fields = null;
    $SQL = 'update rs_term set rstrm_istate = ? where rstrm_pk = ?';
    if (!MyDatabase::RunQuery($fields, $SQL, true, array(0, $this->TermPK)))
    {
      Logging::WriteLog(LogType::Error, 'Reservation->UpdateNewTermState - old term update failed.');
      return false;      
    }

    if ($this->NewTermPK > 0)
    {
      $fields = null;
      if(!MyDatabase::RunQuery(
        $fields, 
        'insert into rs_restermrel(rsrtr_fterm, rsrtr_freservation) values(?, ?)', 
        true, array($this->NewTermPK, $this->PK)))
      {
        Logging::WriteLog(LogType::Error, 'Reservation->UpdateNewTermState - insert reltab failed.');
        return false;
      }
      if (!MyDatabase::RunQuery($fields, $SQL, true, array(1, $this->NewTermPK)))
      {
        Logging::WriteLog(LogType::Error, 'Reservation->UpdateNewTermState - new term update failed.');
        return false;      
      }
    }
    
    $this->TermPK = $this->NewTermPK;
    return true;
  }
}  
