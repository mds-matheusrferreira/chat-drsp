# Chat DRSP — IA local para apoio ao SUAS

Sistema web em Laravel para atendimento interno com IA local, voltado ao apoio do DRSP — Departamento de Rede Socioassistencial Privada — em temas relacionados ao SUAS, CEBAS, CNEAS e documentos internos da área.

O projeto combina:

- chat institucional em português;
- modelo local via Ollama;
- base de conhecimento com documentos enviados pela equipe;
- busca semântica em documentos indexados;
- área administrativa protegida para upload, renovação e exclusão de documentos.

## Objetivo

A aplicação serve como um assistente interno para apoiar consultas de trabalho do DRSP. O chat responde com base no prompt institucional e, quando houver documentos indexados, utiliza os trechos encontrados na base interna como referência principal.

O sistema foi pensado para trabalhar com documentos recorrentes da área, inclusive certificados, registros e materiais com validade, permitindo renovação de arquivos quando um documento novo substitui outro de mesmo nome e formato.

## Tecnologias usadas

### Backend

- PHP 8.4 no ambiente local atual
- Laravel 12
- Laravel Queue com driver `database`
- SQLite por padrão no ambiente local
- Laravel HTTP Client
- Laravel Process

### Frontend

- Blade templates
- CSS próprio nas views
- Vite
- TailwindCSS disponível no projeto
- Axios disponível no projeto

### IA e busca de documentos

- Ollama para execução local do modelo de linguagem
- Modelo padrão: `gemma3:4b`
- Python para ingestão, remoção e busca nos documentos
- ChromaDB como base vetorial local
- Sentence Transformers para embeddings
- Bibliotecas Python para leitura de documentos:
  - PDF
  - DOCX
  - XLS / XLSX
  - CSV
  - TXT

## Principais telas e rotas

### Chat

| Rota | Método | Função |
| --- | --- | --- |
| `/` | GET | Tela principal do chat |
| `/chat` | POST | Envia pergunta sem streaming |
| `/chat/stream` | POST | Envia pergunta com resposta em streaming |

Arquivos principais:

- `resources/views/chat.blade.php` — interface do chat
- `app/Http/Controllers/ChatController.php` — controle do chat, montagem do prompt e chamada ao Ollama
- `app/Services/Knowledge/KnowledgeSearchService.php` — busca trechos relevantes nos documentos indexados

### Administração de documentos

| Rota | Método | Função |
| --- | --- | --- |
| `/documents/login` | GET | Tela de login da área de documentos |
| `/documents/login` | POST | Autenticação da área de documentos |
| `/documents/logout` | POST | Logout da área de documentos |
| `/documents` | GET | Lista e upload de documentos |
| `/documents` | POST | Envia documento para indexação |
| `/documents/delete-selected` | POST | Exclui documentos selecionados com senha |
| `/documents/{document}` | DELETE | Remove documento individualmente |

Arquivos principais:

- `resources/views/documents/login.blade.php` — modal/tela de login da área restrita
- `resources/views/documents/index.blade.php` — área de upload, listagem e exclusão de documentos
- `app/Http/Controllers/DocumentAuthController.php` — login/logout da área de documentos
- `app/Http/Controllers/KnowledgeDocumentController.php` — upload, listagem e remoção de documentos
- `app/Http/Middleware/RequireDocumentAdmin.php` — protege a área de documentos
- `app/Models/KnowledgeDocument.php` — modelo dos documentos cadastrados

## Login da área de documentos

A área `/documents` é protegida por sessão.

Credenciais padrão no ambiente local:

```env
KNOWLEDGE_DOCUMENT_ADMIN_USERNAME=admin
KNOWLEDGE_DOCUMENT_ADMIN_PASSWORD=drsp
```

Esses valores ficam em:

- `.env`
- `.env.example`
- `config/knowledge.php`

A senha também é exigida para excluir documentos selecionados na tela **Base atual**.

## Prompt do agente de IA

O prompt principal do agente fica em:

