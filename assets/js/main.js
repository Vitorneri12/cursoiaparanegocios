// Mascaras simples para inputs
document.addEventListener('DOMContentLoaded', () => {
    const onlyDigits = v => (v || '').replace(/\D/g, '');

    const maskCpfCnpj = el => {
        el.addEventListener('input', () => {
            const v = onlyDigits(el.value).slice(0, 14);
            if (v.length <= 11) {
                el.value = v
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            } else {
                el.value = v
                    .replace(/^(\d{2})(\d)/, '$1.$2')
                    .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
                    .replace(/\.(\d{3})(\d)/, '.$1/$2')
                    .replace(/(\d{4})(\d)/, '$1-$2');
            }
        });
    };

    const maskTelefone = el => {
        el.addEventListener('input', () => {
            const v = onlyDigits(el.value).slice(0, 11);
            if (v.length <= 10) {
                el.value = v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3').trim();
            } else {
                el.value = v.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3').trim();
            }
        });
    };

    const maskCep = el => {
        el.addEventListener('input', () => {
            el.value = onlyDigits(el.value).slice(0, 8).replace(/(\d{5})(\d)/, '$1-$2');
        });
    };

    const maskCard = el => {
        el.addEventListener('input', () => {
            el.value = onlyDigits(el.value).slice(0, 19).replace(/(\d{4})(?=\d)/g, '$1 ').trim();
        });
    };

    document.querySelectorAll('input[name="cpf_cnpj"]').forEach(maskCpfCnpj);
    document.querySelectorAll('input[name="telefone"]').forEach(maskTelefone);
    document.querySelectorAll('input[name="cep"]').forEach(maskCep);
    document.querySelectorAll('input[name="cc_number"]').forEach(maskCard);

    // Auto-preenchimento de endereco via ViaCEP
    const cepInput = document.querySelector('input[name="cep"]');
    if (cepInput) {
        cepInput.addEventListener('blur', async () => {
            const cep = onlyDigits(cepInput.value);
            if (cep.length !== 8) return;
            try {
                const r = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const j = await r.json();
                if (j.erro) return;
                const set = (name, val) => {
                    const el = document.querySelector(`[name="${name}"]`);
                    if (el && !el.value) el.value = val || '';
                };
                set('logradouro', j.logradouro);
                set('bairro', j.bairro);
                set('cidade', j.localidade);
                const estado = document.querySelector('[name="estado"]');
                if (estado && !estado.value) estado.value = (j.uf || '').toUpperCase();
                document.querySelector('[name="numero"]')?.focus();
            } catch (e) {}
        });
    }
});
