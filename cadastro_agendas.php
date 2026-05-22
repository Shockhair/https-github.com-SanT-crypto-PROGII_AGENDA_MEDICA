<?php
session_start();
require_once("conexao.php");// importar o conexao.php para esta página

if(!isset($_SESSION['cod_usuario'])){
    header("Location: login.php");
    exit;
}
$cod_usuario = $_SESSION['cod_usuario'];
$nomeUsuario = "";
$emailUsuario = "";
$sql = "SELECT * FROM usuario WHERE cod_usuario = '$cod_usuario'";

$result = mysqli_query($conexao_bd,$sql); //pega o resultado da query e lança num array

if($consulta = mysqli_fetch_assoc($result)){ //leitura do array
    $nomeUsuario  = $consulta['nome'];
    $emailUsuario = $consulta['email'];
}

/* ============================================================
   DADOS DO OPERADOR LOGADO
   TODO: Substituir pelos dados vindos da $_SESSION
============================================================ */
$operadorNome  = $nomeUsuario;
$operadorEmail = $emailUsuario;

/* ============================================================
   PROCESSAMENTO DE AÇÕES (POST)
   TODO: Implementar as ações ao integrar com o banco de dados

   Estrutura esperada para receber via $_POST:
   - acao        : 'novo' | 'editar' | 'cancelar'
   - id          : int  (apenas para editar/cancelar)
   - paciente    : string
   - medico_id   : int
   - especialidade_id : int
   - data        : 'YYYY-MM-DD'
   - horario     : 'HH:MM'
   - status      : 'Confirmado' | 'Pendente'
*/
echo (">>> passou 0 | " . $_SERVER['REQUEST_METHOD']);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo (">>> passou 1");
        $acao = isset($_POST['acao']) ? $_POST['acao'] : '';
        if ($acao === 'novo') {
            $paciente      = $_POST['paciente'];
            $medico_id     = $_POST['medico_id'];
            $especialidade = $_POST['especialidade'];
            $data          = $_POST['data'];
            $horario       = $_POST['horario'];
            $status        = $_POST['status'];
            $sql           = "INSERT INTO 
                              agendamentos(paciente, medico_id, especialidade_id, data, horario, status) 
                              VALUES('".$paciente."', ".$medico_id.", 1, '".$data."',
                              '".$horario."', '".$status."')";
            mysqli_query($conexao_bd, $sql) or die('ERR: '.mysql_error());
           // INSERT INTO agendamentos (...) VALUES (...)
        } elseif ($acao === 'editar') {
            $paciente      = $_POST['paciente'];
            $medico_id     = $_POST['medico_id'];
            $especialidade = $_POST['especialidade'];
            $data          = $_POST['data'];
            $horario       = $_POST['horario'];
            $status        = $_POST['status'];
            $id_agenda     = $_POST['id'];
            $sql = "UPDATE agendamentos SET 
                     paciente = '".$paciente."',
                     medico_id = ".$medico_id.",
                     especialidade_id = 1, 
                     data = '".$data."',
                     horario = '".$horario."',
                     status  = '".$status."'
                    WHERE id = ".$id_agenda;
            mysqli_query($conexao_bd, $sql) or die("ERR.: ".mysql_error());
        } elseif ($acao === 'cancelar') {
            $id_agenda     = $_POST['id'];
            $sql = "DELETE FROM agendamentos WHERE id = ".$id_agenda;
            mysqli_query($conexao_bd, $sql) or die("ERR.: ".mysql_error());
           // UPDATE agendamentos SET status = 'Cancelado' WHERE id = ?
       }
       //header("Location: cadastro_agendas.php");
       //exit;
    }
//============================================================ */

/* ============================================================
   FILTROS DE BUSCA
   TODO: Usar estes valores para montar a query no banco
   Exemplo: WHERE data BETWEEN :dataInicio AND :dataFim
            AND (medico_id = :medico OR :medico IS NULL)
            AND (status = :status OR :status IS NULL)
============================================================ */
$filtroPaciente = trim(isset($_GET['paciente']) ? $_GET['paciente'] : '');
$filtroMedico   = trim(isset($_GET['medico'])   ? $_GET['medico']   : '');
$filtroStatus   = trim(isset($_GET['status'])   ? $_GET['status']   : '');
$filtroDataIni  = trim(isset($_GET['data_ini']) ? $_GET['data_ini'] : '');
$filtroDataFim  = trim(isset($_GET['data_fim']) ? $_GET['data_fim'] : '');

