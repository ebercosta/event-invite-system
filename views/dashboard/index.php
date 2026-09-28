<?php
ob_start();
?>
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
    <p class="text-slate-600">Visão geral dos seus eventos</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <p class="text-sm text-slate-500">Total de Eventos</p>
        <p class="text-3xl font-bold text-slate-900 mt-1"><?= $totalEvents ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <p class="text-sm text-slate-500">Total de Convidados</p>
        <p class="text-3xl font-bold text-slate-900 mt-1"><?= $totalGuests ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <p class="text-sm text-slate-500">Presenças Confirmadas</p>
        <p class="text-3xl font-bold text-green-600 mt-1"><?= $confirmedGuests ?></p>
    </div>
</div>

<div class="flex justify-between items-center mb-4">
    <h2 class="text-lg font-semibold text-slate-900">Eventos Recentes</h2>
    <a href="/events/create" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">+ Novo Evento</a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <?php if (empty($recentEvents)): ?>
    <div class="p-8 text-center text-slate-500">Nenhum evento cadastrado ainda.</div>
    <?php else: ?>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="text-left px-6 py-3 font-medium">Evento</th>
                <th class="text-left px-6 py-3 font-medium">Data</th>
                <th class="text-left px-6 py-3 font-medium">Convidados</th>
                <th class="text-left px-6 py-3 font-medium">Confirmados</th>
                <th class="text-left px-6 py-3 font-medium">Status</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php foreach ($recentEvents as $e): ?>
            <tr class="hover:bg-slate-50">
                <td class="px-6 py-4 font-medium text-slate-900"><?= htmlspecialchars($e['title']) ?></td>
                <td class="px-6 py-4 text-slate-600"><?= date('d/m/Y', strtotime($e['event_date'])) ?></td>
                <td class="px-6 py-4 text-slate-600"><?= $e['guests_count'] ?></td>
                <td class="px-6 py-4 text-green-600 font-medium"><?= $e['confirmed_count'] ?></td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                        <?= $e['status'] === 'published' ? 'bg-green-100 text-green-700' : ($e['status'] === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600') ?>">
                        <?= $e['status'] ?>
                    </span>
                </td>
                <td class="px-6 py-4 text-right">
                    <a href="/events/<?= $e['id'] ?>" class="text-blue-600 hover:underline">Ver</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$title = 'Dashboard';
include __DIR__ . '/../layouts/main.php';
