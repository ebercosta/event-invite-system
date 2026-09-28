<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Event Invite</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">
        <h1 class="text-2xl font-bold text-slate-900 mb-6 text-center">Entrar</h1>

        <?php if (!empty($error)): ?>
        <div class="bg-red-50 text-red-700 p-3 rounded-lg mb-4 text-sm"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/login" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">E-mail</label>
                <input type="email" name="email" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Senha</label>
                <input type="password" name="password" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition">
                Entrar
            </button>
        </form>

        <p class="text-center text-sm text-slate-600 mt-6">
            Não tem conta? <a href="/register" class="text-blue-600 hover:underline">Cadastre-se</a>
        </p>
    </div>
</body>
</html>
