<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of registrationForm
 *
 * @author JJ
 */

class RegistrationForm {
    protected $user;

    function __construct() { ?>
        <h3>Formularz rejestracji</h3>
        <p>
        <form action="rejestracja.php" method="post"> 
            Nazwa użytkownika: <br/>
            <input name="userName" required /><br/> 
            Imię i nazwisko: <br/>
            <input name="fullName" required /><br/>
            Adres e-mail: <br/>
            <input name="email" type="email" required /><br/>
            Hasło: <br/>
            <input type="password" name="passwd" required />
            <small style="display:block;color:gray;">Hasło musi zawierać co najmniej 6 znaków.</small><br/>
            <input type="submit" name="submit" value="Rejestruj" />
            <input type="reset" value="Anuluj" />
        </form>
        </p> 
    <?php  
    }

    public function checkUser($db) {
        $args = [
            'userName' => [
                'filter' => FILTER_VALIDATE_REGEXP,
                'options' => ['regexp' => '/^[0-9A-Za-ząęłńśćźżó_]{2,25}$/']
            ],
            'fullName' => [
                'filter' => FILTER_VALIDATE_REGEXP,
                'options' => ['regexp' => '/^[A-Za-ząęłńśćźżó\s]{2,50}$/']
            ],
            'email' => FILTER_VALIDATE_EMAIL,
            'passwd' => [
                'filter' => FILTER_VALIDATE_REGEXP,
                'options' => ['regexp' => '/.{6,}/'] // Hasło min. 6 znaków
            ]
        ];

        $dane = filter_input_array(INPUT_POST, $args);
        $errors = "";

        // Sprawdzanie unikalności nazwy użytkownika i e-maila
        $email = $dane['email'];
        $userName = $dane['userName'];

        $query = "SELECT * FROM users WHERE email = '$email' OR username = '$userName'";
        $result = $db->getMysqli()->query($query);

        if ($result->num_rows > 0) {
            echo "<p style='color:red;'>Użytkownik o podanym adresie e-mail lub nazwie użytkownika już istnieje.</p>";
            return null;
        }

        foreach ($dane as $key => $value) {
            if ($value === false || $value === null) {
                $errors .= "Nieprawidłowe dane w polu: $key. ";
            }
        }

        if ($errors === "") {
            $this->user = new User(
                $dane['userName'],
                $dane['fullName'],
                $dane['email'],
                $dane['passwd']
            );
        } else {
            echo "<p style='color:red;'>Błędne dane: $errors</p>";
            $this->user = null;
        }

        return $this->user;
    }
}