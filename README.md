# API REST · PetCare

API REST em PHP puro com banco MySQL (via PDO) para gerenciamento de tutores, pets, perfis e usuários da clínica veterinária PetCare.

## Integrantes
- [Preencher nome 1]
- [Preencher nome 2]
- [Preencher nome 3]

## Como rodar o projeto
1. [Preencher passo a passo: colocar no htdocs do XAMPP, importar `banco.sql`, configurar `Conexao.php`, etc.]

## Usuário de teste
- **E-mail:** `[preencher]`
- **Senha:** `[preencher]`

## Regra de exclusão de tutores
- [Preencher justificativa para `ON DELETE RESTRICT` — ao tentar excluir um tutor que possui pets cadastrados, a API impede a exclusão e retorna `409`.]

## Estrutura do projeto
- `index.php` — roteador principal, validações e respostas JSON
- `Conexao.php` — conexão PDO com o banco MySQL `petcare`
- `Usuario.php` — operações de banco para tutores (`id_perfil = 2`) e usuários
- `Pet.php` — operações de banco para pets
- `Perfil.php` — operações de banco para perfis
- `banco.sql` — criação do banco `petcare`, tabelas e dados de exemplo

## Rotas disponíveis

URL base local: `http://localhost/petcare`

### Tutores
| Método | Rota | Descrição |
| :--- | :--- | :--- |
| `GET` | `/tutores` | Lista todos os tutores e o total |
| `GET` | `/tutores/{id}` | Busca um tutor pelo ID |
| `GET` | `/tutores/{id}/pets` | Lista os pets de um tutor |
| `POST` | `/tutores` | Cadastra um novo tutor (`nome`, `telefone`, `email`) |
| `PUT` | `/tutores/{id}` | Atualiza os dados de um tutor |
| `DELETE` | `/tutores/{id}` | Exclui um tutor (`204` ou `409` se possuir pets) |

### Pets
| Método | Rota | Descrição |
| :--- | :--- | :--- |
| `GET` | `/pets` | Lista todos os pets (aceita filtro opcional `?especie=`) |
| `GET` | `/pets/{id}` | Busca um pet pelo ID (com o nome do tutor via `JOIN`) |
| `POST` | `/pets` | Cadastra um novo pet (`nome`, `especie`, `raca`, `data_nascimento`, `peso_kg`, `tutor_id`) |
| `PUT` | `/pets/{id}` | Atualiza os dados de um pet |
| `DELETE` | `/pets/{id}` | Exclui um pet (`204`) |

### Perfis
| Método | Rota | Descrição |
| :--- | :--- | :--- |
| `GET` | `/perfis` | Lista todos os perfis |
| `GET` | `/perfis/{id}` | Busca um perfil pelo ID |
| `POST` | `/perfis` | Cadastra um novo perfil (`tipo`) |
| `PUT` | `/perfis/{id}` | Atualiza um perfil |
| `DELETE` | `/perfis/{id}` | Exclui um perfil (`204`) |

### Usuários
| Método | Rota | Descrição |
| :--- | :--- | :--- |
| `GET` | `/usuarios` | Lista todos os usuários |
| `GET` | `/usuarios/{id}` | Busca um usuário pelo ID |
| `POST` | `/usuarios` | Cadastra um novo usuário (`nome`, `email`, `telefone`, `senha`, `id_perfil`) |
| `PUT` | `/usuarios/{id}` | Atualiza um usuário |
| `DELETE` | `/usuarios/{id}` | Exclui um usuário (`204`) |
