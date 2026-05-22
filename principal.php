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
   principal.php - Dashboard de Agendamento de Consultas Médicas
   ------------------------------------------------------------
   TODO: Adicionar validação de sessão aqui (após implementar login)
   Ex:
   session_start();
   if (!isset($_SESSION['operador'])) {
       header("Location: login.php");
       exit;
   }
============================================================ */

/* ============================================================
   DADOS DO OPERADOR LOGADO
   TODO: Substituir pelos dados vindos da $_SESSION
============================================================ */
$operadorNome  = $nomeUsuario; //"Dr. João Silva";
$operadorEmail = $emailUsuario; //"joao.silva@clinica.com";

/* ============================================================
   DADOS DO MÊS ATUAL (cálculo do calendário)
============================================================ */
$mesAtual    = isset($_GET['mes']) ? max(1, min(12, (int)$_GET['mes'])) : (int)date('n');
$anoAtual    = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');
$nomesMeses  = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
$nomeMes     = $nomesMeses[$mesAtual];
$primeiroDia = mktime(0, 0, 0, $mesAtual, 1, $anoAtual);
$diaSemanaInicio = (int)date('w', $primeiroDia); // 0=Dom ... 6=Sáb
$totalDias   = (int)date('t', $primeiroDia);
$diaHoje     = (int)date('j');
$mesHoje     = (int)date('n');
$anoHoje     = (int)date('Y');

// Mês anterior
$mesAnterior = $mesAtual - 1;
$anoAnterior = $anoAtual;
if ($mesAnterior < 1) { $mesAnterior = 12; $anoAnterior--; }

// Próximo mês
$proximoMes = $mesAtual + 1;
$proximoAno = $anoAtual;
if ($proximoMes > 12) { $proximoMes = 1; $proximoAno++; }

