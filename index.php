<?php
require_once __DIR__ . '/includes/db.php';

$evento = config()['evento'];
try {
    $preco = precoAtual();
} catch (Throwable $e) {
    // Fallback se DB ainda nao configurado
    $preco = [
        'pagas' => 0,
        'promo_ativa' => true,
        'preco' => $evento['preco_promo'],
        'preco_promo' => $evento['preco_promo'],
        'preco_normal' => $evento['preco_normal'],
        'vagas_total' => $evento['vagas_total'],
        'vagas_restantes' => $evento['vagas_total'],
        'vagas_restantes_promo' => $evento['vagas_promo'],
        'esgotado' => false,
    ];
}

$fmt = fn(float $v) => 'R$ ' . number_format($v, 2, ',', '.');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>IA PARA NEGÓCIOS — Palestra Presencial | TexanEduc</title>
<meta name="description" content="Como empresas estão aumentando lucro com Inteligência Artificial. Palestra presencial em Ribeirão Preto — 09 de maio.">
<meta property="og:title" content="IA PARA NEGÓCIOS — Palestra Presencial">
<meta property="og:description" content="Como empresas estão aumentando lucro com Inteligência Artificial.">
<meta property="og:type" content="website">
<link rel="icon" href="assets/images/logo-compact.png">
<link rel="stylesheet" href="assets/css/style.css?v=1">
</head>
<body>

<header class="topbar">
    <div class="container topbar-inner">
        <a href="/" class="logo">
            <img src="assets/images/logo.png" alt="TexanEduc">
        </a>
        <div class="topbar-actions">
            <a href="https://wa.me/5519978033293?text=Ol%C3%A1!%20Tenho%20interesse%20na%20palestra%20IA%20PARA%20NEG%C3%93CIOS" target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                WhatsApp
            </a>
            <a href="#inscricao" class="btn btn-primary btn-sm">Garantir minha vaga</a>
        </div>
    </div>
</header>

<section class="hero">
    <div class="container hero-inner">
        <div class="hero-text">
            <span class="badge">PALESTRA PRESENCIAL · 09 DE MAIO</span>
            <h1>IA <span class="hl">PARA NEGÓCIOS</span></h1>
            <p class="lead">Como empresas estão aumentando lucro com Inteligência Artificial — aplicações práticas para você implementar no dia seguinte.</p>

            <ul class="hero-info">
                <li><strong>Local:</strong> TRYP by Wyndham Ribeirão Preto</li>
                <li><strong>Data:</strong> 09 de maio</li>
                <li><strong>Horário:</strong> 09h30 às 16h00</li>
            </ul>

            <div class="hero-cta">
                <a href="#inscricao" class="btn btn-primary btn-lg">Quero garantir minha vaga</a>
                <?php if ($preco['promo_ativa'] && !$preco['esgotado']): ?>
                <span class="hero-promo">Restam <strong><?= $preco['vagas_restantes_promo'] ?> vagas</strong> com desconto</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="hero-card">
            <div class="card-price">
                <?php if ($preco['esgotado']): ?>
                    <div class="price-label">Vagas</div>
                    <div class="price-value">ESGOTADO</div>
                    <div class="price-foot">Acompanhe nossas próximas turmas</div>
                <?php else: ?>
                    <?php if ($preco['promo_ativa']): ?>
                        <div class="price-label">Lote promocional · primeiros 30</div>
                        <div class="price-old"><?= $fmt($preco['preco_normal']) ?></div>
                        <div class="price-value"><?= $fmt($preco['preco_promo']) ?></div>
                        <div class="price-foot">por participante · vaga garantida após pagamento</div>
                    <?php else: ?>
                        <div class="price-label">Lote único</div>
                        <div class="price-value"><?= $fmt($preco['preco_normal']) ?></div>
                        <div class="price-foot">por participante</div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <ul class="card-includes">
                <li>Conteúdo estratégico + demonstrações ao vivo</li>
                <li>Coffee break</li>
                <li>Certificado de participação</li>
                <li>Material apresentado</li>
            </ul>
            <?php if (!$preco['esgotado']): ?>
            <a href="#inscricao" class="btn btn-primary btn-block">Inscrever-se agora</a>
            <span class="card-secure">🔒 Pagamento seguro via Asaas · PIX ou Cartão</span>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section section-light">
    <div class="container">
        <h2 class="section-title">Por que essa palestra?</h2>
        <p class="section-sub">A Inteligência Artificial deixou de ser tendência e virou necessidade estratégica para empresas que querem crescer, reduzir custos e se manter competitivas.</p>

        <div class="grid grid-3">
            <div class="feature">
                <div class="feature-ico">📈</div>
                <h3>Aumente o faturamento</h3>
                <p>IA aplicada a marketing, vendas e geração de oportunidades — com casos reais e números.</p>
            </div>
            <div class="feature">
                <div class="feature-ico">⚙️</div>
                <h3>Reduza custos operacionais</h3>
                <p>Automação de processos repetitivos e ganho de eficiência em áreas administrativas.</p>
            </div>
            <div class="feature">
                <div class="feature-ico">🎯</div>
                <h3>Decisões mais inteligentes</h3>
                <p>Use dados a seu favor: análise preditiva, BI moderno e relatórios automáticos.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Para quem é a palestra</h2>
        <div class="grid grid-3">
            <div class="pill">Empresários</div>
            <div class="pill">Diretores e executivos</div>
            <div class="pill">Gestores industriais</div>
            <div class="pill">Profissionais do agronegócio</div>
            <div class="pill">Usinas e cooperativas</div>
            <div class="pill">Empresas de serviços</div>
        </div>
    </div>
