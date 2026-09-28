<?php
use App\Helpers\Auth;
$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Event Invite' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen">
    <nav class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-8">
                    <a href="/dashboard" class="text-xl font-bold text-blue-600">EventInvite</a>
                    <div class="hidden sm:flex gap-4">
                        <a href="/dashboard" class="text-slate-600 hover:text-slate-900 text-sm font-medium">Dashboard</a>
                        <a href="/events" class="text-slate-600 hover:text-slate-900 text-sm font-medium">Eventos</a>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-slate-600"><?= htmlspecialchars($user['name'] ?? '') ?></span>
                    <form action="/logout" method="POST">
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800">Sair</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?= $content ?? '' ?>
    </main>
</body>
</html>
