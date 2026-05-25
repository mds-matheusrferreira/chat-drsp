# Chat DRSP — IA local para apoio ao SUAS

Sistema web em Laravel para atendimento interno com IA local, voltado ao apoio do DRSP — Departamento de Rede Socioassistencial Privada — em temas relacionados ao SUAS, CEBAS, CNEAS e documentos internos da área.

O projeto combina:

- chat institucional em português;
- modelo local via Ollama;
- conversa com histórico recente;
- base de conhecimento com documentos enviados pela equipe;
- busca semântica em documentos indexados;
- exibição separada das fontes recuperadas;
- área administrativa protegida para upload, cadastro de textos, reprocessamento, renovação e exclusão de documentos.

## Objetivo

A aplicação serve como um assistente interno para apoiar consultas de trabalho do DRSP. O chat responde com base no prompt institucional e, quando houver documentos indexados, utiliza os trechos encontrados na base interna como referência principal.

O sistema foi pensado para trabalhar com documentos recorrentes da área, inclusive certificados, registros e materiais com validade, permitindo renovação quando um documento novo substitui outro de mesmo nome e formato.

## Tecnologias usadas

### Backend

- PHP 8.2 ou superior
- Laravel 12
- Laravel Queue com driver `sync` por padrão
- SQLite por padrão no ambiente local
- Laravel HTTP Client
- Symfony Process via serviço Python

### Frontend

- Blade templates
- CSS próprio nas views
- Vite
- TailwindCSS disponível no projeto
- Axios disponível no projeto

### IA e busca de documentos

- Ollama para execução local do modelo de linguagem
- Modelo padrão no `.env.example`: `qwen2.5:3b-instruct`
- Modelo fallback no código: `gemma3:4b`
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
| `/chat/health` | GET | Verifica conexão com Ollama e disponibilidade do modelo |
| `/chat` | POST | Envia pergunta sem streaming |
| `/chat/stream` | POST | Envia pergunta com resposta em streaming |

Arquivos principais:

- `resources/views/chat.blade.php` — interface do chat
- `app/Http/Controllers/ChatController.php` — controle do chat, histórico, montagem do prompt e chamada ao Ollama
- `app/Services/Knowledge/KnowledgeSearchService.php` — busca trechos relevantes nos documentos indexados e formata fontes

### Administração de documentos

| Rota | Método | Função |
| --- | --- | --- |
| `/documents/login` | GET | Tela de login da área de documentos |
| `/documents/login` | POST | Autenticação da área de documentos |
| `/documents/logout` | POST | Logout da área de documentos |
| `/documents` | GET | Lista documentos, upload e cadastro manual de texto |
| `/documents` | POST | Envia arquivos para indexação |
| `/documents/text` | POST | Cadastra texto manual como documento `.txt` |
| `/documents/status` | GET | Retorna status dos documentos em JSON |
| `/documents/{document}/reprocess` | POST | Reprocessa um documento existente |
| `/documents/delete-selected` | POST | Exclui documentos selecionados com senha |
| `/documents/{document}` | DELETE | Remove documento individualmente |

Arquivos principais:

