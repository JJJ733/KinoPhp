<!DOCTYPE html>
<!--
Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHPWebPage.php to edit this template
-->
<html>
<head>
    <meta charset="UTF-8">
    <title>Rejestracja</title>
    <link rel="stylesheet" href="../css/rejestracja.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Rejestracja użytkownika</h1>
    </header>
    <?php
        include_once('../klasy/Baza.php');
        include_once('../klasy/User.php');
        include_once('../klasy/RegistrationForm.php');

        // Ustawienia bazy danych
        $db = new baza('localhost', 'root', '', 'kino');

        // Obsługa formularza
        $rf = new RegistrationForm(); 

        if (filter_input(INPUT_POST, 'submit', FILTER_SANITIZE_FULL_SPECIAL_CHARS)) {
            $user = $rf->checkUser($db); 

            if ($user === null) {
                echo "<p>Niepoprawne dane rejestracji.</p>";
            } else {
                $user->saveDB($db);
                echo "<p style='color:green;'>Zarejestrowano użytkownika:</p>";
                $user->show();

                // Przekierowanie do sukces.php
                header("Location: sukces.php");
                exit();
            }
        }
    ?>
</body>
</html>

