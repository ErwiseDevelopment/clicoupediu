<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cozinha #<?php echo $pedido['id']; ?></title>
    <style>
        @media print {
            @page { margin: 0; size: auto; }
            body { margin: 0; padding: 5px; }
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 100%;
            max-width: 80mm; /* Largura padrão térmica */
            margin: 0 auto;
            background: #fff;
            color: #000;
        }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 10px; }
        .titulo { font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .numero-pedido { font-size: 40px; font-weight: 900; margin: 5px 0; display: block; line-height: 1; }
        .cliente { font-size: 16px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; display: block;}
        .tipo-entrega { background: #000; color: #fff; padding: 2px 5px; font-weight: bold; font-size: 14px; text-transform: uppercase; display: inline-block; margin-top: 5px; border-radius: 4px;}
        
        .item-box { margin-bottom: 15px; border-bottom: 1px dashed #000; padding-bottom: 10px; }
        .item-header { display: flex; align-items: flex-start; }
        .qtd { font-size: 22px; font-weight: 900; width: 35px; text-align: center; margin-right: 5px; }
        .nome-prod { font-size: 18px; font-weight: bold; line-height: 1.1; flex: 1; text-transform: uppercase; }
        
        .complementos { margin-top: 5px; padding-left: 40px; font-size: 14px; }
        .adicional { display: block; font-weight: bold; }
        .obs { 
            display: block; 
            background: #000; 
            color: #fff; 
            padding: 2px 5px; 
            margin-top: 5px; 
            font-weight: bold; 
            font-size: 14px; 
            text-transform: uppercase;
            border-radius: 4px;
        }

        .footer { text-align: center; margin-top: 20px; font-size: 10px; border-top: 1px solid #000; padding-top: 5px;}
    </style>
</head>
<body>
    
    <div class="header">
        <span class="titulo">Ordem de Produção</span>
        <span class="numero-pedido">#<?php echo $pedido['id']; ?></span>
        <span class="cliente"><?php echo $pedido['cliente_nome']; ?></span>
        
        <?php if($pedido['tipo_entrega'] == 'salao'): ?>
            <span class="tipo-entrega">MESA <?php echo $pedido['num_mesa'] ?? '?'; ?></span>
        <?php else: ?>
            <span class="tipo-entrega">DELIVERY</span>
        <?php endif; ?>
        
        <div style="font-size: 12px; margin-top: 5px;">
            <?php echo date('d/m/Y H:i', strtotime($pedido['created_at'])); ?>
        </div>
    </div>

    <div class="itens">
        <?php foreach($itens as $item): ?>
            <div class="item-box">
                <div class="item-header">
                    <div class="qtd"><?php echo $item['quantidade']; ?></div>
                    <div class="nome-prod"><?php echo $item['produto_nome'] ?? $item['nome']; ?></div>
                </div>

                <div class="complementos">
                    <?php if(!empty($item['observacao_item'])): ?>
                        <span class="obs">OBS: <?php echo $item['observacao_item']; ?></span>
                    <?php endif; ?>

                    <?php if(!empty($item['complementos'])): ?>
                        <?php foreach($item['complementos'] as $add): ?>
                            <span class="adicional">+ <?php echo is_array($add) ? $add['nome'] : $add; ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="footer">
        --- FIM DO PEDIDO ---
    </div>

    <script>
        window.onload = function() {
            window.print();
            // Opcional: Fecha a janela após imprimir (alguns navegadores bloqueiam)
            // setTimeout(function(){ window.close(); }, 500);
        }
    </script>
</body>
</html>