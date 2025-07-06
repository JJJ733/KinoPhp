<?php

    session_start();

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo "<script>alert('Dostęp tylko dla administratora!'); window.location.href = 'processLogin.php';</script>";
        exit();
    }


    // Połączenie z bazą danych
    $conn = new mysqli("localhost", "root", "", "kino");
    if ($conn->connect_error) {
        die("Błąd połączenia: " . $conn->connect_error);
    }

    // Funkcje pomocnicze
    function fetchUsers($conn) {
        $result = $conn->query("SELECT id, email FROM users");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    function fetchMovies($conn) {
        $result = $conn->query("SELECT * FROM repertuar");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

if (isset($_POST['sendNotification'])) {
    $message = $conn->real_escape_string($_POST['notification_message']);
    $users = $conn->query("SELECT id FROM users WHERE status = 1"); // Pobierz wszystkich użytkowników
    while ($user = $users->fetch_assoc()) {
        $userId = $user['id'];
        $conn->query("INSERT INTO powiadomienia (user_id, message) VALUES ($userId, '$message')");
    }
    echo "<p>Powiadomienie zostało wysłane do użytkowników.</p>";
}

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administratora</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>Panel Administratora</h1>

    <!-- Sekcja zalogowanych użytkowników -->
    <section>
        <h2>Zalogowani użytkownicy</h2>
        <ul>
            <?php foreach (fetchUsers($conn) as $user): ?>
                <li>ID: <?= $user['id'] ?>, Email: <?= htmlspecialchars($user['email']) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
    
 <section>
        <h2>Wyślij powiadomienie do użytkowników</h2>
        <form method="POST">
            <textarea name="notification_message" rows="5" placeholder="Treść powiadomienia"></textarea><br>
            <button type="submit" name="sendNotification">Wyślij powiadomienie</button>
        </form>
    </section>
</body>
</html>
