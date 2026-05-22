<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" 
    content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Cadastro</title>
</head>
<body>
    <h1>Cadastro</h1>
    <?php
        $name   = $_GET["name"];
        $course = $_GET["course"];

        echo "<h2>Olá $name!</h2><h3>Curso: $course</h3>";
    ?>    
    <ul>
        <li><a href="index.php">Home</a></li>
        <li>Cadastro</li>
        <li>Relatórios</li>
    </ul>
    <hr>
    <form action="cadastrobanco.php" method="POST">
        <div>
            <label for="usuario">Usuário:</label>
            <input type="text" name="usuario" id="usuario"
            placeholder="Usuário">
        </div>
        <div>
        <label for="senha">Senha:</label>
            <input type="password" name="senha" id="senha"
            placeholder="Senha">
        </div
        <div>
            <button type="submit">Enviar</button>
        </div>
    </form>
</body>
</html>