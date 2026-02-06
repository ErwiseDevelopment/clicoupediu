<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cupom #<?php echo str_pad($pedido['id'], 4, '0', STR_PAD_LEFT); ?></title>
    <style>
        /* Reset e Configurações de Impressão */
        @page { margin: 0; padding: 0; }
        body { 
            margin: 0; 
            padding: 5px; 
            background-color: #fff; 
            font-family: 'Courier New', Courier, monospace; 
            font-size: 11px;
            color: #000;
            width: 58mm; /* Largura padrão térmica 58mm ou 80mm */
            max-width: 58mm;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        .line { border-bottom: 1px dashed #000; margin: 5px 0; }
        .line-solid { border-bottom: 1px solid #000; margin: 5px 0; }
        
        .header { margin-bottom: 10px; }
        .loja-nome { font-size: 14px; font-weight: bold; margin-bottom: 2px; }
        .loja-info { font-size: 10px; }
        
        .info-pedido { margin-bottom: 5px; }
        .badge-tipo { 
            background: #000; 
            color: #fff; 
            padding: 2px 0; 
            display: block; 
            margin: 5px 0; 
            font-weight: bold; 
            -webkit-print-color-adjust: exact; 
        }

        .item-row { display: flex; margin-bottom: 5px; }
        .item-qtd { width: 20px; font-weight: bold; vertical-align: top; }
        .item-nome { flex: 1; overflow: hidden; }
        .item-valor { white-space: nowrap; margin-left: 2px; text-align: right; font-weight: bold; }

        .complemento { font-size: 10px; color: #333; display: block; margin-left: 5px; }
        .obs-item { font-size: 10px; font-weight: bold; display: block; margin-top: 2px; }

        .totais { margin-top: 5px; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 2px; }
        .total-final { font-size: 14px; font-weight: bold; margin-top: 3px; border-top: 1px dashed #000; padding-top: 3px; }

        .cliente-box { border: 1px solid #000; padding: 5px; margin-top: 5px; border-radius: 4px; }
        
        .sistema-footer {
            margin-top: 15px;
            padding-top: 5px;
            border-top: 1px solid #000;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header text-center">
        <div class="loja-nome"><?php echo $loja['nome_fantasia']; ?></div>
        <?php if(!empty($loja['endereco_completo'])): ?>
            <div class="loja-info"><?php echo $loja['endereco_completo']; ?></div>
        <?php endif; ?>
        <?php if(!empty($loja['telefone_whatsapp'])): ?>
            <div class="loja-info">Tel: <?php echo $loja['telefone_whatsapp']; ?></div>
        <?php endif; ?>
    </div>

    <div class="line"></div>

    <div class="info-pedido text-center">
        <div class="font-bold" style="font-size: 12px;">PEDIDO #<?php echo str_pad($pedido['id'], 4, '0', STR_PAD_LEFT); ?></div>
        <div><?php echo date('d/m/Y H:i', strtotime($pedido['created_at'])); ?></div>
        <div class="badge-tipo text-center uppercase">
            <?php echo $pedido['tipo_entrega'] == 'entrega' ? 'ENTREGA' : 'RETIRADA / BALCÃO'; ?>
        </div>
    </div>

    <div class="line-solid"></div>
    <div class="font-bold" style="margin-bottom: 4px;">ITENS</div>
    
    <?php foreach($itens as $item): ?>
    <div class="item-row">
        <div class="item-qtd"><?php echo $item['quantidade']; ?>x</div>
        <div class="item-nome">
            <?php echo $item['p_nome']; ?>

            <?php if(!empty($item['complementos'])): ?>
                <?php foreach($item['complementos'] as $add): ?>
                    <span class="complemento">+ <?php echo $add['nome']; ?></span>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if(!empty($item['observacao_item'])): ?>
                <span class="obs-item">OBS: <?php echo $item['observacao_item']; ?></span>
            <?php endif; ?>
        </div>
        <div class="item-valor">R$ <?php echo number_format($item['total'], 2, ',', '.'); ?></div>
    </div>
    <?php endforeach; ?>

    <div class="line"></div>

    <div class="totais">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>R$ <?php echo number_format($pedido['valor_produtos'], 2, ',', '.'); ?></span>
        </div>
        <div class="total-row">
            <span>Taxa Entrega:</span>
            <span>R$ <?php echo number_format($pedido['taxa_entrega'], 2, ',', '.'); ?></span>
        </div>
        <?php if($pedido['desconto'] > 0): ?>
        <div class="total-row">
            <span>Desconto:</span>
            <span>- R$ <?php echo number_format($pedido['desconto'], 2, ',', '.'); ?></span>
        </div>
        <?php endif; ?>
        
        <div class="total-row total-final">
            <span>TOTAL:</span>
            <span>R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></span>
        </div>
    </div>

    <div class="line"></div>

    <div class="total-row">
        <span>Pagamento:</span>
        <span class="uppercase font-bold"><?php echo $pedido['forma_pagamento']; ?></span>
    </div>

    <?php if($pedido['forma_pagamento'] == 'dinheiro'): ?>
        <?php 
            $trocoPara = floatval($pedido['troco_para']);
            $totalPedido = floatval($pedido['valor_total']);
            $trocoDevolver = $trocoPara - $totalPedido;
        ?>
        <?php if($trocoPara > 0): ?>
            <div class="total-row">
                <span>Troco para:</span>
                <span>R$ <?php echo number_format($trocoPara, 2, ',', '.'); ?></span>
            </div>
            <div class="total-row">
                <span>Lev. Troco:</span>
                <span class="font-bold">R$ <?php echo number_format($trocoDevolver, 2, ',', '.'); ?></span>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="line"></div>

    <div class="cliente-box">
        <div class="text-center font-bold uppercase mb-1">Cliente</div>
        <div class="font-bold"><?php echo $pedido['cliente_nome']; ?></div>
        <div><?php echo $pedido['cliente_telefone']; ?></div>
        
        <?php if($pedido['tipo_entrega'] == 'entrega'): ?>
            <div class="line" style="margin: 3px 0;"></div>
            <div style="font-size: 10px;">
                <b>Endereço:</b><br>
                <?php echo $pedido['endereco_entrega']; ?>, <?php echo $pedido['numero']; ?><br>
                <?php echo $pedido['bairro']; ?>
                <?php if(!empty($pedido['complemento'])): ?>
                    <br>Obs: <?php echo $pedido['complemento']; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if(!empty($pedido['observacao'])): ?>
        <div style="margin-top: 10px; border: 1px dashed #000; padding: 5px;">
            <b>OBSERVAÇÃO GERAL:</b><br>
            <?php echo $pedido['observacao']; ?>
        </div>
    <?php endif; ?>

    <div class="text-center" style="margin-top: 10px; font-size: 9px;">
        *** NÃO É DOCUMENTO FISCAL ***
    </div>

    <div class="sistema-footer">
        clicoupediu.app.br
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>