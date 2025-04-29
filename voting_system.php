<?php
// Conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "voting_system";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Conexão falhou: " . $e->getMessage());
}

// Criação das tabelas se não existirem
$conn->exec("
    CREATE TABLE IF NOT EXISTS options (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        votes INT DEFAULT 0
    );

    CREATE TABLE IF NOT EXISTS votes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        option_id INT,
        user_ip VARCHAR(45),
        vote_time DATETIME,
        FOREIGN KEY (option_id) REFERENCES options(id)
    );
");

// Função para evitar votação duplicada pelo mesmo IP
function hasVoted($conn, $ip) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM votes WHERE user_ip = ?");
    $stmt->execute([$ip]);
    return $stmt->fetchColumn() > 0;
}

// Processar voto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['option_id'])) {
    $option_id = filter_input(INPUT_POST, 'option_id', FILTER_VALIDATE_INT);
    $user_ip = $_SERVER['REMOTE_ADDR'];}
    
    if (!hasVoted($conn, $user_ip)) {
        try {
            // Iniciar transação
            $conn->beginTransaction();
            
            // Registrar voto
            $stmt = $conn->prepare("INSERT INTO votes (option_id, user_ip, vote_time) VALUES (?, ?, NOW())");
            $stmt->execute([$option_id, $user_ip]);
            
            // Incrementar contagem de votos
            $stmt = $conn->prepare("UPDATE options SET votes = votes + 1 WHERE id = ?");
            $stmt->execute([$option_id]);
            
            $conn->commit();
            $message = "Voto registrado com sucesso!";

        }
        <style>
        ,body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
    }
    h1 { color: #333; }
    .option { margin: 10px 0; }
    .error { color: red; }
    .success { color: green; }
    .ranking { margin-top: 20px; }
    .ranking table { width: 100%; border-collapse: collapse; }
    .ranking th, .ranking td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    .ranking th { background-color: #f2f2f2; }
        </style>
       
</style>
</head>
<body>
    <h1>Sistema de Votação</h1>
    
    <?php if (isset($message)): ?>
        <p class="<?php echo strpos($message, 'sucesso') !== false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <?php if (!hasVoted($conn, $_SERVER['REMOTE_ADDR'])): ?>
        <form method="POST">
            <h2>Escolha uma opção:</h2>
            <?php
            $stmt = $conn->query("SELECT * FROM options");
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
    <?php else: ?>
        <p>Você já votou. Veja o ranking abaixo:</p>
    <?php endif; ?>

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
            $stmt = $conn->query("SELECT SUM(votes) as total_votes FROM options");
            $total_votes = $stmt->fetch(PDO::FETCH_ASSOC)['total_votes'] ?: 1;
            
            $stmt = $conn->query("SELECT * FROM options ORDER BY votes DESC");
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