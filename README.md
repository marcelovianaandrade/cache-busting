# Cache Busting

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-21759B?logo=wordpress&logoColor=white)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![License: GPL v2](https://img.shields.io/badge/License-GPLv2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)

<p align="center">
  <a href="https://github.com/marcelovianaandrade/cache-busting/releases/latest/download/cache-busting.zip">
    <img src="https://img.shields.io/badge/⬇️_Baixar_última_versão-2271B1?style=for-the-badge&logo=wordpress&logoColor=white" alt="Baixar plugin">
  </a>
  &nbsp;
  <a href="https://github.com/marcelovianaandrade/cache-busting/releases">
    <img src="https://img.shields.io/badge/📦_Ver_releases-555?style=for-the-badge" alt="Ver releases">
  </a>
  &nbsp;
  <a href="https://github.com/marcelovianaandrade/cache-busting/stargazers">
    <img src="https://img.shields.io/badge/⭐_Star-FFC107?style=for-the-badge" alt="Star">
  </a>
</p>

Plugin WordPress leve para **forçar a atualização de CSS e JS nos navegadores** através de versionamento por query string. Oferece uma interface administrativa simples para gerenciar a versão e exibir o histórico da última atualização.

---

## ✨ Recursos

- 🎯 **Interface administrativa** em `Configurações → Cache Busting`
- ⚡ **Botão de atualização rápida** — incrementa a versão automaticamente seguindo o padrão `AAAA.MM.DD.N`
- ✏️ **Edição manual** com validação de formato por regex
- 📅 **Histórico visível** — mostra a versão atual, data/hora da última atualização e quem alterou
- 🔒 **Só afeta o próprio site** — URLs externas (CDNs, Google Fonts) são preservadas
- 🧩 **Compatível** com Elementor, WooCommerce e qualquer tema/plugin
- 🪶 **Leve** — arquivo único, sem dependências, sem JS no front-end

## 📦 Instalação

### Método 1: Upload via painel do WordPress

1. Baixe o ZIP em [Releases](../../releases) (ou clone este repo)
2. WordPress Admin → **Plugins → Adicionar novo → Enviar plugin**
3. Selecione `cache-busting.zip`
4. Clique em **Instalar agora** e depois em **Ativar plugin**

### Método 2: Manual via FTP/SFTP

1. Faça upload da pasta `cache-busting/` para `/wp-content/plugins/`
2. Ative o plugin em **Plugins → Plugins instalados**

## 🚀 Uso

Após ativar, acesse **Configurações → Cache Busting**.

A interface mostra dois cards:

- **Versão Atual** — versão em uso, data/hora da última atualização e o usuário responsável
- **Atualização Rápida** — sugestão da próxima versão e botão de incremento automático

Você também pode editar manualmente o número da versão. O formato exigido é:

```
AAAA.MM.DD.N
```

Exemplo: `2026.05.07.1` (primeira atualização de 7 de maio de 2026).

Incremente o `.N` para múltiplas atualizações no mesmo dia: `2026.05.07.2`, `2026.05.07.3`, etc.

## ⚙️ Como funciona

O plugin engancha nos filtros nativos do WordPress:

```php
add_filter( 'style_loader_src',  'cache_bust', 999 );
add_filter( 'script_loader_src', 'cache_bust', 999 );
```

E adiciona um parâmetro `?cv=VERSAO` ao final das URLs de CSS e JS — **apenas para arquivos servidos do próprio site**:

```
Antes:  /wp-content/themes/meu-tema/style.css?ver=6.7
Depois: /wp-content/themes/meu-tema/style.css?cv=2026.05.07.1
```

Quando a versão muda, o navegador trata como um novo recurso e baixa o arquivo novamente, ignorando o cache.

## 📋 Requisitos

- WordPress 5.0 ou superior
- PHP 7.2 ou superior

## 🛠️ Desenvolvimento

```bash
# Clone o repositório
git clone https://github.com/SEU-USUARIO/cache-busting.git

# Estrutura
cache-busting/
├── cache-busting.php   # Plugin principal
├── readme.txt          # Metadados WordPress.org
└── index.php           # Anti-listagem de diretório
```

Para gerar um ZIP instalável:

```bash
zip -r cache-busting.zip cache-busting/ -x "*.DS_Store" "*.git*"
```

## 📝 Changelog

### 1.0.0
- Lançamento inicial
- Interface administrativa com cards de versão atual e atualização rápida
- Filtros `style_loader_src` e `script_loader_src` aplicados a arquivos do próprio site
- Validação de formato `AAAA.MM.DD.N`
- Registro de data/hora e usuário responsável pela última atualização

## 📄 Licença

Este projeto está licenciado sob a **GPL v2 ou posterior** — veja o arquivo [LICENSE](LICENSE) para detalhes.

## 👤 Autor

**Marcelo Viana de Andrade**

---

Se este plugin foi útil, considere deixar uma ⭐ no repositório!
