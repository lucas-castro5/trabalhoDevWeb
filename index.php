<?php

include 'Conexao.php';
include 'Perfil.php';
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

function validarPerfil(array $dados): void
{
    $erros = [];
    $tipo = trim((string) ($dados['tipo'] ?? ''));

    if (mb_strlen($tipo) < 2 || mb_strlen($tipo) > 50) {
        $erros[] = 'O tipo deve ter entre 2 e 50 caracteres';
    }

    if (!empty($erros)) {
        responder(['detail' => 'Dados inválidos', 'erros' => $erros], 422);
    }
}

function validarUsuario(array $dados): void
{
    $erros = [];
    $nome = trim((string) ($dados['nome'] ?? ''));
    $email = trim((string) ($dados['email'] ?? ''));
    $idPerfil = filter_var($dados['id_perfil'] ?? null, FILTER_VALIDATE_INT);

    if (mb_strlen($nome) < 2 || mb_strlen($nome) > 255) {
        $erros[] = 'O nome deve ter entre 2 e 255 caracteres';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'O e-mail informado é inválido';
    }
    if ($idPerfil === false || $idPerfil <= 0) {
        $erros[] = 'O id_perfil deve ser um número inteiro válido';
    }

    if (!empty($erros)) {
        responder(['detail' => 'Dados inválidos', 'erros' => $erros], 422);
    }
}

