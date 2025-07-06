<?php

session_start();

// Sprawdź, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header("Location: ../skrypty/processLogin.php");
    exit;
}

$user_id = $_SESSION['user_id']; // ID użytkownika z sesji

// Sprawdź, czy istnieje aktywna rezerwacja
if (!isset($_SESSION['reservation'])) {
    die('Nie znaleziono aktywnej rezerwacji do edycji.');
}

$reservation = $_SESSION['reservation'];
$movie_title = htmlspecialchars($reservation['movie']); // Tytuł filmu
$date = htmlspecialchars($reservation['date']);         // Data seansu
$time = htmlspecialchars($reservation['time']);         // Godzina seansu
$tickets = $reservation['tickets'];
$ticket_count = $reservation['count'];

// Połączenie z bazą danych
$mysqli = new mysqli('localhost', 'root', '', 'kino');
if ($mysqli->connect_error) {
    die('Błąd połączenia z bazą danych: ' . $mysqli->connect_error);
}

// Funkcja do pobrania zajętych miejsc (z wykluczeniem obecnej rezerwacji)
function getOccupiedSeats($mysqli, $movie_title, $date, $time, $exclude_reservation_id = null) {
    $query = "SELECT row_number, seat_number FROM seats WHERE movie_title = ? AND date = ? AND time = ?";
    if ($exclude_reservation_id) {
        $query .= " AND reservation_id != ?";
    }

    $stmt = $mysqli->prepare($query);
    if ($exclude_reservation_id) {
        $stmt->bind_param("sssi", $movie_title, $date, $time, $exclude_reservation_id);
    } else {
        $stmt->bind_param("sss", $movie_title, $date, $time);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $occupied_seats = [];
    while ($row = $result->fetch_assoc()) {
        $occupied_seats[] = $row['row_number'] . '-' . $row['seat_number'];
    }
    $stmt->close();
    return $occupied_seats;
}

// Pobierz zajęte miejsca (z wykluczeniem obecnej rezerwacji)
$occupied_seats = getOccupiedSeats($mysqli, $movie_title, $date, $time, $reservation['id']);

// Obsługa usuwania rezerwacji
if (isset($_POST['delete_reservation'])) {
    $mysqli->begin_transaction();
    try {
        // Usuń powiązane miejsca
        $stmt = $mysqli->prepare("DELETE FROM seats WHERE reservation_id = ?");
        $stmt->bind_param("i", $reservation['id']);
        $stmt->execute();
        $stmt->close();

        // Usuń rezerwację
        $stmt = $mysqli->prepare("DELETE FROM reservations WHERE id = ?");
        $stmt->bind_param("i", $reservation['id']);
        $stmt->execute();
        $stmt->close();

        $mysqli->commit();
        unset($_SESSION['reservation']);
        header("Location: reservationsList.php");
        exit;
    } catch (Exception $e) {
        $mysqli->rollback();
        $message = "Błąd podczas usuwania rezerwacji: " . $e->getMessage();
    }
}

// Obsługa zapisu edytowanej rezerwacji
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_reservation'])) {
    // Pobranie danych z formularza
    $seats_with_types = isset($_POST['seats_with_types']) ? json_decode($_POST['seats_with_types'], true) : [];
    if (!is_array($seats_with_types)) $seats_with_types = [];
    $ticket_count = count($seats_with_types);

    if ($ticket_count === 0) {
        $message = "Musisz wybrać przynajmniej jedno miejsce!";
    } elseif ($ticket_count > 10) {
        $message = "Możesz zarezerwować maksymalnie 10 miejsc.";
    } else {
        $mysqli->begin_transaction();
        try {
            // Usuń stare miejsca powiązane z rezerwacją
            $stmt = $mysqli->prepare("DELETE FROM seats WHERE reservation_id = ?");
            $stmt->bind_param("i", $reservation['id']);
            $stmt->execute();
            $stmt->close();

            // Dodaj nowe miejsca z typami biletów
            $stmt = $mysqli->prepare(
                "INSERT INTO seats (reservation_id, row_number, seat_number, movie_title, date, time, ticket_type) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            foreach ($seats_with_types as $seat) {
                $row = (int)$seat['row'];
                $seat_number = (int)$seat['seat'];
                $ticket_type = $seat['type'];
                $stmt->bind_param("iiissss", $reservation['id'], $row, $seat_number, $movie_title, $date, $time, $ticket_type);
                $stmt->execute();
            }
            $stmt->close();

            // Zaktualizuj tabelę reservations o liczbę biletów i ogólny typ biletu
            $general_ticket_type = $seats_with_types[0]['type']; // Pobierz typ pierwszego biletu jako reprezentatywny
            $stmt = $mysqli->prepare("UPDATE reservations SET ticket_count = ?, ticket_type = ? WHERE id = ?");
            $stmt->bind_param("isi", $ticket_count, $general_ticket_type, $reservation['id']);
            $stmt->execute();
            $stmt->close();

            // Zaktualizuj dane w sesji
            $_SESSION['reservation']['tickets'] = $seats_with_types;
            $_SESSION['reservation']['count'] = $ticket_count;

            $mysqli->commit();
            header("Location: reservationsList.php");
            exit;
        } catch (Exception $e) {
            $mysqli->rollback();
            $message = "Błąd podczas edytowania rezerwacji: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edycja rezerwacji - <?= $movie_title; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/rezerwacja.css">
</head>
<body>
<div class="reservation-container">
    <h1>Edycja rezerwacji</h1>
    <div class="movie-info">
        <p><strong>Film:</strong> <?= $movie_title; ?></p>
        <p><strong>Data:</strong> <?= $date; ?></p>
        <p><strong>Godzina:</strong> <?= $time; ?></p>
        <p><strong>Liczba biletów:</strong> <?= $ticket_count; ?></p>
    </div>

    <?php if (!empty($message)): ?>
        <p class="message"><?= htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form action="edycjaRezerwacji.php" method="POST">
        <div class="cinema-room">
            <div class="screen">Ekran</div>
            <?php for ($row = 1; $row <= 12; $row++): ?>
                <div class="row" data-row="<?= $row; ?>">
                    <?php for ($seat = 1; $seat <= 14; $seat++): ?>
                        <?php
                        $seat_id = $row . '-' . $seat;
                        $is_occupied = in_array($seat_id, $occupied_seats);
                        $is_selected = in_array($seat_id, array_map(fn($seat) => $seat['row'] . '-' . $seat['seat'], $tickets));
                        if ($seat === 7) {
                            echo '<div class="path"></div>';
                        }
                        ?>
                        <div class="seat <?= $is_occupied ? 'occupied' : ($is_selected ? 'selected' : ''); ?>" 
                             data-seat="<?= $seat_id; ?>"></div>
                    <?php endfor; ?>
                </div>
            <?php endfor; ?>
        </div>
        <div id="selected-seats-container" class="form-group">
            <h3>Wybrane miejsca</h3>
        </div>
        <input type="hidden" id="seats_with_types" name="seats_with_types" value="<?= htmlspecialchars(json_encode($tickets)); ?>">
        <button type="submit" class="reserve-button">Zaktualizuj rezerwację</button>
    </form>

    <form action="edycjaRezerwacji.php" method="POST">
        <button type="submit" name="delete_reservation" class="delete-button">Usuń rezerwację</button>
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

// Obsługa kliknięcia na miejsca
seats.forEach(seat => {
    seat.addEventListener('click', () => {
        const row = seat.parentElement.dataset.row;
        const seatIndex = Array.from(seat.parentElement.children).indexOf(seat) + 1;
        const seatId = `${row}-${seatIndex}`;

        if (seat.classList.contains('selected')) {
            // Usuń miejsce
            seat.classList.remove('selected');
            const index = selectedSeats.findIndex(s => s.id === seatId);
            if (index !== -1) selectedSeats.splice(index, 1);
            document.getElementById(`seat-${seatId}`).remove();
        } else {
            // Dodaj miejsce
            if (selectedSeats.length < 10) {
                seat.classList.add('selected');
                selectedSeats.push({ id: seatId, row: row, seat: seatIndex, type: 'normalny' });

                const seatTypeDiv = document.createElement('div');
                seatTypeDiv.id = `seat-${seatId}`;
                seatTypeDiv.innerHTML = `
                    <label>Miejsce ${seatId}:</label>
                    <select data-seat="${seatId}">
                        <option value="normalny" selected>Normalny</option>
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

// Funkcja do aktualizacji listy miejsc i ukrytego pola formularza
function updateSeatsWithTypes() {
    const seatsWithTypes = [];
    selectedSeats.forEach(seat => {
        const selectElement = document.querySelector(`select[data-seat="${seat.id}"]`);
        if (selectElement) {
            seat.type = selectElement.value; // Pobierz typ biletu z select
        }

        seatsWithTypes.push({
            row: parseInt(seat.row, 10),
            seat: parseInt(seat.seat, 10),
            type: seat.type // Zaktualizowany typ biletu
        });
    });

    // Zapisz dane w ukrytym polu formularza jako JSON
    seatsWithTypesInput.value = JSON.stringify(seatsWithTypes);
}

// Obsługa zmiany typu biletu
selectedSeatsContainer.addEventListener('change', (event) => {
    if (event.target.tagName === 'SELECT') {
        updateSeatsWithTypes();
    }
});

// Aktualizuj ukryte pole formularza przed wysłaniem
document.querySelector('form').addEventListener('submit', (e) => {
    updateSeatsWithTypes(); // Zaktualizuj dane przed wysłaniem
    console.log('Dane przesyłane do serwera:', seatsWithTypesInput.value); // Debugowanie
});

</script>
</body>
</html>