```text
app/Http/Controllers/ChatController.php
```

Método:

```php
private function prompt(string $message, string $knowledgeContext = ''): string
```

É nesse método que deve ser editado o comportamento do agente, por exemplo:

```text
Você é um agente de IA para ajudar o DRSP dentro do Ministério do Desenvolvimento Social em relação ao SUAS, CEBAS e CNEAS.
```

Prompt atual:

```text
Você é um assistente interno do DRSP — Departamento de Rede Socioassistencial Privada do SUAS.
Responda sempre em português do Brasil.
Use linguagem clara, objetiva e institucional.
Use os documentos internos fornecidos como principal referência quando eles forem relevantes.
Quando a informação não estiver nos documentos fornecidos, diga que não encontrou informação suficiente na base interna.
Não invente normas, prazos, números ou procedimentos.
Para temas sensíveis, recomende validação com a equipe responsável.
```

O prompt final enviado ao modelo é composto por:

1. instruções fixas do agente;
2. contexto recuperado dos documentos internos, quando houver resultado relevante;
3. pergunta do usuário.

## Como o chat funciona

1. O usuário envia uma pergunta pela tela `/`.
2. `ChatController` recebe a mensagem.
3. `KnowledgeSearchService` busca trechos relevantes na base vetorial.
4. `ChatController::prompt()` monta o prompt final.
5. A aplicação envia o prompt para o Ollama.
6. O modelo responde em português.
7. A resposta aparece na interface do chat.

Configurações do Ollama:

```env
OLLAMA_URL=http://127.0.0.1:11434
OLLAMA_MODEL=gemma3:4b
```

## Como a base de documentos funciona

### Upload

A equipe acessa `/documents`, faz login e envia arquivos para a base.

Extensões aceitas por padrão:

```php
txt, csv, pdf, docx, xlsx, xls
```

Configuração:

```text
config/knowledge.php
```

Variável de limite da aplicação:

```env
KNOWLEDGE_MAX_UPLOAD_MB=50
```

### Indexação em segundo plano

Arquivos grandes podem demorar para processar. Por isso o upload não espera a indexação terminar dentro do request HTTP.

Fluxo atual:

1. arquivo é salvo;
2. registro é criado com status `indexing`;
3. job `IndexKnowledgeDocument` é enviado para a fila;
4. worker da fila processa o Python de indexação;
5. documento muda para `ready` ou `failed`.

Arquivos envolvidos:

- `app/Jobs/IndexKnowledgeDocument.php`
- `app/Services/Knowledge/KnowledgeIngestionService.php`
- `knowledge/ingest.py`

### Renovação de documentos

Se for enviado um segundo documento com o mesmo:

- nome original;
- formato/extensão;

então o sistema entende que é uma renovação.

Nesse caso:

1. o documento antigo é removido;
2. o arquivo antigo é excluído;
3. a indexação antiga é removida do ChromaDB;
4. o novo documento é salvo e indexado.

Isso atende ao fluxo de documentos com validade, como certificados de entidades do SUAS.

O banco possui unicidade para:

```text
original_name + extension
```

Essa regra evita duplicidade em uploads simultâneos.

### Exclusão manual de documentos

Na área `/documents`, seção **Base atual**, é possível:

1. selecionar documentos por checkbox;
2. informar a senha administrativa;
3. confirmar a exclusão;
4. remover arquivo, registro e indexação da base.

## Scripts Python

Os scripts ficam em:

```text
knowledge/
```

Principais arquivos:

- `knowledge/ingest.py` — lê o documento, quebra em trechos e indexa no ChromaDB
- `knowledge/search.py` — busca trechos relevantes para uma pergunta
- `knowledge/delete.py` — remove os trechos de um documento da base vetorial
- `knowledge/requirements.txt` — dependências Python

Configurações relacionadas:

```env
KNOWLEDGE_PYTHON_BIN=python3
KNOWLEDGE_CHROMA_PATH=storage/app/private/knowledge/chromadb
KNOWLEDGE_DOCUMENT_PATH=knowledge/documents
KNOWLEDGE_SEARCH_LIMIT=5
```

## Instalação local

### 1. Instalar dependências PHP

```bash
composer install
```

### 2. Instalar dependências JavaScript

```bash
npm install
```

### 3. Criar `.env`

```bash
cp .env.example .env
```

### 4. Gerar chave da aplicação

```bash
php artisan key:generate
```

### 5. Criar banco SQLite, se necessário

```bash
touch database/database.sqlite
```

### 6. Rodar migrations

```bash
php artisan migrate
```

### 7. Instalar dependências Python

```bash
python3 -m pip install -r knowledge/requirements.txt
```

### 8. Garantir que o Ollama está rodando

```bash
ollama serve
```

Em outro terminal, confirme ou baixe o modelo configurado:

```bash
ollama pull gemma3:4b
```

## Comandos para usar o projeto

Para trabalhar com arquivos grandes, use dois terminais.

### Terminal 1 — servidor Laravel

```bash
php -d upload_max_filesize=64M -d post_max_size=64M -d memory_limit=512M -d max_execution_time=300 -d max_input_time=300 artisan serve --host=0.0.0.0 --port=8000
```

Acesse:

```text
http://127.0.0.1:8000
```

Ou, em rede local, use o IP da máquina:

```text
http://IP_DA_MAQUINA:8000
```

### Terminal 2 — fila de indexação

```bash
php artisan queue:work --tries=1 --timeout=1800
```

Esse worker é obrigatório para processar documentos em segundo plano.

### Terminal 3 — Vite, se estiver usando assets em desenvolvimento

```bash
npm run dev
```

## Configurações importantes no `.env`

```env
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite
QUEUE_CONNECTION=database
DB_QUEUE_RETRY_AFTER=2100

OLLAMA_URL=http://127.0.0.1:11434
OLLAMA_MODEL=gemma3:4b

KNOWLEDGE_PYTHON_BIN=python3
KNOWLEDGE_CHROMA_PATH=storage/app/private/knowledge/chromadb
KNOWLEDGE_DOCUMENT_PATH=knowledge/documents
KNOWLEDGE_SEARCH_LIMIT=5
KNOWLEDGE_MAX_UPLOAD_MB=50

KNOWLEDGE_DOCUMENT_ADMIN_USERNAME=admin
KNOWLEDGE_DOCUMENT_ADMIN_PASSWORD=drsp
```

### Sobre `DB_QUEUE_RETRY_AFTER`

Mantenha `DB_QUEUE_RETRY_AFTER=2100` porque o job de indexação pode durar bastante.

O worker usa:

```bash
--timeout=1800
```

O `retry_after` precisa ser maior que o timeout do worker para evitar que o mesmo documento seja processado duas vezes enquanto ainda está indexando.

## Limites para upload grande

O Laravel valida o tamanho pelo `KNOWLEDGE_MAX_UPLOAD_MB`, mas o PHP pode barrar o POST antes da aplicação receber o arquivo.

Por isso o comando recomendado do servidor usa:

```bash
-d upload_max_filesize=64M
-d post_max_size=64M
-d memory_limit=512M
-d max_execution_time=300
-d max_input_time=300
```

Se aparecer erro como:

```text
The POST data is too large.
```

ou:

```text
Maximum execution time of 30 seconds exceeded
```

confirme que:

1. o servidor foi iniciado com o comando completo acima;
2. o worker da fila está rodando;
3. `DB_QUEUE_RETRY_AFTER=2100` está no `.env`;
4. as migrations foram executadas.

## Testes e validação

O projeto possui testes automatizados em `tests/Feature` cobrindo regras importantes da aplicação.

Testes adicionados:

- `tests/Feature/DocumentAuthTest.php`
  - garante que `/documents` exige login;
  - valida login com `admin` / `drsp`;
  - rejeita credenciais incorretas.