/* ============================================================
   AGENDAMENTOS FICTÍCIOS (placeholder para visualização)
   REMOVER QUANDO INTEGRAR COM O BANCO DE DADOS
   TODO: Substituir por:
   $agendamentos = buscarAgendamentos($filtroPaciente, $filtroMedico, $filtroStatus, $filtroDataIni, $filtroDataFim);
============================================================ 
$agendamentos = [
    ['id' =>  1, 'data' => '2026-04-05', 'horario' => '09:00', 'paciente' => 'Maria Souza',     'medico' => 'Dr. Carlos Lima',  'especialidade' => 'Cardiologia',  'status' => 'Confirmado'],
    ['id' =>  2, 'data' => '2026-04-08', 'horario' => '10:30', 'paciente' => 'Carlos Andrade',  'medico' => 'Dra. Ana Paula',   'especialidade' => 'Dermatologia', 'status' => 'Confirmado'],
    ['id' =>  3, 'data' => '2026-04-08', 'horario' => '14:00', 'paciente' => 'Juliana Reis',    'medico' => 'Dr. Pedro Alves',  'especialidade' => 'Ortopedia',    'status' => 'Pendente'],
    ['id' =>  4, 'data' => '2026-04-12', 'horario' => '08:00', 'paciente' => 'Pedro Henrique',  'medico' => 'Dra. Ana Paula',   'especialidade' => 'Dermatologia', 'status' => 'Confirmado'],
    ['id' =>  5, 'data' => '2026-04-15', 'horario' => '11:00', 'paciente' => 'Júlia Mendes',    'medico' => 'Dr. Carlos Lima',  'especialidade' => 'Cardiologia',  'status' => 'Confirmado'],
    ['id' =>  6, 'data' => '2026-04-15', 'horario' => '15:30', 'paciente' => 'Roberto Dias',    'medico' => 'Dr. Pedro Alves',  'especialidade' => 'Ortopedia',    'status' => 'Confirmado'],
    ['id' =>  7, 'data' => '2026-04-15', 'horario' => '16:30', 'paciente' => 'Fernanda Costa',  'medico' => 'Dra. Marina Reis', 'especialidade' => 'Pediatria',    'status' => 'Pendente'],
    ['id' =>  8, 'data' => '2026-04-15', 'horario' => '17:30', 'paciente' => 'Lucas Silva',     'medico' => 'Dr. Carlos Lima',  'especialidade' => 'Cardiologia',  'status' => 'Confirmado'],
    ['id' =>  9, 'data' => '2026-04-20', 'horario' => '09:30', 'paciente' => 'Luiz Henrique',   'medico' => 'Dra. Marina Reis', 'especialidade' => 'Pediatria',    'status' => 'Confirmado'],
    ['id' => 10, 'data' => '2026-04-23', 'horario' => '10:00', 'paciente' => 'Beatriz Ramos',   'medico' => 'Dra. Ana Paula',   'especialidade' => 'Dermatologia', 'status' => 'Pendente'],
    ['id' => 11, 'data' => '2026-04-27', 'horario' => '14:00', 'paciente' => 'Marcos Vinícius', 'medico' => 'Dr. Pedro Alves',  'especialidade' => 'Ortopedia',    'status' => 'Confirmado'],
];*/
$sql = "SELECT * FROM vw_agendamentos";
$result = mysqli_query($conexao_bd, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $agendamentos[] = [
        'id'            => $row['id'],
        'data'          => $row['data'],
        'horario'       => $row['horario'],
        'paciente'      => $row['paciente'],
        'medico'        => $row['medico'],
        'especialidade' => $row['especialidade'],
        'status'        => $row['status']
    ];
}


/* ============================================================
   APLICAÇÃO DOS FILTROS NOS DADOS FICTÍCIOS
   TODO: Remover este bloco ao integrar com o banco —
         a filtragem passará a ser feita diretamente na query SQL
============================================================ */
if ($filtroPaciente !== '' || $filtroMedico !== '' || $filtroStatus !== ''
    || $filtroDataIni !== '' || $filtroDataFim !== '') {

    $agendamentos = array_values(array_filter($agendamentos, function($ag) use (
        $filtroPaciente, $filtroMedico, $filtroStatus, $filtroDataIni, $filtroDataFim
    ) {
        if ($filtroPaciente !== '' && stripos($ag['paciente'], $filtroPaciente) === false) {
            return false;
        }
        if ($filtroMedico !== '' && $ag['medico'] !== $filtroMedico) {
            return false;
        }
        if ($filtroStatus !== '' && $ag['status'] !== $filtroStatus) {
            return false;
        }
        if ($filtroDataIni !== '' && $ag['data'] < $filtroDataIni) {
            return false;
        }
        if ($filtroDataFim !== '' && $ag['data'] > $filtroDataFim) {
            return false;
        }
        return true;
    }));
}