function validarPet(array $dados): void
{
    $erros = [];
    $nome = trim((string) ($dados['nome'] ?? ''));
    $especie = trim((string) ($dados['especie'] ?? ''));
    $dataNascimento = trim((string) ($dados['data_nascimento'] ?? ''));
    $pesoKg = $dados['peso_kg'] ?? null;
    $tutorId = filter_var($dados['tutor_id'] ?? null, FILTER_VALIDATE_INT);

    $especiesValidas = ['cachorro', 'gato', 'ave', 'roedor', 'outro'];

    if (mb_strlen($nome) < 2 || mb_strlen($nome) > 60) {
        $erros[] = 'O nome do pet deve ter entre 2 e 60 caracteres';
    }
    if (!in_array($especie, $especiesValidas, true)) {
        $erros[] = 'A espécie deve ser: cachorro, gato, ave, roedor ou outro';
    }
    $dt = DateTime::createFromFormat('Y-m-d', $dataNascimento);
    if (!$dt || $dt->format('Y-m-d') !== $dataNascimento) {
        $erros[] = 'A data_nascimento deve estar no formato AAAA-MM-DD';
    } elseif ($dataNascimento > date('Y-m-d')) {
        $erros[] = 'A data_nascimento não pode ser uma data futura';
    }
    if (!is_numeric($pesoKg) || (float) $pesoKg <= 0 || (float) $pesoKg > 200) {
        $erros[] = 'O peso_kg deve ser um número maior que 0 e até 200';
    }
    if ($tutorId === false || $tutorId <= 0) {
        $erros[] = 'O tutor_id deve ser um número inteiro válido';
    } elseif (!(new Usuario())->listarTutorPorId($tutorId)) {
        $erros[] = 'O tutor_id informado não existe';
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

    // PUT /tutores/{id} -> atualiza o tutor; $partes[1] contém o id capturado pela regex
    if ($metodo === 'PUT' && preg_match('#^/tutores/(\d+)$#', $caminho, $partes)) {
        $dados = lerJson();
        validarTutor($dados);
        $id = (int) $partes[1];
        $usuario = new Usuario();
        if ($usuario->atualizar($id, $dados)) {
            responder(['id' => $id, 'nome' => $dados['nome'], 'telefone' => $dados['telefone'], 'email' => $dados['email']]);
        }
        responder(['detail' => 'Tutor não encontrado ou sem alterações'], 404);
    }

    // DELETE /tutores/{id} -> exclui o tutor; retorna 204 (sem conteúdo) se der certo
    if ($metodo === 'DELETE' && preg_match('#^/tutores/(\d+)$#', $caminho, $partes)) {
        $usuario = new Usuario();
        if ($usuario->excluir((int) $partes[1])) {
            http_response_code(204);
            exit;
        }
        responder(['detail' => 'Tutor não encontrado'], 404);
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

    // POST /pets -> cria um novo pet (retorna 201 Created)
    if ($metodo === 'POST' && $caminho === '/pets') {
        $dados = lerJson();
        validarPet($dados);
        $pet = new Pet();
        $id = $pet->gravar($dados);
        responder([
            'id' => $id,
            'nome' => $dados['nome'],
            'especie' => $dados['especie'],
            'raca' => $dados['raca'] ?? null,
            'data_nascimento' => $dados['data_nascimento'],
            'peso_kg' => (float) $dados['peso_kg'],
            'tutor_id' => (int) $dados['tutor_id'],
        ], 201);
    }

    // PUT /pets/{id} -> atualiza o pet
    if ($metodo === 'PUT' && preg_match('#^/pets/(\d+)$#', $caminho, $partes)) {
        $dados = lerJson();
        validarPet($dados);
        $id = (int) $partes[1];
        $pet = new Pet();
        if ($pet->atualizar($id, $dados)) {
            responder([
                'id' => $id,
                'nome' => $dados['nome'],
                'especie' => $dados['especie'],
                'raca' => $dados['raca'] ?? null,
                'data_nascimento' => $dados['data_nascimento'],
                'peso_kg' => (float) $dados['peso_kg'],
                'tutor_id' => (int) $dados['tutor_id'],
            ]);
        }
        responder(['detail' => 'Pet não encontrado ou sem alterações'], 404);
    }

    // DELETE /pets/{id} -> exclui o pet; retorna 204 (sem conteúdo) se der certo
    if ($metodo === 'DELETE' && preg_match('#^/pets/(\d+)$#', $caminho, $partes)) {
        $pet = new Pet();
        if ($pet->excluir((int) $partes[1])) {
            http_response_code(204);
            exit;
        }
        responder(['detail' => 'Pet não encontrado'], 404);
    }

    // GET /perfis
    if ($metodo === 'GET' && $caminho === '/perfis') {
        $perfis = (new Perfil())->listar();
        responder([
            'total' => count($perfis),
            'perfis' => $perfis,
        ]);
    }

    // GET /perfis/{id}
    if ($metodo === 'GET' && preg_match('#^/perfis/(\d+)$#', $caminho, $partes)) {
        $perfil = (new Perfil())->listarPorId((int) $partes[1]);
        if (!$perfil) {
            responder(['detail' => 'Perfil não encontrado'], 404);
        }
        responder($perfil);
    }

    // POST /perfis -> cria um novo perfil (retorna 201 Created)
    if ($metodo === 'POST' && $caminho === '/perfis') {
        $dados = lerJson();
        validarPerfil($dados);
        $perfil = new Perfil();
        $id = $perfil->gravar($dados);
        responder(['id' => $id, 'tipo' => $dados['tipo']], 201);
    }

    // PUT /perfis/{id} -> atualiza o perfil
    if ($metodo === 'PUT' && preg_match('#^/perfis/(\d+)$#', $caminho, $partes)) {
        $dados = lerJson();
        validarPerfil($dados);
        $id = (int) $partes[1];
        $perfil = new Perfil();
        if ($perfil->atualizar($id, $dados)) {
            responder(['id' => $id, 'tipo' => $dados['tipo']]);
        }
        responder(['detail' => 'Perfil não encontrado ou sem alterações'], 404);
    }

    // DELETE /perfis/{id} -> exclui o perfil; retorna 204 (sem conteúdo) se der certo
    if ($metodo === 'DELETE' && preg_match('#^/perfis/(\d+)$#', $caminho, $partes)) {
        $perfil = new Perfil();
        if ($perfil->excluir((int) $partes[1])) {
            http_response_code(204);
            exit;
        }
        responder(['detail' => 'Perfil não encontrado'], 404);
    }

    // GET /usuarios
    if ($metodo === 'GET' && $caminho === '/usuarios') {
        $usuarios = (new Usuario())->listarTodos();
        responder([
            'total' => count($usuarios),
            'usuarios' => $usuarios,
        ]);
    }

    // GET /usuarios/{id}
    if ($metodo === 'GET' && preg_match('#^/usuarios/(\d+)$#', $caminho, $partes)) {
        $usuario = (new Usuario())->listarPorId((int) $partes[1]);
        if (!$usuario) {
            responder(['detail' => 'Usuário não encontrado'], 404);
        }
        responder($usuario);
    }

    // POST /usuarios -> cria um novo usuário (retorna 201 Created)
    if ($metodo === 'POST' && $caminho === '/usuarios') {
        $dados = lerJson();
        validarUsuario($dados);
        if (!(new Perfil())->listarPorId((int) $dados['id_perfil'])) {
            responder(['detail' => 'Perfil não encontrado'], 404);
        }
        $usuario = new Usuario();
        $id = $usuario->gravarUsuario($dados);
        responder([
            'id' => $id,
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'telefone' => $dados['telefone'] ?? null,
            'id_perfil' => (int) $dados['id_perfil'],
        ], 201);
    }

    // PUT /usuarios/{id} -> atualiza o usuário
    if ($metodo === 'PUT' && preg_match('#^/usuarios/(\d+)$#', $caminho, $partes)) {
        $dados = lerJson();
        validarUsuario($dados);
        if (!(new Perfil())->listarPorId((int) $dados['id_perfil'])) {
            responder(['detail' => 'Perfil não encontrado'], 404);
        }
        $id = (int) $partes[1];
        $usuario = new Usuario();
        if ($usuario->atualizarUsuario($id, $dados)) {
            responder([
                'id' => $id,
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'telefone' => $dados['telefone'] ?? null,
                'id_perfil' => (int) $dados['id_perfil'],
            ]);
        }
        responder(['detail' => 'Usuário não encontrado ou sem alterações'], 404);
    }

    // DELETE /usuarios/{id} -> exclui o usuário; retorna 204 (sem conteúdo) se der certo
    if ($metodo === 'DELETE' && preg_match('#^/usuarios/(\d+)$#', $caminho, $partes)) {
        $usuario = new Usuario();
        if ($usuario->excluirUsuario((int) $partes[1])) {
            http_response_code(204);
            exit;
        }
        responder(['detail' => 'Usuário não encontrado'], 404);
    }
} catch (PDOException $e) {
    $codigo = $e->errorInfo[1] ?? null;
    if ($codigo === 1062) {
        $mensagem = str_contains($e->getMessage(), 'tipo')
            ? 'Perfil já cadastrado'
            : 'E-mail já cadastrado';
        responder(['detail' => $mensagem], 409);
    }
    if ($codigo === 1451) {
        responder(['detail' => 'Não é possível excluir: registro possui vínculos'], 409);
    }
    if ($codigo === 1452) {
        responder(['detail' => 'Referência inválida (chave estrangeira não encontrada)'], 422);
    }
    responder(['detail' => 'Erro interno no banco de dados'], 500);
}

responder(['detail' => 'Rota não encontrada'], 404);
