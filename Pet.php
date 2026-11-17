<?php

class Pet {

    private PDO $conexao;

    public function __construct() {
        $this->conexao = Conexao::getConnection();
    }

    private function formatar(array $pet): array {
        $pet['peso_kg'] = (float) $pet['peso_kg'];
        return $pet;
    }

    public function listar($especie = null){
        $sql = "SELECT id_pet AS id, nome_pet AS nome, especie, raca,
                       data_nascimento, peso_kg, usuario_id AS tutor_id
                FROM pets";
        if ($especie !== null && $especie !== '') {
            $sql .= " WHERE especie = :especie";
        }
        $sql .= " ORDER BY id_pet";

        $smt = $this->conexao->prepare($sql);
        if ($especie !== null && $especie !== '') {
            $smt->bindValue(':especie', $especie);
        }
        $smt->execute();
        return array_map([$this, 'formatar'], $smt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function listarPorId($id){
        $sql = "SELECT p.id_pet AS id, p.nome_pet AS nome, p.especie, p.raca,
                       p.data_nascimento, p.peso_kg,
                       p.usuario_id AS tutor_id, u.nome_usuario AS tutor_nome
                FROM pets p
                JOIN usuarios u ON u.id_usuario = p.usuario_id
                WHERE p.id_pet = :id";
        $smt = $this->conexao->prepare($sql);
        $smt->bindValue(':id', $id, PDO::PARAM_INT);
        $smt->execute();
        $pet = $smt->fetch(PDO::FETCH_ASSOC);
        return $pet ? $this->formatar($pet) : false;
    }

    public function listarPorTutor($tutorId){
        $sql = "SELECT id_pet AS id, nome_pet AS nome, especie, raca,
                       data_nascimento, peso_kg, usuario_id AS tutor_id
                FROM pets
                WHERE usuario_id = :tutor_id
                ORDER BY id_pet";
        $smt = $this->conexao->prepare($sql);
        $smt->bindValue(':tutor_id', $tutorId, PDO::PARAM_INT);
        $smt->execute();
        return array_map([$this, 'formatar'], $smt->fetchAll(PDO::FETCH_ASSOC));
    }
}