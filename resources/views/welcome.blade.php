<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>TontineChain - Sécurisez votre épargne avec la Blockchain</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">

        <!-- Tailwind CSS -->
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
                scroll-behavior: smooth;
            }

            h1, h2, h3, .font-display {
                font-family: 'Playfair Display', serif;
            }

            .glass-card {
                background: rgba(255, 255, 255, 0.02);
                backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.08);
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .glass-card:hover {
                border-color: var(--emerald-primary);
                background: rgba(16, 185, 129, 0.05);
                transform: translateY(-10px);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            }

            .gold-gradient {
                background: linear-gradient(to right, #d4af37, #f8b803, #c19a6b);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            .emerald-glow {
                box-shadow: 0 0 50px rgba(16, 185, 129, 0.15);
            }

            .step-number {
                background: linear-gradient(45deg, var(--gold-primary), var(--gold-light));
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                font-family: 'Playfair Display', serif;
            }

            @keyframes slideUp {
                from { opacity: 0; transform: translateY(30px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .reveal { animation: slideUp 0.8s ease-out forwards; }
        </style>
    </head>
    <body class="antialiased selection:bg-emerald-500 selection:text-white">
        
        <!-- Background Accents -->
        <div class="fixed inset-0 z-[-1] overflow-hidden pointer-events-none">
            <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-emerald-900/10 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-gold-900/5 rounded-full blur-[100px]"></div>
        </div>

        <!-- Navigation -->
        <nav class="sticky top-0 z-50 backdrop-blur-md bg-black/50 border-b border-white/5">
            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="Logo" class="h-10 w-auto rounded-full border border-gold-primary/30">
                    <span class="text-xl font-bold tracking-tighter gold-gradient">TONTINECHAIN</span>
                </div>
                <div class="hidden md:flex items-center gap-8">
                    <a href="#solution" class="text-sm text-gray-400 hover:text-white transition-colors">La Solution</a>
                    <a href="#trust" class="text-sm text-gray-400 hover:text-white transition-colors">Pourquoi nous ?</a>
                    <a href="#how-it-works" class="text-sm text-gray-400 hover:text-white transition-colors">Fonctionnement</a>
                </div>
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm font-medium hover:text-emerald-400 transition-colors">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium hover:text-white">Connexion</a>
                        <a href="{{ route('register') }}" class="bg-gradient-to-r from-gold-primary to-gold-light text-black px-6 py-2 rounded-full text-sm font-bold hover:scale-105 transition-transform">S'inscrire</a>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative pt-24 pb-20 px-6 text-center max-w-6xl mx-auto">
            <div class="reveal">
                <h1 class="text-6xl md:text-8xl mb-8 leading-tight tracking-tight">
                    L'Épargne Collective <br>
                    <span class="gold-gradient italic">Sans Compromis</span>
                </h1>
                <p class="text-xl text-gray-400 mb-12 max-w-3xl mx-auto leading-relaxed">
                    Marre des tontines basées sur la simple parole ? Passez à la vitesse supérieure avec une gestion automatisée, sécurisée par la blockchain et pilotée par l'intelligence artificielle.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
                    <a href="{{ route('register') }}" class="bg-emerald-600 hover:bg-emerald-500 text-white px-10 py-4 rounded-full text-lg font-bold transition-all shadow-lg emerald-glow">
                        Créer ma première tontine
                    </a>
                </div>
            </div>
        </section>

        <!-- The Problem & Solution -->
        <section id="solution" class="py-24 px-6 max-w-7xl mx-auto border-t border-white/5">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">
                <div class="reveal">
                    <h2 class="text-4xl md:text-5xl mb-8 leading-tight">
                        Pourquoi <span class="gold-gradient">TontineChain</span> ?
                    </h2>
                    <p class="text-gray-400 text-lg mb-8 leading-relaxed">
                        En Afrique, la tontine est sacrée. Mais elle est fragile : membres indélicats, gestion opaque du trésorier, fonds volés... 
                    </p>
                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="mt-1 w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-white mb-1">Confiance Algorithmique</h4>
                                <p class="text-gray-500 text-sm">Le trésorier ne peut pas disparaître avec la caisse. Le système distribue les fonds automatiquement via contrat intelligent.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="mt-1 w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-white mb-1">Inclusion Totale</h4>
                                <p class="text-gray-500 text-sm">Pas besoin de savoir lire. Notre guide audio en langues locales accompagne chaque étape de votre épargne.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="glass-card p-4 rounded-[40px] rotate-3 reveal" style="animation-delay: 0.2s">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="Dashboard Preview" class="rounded-[30px] w-full shadow-2xl border border-white/10">
                </div>
            </div>
        </section>

        <!-- Trust Section -->
        <section id="trust" class="py-24 px-6 bg-emerald-950/20 border-y border-white/5">
            <div class="max-w-7xl mx-auto text-center">
                <h2 class="text-4xl md:text-5xl mb-16">Pourquoi nous faire <span class="gold-gradient">Confiance</span> ?</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-left">
                    <div class="glass-card p-10 rounded-3xl">
                        <div class="text-gold-primary text-4xl mb-6 font-display italic">01.</div>
                        <h3 class="text-2xl font-bold mb-4">Polygon Blockchain</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">
                            Nous utilisons la technologie Polygon pour "ancrer" chaque paiement. Une fois payé, c'est gravé dans le marbre numérique. Personne, même pas nous, ne peut modifier vos transactions.
                        </p>
                    </div>
                    <div class="glass-card p-10 rounded-3xl border-emerald-500/20">
                        <div class="text-gold-primary text-4xl mb-6 font-display italic">02.</div>
                        <h3 class="text-2xl font-bold mb-4">Vérification NPI Stricte</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">
                            Pas de faux comptes. Chaque membre est authentifié par son NPI (Numéro d'Identification Personnelle). Nous créons un environnement où tout le monde est réel et responsable.
                        </p>
                    </div>
                    <div class="glass-card p-10 rounded-3xl">
                        <div class="text-gold-primary text-4xl mb-6 font-display italic">03.</div>
                        <h3 class="text-2xl font-bold mb-4">IA d'Analyse de Risque</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">
                            Notre algorithme analyse le comportement de paiement pour vous dire si un groupe est sûr ou non. Nous protégeons votre argent avant même que vous ne le misiez.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section id="how-it-works" class="py-32 px-6 max-w-7xl mx-auto">
            <div class="text-center mb-24 reveal">
                <h2 class="text-5xl mb-6 font-display">Épargnez en 4 Étapes</h2>
                <p class="text-gray-500 text-lg">Un processus fluide, rapide et totalement sécurisé.</p>
            </div>

            <div class="relative grid grid-cols-1 md:grid-cols-4 gap-12 text-center">
                <!-- Connecteur visuel -->
                <div class="hidden md:block absolute top-12 left-0 right-0 h-0.5 bg-gradient-to-r from-emerald-900/0 via-gold-900/30 to-emerald-900/0 -z-10"></div>

                <div class="reveal" style="animation-delay: 0.1s">
                    <div class="step-number text-5xl font-bold mb-6">1</div>
                    <h4 class="text-xl font-bold mb-3">Rejoignez</h4>
                    <p class="text-gray-500 text-sm">Créez votre compte avec votre numéro de téléphone et vérifiez votre NPI.</p>
                </div>
                <div class="reveal" style="animation-delay: 0.2s">
                    <div class="step-number text-5xl font-bold mb-6">2</div>
                    <h4 class="text-xl font-bold mb-3">Cotisez</h4>
                    <p class="text-gray-500 text-sm">Payez vos cotisations mensuelles via Mobile Money (MTN, Moov).</p>
                </div>
                <div class="reveal" style="animation-delay: 0.3s">
                    <div class="step-number text-5xl font-bold mb-6">3</div>
                    <h4 class="text-xl font-bold mb-3">Ancrez</h4>
                    <p class="text-gray-500 text-sm">Votre paiement est validé sur la Blockchain Polygon en temps réel.</p>
                </div>
                <div class="reveal" style="animation-delay: 0.4s">
                    <div class="step-number text-5xl font-bold mb-6">4</div>
                    <h4 class="text-xl font-bold mb-3">Récoltez</h4>
                    <p class="text-gray-500 text-sm">Recevez la cagnotte directement sur votre compte Mobile Money à votre tour.</p>
                </div>
            </div>
        </section>

        <!-- Final CTA -->
        <section class="py-24 px-6">
            <div class="max-w-5xl mx-auto glass-card p-16 rounded-[60px] text-center border-emerald-500/20 emerald-glow reveal">
                <h2 class="text-4xl md:text-6xl mb-8 font-display">Prêt à entrer dans l'Excellence ?</h2>
                <p class="text-gray-400 text-lg mb-12 max-w-2xl mx-auto">Rejoignez des milliers de citoyens qui font confiance à la technologie pour bâtir leur avenir financier.</p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
                    <a href="{{ route('register') }}" class="bg-gradient-to-r from-gold-primary to-gold-light text-black px-12 py-5 rounded-full text-xl font-bold hover:scale-110 transition-transform">
                        Ouvrir mon compte gratuit
                    </a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-12 px-8 border-t border-white/5 text-center">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="Logo" class="h-8 w-auto rounded-full">
                    <span class="font-bold gold-gradient">TontineChain</span>
                </div>
                <div class="text-gray-600 text-sm">
                    © 2026 TontineChain. Sécurité, Inclusivité, Blockchain.
                </div>
                <div class="flex gap-8 text-gray-500 text-sm">
                    <a href="#" class="hover:text-gold-primary">Légalité</a>
                    <a href="#" class="hover:text-gold-primary">Confidentialité</a>
                    <a href="#" class="hover:text-gold-primary">Contact</a>
                </div>
            </div>
        </footer>

    </body>
</html>
