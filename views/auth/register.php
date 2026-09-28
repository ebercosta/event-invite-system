<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Event Invite</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">
        <h1 class="text-2xl font-bold text-slate-900 mb-6 text-center">Criar Conta</h1>

        <?php if (!empty($errors)): ?>
        <div class="bg-red-50 text-red-700 p-3 rounded-lg mb-4 text-sm">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="/register" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nome</label>
                <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">E-mail</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Senha</label>
                <input type="password" name="password" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Confirmar Senha</label>
                <input type="password" name="password_confirm" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition">
                Cadastrar
            </button>
        </form>

        <p class="text-center text-sm text-slate-600 mt-6">
            Já tem conta? <a href="/login" class="text-blue-600 hover:underline">Entrar</a>
        </p>
    </div>
</body>
</html>
