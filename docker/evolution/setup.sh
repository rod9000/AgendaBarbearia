#!/usr/bin/env bash
set -euo pipefail

#!/usr/bin/env bash
# Evolution API - Setup Automático
set -euo pipefail

if ! command -v docker &>/dev/null; then
    echo "Instalando Docker..."
    curl -fsSL https://get.docker.com | bash
    sudo usermod -aG docker "$USER"
    echo "Faça logout/login para usar docker sem sudo."
fi

if [ ! -f .env ]; then
    cp .env.example .env
    KEY=$(openssl rand -hex 32 2>/dev/null || echo "evo_api_key_$(date +%s)")
    sed -i "s/sua_chave_secreta_aqui/$KEY/" .env
    echo "Chave secreta gerada em .env"
fi

docker compose up -d
echo "Evolution API rodando em http://localhost:8080"
