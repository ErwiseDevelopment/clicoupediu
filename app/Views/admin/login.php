<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ClicouPediu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    
    <div class="max-w-md w-full">
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-600 rounded-xl shadow-lg mb-4">
                <i class="fas fa-shopping-basket text-white text-xl"></i>
            </div>
            <h1 class="text-2xl font-800 text-slate-900 tracking-tight">ClicouPediu<span class="text-blue-600">.app</span></h1>
            <p class="text-slate-400 font-semibold text-[10px] uppercase tracking-[0.2em] mt-1">Painel Administrativo</p>
        </div>

        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 p-8 border border-slate-100">
            
            <?php if(isset($_GET['erro'])): ?>
                <div class="bg-red-50 text-red-600 px-4 py-3 rounded-xl mb-6 text-xs font-bold flex items-center gap-2">
                    <i class="fas fa-circle-exclamation"></i> Dados incorretos.
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'criada'): ?>
                <div class="bg-green-50 text-green-600 px-4 py-3 rounded-xl mb-6 text-xs font-bold flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> Conta criada! Faça seu login.
                </div>
            <?php endif; ?>

            <form action="<?php echo BASE_URL; ?>/admin/auth" method="POST" class="space-y-5">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">E-mail</label>
                    <input type="email" name="email" required placeholder="seu@email.com"
                           class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:border-blue-600 focus:ring-4 focus:ring-blue-50 outline-none transition-all text-sm font-semibold text-slate-700">
                </div>

                <div class="space-y-1.5">
                    <div class="flex justify-between items-center px-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Senha</label>
                    </div>
                    <div class="relative">
                        <input type="password" name="senha" id="senha" required placeholder="••••••••"
                               class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:border-blue-600 focus:ring-4 focus:ring-blue-50 outline-none transition-all text-sm font-semibold text-slate-700">
                        <button type="button" onclick="toggleSenha()" class="absolute right-4 top-3.5 text-slate-400 hover:text-blue-600 transition-colors">
                            <i id="eye-icon" class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full bg-slate-900 hover:bg-blue-600 text-white font-bold py-4 rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 text-sm">
                    Acessar Painel <i class="fas fa-arrow-right text-[10px]"></i>
                </button>
            </form>
            
            <div class="mt-8 text-center">
                <a href="<?php echo BASE_URL; ?>/cadastro" class="text-xs font-semibold text-slate-400 hover:text-blue-600 transition-colors">
                    Não tem conta? <span class="text-blue-600">Cadastre sua loja</span>
                </a>
            </div>
        </div>
        
        <p class="mt-10 text-center text-[9px] font-bold text-slate-300 uppercase tracking-widest">
            &copy; 2026 ClicouPediu.app.br
        </p>
    </div>

    <script>
        function toggleSenha() {
            const input = document.getElementById('senha');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>