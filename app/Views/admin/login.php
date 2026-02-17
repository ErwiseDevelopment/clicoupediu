<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ClicouPediu</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
     <link rel="shortcut icon" href="<?= BASE_URL ?>/assets/img/favicon.png" type="image/png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Padrão de fundo moderno para o lado direito */
        .bg-pattern {
            background-color: #0f172a;
            background-image: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                              radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                              radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
        }
        
        /* Animação suave nos inputs */
        input:focus + i { color: #2563eb; }
    </style>
</head>
<body class="bg-white h-screen w-full flex overflow-hidden">

    <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-8 lg:p-12 relative z-10 bg-white">
        
        <div class="w-full max-w-[400px]">
            <div class="mb-10 text-center lg:text-left">
                <img src="<?php echo BASE_URL; ?>/assets/img/logo.png" alt="ClicouPediu">
            </div>

            <div class="mb-8 text-center lg:text-left">
                <h1 class="text-3xl font-bold text-slate-900 mb-2">Bem-vindo de volta!</h1>
                <p class="text-slate-500 text-sm">Digite suas credenciais para acessar o painel.</p>
            </div>

            <form action="<?php echo BASE_URL; ?>/admin/auth" method="POST" class="space-y-5">
                
                <?php if (isset($_GET['erro'])): ?>
                <div class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm rounded-r flex items-center gap-3">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Email ou senha incorretos.</span>
                </div>
                <?php endif; ?>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5 ml-1">Email Corporativo</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="far fa-envelope text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
                        </div>
                        <input type="email" name="email" required 
                               class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition-all text-slate-800 placeholder-slate-400" 
                               placeholder="ex: seu@email.com">
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1.5 ml-1">
                        <label class="block text-sm font-semibold text-slate-700">Senha</label>
                    </div>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
                        </div>
                        <input type="password" name="senha" id="senha" required 
                               class="w-full pl-11 pr-12 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition-all text-slate-800 placeholder-slate-400" 
                               placeholder="••••••••">
                        
                        <button type="button" onclick="toggleSenha()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer focus:outline-none">
                            <i class="far fa-eye" id="eye-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <div class="flex items-center">
                        <input id="lembrar_me" name="lembrar_me" type="checkbox" 
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer transition-all">
                        <label for="lembrar_me" class="ml-2 block text-sm text-slate-600 cursor-pointer select-none">
                            Manter conectado
                        </label>
                    </div>
                    <a href="#" class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                        Esqueceu a senha?
                    </a>
                </div>

                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-blue-500/30 active:scale-[0.98] transition-all duration-200 flex items-center justify-center gap-2">
                    <span>Entrar no Sistema</span>
                    <i class="fas fa-arrow-right text-sm"></i>
                </button>

            </form>

            <div class="mt-8 text-center border-t border-slate-100 pt-6">
                <p class="text-sm text-slate-500">
                    Ainda não tem uma conta? 
                    <a href="<?php echo BASE_URL; ?>/cadastro" class="font-bold text-blue-600 hover:text-blue-800 transition-colors">
                        Criar loja grátis
                    </a>
                </p>
            </div>
        </div>
        
        <p class="absolute bottom-6 text-center text-[10px] text-slate-400 uppercase tracking-widest font-semibold">
            &copy; 2026 ClicouPediu Tecnologia
        </p>
    </div>

    <div class="hidden lg:flex w-1/2 bg-pattern relative items-center justify-center overflow-hidden">
        
        <div class="absolute top-0 left-0 w-full h-full bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
        
        <div class="relative z-10 bg-white/10 backdrop-blur-lg border border-white/10 p-10 rounded-3xl max-w-md text-white shadow-2xl">
            <div class="w-14 h-14 bg-blue-500 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-blue-500/40">
                <i class="fas fa-rocket text-2xl"></i>
            </div>
            <h2 class="text-3xl font-bold mb-4 leading-tight">Gerencie seu delivery com inteligência.</h2>
            <p class="text-blue-100 text-lg leading-relaxed mb-6">
                Acompanhe pedidos em tempo real, controle o caixa e imprima automaticamente na cozinha. Tudo em um só lugar.
            </p>
            
            <div class="flex items-center gap-3 pt-4 border-t border-white/10">
                <div class="flex -space-x-2">
                    <img class="w-8 h-8 rounded-full border-2 border-slate-800" src="https://i.pravatar.cc/100?img=1" alt="">
                    <img class="w-8 h-8 rounded-full border-2 border-slate-800" src="https://i.pravatar.cc/100?img=2" alt="">
                    <img class="w-8 h-8 rounded-full border-2 border-slate-800" src="https://i.pravatar.cc/100?img=3" alt="">
                </div>
                <div class="text-sm font-medium text-blue-200">
                    +2.000 Lojas ativas
                </div>
            </div>
        </div>

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