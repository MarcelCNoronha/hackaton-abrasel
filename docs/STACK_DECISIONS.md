# Stack Decisions

## Stack Oficial Fechada

Reaproveitada da stack validada no projeto Mundial (`docs/STACK_DECISIONS.md` do repositório `mundial`).

- Backend: `Laravel 13`
- Frontend: `Vue 3`
- Integração web: `Inertia.js`
- UI: `Vuetify`
- Linguagem backend: `PHP 8.4`
- Runtime frontend: `Node.js 22+` (imagem Docker usa `node:24-alpine`)
- Banco: `PostgreSQL 16`
- Cache e filas: `Redis 7`
- CI/CD: `GitHub Actions` (a configurar)
- Orquestração: `Docker Compose`
- Reverse proxy: `Nginx`

## Decisões Confirmadas

- usar `Laravel + Vue 3 + Inertia.js`;
- usar `Vuetify` como biblioteca oficial de UI (convive com Tailwind, que o Breeze já traz — Tailwind cuida do layout/utilitários de página, Vuetify dos componentes ricos: mapa, filtros, cards, formulários);
- usar `Docker Compose` como padrão de ambiente local e produção;
- usar `PostgreSQL` como banco principal (extensão `postgis` a avaliar quando a busca geográfica precisar de índices espaciais reais; MVP inicial calcula distância via fórmula haversine em SQL);
- usar `Redis` para cache, sessão e filas;
- manter backend e painel (consumidor + gestor + admin) no mesmo repositório monólito modular, como no Mundial.

## Ferramentas Complementares

- autenticação inicial: `Laravel Breeze` (stack `vue`, com Inertia);
- geração de QR Code: `simplesoftwareio/simple-qrcode` (ou `endroid/qr-code` direto) para os tokens de visita/cupom;
- leitura de QR no cliente: biblioteca JS de leitura de câmera (ex. `qr-scanner`), decisão a confirmar ao implementar o fluxo de visita;
- mapa: `Leaflet` (open source, sem custo de API key) como padrão inicial; Google Maps é opção futura se for necessário Street View/Places;
- qualidade de código: `Laravel Pint`;
- testes backend: `PHPUnit 12` via Laravel test runner, rodando contra um banco `Postgres` real (`saborlocal_test`, no mesmo container local) em vez de `sqlite` -- a busca de descoberta usa SQL específico de Postgres (`ilike`, fórmula haversine com `acos/radians/sin/cos`) que não roda em `sqlite`, então testar em `sqlite` mascararia justamente a lógica mais crítica da feature;
- o mapa (`Leaflet`) usa marcadores `L.divIcon` customizados via CSS em vez dos ícones PNG padrão -- evita o problema clássico de path de assets quebrado do Leaflet em bundlers como o Vite.

## Decisões de Frontend

- usar `Inertia + props` (sem API REST separada para o consumo interno do próprio frontend);
- não usar `Pinia` inicialmente — estado por página via props do Inertia é suficiente para o escopo do MVP;
- não usar `Livewire`.

## Decisões Operacionais

- ambiente local roda via `docker compose up -d db redis` (Postgres e Redis containerizados) com `php artisan serve` + `npm run dev` nativos para loop de desenvolvimento rápido durante o hackathon;
- o `docker-compose.yml` também inclui os serviços `app`, `queue` e `nginx`, prontos para simular/produzir um ambiente completo containerizado quando necessário;
- portas padrão de Postgres (`5432`) e Redis (`6379`) foram remapeadas neste ambiente local (`5435` e `6380`) para não colidir com outros projetos rodando na mesma máquina — ver `.env`;
- credenciais e segredos não devem ser armazenados em arquivos versionados;
- infraestrutura de produção (VPS, domínio, CI/CD) ainda não definida — a definir quando o projeto sair da fase de hackathon.

## Regra de Atualização

Sempre que qualquer decisão desta lista mudar, atualizar em conjunto:

- `docs/STACK_DECISIONS.md`
- `docs/PROJECT_CONTEXT.md`
- `docs/ARQUITETURA_BASE.md`