/* ============================================================
   AGENDAMENTOS FICTÍCIOS (placeholder para visualização)
   REMOVER QUANDO INTEGRAR COM O BANCO DE DADOS
   Estrutura esperada: chave = dia do mês, valor = array de agendamentos
============================================================ */
$sql = "select *, DAY(data) diaAgenda from vw_agendamentos where MONTH(data) = $mesAtual AND YEAR(data) = $anoAtual";
$result = mysqli_query($conexao_bd,$sql);
while($row = $result->fetch_assoc()){
    //echo ">>>" . $row["paciente"]." | ". $row["data"] . " | " . $row["diaAgenda"] . "<br>";
    $agendamentosFicticios[$row["diaAgenda"]][] = [
        'id'            => $row["id"],
        'horario'       => date("H:i", strtotime($row["horario"])),
        'paciente'      => $row["paciente"],
        'medico'        => $row["medico"],
        'especialidade' => $row["especialidade"],
        'status'        => $row["status"]
    ];
}
/*
$agendamentosFicticios = [
    5  => [
        ['id' => 1, 'horario' => '09:00', 'paciente' => 'Maria Souza',     'medico' => 'Dr. Carlos Lima',  'especialidade' => 'Cardiologia',  'status' => 'Confirmado'],
    ],
    8  => [
        ['id' => 2, 'horario' => '10:30', 'paciente' => 'Carlos Andrade',  'medico' => 'Dra. Ana Paula',   'especialidade' => 'Dermatologia', 'status' => 'Confirmado'],
        ['id' => 3, 'horario' => '14:00', 'paciente' => 'Juliana Reis',    'medico' => 'Dr. Pedro Alves',  'especialidade' => 'Ortopedia',    'status' => 'Pendente'],
    ],
    12 => [
        ['id' => 4, 'horario' => '08:00', 'paciente' => 'Pedro Henrique',  'medico' => 'Dra. Ana Paula',   'especialidade' => 'Dermatologia', 'status' => 'Confirmado'],
    ],
    15 => [
        ['id' => 5, 'horario' => '11:00', 'paciente' => 'Júlia Mendes',    'medico' => 'Dr. Carlos Lima',  'especialidade' => 'Cardiologia',  'status' => 'Confirmado'],
        ['id' => 6, 'horario' => '15:30', 'paciente' => 'Roberto Dias',    'medico' => 'Dr. Pedro Alves',  'especialidade' => 'Ortopedia',    'status' => 'Confirmado'],
        ['id' => 7, 'horario' => '16:30', 'paciente' => 'Fernanda Costa',  'medico' => 'Dra. Marina Reis', 'especialidade' => 'Pediatria',    'status' => 'Pendente'],
        ['id' => 8, 'horario' => '17:30', 'paciente' => 'Lucas Silva',     'medico' => 'Dr. Carlos Lima',  'especialidade' => 'Cardiologia',  'status' => 'Confirmado'],
    ],
    20 => [
        ['id' => 9, 'horario' => '09:30', 'paciente' => 'Luiz Henrique',   'medico' => 'Dra. Marina Reis', 'especialidade' => 'Pediatria',    'status' => 'Confirmado'],
    ],
    23 => [
        ['id' => 10,'horario' => '10:00', 'paciente' => 'Beatriz Ramos',   'medico' => 'Dra. Ana Paula',   'especialidade' => 'Dermatologia', 'status' => 'Pendente'],
    ],
    27 => [
        ['id' => 11,'horario' => '14:00', 'paciente' => 'Marcos Vinícius', 'medico' => 'Dr. Pedro Alves',  'especialidade' => 'Ortopedia',    'status' => 'Confirmado'],
    ],
];*/
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediAgenda - Painel Principal</title>

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

        /* Em telas pequenas, sidebar vira overlay e conteúdo ocupa tela toda */
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

        /* ==================== CALENDÁRIO ==================== */
        .card-calendario {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid var(--cinza-borda);
            overflow: hidden;
        }
        .calendario-cabecalho {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 22px;
            border-bottom: 1px solid var(--cinza-borda);
            flex-wrap: wrap;
            gap: 10px;
        }
        .calendario-cabecalho h4 {
            margin: 0;
            color: var(--azul-escuro);
            font-weight: 600;
            text-transform: capitalize;
        }
        .calendario-cabecalho .btn-nav {
            border: 1px solid var(--cinza-borda);
            background: #fff;
            color: var(--texto-escuro);
            padding: 6px 12px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .calendario-cabecalho .btn-nav:hover {
            background: var(--azul-claro);
            color: var(--azul-primario);
            border-color: var(--azul-primario);
        }

        .calendario-grade {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            background: var(--cinza-borda);
            gap: 1px;
        }
        .calendario-grade .dia-semana {
            background: #fafbfc;
            text-align: center;
            padding: 10px 4px;
            font-weight: 600;
            font-size: 0.85rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .calendario-grade .dia {
            background: #fff;
            min-height: 120px;
            padding: 8px;
            position: relative;
            transition: background 0.15s;
            display: flex;
            flex-direction: column;
        }
        .calendario-grade .dia:hover {
            background: #fafbfc;
        }
        .calendario-grade .dia.vazio {
            background: #f8f9fa;
        }
        .calendario-grade .dia .numero {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--texto-escuro);
            margin-bottom: 4px;
        }
        .calendario-grade .dia.hoje .numero {
            background: var(--azul-primario);
            color: #fff;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Card de agendamento dentro do dia */
        .card-agendamento {
            background: var(--azul-claro);
            border-left: 3px solid var(--azul-primario);
            border-radius: 4px;
            padding: 4px 6px;
            margin-bottom: 3px;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.15s;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        .card-agendamento:hover {
            background: var(--azul-primario);
            color: #fff;
            transform: translateX(2px);
        }
        .card-agendamento .horario {
            font-weight: 600;
        }
        .card-agendamento .paciente {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .link-mais {
            font-size: 0.72rem;
            color: var(--azul-primario);
            cursor: pointer;
            font-weight: 600;
            margin-top: 2px;
        }
        .link-mais:hover {
            text-decoration: underline;
        }

        /* ==================== MODAL ==================== */
        .modal-detalhe .modal-header {
            background: var(--azul-primario);
            color: #fff;
        }
        .modal-detalhe .modal-header .btn-close {
            filter: invert(1);
        }
        .modal-detalhe .info-item {
            padding: 10px 0;
            border-bottom: 1px solid var(--cinza-borda);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .modal-detalhe .info-item:last-child {
            border-bottom: none;
        }
        .modal-detalhe .info-item i {
            color: var(--azul-primario);
            width: 22px;
            font-size: 1.05rem;
        }
        .modal-detalhe .info-item strong {
            color: #6c757d;
            font-weight: 500;
            margin-right: 8px;
        }
    </style>
</head>
<body>

    <!-- ==================================================
         NAVBAR SUPERIOR
    ================================================== -->
    <nav class="navbar-topo d-flex align-items-center justify-content-between px-3">
        <!-- Lado esquerdo: sanduíche + logo + título -->
        <div class="d-flex align-items-center gap-2">
            <button class="btn-sanduiche" id="btnSanduiche" title="Menu">
                <i class="fa-solid fa-bars"></i>
            </button>
            <a class="navbar-brand mb-0 d-flex align-items-center" href="#">
                <i class="fa-solid fa-stethoscope"></i>
                <span>MediAgenda</span>
            </a>
        </div>

        <!-- Lado direito: dropdown do operador -->
        <div class="dropdown">
            <button class="operador-toggle" type="button" id="dropdownOperador" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-circle-user"></i>
                <span class="d-none d-md-inline"><?php echo($operadorNome); ?></span>
                <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-operador" aria-labelledby="dropdownOperador">
                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-user"></i><?php echo htmlspecialchars($operadorNome) ?></a></li>
                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-envelope"></i><?php echo htmlspecialchars($operadorEmail) ?></a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-gear"></i>Configurações</a></li>
                <li><a class="dropdown-item" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sair</a></li>
            </ul>
        </div>
    </nav>

    <!-- ==================================================
         SIDEBAR LATERAL
    ================================================== -->
    <aside class="sidebar" id="sidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link ativo" href="principal.php"><i class="fa-solid fa-calendar-days"></i> Calendário</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="cadastro_agendas.php"><i class="fa-solid fa-calendar-plus"></i> Agendamentos</a>
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
         CONTEÚDO PRINCIPAL - CALENDÁRIO
    ================================================== -->
    <main class="conteudo-principal" id="conteudoPrincipal">

        <div class="card-calendario">

            <!-- Cabeçalho do calendário com navegação -->
            <div class="calendario-cabecalho">
                <h4><?php echo $nomeMes ?> <?php echo $anoAtual ?></h4>
                <div class="d-flex gap-2">
                    <a class="btn-nav" href="?mes=<?php echo $mesAnterior ?>&amp;ano=<?php echo $anoAnterior ?>" title="Mês anterior"><i class="fa-solid fa-chevron-left"></i></a>
                    <a class="btn-nav" href="?" title="Hoje">Hoje</a>
                    <a class="btn-nav" href="?mes=<?php echo $proximoMes ?>&amp;ano=<?php echo $proximoAno ?>" title="Próximo mês"><i class="fa-solid fa-chevron-right"></i></a>
                </div>
            </div>

            <!-- Grade do calendário -->
            <div class="calendario-grade">
                <!-- Cabeçalho dos dias da semana -->
                <div class="dia-semana">Dom</div>
                <div class="dia-semana">Seg</div>
                <div class="dia-semana">Ter</div>
                <div class="dia-semana">Qua</div>
                <div class="dia-semana">Qui</div>
                <div class="dia-semana">Sex</div>
                <div class="dia-semana">Sáb</div>

                <?php
                // Células vazias antes do dia 1 (para alinhar ao dia da semana correto)
                for ($i = 0; $i < $diaSemanaInicio; $i++) {
                    echo '<div class="dia vazio"></div>';
                }

                // Loop pelos dias do mês
                for ($dia = 1; $dia <= $totalDias; $dia++) {
                    $classeHoje = ($dia === $diaHoje && $mesAtual === $mesHoje && $anoAtual === $anoHoje) ? 'hoje' : '';
                    ?>
                    <div class="dia <?php echo $classeHoje ?>">
                        <span class="numero"><?php echo $dia ?></span>

                        <?php
                        /* ============================================================
                           PONTO DE INTEGRAÇÃO COM O BANCO DE DADOS
                           ------------------------------------------------------------
                           TODO: Substituir o array fictício abaixo por uma consulta real.
                           Exemplo de implementação futura:

                           $agendamentosDoDia = buscarAgendamentosDoDia($dia, $mesAtual, $anoAtual);

                           A função deve retornar um array no formato:
                           [
                               [
                                   'id'            => int,
                                   'horario'       => 'HH:MM',
                                   'paciente'      => string,
                                   'medico'        => string,
                                   'especialidade' => string,
                                   'status'        => string
                               ],
                               ...
                           ]
                        ============================================================ */
                        $agendamentosDoDia = isset($agendamentosFicticios[$dia]) ? $agendamentosFicticios[$dia] : array();

                        // Limita exibição a 3 cards; o restante vira "+N mais"
                        $maxExibir  = 3;
                        $totalAgend = count($agendamentosDoDia);
                        $exibir     = array_slice($agendamentosDoDia, 0, $maxExibir);

                        foreach ($exibir as $agend):
                        ?>
                            <!-- ====== Template do card de agendamento (clicável → modal) ====== -->
                            <div class="card-agendamento"
                                 data-id="<?php echo $agend['id'] ?>"
                                 data-horario="<?php echo htmlspecialchars($agend['horario']) ?>"
                                 data-paciente="<?php echo htmlspecialchars($agend['paciente']) ?>"
                                 data-medico="<?php echo htmlspecialchars($agend['medico']) ?>"
                                 data-especialidade="<?php echo htmlspecialchars($agend['especialidade']) ?>"
                                 data-status="<?php echo htmlspecialchars($agend['status']) ?>"
                                 data-data="<?php echo sprintf('%02d/%02d/%d', $dia, $mesAtual, $anoAtual) ?>">
                                <span class="horario"><?php echo htmlspecialchars($agend['horario']) ?></span>
                                <span class="paciente"><?php echo htmlspecialchars($agend['paciente']) ?></span>
                            </div>
                        <?php endforeach; ?>

                        <?php if ($totalAgend > $maxExibir): ?>
                            <span class="link-mais">+ <?php echo $totalAgend - $maxExibir ?> mais</span>
                        <?php endif; ?>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

    </main>

    <!-- ==================================================
         MODAL DE DETALHES DO AGENDAMENTO
    ================================================== -->
    <div class="modal fade modal-detalhe" id="modalAgendamento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-calendar-check me-2"></i>Detalhes do Agendamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="info-item">
                        <i class="fa-solid fa-user"></i>
                        <div><strong>Paciente:</strong> <span id="modalPaciente"></span></div>
                    </div>
                    <div class="info-item">
                        <i class="fa-solid fa-user-doctor"></i>
                        <div><strong>Médico:</strong> <span id="modalMedico"></span></div>
                    </div>
                    <div class="info-item">
                        <i class="fa-solid fa-stethoscope"></i>
                        <div><strong>Especialidade:</strong> <span id="modalEspecialidade"></span></div>
                    </div>
                    <div class="info-item">
                        <i class="fa-solid fa-calendar"></i>
                        <div><strong>Data:</strong> <span id="modalData"></span></div>
                    </div>
                    <div class="info-item">
                        <i class="fa-solid fa-clock"></i>
                        <div><strong>Horário:</strong> <span id="modalHorario"></span></div>
                    </div>
                    <div class="info-item">
                        <i class="fa-solid fa-circle-info"></i>
                        <div><strong>Status:</strong> <span id="modalStatus"></span></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="btnCancelarAgendamento">
                        <i class="fa-solid fa-ban me-1"></i> Cancelar Agendamento
                    </button>
                    <!-- TODO: implementar ação de editar -->
                    <button type="button" class="btn btn-primary"><i class="fa-solid fa-pen me-1"></i> Editar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================ SCRIPTS ================ -->
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // ==================================================
        // TOGGLE DA SIDEBAR (responsivo)
        // ==================================================
        const btnSanduiche      = document.getElementById('btnSanduiche');
        const sidebar           = document.getElementById('sidebar');
        const conteudoPrincipal = document.getElementById('conteudoPrincipal');
        const sidebarOverlay    = document.getElementById('sidebarOverlay');

        btnSanduiche.addEventListener('click', () => {
            if (window.innerWidth <= 991.98) {
                // Mobile: usa overlay
                sidebar.classList.toggle('aberta');
                sidebarOverlay.classList.toggle('ativo');
            } else {
                // Desktop: oculta/mostra a sidebar e expande/contrai o conteúdo
                sidebar.classList.toggle('oculta');
                conteudoPrincipal.classList.toggle('expandido');
            }
        });

        // Clicar no overlay (mobile) fecha a sidebar
        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('aberta');
            sidebarOverlay.classList.remove('ativo');
        });

        // Ao redimensionar, limpa estados que não fazem sentido no novo layout
        window.addEventListener('resize', () => {
            if (window.innerWidth > 991.98) {
                sidebar.classList.remove('aberta');
                sidebarOverlay.classList.remove('ativo');
            }
        });

        // ==================================================
        // CLIQUE NO CARD DE AGENDAMENTO → ABRE MODAL
        // ==================================================
        var modalAgendamento  = new bootstrap.Modal(document.getElementById('modalAgendamento'));
        var agendamentoAtual  = { id: null, paciente: null, data: null, horario: null };

        document.querySelectorAll('.card-agendamento').forEach(function(card) {
            card.addEventListener('click', function() {
                // Guarda os dados do agendamento selecionado para uso no cancelamento
                agendamentoAtual.id       = card.dataset.id;
                agendamentoAtual.paciente = card.dataset.paciente;
                agendamentoAtual.data     = card.dataset.data;
                agendamentoAtual.horario  = card.dataset.horario;

                document.getElementById('modalPaciente').textContent      = card.dataset.paciente;
                document.getElementById('modalMedico').textContent        = card.dataset.medico;
                document.getElementById('modalEspecialidade').textContent = card.dataset.especialidade;
                document.getElementById('modalData').textContent          = card.dataset.data;
                document.getElementById('modalHorario').textContent       = card.dataset.horario;
                document.getElementById('modalStatus').textContent        = card.dataset.status;
                modalAgendamento.show();
            });
        });

        // ==================================================
        // CANCELAR AGENDAMENTO — confirmação via SweetAlert2
        // ==================================================
        document.getElementById('btnCancelarAgendamento').addEventListener('click', function() {
            Swal.fire({
                title: 'Cancelar agendamento?',
                html:  'Deseja cancelar o agendamento de <strong>' + agendamentoAtual.paciente + '</strong>' +
                       '<br>Data: ' + agendamentoAtual.data + ' às ' + agendamentoAtual.horario + '?',
                icon: 'warning',
                showCancelButton:   true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor:  '#6c757d',
                confirmButtonText:  'Sim, cancelar',
                cancelButtonText:   'Voltar'
            }).then(function(result) {
                if (result.isConfirmed) {

                    fetch('cancelar_agendamento.php', {
                        method:  'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body:    'id=' + agendamentoAtual.id
                    })
                    .then(function(response) { return response.json(); })
                    .then(function(dados) {
                        if (!dados.sucesso) {
                            Swal.fire({
                                icon:               'error',
                                title:              'Erro',
                                text:               dados.mensagem || 'Não foi possível cancelar o agendamento.',
                                confirmButtonColor: '#0d6efd'
                            });
                            return;
                        }

                        // Remove o card do calendário
                        var card = document.querySelector('.card-agendamento[data-id="' + agendamentoAtual.id + '"]');
                        if (card) {
                            card.remove();
                        }

                        modalAgendamento.hide();

                        Swal.fire({
                            icon:               'success',
                            title:              'Cancelado!',
                            text:               'O agendamento foi cancelado com sucesso.',
                            confirmButtonColor: '#0d6efd',
                            timer:              2000,
                            showConfirmButton:  false
                        }).then(function() {
                            window.location.reload();
                        });
                    })
                    .catch(function() {
                        Swal.fire({
                            icon:               'error',
                            title:              'Erro de comunicação',
                            text:               'Não foi possível conectar ao servidor. Tente novamente.',
                            confirmButtonColor: '#0d6efd'
                        });
                    });
                }
            });
        });

        // ==================================================
        // TODO: clique no "+ N mais" → abrir modal listando todos os agendamentos do dia
        // (por enquanto exibe apenas um SweetAlert2 informativo)
        // ==================================================
        document.querySelectorAll('.link-mais').forEach(function(link) {
            link.addEventListener('click', function() {
                Swal.fire({
                    icon:               'info',
                    title:              'Em breve',
                    text:               'Aqui será exibida a lista completa de agendamentos do dia.',
                    confirmButtonColor: '#0d6efd'
                });
            });
        });
    </script>
</body>
</html>