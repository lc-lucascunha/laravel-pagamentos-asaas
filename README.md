## Versões utilizadas

- **PHP** (7.2.34)
- **LARAVEL** (7.30.6)
- **COMPOSER** (2.5.5)
- **NODE** (12.22.12)
- **NPM** (6.14.16)

## ASAAS - Automation for Software as a Service
Serviço utilizado para realizar a integração com os pagamentos por PIX, BOLETO e CARTÃO DE CRÉDITO.

Produção: https://www.asaas.com/

Homologação: https://sandbox.asaas.com/

## Instalação e Configuração

Clone o repositório em uma pasta
```
git clone https://github.com/lc-lucascunha/laravel-pagamentos-asaas.git
```

Acesse a pasta do projeto
```
cd laravel-pagamentos-asaas/
```

Instale as dependência
```
composer install
```

Faça uma cópia do arquivo de configuração
```
cp .env.example .env
```

Após criar a DATABASE, abra o arquivo .env e definida as configurações da base de dados
```
nano .env
```
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```
Defina também no .env a URL do AMBIENTE e a API KEY gerada na ASAAS
```
ASAAS_DOMAIN=https://sandbox.asaas.com
ASAAS_KEY=
```

Gere uma chave para a sua aplicação
```
php artisan key:generate
```

Execute a migração da base de dados
```
php artisan migrate
```

Não se esqueça de definir as permissões (caso necessário) nas pasta /bootstrap, /storage, /database.

## Iniciando a aplicação

```
php artisan serve
```



