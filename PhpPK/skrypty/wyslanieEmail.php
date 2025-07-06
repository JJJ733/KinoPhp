<?php

/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */

require_once 'Baza.php';

if ($_SESSION['role'] === 'admin') { // Sprawdzanie, czy admin
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sendNotifications'])) {
        $result = $conn->query("SELECT email FROM users");
        $emails = [];
        while ($row = $result->fetch_assoc()) {
            $emails[] = $row['email'];
        }

        $subject = "Nowe premiery filmowe";
        $message = "Zbliżają się nowe premiery! Zarezerwuj bilety już teraz.";
        $headers = "From: adko@gmail.com";

        foreach ($emails as $email) {
            mail($email, $subject, $message, $headers);
        }

        echo "Powiadomienia zostały wysłane!";
    }
    echo '<form method="POST">
            <button type="submit" name="sendNotifications">Wyślij powiadomienia</button>
          </form>';
}


