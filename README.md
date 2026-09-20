# ShopBR — Documentação do Projeto

## O que é o ShopBR?

O **ShopBR** é um marketplace online — um site de compras onde qualquer pessoa pode se cadastrar como **vendedor** e anunciar seus produtos, enquanto outros usuários navegam, buscam e adicionam itens ao carrinho como **compradores**.

Pense nele como um shopping virtual: há lojistas que montam suas vitrines e clientes que passeiam pelos corredores digitais, escolhendo o que comprar.

---

## Para quem é este projeto?

O ShopBR foi desenvolvido como **Trabalho de Conclusão de Curso (TCC)**. O objetivo é demonstrar na prática como funciona um sistema web completo, com cadastro de usuários, gerenciamento de produtos, sistema de ofertas e carrinho de compras.

---

## Funcionalidades

### Para compradores (visitantes que criam uma conta)

- **Página inicial** — exibe as categorias disponíveis e as melhores ofertas do momento (os produtos mais visualizados com promoção ativa).
- **Busca de produtos** — campo de pesquisa no topo da página para encontrar produtos por nome ou descrição, com filtro lateral por categoria e paginação dos resultados.
- **Navegação por categoria** — cada categoria leva a uma lista dos produtos daquele tipo.
- **Comparação de produtos** — permite selecionar dois ou mais produtos para comparar lado a lado.
- **Carrinho de compras** — o carrinho fica salvo na conta do usuário. É possível adicionar produtos, ajustar a quantidade de cada item ou removê-los.
- **Login e cadastro** — acesso seguro com e-mail e senha.

### Para vendedores

- **Cadastro de vendedor** — além da conta normal, o vendedor informa o nome da loja e CPF/CNPJ.
- **Dashboard (painel do vendedor)** — tela exclusiva com um resumo da loja:
  - Total de produtos cadastrados
  - Quantos produtos estão ativos
  - Quantas ofertas estão ativas no momento
  - Total de visualizações acumuladas nos produtos
- **Gerenciar produtos** — o vendedor pode cadastrar novos produtos (nome, categoria, preço, estoque, descrição e imagem), ativar/desativar produtos existentes e excluí-los.
- **Criar ofertas** — para qualquer produto ativo, o vendedor pode definir um percentual de desconto e uma data de validade. O sistema calcula e exibe automaticamente o preço com desconto.
- **Editar perfil** — atualizar informações pessoais e da loja.

---

## Categorias disponíveis

O sistema já vem com 8 categorias pré-configuradas:

| Categoria      | Ícone |
|----------------|-------|
| Eletrônicos    | 📱    |
| Roupas         | 👗    |
| Casa e Jardim  | 🏠    |
| Esportes       | ⚽    |
| Livros         | 📚    |
| Beleza         | 💄    |
| Brinquedos     | 🧸    |
| Informática    | 💻    |

---

## Como o banco de dados é organizado

O banco de dados é chamado **shopbr** e guarda todas as informações do sistema em tabelas relacionadas entre si. Veja abaixo o que cada tabela armazena e como elas se conectam.

### Tabela `usuarios`
Guarda todos os usuários do sistema — tanto compradores quanto vendedores. Cada pessoa tem um tipo (`comprador` ou `vendedor`) associado à sua conta.

| Campo          | O que guarda                              |
|----------------|-------------------------------------------|
| `id`           | Número único de identificação             |
| `nome`         | Nome completo do usuário                  |
| `email`        | E-mail (único — não pode repetir)         |
| `senha_hash`   | Senha protegida (nunca armazenada em texto puro) |
| `tipo`         | Se é `comprador` ou `vendedor`            |
| `criado_em`    | Data e hora do cadastro                   |

---

### Tabela `vendedores`
Quando um usuário se cadastra como vendedor, esta tabela guarda as informações extras da loja. Cada vendedor está vinculado a exatamente um usuário.

| Campo        | O que guarda                              |
|--------------|-------------------------------------------|
| `usuario_id` | Qual usuário é esse vendedor              |
| `nome_loja`  | Nome da loja no marketplace               |
| `cpf_cnpj`   | CPF ou CNPJ do vendedor (único)           |
| `telefone`   | Telefone de contato                       |
| `ativo`      | Se a loja está ativa (`1`) ou não (`0`)   |

---

### Tabela `categorias`
Lista fixa de categorias. Cada produto precisa pertencer a uma delas.

| Campo   | O que guarda                        |
|---------|-------------------------------------|
| `slug`  | Identificador na URL (ex: `roupas`) |
| `nome`  | Nome exibido na tela                |
| `icone` | Emoji da categoria                  |

---

### Tabela `produtos`
Cada produto anunciado no marketplace fica aqui. Todo produto pertence a um vendedor e a uma categoria.

