<?php
/* ============================================================
   cancelar_agendamento.php
   Endpoint chamado via fetch() pelo principal.php para
   cancelar (status = 'Cancelado') um agendamento pelo id.

   Método esperado : POST
   Parâmetro       : id (int) — id do agendamento
   Retorno         : JSON  { "sucesso": true }
                        ou { "sucesso": false, "mensagem": "..." }
============================================================ */

// Garante que a resposta será sempre JSON
header('Content-Type: application/json; charset=utf-8');

/* ============================================================
   VALIDAÇÃO DA REQUISIÇÃO
============================================================ */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('sucesso' => false, 'mensagem' => 'Método não permitido.'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(array('sucesso' => false, 'mensagem' => 'ID inválido.'));
    exit;
}

/* ============================================================
   CONEXÃO COM O BANCO DE DADOS
   TODO: Mover as credenciais para um arquivo de configuração
         (ex: config.php) fora da pasta pública

   Exemplo de config.php:
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'mediagenda');
   define('DB_USER', 'root');
   define('DB_PASS', '');
============================================================ */

// require_once '../config.php';

// $conexao = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
// $conexao->set_charset('utf8mb4');

// if ($conexao->connect_error) {
//     http_response_code(500);
//     echo json_encode(array('sucesso' => false, 'mensagem' => 'Erro de conexão com o banco.'));
//     exit;
// }

/* ============================================================
   CANCELAMENTO DO AGENDAMENTO
   Utiliza exclusão lógica: atualiza o status para 'Cancelado'
   em vez de remover o registro fisicamente da tabela.

   Para exclusão física, substitua o UPDATE por:
   $sql = 'DELETE FROM agendamentos WHERE id = ?';
============================================================ */

// $sql  = "UPDATE agendamentos SET status = 'Cancelado' WHERE id = ?";
// $stmt = $conexao->prepare($sql);

// if (!$stmt) {
//     http_response_code(500);
//     echo json_encode(array('sucesso' => false, 'mensagem' => 'Erro ao preparar a query.'));
//     $conexao->close();
//     exit;
// }

// $stmt->bind_param('i', $id);
// $stmt->execute();

// if ($stmt->affected_rows === 0) {
//     http_response_code(404);
//     echo json_encode(array('sucesso' => false, 'mensagem' => 'Agendamento não encontrado.'));
//     $stmt->close();
//     $conexao->close();
//     exit;
// }

// $stmt->close();
// $conexao->close();

/* ============================================================
   RESPOSTA DE SUCESSO
   TODO: remover este echo após descomentar o bloco acima
============================================================ */
echo json_encode(array('sucesso' => true));
