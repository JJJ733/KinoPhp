 <!DOCTYPE html>
<!--
Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHPWebPage.php to edit this template
-->

<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/rejestracja.css">
</head>
<body>
    <header>
        <h1>Logowanie użytkownika</h1>
    </header>
    <main>
        <?php
            session_start();
            
            // Dołączenie wymaganych klas
            include_once '../klasy/Baza.php';
            include_once '../klasy/UserManager.php';

            // Inicjalizacja obiektów
            $db = new Baza("localhost", "root", "", "kino");
            $um = new UserManager();

            // Obsługa wylogowania
            if (filter_input(INPUT_GET, "akcja") === "wyloguj") {
                $um->logout($db);
                echo "<p class='success'>Wylogowano pomyślnie.</p>";
                header("Location: processLogin.php");
                exit();
            }

        
            // Obsługa logowania
            if (filter_input(INPUT_POST, "zaloguj")) {
                $status = $um->login($db);
                if ($status > 0) { // Poprawne logowanie
                    $_SESSION['user_id'] = $status;
                    $_SESSION['userName'] = $um->getUserName($db, $status);
                    $_SESSION['role'] = $um->isAdmin($db, $status) ? '2' : '1';
                    if ($um->isAdmin($db, $status)) {
                        header("Location: adminPanel.php");
                    }
                    else {
                        header("Location: testLogin.php");
                    }
                    exit();
                } else {
                    $um->loginForm();
                }
           
            } else {
                // Wyświetlenie formularza logowania
                $um->loginForm();
            }
        ?>
    </main>
</body>