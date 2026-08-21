<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>VicosaFood | A vitrine digital dos restaurantes de Viçosa</title>
<meta name="description" content="Descubra os melhores restaurantes de Viçosa com cupons exclusivos e avaliações verificadas, ou cadastre seu restaurante e alcance mais clientes sem pagar comissão por pedido." />
<link rel="icon" href="/favicon.svg" type="image/svg+xml" />
<style>
  :root {
    color-scheme: dark;
    --page-plane: #120b08;
    --surface-1: #1c120c;
    --surface-2: #261811;
    --text-primary: #fdf6f0;
    --text-secondary: #e3cfc0;
    --text-muted: #a68d7c;
    --border: rgba(249, 115, 22, 0.28);
    --glow-ring: 0 0 0 1px rgba(249, 115, 22, 0.1), 0 0 22px -6px rgba(249, 115, 22, 0.45);
    --accent: #f97316;
    --accent-soft: rgba(249, 115, 22, 0.16);
    --accent-faint: rgba(249, 115, 22, 0.55);
    --ember: #ef4444;
    --status-good: #22c55e;
    --status-warning: #f59e0b;
    --radius-sm: 6px;
    --radius-md: 10px;
    --radius-lg: 16px;
    --font-sans: -apple-system, "Segoe UI", Roboto, Inter, sans-serif;
  }

  * { box-sizing: border-box; }
  html, body { margin: 0; padding: 0; }

  body {
    background: var(--page-plane);
    color: var(--text-primary);
    font-family: var(--font-sans);
    line-height: 1.5;
    -webkit-font-smoothing: antialiased;
  }

  a { color: inherit; }
  img { max-width: 100%; display: block; }

  .wrap {
    max-width: 1120px;
    margin: 0 auto;
    padding: 0 1.5rem;
  }

  /* Nav */
  header.nav {
    position: sticky;
    top: 0;
    z-index: 20;
    background: rgba(18, 11, 8, 0.85);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid var(--border);
  }

  .nav-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    row-gap: 0.6rem;
    padding: 0.9rem 1.5rem;
    max-width: 1120px;
    margin: 0 auto;
  }

  .brand {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    font-weight: 700;
    font-size: 1.05rem;
    text-decoration: none;
  }

  .brand .mark {
    width: 30px;
    height: 30px;
    border-radius: var(--radius-sm);
    background: linear-gradient(135deg, var(--accent), var(--ember));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
  }

  .nav-links { display: flex; align-items: center; gap: 1.75rem; }
  .nav-links a.section-link { color: var(--text-secondary); text-decoration: none; font-size: 0.92rem; }
  .nav-links a.section-link:hover { color: var(--text-primary); }
  .nav-cta { display: flex; align-items: center; gap: 0.75rem; }

  .btn {
    position: relative;
    overflow: hidden;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.6rem 1.15rem;
    border-radius: var(--radius-md);
    font-size: 0.92rem;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid transparent;
    cursor: pointer;
    white-space: nowrap;
    transition: transform 0.2s ease, filter 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
  }

  .btn:hover { transform: translateY(-2px); }
  .btn:active { transform: translateY(0) scale(0.97); }

  .btn-primary {
    background: var(--accent);
    color: #2a0f02;
    box-shadow: var(--glow-ring);
    animation: btn-breathe 3.2s ease-in-out infinite;
  }

  .btn-primary:hover {
    filter: brightness(1.08);
    animation: btn-hover-glow 1.6s ease-in-out infinite;
    transform: translateY(-2px) scale(1.035);
  }

  .btn-primary:active { transform: translateY(0) scale(0.96); }

  @keyframes btn-breathe {
    0%, 100% { box-shadow: var(--glow-ring); }
    50% { box-shadow: 0 0 0 1px rgba(249, 115, 22, 0.25), 0 0 26px -2px rgba(249, 115, 22, 0.65); }
  }

  @keyframes btn-hover-glow {
    0%, 100% { box-shadow: var(--glow-ring), 0 10px 22px -10px rgba(249, 115, 22, 0.55); }
    50% { box-shadow: var(--glow-ring), 0 0 44px 2px rgba(249, 115, 22, 0.85); }
  }

  .btn-ghost { background: transparent; color: var(--text-primary); border-color: var(--border); }
  .btn-ghost:hover { border-color: var(--accent-faint); }
  .btn-lg { padding: 0.85rem 1.5rem; font-size: 1rem; }

  .btn::before {
    content: "";
    position: absolute;
    inset: 0;
    background: radial-gradient(90px circle at var(--bx, 50%) var(--by, 50%), rgba(255, 255, 255, 0.5), transparent 70%);
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
  }

  .btn:hover::before { opacity: 1; }

  .btn-primary::before {
    background: radial-gradient(70px circle at var(--bx, 50%) var(--by, 50%), rgba(255, 255, 255, 0.85), transparent 65%);
    mix-blend-mode: overlay;
  }

  .btn-ghost::before {
    background: radial-gradient(90px circle at var(--bx, 50%) var(--by, 50%), rgba(249, 115, 22, 0.3), transparent 70%);
  }

  .btn-primary::after {
    content: "";
    position: absolute;
    top: 0;
    left: -60%;
    width: 40%;
    height: 100%;
    background: linear-gradient(115deg, transparent, rgba(255, 255, 255, 0.45), transparent);
    transform: skewX(-20deg);
    transition: left 0.6s ease;
  }

  .btn-primary:hover::after { left: 130%; }

  @media (prefers-reduced-motion: reduce) {
    .btn-primary { animation: none; }
    .btn-primary::after { display: none; }
  }

  /* Hero */
  .hero { position: relative; overflow: hidden; padding: 5.5rem 0 4.5rem; text-align: center; }

  .hero::before {
    content: "";
    position: absolute;
    inset: -20% -10% auto -10%;
    height: 620px;
    background:
      radial-gradient(ellipse at 30% 20%, rgba(249, 115, 22, 0.18), transparent 55%),
      radial-gradient(ellipse at 75% 10%, rgba(239, 68, 68, 0.16), transparent 55%);
    pointer-events: none;
    z-index: 0;
  }

  .hero > * { position: relative; z-index: 1; }

  .eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.35rem 0.85rem;
    border-radius: 999px;
    border: 1px solid var(--border);
    background: var(--surface-1);
    color: var(--text-secondary);
    font-size: 0.82rem;
    margin-bottom: 1.5rem;
  }

  .eyebrow .dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--accent);
    box-shadow: 0 0 8px 1px var(--accent);
  }

  h1 {
    font-size: clamp(2rem, 4.2vw, 3.15rem);
    line-height: 1.12;
    margin: 0 auto 1.25rem;
    max-width: 820px;
    letter-spacing: -0.01em;
  }

  h1 .grad {
    background: linear-gradient(90deg, var(--accent), var(--ember));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
  }

  .hero-sub { font-size: 1.1rem; color: var(--text-secondary); max-width: 660px; margin: 0 auto 2.25rem; }

  .hero-ctas {
    display: flex; align-items: center; justify-content: center;
    gap: 0.9rem; flex-wrap: wrap; margin-bottom: 1rem;
  }

  .hero-note { color: var(--text-muted); font-size: 0.85rem; }

  .hero-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.1rem;
    margin-top: 3.25rem;
  }

  .hero-stat {
    text-align: center;
    background: var(--surface-1);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 1.5rem 1.25rem;
    box-shadow: var(--glow-ring);
    transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
  }

  .hero-stat:hover {
    transform: translateY(-4px);
    border-color: var(--accent-faint);
    box-shadow: var(--glow-ring), 0 10px 24px -12px rgba(0, 0, 0, 0.45);
  }

  .hero-stat .icon { font-size: 1.9rem; margin-bottom: 0.6rem; }
  .hero-stat span { color: var(--text-secondary); font-size: 0.9rem; }

  @media (max-width: 700px) { .hero-stats { grid-template-columns: 1fr; } }

  /* Reveal + card hover, shared */
  .reveal { opacity: 0; transform: translateY(18px); transition: opacity 0.7s ease, transform 0.7s ease; }
  .reveal.is-visible { opacity: 1; transform: translateY(0); }

  @media (prefers-reduced-motion: reduce) {
    .reveal { opacity: 1; transform: none; transition: none; }
  }

  .card, .step, .feature-item, .mockup {
    transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
  }

  .card:hover, .step:hover, .feature-item:hover {
    transform: translateY(-4px);
    border-color: var(--accent-faint);
    box-shadow: var(--glow-ring), 0 10px 24px -12px rgba(0, 0, 0, 0.45);
  }

  /* Product mockup — the real check-in -> review -> coupon loop (QrToken/Visit/Review/Coupon),
     not a generic screenshot: shows the actual mechanic instead of another icon+text card. */
  .mockup {
    background: var(--surface-1);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--glow-ring), 0 30px 60px -30px rgba(0, 0, 0, 0.6);
    padding: 1.5rem;
    max-width: 640px;
    margin: 3.5rem auto 0;
    text-align: left;
  }

  .mockup:hover {
    transform: translateY(-6px);
    border-color: rgba(249, 115, 22, 0.4);
    box-shadow: var(--glow-ring), 0 0 60px -10px rgba(249, 115, 22, 0.5), 0 40px 70px -30px rgba(0, 0, 0, 0.7);
  }

  .mockup-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.1rem; }
  .mockup-title { font-weight: 700; font-size: 0.95rem; }
  .mockup-sub { color: var(--text-muted); font-size: 0.75rem; margin-top: 0.15rem; }
  .mockup-badge { color: var(--status-good); font-size: 0.8rem; font-weight: 700; }

  .mockup-flow { display: flex; flex-direction: column; gap: 0.55rem; }

  .mockup-row {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 0.75rem;
    align-items: center;
    padding: 0.65rem 0.85rem;
    border-radius: var(--radius-md);
    background: var(--surface-2);
    border: 1px solid var(--border);
    transition: background 0.25s ease, border-color 0.25s ease, transform 0.25s ease;
  }

  .mockup-row:hover {
    background: #301d13;
    border-color: rgba(249, 115, 22, 0.35);
    transform: translateX(4px);
  }

  .mockup-row .step-icon { font-size: 1.1rem; }
  .mockup-row .field { font-size: 0.85rem; color: var(--text-secondary); }
  .mockup-row .badge-ok { color: var(--status-good); font-size: 0.78rem; font-weight: 600; white-space: nowrap; }

  /* Sections */
  section { padding: 4.5rem 0; }

  .section-head { text-align: center; max-width: 640px; margin: 0 auto 3rem; }
  .section-head .eyebrow { margin-bottom: 1rem; }
  .section-head h2 { font-size: clamp(1.5rem, 2.6vw, 2.1rem); margin: 0 0 0.75rem; letter-spacing: -0.01em; }
  .section-head p { color: var(--text-secondary); margin: 0; }

  .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.1rem; }
  .grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem; }

  .card {
    background: var(--surface-1);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
  }

  .card .icon, .feature-item .icon {
    width: 40px; height: 40px;
    display: flex; align-items: center; justify-content: center;
    border-radius: var(--radius-sm);
    background: var(--accent-soft);
    font-size: 1.15rem;
    margin-bottom: 1rem;
    transition: box-shadow 0.3s ease, transform 0.3s ease;
  }

  .card:hover .icon, .feature-item:hover .icon {
    transform: translateY(-2px) scale(1.06);
    box-shadow: 0 10px 22px -6px rgba(249, 115, 22, 0.55);
  }

  .card h3 { font-size: 1.02rem; margin: 0 0 0.5rem; }
  .card p { color: var(--text-secondary); font-size: 0.9rem; margin: 0; }

  /* Two-track "como funciona" — one path for diners, one for restaurant owners, side by side
     instead of a single generic 3-step strip, since this page (unlike a single-audience SaaS
     landing) genuinely serves two different people with two different jobs to be done. */
  .track { background: var(--surface-1); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem; }
  .track-head { display: flex; align-items: center; gap: 0.65rem; margin-bottom: 1.5rem; }
  .track-head .tag {
    font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;
    padding: 0.3rem 0.6rem; border-radius: 999px; background: var(--accent-soft); color: var(--accent);
  }
  .track-head h3 { margin: 0; font-size: 1.1rem; }

  .steps { counter-reset: step; display: flex; flex-direction: column; gap: 1.25rem; }
  .step { position: relative; padding: 0; display: flex; gap: 1rem; align-items: flex-start; border: none; background: none; }
  .step:hover { transform: none; box-shadow: none; }

  .step .num {
    counter-increment: step;
    flex: none;
    display: inline-flex; align-items: center; justify-content: center;
    width: 34px; height: 34px; border-radius: 50%;
    background: linear-gradient(135deg, var(--accent), var(--ember));
    color: #2a0f02; font-weight: 700;
  }

  .step .num::before { content: counter(step); }
  .step h4 { margin: 0 0 0.3rem; font-size: 0.98rem; }
  .step p { margin: 0; color: var(--text-secondary); font-size: 0.88rem; }

  @media (max-width: 860px) { .grid-2 { grid-template-columns: 1fr; } }

  /* Differentiator */
  .diff {
    background: var(--surface-1);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 2.75rem;
    display: grid;
    grid-template-columns: 1.1fr 1fr;
    gap: 2.5rem;
    align-items: center;
  }

  .diff h2 { font-size: clamp(1.4rem, 2.4vw, 1.85rem); margin: 0 0 1rem; letter-spacing: -0.01em; }
  .diff p { color: var(--text-secondary); margin: 0 0 1rem; }

  .diff-compare { display: flex; flex-direction: column; gap: 0.9rem; }

  .compare-row {
    display: flex; align-items: flex-start; gap: 0.75rem;
    padding: 0.9rem 1rem; border-radius: var(--radius-md);
    background: var(--surface-2); border: 1px solid var(--border);
  }

  .compare-row.good { border-color: var(--accent-faint); }
  .compare-row .mark { flex: none; font-size: 1rem; line-height: 1.3; }
  .compare-row span.label { color: var(--text-secondary); font-size: 0.9rem; }

  .feature-item {
    display: flex; gap: 0.9rem; padding: 1.4rem;
    border-radius: var(--radius-lg); border: 1px solid var(--border);
    background: var(--surface-1);
  }

  .feature-item h3 { margin: 0 0 0.35rem; font-size: 0.98rem; }
  .feature-item p { margin: 0; color: var(--text-secondary); font-size: 0.87rem; }

  /* Final CTA */
  .final-cta { text-align: center; }
  .final-cta h2 { font-size: clamp(1.5rem, 2.6vw, 2.1rem); margin: 0 0 0.75rem; }
  .final-cta p { color: var(--text-secondary); margin: 0 0 2rem; }
  .final-ctas { display: flex; align-items: center; justify-content: center; gap: 0.9rem; flex-wrap: wrap; }

  footer {
    position: relative;
    overflow: hidden;
    border-top: 1px solid var(--border);
    padding: 2.5rem 0;
  }

  .footer-watermark {
    position: absolute;
    left: 50%; bottom: -0.32em;
    transform: translateX(-50%);
    width: 100%;
    text-align: center;
    font-size: clamp(4rem, 16vw, 11rem);
    font-weight: 800;
    letter-spacing: -0.02em;
    color: transparent;
    -webkit-text-stroke: 1.5px rgba(227, 207, 192, 0.16);
    white-space: nowrap;
    pointer-events: none;
    z-index: 0;
    user-select: none;
  }

  .footer-watermark--bright {
    color: rgba(249, 115, 22, 0.85);
    -webkit-text-stroke: 1.5px rgba(249, 115, 22, 1);
    text-shadow: 0 0 30px rgba(249, 115, 22, 0.9);
    -webkit-mask-image: radial-gradient(circle 260px at var(--mx, -999px) var(--my, -999px), black, transparent 70%);
    mask-image: radial-gradient(circle 260px at var(--mx, -999px) var(--my, -999px), black, transparent 70%);
  }

  .footer-inner {
    position: relative; z-index: 1;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 1rem;
  }

  .footer-brand { display: flex; align-items: center; gap: 0.55rem; font-weight: 700; }
  footer .muted { color: var(--text-muted); font-size: 0.85rem; }

  @media (max-width: 860px) {
    .grid-4 { grid-template-columns: 1fr 1fr; }
    .diff { grid-template-columns: 1fr; padding: 1.75rem; }
    .nav-links .section-link { display: none; }
  }

  @media (max-width: 560px) {
    .grid-4 { grid-template-columns: 1fr; }
    .hero { padding: 3.5rem 0 3rem; }
    .hero-stats { gap: 1.5rem; }
    section { padding: 3rem 0; }
    .final-ctas .btn { width: 100%; }
  }
