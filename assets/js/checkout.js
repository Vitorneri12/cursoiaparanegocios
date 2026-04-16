document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-inscricao');
    const blocoCartao = document.getElementById('bloco-cartao');
    const msg = document.getElementById('form-msg');
    const btn = document.getElementById('btn-pagar');
    const selectParcelas = document.getElementById('cc_parcelas');
    const sumTotal = document.getElementById('sum-total');
    const sumTaxaRow = document.getElementById('sum-taxa-row');
    const sumTaxa = document.getElementById('sum-taxa');

    const baseFmt = btn.dataset.baseFmt;
    const fmtBRL = v => 'R$ ' + Number(v).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    const showMsg = (texto, ok = false) => {
        msg.hidden = false;
        msg.textContent = texto;
        msg.classList.toggle('ok', ok);
        msg.scrollIntoView({ behavior: 'smooth', block: 'center' });
    };

    const atualizarTotais = () => {
        const metodo = document.querySelector('input[name="metodo"]:checked')?.value;
        if (metodo === 'CREDIT_CARD' && selectParcelas) {
            const opt = selectParcelas.options[selectParcelas.selectedIndex];
            const total = parseFloat(opt.dataset.total);
            const parcela = parseFloat(opt.dataset.parcela);
            const taxa = parseFloat(opt.dataset.taxa);
            sumTotal.textContent = fmtBRL(total);
            sumTaxa.textContent = fmtBRL(taxa);
            sumTaxaRow.hidden = false;
            btn.textContent = `Confirmar inscrição · ${selectParcelas.value}x de ${fmtBRL(parcela)}`;
        } else {
            sumTotal.textContent = baseFmt;
            sumTaxaRow.hidden = true;
            btn.textContent = `Confirmar inscrição · ${baseFmt}`;
        }
    };

    document.querySelectorAll('input[name="metodo"]').forEach(radio => {
        radio.addEventListener('change', () => {
            const ehCartao = radio.value === 'CREDIT_CARD' && radio.checked;
            blocoCartao.hidden = !ehCartao;
            blocoCartao.querySelectorAll('input').forEach(i => {
                if (['cc_number', 'cc_holder', 'cc_month', 'cc_year', 'cc_cvv'].includes(i.name)) {
                    i.required = ehCartao;
                }
            });
            atualizarTotais();
        });
    });

    selectParcelas?.addEventListener('change', atualizarTotais);
    atualizarTotais();

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        msg.hidden = true;

        const data = {};
        new FormData(form).forEach((v, k) => data[k] = v);

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        btn.disabled = true;
        const txtOriginal = btn.textContent;
        btn.textContent = 'Processando...';

        try {
            const r = await fetch('api/criar-cobranca.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            });
            const j = await r.json();

            if (!j.ok) {
                throw new Error(j.erro || 'Erro ao processar pagamento');
            }

            const params = new URLSearchParams();
            params.set('payment', j.payment_id);
            params.set('metodo', j.metodo);
            if (j.pix) {
                params.set('pix', JSON.stringify(j.pix));
            }
            window.location.href = 'obrigado.php?' + params.toString();
        } catch (err) {
            showMsg(err.message);
            btn.disabled = false;
            btn.textContent = txtOriginal;
        }
    });
});
