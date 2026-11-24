<?php

class Perfil {

    private PDO $conexao;

    public function __construct() {
        $this->conexao = Conexao::getConnection();
    }

    public function listar(): array {
        $sql = "SELECT perfil_id AS id, tipo FROM perfil ORDER BY perfil_id";
        $smt = $this->conexao->prepare($sql);
        $smt->execute();
        return $smt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarPorId(int $id) {
        $sql = "SELECT perfil_id AS id, tipo FROM perfil WHERE perfil_id = :id";
        $smt = $this->conexao->prepare($sql);
        $smt->bindValue(':id', $id, PDO::PARAM_INT);
        $smt->execute();
        return $smt->fetch(PDO::FETCH_ASSOC);
    }

    public function gravar(array $dados): int {
        $sql = "INSERT INTO perfil (tipo) VALUES (:tipo)";
        $smt = $this->conexao->prepare($sql);
        $smt->bindValue(':tipo', $dados['tipo']);
        $smt->execute();
        return (int) $this->conexao->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool {
        $sql = "UPDATE perfil SET tipo = :tipo WHERE perfil_id = :id";
        $smt = $this->conexao->prepare($sql);
        $smt->bindValue(':tipo', $dados['tipo']);
        $smt->bindValue(':id', $id, PDO::PARAM_INT);
        $smt->execute();
        return $smt->rowCount() > 0;
    }

    public function excluir(int $id): bool {
        $sql = "DELETE FROM perfil WHERE perfil_id = :id";
        $smt = $this->conexao->prepare($sql);
        $smt->bindValue(':id', $id, PDO::PARAM_INT);
        $smt->execute();
        return $smt->rowCount() > 0;
    }
}
