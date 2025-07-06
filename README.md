# 🎬 Kino 
---

## 📌 Temat: System Rezerwacji Biletów KINO

System umożliwia użytkownikom rejestrację, logowanie oraz dokonywanie rezerwacji biletów na seanse filmowe. Administrator ma dostęp do listy użytkowników i może wysyłać do nich powiadomienia.

---

## 💡 Funkcjonalności
 ![Zrzut ekranu 2025-07-06 151312](https://github.com/user-attachments/assets/6da6506f-c3ed-4d04-a695-212fe0dfafca)

👥 **Użytkownik:**
- Rejestracja nowego konta
 ![Zrzut ekranu 2025-07-06 152603](https://github.com/user-attachments/assets/1d423d9b-c24e-4c01-96c6-fd4b12a2b5d9)

- Logowanie za pomocą loginu i hasła
 ![Zrzut ekranu 2025-07-06 151514](https://github.com/user-attachments/assets/cb46a98e-0885-428d-bcbc-4ea195c456b5)

- Rezerwacja biletów (z wyborem daty, godziny i miejsc)
 ![Zrzut ekranu 2025-07-06 152237](https://github.com/user-attachments/assets/a1c8ca08-dfb4-4c72-803b-d8a012582c92)
 ![Zrzut ekranu 2025-07-06 152300](https://github.com/user-attachments/assets/43573a50-b3cc-46f8-a40b-b3d0a1e4aba4)

- Podgląd dostępnych miejsc w czasie rzeczywistym
 ![Zrzut ekranu 2025-07-06 152350](https://github.com/user-attachments/assets/2b6027d4-b486-4bd2-904e-682d16d1fe5e)

- Edycja (zmiana miejsc lub liczby biletów) i usuwanie rezerwacji 
 ![Zrzut ekranu 2025-07-06 152445](https://github.com/user-attachments/assets/cff6fd08-baf9-4b2c-8b1f-16e62cde81b5)
 ![Zrzut ekranu 2025-07-06 152517](https://github.com/user-attachments/assets/9ed7c407-4f3f-4687-b5c1-93dfd8e90374)


- Odbieranie powiadomień o promocjach i nowościach
 ![Zrzut ekranu 2025-07-06 152205](https://github.com/user-attachments/assets/5c106b49-589b-460e-adb3-66d253ed5bd1)


🛠️ **Administrator:**
- Podgląd listy zarejestrowanych użytkowników
- Wysyłanie powiadomień do użytkowników
  ![Zrzut ekranu 2025-07-06 151645](https://github.com/user-attachments/assets/62ff483c-1b44-4044-b6da-67ff577bf5d0)

  

---

## 🚀 Uruchomienie projektu lokalnie

1. Wypakuj folder `PhpPK` do katalogu: xampp/htdocs
   
2.  Uruchom **XAMPP** i włącz moduły:
- Apache
- MySQL

3. Wejdź do **phpMyAdmin**:
- Otwórz: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
- Stwórz nową bazę danych o nazwie: `kino`
- W zakładce **Import** załaduj plik `kino.sql` 

4. Uruchom projekt:
- W przeglądarce: (http://localhost/PhpPK/index.php)

---

## 📂 Struktura bazy danych

Plik `kino.sql` zawiera następujące tabele:
- `users` – zarejestrowani użytkownicy
- `powiadomienia` – system wiadomości i promocji
- `logged_in_users` – zalogowani użytkownicy
- `reservations` – dokonane rezerwacje
- `seat` – miejsca w salach kinowych

---

## 👤 Konta testowe

### 👩 Użytkownik:
- **Login:** `Agnieszka`
- **Hasło:** `Agnieszka123`

### 🧑‍💼 Administrator:
- **Login:** `Admin`
- **Hasło:** `Administrator`

---