</section>

<section class="section section-dark" id="programacao">
    <div class="container">
        <h2 class="section-title">Programação completa</h2>
        <div class="timeline">
            <div class="time-item">
                <div class="time-hour">09h30</div>
                <div class="time-body">
                    <h4>Abertura</h4>
                    <p>Cenário atual da IA · Impacto nos negócios · Apresentação institucional.</p>
                </div>
            </div>
            <div class="time-item">
                <div class="time-hour">10h15</div>
                <div class="time-body">
                    <h4>IA que aumenta lucro</h4>
                    <p>Marketing e vendas com IA · Geração de oportunidades · Demonstrações práticas.</p>
                </div>
            </div>
            <div class="time-item">
                <div class="time-hour">11h30</div>
                <div class="time-body">
                    <h4>IA que reduz custos</h4>
                    <p>Automação de processos · Eficiência operacional · Casos reais.</p>
                </div>
            </div>
            <div class="time-item">
                <div class="time-hour">12h30</div>
                <div class="time-body">
                    <h4>Almoço · Networking (1h30)</h4>
                    <p>Tempo para relacionamento entre participantes.</p>
                </div>
            </div>
            <div class="time-item">
                <div class="time-hour">14h00</div>
                <div class="time-body">
                    <h4>IA aplicada ao mercado regional</h4>
                    <p>Agronegócio · Indústria · Usinas · Serviços.</p>
                </div>
            </div>
            <div class="time-item">
                <div class="time-hour">14h45</div>
                <div class="time-body">
                    <h4>Demonstrações práticas</h4>
                    <p>Automação empresarial · IA no atendimento · IA em análise de dados.</p>
                </div>
            </div>
            <div class="time-item">
                <div class="time-hour">15h30</div>
                <div class="time-body">
                    <h4>Encerramento estratégico</h4>
                    <p>Como iniciar na IA · Próximos passos · Oportunidades.</p>
                </div>
            </div>
            <div class="time-item">
                <div class="time-hour">16h00</div>
                <div class="time-body">
                    <h4>Encerramento oficial</h4>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Palestrante</h2>
        <div class="speaker">
            <div class="speaker-photo">HG</div>
            <div class="speaker-body">
                <h3>Higor Corrêa Gimenes</h3>
                <p class="speaker-role">CEO da TexanGroup</p>
                <p>Especialista em Inteligência Artificial aplicada aos negócios, com atuação direta em projetos de IA, automação e dados em empresas dos setores industrial, agronegócio e serviços.</p>
            </div>
        </div>
    </div>
</section>

