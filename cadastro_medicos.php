<?php
/* ============================================================
   MediAgenda - Cadastro de Médicos
   CRUD completo com dados reais do banco de dados.

   Requisitos atendidos:
   - cadastrar médicos;
   - listar médicos cadastrados;
   - editar dados dos médicos;
   - inativar e reativar médicos;
   - relacionar médicos às especialidades;
   - utilizar dados reais do banco labdbprog2.
============================================================ */

require_once __DIR__ . '/conexao.php';

if (!isset($conexao_bd) || !$conexao_bd) {
    die('Erro: a variável $conexao_bd não foi encontrada. Verifique o arquivo conexao.php.');
}

mysqli_set_charset($conexao_bd, 'utf8mb4');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$mensagem = '';
$tipoMensagem = 'success';

function texto($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function redirecionarComMensagem($tipo, $mensagem) {
    header('Location: cadastro_medicos.php?tipo=' . urlencode($tipo) . '&msg=' . urlencode($mensagem));
    exit;
}

function buscarEspecialidades($conexao_bd) {
    $sql = "SELECT id, nome FROM especialidades WHERE status = 'Ativo' ORDER BY nome ASC";
    $resultado = mysqli_query($conexao_bd, $sql);
    $especialidades = array();

    while ($linha = mysqli_fetch_assoc($resultado)) {
        $especialidades[] = $linha;
    }

    return $especialidades;
}

function buscarMedicos($conexao_bd, $filtroNome, $filtroEspecialidade, $filtroStatus) {
    $sql = "
        SELECT
            m.id,
            m.nome,
            m.crm,
            m.especialidade_id,
            e.nome AS especialidade,
            m.telefone,
            m.email,
            m.status
        FROM medicos m
        INNER JOIN especialidades e ON e.id = m.especialidade_id
        WHERE 1 = 1
    ";

    $tipos = '';
    $parametros = array();

    if ($filtroNome !== '') {
        $sql .= ' AND (m.nome LIKE ? OR m.crm LIKE ? OR m.email LIKE ?)';
        $busca = '%' . $filtroNome . '%';
        $tipos .= 'sss';
        $parametros[] = $busca;
        $parametros[] = $busca;
        $parametros[] = $busca;
    }

    if ($filtroEspecialidade !== '') {
        $sql .= ' AND m.especialidade_id = ?';
        $tipos .= 'i';
        $parametros[] = (int)$filtroEspecialidade;
    }

    if ($filtroStatus !== '') {
        $sql .= ' AND m.status = ?';
        $tipos .= 's';
        $parametros[] = $filtroStatus;
    }

    $sql .= ' ORDER BY m.nome ASC';

    $stmt = mysqli_prepare($conexao_bd, $sql);

    if (!empty($parametros)) {
        mysqli_stmt_bind_param($stmt, $tipos, ...$parametros);
    }

    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    $medicos = array();
    while ($linha = mysqli_fetch_assoc($resultado)) {
        $medicos[] = $linha;
    }

    mysqli_stmt_close($stmt);
    return $medicos;
}

function limparTextoPost($campo) {
    return trim(isset($_POST[$campo]) ? $_POST[$campo] : '');
}

function validarStatus($status) {
    return in_array($status, array('Ativo', 'Inativo'), true) ? $status : 'Ativo';
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $acao = limparTextoPost('acao');
        $id = (int)limparTextoPost('id');

        if ($acao === 'novo' || $acao === 'editar') {
            $nome = limparTextoPost('nome');
            $crm = limparTextoPost('crm');
            $especialidadeId = (int)limparTextoPost('especialidade_id');
            $telefone = limparTextoPost('telefone');
            $email = limparTextoPost('email');
            $status = validarStatus(limparTextoPost('status'));

            if ($nome === '' || $crm === '' || $especialidadeId <= 0) {
                redirecionarComMensagem('danger', 'Preencha nome, CRM e especialidade.');
            }

            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                redirecionarComMensagem('danger', 'Informe um e-mail válido.');
            }

            if ($acao === 'novo') {
                $sql = '
                    INSERT INTO medicos
                        (nome, crm, especialidade_id, telefone, email, status)
                    VALUES
                        (?, ?, ?, ?, ?, ?)
                ';
                $stmt = mysqli_prepare($conexao_bd, $sql);
                mysqli_stmt_bind_param($stmt, 'ssisss', $nome, $crm, $especialidadeId, $telefone, $email, $status);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                redirecionarComMensagem('success', 'Médico cadastrado com sucesso.');
            }

            if ($acao === 'editar') {
                if ($id <= 0) {
                    redirecionarComMensagem('danger', 'Médico inválido para edição.');
                }

                $sql = '
                    UPDATE medicos
                    SET nome = ?, crm = ?, especialidade_id = ?, telefone = ?, email = ?, status = ?
                    WHERE id = ?
                ';
                $stmt = mysqli_prepare($conexao_bd, $sql);
                mysqli_stmt_bind_param($stmt, 'ssisssi', $nome, $crm, $especialidadeId, $telefone, $email, $status, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                redirecionarComMensagem('success', 'Médico atualizado com sucesso.');
            }
        }

        if ($acao === 'inativar' || $acao === 'reativar') {
            if ($id <= 0) {
                redirecionarComMensagem('danger', 'Médico inválido.');
            }

            $novoStatus = ($acao === 'inativar') ? 'Inativo' : 'Ativo';
            $sql = 'UPDATE medicos SET status = ? WHERE id = ?';
            $stmt = mysqli_prepare($conexao_bd, $sql);
            mysqli_stmt_bind_param($stmt, 'si', $novoStatus, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $textoMensagem = ($acao === 'inativar') ? 'Médico inativado com sucesso.' : 'Médico reativado com sucesso.';
            redirecionarComMensagem('success', $textoMensagem);
        }
    }
} catch (mysqli_sql_exception $erro) {
    if ((int)$erro->getCode() === 1062) {
        $mensagem = 'Já existe um médico cadastrado com esse CRM.';
    } else {
        $mensagem = 'Erro no banco de dados: ' . $erro->getMessage();
    }
    $tipoMensagem = 'danger';
}

if (isset($_GET['msg'])) {
    $mensagem = trim($_GET['msg']);
    $tipoMensagem = isset($_GET['tipo']) ? trim($_GET['tipo']) : 'success';
}

$filtroNome = trim(isset($_GET['nome']) ? $_GET['nome'] : '');
$filtroEspecialidade = trim(isset($_GET['especialidade_id']) ? $_GET['especialidade_id'] : '');
$filtroStatus = trim(isset($_GET['status']) ? $_GET['status'] : '');

$especialidades = buscarEspecialidades($conexao_bd);
$medicos = buscarMedicos($conexao_bd, $filtroNome, $filtroEspecialidade, $filtroStatus);
$totalAtivos = 0;
$totalInativos = 0;

foreach ($medicos as $medico) {
    if ($medico['status'] === 'Ativo') {
        $totalAtivos++;
    } else {
        $totalInativos++;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediAgenda - Cadastro de Médicos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --azul: #0d6efd;
            --azul-escuro: #084298;
            --azul-claro: #e7f1ff;
            --fundo: #f5f7fb;
            --borda: #dee2e6;
            --texto: #1f2d3d;
            --sidebar: 250px;
        }

        body {
            margin: 0;
            background: var(--fundo);
            color: var(--texto);
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }

        .navbar-topo {
            height: 60px;
            background: linear-gradient(90deg, var(--azul), var(--azul-escuro));
            box-shadow: 0 2px 10px rgba(0,0,0,.12);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .navbar-brand, .navbar-brand:hover {
            color: #fff;
            font-weight: 700;
        }

        .sidebar {
            position: fixed;
            top: 60px;
            left: 0;
            width: var(--sidebar);
            height: calc(100vh - 60px);
            background: #fff;
            border-right: 1px solid var(--borda);
            padding: 18px 0;
        }

        .sidebar .nav-link {
            color: var(--texto);
            display: flex;
            gap: 10px;
            align-items: center;
            padding: 12px 20px;
            border-left: 4px solid transparent;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.ativo {
            background: var(--azul-claro);
            color: var(--azul-escuro);
            border-left-color: var(--azul);
            font-weight: 600;
        }

        .conteudo {
            margin-left: var(--sidebar);
            margin-top: 60px;
            padding: 26px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }

        .page-header h1 {
            font-size: 1.55rem;
            color: var(--azul-escuro);
            font-weight: 800;
            margin: 0;
        }

        .card-pagina {
            background: #fff;
            border: 1px solid var(--borda);
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0,0,0,.05);
            padding: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            border-left: 4px solid var(--azul);
            background: #fff;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,.04);
        }

        .stat-card .numero {
            font-size: 1.7rem;
            font-weight: 800;
            color: var(--azul-escuro);
        }

        .tabela-medicos th {
            background: var(--azul-claro);
            color: var(--azul-escuro);
            white-space: nowrap;
        }

        .avatar-medico {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--azul-claro);
            color: var(--azul);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            margin-right: 8px;
        }

        .badge-status {
            border-radius: 999px;
            padding: 5px 11px;
            font-weight: 700;
            font-size: .78rem;
        }

        .badge-ativo {
            background: #d1e7dd;
            color: #0f5132;
        }

        .badge-inativo {
            background: #f8d7da;
            color: #842029;
        }

        @media (max-width: 900px) {
            .sidebar {
                position: static;
                width: 100%;
                height: auto;
                margin-top: 60px;
                border-right: 0;
                border-bottom: 1px solid var(--borda);
            }

            .conteudo {
                margin-left: 0;
                margin-top: 0;
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-topo px-3">
        <a class="navbar-brand" href="principal.php">
            <i class="fa-solid fa-calendar-check me-2"></i>MediAgenda
        </a>
        <span class="text-white small">Cadastro de Médicos</span>
    </nav>

    <aside class="sidebar">
        <nav class="nav flex-column">
            <a class="nav-link" href="principal.php"><i class="fa-solid fa-house"></i> Início</a>
            <a class="nav-link ativo" href="cadastro_medicos.php"><i class="fa-solid fa-user-doctor"></i> Médicos</a>
            <a class="nav-link" href="cadastro_especialidades.php"><i class="fa-solid fa-stethoscope"></i> Especialidades</a>
            <a class="nav-link" href="cadastro_agendas.php"><i class="fa-solid fa-calendar-days"></i> Agendamentos</a>
            <a class="nav-link" href="relatorios.php"><i class="fa-solid fa-chart-line"></i> Relatórios</a>
        </nav>
    </aside>

    <main class="conteudo">
        <div class="page-header">
            <div>
                <h1><i class="fa-solid fa-user-doctor me-2"></i>Cadastro de Médicos</h1>
                <p class="text-muted mb-0">Gerencie médicos, especialidades, dados de contato e status cadastral.</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMedico" onclick="abrirNovoMedico()">
                <i class="fa-solid fa-plus me-1"></i> Novo médico
            </button>
        </div>

        <?php if ($mensagem !== ''): ?>
            <div class="alert alert-<?php echo texto($tipoMensagem); ?> alert-dismissible fade show" role="alert">
                <?php echo texto($mensagem); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        <?php endif; ?>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="text-muted small">Total listado</div>
                    <div class="numero"><?php echo count($medicos); ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="text-muted small">Ativos no filtro atual</div>
                    <div class="numero"><?php echo $totalAtivos; ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="text-muted small">Inativos no filtro atual</div>
                    <div class="numero"><?php echo $totalInativos; ?></div>
                </div>
            </div>
        </div>

        <section class="card-pagina">
            <h2 class="h6 text-primary fw-bold mb-3"><i class="fa-solid fa-filter me-1"></i> Filtros de busca</h2>
            <form method="get" action="cadastro_medicos.php" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Nome, CRM ou e-mail</label>
                    <input type="text" name="nome" class="form-control" value="<?php echo texto($filtroNome); ?>" placeholder="Ex: Carlos, CRM/SP ou email">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Especialidade</label>
                    <select name="especialidade_id" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ($especialidades as $especialidade): ?>
                            <option value="<?php echo (int)$especialidade['id']; ?>" <?php echo ((string)$filtroEspecialidade === (string)$especialidade['id']) ? 'selected' : ''; ?>>
                                <?php echo texto($especialidade['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="Ativo" <?php echo ($filtroStatus === 'Ativo') ? 'selected' : ''; ?>>Ativo</option>
                        <option value="Inativo" <?php echo ($filtroStatus === 'Inativo') ? 'selected' : ''; ?>>Inativo</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-magnifying-glass"></i></button>
                    <a href="cadastro_medicos.php" class="btn btn-outline-secondary w-100"><i class="fa-solid fa-xmark"></i></a>
                </div>
            </form>
        </section>

        <section class="card-pagina">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h6 text-primary fw-bold mb-0"><i class="fa-solid fa-table-list me-1"></i> Médicos cadastrados</h2>
                <span class="text-muted small"><?php echo count($medicos); ?> registro(s) encontrado(s)</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle tabela-medicos mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>CRM</th>
                            <th>Especialidade</th>
                            <th>Telefone</th>
                            <th>E-mail</th>
                            <th>Status</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($medicos)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fa-solid fa-user-xmark me-2"></i>Nenhum médico encontrado.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($medicos as $medico): ?>
                            <?php
                                $partesNome = preg_split('/\s+/', str_replace(array('Dr.', 'Dra.'), '', $medico['nome']));
                                $iniciais = '';
                                foreach ($partesNome as $parte) {
                                    $parte = trim($parte);
                                    if ($parte !== '') {
                                        $iniciais .= strtoupper(substr($parte, 0, 1));
                                    }
                                    if (strlen($iniciais) >= 2) {
                                        break;
                                    }
                                }
                                if ($iniciais === '') {
                                    $iniciais = strtoupper(substr($medico['nome'], 0, 1));
                                }
                                $classeBadge = ($medico['status'] === 'Ativo') ? 'badge-ativo' : 'badge-inativo';
                            ?>
                            <tr>
                                <td class="text-muted"><?php echo (int)$medico['id']; ?></td>
                                <td>
                                    <span class="avatar-medico"><?php echo texto($iniciais); ?></span>
                                    <?php echo texto($medico['nome']); ?>
                                </td>
                                <td><?php echo texto($medico['crm']); ?></td>
                                <td><?php echo texto($medico['especialidade']); ?></td>
                                <td><?php echo texto($medico['telefone']); ?></td>
                                <td><?php echo texto($medico['email']); ?></td>
                                <td><span class="badge-status <?php echo $classeBadge; ?>"><?php echo texto($medico['status']); ?></span></td>
                                <td class="text-center" style="white-space: nowrap;">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Editar"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalMedico"
                                        data-id="<?php echo (int)$medico['id']; ?>"
                                        data-nome="<?php echo texto($medico['nome']); ?>"
                                        data-crm="<?php echo texto($medico['crm']); ?>"
                                        data-especialidade-id="<?php echo (int)$medico['especialidade_id']; ?>"
                                        data-telefone="<?php echo texto($medico['telefone']); ?>"
                                        data-email="<?php echo texto($medico['email']); ?>"
                                        data-status="<?php echo texto($medico['status']); ?>"
                                        onclick="abrirEditarMedico(this)">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>

                                    <?php if ($medico['status'] === 'Ativo'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarStatus(<?php echo (int)$medico['id']; ?>, 'inativar')" title="Inativar">
                                            <i class="fa-solid fa-ban"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-outline-success" onclick="confirmarStatus(<?php echo (int)$medico['id']; ?>, 'reativar')" title="Reativar">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <div class="modal fade" id="modalMedico" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form method="post" action="cadastro_medicos.php" id="formMedico">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="tituloModalMedico"><i class="fa-solid fa-user-plus me-2"></i>Novo médico</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="acao" id="acao" value="novo">
                        <input type="hidden" name="id" id="id" value="">

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Nome completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nome" id="nome" maxlength="150" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">CRM <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="crm" id="crm" maxlength="20" placeholder="CRM/SP 12345" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Especialidade <span class="text-danger">*</span></label>
                                <select class="form-select" name="especialidade_id" id="especialidade_id" required>
                                    <option value="">Selecione...</option>
                                    <?php foreach ($especialidades as $especialidade): ?>
                                        <option value="<?php echo (int)$especialidade['id']; ?>"><?php echo texto($especialidade['nome']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Telefone</label>
                                <input type="text" class="form-control" name="telefone" id="telefone" maxlength="20" placeholder="(00) 00000-0000">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">E-mail</label>
                                <input type="email" class="form-control" name="email" id="email" maxlength="150" placeholder="medico@clinica.com">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" id="status">
                                    <option value="Ativo">Ativo</option>
                                    <option value="Inativo">Inativo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form method="post" action="cadastro_medicos.php" id="formStatusMedico" class="d-none">
        <input type="hidden" name="acao" id="acaoStatus" value="">
        <input type="hidden" name="id" id="idStatus" value="">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function abrirNovoMedico() {
            document.getElementById('tituloModalMedico').innerHTML = '<i class="fa-solid fa-user-plus me-2"></i>Novo médico';
            document.getElementById('formMedico').reset();
            document.getElementById('acao').value = 'novo';
            document.getElementById('id').value = '';
            document.getElementById('status').value = 'Ativo';
        }

        function abrirEditarMedico(botao) {
            document.getElementById('tituloModalMedico').innerHTML = '<i class="fa-solid fa-pen me-2"></i>Editar médico';
            document.getElementById('acao').value = 'editar';
            document.getElementById('id').value = botao.dataset.id;
            document.getElementById('nome').value = botao.dataset.nome;
            document.getElementById('crm').value = botao.dataset.crm;
            document.getElementById('especialidade_id').value = botao.dataset.especialidadeId;
            document.getElementById('telefone').value = botao.dataset.telefone;
            document.getElementById('email').value = botao.dataset.email;
            document.getElementById('status').value = botao.dataset.status;
        }

        function confirmarStatus(id, acao) {
            var titulo = acao === 'inativar' ? 'Inativar médico?' : 'Reativar médico?';
            var texto = acao === 'inativar'
                ? 'O médico continuará no banco, mas ficará com status Inativo.'
                : 'O médico voltará ao status Ativo.';
            var confirmacao = acao === 'inativar' ? 'Sim, inativar' : 'Sim, reativar';

            Swal.fire({
                title: titulo,
                text: texto,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: confirmacao,
                cancelButtonText: 'Cancelar',
                confirmButtonColor: acao === 'inativar' ? '#dc3545' : '#198754'
            }).then(function(resultado) {
                if (resultado.isConfirmed) {
                    document.getElementById('acaoStatus').value = acao;
                    document.getElementById('idStatus').value = id;
                    document.getElementById('formStatusMedico').submit();
                }
            });
        }
    </script>
</body>
</html>