- `tests/Feature/KnowledgeDocumentTest.php`
  - valida criação de documento com status `indexing`;
  - garante disparo do job `IndexKnowledgeDocument`;
  - testa validações HTTP de upload: arquivo obrigatório, extensão inválida, tamanho acima do limite e título longo;
  - testa renovação de documento com mesmo nome e formato;
  - testa exclusão selecionada com senha correta;
  - testa validações HTTP da exclusão: seleção vazia, ID inexistente e senha ausente;
  - impede exclusão selecionada com senha incorreta.
- `tests/Feature/KnowledgeDocumentDatabaseTest.php`
  - valida a regra única de banco para `original_name + extension`.

Rodar todos os testes:

```bash
php artisan test
```

Rodar testes pelo Composer:

```bash
composer test
```

Rodar apenas os testes de documentos:

```bash
php artisan test tests/Feature/DocumentAuthTest.php tests/Feature/KnowledgeDocumentTest.php tests/Feature/KnowledgeDocumentDatabaseTest.php
```

Verificar rotas de documentos:

```bash
php artisan route:list --name=documents
```

Verificar sintaxe PHP de um arquivo:

```bash
php -l caminho/do/arquivo.php
```

Build frontend:

```bash
npm run build
```

## Pontos importantes para manutenção

- O prompt do agente fica em `app/Http/Controllers/ChatController.php`.
- A interface do chat fica em `resources/views/chat.blade.php`.
- A administração de documentos fica em `resources/views/documents/index.blade.php`.
- O login da administração fica em `resources/views/documents/login.blade.php`.
- A busca nos documentos fica em `app/Services/Knowledge/KnowledgeSearchService.php`.
- A indexação fica em `app/Services/Knowledge/KnowledgeIngestionService.php` e `knowledge/ingest.py`.
- A fila precisa estar rodando para que documentos saiam do status `indexing`.
- Ao trocar um documento por outro de mesmo nome e formato, o sistema remove o antigo e mantém o novo.
- Não use o chat como fonte normativa final quando a base interna não trouxer informação suficiente.
- Para temas sensíveis, o próprio prompt orienta validação com a equipe responsável.

## Estrutura resumida

```text
app/
  Http/Controllers/
    ChatController.php
    DocumentAuthController.php
    KnowledgeDocumentController.php
  Http/Middleware/
    RequireDocumentAdmin.php
  Jobs/
    IndexKnowledgeDocument.php
  Models/
    KnowledgeDocument.php
  Services/Knowledge/
    KnowledgeIngestionService.php
    KnowledgeSearchService.php

config/
  knowledge.php
  queue.php

database/migrations/
  *_create_knowledge_documents_table.php
  *_create_jobs_table.php

knowledge/
  ingest.py
  search.py
  delete.py
  requirements.txt

resources/views/
  chat.blade.php
  documents/
    login.blade.php
    index.blade.php
```

## Solução de problemas

### O chat não responde

Verifique:

```bash
ollama serve
ollama list
```

Confirme no `.env`:

```env
OLLAMA_URL=http://127.0.0.1:11434
OLLAMA_MODEL=gemma3:4b
```

### Documento fica parado em `indexing`

Inicie o worker:

```bash
php artisan queue:work --tries=1 --timeout=1800
```

Verifique se existem jobs pendentes na tabela `jobs`.

### Upload grande falha

Suba o servidor com:

```bash
php -d upload_max_filesize=64M -d post_max_size=64M -d memory_limit=512M -d max_execution_time=300 -d max_input_time=300 artisan serve --host=0.0.0.0 --port=8000
```

### Login de documentos não abre

Verifique se existe:

```text
resources/views/documents/login.blade.php
```

E se as rotas existem:

```bash
php artisan route:list --name=documents
```

### Busca não encontra documentos

Verifique se:

1. o documento está com status `ready`;
2. o worker da fila rodou;
3. o ChromaDB está em `storage/app/private/knowledge/chromadb`;
4. as dependências Python foram instaladas.
