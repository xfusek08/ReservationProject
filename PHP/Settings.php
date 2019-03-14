<?php
//time zone
date_default_timezone_set('Europe/Prague');

// database
//define("DATABASE_FULLPATH", "d:/Work/Reservation_project/DB/ALASKORES.FDB");
define("DATABASE_FULLPATH", "d:/Work/Reservation_project/DB/RS.FDB");
// test-------------
//define("DATABASE_FULLPATH", "d:\Work\CM_projekt\Working\Aplikace\Exe\DB\LEKOLCM.FDB");
// test-------------
define("DATABASE_USER", "sysdba");
define("DATABASE_PASSWORD", "masterkey");

//Logs
define("LOG_FOLDER", "d:\Work\Reservation_project\Project\logs");

// E-mails
/*
  Nastavení šablony emailu:
    <pk>          - primarni klic
    <voucher>     - číslo voucheru
    <firstname>   - křestní jméno
    <lastname>    - příjmení
    <email>       - e-mail
    <telnumber>   - telefonní číslo
    <address>     - adressa
    <text>        - poznámka
    <crdayname>   - český název dnu vytvoření
    <crdate>      - datum vytvoření
    <crtime>      - čas vytvoření (s přesností na minuty)
    <dayname>     - český název dnu rezervovaného termínu
    <date>        - datum rezervovaného termínu
    <time>        - čas reservovaného termínu (s přesností na minuty)
*/

// E-mail ze kterého bude odeslána zpráva pro klienta
define('FROM_EMAIL', 'Alasko-Rezervace@alasko.cz');

// na který se pošle oznámení o vytvořené rezervaci, může být stejný jako FROM_EMAIL
define('ADMIN_ANNOUNCEMENT_EMAIL', 'petr.fusek97@gmail.com');

// Předmět e-mailu pro klienta
define('TO_CLIENT_EMAIL_DEF_SUBJECT', 'Vytvoření rezervace');

// HTML Šablona e-mailu pro klienta
define('TO_CLIENT_EMAIL_DEF_MESSAGE', 
    '<html>'.
      '<body>'.
       '<p>Vaše rezervace byla vytvořena:</p>'.
       '<table>'.
        '<tr><td>Termín:</td><td><date>, <time> (<dayname>)</td></tr>'.
        '<tr><td>Číslo voucheru:</td><td><voucher></td></tr>'.
        '<tr><td>Jméno a příjmení:</td><td><firstname> <lastname></td></tr>'.
        '<tr><td>Telefon:</td><td><telnumber></td></tr>'.
        '<tr><td>Adresa:</td><td><address></td></tr>'.
       '</table>'.
      '</body>'.
    '</html>');

// Předmět e-mailu pro správce
define('TO_ADMIN_EMAIL_DEF_SUBJECT', 'Nová rezervace.');

// HTML Šablona e-mailu pro správce
define('TO_ADMIN_EMAIL_DEF_MESSAGE', 
    '<html>'.
      '<body>'.
       '<p>Byla vytvořena rezervace:</p>'.
       '<table>'.
        '<tr><td>Termín:</td><td><date>, <time> (<dayname>)</td></tr>'.
        '<tr><td>Číslo voucheru:</td><td><voucher></td></tr>'.
        '<tr><td>Jméno a příjmení:</td><td><firstname> <lastname></td></tr>'.
        '<tr><td>E-mail:</td><td><email></td></tr>'.
        '<tr><td>Telefon:</td><td><telnumber></td></tr>'.
        '<tr><td>Adresa:</td><td><address></td></tr>'.
        '<tr><td>Vytvořeno:</td><td><crdate>, <crtime></td></tr>'.
        '<tr><td>Poznámka:</td></tr>'.
       '</table>'.
       '<div><text></div>'.
      '</body>'.
    '</html>');

class PublicResSettings
{
  public static $ShowOnlyFreeTerms = true;
}

function GetCzechDayName($day) {
    static $names = array('neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota');
    return $names[$day];
}

function BoolTo01Str($var)
{
  if ($var === true)
  {
    return '1';
  }
  else if ($var === false)
  {
    return '0';
  }
  
  return null;
}
