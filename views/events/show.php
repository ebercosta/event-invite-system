<?php
ob_start();
$hasCoords = !empty($event['latitude']) && !empty($event['longitude']);
?>
<div class="mb-6">
    <a href="/events" class="text-sm text-blue-600 hover:underline">← Voltar para eventos</a>
</div>

<?php if ($hasCoords): ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-8">
    <?php if ($event['image_path']): ?>
    <img src="<?= htmlspecialchars($event['image_path']) ?>" alt="Evento" class="w-full h-48 object-cover">
    <?php endif; ?>

    <div class="p-6 sm:p-8">
        <div class="flex flex-wrap justify-between items-start gap-4 mb-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900"><?= htmlspecialchars($event['title']) ?></h1>
                <p class="text-slate-600 mt-1">
                    <?= date('d/m/Y', strtotime($event['event_date'])) ?> às <?= date('H:i', strtotime($event['event_time'])) ?>
                </p>
            </div>
            <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full
                <?= $event['status'] === 'published' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' ?>">
                <?= $event['status'] ?>
            </span>
        </div>

        <p class="text-slate-700 mb-2"><strong>Local:</strong> <?= htmlspecialchars($event['location_name']) ?></p>
        <?php if ($event['location_address']): ?>
        <p class="text-slate-600 text-sm mb-4"><?= htmlspecialchars($event['location_address']) ?></p>
        <?php endif; ?>

        <?php if ($hasCoords): ?>
        <div id="map" class="w-full h-64 rounded-lg border border-slate-200 mb-6 z-0"></div>
        <?php endif; ?>

        <?php if ($event['description']): ?>
        <p class="text-slate-700 mb-6"><?= nl2br(htmlspecialchars($event['description'])) ?></p>
        <?php endif; ?>

        <div class="flex flex-wrap gap-3">
            <a href="/events/<?= $event['id'] ?>/guests" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">Gerenciar Convidados</a>
            <a href="/events/<?= $event['id'] ?>/edit" class="bg-slate-200 hover:bg-slate-300 text-slate-800 text-sm font-medium px-4 py-2 rounded-lg">Editar</a>
            <a href="/exports/event/<?= $event['id'] ?>/attendance" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium px-4 py-2 rounded-lg">Exportar Presença</a>
            <a href="/exports/event/<?= $event['id'] ?>/clicks" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium px-4 py-2 rounded-lg">Exportar Cliques</a>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <h2 class="text-lg font-semibold text-slate-900 mb-4">Resumo de Convidados (<?= count($guests) ?>)</h2>
    <?php
    $confirmed = count(array_filter($guests, fn($g) => $g['status'] === 'confirmed'));
    $pending = count(array_filter($guests, fn($g) => $g['status'] === 'pending'));
    $declined = count(array_filter($guests, fn($g) => $g['status'] === 'declined'));
    ?>
    <div class="flex gap-6 text-sm">
        <div><span class="text-green-600 font-bold text-xl"><?= $confirmed ?></span> confirmados</div>
        <div><span class="text-amber-600 font-bold text-xl"><?= $pending ?></span> pendentes</div>
        <div><span class="text-red-600 font-bold text-xl"><?= $declined ?></span> recusaram</div>
    </div>
</div>

<?php if ($hasCoords): ?>
<script>
    const map = L.map('map').setView([<?= $event['latitude'] ?>, <?= $event['longitude'] ?>], 16);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);

    L.marker([<?= $event['latitude'] ?>, <?= $event['longitude'] ?>])
        .addTo(map)
        .bindPopup("<strong><?= htmlspecialchars(addslashes($event['location_name'])) ?></strong><?= $event['location_address'] ? '<br>' . htmlspecialchars(addslashes($event['location_address'])) : '' ?>")
        .openPopup();
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();
$title = $event['title'];
include __DIR__ . '/../layouts/main.php';
