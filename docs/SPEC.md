# Especificação Funcional — Plataforma Local de Descoberta de Restaurantes

Documento de origem do produto, fornecido no início do projeto. Mantido como referência; mudanças de escopo devem ser refletidas também em `PROJECT_CONTEXT.md`.

## 1. Visão do produto

Desenvolver uma plataforma local para descoberta de restaurantes, bares, cafeterias, lanchonetes e estabelecimentos gastronômicos.

A experiência deve combinar conceitos de:

* mapa e geolocalização;
* descoberta de restaurantes;
* avaliações;
* cardápio digital;
* fotos dos pratos;
* visitas verificadas;
* cupons e benefícios.

A plataforma **não deve ter delivery como foco inicial**.

O principal objetivo é:

> Ajudar o usuário a descobrir onde comer e gerar visitas presenciais aos estabelecimentos.

A pergunta que o produto deve responder rapidamente é:

> "Onde posso comer algo que gosto, perto de mim, agora?"

---

## 2. Tipos de usuário

O sistema deverá trabalhar inicialmente com três perfis.

### 2.1 Visitante

Usuário sem login.

Pode:

* acessar o mapa;
* visualizar estabelecimentos;
* pesquisar restaurantes;
* utilizar filtros;
* visualizar perfis;
* consultar cardápios;
* visualizar pratos e preços;
* visualizar fotos;
* consultar avaliações;
* visualizar localização;
* solicitar rota até o estabelecimento.

Não pode:

* avaliar;
* favoritar;
* receber cupons;
* registrar visitas.

### 2.2 Usuário cadastrado

Consumidor com conta na plataforma.

Possui todas as permissões do visitante e também pode:

* favoritar estabelecimentos;
* registrar visita através de QR Code;
* avaliar estabelecimentos após visita validada;
* consultar seu histórico de avaliações;
* consultar restaurantes visitados;
* receber cupons;
* utilizar cupons;
* gerenciar seu perfil.

### 2.3 Proprietário/Gestor de estabelecimento

Responsável por administrar um ou mais estabelecimentos.

Pode:

* reivindicar um estabelecimento existente;
* cadastrar estabelecimento quando permitido;
* editar informações comerciais;
* cadastrar cardápio;
* cadastrar categorias do cardápio;
* cadastrar pratos;
* definir preços;
* adicionar fotos dos pratos;
* adicionar fotos do estabelecimento;
* atualizar disponibilidade dos pratos;
* criar campanhas de cupons;
* responder avaliações;
* visualizar métricas do estabelecimento.

O proprietário **não pode editar ou excluir avaliações de usuários**.

---

## 3. Descoberta pelo mapa

O mapa será uma das principais interfaces da plataforma.

Ao acessar a aplicação, o usuário poderá permitir acesso à localização.

Com a localização disponível, o sistema deverá apresentar estabelecimentos próximos.

Cada estabelecimento deverá possuir:

* latitude;
* longitude;
* endereço;
* categoria;
* informações básicas;
* status de funcionamento.

Os estabelecimentos serão representados através de marcadores no mapa.

Ao selecionar um marcador, deverá ser apresentado um resumo do estabelecimento.

Exemplo:

**Restaurante X**

⭐ 4,7
📍 650 metros
💰 R$ 30–60 por pessoa
🍔 Hambúrguer / Lanches
🟢 Aberto agora

Ações:

* Ver perfil
* Ver cardápio
* Como chegar

---

## 4. Filtros

A plataforma deverá permitir combinar filtros.

### Filtros principais

#### Tipo de estabelecimento

Exemplos: Restaurante, Bar, Hamburgueria, Pizzaria, Cafeteria, Padaria, Lanchonete, Sorveteria, Açaí, Churrascaria.

#### Tipo de comida

Exemplos: Hambúrguer, Pizza, Japonês, Brasileiro, Mineiro, Italiano, Mexicano, Árabe, Churrasco, Massas, Doces, Café.

Um estabelecimento pode possuir várias categorias.

#### Restrição alimentar

Filtro para pessoas com dietas específicas, intolerâncias e alergias alimentares — não apenas uma tag informativa no prato, e sim um critério real de busca e filtragem.

Exemplos: vegetariano, vegano, sem lactose (intolerância), sem glúten (celíaco/intolerância), sem açúcar, sem amendoim/oleaginosas (alergia), sem frutos do mar (alergia), picante/não picante.

Deve ser possível filtrar tanto restaurantes (que possuam ao menos um prato compatível) quanto pratos individualmente.

#### Distância

Exemplos: até 500 m; até 1 km; até 2 km; até 5 km; qualquer distância.

#### Funcionamento

Aberto agora; abre hoje.

#### Preço

Faixas de preço: $, $$, $$$, $$$$.

#### Avaliação

Exemplo: 4+ estrelas; 4,5+ estrelas.

---

## 5. Perfil do estabelecimento

Cada estabelecimento possuirá uma página própria.

