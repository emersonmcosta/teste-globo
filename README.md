# 📦 Projeto API Mensagens

Projeto desenvolvido com o framework Laravel para receber, processar e reprocessar mensagens de forma assíncrona, com suporte a reprocessamento automático, log de erros, e um histórico completo de processamento.

---

## 🚀 Tecnologias Utilizadas

- PHP >= 8.x
- Laravel >= 10.x
- Composer
- MySQL
- Swagger
- LogViewer
- Docker (opcional)
- PHPUnit (para testes)

---

## ⚙️ Configuração do Ambiente

### 1. Clone o repositório

```bash
git clone https://github.com/emersonmcosta/teste-globo.git
cd teste-globo
```

### 2. Rodar script de instalação ( faz todo processo automático )

```bash
chmod +x configProj.sh
sh configProj.sh
```

Em caso de falha, executar os passos manualmente:

```bash
    docker-compose up -d --build --force-recreate
    docker exec -i api-messages cp -r .env.example .env
    docker exec -i api-messages composer install
    docker exec -i api-messages chmod 777 -R storage
```

### 4. Gere a chave da aplicação

```bash
    docker exec -i api-messages php artisan key:generate
```

### 5. Execute as migrations

```bash
    docker exec -i api-messages php artisan queue:table 
    docker exec -i api-messages php artisan migrate
```

### 6. Execute a aplicação

```bash
    docker exec -i api-messages php artisan queue:work &
```

---

## 🧪 Testes

Execute os testes com:

```bash
    docker exec -i api-messages php artisan test --filter=MessageControllerTest  --stop-on-failure 
    docker exec -i api-messages php artisan test --filter=ProcessarMessageTest  --stop-on-failure 
```
---

## 📁 Estrutura do Projeto

- `app/Http/Controllers` - Controladores da aplicação.
- `app/Models` - Modelos Eloquent.
- `routes/api.php` - Rotas da API.
- `app/Jobs` - Jobs e filas (como ProcessarMessage).
- `app/Http/Requests` - Validações de requisição.
- `app/Http/Resources` - Transformações de resposta JSON.
- `database/migrations` - Migrations do banco de dados.

---

## 💡 Funcionalidades

- Envio e processamento assíncrono de mensagens com filas.
- Validação e formatação de mensagens.
- API RESTful implementando padrão MVC com conceitos de SOLID e TDD.
- Simulação de fila RabbitMQ.
- Histórico de processamento.

---

## 🛠️ Melhorias Futuras

- [ ] Adicionar autenticação via Passport ou Sanctum.
- [ ] Melhorar cobertura de testes (unitários e integração).
- [ ] Documentação Swagger interativa via `l5-swagger`.
- [ ] Monitoramento e dashboard para filas (Laravel Horizon).
- [ ] Deploy automatizado (CI/CD com GitHub Actions ou GitLab CI).
- [ ] Caching avançado com Redis.

---

## 📄 Documentação da API

Swagger:

Acesse via: `http://localhost/public/api/documentation`  

## 📄 Logs

Log-viewer:

Acesse via: `http://localhost/public/log-viewer`  

---


## 🤝 Contribuições

Contribuições são bem-vindas! Sinta-se à vontade para abrir *issues*, *pull requests*, ou sugerir melhorias.

---

## 📜 Licença

Este projeto está licenciado sob a Licença MIT.

---

## 👨‍💻 Autor

Desenvolvido por Emerson Costa - emersonm32@gmail.com 

---
