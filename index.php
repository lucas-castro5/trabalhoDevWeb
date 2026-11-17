<?php

include 'Conexao.php';
include 'Usuario.php';
include 'Pet.php';

const OPCOES_JSON = JSON_PRETTY_PRINT
    | JSON_UNESCAPED_UNICODE
    | JSON_UNESCAPED_SLASHES
    | JSON_PRESERVE_ZERO_FRACTION;

function responder($dados = null, $status = 200)
{
    http_response_code($status);
    if ($status !== 204) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, OPCOES_JSON);
    }
    exit;
}

function lerJson(): array
{
    $corpo = file_get_contents('php://input');
    $dados = json_decode($corpo, true);
    if (!is_array($dados)) {
        responder(['detail' => 'JSON inválido'], 400);
    }
    return $dados;
}

function validarTutor(array $dados): void
{
    $erros = [];
    $nome = trim((string) ($dados['nome'] ?? ''));
    $telefone = trim((string) ($dados['telefone'] ?? ''));
    $email = trim((string) ($dados['email'] ?? ''));

    if (mb_strlen($nome) < 2 || mb_strlen($nome) > 100) {
        $erros[] = 'O nome deve ter entre 2 e 100 caracteres';
    }
    if ($telefone === '') {
        $erros[] = 'O telefone é obrigatório';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'O e-mail informado é inválido';
    }

    if (!empty($erros)) {
        responder(['detail' => 'Dados inválidos', 'erros' => $erros], 422);
    }
}

$metodo = $_SERVER['REQUEST_METHOD'];
$caminho = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($base !== '' && str_starts_with($caminho, $base)) {
    $caminho = substr($caminho, strlen($base)) ?: '/';
}
if (str_starts_with($caminho, '/index.php')) {
    $caminho = substr($caminho, strlen('/index.php')) ?: '/';
}
$caminho = rtrim($caminho, '/') ?: '/';

try {

    // GET /tutores
    if ($metodo === 'GET' && $caminho === '/tutores') {
        $usuario = new Usuario();
        $tutores = $usuario->listar();

        responder([
            'total' => count($tutores),
            'tutores' => $tutores,
        ]);
    }

    // GET /tutores/{id}
    if ($metodo === 'GET' && preg_match('#^/tutores/(\d+)$#', $caminho, $partes)) {
        $tutor = (new Usuario())->listarTutorPorId((int) $partes[1]);
        if (!$tutor) {
            responder(['detail' => 'Tutor não encontrado'], 404);
        }
        responder($tutor);
    }

    // GET /tutores/{id}/pets
    if ($metodo === 'GET' && preg_match('#^/tutores/(\d+)/pets$#', $caminho, $partes)) {
        $tutorId = (int) $partes[1];
        if (!(new Usuario())->listarTutorPorId($tutorId)) {
            responder(['detail' => 'Tutor não encontrado'], 404);
        }

        $pets = (new Pet())->listarPorTutor($tutorId);
        responder([
            'total' => count($pets),
            'pets' => $pets,
        ]);
    }

    // POST /tutores -> cria um novo tutor a partir do JSON enviado (retorna 201 Created)                                                                                
    if ($metodo === 'POST' && $caminho === '/tutores') {
        $dados = lerJson();       // lê o corpo da requisição                                                                                                            
        validarTutor($dados);     // garante nome, telefone e e-mail válidos                                                                                             
        $usuario = new Usuario();
        $id = $usuario->gravar($dados);
        responder(['id' => $id, 'nome' => $dados['nome'], 'telefone' => $dados['telefone'], 'email' => $dados['email']], 201);
    }

    // GET /pets  (filtro opcional ?especie=)
    if ($metodo === 'GET' && $caminho === '/pets') {
        $especie = $_GET['especie'] ?? null;
        $pets = (new Pet())->listar(is_string($especie) ? $especie : null);

        responder([
            'total' => count($pets),
            'pets' => $pets,
        ]);
    }

    // GET /pets/{id}  (com o nome do tutor)
    if ($metodo === 'GET' && preg_match('#^/pets/(\d+)$#', $caminho, $partes)) {
        $pet = (new Pet())->listarPorId((int) $partes[1]);
        if (!$pet) {
            responder(['detail' => 'Pet não encontrado'], 404);
        }
        responder($pet);
    }
} catch (PDOException $e) {
    if (($e->errorInfo[1] ?? null) === 1062) {
        responder(['detail' => 'E-mail já cadastrado'], 409);
    }
    responder(['detail' => 'Erro interno no banco de dados'], 500);
}

responder(['detail' => 'Rota não encontrada'], 404);