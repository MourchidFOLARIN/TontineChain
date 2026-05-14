import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { 
  ShieldCheck, 
  Zap, 
  Bot, 
  MessageSquare, 
  Users, 
  ArrowRight, 
  ChevronDown, 
  Lock, 
  TrendingUp, 
  Smartphone,
  Globe,
  PieChart
} from 'lucide-react';
import logoOfficial from '../assets/logo_official.png';

const LandingPage = ({ onNavigateLogin }) => {
  const [openFaq, setOpenFaq] = useState(null);

  const faqs = [
    { q: "Comment TontineChain sécurise mon argent ?", a: "Chaque groupe possède son propre contrat intelligent (Smart Contract) sur la blockchain Polygon. L'argent est bloqué techniquement et ne peut être libéré que selon les règles du groupe. Aucun humain ne peut détourner les fonds." },
    { q: "Quels sont les frais d'utilisation ?", a: "TontineChain est totalement gratuit pour les utilisateurs. Nous prenons en charge les frais de transaction blockchain (Gasless) pour vous offrir une expérience fluide." },
    { q: "Est-ce compatible avec mon téléphone ?", a: "Oui ! TontineChain est une application mobile-first optimisée pour fonctionner parfaitement sur tous les navigateurs smartphones au Bénin." },
  ];

  return (
    <div className="bg-slate-50 font-inter">
      {/* NAVBAR */}
      <nav className="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-100">
        <div className="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <img src={logoOfficial} alt="Logo" className="w-10 h-10 rounded-full border-2 border-TontineChain-orange shadow-md" />
            <span className="font-black text-xl tracking-tight text-slate-800 uppercase">TontineChain</span>
          </div>
          <button 
            onClick={onNavigateLogin}
            className="text-sm font-bold text-TontineChain-orange hover:bg-orange-50 px-5 py-2.5 rounded-full transition-all"
          >
            Se connecter
          </button>
        </div>
      </nav>

      {/* HERO SECTION */}
      <section className="pt-32 pb-20 px-6 overflow-hidden">
        <div className="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center">
          <motion.div 
            initial={{ opacity: 0, x: -30 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.8 }}
          >
            <div className="inline-flex items-center gap-2 bg-orange-100 text-TontineChain-orange px-4 py-2 rounded-full text-xs font-black uppercase tracking-widest mb-6">
              <Zap size={14} /> Tontine 2.0 • Blockchain 🇧🇯
            </div>
            <h1 className="text-5xl lg:text-7xl font-black text-slate-900 leading-[1.1] mb-8">
              Épargnez ensemble, <span className="text-TontineChain-orange">en toute sécurité.</span>
            </h1>
            <p className="text-lg text-slate-500 mb-10 leading-relaxed max-w-xl">
              La première plateforme de tontine au Bénin sécurisée par la blockchain. Protégez votre épargne collective contre les détournements grâce à la technologie Polygon.
            </p>
            <div className="flex flex-col sm:flex-row gap-4">
              <button 
                onClick={onNavigateLogin}
                className="btn-TontineChain text-lg px-10 py-5"
              >
                Créer ma tontine <ArrowRight />
              </button>
              <div className="flex items-center gap-4 px-6">
                <div className="flex -space-x-3">
                  {[1,2,3].map(i => <div key={i} className="w-10 h-10 rounded-full border-2 border-white bg-slate-200" />)}
                </div>
                <p className="text-xs font-bold text-slate-400 uppercase tracking-tighter">Rejoignez +1200 membres</p>
              </div>
            </div>
          </motion.div>

          <motion.div 
            initial={{ opacity: 0, scale: 0.8 }}
            animate={{ opacity: 1, scale: 1 }}
            className="relative flex justify-center lg:justify-end"
          >
            {/* Mockup Smartphone Simulation */}
            <div className="w-[300px] h-[600px] bg-slate-900 rounded-[3rem] border-[8px] border-slate-800 shadow-2xl relative overflow-hidden">
               <div className="absolute top-0 w-full h-8 flex justify-center items-center">
                  <div className="w-20 h-4 bg-slate-800 rounded-full mt-2" />
               </div>
               <div className="p-4 pt-10 h-full bg-slate-50">
                  <div className="w-full h-32 bg-TontineChain-blue rounded-3xl mb-4 p-4">
                    <div className="w-10 h-1 bg-white/20 rounded-full mb-4" />
                    <div className="w-20 h-4 bg-TontineChain-orange rounded-full" />
                  </div>
                  <div className="space-y-4">
                    {[1,2,3].map(i => (
                      <div key={i} className="w-full h-16 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center px-4 gap-3">
                        <div className="w-8 h-8 bg-slate-100 rounded-lg" />
                        <div className="flex-1 space-y-2">
                           <div className="w-2/3 h-2 bg-slate-100 rounded-full" />
                           <div className="w-1/3 h-1 bg-slate-50 rounded-full" />
                        </div>
                      </div>
                    ))}
                  </div>
               </div>
            </div>
            {/* Floating Badges */}
            <div className="absolute top-20 left-0 bg-white p-4 rounded-2xl shadow-xl border border-slate-100 flex items-center gap-3 animate-bounce duration-[3000ms]">
               <div className="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-500">
                  <ShieldCheck />
               </div>
               <div className="text-left">
                  <p className="text-[10px] font-bold text-slate-400 uppercase">Sécurité</p>
                  <p className="text-xs font-black text-slate-800">100% On-Chain</p>
               </div>
            </div>
          </motion.div>
        </div>
      </section>

      {/* STATS SECTION */}
      <section className="bg-white py-20 px-6 border-y border-slate-100">
        <div className="max-w-7xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-12 text-center">
          {[
            { label: "Volume Sécurisé", val: "250M FCFA" },
            { label: "Groupes Actifs", val: "145+" },
            { label: "Confiance", val: "99.9%" },
            { label: "Vitesse", val: "< 2s" },
          ].map((s, i) => (
            <div key={i}>
              <h3 className="text-3xl font-black text-TontineChain-orange mb-2">{s.val}</h3>
              <p className="text-xs font-bold text-slate-400 uppercase tracking-widest">{s.label}</p>
            </div>
          ))}
        </div>
      </section>

      {/* FEATURES SECTION */}
      <section className="py-32 px-6">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-20">
            <h2 className="text-4xl font-black text-slate-900 mb-4">Pourquoi TontineChain ?</h2>
            <p className="text-slate-500 max-w-2xl mx-auto">Plus qu'une application, un écosystème de confiance pour votre argent.</p>
          </div>

          <div className="grid md:grid-cols-3 gap-8">
            {[
              { icon: <Lock />, title: "Blockchain Immuable", desc: "Vos fonds sont protégés par des Smart Contracts Polygon audités." },
              { icon: <Bot />, title: "IA YAO", desc: "Un assistant intelligent qui analyse les risques et conseille votre groupe." },
              { icon: <MessageSquare />, title: "Messagerie Sociale", desc: "Discutez et partagez vos reçus directement dans vos groupes." },
              { icon: <Smartphone />, title: "Mobile Money", desc: "Intégration native avec MTN MoMo, Moov Money et Celtiis." },
              { icon: <Globe />, title: "Zéro Frais", desc: "Nous payons les frais de transaction pour vous. Totalement Gasless." },
              { icon: <PieChart />, title: "Score Elite", desc: "Améliorez votre réputation financière et débloquez des avantages." },
            ].map((f, i) => (
              <div key={i} className="bg-white p-10 rounded-[32px] border border-slate-100 hover:border-TontineChain-orange/30 transition-all group shadow-sm hover:shadow-xl">
                <div className="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 group-hover:text-TontineChain-orange group-hover:bg-orange-50 transition-all mb-8">
                  {f.icon}
                </div>
                <h4 className="text-xl font-bold text-slate-800 mb-4">{f.title}</h4>
                <p className="text-sm text-slate-500 leading-relaxed">{f.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* FAQ SECTION */}
      <section className="py-32 px-6 bg-slate-900 text-white rounded-[4rem] mx-4 mb-20">
        <div className="max-w-3xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-4xl font-black mb-4">Questions Fréquentes</h2>
          </div>
          
          <div className="space-y-4">
            {faqs.map((faq, i) => (
              <div key={i} className="bg-white/5 border border-white/10 rounded-3xl overflow-hidden">
                <button 
                  onClick={() => setOpenFaq(openFaq === i ? null : i)}
                  className="w-full p-6 text-left flex justify-between items-center"
                >
                  <span className="font-bold">{faq.q}</span>
                  <ChevronDown className={`transition-transform ${openFaq === i ? 'rotate-180' : ''}`} />
                </button>
                {openFaq === i && (
                  <div className="px-6 pb-6 text-slate-400 text-sm leading-relaxed">
                    {faq.a}
                  </div>
                )}
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* FINAL CTA */}
      <section className="py-20 text-center px-6">
        <h2 className="text-4xl font-black text-slate-900 mb-8">Prêt à moderniser vos tontines ?</h2>
        <button 
          onClick={onNavigateLogin}
          className="btn-TontineChain text-xl px-12 py-6"
        >
          Ouvrir mon compte gratuit
        </button>
        <p className="mt-6 text-slate-400 text-sm font-medium uppercase tracking-widest italic">Aucun frais caché • Sécurité Blockchain</p>
      </section>

      {/* FOOTER */}
      <footer className="py-12 border-t border-slate-100">
        <div className="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-8">
          <div className="flex items-center gap-3">
             <img src={logoOfficial} alt="Logo" className="w-8 h-8 rounded-full" />
             <span className="font-black text-slate-800 uppercase">TontineChain</span>
          </div>
          <p className="text-slate-400 text-xs font-bold uppercase tracking-widest">© 2026 TontineChain • Hackathon MIABE 2026 🇧🇯</p>
        </div>
      </footer>
    </div>
  );
};

export default LandingPage;