- `resources/views/documents/login.blade.php` — login da área restrita
- `resources/views/documents/index.blade.php` — área de upload, cadastro manual, listagem, reprocessamento e exclusão
- `app/Http/Controllers/DocumentAuthController.php` — login/logout da área de documentos
- `app/Http/Controllers/KnowledgeDocumentController.php` — upload, texto manual, status, reprocessamento, listagem e remoção
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
private function prompt(string $message, string $knowledgeContext = '', array $history = []): string
```

É nesse método que deve ser editado o comportamento do agente.

Prompt base atual:

```text
Você é um assistente interno do DRSP — Departamento de Rede Socioassistencial Privada do SUAS.
Responda sempre em português do Brasil.
Use linguagem clara, objetiva e institucional.
Responda somente com base no contexto de documentos internos fornecido quando houver contexto.
Quando a informação não estiver nos documentos fornecidos, diga que não encontrou informação suficiente na base interna.
Não invente normas, prazos, números, procedimentos ou conceitos que não apareçam no contexto.
Para temas sensíveis, recomende validação com a equipe responsável.
Não liste fontes no texto da resposta; a aplicação exibirá as fontes recuperadas em bloco separado.
```

O prompt final enviado ao modelo é composto por:

1. instruções fixas do agente;
2. histórico recente da conversa, quando enviado pela interface;
3. contexto recuperado dos documentos internos, quando houver resultado relevante;
4. pergunta do usuário.

## Como o chat funciona

1. O usuário envia uma pergunta pela tela `/`.
2. A interface envia a mensagem e parte do histórico recente.
3. `ChatController` valida a mensagem e normaliza o histórico.
4. `KnowledgeSearchService` busca trechos relevantes usando a pergunta atual junto das últimas mensagens do usuário.
5. `ChatController::prompt()` monta o prompt final com instruções, histórico, contexto e pergunta.
6. A aplicação envia o prompt para o Ollama.
7. O modelo responde em português.
8. A resposta aparece na interface do chat.
9. As fontes recuperadas são exibidas em bloco separado pela aplicação.

Configurações do Ollama:

```env
OLLAMA_URL=http://127.0.0.1:11434
OLLAMA_MODEL=qwen2.5:3b-instruct
```

## Como a base de documentos funciona

### Upload de arquivos

A equipe acessa `/documents`, faz login e envia arquivos para a base.

Extensões aceitas por padrão:

```text
txt, md, csv, pdf, docx, xlsx, xls
```

Configuração:

```text
config/knowledge.php
```

Variável de limite da aplicação:

```env
KNOWLEDGE_MAX_UPLOAD_MB=50
```

### Cadastro manual de texto

A tela `/documents` também permite cadastrar um texto manualmente.

Fluxo:

1. a equipe informa um título;
2. cola ou digita o conteúdo;
3. o sistema salva o texto como documento `.txt`;
4. o documento é indexado com o mesmo fluxo dos arquivos enviados.

Validações principais:

- título obrigatório, até 255 caracteres;
- texto obrigatório, mínimo de 10 caracteres e máximo de 200000 caracteres.

### Indexação automática

A indexação acontece automaticamente durante o fluxo de upload, cadastro manual ou reprocessamento. Não é necessário iniciar `queue:work` em outro terminal quando `QUEUE_CONNECTION=sync`.

Fluxo atual:

1. arquivo ou texto é salvo;
2. registro é criado com status `indexing`;
3. job `IndexKnowledgeDocument` é executado de forma síncrona;
4. o Python de indexação processa o conteúdo;
5. documento muda para `ready` ou `failed`.

Arquivos envolvidos:

- `app/Jobs/IndexKnowledgeDocument.php`
- `app/Services/Knowledge/KnowledgeIngestionService.php`
- `app/Services/Knowledge/KnowledgePythonProcess.php`
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

### Reprocessamento

Na tela `/documents`, um documento pode ser reprocessado quando não estiver em status `indexing`.

O reprocessamento:

1. altera o status para `indexing`;
2. zera a contagem de chunks;
3. remove a mensagem de erro anterior;
4. executa novamente `IndexKnowledgeDocument`.

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
KNOWLEDGE_PYTHON_BIN=python
KNOWLEDGE_CHROMA_PATH=storage/app/private/knowledge/chromadb
KNOWLEDGE_TMP_PATH=storage/app/private/knowledge/tmp
KNOWLEDGE_DOCUMENT_PATH=knowledge/documents
KNOWLEDGE_SEARCH_LIMIT=8
KNOWLEDGE_CHUNK_SIZE=700
KNOWLEDGE_CHUNK_OVERLAP=120
```

Observações:

- `KNOWLEDGE_SEARCH_LIMIT` é limitado entre 1 e 20.
- `KNOWLEDGE_CHUNK_SIZE` é limitado entre 200 e 3000.
- `KNOWLEDGE_CHUNK_OVERLAP` não pode ser maior que o tamanho do chunk.
- `KNOWLEDGE_TMP_PATH` define uma pasta temporária local do projeto para execução dos processos Python.

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
python -m pip install -r knowledge/requirements.txt
```

### 8. Garantir que o Ollama está rodando

```bash
ollama serve
```

Em outro terminal, confirme ou baixe o modelo configurado:

```bash
ollama pull qwen2.5:3b-instruct
```

## Comandos para usar o projeto

### Servidor Laravel

Para trabalhar com upload e indexação de arquivos maiores, suba o servidor com limites maiores de PHP:

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

### Vite, se estiver usando assets em desenvolvimento

```bash
npm run dev
```

### Script Composer de desenvolvimento

O projeto também possui o script:

```bash
composer dev
```

Ele inicia servidor, fila, logs e Vite em paralelo. No fluxo atual de indexação automática com `QUEUE_CONNECTION=sync`, o servidor Laravel isolado já é suficiente para upload, cadastro manual e reprocessamento.

## Configurações importantes no `.env`

```env
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/chat-drsp

DB_CONNECTION=sqlite
SESSION_DRIVER=database
QUEUE_CONNECTION=sync
DB_QUEUE_RETRY_AFTER=2100

