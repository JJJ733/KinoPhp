<?php
session_start();

// Dołącz wymagane klasy
include_once '../klasy/Baza.php';
include_once '../klasy/UserManager.php';

$db = new Baza("localhost", "root", "", "kino");
$um = new UserManager();

// Ustawienie lokalizacji na język polski
setlocale(LC_TIME, 'pl_PL.UTF-8', 'polish.UTF-8', 'pl.UTF-8');

// Sprawdź, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Tylko dla zalogowanych użytkowników.'); window.location.href = '../skrypty/processLogin.php';</script>";
    exit();
}

$userId = $_SESSION['user_id'];
$userName = htmlspecialchars($_SESSION['userName']);

// Połączenie z bazą danych
$conn = new mysqli("localhost", "root", "", "kino");
if ($conn->connect_error) {
    die("Błąd połączenia z bazą danych: " . $conn->connect_error);
}

// Pobierz nieprzeczytane powiadomienia
$unreadCountQuery = $conn->query("SELECT COUNT(*) AS unread FROM powiadomienia WHERE user_id = $userId AND is_read = 0");
$unreadCount = $unreadCountQuery->fetch_assoc()['unread'];

// Pobierz wszystkie powiadomienia
$notificationsQuery = $conn->query("SELECT * FROM powiadomienia WHERE user_id = $userId ORDER BY created_at DESC");
$notifications = $notificationsQuery->fetch_all(MYSQLI_ASSOC);

// Oznacz powiadomienia jako przeczytane
if (isset($_POST['mark_as_read'])) {
    $conn->query("UPDATE powiadomienia SET is_read = 1 WHERE user_id = $userId");
    header("Refresh:0");
    exit();
}

// Obsługa wyboru dnia
$selected_day = isset($_GET['day']) ? $_GET['day'] : date("Y-m-d"); // Domyślnie dzisiaj
$current_date = strtotime($selected_day);

// Wczytaj dane filmów z JSON
$json_file = __DIR__ . '/../movies.json';
$movies = [];
if (file_exists($json_file)) {
    $json_data = file_get_contents($json_file);
    $movies = json_decode($json_data, true)['movies'];
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kino Magia - Panel Użytkownika</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/repertuar.css">
    <link rel="stylesheet" href="../css/testLogin.css">
    
</head>
<body>
<header>
    <div class="top-bar">
        <div class="welcome-message">
            <span>Witaj, <?= $userName; ?>! Możesz teraz przeglądać repertuar, dokonać rezerwacji oraz sprawdzić powiadomienia.</span>
        </div>
        <div class="auth-buttons">
            <button onclick="openNotifications()">Powiadomienia (<?= $unreadCount; ?>)</button>
            <a href="../skrypty/processLogin.php?akcja=wyloguj">Wyloguj</a>
        </div>
    </div>
</header>
<main>
    <!-- Sekcja powiadomień -->
    <div id="notifications-section">
        <h2>Powiadomienia</h2>
        <?php if (count($notifications) > 0): ?>
            <form method="POST">
                <button type="submit" name="mark_as_read">Oznacz wszystkie jako przeczytane</button>
            </form>
            <ul>
                <?php foreach ($notifications as $notification): ?>
                    <li>
                        <p><?= htmlspecialchars($notification['message']); ?></p>
                        <small>Wysłano: <?= htmlspecialchars($notification['created_at']); ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Brak nowych powiadomień.</p>
        <?php endif; ?>
        <button id="close-notifications" onclick="closeNotifications()">Zamknij</button>
    </div>

    <!-- Sekcja repertuaru -->
    <section>
        <h1>Repertuar</h1>
        <h3>Wybierz dzień:</h3>
        <form method="get" class="day-selector">
            <label for="day">Dzień:</label>
            <select name="day" id="day" onchange="this.form.submit()">
                <?php for ($i = 0; $i < 7; $i++): ?>
                    <?php
                    $date = strtotime("+$i days");
                    $formatted_date = date("Y-m-d", $date);
                    $day_name = strftime("%A", $date);
                    $selected = ($formatted_date === $selected_day) ? "selected" : "";
                    ?>
                    <option value="<?= $formatted_date; ?>" <?= $selected; ?>>
                        <?= mb_strtolower($day_name) . " (" . date("d/m/Y", $date) . ")"; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </form>
        <div class="schedule-container">
            <?php if (!empty($movies)): ?>
                <?php foreach ($movies as $movie): ?>
                    <div class="movie-card">
                        <img src="../obrazy/<?= htmlspecialchars($movie['image']); ?>" alt="<?= htmlspecialchars($movie['title']); ?>">
                        <h3><?= htmlspecialchars($movie['title']); ?></h3>
                        
                        <?php if (isset($movie['times']) && is_array($movie['times'])): ?>
                            <div class="showtimes">
                                <?php 
                                $grouped_times = [];
                                foreach ($movie['times'] as $time_info) {
                                    $format = $time_info['format'];
                                    $time = $time_info['time'];
                                    $grouped_times[$format][] = $time;
                                }
                                ?>
                                <?php foreach ($grouped_times as $format => $times): ?>
                                    <div class="format-group">
                                        <h4><?= htmlspecialchars($format); ?>:</h4>
                                        <?php foreach ($times as $time): ?>
                                            <a href="rezerwacja.php?movie=<?= urlencode($movie['title']); ?>&date=<?= urlencode($selected_day); ?>&time=<?= urlencode($time); ?>" 
                                               class="showtime-button"><?= htmlspecialchars($time); ?></a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <a href="<?= htmlspecialchars($movie['filmweb']); ?>" target="_blank" class="filmweb-button">Więcej na Filmweb</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Brak dostępnych filmów w repertuarze.</p>
            <?php endif; ?>
        </div>
        
        <!-- Przycisk Edycja rezerwacji -->
        <?php if (isset($_SESSION['reservation']) && !empty($_SESSION['reservation'])): ?>
            <div class="reservation-edit">
                <p><strong>Ostatnia rezerwacja:</strong></p>
                <p>Film: <?= htmlspecialchars($_SESSION['reservation']['movie']); ?></p>
                <p>Data: <?= htmlspecialchars($_SESSION['reservation']['date']); ?></p>
                <p>Liczba biletów: <?= htmlspecialchars($_SESSION['reservation']['count']); ?></p>
                <a href="edycjaRezerwacji.php" class="edit-button">Edycja rezerwacji</a>
            </div>
        <?php endif; ?>
        
    </section>
</main>
<footer>
    <div id="stopka"> &copy; 2025 kino pełne magii </div>
</footer>
<script>
    function openNotifications() {
        document.getElementById('notifications-section').style.display = 'block';
    }

    function closeNotifications() {
        document.getElementById('notifications-section').style.display = 'none';
    }
</script>
</body>
</html>
