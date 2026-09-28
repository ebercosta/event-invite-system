<?php
ob_start();
?>
<div class="mb-6">
    <a href="/events/<?= $event['id'] ?>" class="text-sm text-blue-600 hover:underline">← Voltar para o evento</a>
</div>

<div class="flex flex-wrap justify-between items-center gap-4 mb-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Convidados</h1>
        <p class="text-slate-600"><?= htmlspecialchars($event['title']) ?> · <?= count($guests) ?> convidados</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <button onclick="sendAllInvites()" class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            Enviar Convites
        </button>
        <a href="/exports/event/<?= $event['id'] ?>/attendance" class="bg-slate-200 hover:bg-slate-300 text-slate-800 text-sm font-medium px-4 py-2 rounded-lg">Exportar</a>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="bg-green-50 text-green-700 p-3 rounded-lg mb-4 text-sm">Convidado adicionado com sucesso!</div>
<?php endif; ?>
<?php if (isset($_GET['import'])): ?>
<div class="bg-blue-50 text-blue-700 p-3 rounded-lg mb-4 text-sm"><?= htmlspecialchars($_GET['import']) ?></div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
<div class="bg-red-50 text-red-700 p-3 rounded-lg mb-4 text-sm">Erro: <?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Adicionar convidado -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h2 class="font-semibold text-slate-900 mb-4">Adicionar Convidado</h2>
        <form method="POST" action="/events/<?= $event['id'] ?>/guests" class="space-y-3">
            <input type="text" name="name" placeholder="Nome" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="email" name="email" placeholder="E-mail" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="text" name="phone" placeholder="Telefone (WhatsApp)" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 rounded-lg">Adicionar</button>
        </form>
    </div>

    <!-- Importar CSV -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 lg:col-span-2">
        <h2 class="font-semibold text-slate-900 mb-4">Importar CSV</h2>
        <p class="text-sm text-slate-500 mb-3">Colunas aceitas: <code>nome,email,telefone</code> (separadas por vírgula ou ponto-e-vírgula)</p>
        <form method="POST" action="/events/<?= $event['id'] ?>/guests/import" enctype="multipart/form-data" class="flex flex-wrap gap-3 items-end">
            <input type="file" name="csv" accept=".csv,.txt" required class="text-sm">
            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium px-4 py-2 rounded-lg">Importar</button>
        </form>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <?php if (empty($guests)): ?>
    <div class="p-8 text-center text-slate-500">Nenhum convidado cadastrado.</div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="text-left px-4 py-3 font-medium">Nome</th>
                    <th class="text-left px-4 py-3 font-medium">E-mail</th>
                    <th class="text-left px-4 py-3 font-medium">Telefone</th>
                    <th class="text-left px-4 py-3 font-medium">Status</th>
                    <th class="text-left px-4 py-3 font-medium">Envios</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($guests as $g): ?>
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-medium text-slate-900"><?= htmlspecialchars($g['name']) ?></td>
                    <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($g['email']) ?></td>
                    <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($g['phone'] ?? '-') ?></td>
                    <td class="px-4 py-3">
                        <?php
                        $statusClass = match($g['status']) {
                            'confirmed' => 'bg-green-100 text-green-700',
                            'declined'  => 'bg-red-100 text-red-700',
                            default     => 'bg-amber-100 text-amber-700',
                        };
                        ?>
                        <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full <?= $statusClass ?>"><?= $g['status'] ?></span>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">
                        <?php if ($g['email_sent_at']): ?>✉ <?php endif; ?>
                        <?php if ($g['whatsapp_sent_at']): ?>📱 <?php endif; ?>
                        <?php if (!$g['email_sent_at'] && !$g['whatsapp_sent_at']): ?>-<?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="/convite/<?= $g['unique_token'] ?>" target="_blank" class="text-blue-600 hover:underline text-xs">Link</a>
                        <button onclick="sendSingle(<?= $g['id'] ?>)" class="text-green-600 hover:underline text-xs">Enviar</button>
                        <form action="/guests/<?= $g['id'] ?>/delete" method="POST" class="inline" onsubmit="return confirm('Remover convidado?')">
                            <button type="submit" class="text-red-600 hover:underline text-xs">Excluir</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
async function sendAllInvites() {
    if (!confirm('Enfileirar convites para todos os pendentes?')) return;
    const res = await fetch('/events/<?= $event['id'] ?>/guests/send-invites', { method: 'POST' });
    const data = await res.json();
    alert(data.message);
}

async function sendSingle(id) {
    const res = await fetch('/guests/' + id + '/send-invite', { method: 'POST' });
    const data = await res.json();
    alert(data.message);
}
</script>
<?php
$content = ob_get_clean();
$title = 'Convidados - ' . $event['title'];
include __DIR__ . '/../layouts/main.php';
