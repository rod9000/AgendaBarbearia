# Docker Setup - Agenda Barbearia

## Requisitos
- Docker Desktop instalado

## Instalação

1. Copie `.env.docker` para `.env`:
```bash
cp .env.docker .env
```

2. Suba os containers:
```bash
docker-compose up -d
```

3. Acesse:
- **App:** http://localhost:8001
- **Evolution API:** http://localhost:8080
- **Manager:** http://localhost:8080/manager

## Serviços

| Serviço | Porta | Descrição |
|---------|-------|-----------|
| Laravel | 8001 | Aplicação principal |
| MySQL | 3306 | Banco de dados |
| Redis | 6379 | Cache |
| Evolution API | 8080 | WhatsApp API |
| MongoDB | 27017 | Evolution API DB |

## Comandos Úteis

```bash
# Subir tudo
docker-compose up -d

# Ver logs
docker-compose logs -f

# Parar tudo
docker-compose down

# Rebuild
docker-compose up -d --build
```
