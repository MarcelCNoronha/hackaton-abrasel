# Project Context

Documento de referência operacional do projeto. Deve refletir o estado atual de implementação.

## Objetivo do produto

Plataforma local de descoberta de restaurantes (mapa, busca, cardápio digital, avaliações com visita verificada por QR Code e cupons de fidelização), sem foco em delivery. Ver `docs/SPEC.md`.

## Nome e domínio

- nome do produto: **VicosaFood** (`config('app.name')` / `APP_NAME`, compartilhado ao frontend via Inertia como `appName` -- nunca hardcoded em componentes, para renomear em um único lugar);
- domínio de produção: `vicosa.tech`; a estratégia é reservar o nome para expansão por segmento como subdomínios (ex.: um futuro `VicosaService` para outros tipos de serviço local), então o app deste repositório deve ficar isolado como o serviço de gastronomia (`food.vicosa.tech` ou equivalente), sem acoplar lógica de outros segmentos aqui;
- identificadores internos de infraestrutura (nome da imagem Docker `saborlocal-app`, banco/usuário Postgres `saborlocal`/`saborlocal_test`) ainda usam o nome de trabalho anterior -- são cosméticos e não expostos ao usuário; renomear exige recriar containers/volumes, então ficou de fora desta rodada por não valer o risco agora. Renomear quando for conveniente (ex.: próxima vez que os volumes forem recriados de qualquer forma).

## Stack oficial

Ver `docs/STACK_DECISIONS.md` (reaproveitada do projeto Mundial).

## Status funcional atual

Implementado:

- ambiente local via Docker Compose (Postgres 16 + Redis 7), portas remapeadas para `5435`/`6380` para não colidir com outros projetos na máquina;
- Laravel 13 + Breeze (stack `vue`) + Inertia + Vuetify funcionando lado a lado com Tailwind;
- modelo de dados completo do domínio (26 migrations): restaurantes, reivindicação/gestão, categorias/cuisines/`dietary_tags` (dietas e alergias/intolerâncias), cardápio (menus/categorias/itens/fotos), favoritos, `qr_tokens`+`visits` para visita verificada, `reviews`+respostas+fotos, `coupon_campaigns`/`coupons`/`coupon_redemptions`, `events` para telemetria do funil;
- camada de models Eloquent completa (~20 models, 7 enums em `App\Enums`) com as regras de negócio centrais já encapsuladas: `Restaurant::scopeWithinDistance` (haversine em SQL), `QrToken::isValid()`, `Visit::isReviewable()`, `CouponCampaign::isOpenForIssuance()` (nunca depende da nota da review — regra de imparcialidade da spec §18);
- seeders de dados de referência (`CategorySeeder`, `CuisineSeeder`, `DietaryTagSeeder`) plugados no `DatabaseSeeder`, mais um usuário admin e um consumidor de teste;
- autenticação e perfis de acesso: cadastro público sempre cria `role=consumer`; middleware `role:owner`/`role:admin` (alias `App\Http\Middleware\EnsureUserRole`) protegendo `/gestor` e `/admin`; `RestaurantPolicy::manage()` checando o pivot `restaurant_owners` para autorização granular por estabelecimento; navegação condicional por role no `AuthenticatedLayout.vue`; painéis-placeholder `Owner/Dashboard.vue` e `Admin/Dashboard.vue` já renderizando com componentes Vuetify reais;
- suíte de testes (`tests/Feature/RoleAccessTest.php`, `tests/Feature/DiscoveryTest.php`) cobrindo os 3 perfis + guest e a descoberta pública, rodando via PHPUnit (não há Pest instalado neste projeto) contra um banco `Postgres` de teste real (`saborlocal_test`) -- ver `docs/STACK_DECISIONS.md`;
- **descoberta pública** (`/`, `DiscoveryController`, página `Discover/Index.vue`): mapa Leaflet com marcadores customizados, geolocalização do navegador (com fallback caso o usuário negue), busca textual (nome do restaurante ou prato), e filtros combináveis -- tipo de estabelecimento, tipo de comida, restrição alimentar (exige 1 prato que atenda a TODAS as tags marcadas), distância, faixa de preço, avaliação mínima e "aberto agora" (`Restaurant::scopeOpenNow`, `Restaurant::isOpenAt`). Acessível sem login, como manda a spec (visitante);
- **perfil do estabelecimento** (`/restaurantes/{slug}`, `RestaurantController@show`, página `Restaurants/Show.vue`): dados comerciais, cardápio completo com tags de restrição alimentar por prato, horário de funcionamento, avaliações publicadas e botão "Como chegar" (link direto pro Google Maps, sem precisar de API key);
- `RestaurantSeeder` com 6 estabelecimentos de demonstração (coordenadas reais em torno do centro de Viçosa/MG, `-20.7546, -42.8825`, próximo à UFV), cada um com cardápio, horário de funcionamento e ao menos um prato com tags de restrição alimentar -- inclui um estabelecimento (`Veggie Green Bowl`) pensado especificamente pra exercitar o filtro combinado vegano+sem-glúten. Nomes e dados são fictícios (não representam estabelecimentos reais de Viçosa), só a geografia é real -- ver `docs/EDITAL_ABRASEL.md`.

