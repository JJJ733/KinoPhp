<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of UserManager
 *
 * @author JJ
 */
class UserManager {
    public function loginForm() {
        ?>
        <h3>Formularz logowania</h3>
        <form action="processLogin.php" method="post">
            <label for="login">Login:</label><br>
            <input type="text" id="login" name="login" required><br><br>
            <label for="passwd">Hasło:</label><br>
            <input type="password" id="passwd" name="passwd" required><br><br>
            <input type="submit" value="Zaloguj" name="zaloguj">
            <input type="reset" value="Anuluj" />
        </form>
        <?php
    }
    
    public function login($db) {
    $args = [
        'login' => FILTER_SANITIZE_ADD_SLASHES,
        'passwd' => FILTER_SANITIZE_ADD_SLASHES
    ];
    $dane = filter_input_array(INPUT_POST, $args);
    $login = $dane['login'];
    $passwd = $dane['passwd'];

    // Pobierz dane użytkownika (w tym status)
    $sql = "SELECT id, passwd, status FROM users WHERE userName = ?";
    $stmt = $db->getMysqli()->prepare($sql);
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($passwd, $user['passwd'])) {
            // Poprawne logowanie
            $userId = $user['id'];
            session_start();
            $_SESSION['user_id'] = $userId;
            $_SESSION['userName'] = $login;
            $_SESSION['role'] = ($user['status'] == 2) ? 'admin' : 'user'; // Ustawienie roli
            $sessionId = session_id();

            // Usuń stare wpisy sesji
            $db->delete("DELETE FROM logged_in_users WHERE userId = $userId");
            $db->delete("DELETE FROM logged_in_users WHERE sessionId = '$sessionId'");

            // Dodaj nową sesję
            $date = date("Y-m-d H:i:s");
            $sql = "INSERT INTO logged_in_users (sessionId, userId, lastUpdate) VALUES ('$sessionId', $userId, '$date')";
            $db->insert($sql);

            // Przekierowanie na podstawie roli
            if ($_SESSION['role'] === 'admin') {
                header("Location: adminPanel.php");
            } else {
                header("Location: testLogin.php");
            }
            exit();
        }
    }

    // Nieprawidłowe dane logowania
    echo "Nieprawidłowy login lub hasło.";
    return -1;
}

    public function logout($db) {
        session_start();
        $sessionId = session_id();
        $db->delete("DELETE FROM logged_in_users WHERE sessionId = '$sessionId'");
        session_unset();
        session_destroy();
    }

    public function getLoggedInUser($db, $sessionId) {
        $sql = "SELECT userId FROM logged_in_users WHERE sessionId = '$sessionId'";
        $result = $db->select($sql, ["userId"]);
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['userId'];
        }
        return -1;
    }

    public function isAdmin($db, $userId) {
        return $db->isAdmin($userId, "users");
    }
    
    //administaror funkcje 
    public function getUserRole($db, $userId) {
        $sql = "SELECT status FROM users WHERE id = ?";
        $params = [$userId];
        $result = $db->select($sql, $params);

        if ($result && count($result) > 0) {
            return $result[0]['status'] == 2 ? 'admin' : 'user'; // Status "2" oznacza admina
        }

        return 'user'; // Domyślnie zwykły użytkownik
    }


    public function getAllUsers($db) {
        $sql = "SELECT id, username, email, rola FROM users";
        return $db->select($sql, []);
    }
    function czyAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    
    public function getUserName($db, $userId) {
        $query = "SELECT userName FROM users WHERE id = ?";
        $params = [$userId];
        $result = $db->select($query, $params);

        if ($result && count($result) > 0) {
            return $result[0]['userName'];
        }
        return null;
    }   
}