# API REST · PetCare

API REST em PHP puro com banco MySQL (via PDO) para gerenciamento de tutores, pets, perfis e usuários da clínica veterinária PetCare.

## Integrantes
- Lucas Gabriel Castro dos Santos
- Letícia Araújo da Silva Amparo
- André Silva Queiroz

## Como rodar o projeto
1. Inicie os serviços **Apache** e **MySQL** no painel do XAMPP.
2. Clone o repositório dentro da pasta `htdocs` do XAMPP com o nome `petcare`:
   ```bash
   git clone https://github.com/lucas-castro5/trabalhoDevWeb.git C:/xampp/htdocs/petcare
   ```
3. Importe e execute o script `banco.sql` no MySQL (via phpMyAdmin ou terminal) para criar o banco `petcare`, as tabelas e os dados de exemplo.
4. Confira as credenciais do MySQL em `Conexao.php` (por padrão está com usuário `root` e senha `ceub123456`; no XAMPP padrão sem senha, altere `$pass = ""`).
5. Acesse a API em `http://localhost/petcare` ou execute as requisições prontas no arquivo `requisicoes.http` (usando a extensão REST Client no VS Code).

## Usuário de teste
- **E-mail:** `professor@petcare.com`
- **Senha:** `123456` *(gravada no `banco.sql` com hash `password_hash`)*

## Modelagem e regra de exclusão de tutores
- **Modelagem:** tutores e administradores são armazenados na tabela `usuarios`, diferenciados pela tabela `perfil` (`1 = admin`, `2 = tutor`). As rotas `/tutores` filtram apenas usuários com perfil `tutor`.
- **Exclusão de tutores (`ON DELETE RESTRICT`):** optamos por **impedir** a exclusão de um tutor que possua pets cadastrados, em vez de apagar os pets em cascata (`ON DELETE CASCADE`), para evitar perda acidental dos registros e do histórico dos animais atendidos pela clínica. Ao tentar excluir um tutor vinculado a pets, o MySQL bloqueia a operação (código `1451`) e a API retorna **`409 Conflict`**. Para excluir o tutor, primeiro é necessário remover ou transferir seus pets.

## Estrutura do projeto
- `index.php` — roteador principal, validações e respostas JSON
- `.htaccess` — reescrita de URLs limpas para `index.php` e repasse do cabeçalho `Authorization`
- `Conexao.php` — conexão PDO com o banco MySQL `petcare`
- `Usuario.php` — operações de banco para tutores (`id_perfil = 2`) e usuários (com `password_hash`)
- `Pet.php` — operações de banco para pets (incluindo `JOIN` com tutor e filtro `?especie=`)
- `Perfil.php` — operações de banco para perfis
- `banco.sql` — criação do banco `petcare`, tabelas e dados de exemplo
- `requisicoes.http` — coleção de requisições cobrindo todas as rotas e casos de erro

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
