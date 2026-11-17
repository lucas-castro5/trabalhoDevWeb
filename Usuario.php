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

}

?>