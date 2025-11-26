<?php
header('Content-Type: application/json');

// Carrega o arquivo de vendas
$vendasFile = 'vendas.json';
$data = json_decode(file_get_contents($vendasFile), true);

$result = [];

// Faz o calculo da comissão
foreach($data["vendas"] as $v){
    $vend = $v["vendedor"];
    $val = $v["valor"];

    if(!isset($result[$vend])) $result[$vend] = 0;

    if($val >= 500){
        $result[$vend] += $val * 0.05; // Comissao de 5%
    } else if($val >= 100){
        $result[$vend] += $val * 0.01; // Comissao de 1%
    }

    // Arrendondei para decimal por se tratar de dinheiro
    $result[$vend] = round($result[$vend], 2);
}

// Retornar resultado em JSON
echo json_encode($result, JSON_PRETTY_PRINT);
?>
