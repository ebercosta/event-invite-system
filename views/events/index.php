<?php
ob_start();
?>
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Eventos</h1>
        <p class="text-slate-600">Gerencie todos os seus eventos</p>
    </div>
    <a href="/events/create" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2.5 rounded-lg">+ Novo Evento</a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <?php if (empty($events)): ?>
    <div class="p-12 text-center text-slate-500">
        <p class="mb-4">Nenhum evento cadastrado.</p>
        <a href="/events/create" class="text-blue-600 hover:underline">Criar primeiro evento</a>
    </div>
    <?php else: ?>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="text-left px-6 py-3 font-medium">Título</th>
                <th class="text-left px-6 py-3 font-medium">Data / Hora</th>
                <th class="text-left px-6 py-3 font-medium">Local</th>
                <th class="text-left px-6 py-3 font-medium">Convidados</th>
                <th class="text-left px-6 py-3 font-medium">Status</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php foreach ($events as $e): ?>
            <tr class="hover:bg-slate-50">
                <td class="px-6 py-4 font-medium text-slate-900"><?= htmlspecialchars($e['title']) ?></td>
                <td class="px-6 py-4 text-slate-600">
                    <?= date('d/m/Y', strtotime($e['event_date'])) ?>
                    <span class="text-slate-400">às</span>
                    <?= date('H:i', strtotime($e['event_time'])) ?>
                </td>
                <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($e['location_name']) ?></td>
                <td class="px-6 py-4">
                    <span class="text-slate-900"><?= $e['guests_count'] ?></span>
                    <span class="text-green-600 text-xs ml-1">(<?= $e['confirmed_count'] ?> conf.)</span>
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                        <?= $e['status'] === 'published' ? 'bg-green-100 text-green-700' : ($e['status'] === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600') ?>">
                        <?= $e['status'] ?>
                    </span>
                </td>
                <td class="px-6 py-4 text-right space-x-3">
                    <a href="/events/<?= $e['id'] ?>" class="text-blue-600 hover:underline">Ver</a>
                    <a href="/events/<?= $e['id'] ?>/guests" class="text-slate-600 hover:underline">Convidados</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$title = 'Eventos';
include __DIR__ . '/../layouts/main.php';
