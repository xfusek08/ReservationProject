<?php
class ContentDetail
{
  public $Caption;
  public $Refresh;
  public $FocusTermPK;
  public $Unsett;
  
  protected $Actions;
  protected $Alerts;
  
  public function __construct($cap)
  {
    $this->Refresh = false;
    $this->Unsett = false;
    $this->Caption = $cap;
    $this->Actions = array();   
    $this->Alerts = array();
    $this->FocusTermPK = 0;
  }
  
  public function BuildResponse()
  {
    $res = '<respxml caption="' . $this->Caption . '"';
    if ($this->Refresh)
    {
      $res .= ' refresh="refresh" ';
      
      $this->Refresh = false;
    }
    if ($this->Unsett)
    {
      $res .= ' unsett="true" ';
    }      
    if ($this->FocusTermPK > 0)
    {
      $res .= ' focusterm="' . $this->FocusTermPK . '" ';
      $this->FocusTermPK = 0;
    }    
    $res .= '><actions>';
    for ($i = 0; $i < count($this->Actions); $i++)
    {
      $res .= '<action ident="' . $this->Actions[$i]->Ident . '" cap="' . $this->Actions[$i]->Caption . '" />';            
    }    
    $res .= '</actions><alerts>';
    for ($i = 0; $i < count($this->Alerts); $i++)
    {
      $res .= '<alert color="' . $this->Alerts[$i]->Color . '" text="' . $this->Alerts[$i]->Text . '" />';            
    }    
    unset($this->Alerts);
    $this->Alerts = array();
    $res .= '</alerts><inhtml>';
    if (!$this->Unsett)
    {
      $res .= $this->BuildInHTML();
    }
    $res .= '</inhtml>';
    $res .= '</respxml>';
    return $res;    
  }  
  protected function AddAction($ident, $cap)
  {
    $this->Actions[] = new DeatilAction($ident, $cap);
  }
  protected function AddAlert($color, $text)
  {
    $this->Alerts[] = new Alert($color, $text);    
  }
  protected function RemoveActionByIdent($ident, $cap)
  {
    echo "RemoveActionByIdent(ident, cap) otestovat až to bude v pořadí!!";
    for ($i = 0; $i < count($this->Actions); $i++)
    {
      if ($this->Actions[$i]->Ident == $ident)
      {
        unset($this->Actions[$i]);
        break;        
      }
    }
  }
  public function ProcessRequest()
  {
    if ($_POST['event'] == 'actionclick')
    {
      $this->ActionClicked($_POST['ident']);
    }
    else if ($_POST['event'] == 'formsubmit')
    {
      $this->FormSubmit();
    }
  }
  
  protected function BuildInHTML() {}
  protected function ActionClicked($actionIdent) {}
  protected function FormSubmit(){}
}
class DeatilAction
{
  public $Ident;
  public $Caption;
  public function __construct($ident, $cap)
  {
    $this->Ident = $ident;
    $this->Caption = $cap;    
  }
}

class Alert
{
  public $Color;
  public $Text;
  
  public function __construct($color, $text)
  {
    $this->Color = $color;    
    $this->Text = $text;
  }  
}