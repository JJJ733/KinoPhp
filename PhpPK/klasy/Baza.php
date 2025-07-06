<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of baza
 *
 * @author JJ
 */

class Baza {
    private $mysqli;

    // Konstruktor inicjalizujący połączenie z bazą danych
    public function __construct($serwer, $user, $pass, $baza) {
        $this->mysqli = new mysqli($serwer, $user, $pass, $baza);

        if ($this->mysqli->connect_errno) {
            printf("Nie udało się połączyć z serwerem: %s\n", $this->mysqli->connect_error);
            exit();
        }

        $this->mysqli->set_charset("utf8");
    }

    // Destruktor zamykający połączenie z bazą danych
    function __destruct() {
        $this->mysqli->close();
    }

    /**
     * Wykonuje zapytanie SELECT z użyciem prepared statements
     * @param string $sql Zapytanie SQL z placeholderami (np. "SELECT * FROM tabela WHERE id = ?")
     * @param array $params Parametry do bindowania w zapytaniu
     * @return array Wynik jako tablica asocjacyjna
     */
    public function select($sql, $params = []) {
        $stmt = $this->mysqli->prepare($sql); // Przygotowanie zapytania
        if ($stmt === false) {
            throw new Exception("Nie udało się przygotować zapytania: " . $this->mysqli->error);
        }

        // Bindowanie parametrów, jeśli istnieją
        if (!empty($params)) {
            $types = str_repeat("s", count($params)); // Przyjęto, że wszystkie parametry są stringami
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute(); // Wykonanie zapytania
        $result = $stmt->get_result(); // Pobranie wyników

        // Pobieranie danych
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $stmt->close(); // Zamknięcie zapytania
        return $data; // Zwrócenie danych
    }

    /**
     * Wykonuje zapytanie INSERT
     * @param string $sql Zapytanie SQL
     * @return bool True, jeśli zapytanie zakończyło się sukcesem, False w przeciwnym razie
     */
    public function insert($sql) {
        if ($this->mysqli->query($sql)) {
            return true;
        } else {
            echo "Błąd podczas dodawania rekordu: " . $this->mysqli->error;
            return false;
        }
    }

    /**
     * Wykonuje zapytanie DELETE
     * @param string $sql Zapytanie SQL
     * @return bool True, jeśli zapytanie zakończyło się sukcesem, False w przeciwnym razie
     */
    public function delete($sql) {
        if ($this->mysqli->query($sql)) {
            return true;
        } else {
            echo "Błąd podczas usuwania rekordu: " . $this->mysqli->error;
            return false;
        }
    }

    /**
     * Sprawdza dane logowania użytkownika i zwraca jego ID
     * @param string $login Login użytkownika
     * @param string $passwd Hasło użytkownika
     * @param string $tabela Nazwa tabeli z użytkownikami
     * @return int ID użytkownika, jeśli dane są poprawne, -1 w przeciwnym razie
     */
    public function selectUser($login, $passwd, $tabela) {
        $id = -1;
        $sql = "SELECT * FROM $tabela WHERE userName = ?";
        $stmt = $this->mysqli->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Nie udało się przygotować zapytania: " . $this->mysqli->error);
        }

        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_object();
            $hash = $row->passwd; // Pobranie hasła zahashowanego
            if (password_verify($passwd, $hash)) {
                $id = $row->id;
            }
        }

        $stmt->close();
        return $id; // ID użytkownika lub -1 jeśli logowanie się nie powiodło
    }

    /**
     * Sprawdza, czy użytkownik jest administratorem
     * @param int $userId ID użytkownika
     * @param string $tabela Nazwa tabeli z użytkownikami
     * @return bool True, jeśli użytkownik jest administratorem, False w przeciwnym razie
     */
    
    public function isAdmin($userId, $tabela) {
        $sql = "SELECT status FROM $tabela WHERE id = ?";
        $stmt = $this->mysqli->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Nie udało się przygotować zapytania: " . $this->mysqli->error);
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            return $row['status'] == 2; // Zakładamy, że status "2" oznacza admina
        }

        return false;
    }

    public function getMysqli() {
        return $this->mysqli;
    }
}

