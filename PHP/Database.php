<?php
class MyDatabase
{  
  public static $DBFullPath = DATABASE_FULLPATH;
  
  public static $UserName = DATABASE_USER;
  public static $Password = DATABASE_PASSWORD;
  
  public static $TransactionOpen;

  public static $PDO;
  
  public static function Connect()
  {
    $TransactionOpen = false;
    
    if (!isset($PDO))
    {
      
      $settings = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        
        //PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
          // stimhle nefunguji datetime typy pri querry
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_AUTOCOMMIT => 0
      );
      
      try 
      {
        self::$PDO = new PDO(
          "firebird:dbname=" . self::$DBFullPath . ";charset=UTF8", 
          self::$UserName, 
          self::$Password,
          $settings);
      }
      catch (PDOException $e)
      {
        Logging::WriteLog(LogType::Error, "Database connection error; " . $e->getMessage());
        echo $e->getMessage() . '</br>';
        die("Database connection failed"); // asi rovnou neukoncovat celou stranku ale t5eba se vratit ...
      }
    }
  }
  
  public static function RunQuery(&$fields, $SQL, $ExternTransaction, $params = false)
  {
    $fields = null;
    try
    {
      if (!$ExternTransaction)
      {
        self::$PDO->beginTransaction();
      }
      
      $query = self::$PDO->prepare($SQL);
      
      if (!$params)
        $query->execute();
      
      else if (!is_array($params))
        $query->execute(array($params));      
      
      else
        $query->execute($params);      
      
      $fields = $query->fetchAll();   

      if (!$ExternTransaction)
      {
        self::$PDO->commit();      
      }
    }
    catch(PDOException $e)
    {      
      Logging::WriteLog(LogType::Error, "MyDatabase->RunQuery; " . $e->getMessage());
      Logging::WriteLog(LogType::Error, "MyDatabase->RunQuery; SQL: " . $SQL);      
      if (!$ExternTransaction)
      {
        Logging::WriteLog(LogType::Anouncement, "RollBack");      
        self::$PDO->rollBack();      
      }
      return false;
    }
    return true;
  }
  
  public static function GetOneValue(&$Val, $SQL, $params = false)
  {
    $fields = null;
    
    if (!self::RunQuery($fields, $SQL, false, $params))
    {
      return false;
    }
    
    if ($fields)
    {    
      $Val = $fields[0][0];
    }
    return true;
  } 

}

MyDatabase::Connect();