O perfil poderá conter: nome; foto de capa; galeria; descrição; endereço; localização no mapa; distância do usuário; telefone; WhatsApp; redes sociais; horário de funcionamento; categorias; tipos de comida; faixa de preço; nota média; quantidade de avaliações; cardápio; fotos dos pratos; avaliações; cupons disponíveis.

Ações principais: Como chegar; Ver cardápio; Favoritar; Avaliar.

---

## 6. Estabelecimentos sem proprietário cadastrado

Um estabelecimento poderá existir na plataforma mesmo sem possuir proprietário cadastrado.

Nesse estado, o estabelecimento terá status semelhante a: "Perfil não reivindicado."

Posteriormente, o proprietário poderá solicitar: "Este estabelecimento é meu."

Deverá existir um processo de reivindicação e validação. Após aprovação, o usuário passa a ser gestor daquele estabelecimento.

---

## 7. Cardápio

Cada estabelecimento poderá possuir um ou mais cardápios.

Exemplo — **Cardápio principal**, categorias: Entradas, Hambúrgueres, Pizzas, Pratos, Sobremesas, Bebidas.

Cada categoria possui vários itens.

---

## 8. Pratos

Cada item do cardápio deverá possuir: nome; descrição; preço; categoria; foto principal; fotos adicionais; disponibilidade; informações opcionais.

Informações opcionais poderão incluir: vegetariano; vegano; sem lactose; sem glúten; picante; tamanho; serve quantas pessoas.

Exemplo:

### Smash Bacon

Foto

Pão brioche, carne 160g, cheddar, bacon e molho da casa.

**R$ 32,90**

Disponível

---

## 9. Fotos dos pratos

Os restaurantes poderão cadastrar fotos reais dos seus pratos.

As fotos deverão estar vinculadas ao item correspondente do cardápio sempre que possível.

Isso permitirá futuramente uma descoberta baseada no próprio prato.

Exemplo: usuário pesquisa "Hambúrguer" → o sistema pode apresentar tanto restaurantes quanto pratos disponíveis próximos ao usuário.

---

## 10. Favoritos

Usuários autenticados poderão favoritar estabelecimentos.

O perfil do usuário deverá possuir uma área **Meus favoritos** com os restaurantes salvos.

---

## 11. Avaliações

Somente usuários autenticados poderão realizar avaliações.

Porém, estar autenticado não é suficiente. Para avaliar determinado estabelecimento, o usuário deverá possuir uma visita verificada.

---

## 12. Visita verificada

Cada estabelecimento deverá possuir um mecanismo de QR Code.

O usuário estará fisicamente no estabelecimento e utilizará o aplicativo para ler o QR Code.

Fluxo: Usuário visita restaurante → Escaneia QR Code → Sistema valida QR → Visita registrada → Permissão para avaliar liberada.

A visita deverá registrar pelo menos: usuário; estabelecimento; data; horário; identificador da validação.

---

## 13. Segurança do QR Code

Evitar, sempre que possível, QR Codes permanentes completamente estáticos. Um QR estático pode ser fotografado e compartilhado.

A arquitetura deverá permitir QR Codes: temporários; rotativos; vinculados a tokens; com período de validade.

O sistema deverá impedir reutilizações indevidas.

A estratégia técnica poderá evoluir durante o desenvolvimento, mas a regra é: uma avaliação deve representar uma visita real ao estabelecimento.

---

## 14. Avaliação verificada

Somente após uma visita válida o botão de avaliação será liberado.

Toda avaliação originada desse processo deverá possuir identificação: **✓ Visita verificada**.

A avaliação poderá possuir nota geral (1 a 5 estrelas) e, opcionalmente: comida; atendimento; ambiente; custo-benefício. Também poderá possuir comentário e fotos da experiência.

---

## 15. Regras das avaliações

O estabelecimento: pode responder à avaliação; não pode alterar a avaliação; não pode excluir a avaliação; pode denunciar conteúdo inadequado.

O usuário: somente pode avaliar após visita verificada; não pode criar avaliações ilimitadas a partir da mesma validação.

Deve existir proteção contra: spam; avaliações duplicadas; manipulação; fraude; abuso.

---

## 16. Nota do estabelecimento

A plataforma deverá calcular a nota média utilizando avaliações válidas.

Deverão ser armazenadas também informações como: quantidade total de avaliações; quantidade de avaliações verificadas; distribuição das estrelas.

---

## 17. Cupons por avaliação

O restaurante poderá criar campanhas oferecendo benefícios para usuários que avaliarem sua experiência.

Fluxo: Visita → QR Code → Visita validada → Avaliação → Cupom liberado → Retorno ao estabelecimento → Cupom utilizado.

---

## 18. Regra de imparcialidade

O benefício nunca poderá depender da nota fornecida. É proibido criar campanhas como "Dê 5 estrelas e ganhe 10%." A regra permitida é "Faça uma avaliação e ganhe 10%."

