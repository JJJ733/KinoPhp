<?php

session_start();

// Sprawdź, czy jest ustawiona wiadomość do wyświetlenia
$message = '';
if (isset($_SESSION['reservation_action_message'])) {
    $message = $_SESSION['reservation_action_message'];
    unset($_SESSION['reservation_action_message']); // Usuń wiadomość po jej wyświetleniu
} else {
    $message = 'Nieautoryzowany dostęp do strony.';
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informacja o rezerwacji</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/reservationsList.css">
    <script>
        // Automatyczne przekierowanie po 5 sekundach
        setTimeout(() => {
            window.location.href = "testLogin.php";
        }, 5000);
    </script>
</head>
<body>
<div class="message-container">
    <div class="message-box">
        <h1>Dziękujemy!</h1>
        <p><?= htmlspecialchars($message); ?></p>
        <p>Za chwilę zostaniesz przekierowany na stronę główną.</p>
    </div>
</div>
</body>
</html>

