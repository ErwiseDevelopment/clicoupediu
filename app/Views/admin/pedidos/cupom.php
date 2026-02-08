<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Pedido #<?php echo $pedido['id']; ?></title>
    <style>
        /* RESET E CONFIGURAÇÕES GERAIS */
        * {
            box-sizing: border-box; /* Garante que padding não estoure a largura */
        }

        @page { margin: 0; padding: 0; size: auto; }
        
        body {
            margin: 0;
            /* Margem de segurança: 2mm Esquerda, 4mm Direita (pra compensar a zona morta), 2mm Topo */
            padding: 2mm 5mm 2mm 2mm; 
            background-color: #fff;
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.2;
            width: 100%;
        }

        /* CAIXA PRINCIPAL (Card) */
        .cupom-box {
            border: 1px solid #000;
            border-radius: 10px;
            padding: 8px;
            width: 100%; /* Ocupa 100% do espaço disponível (que já tem o padding do body) */
            display: block;
            background: white;
        }

        /* UTILITÁRIOS */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }
        .font-black { font-weight: 900; }
        .uppercase { text-transform: uppercase; }
        .text-sm { font-size: 10px; }
        .text-lg { font-size: 13px; }
        .text-xl { font-size: 16px; }
        .text-2xl { font-size: 20px; }

        /* DIVISÓRIAS */
        .divider {
            border-bottom: 1px dashed #000;
            margin: 6px 0;
            display: block;
            width: 100%;
        }
        .divider-solid {
            border-bottom: 1px solid #000;
            margin: 6px 0;
        }

        /* CABEÇALHO PRETO */
        .header-box {
            background: #000;
            color: #fff;
            border-radius: 6px;
            padding: 6px 0;
            margin-top: 5px;
            margin-bottom: 10px;
            text-align: center;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ITENS */
        .item-row {
            display: flex;
            align-items: flex-start;
            margin-bottom: 8px;
            font-size: 12px;
        }
        .qtd-badge {
            border: 1px solid #000;
            border-radius: 4px;
            padding: 1px 0;
            width: 22px;
            text-align: center;
            margin-right: 6px;
            font-weight: 800;
            font-size: 11px;
            flex-shrink: 0;
        }
        .prod-nome { flex: 1; font-weight: 700; line-height: 1.1; }
        .prod-valor { font-weight: 800; margin-left: 5px; white-space: nowrap; text-align: right; }

        /* COMPLEMENTOS */
        .sub-item {
            font-size: 10px;
            color: #000;
            margin-left: 30px; 
            margin-top: 1px;
            display: block;
            line-height: 1.1;
        }
        
        /* TOTAIS */
        .row-total {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 3px;
        }
        .total-zao {
            font-size: 20px;
            font-weight: 900;
            margin-top: 2px;
        }

        /* PAGAMENTO */
        .pagamento-tag {
            border: 1px solid #000;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 12px;
            display: inline-block;
        }

        /* CLIENTE */
        .box-cliente {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 8px;
            margin-top: 10px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    </style>
</head>
<body>

<div class="cupom-box">

    <div class="text-center">
        <div class="font-black text-xl uppercase"><?php echo $loja['nome_fantasia']; ?></div>
        <?php if(!empty($loja['telefone_whatsapp'])): ?>
            <div class="text-sm mt-1">WhatsApp: <?php echo $loja['telefone_whatsapp']; ?></div>
        <?php endif; ?>
    </div>

    <div class="header-box">
        <div class="text-2xl font-black">PEDIDO #<?php echo $pedido['id']; ?></div>
        <div style="font-size: 11px; margin-top: 2px; font-weight: bold;">
            <?php echo date('d/m H:i', strtotime($pedido['created_at'])); ?> • 
            <?php echo $pedido['tipo_entrega'] == 'entrega' ? 'ENTREGA' : 'RETIRADA'; ?>
        </div>
    </div>

    <div style="margin-top: 5px;">
        <?php foreach($itens as $item): ?>
            <div class="item-row">
                <div class="qtd-badge"><?php echo $item['quantidade']; ?>x</div>
                <div class="prod-nome">
                    <?php echo $item['p_nome']; ?>
                </div>
                <div class="prod-valor"><?php echo number_format($item['total'], 2, ',', '.'); ?></div>
            </div>

            <?php if(!empty($item['complementos']) || !empty($item['observacao_item'])): ?>
                <div style="margin-bottom: 8px; margin-top: -4px;">
                    <?php if(!empty($item['complementos'])): ?>
                        <?php foreach($item['complementos'] as $add): ?>
                            <span class="sub-item">+ <?php echo $add['nome']; ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if(!empty($item['observacao_item'])): ?>
                        <span class="sub-item font-bold">OBS: <?php echo $item['observacao_item']; ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="divider"></div>

    <div class="row-total">
        <span>Soma dos itens:</span>
        <b><?php echo number_format($pedido['valor_produtos'], 2, ',', '.'); ?></b>
    </div>
    
    <div class="row-total">
        <span>Taxa de Entrega:</span>
        <b><?php echo number_format($pedido['taxa_entrega'], 2, ',', '.'); ?></b>
    </div>

    <?php if($pedido['desconto'] > 0): ?>
    <div class="row-total">
        <span>Desconto:</span>
        <b>- <?php echo number_format($pedido['desconto'], 2, ',', '.'); ?></b>
    </div>
    <?php endif; ?>

    <div class="divider-solid"></div>

    <div class="row-total" style="align-items: center; margin-top: 5px;">
        <span class="font-bold text-lg">TOTAL:</span>
        <span class="total-zao">R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></span>
    </div>

    <div class="divider"></div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:8px;">
        <span class="font-bold text-sm">Pagamento:</span>
        <div class="pagamento-tag"><?php echo $pedido['forma_pagamento']; ?></div>
    </div>

    <?php if($pedido['forma_pagamento'] == 'dinheiro' && $pedido['troco_para'] > 0): 
        $troco = $pedido['troco_para'] - $pedido['valor_total'];
    ?>
        <div class="row-total" style="margin-top:6px;">
            <span>Troco p/ R$ <?php echo number_format($pedido['troco_para'], 2, ',', '.'); ?>:</span>
            <span class="font-bold">R$ <?php echo number_format($troco, 2, ',', '.'); ?></span>
        </div>
    <?php endif; ?>

    <div class="box-cliente">
        <div style="border-bottom: 1px solid #ccc; padding-bottom: 4px; margin-bottom: 4px; font-size: 10px; text-align: center; text-transform: uppercase; color: #555;">
            DADOS DO CLIENTE
        </div>
        <div class="font-black text-lg uppercase leading-tight"><?php echo $pedido['cliente_nome']; ?></div>
        <div class="font-bold text-sm mt-1"><?php echo $pedido['cliente_telefone']; ?></div>
        
        <?php if($pedido['tipo_entrega'] == 'entrega'): ?>
            <div style="margin-top:8px; font-size:12px; line-height:1.3;" class="uppercase">
                <span class="font-bold">Endereço de Entrega:</span><br>
                <?php echo $pedido['endereco_entrega']; ?>, <?php echo $pedido['numero']; ?><br>
                <?php echo $pedido['bairro']; ?>
                <?php if(!empty($pedido['complemento'])): ?>
                    <br><em>Comp: <?php echo $pedido['complemento']; ?></em>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="text-center font-bold bg-white border border-black rounded p-2 mt-2 text-sm">
                RETIRADA NO BALCÃO
            </div>
        <?php endif; ?>
    </div>

    <?php if(!empty($pedido['observacao'])): ?>
        <div style="margin-top: 10px; border: 1px dashed #000; padding: 6px; border-radius: 6px;">
            <b class="uppercase" style="font-size:10px;">Observação do Pedido:</b><br>
            <span class="uppercase font-bold text-sm"><?php echo $pedido['observacao']; ?></span>
        </div>
    <?php endif; ?>

    <div class="text-center" style="margin-top: 15px; font-size: 9px; color: #555;">
        <b>www.clicoupediu.app.br</b>
    </div>

</div>


</body>
</html>