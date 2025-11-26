<?php
header('Content-Type: application/json');

// Ler dados do POST
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['valor']) || !isset($data['vencimento'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Informe valor e vencimento']);
    exit;
}

$valor = floatval($data['valor']);
$vencimentoStr = $data['vencimento'];

// Converter a data em formato DD/MM/AAAA
$vencimentoDate = DateTime::createFromFormat('d/m/Y', $vencimentoStr);
if (!$vencimentoDate) {
    http_response_code(400);
    echo json_encode(['error' => 'Data de vencimento inválida. Use DD/MM/AAAA']);
    exit;
}

// Calcular dias de atraso
$hoje = new DateTime();
$diasAtraso = max(0, $hoje->diff($vencimentoDate)->days);

// Somente considera atraso se a data de vencimento já passou
if ($vencimentoDate > $hoje) {
    $diasAtraso = 0;
}

// Juros de 2,5% ao dia
$juros = $valor * 0.025 * $diasAtraso;
$total = $valor + $juros;

// Retornar resultado
echo json_encode([
    'valorOriginal' => number_format($valor, 2, '.', ''),
    'diasAtraso' => $diasAtraso,
    'juros' => number_format($juros, 2, '.', ''),
    'total' => number_format($total, 2, '.', '')
], JSON_PRETTY_PRINT);
?>
