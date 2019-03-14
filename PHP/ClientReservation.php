<?php
$Reservation = null;
$AlertLines = array();
$Reservation = new Reservation(0);

if (isset($_POST['c_submit']) || isset($_POST['c_back']))
{
  $Succes = true;
  if (!isset($_POST['c_selterm']))
  {
    $AlertLines[] = 'Je nutné vybrat termín.';
    $Reservation->TermPK = 0;
    $Succes = false;
  }
  else 
  {
    $Reservation->TermPK = intval(trim($_POST['c_selterm']));
  }
  $Reservation->VoucherNum = trim($_POST['c_voucher']);
  $Reservation->ClFirstName = trim($_POST['c_firstname']);
  $Reservation->ClLastName = trim($_POST['c_lastname']);
  $Reservation->ClEmail = trim($_POST['c_email']);
  $Reservation->ClTelNum = trim($_POST['c_telnumber']);
  $Reservation->ClAddress = trim($_POST['c_address']);
  $Reservation->Text = trim($_POST['c_note']);
  $Reservation->NewTermPK = $Reservation->TermPK; // je nutné protože validate() kontroluje pouze new termpk
  
  if ($Succes) { $Succes = $Reservation->Validate(); }
  
  if ($Succes && !isset($_POST['recapconfirm']))
  {
    if (BuildRecapConfirm($Reservation))
      return; 
    else 
    {
      $AlertLines[] = 'Vyskytla se neznmá chyba.';
    }
  }
  else if ($Succes && isset($_POST['c_submit']))
  {
    $Succes = $Reservation->SaveToDB();
    if ($Succes) { $Succes = $Reservation->Init(); }
    if ($Succes)
    {
      if (SendCreatedReservationEmail($Reservation))
      {
        ?>
        <h3>Vaše rezervace byla vytvořena.</h3>        
        <?php
        return;
      }
      else
      {
        if (!$Reservation->DeleteFromDB())
        {
          Logging::WriteLog(LogType::Error, 'ClientReservation.php - Failed to delete not valid reservation.');
        }
        $AlertLines[] = 'E-mail s oznámením o rezervaci se nepodařilo doručit. (rezervace nebude vytvořena)';
        $AlertLines[] = 'Ujistěte se, že zadávaná e-mailová adresa je platná.';
      }
    }  
  }
}
?>
  <form method="post">
    <h3>Vyberte termín:</h3>
    <div class="termchoose" type="client" termpk="<?php echo($Reservation->TermPK); ?>">
      <div class="monthview">
        <div id='datepicker'></div>
      </div>
      <div class="daytermview">
        <div class="dtr-conn">
          <div class="dtr-header"></div>
          <div class="dtr-cont-header">Volné termíny</div>  
          <div class="dtr-content">                                                
          </div>
        </div>
      </div>
    </div>            
    <div class="res-form-tab">
      <table>
        <tr>
          <td style="padding-bottom: 12px;">Číslo voucheru:</td>
          <td <?php if ($Reservation->error_VoucherNum != ''){ echo('class="res-form-requrie"'); } ?> style="padding-bottom: 12px;">
            <input type="text" name="c_voucher" value="<?php echo($Reservation->VoucherNum); ?>" maxlength="100"/>&#160;*
          </td>
        </tr>
        <tr>
          <td>Jméno:</td>
          <td <?php if ($Reservation->error_ClFirstName != ''){ echo('class="res-form-requrie"'); } ?>>
            <input type="text" name="c_firstname" value="<?php echo($Reservation->ClFirstName); ?>" maxlength="100"/>&#160;*
          </td>
        </tr>
        <tr>
          <td>Příjmení:</td>
          <td <?php if ($Reservation->error_ClLastName != ''){ echo('class="res-form-requrie"'); } ?>>
            <input type="text" name="c_lastname" value="<?php echo($Reservation->ClLastName); ?>" maxlength="100"/>&#160;*
          </td>
        </tr>
        <tr>
          <td>E-mail:</td>
          <td <?php if ($Reservation->error_ClEmail != ''){ echo('class="res-form-requrie"'); } ?>>
            <input type="text" name="c_email" value="<?php echo($Reservation->ClEmail); ?>" maxlength="300"/>&#160;*
          </td>
        </tr>
        <tr>
          <td>Telefonní číslo:</td>
          <td <?php if ($Reservation->error_ClTelNum != ''){ echo('class="res-form-requrie"'); } ?>>
            <input type="text" name="c_telnumber" value="<?php echo($Reservation->ClTelNum); ?>" maxlength="100"/>&#160;*
          </td>
        </tr>
        <tr>
          <td>Adresa:</td>
          <td><input type="text" name="c_address" value="<?php echo($Reservation->ClAddress); ?>" maxlength="100"/>&#160;&#160;</td>
        </tr>
        <tr>
          <td>Poznámka:</td>
          <td><textarea class="editarea" name="c_note" maxlength="4000"><?php echo($Reservation->Text); ?></textarea></td>
        </tr>
      </table>
      <input type="submit" name="c_submit" value="Rezervovat"/>              
    </div>
  </form>
  <hr/>
