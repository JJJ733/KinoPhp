<?php
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['userId'])) {
    echo "<script>alert('Tylko dla zalogowanych użytkowników. Zaloguj się, aby zarezerwować bilety.'); window.location.href = '../processLogin.php';</script>";
    exit;
}


// Połączenie z bazą danych
$mysqli = new mysqli('localhost', 'root', '', 'kino');

if ($mysqli->connect_error) {
    die('Błąd połączenia z bazą danych: ' . $mysqli->connect_error);
}

// Wczytaj dane filmów z JSON
$json_file = __DIR__ . '/../movies.json';
$movies = [];
if (file_exists($json_file)) {
    $json_data = file_get_contents($json_file);
    $movies = json_decode($json_data, true)['movies'];
}

// Funkcja do pobrania zajętych miejsc
function getOccupiedSeats($mysqli, $movie_id, $date) {
    $stmt = $mysqli->prepare("SELECT row_number, seat_number FROM seats WHERE movie_id = ? AND date = ?");
    $stmt->bind_param("is", $movie_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $occupied_seats = [];
    while ($row = $result->fetch_assoc()) {
        $occupied_seats[] = $row['row_number'] . '-' . $row['seat_number'];
    }
    $stmt->close();
    return $occupied_seats;
}

// Pobierz zajęte miejsca
$occupied_seats = [];
if (!empty($_GET['movie']) && !empty($_GET['date'])) {
    $movie_id = (int)$_GET['movie'];
    $date = $_GET['date'];
    $occupied_seats = getOccupiedSeats($mysqli, $movie_id, $date);
}

// Obsługa rezerwacji
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pobranie i walidacja danych wejściowych
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_NUMBER_INT);
    $movie_id = isset($_GET['movie']) ? htmlspecialchars($_GET['movie']) : null;
    $date = isset($_GET['date']) ? htmlspecialchars($_GET['date']) : null;

    // Miejsca z ich typami biletów
    $seats_with_types = isset($_POST['seats_with_types']) ? json_decode($_POST['seats_with_types'], true) : [];
    if (!is_array($seats_with_types)) $seats_with_types = []; // Upewnij się, że dane są tablicą
    $ticket_count = count($seats_with_types);

    // Walidacja danych
    if ($ticket_count === 0) {
        $message = "Musisz wybrać przynajmniej jedno miejsce!";
    } elseif ($ticket_count > 10) {
        $message = "Możesz zarezerwować maksymalnie 10 miejsc.";
    } elseif ($name && $email && $phone && $movie_id && $date && !empty($seats_with_types)) {
        // Zapisanie rezerwacji do tabeli `reservations`
        $general_ticket_type = $seats_with_types[0]['type']; // Pierwszy typ jako ogólny
        $stmt = $mysqli->prepare("INSERT INTO reservations (name, email, phone, movie_id, date, ticket_count, ticket_type, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssssis", $name, $email, $phone, $movie_id, $date, $ticket_count, $general_ticket_type);

        if ($stmt->execute()) {
            $reservation_id = $stmt->insert_id;

            // Zapisanie szczegółów miejsc w tabeli `seats`
            $seat_stmt = $mysqli->prepare("INSERT INTO seats (reservation_id, row_number, seat_number, movie_id, date, ticket_type) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($seats_with_types as $seat) {
                $row = (int)$seat['row'];
                $seat_number = (int)$seat['seat'];
                $ticket_type = $seat['type'];
                $seat_stmt->bind_param("iiisss", $reservation_id, $row, $seat_number, $movie_id, $date, $ticket_type);
                $seat_stmt->execute();
            }
            $seat_stmt->close();

            // Przekierowanie po sukcesie
            echo "<script>
                alert('Rezerwacja została pomyślnie zapisana! Zaraz wrócisz na stronę główną.');
                window.location.href = 'testLogin.php';
            </script>";
            exit;
        } else {
            $message = "Błąd podczas zapisywania rezerwacji: " . $mysqli->error;
        }
        $stmt->close();
    } else {
        $message = "Wszystkie pola są wymagane i muszą być poprawne!";
    }
}

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rezerwacja miejsc</title>
    <link rel="stylesheet" href="../css/rezerwacja.css">
