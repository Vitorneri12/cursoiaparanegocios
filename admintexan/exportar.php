<?php
require_once __DIR__ . '/_auth.php';
adminProtege();

$filtroStatus = $_GET['status'] ?? '';
$filtroMetodo = $_GET['metodo'] ?? '';
$busca        = trim($_GET['q'] ?? '');

$where = []; $params = [];
if ($filtroStatus !== '') { $where[] = 'status = ?'; $params[] = $filtroStatus; }
if ($filtroMetodo !== '') { $where[] = 'metodo_pagamento = ?'; $params[] = $filtroMetodo; }
if ($busca !== '') {
    $where[] = '(nome LIKE ? OR email LIKE ? OR cpf_cnpj LIKE ? OR telefone LIKE ? OR empresa LIKE ?)';
    $like = '%' . $busca . '%';
    array_push($params, $like, $like, $like, $like, $like);
}

$sql = 'SELECT * FROM inscricoes';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY id DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);

$nome = 'inscricoes-' . date('Y-m-d-His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nome . '"');

$out = fopen('php://output', 'w');
// BOM para Excel reconhecer UTF-8
fwrite($out, "\xEF\xBB\xBF");

fputcsv($out, [
    'ID','Data inscricao','Data pagamento','Nome','Email','CPF/CNPJ','Telefone',
    'Empresa','Cargo','CEP','Logradouro','Numero','Complemento','Bairro','Cidade','Estado',
    'Valor','Metodo','Status','Asaas Payment ID',
], ';');

while ($i = $stmt->fetch()) {
    fputcsv($out, [
        $i['id'], $i['data_inscricao'], $i['data_pagamento'],
        $i['nome'], $i['email'], $i['cpf_cnpj'], $i['telefone'],
        $i['empresa'], $i['cargo'],
        $i['cep'], $i['logradouro'], $i['numero'], $i['complemento'], $i['bairro'], $i['cidade'], $i['estado'],
        number_format((float)$i['valor'], 2, ',', '.'),
        $i['metodo_pagamento'], $i['status'], $i['asaas_payment_id'],
    ], ';');
}
fclose($out);