<?php
if (!$Reservation->Valid && $Reservation->Validated)
{
  echo('<div class="res-form-alert">' . $Reservation->error_VoucherNum . '</div>');
  echo('<div class="res-form-alert">' . $Reservation->error_ClFirstName . '</div>');
  echo('<div class="res-form-alert">' . $Reservation->error_ClLastName . '</div>');
  echo('<div class="res-form-alert">' . $Reservation->error_ClEmail . '</div>');
  echo('<div class="res-form-alert">' . $Reservation->error_ClTelNum . '</div>');
}
if (!$Reservation->Valid && $Reservation->Validated && $Reservation->SaveDBMessage != '')
{
  echo('<div class="res-form-alert">' . $Reservation->SaveDBMessage . '</div>');
}
if (count($AlertLines) > 0)
{
  foreach ($AlertLines as $str)
  {
    echo('<div class="res-form-alert">' . $str . '</div>');
  }
}
?>
  * Povinné údaje
<?php
function BuildRecapConfirm($Reservation)
{
  $term = 0;
  $val = null;
  $SQL = 'select rstrm_dtfrom from rs_term where rstrm_pk = ?';
  if(!MyDatabase::GetOneValue($val, $SQL, $Reservation->TermPK))
  {
    Logging::WriteLog(LogType::Error, 'BuildRecapConfirm - term datetime search failed.');
    return false;
  }
  if ((bool)strtotime($val))
  {
    $term = strtotime($val);    
  }
  else
  {
    Logging::WriteLog(LogType::Error, 'BuildRecapConfirm - no term datetime found.');
    return false;
  }
  ?>
  
  <form method="post">
    <input type="hidden" name="c_selterm" value="<?php echo($Reservation->TermPK); ?>"/>
    <input type="hidden" name="c_voucher" value="<?php echo($Reservation->VoucherNum); ?>"/>
    <input type="hidden" name="c_firstname" value="<?php echo($Reservation->ClFirstName); ?>"/>
    <input type="hidden" name="c_lastname" value="<?php echo($Reservation->ClLastName); ?>"/>
    <input type="hidden" name="c_email" value="<?php echo($Reservation->ClEmail); ?>"/>
    <input type="hidden" name="c_address" value="<?php echo($Reservation->ClAddress); ?>"/>
    <input type="hidden" name="c_telnumber" value="<?php echo($Reservation->ClTelNum); ?>"/>
    <input type="hidden" name="recapconfirm" value="ok"/>
    
    <h3>Rezervace bude vytvořena:</h3>
    <table>
      <tr>
        <td>Termín:</td>
        <td>
          <?php 
          echo(date('d.m.Y, H:i', $term) . ' (' . GetCzechDayName(date('w', $term)) . ')'); 
          ?>
        </td>
      </tr>
      <tr>
        <td>Číslo voucheru:</td>
        <td><?php echo($Reservation->VoucherNum); ?></td>
      </tr>
      <tr>
        <td>Jméno:</td>
        <td><?php echo($Reservation->ClFirstName); ?></td>
      </tr>
      <tr>
        <td>Příjmení:</td>
        <td><?php echo($Reservation->ClLastName); ?></td>
      </tr>
      <tr>
        <td>E-mail:</td>
        <td><?php echo($Reservation->ClEmail); ?></td>
      </tr>
      <tr>
        <td>Telefonní číslo:</td>
        <td><?php echo($Reservation->ClTelNum); ?></td>
      </tr>
      <tr>
        <td>Adresa:</td>
        <td><?php echo($Reservation->ClAddress); ?></td>
      </tr>
      <tr>
        <td>Poznámka:</td>
        <td><textarea readonly class="editarea" name="c_note" maxlength="4000"><?php echo($Reservation->Text); ?></textarea></td>
      </tr>
    </table>
    <input type="submit" name="c_submit" value="Porvtdit"/>              
    <input type="submit" name="c_back" value="Zpět"/>              
  </form>  
  
  <?php
  return true;
}
