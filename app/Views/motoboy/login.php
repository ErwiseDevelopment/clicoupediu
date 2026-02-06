<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Entregador | Acesso</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-900 flex items-center justify-center h-screen px-4">

    <div class="bg-white w-full max-w-sm rounded-2xl p-8 shadow-2xl">
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-motorcycle text-3xl text-blue-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Área do Motoboy</h1>
            <p class="text-gray-500 text-sm mt-1">Digite seu WhatsApp cadastrado</p>
        </div>

        <?php if(isset($_GET['erro'])): ?>
            <div class="bg-red-100 text-red-600 p-3 rounded-lg text-sm text-center mb-4 font-bold border border-red-200">
                <i class="fas fa-times-circle mr-1"></i> Número não encontrado!
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/app-motoboy/autenticar" method="POST">
            <div class="mb-6">
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">WhatsApp (Só números)</label>
                <input type="tel" name="whatsapp" required placeholder="Ex: 11999998888" 
                       class="w-full border-2 border-gray-200 rounded-xl px-4 py-4 text-xl font-bold text-center outline-none focus:border-blue-500 transition placeholder-gray-300">
            </div>

            <button type="submit" class="w-full bg-gray-900 text-white py-4 rounded-xl font-bold text-lg hover:bg-black transition shadow-lg flex items-center justify-center gap-2">
                <span>ACESSAR</span> <i class="fas fa-arrow-right"></i>
            </button>
        </form>
    </div>

</body>
</html>