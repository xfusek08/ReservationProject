<?php
class ItemType
{
  const Text = 1;
  const Password = 2;
  const Time = 3;
  const ChechBox = 4;
}

class NewTermsForm
{
  private $datetime = 0;
  public $Refresh;
  
  public function __construct($date) 
  {
    $this->datetime = strtotime($date);    
  }
  
  public function BuildFormHTML($termarray)
  {
    $res = 
      '<div class="newtermsform checkbeforeclose">'.
        '<form method="post">'.
          '<div class="newtermsform-caption">Přidat nové termíny pro den: ' . date('d.m.Y', $this->datetime). '</div>'.
          '<div class="newtermsform-body">'.
            '<table>'.
              '<thead>'.
                '<tr>'.
                  '<td></td>'.
                  '<td>Čas</td>'.
                  '<td>Viditelné</td>'.
                '</tr>'.
              '</thead>'.
              '<tbody>';
      for ($i = 0; $i < count($termarray); $i++)
      {
        $Checked = '';
        if ($termarray[$i]->Visible)
        {
          $Checked = 'checked="checked"';          
        }
        
        $res .= '<tr class="datarow" cout="' . ($i + 1) . '">'.
                  '<td>' . ($i + 1) . '.</td>'.
                  '<td>'.
                    '<div class="timeinput" >'.
                      '<input type="text" size="1" name="tt' . ($i + 1) . '" value="' . $termarray[$i]->Timestr . '" maxlength="5"/>'.
                      '<button class="seltimebt"><img src="../images/clock.png"/></button>'.
                    '</div>'.
                  '</td>'.
                  '<td><input type="checkbox" ' . $Checked . '  name="tv' . ($i + 1) . '" value=""/></td>';
        $res .= '<td style="text-align: left">' . $termarray[$i]->ErrorText . '</td>';
        $res .= '</tr>';
      }
         
    $res .=   '</tbody>'.
            '</table>'.
            '<div class="newtermsform-options">'.
              '<div class="addtimebt">Přidat</div>';
    if (count($termarray) > 1)
    {
       $res .='<div class="removetimebt">Odebrat</div>';
    }
    $res .= '</div>'.
          '</div>'.
          '<div class="newtermsform-footer">'.
            '<input type="submit" value="Potrvdit" name="c_submit" />'.
            '<input type="submit" value="Zrušit" name="c_storno" />'.
          '</div>'.
        '</form>'.
      '</div>';
    return $res;
  }

  public function ProcessPostAsynchReq()
  {
    if (isset($_POST['c_storno']))
    {
      unset($_SESSION['newtermsform']);
      return "close";
    }
    
    if (!isset($_POST['c_submit']))
    {
      return "error";
    }
    
    $termarray = array();
    
    $i = 1;    
    $stop = false;  
    $allvalid = true;
    
    while(!$stop)
    {
      if (isset($_POST['tt' . $i]))
      {
        $termarray[] = new TermFormItem($this->datetime, $_POST['tt' . $i], isset($_POST['tv' . $i]));
        if (!$termarray[$i - 1]->Validate($termarray))
        {
          //chyba pri spracovani
          return "dbfail";
        }
        if (!$termarray[$i - 1]->Valid)
        {
          $allvalid = false;
        }
        $i++;
      }
      else
      {
        $stop = true;
      }
    }   
    
    // pokud je validni zapsat do db
    if ($allvalid)
    {
      if ($this->WriteToDB($termarray))
      {
        return "succes";
      }
      else
      {
        return "dbfail";
      }
    }
    else
    {
      //echo("vracim neuspech...");
      return $this->BuildFormHTML($termarray);
    }
  }
  
  public function BuildInitHTML()
  {
    $terms = array();
    $terms[] = new TermFormItem($this->datetime, '', true);
    return $this->BuildFormHTML($terms);    
  }  
  
  public function WriteToDB($termarray)
  {
    $SQL = 'insert into rs_term (rstrm_dtfrom, rstrm_istate) values(?, ?);';
    $fields = null;
    $succes = true;
    
    try
    {
      MyDatabase::$PDO->beginTransaction();
      
      foreach ($termarray as $actterm)
      {
        if (!MyDatabase::RunQuery($fields, $SQL, true,  array(date('d.m.Y H:i', $actterm->DateTime), $actterm->StateNum)))
        {
          Logging::WriteLog(LogType::Error, 'Inserting new term failed.');
          $succes = false;
          break;                
        }
      }
      if ($succes)
      {
        MyDatabase::$PDO->commit();
      } 
      else
      {
        Logging::WriteLog(LogType::Anouncement, "RollBack");
        MyDatabase::$PDO->rollBack();
        $succes = false;
      }
    }
    catch (PDOException $e)
    {
      Logging::WriteLog(LogType::Error, $e->getMessage());
      Logging::WriteLog(LogType::Anouncement, "RollBack");
      MyDatabase::$PDO->rollBack();
      $succes = false;
    }
    return $succes;
  }
}
class TermFormItem
{
  public $DateTime;
  public $Visible;
  public $StateNum;
  public $Timestr;  
  public $Valid;
  public $ErrorText;
  
  public function __construct($date, $timestr, $visible)
  {
    $this->Timestr = $timestr;

    $time = strtotime($this->Timestr);
    $this->DateTime = strtotime('+' . date('H', $time) . ' hour +' . date('i', $time) . ' minutes', $date);
    $this->Visible = $visible;
    $this->Valid = false;
    
    if (!$this->Visible)
    {
      $this->StateNum = 2;
    }
    else
    {
      $this->StateNum = 0;      
    }
  }
  public function Validate($termarray)
  { 
    $this->Valid = true;
    
    if ($this->Timestr == '')
    {
      $this->ErrorText = 'Čas musí být vyplněn.';
      $this->Valid = false;          
      return true;      
    }
    
    if (!(bool)strtotime($this->Timestr))
    {
      $this->ErrorText = 'Nesprávný formát času.';      
      $this->Valid = false;
      return true;      
    }
    
    // prepsat na for
    for ($i = 0; $i < count($termarray) - 1; $i++)
    {     
      if ($termarray[$i]->DateTime == $this->DateTime)
      {
        Logging::WriteLog(LogType::Anouncement, 'Term' . $i . $termarray[$i]->Timestr);
        $this->ErrorText = 'Tato hodnota je již zadána.';      
        $this->Valid = false;
        return true;              
      }
    }
    
    $fields = null;
    if (!MyDatabase::RunQuery($fields, 'select rstrm_pk from rs_term where rstrm_dtfrom = ?', false, date('d.m.Y H:i', $this->DateTime)))
    {
      Logging::WriteLog(LogType::Error, 'NewTermsForm->Validate - select existing term db error');
      $this->Valid = false;
      return false;      
    }

    if (count($fields) > 0)
    {
      $this->ErrorText = 'Tento termín již existuje.';      
      $this->Valid = false;
      return true;     
    }
    return true;     
  }  
}
