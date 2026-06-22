# ADR 001: Implantação do Sistema Legado sob Subdiretório (/antigo) no Apache

## Status
Aprovado

## Contexto
Precisamos rodar a versão legada do `smart-app` (esta branch) no mesmo servidor de hospedagem que serve o sistema atual.
Devido a restrições operacionais (apenas acesso via FTP e incapacidade de criar subdomínios via painel ou DNS), a única solução viável é servir o sistema sob a URL `smart4bts.com.br/antigo`.

Nas tentativas anteriores de configuração, o redirecionamento do `.htaccess` gerava loops infinitos e erros 500, impossibilitando o funcionamento do sistema legado ou o isolamento correto dos arquivos públicos.

## Decisão
1. **Redirecionamento Simplificado no `.htaccess` da Raiz:** 
   Substituímos as regras complexas por um redirecionamento limpo que insere `/public` na frente de qualquer URI que não inicie com `public/`. Isso delega a checagem de existência de arquivos reais e o roteamento interno ao `.htaccess` nativo do Laravel localizado em `public/.htaccess`.
   
2. **Ajuste de Roteamento no `public/index.php`:**
   Mantemos as definições manuais de variáveis globais em [public/index.php](file:///f:/Projetos/4BTS/smart-app/public/index.php):
   ```php
   $_SERVER['SCRIPT_NAME'] = '/antigo/index.php';
   $_SERVER['PHP_SELF'] = '/antigo/index.php';
   ```
   E forçamos a URL raiz do Laravel no boot do container:
   ```php
   $app->booted(function () {
       url()->forceRootUrl(rtrim(config('app.url'), '/') . '/antigo');
   });
   ```
   Isso engana o router do Laravel e o gerador de URLs para entenderem que o sistema está em `/antigo` (e não em `/antigo/public`), gerando links e redirecionamentos corretos no frontend.

3. **Exceção no `.htaccess` do Sistema Principal (Raiz da Hospedagem):**
   Para evitar que o sistema atual intercepte rotas virtuais pertencentes ao `/antigo/`, deve ser adicionada a seguinte linha no arquivo `.htaccess` principal (localizado na pasta raiz de hospedagem, ex: `public_html/`):
   ```apache
   RewriteCond %{REQUEST_URI} !^/antigo
   ```
   Esta linha deve preceder as regras de Front Controller do sistema principal.

## Consequências
* **Segurança Aumentada:** O acesso direto a arquivos sensíveis na raiz do `/antigo` (como `.env`, `composer.json`, etc.) é automaticamente bloqueado ou reescrito para o escopo de `public/` (onde o arquivo não existe, resultando em 404), eliminando o risco de exposição.
* **Isolamento de Rotas:** Ambas as aplicações rodam de forma independente no mesmo servidor web sem conflitos de redirecionamento ou loops infinitos de reescrita interna no Apache.
* **Complexidade FTP:** O deploy requer o upload físico da pasta `/antigo` contendo todo o projeto estruturado. Caso o domínio aponte direto para a pasta `public/` do sistema principal no servidor, a pasta física `antigo` deverá ser colocada dentro dessa mesma pasta `public/`.
