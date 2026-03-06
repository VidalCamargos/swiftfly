# SwiftFly API (Laravel + JWT)

API REST para gerenciamento de pedidos de viagem com autenticação JWT.

## Sobre o projeto

O SwiftFly é uma API REST desenvolvida em Laravel 12 com PHP 8.4, projetada para gerenciar pedidos de viagem corporativa. A aplicação utiliza autenticação JWT (JSON Web Tokens) e permite que usuários criem, listem e atualizem pedidos de viagem, com autorização baseada em políticas do Laravel (apenas administradores podem aprovar/cancelar solicitações).

### Funcionalidades principais

- **Autenticação JWT**: Registro de usuários com suporte a códigos de administrador
- **Gestão de pedidos de viagem**: CRUD completo para solicitações
- **Autorização por permissões**: Usuários comuns podem ver seus próprios pedidos; apenas admins podem atualizar status
- **Filtros avançados**: Busca por status, destino e intervalos de datas (ida e volta)
- **Testes automatizados**: Suíte de testes PHPaint para garantir qualidade
- **Documentação Swagger**: OpenAPI 3.0 completo com exemplos

## Requisitos

- Git
- Docker Engine + Docker Compose

Portas usadas localmente:

- API (nginx): 80
- Swagger UI: 3000
- MySQL: 3306
- Redis: 6379
- Mailcatcher (SMTP): 1025
- Mailcatcher (UI): 1080

## Stack

- PHP 8.4 + Laravel 12
- MySQL 8
- Redis
- JWT (tymon/jwt-auth)
- Spatie/Laravel Query Builder (filtros e ordenação)
- Swagger UI (docker)

## Setup rápido (Docker)

1) Clone o repositório

```bash
git clone git@github.com:VidalCamargos/swiftfly.git
cd swiftfly
```

2) Suba os containers

```bash
docker compose up -d --build
```

3) Teste se a API está respondendo

```bash
curl http://api.swiftfly.localhost/v1/ping
# Deve retornar: "pong"
```

Na primeira inicialização, o container `api` roda uma automação (`provision/scripts/entrypoint.sh`) que:

- Copia `.env.example` → `.env` (se ainda não existir)
- Instala dependências com `composer install`
- Gera chave da aplicação com `php artisan key:generate`
- Gera segredo JWT com `php artisan jwt:secret`
- Executa `php artisan migrate:fresh --seed` para criar o banco e popular dados

Para acompanhar os logs:

```bash
docker compose logs -f api
```

## URLs

- API: `http://api.swiftfly.localhost/v1`
- Swagger UI: `http://localhost:3000` (especificação em `openapi.yml`)
- Mailcatcher UI: `http://localhost:1080` (para testar envio de emails)

## Usuário inicial

Após o `migrate:fresh --seed`, o projecto cria um usuário administrador com acesso pré-configurado:

- **Email**: `admin@example.com`
- **Senha**: `Password123!`
- **Perfil**: Administrador

Faça login com ele para começar a usar a API:
```bash
curl -s -X POST "http://api.swiftfly.localhost/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"Password123!"}'
```

## Filas (Queues)

O projeto usa **Redis** como driver de filas (`QUEUE_CONNECTION=redis` no `.env`). Para consumir jobs em produção ou local:

```bash
docker compose exec api php artisan queue:work
```

Caso queira processamento síncrono (útil para desenvolvimento), altere a variável de ambiente:
```bash
QUEUE_CONNECTION=sync
```

## Coleção de requisições (Insomnia)

Para facilitar o teste, foi disponibilizada uma coleção de requisições completa do Insomnia:
- **Arquivo**: `provision/requests insomnia/swiftfly-insomnia.yaml`
- Conteúdo: Todos os endpoints de autenticação e pedidos de viagem
- Variáveis de ambiente já configuradas: `base_url` e `token`

Importe-o diretamente no Insomnia e comece a testar sem precisar configurar nada adicional.

## Como listar rotas

```bash
docker compose exec api php artisan route:list
```

## Autenticação (JWT)

A API utiliza autenticação JWT para acesso às rotas. Veja os endpoints, payloads e exemplos completos na documentação Swagger.
 
Após login/registro, use o token no header:

```text
Authorization: Bearer <token>
```

**Nota**: Para criar usuário administrador, envie o parâmetro `admin_code` (padrão no `.env.example`: `ADMIN_CODE=swiftfly-admin`).

## Pedidos de viagem (Travel Orders)

Todos os endpoints, filtros, schemas e exemplos estão documentados no Swagger: `http://localhost:3000`

### Fluxo de uso

1. Faça login ou registro na API usando os endpoints `/auth/login` ou `/auth/register`
2. Utilize o token JWT retornado nos headers `Authorization: Bearer <token>`
3. Crie pedidos de viagem com dados de destino e datas
4. Liste e filtre seus pedidos por status, destino e intervalos de datas
5. Apenas administradores podem aprovar ou cancelar pedidos

## Rodar testes

```bash
docker compose exec api php artisan test
```

## Qualidade de código e refatoração

O projeto utiliza **Rector** (refatoração automatizada) e **ECS** (EasyCodingStandard - PSR-12 + Laravel) para manter a qualidade e consistência do código.

### Rector

Reforça melhores práticas e moderniza o código (PHP 8.4, Laravel 12).

```bash
docker compose exec api vendor/bin/rector
```

Para ver o que será modificado sem aplicar as mudanças:

```bash
docker compose exec api vendor/bin/rector --dry-run
```

### ECS (EasyCodingStandard)

Aplica PSR-12 e regras específicas de Laravel (formato curto de arrays, 120 caracteres por linha, etc.).

```bash
docker compose exec api vendor/bin/ecs --fix
```

Para verificar sem modificar:

```bash
docker compose exec api vendor/bin/ecs
```

## Troubleshooting

- Recriar banco/seed:

```bash
docker compose exec api php artisan migrate:fresh --seed
```

- Para repetir a automação de inicialização, remova o arquivo de controle e reinicie o container:

```bash
rm -f .docker_setup_completed
docker compose restart api
```
