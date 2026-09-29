<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event->title) ?> - Convite</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php if (!empty($event->latitude) && !empty($event->longitude)): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <?php endif; ?>
</head>
<body class="bg-slate-100 min-h-screen">
    <div class="max-w-lg mx-auto px-4 py-8">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            
            <?php if ($event->image_path): ?>
            <img src="<?= htmlspecialchars($event->image_path) ?>" 
                 alt="Evento" 
                 class="w-full h-56 object-cover">
            <?php endif; ?>

            <div class="p-6 sm:p-8">
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 mb-2">
                    <?= htmlspecialchars($event->title) ?>
                </h1>

                <p class="text-slate-600 mb-6">
                    Olá, <strong><?= htmlspecialchars($guest->name) ?></strong>!
                </p>

                <?php if ($event->custom_message): ?>
                <p class="text-slate-700 mb-6 leading-relaxed">
                    <?= nl2br(htmlspecialchars($event->custom_message)) ?>
                </p>
                <?php endif; ?>

                <div class="bg-slate-50 rounded-xl p-4 mb-6 space-y-2">
                    <div class="flex items-center gap-2 text-slate-700">
                        <span>📅</span>
                        <span><?= date('d/m/Y', strtotime($event->event_date)) ?></span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-700">
                        <span>🕐</span>
                        <span><?= date('H:i', strtotime($event->event_time)) ?></span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-700">
                        <span>📍</span>
                        <span><?= htmlspecialchars($event->location_name) ?></span>
                    </div>
                    <?php if ($event->location_address): ?>
                    <div class="text-sm text-slate-500 pl-7">
                        <?= htmlspecialchars($event->location_address) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($event->latitude) && !empty($event->longitude)): ?>
                <div id="map" class="w-full h-52 rounded-xl border border-slate-200 mb-6 z-0"></div>
                <?php endif; ?>

                <div class="text-center mb-8">
                    <p class="text-sm text-slate-500 mb-3">Escaneie o QR Code</p>
                    <img src="<?= htmlspecialchars($qrCodePath) ?>" 
                         alt="QR Code" 
                         class="mx-auto w-40 h-40 rounded-lg border border-slate-200">
                </div>

                <?php if ($guest->status === 'pending'): ?>
                <div class="flex flex-col sm:flex-row gap-3">
                    <button onclick="respond('confirm')" 
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition">
                        Confirmar Presença
                    </button>
                    <button onclick="respond('decline')" 
                            class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold py-3 px-6 rounded-xl transition">
                        Não Poderei Ir
                    </button>
                </div>
                <?php elseif ($guest->status === 'confirmed'): ?>
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 text-center font-medium">
                    ✓ Presença confirmada!
                </div>
                <?php else: ?>
                <div class="bg-slate-100 border border-slate-200 text-slate-600 rounded-xl p-4 text-center font-medium">
                    Resposta registrada. Sentiremos sua falta!
                </div>
                <?php endif; ?>
            </div>
        </div>

        <p class="text-center text-xs text-slate-400 mt-6">
            Este convite é pessoal e intransferível.
        </p>
    </div>

    <?php if (!empty($event->latitude) && !empty($event->longitude)): ?>
    <script>
        const map = L.map('map').setView([<?= $event->latitude ?>, <?= $event->longitude ?>], 16);

        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri &mdash; Source: Esri, USGS, NOAA',
            maxZoom: 19
        }).addTo(map);

        L.marker([<?= $event->latitude ?>, <?= $event->longitude ?>])
            .addTo(map)
            .bindPopup("<strong><?= htmlspecialchars(addslashes($event->location_name)) ?></strong>")
            .openPopup();
    </script>
    <?php endif; ?>

    <script>
        async function respond(action) {
            const token = '<?= htmlspecialchars($token) ?>';
            const res = await fetch(`/convite/${token}/${action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });
            const data = await res.json();
            
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Erro ao registrar resposta');
            }
        }
    </script>
</body>
</html>
