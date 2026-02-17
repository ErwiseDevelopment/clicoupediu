<!DOCTYPE html>
<html lang="pt-br" class="scroll-smooth overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>clicoupediu.app.br | Sistema Completo para Delivery sem Comissões</title>
    <meta name="description" content="Tenha seu próprio aplicativo de delivery, cardápio digital, KDS e gestão de motoboys por apenas R$150/mês.">
    
    <link rel="shortcut icon" href="<?= BASE_URL ?>/assets/img/favicon.png" type="image/png">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        h1, h2, h3, .font-display { font-family: 'Poppins', sans-serif; }
        
        /* Gradientes Baseados na Identidade Azul */
        .bg-gradient-primary { background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%); } 
        .text-gradient-primary { background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        
        /* Fundo com malha de pontos */
        .bg-mesh { background-color: #ffffff; background-image: radial-gradient(at 40% 20%, rgba(29, 78, 216, 0.08) 0px, transparent 50%), radial-gradient(at 80% 0%, rgba(30, 58, 138, 0.08) 0px, transparent 50%), radial-gradient(at 0% 50%, rgba(29, 78, 216, 0.08) 0px, transparent 50%), radial-gradient(at 80% 50%, rgba(30, 58, 138, 0.08) 0px, transparent 50%), radial-gradient(at 0% 100%, rgba(29, 78, 216, 0.08) 0px, transparent 50%), radial-gradient(at 80% 100%, rgba(30, 58, 138, 0.08) 0px, transparent 50%), radial-gradient(at 0% 0%, rgba(29, 78, 216, 0.08) 0px, transparent 50%); }

        .feature-card { position: relative; overflow: hidden; z-index: 1; }
        .feature-card::after { content: ''; position: absolute; top: 0; left: -100%; width: 200%; height: 100%; background: linear-gradient(115deg, transparent 0%, rgba(255,255,255,0.4) 45%, rgba(255,255,255,0.0) 55%, transparent 100%); transition: all 0.5s; z-index: -1; }
        .feature-card:hover::after { left: 100%; }

        .phone-mockup { border-radius: 30px; border: 8px solid #111827; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); aspect-ratio: 9/18; background: #fff; position: relative; }
        .phone-notch { position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 40%; height: 20px; bg-gray-900; border-bottom-left-radius: 15px; border-bottom-right-radius: 15px; z-index: 10; background: #111827;}
        .tablet-mockup { border-radius: 20px; border: 8px solid #111827; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); aspect-ratio: 16/10; background: #fff; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 overflow-x-hidden">

    <nav class="fixed w-full bg-white/90 backdrop-blur-lg z-50 border-b border-slate-100 py-3 transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <div class="flex items-center gap-2 flex-shrink-0">
                <img src="<?= BASE_URL ?>/assets/img/logosidebar.png" alt="Logo clicoupediu.app.br" class="h-12 md:h-16 w-auto object-contain">
            </div>
            
            <div class="hidden md:flex items-center space-x-8 font-semibold text-sm text-slate-600">
                <a href="#funcionalidades" class="hover:text-blue-700 transition">Funcionalidades</a>
                <a href="#planos" class="hover:text-blue-700 transition">Planos e Preços</a>
            </div>

            <div class="flex items-center gap-3">
                <a href="<?= BASE_URL ?>/admin" class="hidden sm:flex items-center gap-2 font-bold text-sm text-slate-700 border-2 border-slate-200 px-4 py-2 rounded-xl hover:border-blue-700 hover:text-blue-700 transition">
                    <i class="fas fa-lock text-xs opacity-70"></i> Entrar
                </a>
                <a href="<?= BASE_URL ?>/cadastro" class="bg-gradient-primary text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-blue-200 transition transform hover:-translate-y-1 active:scale-95 flex items-center gap-2">
                    Criar Loja Grátis <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </nav>

    <header class="bg-mesh pt-36 pb-20 md:pt-48 md:pb-32 overflow-hidden relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                
                <div data-aos="fade-right" data-aos-duration="1000">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200 text-blue-700 text-xs font-bold uppercase tracking-wide mb-6">
                        <i class="fas fa-bolt animate-pulse text-blue-600"></i> Sistema de Alta Performance
                    </div>
                    
                    <h1 class="text-4xl md:text-6xl font-black text-slate-900 tracking-tight leading-none mb-6">
                        Seu Delivery Próprio, <br>
                        <span class="text-gradient-primary">Suas Regras.</span>
                    </h1>
                    
                    <p class="text-lg text-slate-600 mb-8 leading-relaxed max-w-xl">
                        Fim da bagunça no WhatsApp. Tenha um sistema completo com Cardápio Digital, Monitor de Cozinha, Frente de Caixa e Painel de Motoboys por um <strong>preço único</strong>.
                    </p>
                    
                    <div class="flex flex-wrap gap-4">
                        <a href="#planos" class="bg-slate-900 text-white px-8 py-4 rounded-2xl font-black text-lg shadow-xl hover:bg-slate-800 transition flex items-center gap-3">
                            Ver o Plano Único <i class="fas fa-arrow-down"></i>
                        </a>
                        <p class="text-sm text-slate-500 flex items-center gap-2 mt-3 w-full sm:w-auto justify-center sm:justify-start">
                            <i class="fas fa-check-circle text-blue-600"></i> Sem taxas escondidas. Sem comissão.
                        </p>
                    </div>
                </div>

                <div class="relative hidden lg:block" data-aos="fade-left" data-aos-delay="200" data-aos-duration="1000">
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-blue-600/10 rounded-full blur-3xl -z-10 animate-pulse"></div>
                    
                    <div class="relative flex items-center justify-center">
                         <div class="tablet-mockup w-[550px] absolute top-12 -right-12 rotate-3 opacity-95 shadow-2xl border-slate-900 bg-slate-100 p-2">
                            <div class="h-full w-full bg-white rounded-lg overflow-hidden relative border border-gray-200">
                                <div class="bg-slate-900 text-white p-3 flex justify-between items-center">
                                    <span class="font-bold flex items-center gap-2"><i class="fas fa-fire text-red-500"></i> KDS Cozinha</span>
                                    <span class="text-xs bg-slate-700 px-2 py-1 rounded">3 Pedidos em Fila</span>
                                </div>
                                <div class="p-3 flex gap-3">
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 w-1/3 shadow-sm">
                                        <div class="font-bold text-slate-800 mb-2">#1024 - Entrega</div>
                                        <div class="space-y-1">
                                            <div class="text-xs text-slate-700 font-semibold">1x X-Tudo Monstro</div>
                                            <div class="text-[10px] text-slate-500">+ Bacon Extra</div>
                                            <div class="text-xs text-slate-700 font-semibold mt-2">1x Coca-Cola 2L</div>
                                        </div>
                                    </div>
                                     <div class="bg-gray-50 border border-slate-200 opacity-60 rounded-lg p-3 w-1/3"></div>
                                     <div class="bg-gray-50 border border-slate-200 opacity-60 rounded-lg p-3 w-1/3"></div>
                                </div>
                            </div>
                         </div>

                        <div class="phone-mockup w-[280px] relative z-10 -rotate-3 shadow-[0_35px_60px_-15px_rgba(0,0,0,0.3)] border-slate-900">
                            <div class="phone-notch"></div>
                            <div class="h-full w-full bg-gray-50 flex flex-col">
                                <div class="bg-white p-4 pt-10 pb-3 shadow-sm z-10 flex items-center justify-between">
                                    <div>
                                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">Bem-vindo ao</div>
                                        <div class="font-bold text-slate-900 leading-none">clicou<span class="text-blue-700">pediu</span></div>
                                    </div>
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 shadow-sm">
                                        <i class="fas fa-shopping-bag text-xs"></i>
                                    </div>
                                </div>
                                <div class="p-4 space-y-4 overflow-hidden flex-1 bg-white rounded-t-3xl shadow-[0_-10px_20px_rgba(0,0,0,0.02)] relative mt-2 border-t border-gray-100">
                                    <div class="flex gap-3 overflow-hidden opacity-80">
                                        <div class="h-20 w-32 bg-blue-600 rounded-xl shrink-0 flex items-center justify-center text-white/50"><i class="fas fa-image text-2xl"></i></div>
                                        <div class="h-20 w-32 bg-slate-200 rounded-xl shrink-0"></div>
                                    </div>
                                    <div class="h-24 bg-white border border-slate-100 rounded-2xl p-3 flex gap-3 shadow-sm">
                                        <div class="w-20 h-20 bg-slate-100 rounded-lg shrink-0"></div>
                                        <div class="flex-1 py-1">
                                            <div class="h-4 w-3/4 bg-slate-200 rounded mb-2"></div>
                                            <div class="h-3 w-1/2 bg-slate-100 rounded"></div>
                                            <div class="h-4 w-1/3 bg-blue-100 rounded mt-3"></div>
                                        </div>
                                    </div>
                                     <div class="h-24 bg-white border border-slate-100 rounded-2xl p-3 flex gap-3 shadow-sm opacity-50"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section id="vantagens" class="bg-slate-900 py-12 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 flex flex-col md:flex-row items-center justify-between gap-8 text-center md:text-left">
            <div>
                <h2 class="text-3xl md:text-4xl font-black text-white uppercase tracking-wide drop-shadow-lg">
                    <span class="text-blue-500">Chega</span> de pagar comissões.
                </h2>
                <p class="text-slate-300 text-lg mt-2">O lucro do seu delivery deve ser seu. Venda sem limites por um valor fixo.</p>
            </div>
            <div class="flex items-center justify-center gap-6 shrink-0 bg-slate-800/50 p-4 rounded-2xl border border-slate-700">
                 <div class="text-center">
                    <div class="text-3xl font-black text-white">0%</div>
                    <div class="text-xs text-slate-400 uppercase font-bold">Taxa por Pedido</div>
                 </div>
                 <div class="h-12 w-px bg-slate-700"></div>
                 <div class="text-center">
                    <div class="text-3xl font-black text-blue-400">100%</div>
                    <div class="text-xs text-slate-400 uppercase font-bold">Do Lucro é Seu</div>
                 </div>
            </div>
        </div>
    </section>

    <section id="funcionalidades" class="py-24 bg-slate-50 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="font-display text-3xl md:text-5xl font-black text-slate-900 mb-4">
                    Tudo para sua operação <span class="text-blue-700">decolar</span> 🚀
                </h2>
                <p class="text-lg text-slate-500 max-w-2xl mx-auto">Ferramentas integradas que eliminam a bagunça, reduzem erros e aumentam sua velocidade de atendimento.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                
                <div class="feature-card bg-white p-8 rounded-[2rem] border-2 border-slate-100 shadow-sm hover:border-blue-200 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 group" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-16 h-16 bg-blue-50 text-blue-700 rounded-2xl flex items-center justify-center text-3xl mb-6 group-hover:rotate-6 transition shadow-sm border border-blue-100">
                        <i class="fas fa-store"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-4">Cardápio Digital Interativo</h3>
                    <p class="text-slate-600 leading-relaxed mb-6">Fim das mensagens manuais! O cliente pede, o frete é calculado pelo Google e o pedido entra direto no sistema.</p>
                    <ul class="space-y-2 text-sm font-semibold text-slate-500">
                        <li class="flex items-center gap-2"><i class="fas fa-check text-blue-600"></i> Fotos e opcionais completos</li>
                        <li class="flex items-center gap-2"><i class="fas fa-check text-blue-600"></i> Cálculo automático de frete</li>
                        <li class="flex items-center gap-2"><i class="fas fa-check text-blue-600"></i> Fim dos pedidos confusos</li>
                    </ul>
                </div>

                <div class="feature-card bg-slate-900 p-8 rounded-[2rem] border-2 border-slate-800 shadow-xl hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 group text-white md:scale-105 z-10 relative" data-aos="fade-up" data-aos-delay="200">
                    <div class="absolute top-0 right-0 bg-blue-600 text-white text-[10px] font-black px-3 py-1.5 rounded-bl-xl rounded-tr-[1.8rem] tracking-widest uppercase">Mais Popular</div>
                    <div class="w-16 h-16 bg-white/10 text-blue-400 rounded-2xl flex items-center justify-center text-3xl mb-6 group-hover:rotate-6 transition border border-white/20">
                        <i class="fas fa-fire-burner"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-4">Monitor de Cozinha (KDS)</h3>
                    <p class="text-slate-300 leading-relaxed mb-6">A revolução da sua cozinha. Os pedidos aparecem na tela, organizados por tempo e status, sem precisar imprimir papel.</p>
                    <ul class="space-y-2 text-sm font-semibold text-slate-300">
                        <li class="flex items-center gap-2"><i class="fas fa-check text-blue-400"></i> Alerta sonoro de novo pedido</li>
                        <li class="flex items-center gap-2"><i class="fas fa-check text-blue-400"></i> Separação bebida x prato</li>
                        <li class="flex items-center gap-2"><i class="fas fa-check text-blue-400"></i> Histórico em tempo real</li>
                    </ul>
                </div>

                <div class="feature-card bg-white p-8 rounded-[2rem] border-2 border-slate-100 shadow-sm hover:border-indigo-200 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 group" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-3xl mb-6 group-hover:rotate-6 transition shadow-sm border border-indigo-100">
                        <i class="fas fa-motorcycle"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-4">App do Entregador</h3>
                    <p class="text-slate-600 leading-relaxed mb-6">Seus motoboys têm um painel próprio. Eles veem as entregas e abrem a melhor rota no GPS com um único clique.</p>
                    <ul class="space-y-2 text-sm font-semibold text-slate-500">
                        <li class="flex items-center gap-2"><i class="fas fa-check text-indigo-500"></i> Roteirização inteligente</li>
                        <li class="flex items-center gap-2"><i class="fas fa-check text-indigo-500"></i> Login seguro no celular</li>
                        <li class="flex items-center gap-2"><i class="fas fa-check text-indigo-500"></i> Motoboy não esquece a bebida</li>
                    </ul>
                </div>

                <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div class="feature-card bg-white p-6 rounded-3xl border-2 border-slate-100 flex items-start gap-4 hover:border-blue-200 transition group cursor-default" data-aos="fade-up" data-aos-delay="100">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition border border-blue-100">
                            <i class="fas fa-desktop"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-lg text-slate-900 mb-1">PDV Ágil (Caixa)</h4>
                            <p class="text-sm text-slate-500 font-medium leading-snug">Lance pedidos de balcão e telefone rapidamente. Autocomplete de cliente.</p>
                        </div>
                    </div>
                     <div class="feature-card bg-white p-6 rounded-3xl border-2 border-slate-100 flex items-start gap-4 hover:border-teal-200 transition group cursor-default" data-aos="fade-up" data-aos-delay="200">
                        <div class="w-12 h-12 bg-teal-50 text-teal-600 rounded-xl flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition border border-teal-100">
                            <i class="fas fa-qrcode"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-lg text-slate-900 mb-1">Gestão de Mesas</h4>
                            <p class="text-sm text-slate-500 font-medium leading-snug">QR Code nas mesas para o cliente pedir sozinho ou para o garçom usar.</p>
                        </div>
                    </div>
                     <div class="feature-card bg-white p-6 rounded-3xl border-2 border-slate-100 flex items-start gap-4 hover:border-blue-200 transition group cursor-default" data-aos="fade-up" data-aos-delay="300">
                        <div class="w-12 h-12 bg-blue-50 text-blue-700 rounded-xl flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition border border-blue-100">
                            <i class="fas fa-print"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-lg text-slate-900 mb-1">Impressão Nativa</h4>
                            <p class="text-sm text-slate-500 font-medium leading-snug">Spooler Windows para impressão automática de cupons na sua impressora térmica.</p>
                        </div>
                    </div>
                 </div>
            </div>
        </div>
    </section>

    <section id="planos" class="py-24 bg-white relative border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="font-display text-3xl md:text-5xl font-black text-slate-900 mb-4">
                    Simples, Justo e <span class="text-blue-700">Transparente</span> 💎
                </h2>
                <p class="text-lg text-slate-500 max-w-2xl mx-auto">Sem pegadinhas, sem taxas escondidas e sem comissão. Você paga um valor fixo e o sistema é todo seu.</p>
            </div>

            <div class="max-w-md mx-auto" data-aos="zoom-in" data-aos-delay="100">
                <div class="bg-slate-900 rounded-[2rem] p-8 md:p-10 border border-slate-800 shadow-2xl relative text-center text-white">
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-blue-600 text-white font-black px-6 py-2 rounded-full uppercase tracking-widest text-xs shadow-lg shadow-blue-500/50 border-2 border-slate-900 whitespace-nowrap">
                        Acesso Liberado
                    </div>
                    
                    <h3 class="text-2xl font-bold mb-2 mt-4 text-slate-100">Plano Único</h3>
                    <p class="text-slate-400 mb-8 text-sm">Tudo que você precisa para crescer hoje.</p>
                    
                    <div class="flex items-end justify-center gap-1 mb-8">
                        <span class="text-2xl font-bold text-slate-400 mb-2">R$</span>
                        <span class="text-7xl font-black text-white leading-none">150</span>
                        <span class="text-lg text-slate-400 font-medium mb-2">/mês</span>
                    </div>

                    <div class="h-px w-full bg-slate-800 mb-8"></div>

                    <ul class="space-y-4 text-left mb-10 font-medium text-slate-300 text-sm">
                        <li class="flex items-center gap-3"><i class="fas fa-check-circle text-blue-500 text-lg"></i> Pedidos Delivery Ilimitados (0% Taxa)</li>
                        <li class="flex items-center gap-3"><i class="fas fa-check-circle text-blue-500 text-lg"></i> Cardápio Digital (Link e QR Code)</li>
                        <li class="flex items-center gap-3"><i class="fas fa-check-circle text-blue-500 text-lg"></i> Sistema PDV de Frente de Caixa</li>
                        <li class="flex items-center gap-3"><i class="fas fa-check-circle text-blue-500 text-lg"></i> Monitor KDS (Tela da Cozinha)</li>
                        <li class="flex items-center gap-3"><i class="fas fa-check-circle text-blue-500 text-lg"></i> App/Painel dos Motoboys com GPS</li>
                        <li class="flex items-center gap-3"><i class="fas fa-check-circle text-blue-500 text-lg"></i> Integração com Impressoras Térmicas</li>
                    </ul>

                    <a href="<?= BASE_URL ?>/cadastro" class="block w-full bg-blue-600 hover:bg-blue-500 text-white py-4 rounded-xl font-black text-lg transition duration-300 shadow-lg shadow-blue-500/25">
                        Assinar Agora
                    </a>
                    <p class="mt-4 text-xs text-slate-500 font-medium">Cancele quando quiser. Sem fidelidade.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-gradient-primary py-24 relative overflow-hidden">
        <div class="max-w-4xl mx-auto px-4 relative z-10 text-center text-white" data-aos="zoom-in">
            <h2 class="font-display text-4xl md:text-5xl font-black mb-6 leading-tight drop-shadow-md">
                Chegou a hora de profissionalizar a sua operação.
            </h2>
            <p class="text-blue-100 text-lg font-medium mb-10 max-w-2xl mx-auto leading-relaxed">
                Pare de rasgar dinheiro pagando comissões altas e evite os erros do atendimento manual.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="<?= BASE_URL ?>/cadastro" class="bg-white text-blue-800 px-10 py-5 rounded-2xl font-black text-lg shadow-[0_10px_40px_-10px_rgba(0,0,0,0.3)] hover:bg-blue-50 hover:scale-105 transition duration-300 flex items-center justify-center gap-3">
                    QUERO MEU SISTEMA <i class="fas fa-arrow-right"></i>
                </a>
                 <a href="<?= BASE_URL ?>/admin" class="bg-transparent border-2 border-white/30 text-white px-10 py-5 rounded-2xl font-bold text-lg hover:bg-white/10 hover:border-white transition duration-300 flex items-center justify-center gap-2">
                    <i class="fas fa-lock text-sm opacity-70"></i> Já sou cliente
                </a>
            </div>
        </div>
    </section>
<footer class="bg-slate-950 border-t border-slate-900 pt-16 pb-8 text-slate-400">
        <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
            <div class="md:col-span-2">
                 <div class="flex items-center gap-2 flex-shrink-0 mb-4 bg-white/5 p-3 rounded-2xl inline-block w-max">
                    <img src="<?= BASE_URL ?>/assets/img/logosidebar.png" alt="Logo" class="h-10 md:h-12 w-auto object-contain">
                </div>
                <p class="mb-6 text-sm leading-relaxed max-w-sm">A plataforma definitiva para deliverys e restaurantes que buscam independência, tecnologia de ponta e mais lucro no final do mês.</p>
                
                <div class="flex gap-4">
                    <a href="https://instagram.com/erwisedev" target="_blank" title="Siga no Instagram" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-300 hover:bg-gradient-primary hover:text-white transition shadow-sm"><i class="fab fa-instagram"></i></a>
                    <a href="https://wa.me/5511934008521" target="_blank" title="Fale no WhatsApp" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-300 hover:bg-[#25D366] hover:text-white transition shadow-sm"><i class="fab fa-whatsapp text-lg"></i></a>
                    <a href="https://erwise.com.br/" target="_blank" title="Visite nosso Site" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-300 hover:bg-blue-600 hover:text-white transition shadow-sm"><i class="fas fa-globe"></i></a>
                </div>
            </div>
            
            <div>
                <h4 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Sistema</h4>
                <ul class="space-y-2 text-sm font-medium">
                    <li><a href="#funcionalidades" class="hover:text-blue-400 transition">Cardápio Digital</a></li>
                    <li><a href="#funcionalidades" class="hover:text-blue-400 transition">Monitor KDS</a></li>
                    <li><a href="#funcionalidades" class="hover:text-blue-400 transition">Gestão de Motoboys</a></li>
                    <li><a href="#planos" class="hover:text-blue-400 transition">Planos e Preços</a></li>
                </ul>
            </div>
            
            <div>
                <h4 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Contato</h4>
                <ul class="space-y-2 text-sm font-medium">
                    <li><a href="https://wa.me/5511934008521" target="_blank" class="hover:text-blue-400 transition">Suporte via WhatsApp</a></li>
                    <li><a href="https://erwise.com.br/" target="_blank" class="hover:text-blue-400 transition">Conheça a Erwise</a></li>
                    <li><a href="#" class="hover:text-blue-400 transition">Termos de Uso</a></li>
                    <li><a href="#" class="hover:text-blue-400 transition">Privacidade</a></li>
                </ul>
            </div>
        </div>
        
        <div class="max-w-7xl mx-auto px-4 text-center border-t border-slate-800 pt-8 flex flex-col md:flex-row items-center justify-between gap-4 text-xs font-medium">
            <p>© <?= date('Y') ?> clicoupediu.app.br. Todos os direitos reservados.</p>
            
            <p class="flex items-center gap-1">Desenvolvido por <a href="https://erwise.com.br/" target="_blank" class="text-blue-400 font-bold hover:underline transition">Erwise Dev</a>.</p>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            once: true,
            offset: 100,
            duration: 800,
            easing: 'ease-in-out-quad'
        });

        // Navbar efeito scroll
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('bg-white/95', 'shadow-sm', 'py-2');
                navbar.classList.remove('bg-white/90', 'py-3');
            } else {
                navbar.classList.remove('bg-white/95', 'shadow-sm', 'py-2');
                navbar.classList.add('bg-white/90', 'py-3');
            }
        });
    </script>

</body>
</html>