/* ============================================================
   MÉDICOS DISPONÍVEIS
   TODO: Substituir por consulta ao banco:
   $medicos = buscarMedicos();
============================================================ */
$medicos = [
    ['id' => 1, 'nome' => 'Dr. Carlos Lima',  'especialidade' => 'Cardiologia'],
    ['id' => 2, 'nome' => 'Dra. Ana Paula',   'especialidade' => 'Dermatologia'],
    ['id' => 3, 'nome' => 'Dr. Pedro Alves',  'especialidade' => 'Ortopedia'],
    ['id' => 4, 'nome' => 'Dra. Marina Ana Reis', 'especialidade' => 'Pediatria'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediAgenda - Cadastro de Agendas</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">

    <!-- ================ CDNs ================ -->
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- ================ ESTILOS DA APLICAÇÃO ================ -->
    <style>
        :root {
            --azul-primario: #0d6efd;
            --azul-escuro:   #084298;
            --azul-claro:    #e7f1ff;
            --cinza-fundo:   #f5f7fa;
            --cinza-borda:   #e3e6ea;
            --texto-escuro:  #1f2d3d;
            --sidebar-larg:  250px;
        }

        body {
            background-color: var(--cinza-fundo);
            font-family: 'Segoe UI', Tahoma, sans-serif;
            color: var(--texto-escuro);
            overflow-x: hidden;
        }

        /* ==================== NAVBAR SUPERIOR ==================== */
        .navbar-topo {
            background: linear-gradient(90deg, var(--azul-primario) 0%, var(--azul-escuro) 100%);
            height: 60px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1030;
        }
        .navbar-topo .navbar-brand {
            color: #fff;
            font-weight: 600;
            font-size: 1.25rem;
        }
        .navbar-topo .navbar-brand i {
            margin-right: 8px;
        }
        .btn-sanduiche {
            background: transparent;
            border: none;
            color: #fff;
            font-size: 1.3rem;
            padding: 6px 12px;
            border-radius: 6px;
            transition: background 0.2s;
        }
        .btn-sanduiche:hover {
            background: rgba(255,255,255,0.15);
        }
        .operador-toggle {
            background: transparent;
            border: none;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 30px;
            transition: background 0.2s;
        }
        .operador-toggle:hover, .operador-toggle:focus {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }
        .operador-toggle i.fa-circle-user {
            font-size: 1.6rem;
        }
        .dropdown-menu-operador {
            min-width: 220px;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            border: none;
        }
        .dropdown-menu-operador .dropdown-item i {
            width: 22px;
            color: var(--azul-primario);
        }

        /* ==================== SIDEBAR LATERAL ==================== */
        .sidebar {
            position: fixed;
            top: 60px;
            left: 0;
            width: var(--sidebar-larg);
            height: calc(100vh - 60px);
            background: #fff;
            border-right: 1px solid var(--cinza-borda);
            padding: 20px 0;
            transition: transform 0.3s ease;
            z-index: 1020;
            overflow-y: auto;
        }
        .sidebar.oculta {
            transform: translateX(calc(var(--sidebar-larg) * -1));
        }
        .sidebar .nav-link {
            color: var(--texto-escuro);
            padding: 12px 20px;
            border-left: 3px solid transparent;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar .nav-link i {
            width: 22px;
            color: var(--azul-primario);
            font-size: 1.05rem;
        }
        .sidebar .nav-link:hover {
            background: var(--azul-claro);
            border-left-color: var(--azul-primario);
            color: var(--azul-escuro);
        }
        .sidebar .nav-link.ativo {
            background: var(--azul-claro);
            border-left-color: var(--azul-primario);
            color: var(--azul-escuro);
            font-weight: 600;
        }

        /* Overlay (em mobile, escurece o fundo quando sidebar aberta) */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 60px; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.4);
            z-index: 1010;
        }
        .sidebar-overlay.ativo {
            display: block;
        }

        /* ==================== CONTEÚDO PRINCIPAL ==================== */
        .conteudo-principal {
            margin-top: 60px;
            margin-left: var(--sidebar-larg);
            padding: 25px;
            transition: margin-left 0.3s ease;
            min-height: calc(100vh - 60px);
        }
        .conteudo-principal.expandido {
            margin-left: 0;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(calc(var(--sidebar-larg) * -1));
            }
            .sidebar.aberta {
                transform: translateX(0);
                box-shadow: 2px 0 12px rgba(0,0,0,0.15);
            }
            .conteudo-principal {
                margin-left: 0;
            }
        }

        /* ==================== CABEÇALHO DA PÁGINA ==================== */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 22px;
        }
        .page-header h2 {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--azul-escuro);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .page-header h2 i {
            color: var(--azul-primario);
        }

        /* ==================== CARD GENÉRICO ==================== */
        .card-pagina {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid var(--cinza-borda);
            padding: 20px 24px;
            margin-bottom: 20px;
        }
        .card-pagina .card-titulo {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--azul-escuro);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-pagina .card-titulo i {
            color: var(--azul-primario);
        }

        /* ==================== TABELA ==================== */
        .tabela-agendamentos {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.88rem;
        }
        .tabela-agendamentos thead th {
            background: var(--azul-claro);
            color: var(--azul-escuro);
            font-weight: 600;
            padding: 10px 14px;
            border-bottom: 2px solid var(--cinza-borda);
            white-space: nowrap;
        }
        .tabela-agendamentos tbody tr {
            transition: background 0.15s;
        }
        .tabela-agendamentos tbody tr:hover {
            background: #f8fbff;
        }
        .tabela-agendamentos tbody td {
            padding: 10px 14px;
            border-bottom: 1px solid var(--cinza-borda);
            vertical-align: middle;
        }
        .tabela-agendamentos tbody tr:last-child td {
            border-bottom: none;
        }

        /* ==================== BADGES DE STATUS ==================== */
        .badge-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .badge-confirmado {
            background: #d1e7dd;
            color: #0a3622;
        }
        .badge-pendente {
            background: #fff3cd;
            color: #664d03;
        }
        .badge-cancelado {
            background: #f8d7da;
            color: #58151c;
        }

        /* ==================== MODAL ==================== */
        .modal-form .modal-header {
            background: var(--azul-primario);
            color: #fff;
        }
        .modal-form .modal-header .btn-close {
            filter: invert(1);
        }
        .modal-form label {
            font-weight: 500;
            font-size: 0.88rem;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>

    <!-- ==================================================
         NAVBAR SUPERIOR
    ================================================== -->
    <nav class="navbar-topo d-flex align-items-center justify-content-between px-3">
        <div class="d-flex align-items-center gap-2">
            <button class="btn-sanduiche" id="btnSanduiche" title="Menu">
                <i class="fa-solid fa-bars"></i>
            </button>
            <a class="navbar-brand mb-0 d-flex align-items-center" href="principal.php">
                <i class="fa-solid fa-stethoscope"></i>
                <span>MediAgenda</span>
            </a>
        </div>

        <div class="dropdown">
            <button class="operador-toggle" type="button" id="dropdownOperador" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-circle-user"></i>
                <span class="d-none d-md-inline"><?php echo $operadorNome ?></span>
                <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-operador" aria-labelledby="dropdownOperador">
                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-user"></i><?php echo htmlspecialchars($operadorNome) ?></a></li>
                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-envelope"></i><?php echo htmlspecialchars($operadorEmail) ?></a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-gear"></i>Configurações</a></li>
                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-right-from-bracket"></i>Sair</a></li>
            </ul>
        </div>
    </nav>

    <!-- ==================================================
         SIDEBAR LATERAL
    ================================================== -->
    <aside class="sidebar" id="sidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="principal.php"><i class="fa-solid fa-calendar-days"></i> Calendário</a>
            </li>
            <li class="nav-item">
                <a class="nav-link ativo" href="cadastro_agendas.php"><i class="fa-solid fa-calendar-plus"></i> Agendamentos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="cadastro_medicos.php"><i class="fa-solid fa-user-doctor"></i> Cadastro de Médicos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"><i class="fa-solid fa-list-check"></i> Cadastro de Especialidades</a>
            </li>
        </ul>
    </aside>

    <!-- Overlay para mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ==================================================
         CONTEÚDO PRINCIPAL
    ================================================== -->
    <main class="conteudo-principal" id="conteudoPrincipal">

        <!-- Cabeçalho da página -->
        <div class="page-header">
            <h2><i class="fa-solid fa-calendar-days"></i> Cadastro de Agendas</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalFormAgenda">
                <i class="fa-solid fa-plus me-1"></i> Novo Agendamento
            </button>
        </div>

        <!-- ============================================================
             FILTROS DE BUSCA
             TODO: ao submeter, os valores serão enviados via GET e usados
             para filtrar a consulta ao banco de dados
        ============================================================ -->
        <div class="card-pagina">
            <div class="card-titulo"><i class="fa-solid fa-magnifying-glass"></i> Filtros</div>
            <form method="GET" action="cadastro_agendas.php">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="filtroPaciente">Paciente</label>
                        <input type="text" class="form-control form-control-sm" id="filtroPaciente"
                               name="paciente" placeholder="Nome do paciente"
                               value="<?php echo htmlspecialchars($filtroPaciente) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="filtroMedico">Médico</label>
                        <select class="form-select form-select-sm" id="filtroMedico" name="medico">
                            <option value="">Todos</option>
                            <?php foreach ($medicos as $m): ?>
                                <option value="<?php echo htmlspecialchars($m['nome']) ?>"
                                    <?php echo ($filtroMedico === $m['nome']) ? 'selected' : '' ?>>
                                    <?php echo htmlspecialchars($m['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filtroStatus">Status</label>
                        <select class="form-select form-select-sm" id="filtroStatus" name="status">
                            <option value="">Todos</option>
                            <option value="Confirmado" <?php echo ($filtroStatus === 'Confirmado') ? 'selected' : '' ?>>Confirmado</option>
                            <option value="Pendente"   <?php echo ($filtroStatus === 'Pendente')   ? 'selected' : '' ?>>Pendente</option>
                            <option value="Cancelado"  <?php echo ($filtroStatus === 'Cancelado')  ? 'selected' : '' ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filtroDataIni">Data inicial</label>
                        <input type="date" class="form-control form-control-sm" id="filtroDataIni"
                               name="data_ini" value="<?php echo htmlspecialchars($filtroDataIni) ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="filtroDataFim">Data final</label>
                        <input type="date" class="form-control form-control-sm" id="filtroDataFim"
                               name="data_fim" value="<?php echo htmlspecialchars($filtroDataFim) ?>">
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Filtrar
                    </button>
                    <a href="cadastro_agendas.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-xmark me-1"></i> Limpar
                    </a>
                </div>
            </form>
        </div>

        <!-- ============================================================
             TABELA DE AGENDAMENTOS
             TODO: os dados virão do banco — $agendamentos será o resultado
             da query filtrada. A paginação também será implementada aqui.
        ============================================================ -->
        <div class="card-pagina">
            <div class="card-titulo d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-table-list"></i> Agendamentos</span>
                <!-- TODO: exibir total real vindo do banco -->
                <span id="contadorRegistros" class="text-muted" style="font-size:0.82rem; font-weight:400;">
                    <?php echo count($agendamentos) ?> registro(s) encontrado(s)
                </span>
            </div>

            <div class="table-responsive">
                <table class="tabela-agendamentos">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Data</th>
                            <th>Horário</th>
                            <th>Paciente</th>
                            <th>Médico</th>
                            <th>Especialidade</th>
                            <th>Status</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($agendamentos)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fa-solid fa-calendar-xmark me-2"></i>Nenhum agendamento encontrado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($agendamentos as $ag):
                                // Formata data de YYYY-MM-DD para DD/MM/YYYY
                                $dataFormatada = date('d/m/Y', strtotime($ag['data']));

                                // Define classe do badge conforme status
                                if ($ag['status'] === 'Confirmado') {
                                    $classeBadge = 'badge-confirmado';
                                } elseif ($ag['status'] === 'Pendente') {
                                    $classeBadge = 'badge-pendente';
                                } else {
                                    $classeBadge = 'badge-cancelado';
                                }
                            ?>
                            <tr>
                                <td class="text-muted"><?php echo $ag['id'] ?></td>
                                <td><?php echo $dataFormatada ?></td>
                                <td><?php echo htmlspecialchars($ag['horario']) ?></td>
                                <td><?php echo htmlspecialchars($ag['paciente']) ?></td>
                                <td><?php echo htmlspecialchars($ag['medico']) ?></td>
                                <td><?php echo htmlspecialchars($ag['especialidade']) ?></td>
                                <td><span class="badge-status <?php echo $classeBadge ?>"><?php echo htmlspecialchars($ag['status']) ?></span></td>
                                <td class="text-center" style="white-space:nowrap;">
                                    <!-- TODO: passar dados reais para o modal de edição -->
                                    <button class="btn btn-sm btn-outline-primary py-0 px-2 btn-editar"
                                            title="Editar"
                                            data-id="<?php echo $ag['id'] ?>"
                                            data-paciente="<?php echo htmlspecialchars($ag['paciente']) ?>"
                                            data-medico="<?php echo htmlspecialchars($ag['medico']) ?>"
                                            data-especialidade="<?php echo htmlspecialchars($ag['especialidade']) ?>"
                                            data-data="<?php echo $ag['data'] ?>"
                                            data-horario="<?php echo htmlspecialchars($ag['horario']) ?>"
                                            data-status="<?php echo htmlspecialchars($ag['status']) ?>">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <!-- TODO: confirmar e enviar POST acao=cancelar&id=X -->
                                    <button class="btn btn-sm btn-outline-danger py-0 px-2 btn-cancelar"
                                            title="Cancelar agendamento"
                                            data-id="<?php echo $ag['id'] ?>"
                                            data-paciente="<?php echo htmlspecialchars($ag['paciente']) ?>">
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ============================================================
                 PAGINAÇÃO
                 TODO: implementar após integrar com o banco.
                 Variáveis necessárias: $paginaAtual, $totalPaginas
                 Exemplo: ?paciente=X&status=Y&pagina=2
            ============================================================ -->
            <div class="d-flex justify-content-end mt-3">
                <nav aria-label="Paginação">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item disabled"><a class="page-link" href="#">&raquo;</a></li>
                    </ul>
                </nav>
            </div>
        </div>

    </main>

    <!-- ==================================================
         MODAL — NOVO / EDITAR AGENDAMENTO
         TODO: ao confirmar, submeter o formulário via POST
               com acao='novo' ou acao='editar'
    ================================================== -->
    <div class="modal fade modal-form" id="modalFormAgenda" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFormTitulo">
                        <i class="fa-solid fa-calendar-plus me-2"></i>Novo Agendamento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <!-- TODO: action="cadastro_agendas.php" method="POST" ao integrar com banco -->
                <form id="formAgenda" action="cadastro_agendas.php" method="POST"> 
                    <input type="hidden" name="acao" id="formAcao" value="novo">
                    <input type="hidden" name="id"   id="formId"   value="">

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="formPaciente">Paciente <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="formPaciente" 
                                    name="paciente"
                                    placeholder="Nome completo do paciente" required>
                                <!-- TODO: substituir por autocomplete buscando pacientes no banco -->
                            </div>
                            <div class="col-md-6">
                                <label for="formMedico">Médico <span class="text-danger">*</span></label>
                                <select class="form-select" id="formMedico" 
                                 name="medico_id" required>
                                    <option value="">Selecione...</option>
                                    <?php foreach ($medicos as $m): ?>
                                        <option value="<?php echo $m['id'] ?>"><?php echo htmlspecialchars($m['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="formEspecialidade">Especialidade <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" 
                                id="formEspecialidade"
                                       name="especialidade" placeholder="Ex: Cardiologia" required>
                                <!-- TODO: preencher automaticamente ao selecionar o médico -->
                            </div>
                            <div class="col-md-6">
                                <label for="formData">Data <span class="text-danger">*</span></label>
                                <input type="date" class="form-control"  
                                 id="formData" name="data" required>
                            </div>
                            <div class="col-md-6">
                                <label for="formHorario">Horário <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="formHorario" 
                                name="horario" required>
                            </div>
                            <div class="col-12">
                                <label for="formStatus">Status</label>
                                <select class="form-select" id="formStatus" name="status">
                                    <option value="Pendente">Pendente</option>
                                    <option value="Confirmado">Confirmado</option>
                                    <option value="Cancelado">Cancelado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <!-- TODO: mudar para type="submit" ao integrar com banco -->
                        <button type="submit" class="btn btn-primary" onclick="salvarAgendamento()">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Salvar
                        </button>
                    </div>
                </form>t
            </div>
        </div>
    </div>

    <!-- ================ SCRIPTS ================ -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        // ==================================================
        // TOGGLE DA SIDEBAR (responsivo)
        // ==================================================
        var btnSanduiche      = document.getElementById('btnSanduiche');
        var sidebar           = document.getElementById('sidebar');
        var conteudoPrincipal = document.getElementById('conteudoPrincipal');
        var sidebarOverlay    = document.getElementById('sidebarOverlay');

        btnSanduiche.addEventListener('click', function() {
            if (window.innerWidth <= 991.98) {
                sidebar.classList.toggle('aberta');
                sidebarOverlay.classList.toggle('ativo');
            } else {
                sidebar.classList.toggle('oculta');
                conteudoPrincipal.classList.toggle('expandido');
            }
        });
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('aberta');
            sidebarOverlay.classList.remove('ativo');
        });
        window.addEventListener('resize', function() {
            if (window.innerWidth > 991.98) {
                sidebar.classList.remove('aberta');
                sidebarOverlay.classList.remove('ativo');
            }
        });

        // ==================================================
        // INSTÂNCIA ÚNICA DO MODAL E FLAG DE MODO
        // ==================================================
        var modalFormAgendaEl = document.getElementById('modalFormAgenda');
        var modalFormAgenda   = new bootstrap.Modal(modalFormAgendaEl);
        var modoEdicao        = false;

        // Reseta o formulário apenas quando aberto no modo "Novo"
        modalFormAgendaEl.addEventListener('show.bs.modal', function() {
            if (!modoEdicao) {
                document.getElementById('modalFormTitulo').innerHTML =
                    '<i class="fa-solid fa-calendar-plus me-2"></i>Novo Agendamento';
                document.getElementById('formAcao').value = 'novo';
                document.getElementById('formId').value   = '';
                document.getElementById('formAgenda').reset();
            }
            modoEdicao = false;
        });

        // ==================================================
        // EVENT DELEGATION — Editar e Cancelar (cobre linhas dinâmicas)
        // ==================================================
        document.querySelector('.tabela-agendamentos').addEventListener('click', function(e) {
            var btnEditar   = e.target.closest('.btn-editar');
            var btnCancelar = e.target.closest('.btn-cancelar');
           
            if (btnEditar) {
                modoEdicao = true;
                document.getElementById('modalFormTitulo').innerHTML =
                    '<i class="fa-solid fa-pen me-2"></i>Editar Agendamento';
                document.getElementById('formAcao').value          = 'editar';
                document.getElementById('formId').value            = btnEditar.dataset.id;
                document.getElementById('formPaciente').value      = btnEditar.dataset.paciente;
                document.getElementById('formData').value          = btnEditar.dataset.data;
                document.getElementById('formHorario').value       = btnEditar.dataset.horario;
                document.getElementById('formEspecialidade').value = btnEditar.dataset.especialidade;
                document.getElementById('formStatus').value        = btnEditar.dataset.status;

                var sel = document.getElementById('formMedico');
                for (var i = 0; i < sel.options.length; i++) {
                    if (sel.options[i].text === btnEditar.dataset.medico) {
                        sel.selectedIndex = i;
                        break;
                    }
                }
                modalFormAgenda.show();
            }

            if (btnCancelar) {
                Swal.fire({
                    title: 'Cancelar agendamento?',
                    html: 'Deseja cancelar o agendamento de <strong>' + btnCancelar.dataset.paciente + '</strong>?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor:  '#6c757d',
                    confirmButtonText:  'Sim, cancelar',
                    cancelButtonText:   'Voltar'
                }).then(function(result) {
                    
                    var var_acao = "cancelar";
                    var id_agenda = btnCancelar.dataset.id;

                    if (result.isConfirmed) {
                        // TODO: substituir pelo envio real ao banco
                        bodyContent = $.ajax({
                            url: "cadastro_agendas.php",
                            global: false,
                            type: "POST",
                            data: ({id: id_agenda, acao: var_acao}),
                            dataType: "html",
                            async:false,
                            success: function(msg){
                                return msg;
                            }
                        }).responseText;
                        //btnCancelar.closest('tr').remove();
                        //atualizarContadorAgenda();
                        Swal.fire({
                            icon: 'success',
                            title: 'Cancelado!',
                            text: 'O agendamento foi cancelado.',
                            confirmButtonColor: '#0d6efd',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        window.location.href = "cadastro_agendas.php";
                    }
                });
            }
        });

        // ==================================================
        // FUNÇÃO PRINCIPAL: salvar agendamento
        // TODO: substituir o corpo por fetch/AJAX ao integrar com o banco
        // ==================================================
        function salvarAgendamento() {
            var form = document.getElementById('formAgenda');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            /*                            
            var acao          = document.getElementById('formAcao').value;
            var id            = document.getElementById('formId').value;
            var paciente      = document.getElementById('formPaciente').value.trim();
            var medicoSel     = document.getElementById('formMedico');
            var medico        = medicoSel.options[medicoSel.selectedIndex].text;
            var especialidade = document.getElementById('formEspecialidade').value.trim();
            var dataISO       = document.getElementById('formData').value;
            var horario       = document.getElementById('formHorario').value;
            var status        = document.getElementById('formStatus').value;
            var partes        = dataISO.split('-');
            var dataFmt       = partes[2] + '/' + partes[1] + '/' + partes[0];

            if (acao === 'editar') {
                var btnEditar = document.querySelector('.btn-editar[data-id="' + id + '"]');
                if (btnEditar) {
                    var tr = btnEditar.closest('tr');
                    tr.cells[1].textContent = dataFmt;
                    tr.cells[2].textContent = horario;
                    tr.cells[3].textContent = paciente;
                    tr.cells[4].textContent = medico;
                    tr.cells[5].textContent = especialidade;
                    tr.cells[6].innerHTML   = '<span class="badge-status ' + getBadgeClassAgenda(status) + '">' + status + '</span>';

                    btnEditar.dataset.paciente      = paciente;
                    btnEditar.dataset.medico        = medico;
                    btnEditar.dataset.especialidade = especialidade;
                    btnEditar.dataset.data          = dataISO;
                    btnEditar.dataset.horario       = horario;
                    btnEditar.dataset.status        = status;

                    var btnCancelar = tr.querySelector('.btn-cancelar');
                    if (btnCancelar) btnCancelar.dataset.paciente = paciente;
                }
            } else {
                var tbody    = document.querySelector('.tabela-agendamentos tbody');
                var semDados = tbody.querySelector('td[colspan]');
                if (semDados) semDados.closest('tr').remove();

                var novoId = 'tmp-' + Date.now();
                tbody.appendChild(criarLinhaAgendamento(novoId, dataFmt, horario, paciente, medico, especialidade, status, dataISO));
                atualizarContadorAgenda();
            }
            */
            modalFormAgenda.hide();
            Swal.fire({
                icon: 'success',
                title: 'Salvo!',
                text: acao === 'editar' ? 'Agendamento atualizado com sucesso.' : 'Agendamento cadastrado com sucesso.',
                confirmButtonColor: '#0d6efd',
                timer: 2000,
                showConfirmButton: false
            });
        }

        // Cria um <tr> completo para a tabela de agendamentos
        function criarLinhaAgendamento(id, dataFmt, horario, paciente, medico, especialidade, status, dataISO) {
            var tr = document.createElement('tr');

            var tdId = document.createElement('td'); tdId.className = 'text-muted'; tdId.textContent = '—';
            var tdDt = document.createElement('td'); tdDt.textContent = dataFmt;
            var tdHr = document.createElement('td'); tdHr.textContent = horario;
            var tdPa = document.createElement('td'); tdPa.textContent = paciente;
            var tdMe = document.createElement('td'); tdMe.textContent = medico;
            var tdEs = document.createElement('td'); tdEs.textContent = especialidade;

            var tdSt  = document.createElement('td');
            var badge = document.createElement('span');
            badge.className   = 'badge-status ' + getBadgeClassAgenda(status);
            badge.textContent = status;
            tdSt.appendChild(badge);

            var tdAc = document.createElement('td');
            tdAc.className        = 'text-center';
            tdAc.style.whiteSpace = 'nowrap';

            var btnEdit = document.createElement('button');
            btnEdit.className              = 'btn btn-sm btn-outline-primary py-0 px-2 btn-editar';
            btnEdit.title                  = 'Editar';
            btnEdit.innerHTML              = '<i class="fa-solid fa-pen"></i>';
            btnEdit.dataset.id             = id;
            btnEdit.dataset.paciente       = paciente;
            btnEdit.dataset.medico         = medico;
            btnEdit.dataset.especialidade  = especialidade;
            btnEdit.dataset.data           = dataISO;
            btnEdit.dataset.horario        = horario;
            btnEdit.dataset.status         = status;

            var btnCan = document.createElement('button');
            btnCan.className        = 'btn btn-sm btn-outline-danger py-0 px-2 btn-cancelar';
            btnCan.title            = 'Cancelar agendamento';
            btnCan.innerHTML        = '<i class="fa-solid fa-ban"></i>';
            btnCan.dataset.id       = id;
            btnCan.dataset.paciente = paciente;

            tdAc.appendChild(btnEdit);
            tdAc.appendChild(btnCan);
            tr.appendChild(tdId); tr.appendChild(tdDt); tr.appendChild(tdHr);
            tr.appendChild(tdPa); tr.appendChild(tdMe); tr.appendChild(tdEs);
            tr.appendChild(tdSt); tr.appendChild(tdAc);
            return tr;
        }

        // Retorna a classe CSS do badge de status
        function getBadgeClassAgenda(status) {
            if (status === 'Confirmado') return 'badge-confirmado';
            if (status === 'Pendente')   return 'badge-pendente';
            return 'badge-cancelado';
        }

        // Atualiza o texto do contador de registros
        function atualizarContadorAgenda() {
            var tbody  = document.querySelector('.tabela-agendamentos tbody');
            var linhas = tbody.rows;
            var total  = 0;
            for (var i = 0; i < linhas.length; i++) {
                if (!linhas[i].querySelector('td[colspan]')) total++;
            }
            if (tbody.rows.length === 0) {
                var tr = document.createElement('tr');
                var td = document.createElement('td');
                td.setAttribute('colspan', '8');
                td.className = 'text-center text-muted py-4';
                td.innerHTML = '<i class="fa-solid fa-calendar-xmark me-2"></i>Nenhum agendamento encontrado.';
                tr.appendChild(td);
                tbody.appendChild(tr);
            }
            var el = document.getElementById('contadorRegistros');
            if (el) el.textContent = total + ' registro(s) encontrado(s)';
        }
    </script>
</body>
</html>