OLLAMA_URL=http://127.0.0.1:11434
OLLAMA_MODEL=qwen2.5:3b-instruct

KNOWLEDGE_PYTHON_BIN=python
KNOWLEDGE_CHROMA_PATH=storage/app/private/knowledge/chromadb
KNOWLEDGE_TMP_PATH=storage/app/private/knowledge/tmp
KNOWLEDGE_DOCUMENT_PATH=knowledge/documents
KNOWLEDGE_SEARCH_LIMIT=8
KNOWLEDGE_CHUNK_SIZE=700
KNOWLEDGE_CHUNK_OVERLAP=120
KNOWLEDGE_MAX_UPLOAD_MB=50

KNOWLEDGE_DOCUMENT_ADMIN_USERNAME=admin
KNOWLEDGE_DOCUMENT_ADMIN_PASSWORD=drsp
```

### Sobre a indexação

A fila padrão é `sync`, então a indexação roda automaticamente no upload, cadastro manual e reprocessamento. Se um documento ficar em `indexing`, verifique o erro salvo no registro do documento, o binário `KNOWLEDGE_PYTHON_BIN`, a pasta `KNOWLEDGE_TMP_PATH` e as dependências Python.

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
2. `KNOWLEDGE_PYTHON_BIN` aponta para o Python com as dependências instaladas;
3. `KNOWLEDGE_TMP_PATH` existe ou pode ser criado pela aplicação;
4. as migrations foram executadas.

## Testes e validação

O projeto possui testes automatizados em `tests/Feature` cobrindo regras importantes da aplicação.

Testes principais:

- `tests/Feature/ChatControllerTest.php`
  - garante que o fallback sem streaming armazena fontes na sessão.
- `tests/Feature/DocumentAuthTest.php`
  - garante que `/documents` exige login;
  - valida login com `admin` / `drsp`;
  - rejeita credenciais incorretas.
- `tests/Feature/KnowledgeDocumentTest.php`
  - valida criação de documento com status `indexing`;
  - garante disparo do job `IndexKnowledgeDocument`;
  - testa validações HTTP de upload;
  - testa renovação de documento com mesmo nome e formato;
  - testa exclusão selecionada com senha correta;
  - testa validações HTTP da exclusão;
  - impede exclusão selecionada com senha incorreta.
- `tests/Feature/KnowledgeDocumentDatabaseTest.php`
  - valida a regra única de banco para `original_name + extension`.
- `tests/Feature/KnowledgeSearchServiceTest.php`
  - valida ambiente temporário do processo Python;
  - valida leitura de JSON mesmo quando o Python escreve saída extra;
  - valida formatação de contexto e fontes a partir dos mesmos resultados.

Rodar todos os testes:

```bash
php artisan test
```

Rodar testes pelo Composer:

```bash
composer test
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
- O processo Python é centralizado em `app/Services/Knowledge/KnowledgePythonProcess.php`.
- A indexação roda automaticamente com `QUEUE_CONNECTION=sync`.
- Ao trocar um documento por outro de mesmo nome e formato, o sistema remove o antigo e mantém o novo.
- O chat usa histórico recente para resolver referências de continuação, mas as respostas devem permanecer baseadas nos documentos recuperados quando houver contexto.
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
    KnowledgePythonProcess.php
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

tests/Feature/
  ChatControllerTest.php
  DocumentAuthTest.php
  KnowledgeDocumentDatabaseTest.php
  KnowledgeDocumentTest.php
  KnowledgeSearchServiceTest.php
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
OLLAMA_MODEL=qwen2.5:3b-instruct
```

Também é possível verificar a saúde do Ollama pela rota:

```text
/chat/health
```

### Modelo não encontrado no Ollama

Baixe o modelo configurado no `.env`:

```bash
ollama pull qwen2.5:3b-instruct
```

Ou altere `OLLAMA_MODEL` para um modelo já disponível em `ollama list`.

### Documento fica parado em `indexing`

Verifique se o Python configurado tem as dependências instaladas:

```bash
python -m pip install -r knowledge/requirements.txt
```

Confirme no `.env`:

```env
KNOWLEDGE_PYTHON_BIN=python
QUEUE_CONNECTION=sync
KNOWLEDGE_TMP_PATH=storage/app/private/knowledge/tmp
```

Verifique o erro salvo no registro do documento e os logs da aplicação.

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
2. o ChromaDB está em `storage/app/private/knowledge/chromadb`;
3. `KNOWLEDGE_SEARCH_LIMIT` está configurado;
4. as dependências Python foram instaladas.
