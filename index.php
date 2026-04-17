<?php
require_once __DIR__ . '/includes/db.php';

$evento = config()['evento'];
try {
    $preco = precoAtual();
} catch (Throwable $e) {
    // Fallback se DB ainda nao configurado
    $preco = [
        'pagas' => 0,
        'numero_lote' => 1,
        'nome_lote' => $evento['lotes'][0]['nome'],
        'preco' => $evento['lotes'][0]['preco'],
        'lote' => $evento['lotes'][0],
        'lotes' => $evento['lotes'],
        'preco_maximo' => max(array_column($evento['lotes'], 'preco')),
        'vagas_total' => $evento['vagas_total'],
        'vagas_restantes' => $evento['vagas_total'],
        'vagas_lote' => $evento['lotes'][0]['vagas'],
        'esgotado' => false,
        'pre_venda' => false,
    ];
}

$fmt = fn(float $v) => 'R$ ' . number_format($v, 2, ',', '.');
$fmtData = fn(string $d) => date('d/m', strtotime($d));
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
<link rel="stylesheet" href="assets/css/style.css?v=2">
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
                <?php if (!$preco['esgotado'] && $preco['vagas_lote'] > 0): ?>
                <span class="hero-promo">Restam <strong><?= $preco['vagas_lote'] ?> vagas</strong> no <?= htmlspecialchars($preco['nome_lote']) ?></span>
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
                    <div class="price-label"><?= htmlspecialchars($preco['nome_lote']) ?></div>
                    <?php if ($preco['preco'] < $preco['preco_maximo']): ?>
                        <div class="price-old">De <?= $fmt($preco['preco_maximo']) ?> por</div>
                    <?php endif; ?>
                    <div class="price-value"><?= $fmt($preco['preco']) ?></div>
                    <div class="price-foot">por participante · em até 3x no cartão</div>
                <?php endif; ?>
            </div>

            <ul class="card-includes">
                <li>Acesso completo à palestra presencial</li>
                <li>Coffee break</li>
                <li>Certificado de participação</li>
                <li>Material apresentado</li>
            </ul>

            <?php if (!$preco['esgotado']): ?>
                <div class="bonus-box">
                    <div class="bonus-tag">🎁 Bônus exclusivo</div>
                    <p>Participantes terão acesso a uma <strong>avaliação inicial</strong> para identificar onde a IA pode gerar aumento de lucro e redução de custos em sua empresa.</p>
                </div>
                <a href="#inscricao" class="btn btn-primary btn-block">Inscrever-se agora</a>
                <span class="card-secure">Pagamento seguro via Asaas · PIX ou Cartão</span>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section section-light" id="por-que">
    <div class="container">
        <h2 class="section-title">Por que participar desta palestra?</h2>

        <div class="grid grid-2">
            <div class="feature-rich">
                <div class="feature-num">01</div>
                <h3>Aumente o faturamento com inteligência, não com esforço</h3>
                <p>Descubra como empresas estão utilizando Inteligência Artificial para identificar oportunidades escondidas, melhorar conversões e gerar mais receita — sem depender apenas de aumento de equipe ou investimento em mídia.</p>
            </div>
            <div class="feature-rich">
                <div class="feature-num">02</div>
                <h3>Reduza custos sem perder eficiência</h3>
                <p>Identifique onde sua empresa está perdendo dinheiro todos os dias e veja, na prática, como a automação inteligente elimina desperdícios, reduz retrabalho e melhora a produtividade operacional.</p>
            </div>
            <div class="feature-rich">
                <div class="feature-num">03</div>
                <h3>Tome decisões mais rápidas e assertivas</h3>
                <p>Pare de decidir no "feeling". Aprenda como utilizar dados, análises preditivas e inteligência artificial para tomar decisões com mais segurança, velocidade e impacto financeiro real.</p>
            </div>
            <div class="feature-rich">
                <div class="feature-num">04</div>
                <h3>Entenda como aplicar IA no seu negócio (de verdade)</h3>
                <p>Nada de teoria genérica. Você vai enxergar como implementar IA na sua realidade, independentemente do seu setor — com exemplos práticos de empresas que já estão colhendo resultados.</p>
            </div>
            <div class="feature-rich feature-wide">
                <div class="feature-num">05</div>
                <h3>Tenha vantagem competitiva no seu mercado</h3>
                <p>Enquanto muitas empresas ainda estão "estudando IA", outras já estão ganhando mais, gastando menos e tomando decisões melhores. Esta palestra mostra exatamente como não ficar para trás.</p>
            </div>
        </div>

        <div class="impact-box">
            <p>Empresas não perdem dinheiro por falta de sistema…</p>
            <p><strong>Perdem por falta de decisão.</strong></p>
            <p class="muted">Se sua empresa ainda não usa Inteligência Artificial de forma estratégica, você já está competindo em desvantagem.</p>
        </div>
    </div>