</style>
</head>
<body>

<header class="nav">
  <div class="nav-inner">
    <a href="{{ route('landing') }}" class="brand">
      <span class="mark">🍽️</span>
      <span>VicosaFood</span>
    </a>
    <nav class="nav-links">
      <a class="section-link" href="#como-funciona">Como funciona</a>
      <a class="section-link" href="#recursos">Recursos</a>
      <a class="section-link" href="#para-restaurantes">Para restaurantes</a>
      <div class="nav-cta">
        <a class="btn btn-ghost" href="{{ route('login') }}">Entrar</a>
        <a class="btn btn-primary" href="{{ route('discover') }}">Descobrir restaurantes</a>
      </div>
    </nav>
  </div>
</header>

<main>
  <section class="hero">
    <div class="wrap">
      <span class="eyebrow"><span class="dot"></span> Hackathon Abrasel · feito em Viçosa/MG</span>
      <h1>A vitrine digital dos <span class="grad">restaurantes de Viçosa</span></h1>
      <p class="hero-sub">
        Descubra onde comer com cupons exclusivos e avaliações de visitas reais. Se você tem um
        restaurante, cadastre seu cardápio e alcance mais clientes — sem pagar comissão por pedido.
      </p>
      <div class="hero-ctas">
        <a class="btn btn-primary btn-lg" href="{{ route('discover') }}">Descobrir restaurantes</a>
        <a class="btn btn-ghost btn-lg" href="{{ route('register') }}">Cadastrar meu restaurante</a>
      </div>
      <p class="hero-note">Gratuito para restaurantes durante o beta do hackathon.</p>

      <div class="hero-stats">
        <div class="hero-stat reveal">
          <div class="icon">📍</div>
          <span>Busca por bairro, cozinha e faixa de preço</span>
        </div>
        <div class="hero-stat reveal">
          <div class="icon">🎟️</div>
          <span>Cupons e promoções exclusivas por restaurante</span>
        </div>
        <div class="hero-stat reveal">
          <div class="icon">⭐</div>
          <span>Avaliações verificadas de visitas reais</span>
        </div>
      </div>

      <div class="mockup reveal">
        <div class="mockup-head">
          <div>
            <div class="mockup-title">Sabor da Serra · Centro</div>
            <div class="mockup-sub">Check-in via QR Code na mesa</div>
          </div>
          <span class="mockup-badge">✓ visita confirmada</span>
        </div>
        <div class="mockup-flow">
          <div class="mockup-row">
            <span class="step-icon">📱</span>
            <span class="field">Cliente escaneia o QR Code do restaurante</span>
            <span class="badge-ok">check-in</span>
          </div>
          <div class="mockup-row">
            <span class="step-icon">⭐</span>
            <span class="field">Avalia a visita real, com fotos</span>
            <span class="badge-ok">avaliação verificada</span>
          </div>
          <div class="mockup-row">
            <span class="step-icon">🎟️</span>
            <span class="field">Ganha um cupom de desconto pra próxima visita</span>
            <span class="badge-ok">cupom liberado</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="problema">
    <div class="wrap">
      <div class="section-head">
        <span class="eyebrow">O problema</span>
        <h2>Descobrir onde comer bem em Viçosa ainda é um esforço manual</h2>
        <p>Grupo de WhatsApp, indicação de boca a boca, cupom perdido no Instagram.</p>
      </div>
      <div class="grid-4">
        <div class="card reveal">
          <div class="icon">🔍</div>
          <h3>Difícil achar o lugar certo</h3>
          <p>Sem filtro por bairro, tipo de cozinha ou faixa de preço, a busca vira tentativa e erro.</p>
        </div>
        <div class="card reveal">
          <div class="icon">🎟️</div>
          <h3>Cupom espalhado</h3>
          <p>Promoção em story que some em 24h, sem um lugar único pra conferir o que está valendo.</p>
        </div>
        <div class="card reveal">
          <div class="icon">⭐</div>
          <h3>Avaliação sem confiança</h3>
          <p>Nota de quem nunca visitou o restaurante não ajuda ninguém a decidir.</p>
        </div>
        <div class="card reveal">
          <div class="icon">📋</div>
          <h3>Cardápio desatualizado</h3>
          <p>Preço e prato mudam e o cliente só descobre depois de já estar na porta.</p>
        </div>
      </div>
    </div>
  </section>

  <section id="como-funciona">
    <div class="wrap">
      <div class="section-head">
        <span class="eyebrow">Como funciona</span>
        <h2>Um app, dois caminhos</h2>
        <p>Pra quem busca onde comer, e pra quem tem um restaurante pra mostrar.</p>
      </div>
      <div class="grid-2">
        <div class="track reveal">
          <div class="track-head">
            <span class="tag">Cliente</span>
            <h3>Pra quem busca onde comer</h3>
          </div>
          <div class="steps">
            <div class="step">
              <div class="num"></div>
              <div>
                <h4>Descubra por perto</h4>
                <p>Filtre por bairro, tipo de cozinha e faixa de preço até achar o restaurante certo.</p>
              </div>
            </div>
            <div class="step">
              <div class="num"></div>
              <div>
                <h4>Faça check-in com QR Code</h4>
                <p>Escaneie o código na mesa do restaurante pra confirmar que a visita é real.</p>
              </div>
            </div>
            <div class="step">
              <div class="num"></div>
              <div>
                <h4>Avalie e ganhe cupom</h4>
                <p>Sua avaliação vira um cupom de desconto pra próxima visita, sem pegadinha.</p>
              </div>
            </div>
          </div>
        </div>
        <div class="track reveal" id="para-restaurantes">
          <div class="track-head">
            <span class="tag">Restaurante</span>
            <h3>Pra quem tem um restaurante</h3>
          </div>
          <div class="steps">
            <div class="step">
              <div class="num"></div>
              <div>
                <h4>Cadastre em minutos</h4>
                <p>Cardápio, fotos, horário de funcionamento e faixa de preço, sem precisar de TI.</p>
              </div>
            </div>
            <div class="step">
              <div class="num"></div>
              <div>
                <h4>Gere QR Codes pra mesa</h4>
                <p>Cada check-in confirma uma visita de verdade, base pra avaliação e pra cupom.</p>
              </div>
            </div>
            <div class="step">
              <div class="num"></div>
              <div>
                <h4>Acompanhe no painel</h4>
                <p>Visitas, avaliações e cupons resgatados, tudo em um painel de gestão só seu.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="recursos">
    <div class="wrap">
      <div class="section-head">
        <span class="eyebrow">Recursos</span>
        <h2>Feito pra confiança, não só pra listagem</h2>
      </div>
      <div class="grid-4">
        <div class="feature-item reveal">
          <div class="icon">📱</div>
          <div>
            <h3>Check-in por QR Code</h3>
            <p>Cada visita é confirmada na hora, direto na mesa do restaurante.</p>
          </div>
        </div>
        <div class="feature-item reveal">
          <div class="icon">⭐</div>
          <div>
            <h3>Avaliações verificadas</h3>
            <p>Só quem fez check-in pode avaliar. Sem review fake, sem concorrente derrubando nota.</p>
          </div>
        </div>
        <div class="feature-item reveal">
          <div class="icon">🎟️</div>
          <div>
            <h3>Cupons por campanha</h3>
            <p>O restaurante define a campanha, o cliente resgata o cupom ao avaliar a visita.</p>
          </div>
        </div>
        <div class="feature-item reveal">
          <div class="icon">📊</div>
          <div>
            <h3>Painel do gestor</h3>
            <p>Visitas, avaliações e cupons resgatados em um painel único, sem planilha.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="diferencial">
    <div class="wrap">
      <div class="diff reveal">
        <div>
          <span class="eyebrow">Diferencial</span>
          <h2>Sem comissão por pedido. Sem review de quem não foi lá.</h2>
          <p>
            A maioria das plataformas de descoberta cobra comissão por venda, ou aceita qualquer
            avaliação sem checar se a pessoa visitou o restaurante. O VicosaFood confirma a visita
            pelo QR Code antes de liberar a avaliação e o cupom.
          </p>
          <p>Restaurante ganha visibilidade real. Cliente confia no que está lendo.</p>
        </div>
        <div class="diff-compare">
          <div class="compare-row">
            <span class="mark">📋</span>
            <span class="label">Grupo de WhatsApp e indicação: sem histórico, sem cupom rastreável, sem dado sobre quem realmente foi.</span>
          </div>
          <div class="compare-row good">
            <span class="mark">✅</span>
            <span class="label">VicosaFood: check-in confirma a visita, avaliação e cupom nascem dessa visita real.</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="final-cta">
    <div class="wrap">
      <h2>Pronto pra começar?</h2>
      <p>Descubra um restaurante agora, ou cadastre o seu em poucos minutos.</p>
      <div class="final-ctas">
        <a class="btn btn-primary btn-lg" href="{{ route('discover') }}">Descobrir restaurantes</a>
        <a class="btn btn-ghost btn-lg" href="{{ route('register') }}">Cadastrar meu restaurante</a>
      </div>
    </div>
  </section>
