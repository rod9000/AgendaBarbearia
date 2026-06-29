# Evolution API v2.3.7 - Setup

## Requisitos
- Docker Desktop instalado

## Instalação no novo PC

1. Copie a pasta `docker/evolution/` para o novo PC
2. Execute:

```bash
cd docker/evolution
docker-compose up -d
```

3. Acesse o Manager: `http://localhost:8080/manager`
4. Configure no Laravel: `Admin → WhatsApp → Evolution API`
5. Conecte o WhatsApp via QR Code

## Arquivos
- `docker-compose.yml` - Stack completa (Redis + MongoDB + API)
- `.env` - Variáveis de ambiente
- `BACKUP_V1.8.7.md` - Backup da versão anterior

## Portas
- **8080** - Evolution API
- **8001** - Laravel (webhook)

## Variáveis de ambiente (.env)
| Variável | Descrição | Padrão |
|----------|-----------|--------|
| `EVOLUTION_API_KEY` | Chave da API | `changeme_super_secret_key_2026` |
| `SERVER_URL` | URL pública da API | `http://localhost:8080` |
| `WEBHOOK_URL` | URL do webhook Laravel | `http://host.docker.internal:8001/api/webhook/evolution` |
| `CONFIG_SESSION_PHONE_CLIENT` | Cliente da sessão | `Chrome` |
