# DEPLOY do Projeto AURORA Regmel

O Makefile automatiza várias etapas do processo de configuração e manutenção do Aurora usando Docker. Porém muitos dos comandos não podem ser executados em produção.


## Comandos disponíveis

Para ver a lista de comandos disponiveis [acesse aqui](./COMMANDS.md)

---

Siga o passo a passo abaixo para fazer o deploy:

## Passo a passo

<details>
<summary>Clonar a aplicação</summary>

### `clone`

Faça o clone da aplicação

```shell
git clone https://github.com/ecossistema-aurora/regmel
```

ou 

```shell
git clone git@github.com:ecossistema-aurora/regmel.git
```

### `branch`

O branch da produção deverá ser o `production`

```shell
git checkout production
```

</details>



<details>
<summary>Instalar/Preparar a Aplicação</summary>

### `env`

Copie o arquivo `.env.example`, o novo arquivo terá as configurações de acesso a aplicação, servidor de email, e tipo de ambiente

```shell
cp .env.example .env
```

### `setup`

Precisamos agora criar os bancos de dados, tabelas, dados, instalar dependências e tudo o mais, para isso basta executar:

```shell
make setup
```

### `regmel`

Para a aplicação REGMEL há um comando que cria um conjunto de dados necessários para o processo de cadastro dos Municipios e Empresas, basta executar:

```shell
make demo-regmel
```
</details>

<details>
<summary>Pós Instalação (Importante)</summary>

### `env`

Após a instalação precisamos configurar o arquivo `.env`:

- **linha 18:** Alterar para `APP_ENV=prod`
- **linha 55:** Configurar de acordo com o serviço de email

</details>

<details>
<summary>Atualização do ambiente (Quando houver novas versões)</summary>

### `pull`

Atualizar o branch
```shell
git pull origin production
```

### `banco de dados`

Atualizar o banco de dados (tabelas)
```shell
make migrate_database
```

### `assets`

Compilar o CSS/Javascript
```shell
make compile_frontend
```
</details>
