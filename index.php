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
    responder(['detail' => 'Erro interno no banco de dados'], 500);
}

responder(['detail' => 'Rota não encontrada'], 404);