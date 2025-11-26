<?php
header('Content-Type: application/json');

// Arquivos .json
$estoqueFile = 'estoque.json';
$movsFile = 'movimentacoes.json';

// Carrega o estoque e movimentações
$estoqueData = json_decode(file_get_contents($estoqueFile), true);
if (!file_exists($movsFile)) file_put_contents($movsFile, json_encode([]));
$movs = json_decode(file_get_contents($movsFile), true);

// Ler dados do POST
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error'=>'Dados inválidos']);
    exit;
}

if (isset($data['codigoProduto'])) {
    $data = [$data];
}

$results = [];

foreach($data as $item){

  if(!isset($item['codigoProduto'], $item['quantidade'], $item['descricaoMovimentacao'])){
      continue;
  }

  $codigo = intval($item['codigoProduto']);
  $quantidade = intval($item['quantidade']);
  $desc = $item['descricaoMovimentacao'];

  $finalEstoque = null;

  // Atualizar estoque do produto
  foreach($estoqueData['estoque'] as &$p){
      if($p['codigoProduto'] == $codigo){
          $p['estoque'] += $quantidade;
          $finalEstoque = $p['estoque'];
          break;
      }
  }

  if($finalEstoque === null){
      $results[] = [
          'codigoProduto' => $codigo,
          'error' => 'Produto não encontrado'
      ];
      continue;
  }

  // Registrar movimentação
  $movs[] = [
      'idMovimentacao' => uniqid(),
      'codigoProduto' => $codigo,
      'descricaoMovimentacao' => $desc,
      'quantidade' => $quantidade,
      'data' => date("Y-m-d H:i:s")
  ];

  $results[] = [
      'codigoProduto' => $codigo,
      'estoqueFinal' => $finalEstoque
  ];
}

// Salvar alterações
file_put_contents($estoqueFile, json_encode($estoqueData, JSON_PRETTY_PRINT));
file_put_contents($movsFile, json_encode($movs, JSON_PRETTY_PRINT));

// Retornar resultados
echo json_encode($results, JSON_PRETTY_PRINT);
?>
