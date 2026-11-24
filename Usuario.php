<?php

class Usuario {

    private PDO $conexao;

    public function __construct() {
        $this->conexao = Conexao::getConnection();
    }

    public function listarTutorPorId($id){
        $sql = "SELECT u.id_usuario AS id,
                       u.nome_usuario AS nome,
                       u.telefone,
                       u.email_usuario AS email
                FROM usuarios u
                JOIN perfil pf ON pf.perfil_id = u.id_perfil
                WHERE pf.tipo = 'tutor' AND u.id_usuario = :id";
        $smt = $this->conexao->prepare($sql);
        $smt->bindValue(':id', $id, PDO::PARAM_INT);
        $smt->execute();
        return $smt->fetch(PDO::FETCH_ASSOC); // false se não existe ou se não é tutor
    }
    public function listar(){
        $sql = "SELECT u.id_usuario AS id,
                       u.nome_usuario AS nome,
                       u.telefone,
                       u.email_usuario AS email
                FROM usuarios u
                JOIN perfil pf ON pf.perfil_id = u.id_perfil
                WHERE pf.tipo = 'tutor'
                ORDER BY u.id_usuario";
        $smt = $this->conexao->prepare($sql);
        $smt->execute();
        return $smt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function gravar(array $dados): int {
        $sql = "INSERT INTO usuarios (nome_usuario, email_usuario, telefone, senha_hash, id_perfil)
                VALUES (:nome, :email, :telefone, :senha_hash, 2)";
        $smt = $this->conexao->prepare($sql);
        $smt->bindValue(':nome', $dados['nome']);
        $smt->bindValue(':email', $dados['email']);
        $smt->bindValue(':telefone', $dados['telefone']);
        $smt->bindValue(':senha_hash', $dados['senha_hash'] ?? '');
        $smt->execute();
        return (int) $this->conexao->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool {
        $sql = "UPDATE usuarios
                SET nome_usuario = :nome,
                    email_usuario = :email,
                    telefone = :telefone
                WHERE id_usuario = :id AND id_perfil = 2";
        $smt = $this->conexao->prepare($sql);
        $smt->bindValue(':nome', $dados['nome']);
        $smt->bindValue(':email', $dados['email']);
        $smt->bindValue(':telefone', $dados['telefone']);
        $smt->bindValue(':id', $id, PDO::PARAM_INT);
        $smt->execute();
        return $smt->rowCount() > 0;
    }

    public function excluir(int $id): bool {
        $sql = "DELETE FROM usuarios WHERE id_usuario = :id AND id_perfil = 2";
        $smt = $this->conexao->prepare($sql);
        $smt->bindValue(':id', $id, PDO::PARAM_INT);
        $smt->execute();
        return $smt->rowCount() > 0;
    }

    public function listarTodos(): array {
        $sql = "SELECT u.id_usuario AS id,
                       u.nome_usuario AS nome,
                       u.email_usuario AS email,
                       u.telefone,
                       u.id_perfil,
                       pf.tipo AS perfil
                FROM usuarios u
                JOIN perfil pf ON pf.perfil_id = u.id_perfil
                ORDER BY u.id_usuario";
        $smt = $this->conexao->prepare($sql);
        $smt->execute();
        return $smt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarPorId(int $id) {
        $sql = "SELECT u.id_usuario AS id,
                       u.nome_usuario AS nome,
                       u.email_usuario AS email,
                       u.telefone,
                       u.id_perfil,
                       pf.tipo AS perfil
                FROM usuarios u
                JOIN perfil pf ON pf.perfil_id = u.id_perfil
                WHERE u.id_usuario = :id";
        $smt = $this->conexao->prepare($sql);
        $smt->bindValue(':id', $id, PDO::PARAM_INT);
        $smt->execute();
        return $smt->fetch(PDO::FETCH_ASSOC);
    }

    public function gravarUsuario(array $dados): int {
        $sql = "INSERT INTO usuarios (nome_usuario, email_usuario, telefone, senha_hash, id_perfil)
                VALUES (:nome, :email, :telefone, :senha_hash, :id_perfil)";
        $senhaHash = isset($dados['senha']) && $dados['senha'] !== ''
            ? password_hash((string) $dados['senha'], PASSWORD_DEFAULT)
            : ($dados['senha_hash'] ?? '');
        $smt = $this->conexao->prepare($sql);
        $smt->bindValue(':nome', $dados['nome']);
        $smt->bindValue(':email', $dados['email']);
        $smt->bindValue(':telefone', $dados['telefone'] ?? null);
        $smt->bindValue(':senha_hash', $senhaHash);
        $smt->bindValue(':id_perfil', (int) $dados['id_perfil'], PDO::PARAM_INT);
        $smt->execute();
        return (int) $this->conexao->lastInsertId();
    }

    public function atualizarUsuario(int $id, array $dados): bool {
        $atualizarSenha = isset($dados['senha']) && $dados['senha'] !== '';
        $sql = "UPDATE usuarios
                SET nome_usuario = :nome,
                    email_usuario = :email,
                    telefone = :telefone,
                    id_perfil = :id_perfil"
            . ($atualizarSenha ? ", senha_hash = :senha_hash" : "")
            . " WHERE id_usuario = :id";
        $smt = $this->conexao->prepare($sql);
        $smt->bindValue(':nome', $dados['nome']);
        $smt->bindValue(':email', $dados['email']);
        $smt->bindValue(':telefone', $dados['telefone'] ?? null);
        $smt->bindValue(':id_perfil', (int) $dados['id_perfil'], PDO::PARAM_INT);
        if ($atualizarSenha) {
            $smt->bindValue(':senha_hash', password_hash((string) $dados['senha'], PASSWORD_DEFAULT));
        }
        $smt->bindValue(':id', $id, PDO::PARAM_INT);
        $smt->execute();
        return $smt->rowCount() > 0;
    }

    public function excluirUsuario(int $id): bool {
        $sql = "DELETE FROM usuarios WHERE id_usuario = :id";
        $smt = $this->conexao->prepare($sql);
        $smt->bindValue(':id', $id, PDO::PARAM_INT);
        $smt->execute();
        return $smt->rowCount() > 0;
    }

}

?>