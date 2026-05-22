<?php
    if(!isset($_SESSION)){
        session_start();
    }
    //destruir a sessão:
    session_destroy();
    //redirecionar para a página de login:
    header("location:login.php");
?>