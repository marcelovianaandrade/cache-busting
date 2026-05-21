=== Cache Busting ===
Contributors: marcelovianaandrade
Tags: cache, cache busting, css, js, versioning, browser cache
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.2
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Força a atualização de CSS e JS nos navegadores através de versionamento por query string. Interface simples para gerenciar a versão.

== Description ==

Plugin leve que adiciona um parâmetro `?cv=VERSAO` ao final de todas as URLs de CSS e JS do seu site, forçando os navegadores a baixar novamente os arquivos sempre que você atualizar a versão.

**Recursos:**

* Interface administrativa em **Configurações → Cache Busting**
* Botão de "Atualização Rápida" que incrementa a versão automaticamente seguindo o padrão `AAAA.MM.DD.N`
* Edição manual com validação de formato
* Exibe a versão atual, data/hora da última atualização e quem alterou
* Apenas arquivos do próprio site são versionados (URLs externas como CDNs e Google Fonts são preservadas)
* Compatível com Elementor, WooCommerce e qualquer tema/plugin

== Installation ==

1. Faça upload do arquivo ZIP em **Plugins → Adicionar novo → Enviar plugin**
2. Ative o plugin
3. Acesse **Configurações → Cache Busting** para gerenciar a versão

== Frequently Asked Questions ==

= Como faço para forçar uma atualização? =

Acesse **Configurações → Cache Busting** e clique em "Forçar Atualização Agora", ou edite o número manualmente.

= O plugin afeta arquivos de CDNs externos? =

Não. Apenas arquivos servidos do seu próprio domínio (URL do site) recebem o parâmetro de versão.

= Qual é o formato da versão? =

`AAAA.MM.DD.N` — por exemplo: `2026.05.07.1`. Incremente o `.N` para múltiplas atualizações no mesmo dia.

== Changelog ==

= 1.0.0 =
* Lançamento inicial
* Interface administrativa com cards de versão atual e atualização rápida
* Filtros `style_loader_src` e `script_loader_src` aplicados a arquivos do próprio site
* Validação de formato AAAA.MM.DD.N

## 📸 Screenshots

![Tela principal](assets/screenshot-1.png)
*Interface administrativa com cards de versão atual e atualização rápida*

