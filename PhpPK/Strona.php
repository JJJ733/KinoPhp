<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of Strona
 *
 * @author JJ
 */

class Strona {
    // Pola (własności) klasy:
    protected $zawartosc;
    protected $tytul = "Modułowy serwis PHP";
    protected $slowa_kluczowe = "narzędzia internetowe, php, repertuar";
    protected $przyciski = array(
        "Kontakt" => "?strona=index",
        "Repertuar" => "?strona=repertuar",
        "O nas" => "?strona=oNas"
    );

    // Metody modyfikujące fragmenty strony
    public function ustaw_zawartosc($nowa_zawartosc) {
        $this->zawartosc = $nowa_zawartosc;
    }

    public function ustaw_tytul($nowy_tytul) {
        $this->tytul = $nowy_tytul;
    }

    public function ustaw_slowa_kluczowe($nowe_slowa) {
        $this->slowa_kluczowe = $nowe_slowa;
    }

    public function ustaw_przyciski($nowe_przyciski) {
        $this->przyciski = $nowe_przyciski;
    }

    public function ustaw_style(...$urls) {
        foreach ($urls as $url) {
            if ($url) { // Upewnij się, że URL istnieje
                echo '<link rel="stylesheet" href="' . $url . '" type="text/css">';
            }
        }
    }


    // Funkcje wyświetlające stronę
    public function wyswietl() {
        $this->wyswietl_naglowek();
        $this->wyswietl_zawartosc();
        $this->wyswietl_stopke();
    }

    public function wyswietl_tytul() {
        echo "<title>$this->tytul</title>";
    }

    public function wyswietl_slowa_kluczowe() {
        echo "<meta name=\"keywords\" content=\"$this->slowa_kluczowe\">";
    }

    public function wyswietl_menu() {
        echo "<div id='nav'>";
        foreach ($this->przyciski as $nazwa => $url) {
            echo ' <a href="' . $url . '">' . $nazwa . '</a>';
        }
        echo "</div>";
    }
    
    public function wyswietl_naglowek() {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <?php
            $this->ustaw_style('css/style.css');
            echo "<title>" . $this->tytul . "</title>";
            ?>
        </head>
        <body>
            <header>
                <div id="login-bar">
                    <a href="skrypty/processLogin.php" class="login-btn">Zaloguj się</a>
                    <a href="skrypty/rejestracja.php" class="register-btn">Zarejestruj się</a>
                </div>
            </header>
            <?php
    }

    public function wyswietl_zawartosc() {
        echo '<div id="content">';
        echo '<div id="image-banner">';
        echo '<img src="obrazy/kinoNapis.jpg" alt="Cinema" />';
        echo '</div>';
        echo '<div id="nav">';
        foreach ($this->przyciski as $nazwa => $url) {
            echo '<a href="' . $url . '">' . $nazwa . '</a>';
        }
        echo '</div>';
        echo '</div>';

        echo '<div id="main">';
        if (!empty($this->zawartosc)) {
            echo $this->zawartosc;
        } else {
            echo "<p>Brak treści do wyświetlenia.</p>";
        }
        echo '</div>';
    }


    public function wyswietl_stopke() {
        echo '<div id="stopka"> &copy; 2025 kino pełne magii </div>';
        echo '</body></html>';
    }

    public function wyswietl_repertuar() {
        
    }
}
