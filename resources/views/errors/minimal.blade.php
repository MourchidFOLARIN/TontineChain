<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - TontineChain</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Playfair+Display:ital,wght@1,700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #050505; color: #e5e7eb; font-family: 'Inter', sans-serif; }
        .gold-gradient { background: linear-gradient(to right, #d4af37, #f8b803); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .glass-card { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.08); }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-6 overflow-hidden">
    <!-- background ornaments -->
    <div class="fixed inset-0 z-[-1]">
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-emerald-900/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-gold-900/5 rounded-full blur-[100px]"></div>
    </div>

    <div class="max-w-md w-full glass-card p-12 rounded-[40px] text-center shadow-2xl relative">
        <div class="mb-8">
            <h1 class="text-9xl font-bold text-white/5 absolute left-1/2 -translate-x-1/2 -translate-y-4 pointer-events-none">@yield('code')</h1>
            <div class="relative z-10 text-6xl mb-6">
                @yield('icon', '⚠️')
            </div>
        </div>
        
        <h2 class="text-3xl font-bold mb-4 italic gold-gradient" style="font-family: 'Playfair Display', serif;">@yield('title')</h2>
        <p class="text-gray-400 mb-10 leading-relaxed">
            @yield('message')
        </p>

        <a href="/" class="inline-block bg-emerald-600 hover:bg-emerald-500 text-white px-8 py-3 rounded-full font-bold transition-all shadow-lg shadow-emerald-900/20">
            Retourner à l'accueil
        </a>

        <div class="mt-12 pt-8 border-t border-white/5">
            <p class="text-[10px] text-gray-600 uppercase tracking-widest">TontineChain • Excellence Numérique</p>
        </div>
    </div>
</body>
</html>
