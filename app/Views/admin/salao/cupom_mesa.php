<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Conta Mesa <?php echo $mesa['numero']; ?></title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Courier New', Courier, monospace; width: 100%; margin: 0; padding: 5px; font-size: 12px; color: #000; }
        .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 5px; margin-bottom: 10px; }
        .empresa { font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .mesa-titulo { font-size: 16px; font-weight: bold; margin: 5px 0; display: block; }
        
        .participante-box { margin-bottom: 15px; border-bottom: 1px dotted #000; padding-bottom: 5px; }
        .participante-nome { font-weight: bold; font-size: 13px; text-transform: uppercase; background: #eee; display: block; padding: 2px; margin-bottom: 5px; }
        
        .item { display: flex; margin-bottom: 3px; }
        .qtd { width: 25px; font-weight: bold; }
        .nome { flex: 1; }
        .valor { text-align: right; white-space: nowrap; }
        .sub-info { font-size: 10px; color: #333; margin-left: 25px; }
        
        .subtotal-part { text-align: right; font-weight: bold; margin-top: 5px; font-size: 12px; }
        
        .total-box { border-top: 2px dashed #000; margin-top: 10px; padding-top: 10px; text-align: right; font-size: 16px; font-weight: bold; }
        .footer { text-align: center; margin-top: 20px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="empresa"><?php echo $empresa['nome_fantasia']; ?></div>
        <span class="mesa-titulo">MESA <?php echo $mesa['numero']; ?></span>
        <div><?php echo date('d/m/Y H:i'); ?></div>
        <div>*** CONFERÊNCIA DE CONTA ***</div>
    </div>

    <?php foreach($dadosParticipantes as $p): ?>
        <?php if(!empty($p['itens'])): ?>
            <div class="participante-box">
                <span class="participante-nome">
                    <i class="fas fa-user"></i> <?php echo $p['nome']; ?>
                    <?php if($p['status_pagamento'] == 'pago'): ?> (PAGO) <?php endif; ?>
                </span>
                
                <?php foreach($p['itens'] as $i): ?>
                    <div class="item">
                        <div class="qtd"><?php echo $i['qtd']; ?></div>
                        <div class="nome">
                            <?php echo $i['nome']; ?>
                            <?php if(!empty($i['adds'])): ?>
                                <div class="sub-info">+ <?php echo implode(', ', $i['adds']); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="valor"><?php echo number_format($i['total'], 2, ',', '.'); ?></div>
                    </div>
                <?php endforeach; ?>

                <div class="subtotal-part">
                    Subtotal: R$ <?php echo number_format($p['subtotal'], 2, ',', '.'); ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="total-box">
        TOTAL GERAL: R$ <?php echo number_format($totalGeral, 2, ',', '.'); ?>
    </div>

    <div class="footer">
        Não possui valor fiscal.<br>
        Taxa de serviço não inclusa.<br>
        Obrigado pela preferência!
    </div>
    
    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>