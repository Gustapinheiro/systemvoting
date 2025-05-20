<!-- projetos.php -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="projeto.css">
    <title>Projetos - Sala 3F</title>
</head>
<body>
    <h1>Escolha um Projeto - Sala 3F</h1>
    <form action="feedback.php" method="POST">
        <div class="projeto-container">
            <div class="projeto">
                <input type="radio" name="projeto" value="Projeto 1" required> Projeto 1
            </div>
            <div class="projeto">
                <input type="radio" name="projeto" value="Projeto 2" required> Projeto 2
            </div>
        </div>
        <input type="hidden" name="sala" value="3F">
        <button type="submit" name="votar" class="btn-votar">Votar</button>
    </form>
    <a href="sala.php">Voltar</a>
</body>
</html>