</section>

<section class="section" id="para-quem">
    <div class="container">
        <h2 class="section-title">Para quem é esta palestra</h2>
        <p class="section-sub">Se sua empresa gera dados, existe dinheiro escondido que você ainda não está vendo.</p>

        <div class="grid grid-3">
            <div class="audience-card">
                <h3>Empresários e donos de empresa</h3>
                <p>Que sentem que poderiam estar faturando mais, mas não têm clareza de onde estão perdendo dinheiro ou como escalar o negócio com eficiência.</p>
            </div>
            <div class="audience-card">
                <h3>Diretores e executivos</h3>
                <p>Que precisam tomar decisões rápidas e estratégicas, mas ainda não utilizam dados e IA como vantagem competitiva no dia a dia.</p>
            </div>
            <div class="audience-card">
                <h3>Gestores industriais</h3>
                <p>Que enfrentam desafios de produtividade, desperdício e eficiência operacional — e buscam soluções práticas para reduzir custo e melhorar performance.</p>
            </div>
            <div class="audience-card">
                <h3>Profissionais do agronegócio</h3>
                <p>Produtores, gestores e líderes que querem utilizar tecnologia para tomar decisões mais inteligentes sobre produção, safra e rentabilidade.</p>
            </div>
            <div class="audience-card">
                <h3>Usinas e cooperativas</h3>
                <p>Que lidam com operações complexas e precisam aumentar eficiência, previsibilidade e controle — transformando dados em decisões financeiras melhores.</p>
            </div>
            <div class="audience-card">
                <h3>Empresas de serviços</h3>
                <p>Que desejam automatizar atendimento, melhorar processos internos e aumentar resultado sem aumentar estrutura.</p>
            </div>
        </div>

        <div class="impact-box impact-yellow">
            <p>Esta palestra é para quem:</p>
            <ul>
                <li>Quer aumentar faturamento</li>
                <li>Precisa reduzir custos</li>
                <li>Entende que decisão baseada em dados não é mais opcional</li>
            </ul>
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

