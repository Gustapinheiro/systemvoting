<?php
// Conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "system_voting";

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Votação - Salas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .sala-container {
            display: flex;
            gap: 20px;
            justify-content: center;
        }
        .sala {
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
        }
        .sala:hover {
            background-color: #f0f0f0;
        }
        .feedback-list {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <h1>Escolha uma Sala</h1>
    <div class="sala-container">
        <a href="projeto.php"><div class="sala">3° F</div></a>
        <a href="#"><div class="sala">2º F</div></a>
        <a href="#"><div class="sala">1º F</div></a>
    </div>

    <div class="feedback-list">
        <h2>Feedbacks Registrados</h2>
        <?php
        $conn = new mysqli('localhost', 'root', '', 'system_voting');
        if ($conn->connect_error) {
            die("Conexão falhou: " . $conn->connect_error);
        }

        $sql = "SELECT v.sala, v.projeto, f.feedback, f.data_criacao 
                FROM votos v 
                LEFT JOIN feedbacks f ON v.id = f.voto_id 
                ORDER BY f.data_criacao DESC";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<div>";
                echo "<p><strong>Sala:</strong> " . $row["sala"] . " | ";
                echo "<strong>Projeto:</strong> " . $row["projeto"] . " | ";
                echo "<strong>Feedback:</strong> " . ($row["feedback"] ?? "Sem feedback") . " | ";
                echo "<strong>Data:</strong> " . ($row["data_criacao"] ?? "-") . "</p>";
                echo "</div>";
            }
        } else {
            echo "<p>Nenhum feedback registrado ainda.</p>";
        }
        $conn->close();
        ?>
    </div>
</body>
</html>
