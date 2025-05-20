<?php
// Conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "system_voting";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Conexão falhou: " . $e->getMessage());
}

// Inserir opções iniciais se não houver nenhuma
$stmt = $conn->query("SELECT COUNT(*) FROM Options");
if ($stmt->fetchColumn() == 0) {
    $conn->exec("
        INSERT INTO Options (name, votes) VALUES 
        ('Sala 1', 0),
        ('Sala 2', 0),
        ('Sala 3', 0)
    ");
}

// Processar voto
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['option_id'])) {
    $option_id = filter_input(INPUT_POST, 'option_id', FILTER_VALIDATE_INT);
    $user_ip = $_SERVER['REMOTE_ADDR'];

    if ($option_id) {
        try {
            // Iniciar transação
            $conn->beginTransaction();
            
            // Registrar voto
            $stmt = $conn->prepare("INSERT INTO Votes (option_id, user_ip, vote_time) VALUES (?, ?, NOW())");
            $stmt->execute([$option_id, $user_ip]);
            
            // Incrementar contagem de votos
            $stmt = $conn->prepare("UPDATE Options SET votes = votes + 1 WHERE id = ?");
            $stmt->execute([$option_id]);
            
            $conn->commit();
            $message = "Voto registrado com sucesso!";
        } catch (PDOException $e) {
            $conn->rollBack();
            $message = "Erro ao registrar voto: " . $e->getMessage();
        }
    } else {
        $message = "Opção inválida!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1 { color: #333; }
        .option { margin: 10px 0; }
        .error { color: red; }
        .success { color: green; }
        .ranking { margin-top: 20px; }
        .ranking table { width: 100%; border-collapse: collapse; }
        .ranking th, .ranking td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .ranking th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Sistema de Votação</h1>
    
    <?php if (isset($message)): ?>
        <p class="<?php echo strpos($message, 'sucesso') !== false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <h2>Escolha uma opção:</h2>
        <?php
        $stmt = $conn->query("SELECT * FROM Options");
        while ($option = $stmt->fetch(PDO::FETCH_ASSOC)):
        ?>
            <div class="option">
                <input type="radio" name="option_id" value="<?php echo $option['id']; ?>" 
                       id="option_<?php echo $option['id']; ?>" required>
                <label for="option_<?php echo $option['id']; ?>">
                    <?php echo htmlspecialchars($option['name']); ?>
                </label>
            </div>
        <?php endwhile; ?>
        <button type="submit">Votar</button>
    </form>

    <div class="ranking">
        <h2>Ranking de Votos</h2>
        <table>
            <tr>
                <th>Posição</th>
                <th>Opção</th>
                <th>Votos</th>
                <th>Porcentagem</th>
            </tr>
            <?php
            $stmt = $conn->query("SELECT SUM(votes) as total_votes FROM Options");
            $total_votes = $stmt->fetch(PDO::FETCH_ASSOC)['total_votes'] ?: 1;
            
            $stmt = $conn->query("SELECT * FROM Options ORDER BY votes DESC");
            $position = 1;
            while ($option = $stmt->fetch(PDO::FETCH_ASSOC)):
                $percentage = ($option['votes'] / $total_votes) * 100;
            ?>
                <tr>
                    <td><?php echo $position++; ?>º</td>
                    <td><?php echo htmlspecialchars($option['name']); ?></td>
                    <td><?php echo $option['votes']; ?></td>
                    <td><?php echo number_format($percentage, 2); ?>%</td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>

<?php
// Fechar conexão
$conn = null;
?>