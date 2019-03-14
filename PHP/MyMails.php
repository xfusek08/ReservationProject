<?php
// emaily nejjednodussim spusobem

class MailState
{  
  const NotReady = 0;
  const Success = 1;
  const NotValidData = 2;
  const SendFailed = 3;
}

class MyMail
{
  public $To;
  public $From;
  public $Headers;
  public $Subject;
  public $Message;
  public $State = MailState::NotReady;
  
  public function Send()
  {
    if (!$this->Validate()) return false;
    $headers = 'From: ' . $this->From . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8';
    if (!@mail($this->To, $this->Subject, $this->Message, $headers))
    {
      $this->State = MailState::SendFailed;

      Logging::WriteLog(LogType::Error, 'MyMail->Send - Mail send failed.');
      Logging::WriteLog(LogType::Anouncement, 'MyMail->Send - mail data:' .
          ' $this->To: ' . $this->To .
          ' $this->From: ' . $this->From .
          ' $this->Message: ' . $this->Message .
          ' $this->Subject: ' . $this->Subject);
      return false; 
    }
    $this->State = MailState::Success;
    return true;    
  }
  public function Validate()
  {
    if (
      !filter_var($this->To, FILTER_VALIDATE_EMAIL) || 
      !filter_var($this->From, FILTER_VALIDATE_EMAIL) ||
      $this->Message == '' ||
      $this->Subject == '')
    {
      $this->State = MailState::NotValidData;
      Logging::WriteLog(LogType::Error, 'MyMail->Validate - invalid mail data');
      Logging::WriteLog(LogType::Anouncement, 'MyMail->Validate - mail data:' .
          ' $this->To: ' . $this->To .
          ' $this->From: ' . $this->From .
          ' $this->Message: ' . $this->Message .
          ' $this->Subject: ' . $this->Subject);
      return false;
    }
    return true;
  }
}
function SendCreatedReservationEmail($reservation)
{
  // to client
  $Mail = new MyMail();
  $Mail->From = FROM_EMAIL;
  $Mail->To = $reservation->ClEmail;
  $Mail->Subject = TO_CLIENT_EMAIL_DEF_SUBJECT;
  $Mail->Message = BuildResEMailBody($reservation, TO_CLIENT_EMAIL_DEF_MESSAGE);
  if (!$Mail->Send())
  {
    return false;
  }
  
  //to admin
  $Mail = new MyMail();
  $Mail->From = FROM_EMAIL;
  $Mail->To = ADMIN_ANNOUNCEMENT_EMAIL;
  $Mail->Subject = TO_ADMIN_EMAIL_DEF_SUBJECT;
  $Mail->Message = BuildResEMailBody($reservation, TO_ADMIN_EMAIL_DEF_MESSAGE);
  if (!$Mail->Send())
  {
    return false;
  }
  return true;
}
function BuildResEMailBody($reservation, $template)
{
  $template = str_replace('<pk>', $reservation->PK, $template);
  $template = str_replace('<voucher>', $reservation->VoucherNum, $template);
  $template = str_replace('<firstname>', $reservation->ClFirstName, $template);
  $template = str_replace('<lastname>', $reservation->ClLastName, $template);
  $template = str_replace('<email>', $reservation->ClEmail, $template);
  $template = str_replace('<telnumber>', $reservation->ClTelNum, $template);
  $template = str_replace('<address>', $reservation->ClAddress, $template);
  $template = str_replace('<text>', $reservation->Text, $template);
  $template = str_replace('<crdayname>', GetCzechDayName(date('w', $reservation->CreateDT)), $template);
  $template = str_replace('<crdate>', date('d.m.Y', $reservation->CreateDT), $template);
  $template = str_replace('<crtime>', date('H:i', $reservation->CreateDT), $template);
  $template = str_replace('<dayname>', GetCzechDayName(date('w', $reservation->TermTimeStamp)), $template);
  $template = str_replace('<date>', date('d.m.Y', $reservation->TermTimeStamp), $template);
  $template = str_replace('<time>', date('H:i', $reservation->TermTimeStamp), $template);
  return $template;
}