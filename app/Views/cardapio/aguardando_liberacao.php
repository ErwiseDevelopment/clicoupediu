<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aguardando Liberação</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @keyframes pulse-ring {
            0% { transform: scale(0.8); opacity: 0.5; }
            100% { transform: scale(2); opacity: 0; }
        }
        .ring-animation::before {
            content: '';
            position: absolute;
            width: 100%; height: 100%;
            border-radius: 50%;
            border: 2px solid white;
            animation: pulse-ring 2s infinite;
        }
    </style>
</head>
<body class="bg-indigo-600 h-screen flex flex-col items-center justify-center text-white px-6 text-center">

    <div class="relative w-24 h-24 flex items-center justify-center bg-indigo-500 rounded-full mb-8 ring-animation">
        <i class="fas fa-utensils text-4xl"></i>
    </div>

    <h1 class="text-2xl font-bold mb-3">Solicitação Enviada!</h1>
    <p class="text-indigo-100 text-sm mb-10 leading-relaxed">
        Já avisamos a nossa equipe.<br>
        Por favor, aguarde um instante enquanto liberamos o cardápio para sua mesa.
    </p>

    <div class="bg-indigo-800/50 rounded-xl p-4 flex items-center gap-3 w-full max-w-xs mx-auto backdrop-blur-sm border border-indigo-500/30">
        <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin shrink-0"></div>
        <div class="text-left">
            <p class="text-xs font-bold text-indigo-200 uppercase">Status</p>
            <p class="text-sm font-semibold">Aguardando aprovação...</p>
        </div>
    </div>

    <script>
        // Poll para verificar se o garçom aprovou
        const checkInterval = setInterval(() => {
            const formData = new FormData();
            formData.append('hash', '<?= $hashMesa ?>');

            fetch('<?= BASE_URL ?>/cardapio/apiChecarStatus', { // Ajuste a rota conforme seu roteamento
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'liberado') {
                    clearInterval(checkInterval);
                    // Recarrega a página. Como agora está aprovado, 
                    // o PHP vai redirecionar para o cardápio automaticamente.
                    window.location.reload(); 
                }
            })
            .catch(err => console.error(err));
        }, 3000); // Verifica a cada 3 segundos
    </script>

</body>
</html>