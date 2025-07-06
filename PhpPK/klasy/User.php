<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of user
 *
 * @author JJ
 */
class User {
    const STATUS_USER = 1; 
    const STATUS_ADMIN = 2; 

    protected $userName; 
    protected $passwd;
    protected $fullName;
    protected $email;
    protected $date; 
    protected $status;
    
    public static function setDatabase($db) {
        self::$db = $db;
    }
    
    function __construct($userName, $fullName, $email, $passwd) { 
        $this->userName = $userName;
        $this->fullName = $fullName;
        $this->email = $email;
        $this->passwd = password_hash($passwd, PASSWORD_DEFAULT);
        $this->date = (new DateTime())->format('Y-m-d');
        $this->status = self::STATUS_USER; 
    } 
    
    public function show() {
        echo "{$this->userName} {$this->fullName}, {$this->email}, status: " . 
            ($this->status == self::STATUS_ADMIN ? '2' : '1') . 
            ", data: {$this->date}\n";
    }


    public function setUserName($userName) {
        $this->userName = $userName;    
    }
    
    public function getUserName() {
        return $this->userName;
    }

    public function setFullName($fullName) {
        $this->fullName = $fullName;
    }
    
    public function getFullName() {
        return $this->fullName;
    }

    public function setEmail($email) {
        $this->email = $email;
    }
    
    public function getEmail() {
        return $this->email;
    }

    public function setPasswd($passwd) {
        $this->passwd = password_hash($passwd, PASSWORD_DEFAULT);
    }
    
    public function getPasswd() {
        return $this->passwd;
    }

    public function getDate() {
        return $this->date;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getStatus() {
        return $this->status;
    }
    
    public function saveDB($db) {
       try {
            $sql = sprintf(
                "INSERT INTO users (username, fullname, email, passwd, date, status) VALUES ('%s', '%s', '%s', '%s', '%s', %d)",
                $db->getMysqli()->real_escape_string($this->userName),
                $db->getMysqli()->real_escape_string($this->fullName),
                $db->getMysqli()->real_escape_string($this->email),
                $db->getMysqli()->real_escape_string($this->passwd), 
                $db->getMysqli()->real_escape_string($this->date),
                $this->status
            );

            if ($db->insert($sql)) {
                echo "Użytkownik został zapisany pomyślnie.<br>";
            } else {
             echo "Błąd podczas zapisywania użytkownika do bazy danych.<br>";
            }
        } catch (Exception $e) {
            echo "Błąd podczas zapisywania użytkownika do bazy danych: " . $e->getMessage() . "<br>";
        }
    }
    
    public static function getAllUsersFromDB($db) {
    try {
        $sql = "SELECT username, fullname, email, DATE(date) as date, status FROM users";
        $result = $db->select($sql, ["username", "fullname", "email", "date", "status"]);
        
        if ($result) {
            echo $result;
        } else {
            echo "Brak użytkowników w bazie danych.<br>";
        }
    } catch (Exception $e) {
        echo "Błąd podczas pobierania użytkowników z bazy danych: " . $e->getMessage() . "<br>";
    }
}

}