<section class="section section-light" id="sobre">
    <div class="container">
        <h2 class="section-title">Sobre a Texan Group</h2>
        <p class="section-sub">Há mais de uma década, a Texan Group desenvolve soluções em tecnologia e inteligência artificial para empresas que querem crescer com eficiência e dados.</p>

        <div class="grid grid-3">
            <div class="feature">
                <div class="feature-ico">🚀</div>
                <h3>Tecnologia aplicada</h3>
                <p>Desenvolvemos sistemas, automações e plataformas digitais para indústria, agronegócio, usinas e serviços.</p>
            </div>
            <div class="feature">
                <div class="feature-ico">🧠</div>
                <h3>IA para o negócio</h3>
                <p>Implementamos IA generativa, agentes autônomos, análise preditiva e automações que resolvem problemas reais.</p>
            </div>
            <div class="feature">
                <div class="feature-ico">🤝</div>
                <h3>Parceria de longo prazo</h3>
                <p>Atuamos lado a lado com líderes e equipes técnicas para entregar resultado mensurável — não só tecnologia.</p>
            </div>
        </div>

        <div class="sobre-cta">
            <a href="https://www.texangroup.com.br" target="_blank" rel="noopener" class="btn btn-secondary">Conheça www.texangroup.com.br →</a>
        </div>
    </div>
</section>

<section class="section section-cta" id="inscricao">
    <div class="container">
        <h2 class="section-title">Garanta sua vaga</h2>

        <?php if ($preco['esgotado']): ?>
            <p class="section-sub">Todas as vagas foram preenchidas. Deixe seu contato para a próxima turma em contato@texaneduc.com.br.</p>
        <?php else: ?>
            <p class="section-sub">
                <?php if ($preco['promo_ativa']): ?>
                    Restam <strong><?= $preco['vagas_restantes_promo'] ?> vagas no lote promocional</strong> a <?= $fmt($preco['preco_promo']) ?>.
                    Depois disso, o valor passa para <?= $fmt($preco['preco_normal']) ?>.
                <?php else: ?>
                    Restam <strong><?= $preco['vagas_restantes'] ?> vagas</strong> a <?= $fmt($preco['preco_normal']) ?>.
                <?php endif; ?>
            </p>

            <div class="cta-box">
                <div class="cta-price">
                    <?php if ($preco['promo_ativa']): ?>
                        <span class="old"><?= $fmt($preco['preco_normal']) ?></span>
                        <span class="now"><?= $fmt($preco['preco_promo']) ?></span>
                    <?php else: ?>
                        <span class="now"><?= $fmt($preco['preco_normal']) ?></span>
                    <?php endif; ?>
                    <span class="per">por participante</span>
                </div>
                <a href="checkout.php" class="btn btn-primary btn-lg">Inscrever-me agora</a>
                <p class="cta-note">Pagamento via PIX ou Cartão de Crédito (até 5x) · Processado pela Asaas</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<footer class="footer">
    <div class="container footer-inner">
        <div class="footer-brand">
            <img src="assets/images/logo.png" alt="TexanEduc" class="footer-logo">
            <p>Soluções em Tecnologia e Inteligência Artificial</p>
            <p class="footer-tag">Uma iniciativa <a href="https://www.texangroup.com.br" target="_blank" rel="noopener"><strong>Texan Group</strong></a></p>
        </div>
        <div class="footer-info">
            <p><strong>Endereço:</strong> Ricardo Benetton Martins, 1000 — Parque II do Polo de Tecnologia, Prédio 9A, Campinas-SP — CEP 13086-902</p>
            <p><strong>Site:</strong> <a href="https://www.texangroup.com.br" target="_blank" rel="noopener">www.texangroup.com.br</a></p>
            <p><strong>E-mail:</strong> <a href="mailto:contato@texaneduc.com.br">contato@texaneduc.com.br</a></p>
        </div>
    </div>
    <div class="container footer-bot">
        <small>© <?= date('Y') ?> Texan Group · TexanEduc. Todos os direitos reservados.</small>
    </div>
</footer>

<script src="assets/js/main.js?v=1"></script>

<elevenlabs-convai agent-id="agent_4501kpbqc4xbemps4n1ys9panxqa"></elevenlabs-convai>
<script src="https://unpkg.com/@elevenlabs/convai-widget-embed" async type="text/javascript"></script>
</body>
</html>
