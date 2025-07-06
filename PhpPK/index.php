<!DOCTYPE html>
<!--
Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
Click nbfs://nbhost/SystemFileSystem/Templates/Project/PHP/PHPProject.php to edit this template
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>Index</title>
    </head>
    <body>
    <?php 

        require_once("Strona.php"); 
        $strona_akt = new Strona(); 

        // Sprawdź, co wybrał użytkownik
        if (filter_input(INPUT_GET, 'strona')) { 
            $strona = filter_input(INPUT_GET, 'strona'); 
            switch ($strona) { 
                case 'repertuar': 
                    $strona = 'repertuarIndex'; // Zmieniono na repertuarIndex
                    break; 
                case 'oNas': 
                    $strona = 'oNas'; 
                    break; 
                default: 
                    $strona = 'glowna'; 
            } 
        } else { 
            $strona = "glowna"; 
        } 

        // Dołącz wybrany plik z ustawioną zmienną $tytul i $zawartosc
        $plik = "skrypty/" . $strona . ".php"; 

        if (file_exists($plik)) {
            require_once($plik);
            
            // Dodanie stylów, jeśli są wymagane
            $extra_style = $strona === 'repertuarIndex' ? "../css/repertuar.css" : null;
            $strona_akt->ustaw_style('../css/style.css', $extra_style);

            // Ustawienie tytułu i zawartości strony
            $strona_akt->ustaw_tytul($tytul);
            $strona_akt->ustaw_zawartosc($zawartosc);

            // Wyświetlenie strony
            $strona_akt->wyswietl();
        } else {
            // Obsługa błędu 404
            echo "<h1>Błąd 404</h1>";
            echo "<p>Strona, której szukasz, nie została znaleziona.</p>";
        }

        if ($strona === 'repertuarIndex') {
            $strona_akt->ustaw_tytul("Repertuar kina");
            $strona_akt->ustaw_zawartosc($zawartosc); // Zawartość repertuaru
        } else {
            $strona_akt->ustaw_tytul("Strona główna");
            $strona_akt->ustaw_zawartosc("<p>Witamy na stronie głównej!</p>");
        }
    ?>
</body>
</html>












