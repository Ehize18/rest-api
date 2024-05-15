<?php

$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "your_database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $listingId = $_POST["listing_id"];
    $userId = $_POST["user_id"];
    $rating = $_POST["rating"];
    $comment = $_POST["comment"];

    $sql = "INSERT INTO reviews (listing_id, user_id, rating, comment)
            VALUES ('$listingId', '$userId', '$rating', '$comment')";

    if ($conn->query($sql) === TRUE) {
        echo "Отзыв успешно добавлен.";
    } else {
        echo "Ошибка: " . $sql . "<br>" . $conn->error;
    }
}

$listingId = $_GET["listing_id"];

$sql = "SELECT * FROM listings WHERE id = '$listingId'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    echo "<h2>" . $row["title"] . "</h2>";
    echo "<p>" . $row["description"] . "</p>";

    echo "<form method='post' action=''>";
    echo "<input type='hidden' name='listing_id' value='" . $listingId . "'>";
    echo "<input type='hidden' name='user_id' value='" . $_SESSION["user_id"] . "'>"; 
    echo "<label for='rating'>Рейтинг:</label>";
    echo "<select name='rating'>";
    echo "<option value='1'>1</option>";
    echo "<option value='2'>2</option>";
    echo "<option value='3'>3</option>";
    echo "<option value='4'>4</option>";
    echo "<option value='5'>5</option>";
    echo "</select><br>";
    echo "<label for='comment'>Комментарий:</label>";
    echo "<textarea name='comment'></textarea><br>";
    echo "<input type='submit' value='Оставить отзыв'>";
    echo "</form>";

    $sql = "SELECT * FROM reviews WHERE listing_id = '$listingId'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<h3>Отзывы:</h3>";
        while ($row = $result->fetch_assoc()) {
            echo "<p><strong>Рейтинг:</strong> " . $row["rating"] . "</p>";
            echo "<p>" . $row["comment"] . "</p>";
        }
    } else {
        echo "Пока нет отзывов об этом объявлении.";
    }
} else {
    echo "Объявление не найдено.";
}

$conn->close();

?>