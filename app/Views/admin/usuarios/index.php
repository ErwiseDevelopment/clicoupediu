<?php $titulo = "Equipa e Acessos"; require __DIR__ . '/../../partials/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../../partials/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Utilizadores do Sistema</h1>
                <p class="text-sm text-gray-500">Controle quem pode aceder e operar a plataforma.</p>
            </div>
            <button onclick="novoUtilizador()" class="bg-blue-600 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg hover:bg-blue-700 transition flex items-center gap-2">
                <i class="fas fa-plus"></i> Novo Acesso
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 sticky top-6">
                    <h3 id="form_title" class="font-bold text-gray-800 mb-4">Novo Utilizador</h3>
                    <form action="<?= BASE_URL ?>/admin/usuarios/salvar" method="POST" class="space-y-4">
                        <input type="hidden" name="id" id="user_id">
                        
                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase">Nome Completo</label>
                            <input type="text" name="nome" id="user_nome" required class="w-full border rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>

                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase">E-mail (Login)</label>
                            <input type="email" name="email" id="user_email" required class="w-full border rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-gray-400 uppercase">Nível</label>
                                <select name="nivel" id="user_nivel" class="w-full border rounded-xl p-3 text-sm bg-white">
                                    <option value="caixa">Caixa</option>
                                    <option value="gerente">Gerente</option>
                                    <option value="cozinha">Cozinha</option>
                                    <option value="dono">Dono</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-gray-400 uppercase">Password</label>
                                <input type="password" name="senha" id="user_senha" class="w-full border rounded-xl p-3 text-sm" placeholder="******">
                            </div>
                        </div>

                        <div class="pt-4 flex gap-2">
                            <button type="submit" class="flex-1 bg-gray-900 text-white py-3 rounded-xl font-bold hover:bg-black transition">Gravar</button>
                            <button type="button" onclick="novoUtilizador()" id="btn_cancelar" class="hidden px-4 bg-gray-100 text-gray-500 rounded-xl hover:bg-gray-200"><i class="fas fa-times"></i></button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Utilizador</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Nível</th>
                                <th class="px-6 py-4 text-right"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach($usuarios as $u): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-800"><?= $u['nome'] ?></div>
                                    <div class="text-xs text-gray-500"><?= $u['email'] ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-md text-[10px] font-black uppercase <?= $u['nivel'] == 'dono' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' ?>">
                                        <?= $u['nivel'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <button onclick='editar(<?= json_encode($u) ?>)' class="text-blue-600 hover:text-blue-900"><i class="fas fa-edit"></i></button>
                                    <a href="<?= BASE_URL ?>/admin/usuarios/excluir?id=<?= $u['id'] ?>" onclick="return confirm('Apagar este acesso?')" class="text-red-400 hover:text-red-600"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
function editar(u) {
    document.getElementById('user_id').value = u.id;
    document.getElementById('user_nome').value = u.nome;
    document.getElementById('user_email').value = u.email;
    document.getElementById('user_nivel').value = u.nivel;
    document.getElementById('user_senha').placeholder = "Deixe vazio para manter";
    document.getElementById('form_title').innerText = "Editar Utilizador";
    document.getElementById('btn_cancelar').classList.remove('hidden');
}

function novoUtilizador() {
    document.getElementById('user_id').value = "";
    document.getElementById('user_nome').value = "";
    document.getElementById('user_email').value = "";
    document.getElementById('user_senha').placeholder = "******";
    document.getElementById('form_title').innerText = "Novo Utilizador";
    document.getElementById('btn_cancelar').classList.add('hidden');
}
</script>