<section class="section" id="palestrantes">
    <div class="container">
        <h2 class="section-title">Palestrantes</h2>
        <p class="section-sub">Profissionais com experiência prática em IA aplicada aos negócios.</p>

        <div class="grid grid-2">
            <article class="speaker-card" data-speaker>
                <div class="speaker-head">
                    <div class="speaker-photo">HG</div>
                    <div>
                        <h3>Higor Corrêa Gimenes</h3>
                        <p class="speaker-role">CEO da TexanGroup</p>
                    </div>
                </div>
                <p class="speaker-short">Empresário e especialista em Inteligência Artificial aplicada a negócios, com mais de 20 anos de experiência em transformação digital, automação e análise de dados em larga escala.</p>
                <div class="speaker-full" hidden>
                    <p>À frente da TexanGroup, lidera o desenvolvimento de soluções de IA proprietária voltadas para empresas que buscam aumentar faturamento, reduzir custos e tomar decisões mais assertivas.</p>
                    <p>Sua trajetória inclui participação em projetos estratégicos no Brasil e no exterior, envolvendo governo, indústria, agronegócio e plataformas digitais, sempre com foco na aplicação prática da tecnologia como diferencial competitivo.</p>
                    <p>Hoje, seu trabalho está centrado na construção de sistemas inteligentes que transformam dados em decisões, permitindo que empresas operem com maior eficiência, previsibilidade e escala.</p>
                </div>
                <div class="speaker-actions">
                    <button type="button" class="link-more" data-toggle>Ler mais ↓</button>
                    <a href="https://www.linkedin.com/in/higorgimenes" target="_blank" rel="noopener" class="link-li">LinkedIn →</a>
                </div>
            </article>

            <article class="speaker-card" data-speaker>
                <div class="speaker-head">
                    <div class="speaker-photo">CS</div>
                    <div>
                        <h3>Claudio Soldera</h3>
                        <p class="speaker-role">Tecnologia e desenvolvimento de sistemas</p>
                    </div>
                </div>
                <p class="speaker-short">Profissional com sólida experiência em tecnologia e desenvolvimento de sistemas, atuando na construção de soluções digitais voltadas à eficiência operacional e à transformação de processos empresariais.</p>
                <div class="speaker-full" hidden>
                    <p>Com forte atuação prática, participa de projetos que envolvem integração de sistemas, automação e desenvolvimento de aplicações estratégicas, contribuindo diretamente para a evolução tecnológica de empresas em diferentes segmentos.</p>
                    <p>Seu trabalho está focado na aplicação de tecnologia de forma estruturada e orientada a resultado, conectando sistemas e dados para gerar maior produtividade e controle operacional.</p>
                </div>
                <div class="speaker-actions">
                    <button type="button" class="link-more" data-toggle>Ler mais ↓</button>
                    <a href="https://www.linkedin.com/in/claudio-soldera" target="_blank" rel="noopener" class="link-li">LinkedIn →</a>
                </div>
            </article>

            <article class="speaker-card" data-speaker>
                <div class="speaker-head">
                    <div class="speaker-photo">GC</div>
                    <div>
                        <h3>Guilherme Cabreira</h3>
                        <p class="speaker-role">Dados e soluções inteligentes</p>
                    </div>
                </div>
                <p class="speaker-short">Especialista em dados, tecnologia e desenvolvimento de soluções inteligentes, atua na construção de ambientes orientados à análise de dados e tomada de decisão estratégica.</p>
                <div class="speaker-full" hidden>
                    <p>Com experiência em projetos envolvendo inteligência de dados, integração de sistemas e automação, trabalha diretamente na criação de estruturas que permitem às empresas extrair valor real das informações disponíveis.</p>
                    <p>Sua atuação é voltada para transformar dados em ativos estratégicos, contribuindo para aumento de performance, eficiência e competitividade empresarial.</p>
                </div>
                <div class="speaker-actions">
                    <button type="button" class="link-more" data-toggle>Ler mais ↓</button>
                    <a href="https://www.linkedin.com/in/guilherme-cabreira" target="_blank" rel="noopener" class="link-li">LinkedIn →</a>
                </div>
            </article>

            <article class="speaker-card" data-speaker>
                <div class="speaker-head">
                    <div class="speaker-photo">FG</div>
                    <div>
                        <h3>Flávio Roberto de Freitas Gonçalves</h3>
                        <p class="speaker-role">Doutor e pesquisador em tecnologia</p>
                    </div>
                </div>
                <p class="speaker-short">Doutor e pesquisador com atuação na área de tecnologia e inovação, com experiência acadêmica e científica voltada ao desenvolvimento de soluções avançadas e aplicação de conhecimento técnico em contextos reais.</p>
                <div class="speaker-full" hidden>
                    <p>Sua atuação contribui para a formação de profissionais com base sólida, conectando teoria e prática no uso de tecnologias emergentes como Inteligência Artificial.</p>
                </div>
                <div class="speaker-actions">
                    <button type="button" class="link-more" data-toggle>Ler mais ↓</button>
                    <a href="http://lattes.cnpq.br" target="_blank" rel="noopener" class="link-li">Lattes →</a>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="section section-light" id="sobre">
    <div class="container">
        <h2 class="section-title">Sobre a TexanGroup</h2>
        <p class="section-sub">
            Há mais de uma década, a TexanGroup desenvolve soluções em tecnologia e Inteligência Artificial com um único objetivo:
            <strong>aumentar lucro, reduzir custos e melhorar a tomada de decisão das empresas.</strong>
        </p>

        <p class="section-text">
            Mais do que desenvolver sistemas, a TexanGroup atua na construção de um ecossistema inteligente, onde dados, processos e tecnologia trabalham juntos para gerar resultado real.
        </p>

        <div class="grid grid-3">
            <div class="feature">
                <h3>Tecnologia aplicada ao resultado</h3>
                <p>Sistemas, automações e plataformas digitais que vão além da operação — estruturam o negócio para crescer com eficiência. Atuamos em indústria, agronegócio, usinas e serviços.</p>
            </div>
            <div class="feature">
                <h3>IA como motor de decisão</h3>
                <p>Não usamos IA como tendência, mas como ferramenta estratégica de negócio: IA generativa, agentes autônomos, análise preditiva e automação inteligente — orientadas para impactar o resultado financeiro.</p>
            </div>
            <div class="feature">
                <h3>Parceria estratégica de longo prazo</h3>
                <p>Atuamos como parceiro estratégico, lado a lado com empresários, diretores e equipes técnicas. Nosso compromisso não é entregar tecnologia — é entregar resultado real e contínuo.</p>
            </div>
        </div>

        <div class="impact-box">
            <p>Acreditamos que empresas não precisam de mais sistemas…</p>
            <p><strong>Precisam de melhores decisões.</strong></p>
            <p class="muted">E é exatamente isso que a TexanGroup constrói.</p>
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
            <p class="section-sub">Todas as vagas foram preenchidas. Deixe seu contato em contato@texaneduc.com.br para a próxima turma.</p>
        <?php else: ?>
            <p class="section-sub">
                Restam <strong><?= $preco['vagas_lote'] ?> vagas</strong> no <strong><?= htmlspecialchars($preco['nome_lote']) ?></strong> por <?= $fmt($preco['preco']) ?>.
                <?php
                // Acha o proximo lote (se existir)
                $proximoPreco = null;
                foreach ($preco['lotes'] as $l) {
                    if ($l['preco'] > $preco['preco']) { $proximoPreco = $l['preco']; break; }
                }
                ?>
                <?php if ($proximoPreco): ?>
                    Após o encerramento deste lote, o valor passa para <?= $fmt($proximoPreco) ?>.
                <?php endif; ?>
            </p>

            <div class="cta-box">
                <div class="cta-price">
                    <?php if ($preco['preco'] < $preco['preco_maximo']): ?>
                        <span class="old">De <?= $fmt($preco['preco_maximo']) ?> por</span>
                    <?php endif; ?>
                    <span class="now"><?= $fmt($preco['preco']) ?></span>
                    <span class="per">por participante</span>
                </div>

                <ul class="cta-includes">
                    <li>Acesso completo à palestra presencial</li>
                    <li>Coffee break incluso</li>
                    <li>Certificado de participação</li>
                    <li>Bônus: avaliação inicial de IA para sua empresa</li>
                </ul>

                <div class="cta-warn">
                    <strong>Atenção:</strong> as vagas são limitadas e preenchidas por ordem de inscrição. O lote atual pode ser encerrado a qualquer momento.
                </div>

                <a href="checkout.php" class="btn btn-primary btn-lg btn-block">Inscrever-me agora</a>
                <p class="cta-note">Pagamento via PIX ou Cartão de Crédito (até 3x) · Processamento seguro via Asaas</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<footer class="footer">
    <div class="container footer-inner">
        <div class="footer-brand">
            <img src="assets/images/logo.png" alt="TexanEduc" class="footer-logo">
            <p>Soluções em Tecnologia e Inteligência Artificial</p>
            <p class="footer-tag">Uma iniciativa</p>
            <a href="https://www.texangroup.com.br" target="_blank" rel="noopener" class="footer-tg">
                <img src="assets/images/logo-texangroup.jpg" alt="Texan Group">
            </a>
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

<script src="assets/js/main.js?v=2"></script>

<script>
// Toggle "ler mais" nos palestrantes
document.querySelectorAll('[data-toggle]').forEach(btn => {
    btn.addEventListener('click', () => {
        const card = btn.closest('[data-speaker]');
        const full = card.querySelector('.speaker-full');
        const aberto = !full.hidden;
        full.hidden = aberto;
        btn.textContent = aberto ? 'Ler mais ↓' : 'Ler menos ↑';
    });
});
</script>

<elevenlabs-convai agent-id="agent_4501kpbqc4xbemps4n1ys9panxqa"></elevenlabs-convai>
<script src="https://unpkg.com/@elevenlabs/convai-widget-embed" async type="text/javascript"></script>
</body>
</html>
