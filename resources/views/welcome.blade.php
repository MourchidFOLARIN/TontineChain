<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>TontineChain - L'Excellence Financière Décentralisée</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">

        <!-- Tailwind CSS (Via Vite) -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --emerald-dark: #004d2c;
                --emerald-primary: #10b981;
                --gold-primary: #d4af37;
                --gold-light: #f8b803;
                --dark-bg: #050505;
            }

            body {
                font-family: 'Inter', sans-serif;
                background-color: var(--dark-bg);
                color: #e5e7eb;
                overflow-x: hidden;
            }

            h1, h2, .font-display {
                font-family: 'Playfair Display', serif;
            }

            .glass-card {
                background: rgba(255, 255, 255, 0.03);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.1);
                transition: all 0.3s ease;
            }

            .glass-card:hover {
                border-color: var(--emerald-primary);
                background: rgba(16, 185, 129, 0.05);
                transform: translateY(-5px);
            }

            .emerald-gradient-bg {
                background: radial-gradient(circle at bottom center, var(--emerald-dark) 0%, transparent 70%);
            }

            .gold-text-gradient {
                background: linear-gradient(to right, #d4af37, #f8b803, #c19a6b);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            .btn-premium {
                background: linear-gradient(45deg, #d4af37, #f8b803);
                color: #000;
                font-weight: 700;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
            }

            .btn-premium:hover {
                box-shadow: 0 6px 25px rgba(212, 175, 55, 0.5);
                transform: scale(1.05);
            }

            .logo-glow {
                filter: drop-shadow(0 0 20px rgba(16, 185, 129, 0.3));
            }

            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .animate-fade-in {
                animation: fadeIn 1s ease forwards;
            }
        </style>
    </head>
    <body class="antialiased selection:bg-emerald-500 selection:text-white">
        
        <!-- Background Accents -->
        <div class="fixed inset-0 z-[-1] overflow-hidden pointer-events-none">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-emerald-900/20 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 -right-24 w-80 h-80 bg-emerald-800/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 right-0 h-[50vh] emerald-gradient-bg"></div>
        </div>

        <!-- Navigation -->
        <nav class="relative z-10 flex items-center justify-between px-8 py-6 max-w-7xl mx-auto">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.jpeg') }}" alt="Logo" class="h-12 w-auto logo-glow">
                <span class="text-xl font-bold tracking-tighter gold-text-gradient hidden md:block">TONTINECHAIN</span>
            </div>
            
            <div class="flex items-center gap-6">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm font-medium hover:text-emerald-400 transition-colors">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium hover:text-emerald-400 transition-colors">Se connecter</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn-premium px-6 py-2 rounded-full text-sm">Rejoindre l'élite</a>
                        @endif
                    @endauth
                @endif
            </div>
        </nav>

        <!-- Hero Section -->
        <header class="relative z-10 pt-20 pb-32 px-6 text-center max-w-5xl mx-auto">
            <div class="animate-fade-in">
                <span class="inline-block px-4 py-1.5 mb-6 text-xs font-bold tracking-widest uppercase border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 rounded-full">
                    Hackathon MIABE 2026 - Winner Edition
                </span>
                <h1 class="text-5xl md:text-7xl lg:text-8xl mb-8 leading-tight font-display tracking-tight">
                    L'Art de la Tontine <br>
                    <span class="gold-text-gradient">Redéfini par la Blockchain</span>
                </h1>
                <p class="text-lg md:text-xl text-gray-400 mb-12 max-w-2xl mx-auto leading-relaxed">
                    Sécurité absolue, transparence immuable et inclusion totale. TontineChain fusionne la tradition africaine avec la technologie de pointe Polygon.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
                    <a href="{{ route('register') }}" class="btn-premium px-10 py-4 rounded-full text-lg w-full sm:w-auto">
                        Commencer maintenant
                    </a>
                    <a href="#features" class="px-8 py-3 text-sm font-semibold border border-white/10 rounded-full hover:bg-white/5 transition-all">
                        Découvrir la vision
                    </a>
                </div>
            </div>
        </header>

        <!-- Stats Section -->
        <section class="relative z-10 py-20 px-6 bg-black/40 border-y border-white/5">
            <div class="max-w-7xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-3xl font-bold gold-text-gradient mb-2">0%</div>
                    <div class="text-xs uppercase tracking-widest text-gray-500">Risque de fraude</div>
                </div>
                <div>
                    <div class="text-3xl font-bold gold-text-gradient mb-2">100%</div>
                    <div class="text-xs uppercase tracking-widest text-gray-500">Audit Blockchain</div>
                </div>
                <div>
                    <div class="text-3xl font-bold gold-text-gradient mb-2">24/7</div>
                    <div class="text-xs uppercase tracking-widest text-gray-500">Accès Audio Guide</div>
                </div>
                <div>
                    <div class="text-3xl font-bold gold-text-gradient mb-2">Polygon</div>
                    <div class="text-xs uppercase tracking-widest text-gray-500">Infrastructure</div>
                </div>
            </div>
        </section>

        <!-- Features Grid -->
        <section id="features" class="relative z-10 py-32 px-6 max-w-7xl mx-auto">
            <div class="text-center mb-20">
                <h2 class="text-4xl md:text-5xl mb-6">Un Écosystème de Prestige</h2>
                <div class="w-24 h-1 bg-emerald-500 mx-auto rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Blockchain Card -->
                <div class="glass-card p-10 rounded-3xl group">
                    <div class="w-16 h-16 bg-emerald-500/20 rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Immuabilité Blockchain</h3>
                    <p class="text-gray-400 leading-relaxed">Chaque versement est ancré sur le réseau Polygon, garantissant une preuve de paiement infalsifiable et éternelle.</p>
                </div>

                <!-- IA Risk Card -->
                <div class="glass-card p-10 rounded-3xl group border-emerald-500/20">
                    <div class="w-16 h-16 bg-gold-primary/20 rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-gold-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.674a1 1 0 001.923-.641C16.033 15.664 17 14.28 17 12a5 5 0 00-10 0c0 2.28.967 3.664 1.74 4.359a1 1 0 001.923.641zM12 9v4m0 0h.01m-4.699 2h9.398"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Intelligence Prédictive</h3>
                    <p class="text-gray-400 leading-relaxed">Notre IA analyse les comportements pour prévenir les défauts de paiement avant même qu'ils ne surviennent.</p>
                </div>

                <!-- Inclusion Card -->
                <div class="glass-card p-10 rounded-3xl group">
                    <div class="w-16 h-16 bg-emerald-500/20 rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Inclusion Linguistique</h3>
                    <p class="text-gray-400 leading-relaxed">Accessible à tous via notre guide audio multilingue (Fon, Yoruba, Français), car la solidarité ne connaît pas de frontières.</p>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="relative z-10 py-12 px-8 border-t border-white/5 text-center">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="Logo" class="h-8 w-auto">
                    <span class="font-bold gold-text-gradient">TontineChain</span>
                </div>
                <div class="text-gray-500 text-sm">
                    © 2026 TontineChain. Conçu avec excellence pour le MIABE Hackathon.
                </div>
                <div class="flex gap-6">
                    <a href="#" class="text-gray-400 hover:text-emerald-400">Twitter</a>
                    <a href="#" class="text-gray-400 hover:text-emerald-400">GitHub</a>
                </div>
            </div>
        </footer>

    </body>
</html>