O usuário deverá receber o benefício independentemente da nota (1 a 5 estrelas). O restaurante também não deverá conseguir impedir a emissão do cupom com base na nota.

---

## 19. Campanhas de cupons

O restaurante poderá definir: nome da campanha; descrição; benefício; data inicial; data final; validade do cupom recebido; quantidade disponível; limite por usuário; consumo mínimo; dias permitidos; horários permitidos.

---

## 20. Cupom individual

Após cumprir as condições, deverá ser criado um cupom individual para o usuário, com: usuário; estabelecimento; campanha; código único; data de emissão; data de validade; status.

Possíveis estados: disponível; utilizado; expirado; cancelado.

---

## 21. Utilização do cupom

Na próxima visita, o usuário poderá apresentar o cupom (QR Code ou código único). O estabelecimento valida o código. Após validação: Disponível → Utilizado. Um cupom utilizado não poderá ser utilizado novamente.

---

## 22. Perfil do usuário

Área pessoal: Favoritos; Minhas visitas; Minhas avaliações; Meus cupons (disponíveis / utilizados / expirados).

---

## 23. Busca

Além do mapa, deverá existir busca textual: nome do restaurante; tipo de comida; estabelecimento; prato.

---

## 24. Descoberta por pratos

A arquitetura deve permitir que futuramente o usuário descubra diretamente pratos (ex.: pesquisar "Parmegiana" retorna pratos de restaurantes diferentes, com preço, nota e distância).

---

## 25. Ordenação dos resultados

Por: relevância; distância; avaliação; popularidade; faixa de preço.

O algoritmo de relevância poderá futuramente considerar: proximidade; correspondência com a pesquisa; nota; quantidade de avaliações; estabelecimento aberto; completude do perfil; popularidade recente.

---

## 26. Resultados patrocinados

No futuro, restaurantes poderão pagar por maior exposição, sempre identificados como **Patrocinado**, nunca misturados com resultados orgânicos sem identificação.

---

## 27. Métricas para restaurantes

Registrar desde o início: visualizações do estabelecimento; visualizações do cardápio; visualizações de pratos; favoritos; cliques em telefone; cliques em WhatsApp; cliques em "Como chegar"; visitas verificadas; avaliações recebidas; cupons emitidos; cupons utilizados.

---

## 28. Métrica principal do produto

Funil: Impressão no mapa → Visualização do perfil → Visualização do cardápio → Clique em "Como chegar" → Visita verificada → Avaliação → Cupom → Nova visita.

---

## 29. Painel do estabelecimento

Áreas: Meu estabelecimento; Cardápio; Fotos; Avaliações; QR Code; Cupons; Métricas.

---

## 30. Administração da plataforma

Administrador poderá: gerenciar usuários; gerenciar estabelecimentos; analisar reivindicações; moderar avaliações; analisar denúncias; gerenciar categorias; bloquear usuários; bloquear estabelecimentos; acompanhar atividades suspeitas; gerenciar conteúdo.

---

## 31. Entidades principais

`User`, `Restaurant`, `RestaurantOwner`, `RestaurantClaim`, `Category`, `Cuisine`, `RestaurantCategory`, `RestaurantCuisine`, `Menu`, `MenuCategory`, `MenuItem`, `MenuItemPhoto`, `RestaurantPhoto`, `Favorite`, `Visit`, `QRToken`, `Review`, `ReviewPhoto`, `ReviewReply`, `CouponCampaign`, `Coupon`, `CouponRedemption`, `Event/Analytics`.

Ver `ARQUITETURA_BASE.md` para a modelagem definitiva adotada (inclui `DietaryTag`, adicionado para o filtro de restrição alimentar).

---

## 32. Escopo do MVP

### Consumidor

Mapa; geolocalização; busca; filtros; perfil do restaurante; cardápio; fotos dos pratos; login/cadastro; favoritos; visita por QR Code; avaliação verificada; cupons.

### Restaurante

Reivindicação; edição do perfil; gerenciamento do cardápio; upload de fotos; gerenciamento do QR; avaliações; respostas; campanhas de cupons.

### Administração

Usuários; restaurantes; reivindicações; moderação básica.

---

## 33. Fora do escopo inicial

Delivery; entregadores; cálculo de frete; checkout; pagamento de pedidos; gestão de cozinha; integração com PDV; marketplace completo de pedidos.

A arquitetura não precisa impedir essas funcionalidades futuras, mas elas não fazem parte do MVP.

---

## 34. Princípio central

**DESCOBRIR → ESCOLHER → VISITAR → AVALIAR → VOLTAR**

O diferencial: **localização + filtros + cardápio + pratos + avaliações verificadas + benefícios para retorno.**

---

## 35. Diretriz para desenvolvimento

Preservar a separação entre: descoberta pública; dados do estabelecimento; conteúdo administrado pelo restaurante; interação do consumidor; validação presencial; avaliações; fidelização através de cupons; administração e moderação.

Evitar adicionar complexidade de delivery ao núcleo do produto.

> **"Onde vou comer hoje?"**
