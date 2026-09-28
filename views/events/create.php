<?php
ob_start();
?>
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Novo Evento</h1>
</div>

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
        <input type="text" name="location_address" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Latitude (opcional)</label>
            <input type="text" name="latitude" placeholder="-7.1195" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Longitude (opcional)</label>
            <input type="text" name="longitude" placeholder="-34.8450" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
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
<?php
$content = ob_get_clean();
$title = 'Novo Evento';
include __DIR__ . '/../layouts/main.php';
