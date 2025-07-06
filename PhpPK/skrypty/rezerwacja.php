<?php
session_start();

// Połączenie z bazą danych
$mysqli = new mysqli('localhost', 'root', '', 'kino');
if ($mysqli->connect_error) {
    die('Błąd połączenia z bazą danych: ' . $mysqli->connect_error);
}

// Pobierz dane o filmie, dacie i godzinie z parametrów GET
$movie_title = isset($_GET['movie']) ? htmlspecialchars($_GET['movie']) : null;
$date = isset($_GET['date']) ? htmlspecialchars($_GET['date']) : null;
$time = isset($_GET['time']) ? htmlspecialchars($_GET['time']) : null;

// Sprawdź, czy dane filmu, daty i godziny zostały przekazane
if (!$movie_title || !$date || !$time) {
    die('Brak wymaganych danych: tytułu filmu, daty lub godziny.');
}

// Funkcja do pobrania zajętych miejsc
function getOccupiedSeats($mysqli, $movie_title, $date, $time) {
    $stmt = $mysqli->prepare("SELECT row_number, seat_number FROM seats WHERE movie_title = ? AND date = ? AND time = ?");
    $stmt->bind_param("sss", $movie_title, $date, $time);
    $stmt->execute();
    $result = $stmt->get_result();
    $occupied_seats = [];
    while ($row = $result->fetch_assoc()) {
        $occupied_seats[] = $row['row_number'] . '-' . $row['seat_number'];
    }
    $stmt->close();
    return $occupied_seats;
}

// Pobierz zajęte miejsca dla wybranego filmu, daty i godziny
$occupied_seats = getOccupiedSeats($mysqli, $movie_title, $date, $time);

// Obsługa rezerwacji
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_NUMBER_INT);
    $movie_title = htmlspecialchars($_POST['movie']); // Tytuł filmu przekazany w ukrytym polu formularza
    $date = htmlspecialchars($_POST['date']);
    $time = htmlspecialchars($_POST['time']);
    $seats_with_types = isset($_POST['seats_with_types']) ? json_decode($_POST['seats_with_types'], true) : [];
    if (!is_array($seats_with_types)) $seats_with_types = [];
    $ticket_count = count($seats_with_types);

    if ($ticket_count === 0) {
        $message = "Musisz wybrać przynajmniej jedno miejsce!";
    } elseif ($ticket_count > 10) {
        $message = "Możesz zarezerwować maksymalnie 10 miejsc.";
    } elseif ($name && $email && $phone && $movie_title && $date && $time && !empty($seats_with_types)) {
        $general_ticket_type = $seats_with_types[0]['type'];
        $stmt = $mysqli->prepare("INSERT INTO reservations (name, email, phone, movie_title, date, time, ticket_count, ticket_type, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssssssis", $name, $email, $phone, $movie_title, $date, $time, $ticket_count, $general_ticket_type);

        if ($stmt->execute()) {
            $reservation_id = $stmt->insert_id;

            $seat_stmt = $mysqli->prepare("INSERT INTO seats (reservation_id, row_number, seat_number, movie_title, date, time, ticket_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($seats_with_types as $seat) {
                $row = (int)$seat['row'];
                $seat_number = (int)$seat['seat'];
                $ticket_type = $seat['type'];
                $seat_stmt->bind_param("iiissss", $reservation_id, $row, $seat_number, $movie_title, $date, $time, $ticket_type);
                $seat_stmt->execute();
            }
            $seat_stmt->close();

            $_SESSION['reservation'] = [
                'id' => $reservation_id,
                'movie' => $movie_title,
                'date' => $date,
                'time' => $time,
                'tickets' => $seats_with_types,
                'count' => $ticket_count
            ];

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
    <title>Rezerwacja miejsc - <?= htmlspecialchars($movie_title); ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/rezerwacja.css">
</head>
<body>
<div class="reservation-container">
    <h1>Rezerwacja miejsc</h1>
    <div class="movie-info">
        <p><strong>Film:</strong> <?= htmlspecialchars($movie_title); ?></p>
        <p><strong>Data:</strong> <?= htmlspecialchars($date); ?></p>
        <p><strong>Godzina:</strong> <?= htmlspecialchars($time); ?></p>
    </div>

    <?php if (!empty($message)): ?>
        <p class="message"><?= htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form action="rezerwacja.php?movie=<?= urlencode($movie_title); ?>&date=<?= urlencode($date); ?>&time=<?= urlencode($time); ?>" method="POST">
        <input type="hidden" name="movie" value="<?= htmlspecialchars($movie_title); ?>">
        <input type="hidden" name="date" value="<?= htmlspecialchars($date); ?>">
        <input type="hidden" name="time" value="<?= htmlspecialchars($time); ?>">
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
            <input type="tel" id="phone" pattern="\d{9}" name="phone" required>
        </div>
        <div class="cinema-room">
            <div class="screen">Ekran</div>
            <?php for ($row = 1; $row <= 12; $row++): ?>
                <div class="row" data-row="<?= $row; ?>">
                    <?php for ($seat = 1; $seat <= 14; $seat++): ?>
                        <?php
                        $seat_id = $row . '-' . $seat;
                        $is_occupied = in_array($seat_id, $occupied_seats);
                        if ($seat === 7) {
                            echo '<div class="path"></div>';
                        }
                        ?>
                        <div class="seat <?= $is_occupied ? 'occupied' : ''; ?>" 
                             data-seat="<?= $seat_id; ?>"></div>
                    <?php endfor; ?>
                </div>
            <?php endfor; ?>
        </div>
        <div id="selected-seats-container" class="form-group">
            <h3>Wybrane miejsca</h3>
            <ul id="summary-list"></ul>
        </div>
        <input type="hidden" id="seats_with_types" name="seats_with_types" value="">
        <button type="submit" class="reserve-button">Zarezerwuj</button>
    </form>
</div>
<footer>
    <div id="stopka"> &copy; 2025 kino pełne magii </div>
</footer>
<script>
const seats = document.querySelectorAll('.seat:not(.occupied)');
const selectedSeats = [];
const selectedSeatsContainer = document.getElementById('selected-seats-container');
const seatsWithTypesInput = document.getElementById('seats_with_types');
const summaryList = document.getElementById('summary-list');

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
    summaryList.innerHTML = ''; // Wyczyść podsumowanie przed aktualizacją
    document.querySelectorAll('#selected-seats-container select').forEach(select => {
        const seatId = select.getAttribute('data-seat');
        const [row, seat] = seatId.split('-'); // Rozdziel numer rzędu i miejsca
        const type = select.value; // Pobierz wybrany typ biletu

        // Zapisz dane o miejscu i typie biletu w obiekcie
        seatsWithTypes.push({ row: parseInt(row), seat: parseInt(seat), type });

        // Dodaj do podsumowania
        const li = document.createElement('li');
        li.textContent = `Miejsce ${row}-${seat} - ${type}`;
        summaryList.appendChild(li);
    });
    // Zapisz dane w ukrytym polu formularza jako JSON
    seatsWithTypesInput.value = JSON.stringify(seatsWithTypes);
    
        document.querySelectorAll('#selected-seats-container').forEach(container => {
        container.addEventListener('change', updateSeatsWithTypes); // Aktualizacja przy zmianie typu
    });
}

</script>
</body>
</html>