</head>
<body>
<div class="reservation-container">
    <h1>Rezerwacja miejsc</h1>

    <?php if (!empty($message)): ?>
        <p class="message"><?= htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form action="rezerwacjaIndex.php" method="POST">
        <div class="form-group">
            <label for="name">Imię i nazwisko:</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div class="form-group">
            <label for="email">Adres e-mail:</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="phone">Numer telefonu:</label>
            <input type="tel" id="phone" name="phone" pattern="\d{9}" required>
        </div>

        <div class="form-group">
            <label for="city">Miasto:</label>
            <input type="text" id="city" name="city" required>
        </div>

        <div class="form-group">
            <label for="ticket_type">Rodzaj biletu:</label>
            <select id="ticket_type" name="ticket_type" required>
                <option value="normalny">Normalny</option>
                <option value="ulgowy">Ulgowy</option>
            </select>
        </div>

        <div class="form-group">
            <label for="movie">Film:</label>
            <select id="movie" name="movie" required>
                <?php foreach ($movies as $movie): ?>
                    <option value="<?= htmlspecialchars($movie['id']); ?>"><?= htmlspecialchars($movie['title']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="date">Data seansu:</label>
            <input type="date" id="date" name="date" required>
        </div>

        <div class="cinema-room">
            <div class="screen">Ekran</div>
            <?php for ($row = 1; $row <= 12; $row++): ?>
                <div class="row">
                    <?php for ($seat = 1; $seat <= 14; $seat++): ?>
                        <?php
                        $seat_id = $row . '-' . $seat;
                        if ($seat === 7) {
                            echo '<div class="path"></div>';
                        }
                        ?>
                        <div class="seat <?= in_array($seat_id, $occupied_seats) ? 'occupied' : ''; ?>" data-seat="<?= $seat_id; ?>"></div>
                    <?php endfor; ?>
                </div>
            <?php endfor; ?>
        </div>

        <input type="hidden" id="seats" name="seats" value="">
        <button type="submit" class="reserve-button">Zarezerwuj</button>
    </form>
</div>

<script>
    const seats = document.querySelectorAll('.seat:not(.occupied)');
    const selectedSeats = [];
    const selectedSeatsContainer = document.getElementById('selected-seats-container');
    const seatsWithTypesInput = document.getElementById('seats_with_types');

    seats.forEach(seat => {
        seat.addEventListener('click', () => {
            const row = seat.parentElement.dataset.row;
            const seatIndex = Array.from(seat.parentElement.children).indexOf(seat) + 1;
            const seatId = `${row}-${seatIndex}`;

            if (seat.classList.contains('selected')) {
                seat.classList.remove('selected');
                selectedSeats.splice(selectedSeats.indexOf(seatId), 1);
                document.getElementById(`seat-${seatId}`).remove();
            } else {
                if (selectedSeats.length < 10) {
                    seat.classList.add('selected');
                    selectedSeats.push(seatId);

                    const seatTypeDiv = document.createElement('div');
                    seatTypeDiv.id = `seat-${seatId}`;
                    seatTypeDiv.innerHTML = `
                        <label>Miejsce ${seatId}:</label>
                        <select data-seat="${seatId}">
                            <option value="normalny">Normalny</option>
                            <option value="ulgowy">Ulgowy</option>
                        </select>
                    `;
                    selectedSeatsContainer.appendChild(seatTypeDiv);
                } else {
                    alert('Możesz zarezerwować maksymalnie 10 miejsc!');
                }
            }
            updateSeatsWithTypes();
        });
    });

    function updateSeatsWithTypes() {
        const seatsWithTypes = [];
        document.querySelectorAll('#selected-seats-container select').forEach(select => {
            const seatId = select.getAttribute('data-seat');
            const [row, seat] = seatId.split('-');
            seatsWithTypes.push({ row, seat, type: select.value });
        });
        seatsWithTypesInput.value = JSON.stringify(seatsWithTypes);
    }
</script>
</body>
</html>