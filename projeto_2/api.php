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

if(isset($data['codigoProduto']) && isset($data['quantidade']) && isset($data['descricaoMovimentacao'])){
    $codigo = intval($data['codigoProduto']);
    $quantidade = intval($data['quantidade']);
    $desc = $data['descricaoMovimentacao'];

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
        http_response_code(404);
        echo json_encode(['error'=>'Produto não encontrado']);
        exit;
    }

    // Registrar movimentação
    $movs[] = [
        'idMovimentacao' => uniqid(),
        'codigoProduto' => $codigo,
        'descricaoMovimentacao' => $desc,
        'quantidade' => $quantidade,
        'data' => date("Y-m-d H:i:s")
    ];

    // Salvar alterações
    file_put_contents($estoqueFile, json_encode($estoqueData, JSON_PRETTY_PRINT));
    file_put_contents($movsFile, json_encode($movs, JSON_PRETTY_PRINT));

    // Retornar estoque final
    echo json_encode(['estoqueFinal' => $finalEstoque]);
    exit;
}

// Se os dados enviados estiverem incorretos
http_response_code(400);
echo json_encode(['error'=>'Dados inválidos']);
?>
