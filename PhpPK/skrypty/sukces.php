<!DOCTYPE html><!--
Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHPWebPage.php to edit this template
-->
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejestracja zakończona</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>Rejestracja przebiegła pomyślnie!</h1>
    <p>Za chwilę zostaniesz przekierowany na stronę główną.</p>
    <?php
        // Automatyczne przekierowanie po kilku sekundach
        header("Refresh: 5; url=../index.php");
    ?>
</body>
</html>