## Hackathon ABRASEL (contexto do edital)

- projeto é a candidatura ao **Hackathon ABRASEL – Alimentação Fora do Lar em Cidades Inteligentes** (Edital CENTEV/UFV 10/2026), evento presencial em Viçosa/MG de 21 a 23/08/2026;
- desafio principal escolhido: **#1 Rota Gastronômica Inteligente de Viçosa**, combinando elementos do **#8 Cardápio Inteligente e Inclusivo** (filtro de restrição alimentar) e do **#19 Monitoramento da Satisfação do Cliente** (avaliações verificadas);
- resumo completo do edital, critérios de avaliação e gaps de compatibilidade identificados: ver `docs/EDITAL_ABRASEL.md`.

Não implementado ainda (ver `docs/SPEC.md` para o escopo completo):

- fluxo de visita verificada por QR Code (geração/rotação no painel do gestor, leitura no cliente);
- fluxo de avaliação + emissão automática de cupom ao concluir a review;
- painel do gestor (CRUD de estabelecimento, cardápio, fotos, campanhas de cupom) além do placeholder atual;
- painel de administração (moderação, aprovação de reivindicações) além do placeholder atual;
- como o usuário vira `role=owner` na prática (hoje só é possível via seeder/tinker — falta o fluxo de `RestaurantClaim` aprovada promovendo o usuário, ou uma tela de promoção manual pelo admin).

## Rotas principais

- `/` (`discover`) — descoberta pública: mapa, busca e filtros, sem exigir login;
- `/restaurantes/{slug}` (`restaurants.show`) — perfil público do estabelecimento e cardápio;
- `/dashboard` — área do consumidor autenticado (qualquer role);
- `/gestor` (`owner.dashboard`) — painel do estabelecimento, exige `role:owner` (admin também acessa);
- `/admin` (`admin.dashboard`) — administração, exige `role:admin`;
- rotas de auth padrão do Breeze (`/login`, `/register`, `/forgot-password` etc.).

## Ambiente local

- `docker compose up -d db redis` sobe Postgres/Redis; `php artisan serve` + `npm run dev` rodam nativos para loop rápido;
- usuários seed: `admin@vicosafood.test` (role admin), `gestor@vicosafood.test` (role owner) e `test@example.com` (role consumer) — senha padrão da factory (`password`).

## Regra de manutenção da documentação

Qualquer alteração relevante de arquitetura, rotas, modelo de dados ou regra de negócio deve atualizar este arquivo, em conjunto com `docs/ARQUITETURA_BASE.md` e, se a stack mudar, `docs/STACK_DECISIONS.md`.
