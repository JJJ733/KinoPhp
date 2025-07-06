<!DOCTYPE html>
<!--
Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHPWebPage.php to edit this template
-->
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tytul; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="css/repertuar.css">
</head>
<body>

    <?php

    session_start(); // Start sesji na początku pliku

    // Funkcja sprawdzająca, czy użytkownik jest zalogowany
    function isUserLoggedIn() {
        return isset($_SESSION['userId']); // Sprawdza, czy w sesji istnieje
    }

    // Wyświetlenie komunikatu o zalogowaniu/niezalogowaniu (opcjonalne)
    function displayLoginMessage() {
        if (isUserLoggedIn()) {
            echo "<p>Witaj, " . htmlspecialchars($_SESSION['userName']) . "! Możesz rezerwować bilety.</p>";
        } else {
            echo "<p>Nie jesteś zalogowany. Rezerwacja biletów jest dostępna tylko dla zalogowanych użytkowników.</p>";
        }
    }



    // Ustawienie nagłówka kodowania
    header('Content-Type: text/html; charset=utf-8');

    $tytul = "Repertuar";

    // Ustawienie lokalizacji na język polski
    setlocale(LC_TIME, 'pl_PL.UTF-8', 'polish.UTF-8', 'pl.UTF-8');

    // Obsługa wyboru dnia
    $selected_day = isset($_GET['day']) ? $_GET['day'] : date("Y-m-d"); // Domyślnie dzisiaj
    $current_date = strtotime($selected_day);

    // Ścieżka do pliku JSON z filmami
    $json_file = __DIR__ . '/../movies.json';

    // Sprawdź, czy plik JSON istnieje
    if (file_exists($json_file)) {
        $json_data = file_get_contents($json_file); // Wczytaj dane z pliku
        $movies = json_decode($json_data, true)['movies']; // Dekoduj dane JSON
    } else {
        $movies = []; // Jeśli plik nie istnieje, ustaw pustą tablicę
    }

    // Budowanie zawartości repertuaru
    $zawartosc = "<div class='schedule-container'>";
    $zawartosc .= "<h1>Repertuar na " . strftime("%A, %d %B %Y", $current_date) . "</h1>"; // Wyświetlanie daty po polsku

    // Formularz wyboru dnia
    $zawartosc .= "<div class='day-selector'>";
    $zawartosc .= "<form method='get'>";
    $zawartosc .= "<input type='hidden' name='strona' value='repertuar'>"; // Dodaj ukryty parametr strona
    $zawartosc .= "<label for='day'>Wybierz dzień:</label>";
    $zawartosc .= "<select name='day' id='day' onchange='this.form.submit()'>"; // Automatyczne przeładowanie strony

    for ($i = 0; $i < 7; $i++) {
        $date = strtotime("+$i days");
        $formatted_date = date("Y-m-d", $date);
        $day_name = strftime("%A", $date); // Nazwa dnia tygodnia po polsku
        $selected = $formatted_date === $selected_day ? "selected" : "";
        $zawartosc .= "<option value='$formatted_date' $selected>" . mb_strtolower($day_name) . " (" . date("d/m/Y", $date) . ")</option>";
    }

    $zawartosc .= "</select>";
    $zawartosc .= "</form>";
    $zawartosc .= "</div>";

    foreach ($movies as $movie) {
        $zawartosc .= "<div class='movie-card'>";
        $zawartosc .= "<img src='obrazy/{$movie['image']}' alt='" . htmlspecialchars($movie['title']) . "'>";
        $zawartosc .= "<h3>" . htmlspecialchars($movie['title']) . "</h3>";

        // Grupowanie seansów według formatu
        if (isset($movie['times']) && is_array($movie['times'])) {
            $grouped_times = [];
            foreach ($movie['times'] as $time_info) {
                $format = $time_info['format'];
                $time = $time_info['time'];
                $grouped_times[$format][] = $time;
            }

            $zawartosc .= "<div class='showtimes'>";
            foreach ($grouped_times as $format => $times) {
                $zawartosc .= "<div class='format-group'>";
                $zawartosc .= "<h4>" . htmlspecialchars($format) . ":</h4>"; // Wyświetl format raz
                foreach ($times as $time) {
                    if (isUserLoggedIn()) {
                        // Link dla zalogowanego użytkownika
                        $zawartosc .= "<a href='skrypty/rezerwacjaIndex.php?movie=" . urlencode($movie['title']) . "&time=" . urlencode($time) . "' class='showtime-button'>$time</a>";
                    } else {
                        // Link dla niezalogowanego użytkownika
                        $zawartosc .= "<a href='#' class='showtime-button' onclick='showLoginAlert(); return false;'>$time</a>";
                    }
                }
                $zawartosc .= "</div>";
            }
            $zawartosc .= "</div>";
        }

        // Przyciski Filmweb
        $zawartosc .= "<a href='" . htmlspecialchars($movie['filmweb']) . "' target='_blank' class='filmweb-button'>Więcej na Filmweb</a>";
        $zawartosc .= "</div>";
    }
    ?>
    <script>
        function showLoginAlert() {
            alert("Tylko dla zalogowanych użytkowników. Zaloguj się, aby dokonać rezerwacji.");
        }
    </script>

</body>
</html>