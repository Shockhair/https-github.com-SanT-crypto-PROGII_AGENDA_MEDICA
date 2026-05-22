<?php
    //abrir banco de dados:
    $host_bd = "localhost";
    $login_bd = "root";
    $password_bd = "";
    $nome_bd = "labdbprog2";
    $port = 3307;

    $conexao_bd = mysqli_connect($host_bd, $login_bd, $password_bd,$nome_bd, $port);


?>