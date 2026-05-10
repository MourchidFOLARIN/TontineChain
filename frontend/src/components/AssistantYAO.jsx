import React, { useState, useRef, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Bot, Send, Sparkles, TrendingUp, CalendarClock, HelpCircle, ArrowLeft, ShieldCheck, Loader2 } from 'lucide-react';

const AssistantYAO = ({ onBack, user }) => {
  const [messages, setMessages] = useState([
    {
      role: 'ai',
      text: `👋 Bonjour ${user?.first_name || ''} ! Je suis **YAO**, votre assistant IA personnel spécialisé en tontines.\n\nJe suis là pour vous accompagner dans votre réussite financière sur TontineChain.\n\nComment puis-je vous aider aujourd'hui ?`,
      time: 'Maintenant'
    }
  ]);
  const [input, setInput] = useState('');
  const [isTyping, setIsTyping] = useState(false);
  const chatEndRef = useRef(null);

  const suggestions = [
    { icon: <TrendingUp size={16} />, label: "Analyse d'épargne", query: "Analyse mes habitudes d'épargne" },
    { icon: <CalendarClock size={16} />, label: "Mes échéances", query: "Quelles sont mes prochaines échéances ?" },
    { icon: <Sparkles size={16} />, label: "Conseils VIP", query: "Comment optimiser mes cotisations ?" },
    { icon: <HelpCircle size={16} />, label: "Sécurité Blockchain", query: "Comment fonctionne la sécurité ?" },
  ];

  const aiResponses = {
    "épargne": `📊 **Analyse de votre profil**\n\nVoici votre bilan actuel :\n• **Score de confiance** : ${user?.score_confiance || 95}/100 ⭐\n• **Statut** : Membre Elite\n• **Fiabilité** : Excellente\n\n💡 Conseil : Votre profil est dans le **Top 5%** des utilisateurs. Continuez ainsi pour débloquer des tontines à capital élevé (jusqu'à 5.000.000 FCFA).`,
    "échéance": `📅 **Vos prochaines échéances**\n\n• **Tontine Dantokpa** — Demain (50 000 FCFA)\n• **Artisans Bénin** — Dans 5 jours (25 000 FCFA)\n\n⏰ N'oubliez pas que chaque paiement à temps augmente votre score de confiance de +0.5 point.`,
    "optimiser": `💡 **Conseils Stratégiques**\n\n1. **Règle des 20%** : Ne consacrez pas plus de 20% de vos revenus mensuels aux tontines.\n2. **Diversification** : Combinez une tontine hebdomadaire pour les flux et une mensuelle pour l'épargne projet.\n3. **Assurance** : Activez l'option de secours pour protéger vos cotisations en cas d'imprévu.\n\n📈 Votre score actuel vous permet déjà de parrainer de nouveaux membres.`,
    "sécurité": `🔗 **Sécurité Blockchain & IA**\n\nSur TontineChain, votre argent est protégé par :\n1. **Smart Contracts** immuables (code sur Polygon).\n2. **Identité Vérifiée** (NPI/KYC).\n3. **IA YAO** (moi-même) qui surveille les comportements suspects.\n\n🛡️ Aucun administrateur ne peut toucher à vos fonds. La distribution est 100% automatique.`,
  };

  useEffect(() => {
    chatEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages, isTyping]);

  const getAiResponse = (query) => {
    const q = query.toLowerCase();
    if (q.includes('épargne') || q.includes('analyse')) return aiResponses['épargne'];
    if (q.includes('échéance') || q.includes('prochaine') || q.includes('quand')) return aiResponses['échéance'];
    if (q.includes('optimis') || q.includes('conseil') || q.includes('vip')) return aiResponses['optimiser'];
    if (q.includes('sécurité') || q.includes('fonctionne') || q.includes('blockchain')) return aiResponses['sécurité'];
    return `Merci pour votre question ! 🤔\n\nJe suis optimisé pour le hackathon MIABE 2026. Je peux vous aider sur la sécurité, vos échéances ou l'optimisation de vos gains.\n\nDites-moi ce qui vous préoccupe !`;
  };

  const handleSend = (text) => {
    const msg = text || input;
    if (!msg.trim()) return;

    setMessages(prev => [...prev, { role: 'user', text: msg, time: 'Maintenant' }]);
    setInput('');
    setIsTyping(true);

    setTimeout(() => {
      setMessages(prev => [...prev, { role: 'ai', text: getAiResponse(msg), time: 'Maintenant' }]);
      setIsTyping(false);
    }, 1500);
  };

  const renderText = (text) => {
    return text.split('\n').map((line, i) => {
      const boldParsed = line.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
      return <p key={i} className="mb-1 leading-relaxed" dangerouslySetInnerHTML={{ __html: boldParsed }} />;
    });
  };

  return (
    <div className="flex flex-col h-full bg-tontine-darker text-white">
      {/* Header */}
      <div className="p-4 md:p-6 glass-panel flex items-center justify-between border-b border-white/5">
        <div className="flex items-center gap-4">
          <button onClick={onBack} className="p-2 hover:bg-white/5 rounded-full transition-colors text-gray-400 hover:text-white">
            <ArrowLeft size={20} />
          </button>
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 md:w-12 md:h-12 rounded-2xl bg-gradient-to-br from-tontine-orange to-tontine-gold flex items-center justify-center shadow-lg shadow-tontine-orange/20">
              <Bot size={24} className="text-tontine-darker" />
            </div>
            <div>
              <h3 className="font-bold text-sm md:text-base">Assistant YAO</h3>
              <div className="flex items-center gap-1.5">
                <div className="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse" />
                <span className="text-[9px] uppercase font-bold text-gray-500 tracking-widest">Expert Tontine IA</span>
              </div>
            </div>
          </div>
        </div>
        <div className="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-white/5 rounded-full border border-white/5">
          <ShieldCheck size={14} className="text-tontine-gold" />
          <span className="text-[10px] font-bold uppercase tracking-wider">Sécurisé</span>
        </div>
      </div>

      {/* Suggestions */}
      <div className="p-3 md:p-4 flex gap-2 overflow-x-auto border-b border-white/5 bg-tontine-dark/30 hide-scrollbar">
        {suggestions.map((s, i) => (
          <button 
            key={i} 
            onClick={() => handleSend(s.query)}
            className="flex items-center gap-2 px-4 py-2 rounded-full border border-white/10 bg-white/5 hover:bg-tontine-orange/10 hover:border-tontine-orange/30 text-[11px] md:text-xs text-gray-300 hover:text-tontine-orange transition-all whitespace-nowrap"
          >
            {s.icon} {s.label}
          </button>
        ))}
      </div>

      {/* Chat Messages */}
      <div className="flex-1 overflow-y-auto p-4 md:p-8 space-y-6 hide-scrollbar">
        <AnimatePresence initial={false}>
          {messages.map((msg, i) => (
            <motion.div 
              key={i} 
              initial={{ opacity: 0, y: 10, scale: 0.98 }} 
              animate={{ opacity: 1, y: 0, scale: 1 }}
              className={`flex ${msg.role === 'user' ? 'justify-end' : 'justify-start'}`}
            >
              <div className={`flex gap-3 max-w-[85%] md:max-w-[75%] ${msg.role === 'user' ? 'flex-row-reverse' : 'flex-row'}`}>
                {msg.role === 'ai' && (
                  <div className="w-8 h-8 md:w-9 md:h-9 rounded-xl bg-gradient-to-br from-tontine-orange to-tontine-gold flex items-center justify-center flex-shrink-0 mt-1 shadow-lg shadow-tontine-orange/10">
                    <Bot size={18} className="text-tontine-darker" />
                  </div>
                )}
                <div className="space-y-1">
                  <div className={`chat-bubble ${msg.role === 'user' ? 'chat-bubble-user text-sm' : 'chat-bubble-ai text-sm shadow-xl'}`}>
                    {renderText(msg.text)}
                  </div>
                  <p className={`text-[8px] text-gray-600 ${msg.role === 'user' ? 'text-right' : 'text-left'}`}>
                    {msg.time}
                  </p>
                </div>
              </div>
            </motion.div>
          ))}
          
          {isTyping && (
            <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} className="flex justify-start">
              <div className="flex gap-3">
                <div className="w-8 h-8 rounded-xl bg-tontine-orange flex items-center justify-center">
                  <Loader2 size={16} className="text-tontine-darker animate-spin" />
                </div>
                <div className="chat-bubble chat-bubble-ai text-[10px] italic flex items-center gap-2">
                  YAO analyse votre demande...
                </div>
              </div>
            </motion.div>
          )}
        </AnimatePresence>
        <div ref={chatEndRef} />
      </div>

      {/* Input Area */}
      <div className="p-4 md:p-6 glass-panel border-t-0 pb-8 md:pb-6 bg-tontine-dark/50">
        <form 
          onSubmit={(e) => { e.preventDefault(); handleSend(); }} 
          className="max-w-4xl mx-auto flex gap-3"
        >
          <input
            value={input}
            onChange={e => setInput(e.target.value)}
            placeholder="Posez une question sur vos finances..."
            className="input-field flex-1 py-4 px-6 rounded-2xl bg-white/5 border-white/10 focus:ring-tontine-orange text-sm"
          />
          <button 
            type="submit" 
            disabled={!input.trim() || isTyping}
            className="btn-primary p-4 rounded-2xl shadow-xl shadow-tontine-orange/20 disabled:opacity-50 hover:scale-105 transition-transform"
          >
            <Send size={20} />
          </button>
        </form>
        <p className="text-center text-[9px] text-gray-600 mt-4 uppercase tracking-widest font-bold">
          Sécurisé par Intelligence Artificielle & Blockchain 🇧🇯
        </p>
      </div>
    </div>
  );
};

export default AssistantYAO;
