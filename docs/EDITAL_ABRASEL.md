# Edital Hackathon ABRASEL — Compatibilidade e Plano

Resumo do **Edital CENTEV/UFV 10/2026** (arquivo original: `Hackathon_ABRASEL.pdf`, na raiz do repositório) e do plano de aderência deste projeto. Ver `docs/PROJECT_CONTEXT.md` para o estado de implementação.

## Fatos-chave do edital

- organizadores: tecnoPARQ/UFV (CenTev/UFV), Projeto Viçosa SMART, ABRASEL;
- evento presencial em Viçosa/MG, no tecnoPARQ/UFV, **21 a 23 de agosto de 2026** (sexta à noite, sábado e domingo integrais, premiação domingo à tarde);
- inscrições: 27/07/2026 a 16/08/2026 (já fechadas antes do início da implementação deste projeto) -- **participação confirmada** pela equipe (checado em 20/08/2026);
- equipe recomendada: 5 a 7 integrantes;
- cada equipe escolhe **um desafio principal** do Anexo II, podendo combinar elementos de outros desafios, desde que mantenha clareza sobre problema, público-alvo, proposta de valor e potencial de implementação;
- entregas finais: pitch de até 5 min; protótipo/MVP/demo funcional/mockup/dashboard/landing page/bot/proposta validada; canvas ou resumo executivo (Anexo IV, 10 campos); explicação de como a solução se conecta a cidades inteligentes e alimentação fora do lar;
- avaliação (100 pts): aderência ao desafio e tema (20), clareza do problema (15), inovação (20), viabilidade técnica/operacional/econômica (20), impacto (15), qualidade do protótipo/pitch (10);
- premiação: 1º R$ 1.000, 2º R$ 500, 3º R$ 300;
- propriedade intelectual da solução permanece com a equipe que a criou;
- **item 14.1 do edital**: participantes não podem apresentar como "inédita" uma solução "desenvolvida integralmente fora do Hackathon".

## Desafio escolhido

**Desafio #1 -- Rota Gastronômica Inteligente de Viçosa** (Anexo II): "organizar bares e restaurantes por localização, tipo de comida, faixa de preço, horário de funcionamento, acessibilidade, formas de pagamento, avaliações, promoções e eventos, fortalecendo turismo, economia local e experiência do cidadão."

Combinado com elementos de:
- **Desafio #8 -- Cardápio Inteligente e Inclusivo**: filtro de restrição alimentar (`dietary_tags`, dieta vs. alergia/intolerância) em nível de prato;
- **Desafio #19 -- Monitoramento da Satisfação do Cliente**: avaliações só liberadas após visita verificada por QR Code (ver `docs/ARQUITETURA_BASE.md`).

## Como lidar com o item 14.1 (trabalho feito antes do evento)

A base técnica (modelo de dados, autenticação, tela de descoberta) foi construída antes do início oficial do evento. Para não conflitar com o item 14.1:

- o pitch deve deixar explícito que a **infraestrutura técnica** foi preparada com antecedência, mas que o **desafio específico, os dados reais de Viçosa e o refinamento da solução** usam o que sair da fala de bares/restaurantes convidados na sexta-feira (cronograma, Anexo I, 19h-20h) e das mentorias do fim de semana;
- os dados de demonstração atuais (`RestaurantSeeder`) são fictícios, só a geografia é real (Viçosa/MG) -- ideal usar o evento para coletar/validar dados de estabelecimentos reais, se possível com os bares/restaurantes convidados;
- enquadrar a apresentação como "chegamos com uma base pronta para iterar rápido e usar o tempo do hackathon no que importa: entender a dor real do setor em Viçosa e refinar a solução com quem vive isso" -- é coerente com o espírito do edital, não uma tentativa de burlar a regra.

## Checklist de aderência

- [x] projeto se encaixa claramente no Desafio #1 (+ elementos de #8 e #19);
- [x] equipe inscrita e confirmada;
- [x] dados de demonstração usam geografia real de Viçosa/MG (corrigido -- estava em São Paulo);
- [ ] resumo executivo / canvas (Anexo IV, 10 campos) -- ainda não redigido;
- [ ] roteiro do pitch de 5 minutos -- ainda não preparado;
- [ ] confirmar tamanho da equipe (recomendado 5-7 integrantes);
- [ ] avaliar se vale a pena substituir os 6 estabelecimentos fictícios por dados reais coletados durante o evento (fala dos bares/restaurantes convidados na sexta-feira).

## Regra de atualização

Atualizar este arquivo conforme o checklist acima avança, e junto de `docs/PROJECT_CONTEXT.md` sempre que a estratégia de aderência ao edital mudar.