</main>

<footer id="site-footer">
  <span class="footer-watermark" aria-hidden="true">VicosaFood</span>
  <span class="footer-watermark footer-watermark--bright" aria-hidden="true">VicosaFood</span>
  <div class="wrap footer-inner">
    <div class="footer-brand">
      <span class="mark" style="width:24px;height:24px;font-size:0.85rem;border-radius:6px;background:linear-gradient(135deg,var(--accent),var(--ember));display:flex;align-items:center;justify-content:center;">🍽️</span>
      <span>VicosaFood</span>
    </div>
    <span class="muted">© {{ date('Y') }} VicosaFood · Hackathon Abrasel</span>
  </div>
</footer>

<script>
  if ("IntersectionObserver" in window) {
    var revealObserver = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            revealObserver.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.15, rootMargin: "0px 0px -40px 0px" },
    );
    document.querySelectorAll(".reveal").forEach(function (el) {
      revealObserver.observe(el);
    });
  } else {
    document.querySelectorAll(".reveal").forEach(function (el) {
      el.classList.add("is-visible");
    });
  }

  (function () {
    var footer = document.getElementById("site-footer");
    if (!footer) return;
    footer.addEventListener("mousemove", function (e) {
      var rect = footer.getBoundingClientRect();
      footer.style.setProperty("--mx", e.clientX - rect.left + "px");
      footer.style.setProperty("--my", e.clientY - rect.top + "px");
    });
    footer.addEventListener("mouseleave", function () {
      footer.style.setProperty("--mx", "-999px");
      footer.style.setProperty("--my", "-999px");
    });
  })();

  (function () {
    document.querySelectorAll(".btn").forEach(function (btn) {
      btn.addEventListener("mousemove", function (e) {
        var rect = btn.getBoundingClientRect();
        btn.style.setProperty("--bx", e.clientX - rect.left + "px");
        btn.style.setProperty("--by", e.clientY - rect.top + "px");
      });
    });
  })();
</script>

</body>
</html>