| Campo          | O que guarda                              |
|----------------|-------------------------------------------|
| `vendedor_id`  | Quem está vendendo o produto              |
| `categoria_id` | Em qual categoria o produto se enquadra   |
| `nome`         | Nome do produto                           |
| `descricao`    | Texto explicativo sobre o produto         |
| `preco`        | Preço normal (sem desconto)               |
| `estoque`      | Quantidade disponível                     |
| `imagem_url`   | Endereço da foto do produto               |
| `ativo`        | Se o produto está visível (`1`) ou não (`0`) |
| `visualizacoes`| Quantas vezes o produto apareceu em buscas |

---

### Tabela `ofertas`
Registra as promoções criadas pelos vendedores. Uma oferta tem prazo de validade — quando a data passa, ela deixa de ser exibida automaticamente.

| Campo          | O que guarda                                    |
|----------------|-------------------------------------------------|
| `produto_id`   | Qual produto está em promoção                   |
| `desconto_pct` | Percentual de desconto (ex: `20.00` = 20% off)  |
| `preco_oferta` | Preço já calculado com o desconto aplicado      |
| `data_inicio`  | Quando a oferta começou                         |
| `data_fim`     | Quando a oferta termina                         |
| `ativa`        | Se a oferta está ligada (`1`) ou desligada (`0`)|

---

### Tabela `carrinho_itens`
Guarda os itens que cada comprador adicionou ao seu carrinho. O carrinho é persistente — se o usuário fechar o navegador e voltar mais tarde, os itens ainda estarão lá.

| Campo          | O que guarda                              |
|----------------|-------------------------------------------|
| `usuario_id`   | De quem é esse carrinho                   |
| `produto_id`   | Qual produto foi adicionado               |
| `quantidade`   | Quantas unidades foram escolhidas         |
| `adicionado_em`| Quando o item entrou no carrinho          |

> Um usuário não pode ter o mesmo produto duas vezes no carrinho — se ele clicar em "Adicionar" de novo, a quantidade é atualizada.

---

## Diagrama de relacionamento

```
usuarios (1) ──── (1) vendedores
    │
    └────── (1 para muitos) carrinho_itens

vendedores (1) ──── (muitos) produtos
    
categorias (1) ──── (muitos) produtos

produtos (1) ──── (muitos) ofertas
produtos (1) ──── (muitos) carrinho_itens
```

Em palavras simples:
- Um usuário pode ser vendedor (mas não obrigatoriamente).
- Um vendedor pode ter vários produtos.
- Cada produto pertence a uma categoria.
- Um produto pode ter várias ofertas ao longo do tempo (mas só uma ativa por vez é exibida).
- Um comprador pode ter vários produtos no carrinho.

---

## Páginas do sistema

| Arquivo                  | O que faz                                             |
|--------------------------|-------------------------------------------------------|
| `index.php`              | Página inicial — categorias e ofertas do dia          |
| `login.php`              | Tela de login                                         |
| `busca.php`              | Resultados de pesquisa com filtros                    |
| `categoria.php`          | Produtos de uma categoria específica                  |
| `carrinho.php`           | Carrinho de compras do usuário logado                 |
| `comparar.php`           | Comparação lado a lado de produtos                    |
| `cadastro-vendedor.php`  | Formulário para se tornar vendedor                    |
| `dashboard-vendedor.php` | Painel de controle do vendedor                        |
| `perfil-vendedor.php`    | Edição do perfil e dados da loja                      |
| `sobre.php`              | Página institucional "Sobre nós"                      |

---

## Como instalar e rodar localmente

O projeto roda em um servidor PHP com banco de dados MySQL — o ambiente mais comum para isso é o **XAMPP**.

### Pré-requisitos

- [XAMPP](https://www.apachefriends.org/) instalado (inclui Apache + PHP + MySQL)

### Passo a passo

1. **Copie a pasta do projeto** para dentro de `C:\xampp\htdocs\` (no Windows) ou `/opt/lampp/htdocs/` (no Linux).

2. **Inicie o XAMPP** e ligue os serviços **Apache** e **MySQL**.

3. **Crie o banco de dados**: abra o navegador, acesse `http://localhost/phpmyadmin`, vá em *Importar* e selecione o arquivo `database.sql` que está na raiz do projeto. Clique em *Executar*.

4. **Acesse o site**: abra o navegador e vá para `http://localhost/projeto-tcc-main/`.

> As configurações de conexão com o banco ficam no arquivo `config/db.php`. Por padrão, o usuário é `root` sem senha — o padrão do XAMPP.

---

## Tecnologias utilizadas

| Camada       | Tecnologia                          |
|--------------|-------------------------------------|
| Interface    | HTML5, CSS3, JavaScript             |
| Servidor     | PHP 7.4+                            |
| Banco de dados | MySQL 5.7+ / MariaDB 10.3+        |
| Ambiente local | XAMPP                             |

---

*Projeto desenvolvido como Trabalho de Conclusão de Curso.*
