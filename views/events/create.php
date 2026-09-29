<?php
ob_start();
?>
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Novo Evento</h1>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<form method="POST" action="/events" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8 space-y-6 max-w-2xl">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Título *</label>
        <input type="text" name="title" required class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Descrição</label>
        <textarea name="description" rows="3" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Data *</label>
            <input type="date" name="event_date" required class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Horário *</label>
            <input type="time" name="event_time" required class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Nome do Local *</label>
        <input type="text" name="location_name" required class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Endereço</label>
        <input type="text" name="location_address" id="location_address" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Digite o endereço ou clique no mapa">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">Localização no Mapa</label>
        <p class="text-xs text-slate-500 mb-2">Clique no mapa para marcar o local do evento</p>
        <div id="map" class="w-full h-64 rounded-lg border border-slate-300 z-0"></div>
        <div class="grid grid-cols-2 gap-4 mt-3">
            <div>
                <label class="block text-xs text-slate-500 mb-1">Latitude</label>
                <input type="text" name="latitude" id="latitude" readonly class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-slate-50">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Longitude</label>
                <input type="text" name="longitude" id="longitude" readonly class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-slate-50">
            </div>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Mensagem Personalizada do Convite</label>
        <textarea name="custom_message" rows="3" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Texto que aparecerá no convite..."></textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Imagem do Evento</label>
        <input type="file" name="image" accept="image/*" class="w-full text-sm text-slate-600">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
        <select name="status" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="draft">Rascunho</option>
            <option value="published">Publicado</option>
        </select>
    </div>

    <div class="flex gap-3 pt-2">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-lg">Salvar Evento</button>
        <a href="/events" class="bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium px-6 py-2.5 rounded-lg">Cancelar</a>
    </div>
</form>

<script>
    const map = L.map('map').setView([-7.1195, -34.8450], 13);

    // CartoDB Positron — gratuito, conforme política de uso (não usa tile.openstreetmap.org)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);

    let marker = null;

    map.on('click', function(e) {
        const lat = e.latlng.lat.toFixed(8);
        const lng = e.latlng.lng.toFixed(8);

        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;

        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng, { draggable: true }).addTo(map);
            marker.on('dragend', function(event) {
                const pos = event.target.getLatLng();
                document.getElementById('latitude').value = pos.lat.toFixed(8);
                document.getElementById('longitude').value = pos.lng.toFixed(8);
            });
        }
    });
</script>

<?php
$content = ob_get_clean();
$title = 'Novo Evento';
include __DIR__ . '/../layouts/main.php';
