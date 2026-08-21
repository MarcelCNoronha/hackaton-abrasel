# Arquitetura Base do Projeto

## Objetivo

Construir uma plataforma local de descoberta de restaurantes (mapa, busca, cardápio digital, avaliações com visita verificada por QR Code e cupons de fidelização), sem foco em delivery. Ver `docs/SPEC.md` para a especificação funcional completa.

## Stack Adotada

Ver `docs/STACK_DECISIONS.md`.

## Decisões Estruturais

- monólito modular, backend e painel (consumidor + gestor + admin) no mesmo repositório, seguindo o padrão do projeto Mundial;
- distância inicialmente calculada via fórmula haversine em SQL sobre `latitude`/`longitude` (sem `PostGIS` no MVP — reavaliar se a base de estabelecimentos crescer e a busca geográfica precisar de índice espacial);
- o app cobre 4 perfis de acesso: visitante (sem conta), usuário cadastrado, proprietário/gestor de estabelecimento, administrador (`users.role`);
- um estabelecimento pode existir sem proprietário (`restaurants.owner_claimed_at IS NULL`) — fica com status "perfil não reivindicado" até uma `restaurant_claims` ser aprovada;
- uma avaliação (`reviews`) só pode ser criada a partir de uma `visits` com QR validado — não existe caminho de avaliação sem visita verificada;
- o benefício de uma campanha de cupom (`coupon_campaigns`) nunca pode ser condicionado à nota da avaliação — a emissão do cupom (`coupons`) é disparada pela criação da `review` em si, independente do valor de `rating`;
- QR Code de visita é rotativo/temporário (`qr_tokens`, com `expires_at`), nunca um código estático permanente — evita visita falsificada por foto compartilhada do QR;
- eventos de analytics (`events`) são gravados desde o início (visualizações, cliques, favoritos, visitas, avaliações, cupons), mesmo sem painel de métricas pronto, para não perder histórico do funil descoberta → visita.

## Módulos

### Descoberta pública

- mapa com marcadores dos estabelecimentos próximos à localização do usuário;
- busca textual por restaurante, tipo de comida ou prato;
- filtros combináveis: tipo de estabelecimento (`categories`), tipo de comida (`cuisines`), restrição alimentar (`dietary_tags`), distância, funcionamento (`business_hours`), faixa de preço (`restaurants.price_range`), avaliação mínima;
- perfil do estabelecimento (dados, localização, cardápio, fotos, avaliações, cupons disponíveis).

### Dados do estabelecimento (gestor)

- reivindicação de estabelecimento existente (`restaurant_claims`) ou cadastro novo;
- edição de dados comerciais, horário de funcionamento, fotos;
- gerenciamento de cardápio, categorias de cardápio e pratos, com fotos e tags de restrição alimentar por prato;
- não tem permissão de edição/exclusão sobre avaliações de usuários — apenas resposta (`review_replies`).

### Interação do consumidor

- favoritos;
- busca e filtros;
- histórico de visitas, avaliações e cupons no perfil do usuário.

### Validação presencial (QR Code)

- `qr_tokens`: token rotativo por estabelecimento, com validade curta;
- `visits`: registrada ao validar o QR (usuário, estabelecimento, token usado, timestamp);
- um `qr_token` marcado como usado não pode gerar uma segunda `visit`.

### Avaliações

- `reviews` vinculada obrigatoriamente a uma `visits`;
- nota geral obrigatória (1–5) e notas opcionais por dimensão (comida, atendimento, ambiente, custo-benefício);
- `review_photos` e `review_replies` (resposta do estabelecimento).

### Fidelização (cupons)

- `coupon_campaigns` define as regras (vigência, validade do cupom emitido, quantidade, limite por usuário, consumo mínimo, dias/horários permitidos);
- `coupons` é o cupom individual emitido para o usuário ao concluir uma avaliação dentro de uma campanha ativa, independente da nota;
- `coupon_redemptions` registra a utilização (`coupons.status`: disponível → utilizado).

### Administração e moderação

- gestão de usuários, estabelecimentos, reivindicações, categorias/cuisines/dietary tags, denúncias de avaliação.

## Modelo de Domínio

- `users` — inclui `role` (`consumer` | `owner` | `admin`);
- `restaurants` — dados comerciais, localização (`latitude`, `longitude`), `price_range`, status de reivindicação;
- `restaurant_owners` — pivot `users` ↔ `restaurants` (permite múltiplos gestores por estabelecimento e múltiplos estabelecimentos por gestor);
- `restaurant_claims` — solicitação de reivindicação com fluxo de aprovação;
- `business_hours` — horário de funcionamento por dia da semana;
- `categories` — tipo de estabelecimento (Restaurante, Bar, Hamburgueria...);
- `cuisines` — tipo de comida (Hambúrguer, Japonês, Italiano...);
- `restaurant_categories`, `restaurant_cuisines` — pivots N:N;
- `dietary_tags` — restrições alimentares e alergênicos (vegetariano, vegano, sem lactose, sem glúten, sem açúcar, sem amendoim/oleaginosas, sem frutos do mar, picante), com `kind` (`diet` | `allergen`) para diferenciar preferência de restrição médica na UI de filtro;
- `restaurant_photos`;
- `menus`, `menu_categories`, `menu_items`, `menu_item_photos`;
- `menu_item_dietary_tags` — pivot N:N entre `menu_items` e `dietary_tags`;
- `favorites` — pivot `users` ↔ `restaurants`;
- `qr_tokens`, `visits`;
- `reviews`, `review_photos`, `review_replies`;
- `coupon_campaigns`, `coupons`, `coupon_redemptions`;
- `events` — telemetria do funil (view, click, favorite, visit, review, coupon).

## Fluxo Macro

1. usuário visitante navega pelo mapa/busca com geolocalização e aplica filtros (incluindo restrição alimentar);
2. usuário cadastrado favorita e usa "Como chegar" para ir ao estabelecimento;
3. no local, escaneia o `qr_token` vigente → sistema cria `visits`;
4. com a visita registrada, o botão de avaliação é liberado → usuário cria `reviews`;
5. se houver `coupon_campaigns` ativa, a criação da review dispara a emissão de um `coupons` individual, independente da nota;
6. o usuário retorna ao estabelecimento e utiliza o cupom → `coupon_redemptions`, `coupons.status = used`;
7. eventos relevantes de cada etapa são gravados em `events` para medir o funil descoberta → visita → retorno.

## Infraestrutura

Ver `docs/STACK_DECISIONS.md` — ambiente local via Docker Compose (Postgres + Redis), deploy de produção ainda não definido nesta fase de hackathon.

## Regra de Atualização da Documentação

Sempre que houver mudança relevante de arquitetura, modelo de domínio ou fluxo, este arquivo deve ser atualizado no mesmo ciclo de trabalho.
