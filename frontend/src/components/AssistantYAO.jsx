import React, { useState, useRef, useEffect } from 'react';
import { motion } from 'framer-motion';
import { Bot, Send, Sparkles, TrendingUp, CalendarClock, HelpCircle, ArrowLeft } from 'lucide-react';

const AssistantYAO = ({ onBack, user }) => {
  const [messages, setMessages] = useState([
    {
      role: 'ai',
      text: `👋 Bonjour ${user?.first_name || ''} ! Je suis **YAO**, votre assistant IA personnel.\n\nJe peux vous aider à :\n• 📊 Analyser vos habitudes d'épargne\n• 📅 Gérer vos échéances\n• 💡 Optimiser vos cotisations\n• ❓ Répondre à vos questions\n\nComment puis-je vous aider aujourd'hui ?`,
      time: 'Maintenant'
    }
  ]);
  const [input, setInput] = useState('');
  const [isTyping, setIsTyping] = useState(false);
  const chatEndRef = useRef(null);

  const suggestions = [
    { icon: <TrendingUp size={16} />, label: "Analyser mes épargnes", query: "Analyse mes habitudes d'épargne" },
    { icon: <CalendarClock size={16} />, label: "Mes prochaines échéances", query: "Quelles sont mes prochaines échéances ?" },
    { icon: <Sparkles size={16} />, label: "Optimiser mes cotisations", query: "Comment optimiser mes cotisations ?" },
    { icon: <HelpCircle size={16} />, label: "Aide générale", query: "Comment fonctionne TontineChain ?" },
  ];

  const aiResponses = {
    "épargne": `📊 **Analyse de vos épargnes**\n\nVoici un résumé de votre activité :\n• **3 tontines** actives\n• **Score de confiance** : ${user?.score_confiance || 95}/100 ⭐\n• **Ponctualité** : 98% de paiements à temps\n\n💡 Conseil : Votre score est excellent ! Vous êtes éligible aux groupes VIP avec des montants plus élevés.`,
    "échéance": `📅 **Vos prochaines échéances**\n\n• **Tontine des Femmes de Dantokpa** — 15 Mai 2026 (50 000 FCFA)\n• **Association des Artisans** — 22 Mai 2026 (25 000 FCFA)\n• **Famille Miabe** — 1er Juin 2026 (10 000 FCFA)\n\n⏰ Je vous enverrai un rappel 24h avant chaque échéance.`,
    "optimiser": `💡 **Conseils d'optimisation**\n\n1. **Diversifiez** : Rejoignez des groupes avec des fréquences différentes (hebdo + mensuel)\n2. **Priorisez** : Payez les tontines avec le plus gros montant en premier\n3. **Fonds de secours** : Activez l'assurance (5%) pour vous protéger des imprévus\n4. **Enchères** : Utilisez le système de décotes pour récupérer vos fonds plus tôt si besoin\n\n📈 En suivant ces conseils, vous pourriez augmenter votre score de confiance de 3 points.`,
    "fonctionne": `🔗 **Comment fonctionne TontineChain ?**\n\n1. **Créez ou rejoignez** un groupe de tontine\n2. **Les règles** sont codées dans un smart contract sur Polygon\n3. **Cotisez** via Mobile Money (MTN/Moov)\n4. **La distribution** est automatique et transparente\n\n🛡️ Chaque transaction est enregistrée sur la blockchain, rendant tout détournement impossible.\n\nVoulez-vous en savoir plus sur un aspect particulier ?`,
  };

  useEffect(() => {
    chatEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  const getAiResponse = (query) => {
    const q = query.toLowerCase();
    if (q.includes('épargne') || q.includes('analyse')) return aiResponses['épargne'];
    if (q.includes('échéance') || q.includes('prochaine')) return aiResponses['échéance'];
    if (q.includes('optimis') || q.includes('conseil')) return aiResponses['optimiser'];
    if (q.includes('fonctionne') || q.includes('comment')) return aiResponses['fonctionne'];
    return `Merci pour votre question ! 🤔\n\nJe suis encore en apprentissage pour le hackathon MIABE 2026. Pour le moment, je peux vous aider avec :\n• L'analyse de vos épargnes\n• Vos échéances\n• L'optimisation de vos cotisations\n\nEssayez une de ces suggestions !`;
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
    }, 1200);
  };

  const renderText = (text) => {
    return text.split('\n').map((line, i) => {
      const boldParsed = line.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
      return <p key={i} style={{ margin: '2px 0' }} dangerouslySetInnerHTML={{ __html: boldParsed }} />;
    });
  };

  return (
    <div style={{ display: 'flex', flexDirection: 'column', height: '100vh', background: 'var(--bg-primary)' }}>
      {/* Header */}
      <div style={{ padding: '16px 24px', borderBottom: '1px solid var(--border-color)', display: 'flex', alignItems: 'center', gap: 16, background: 'var(--bg-secondary)' }}>
        <button onClick={onBack} style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--text-primary)', display: 'flex' }}>
          <ArrowLeft size={22} />
        </button>
        <div style={{ width: 40, height: 40, borderRadius: 12, background: 'linear-gradient(135deg, #8b5cf6, #6d28d9)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
          <Bot size={22} color="white" />
        </div>
        <div>
          <h3 style={{ margin: 0, fontSize: 16, fontWeight: 700 }}>YAO Assistant</h3>
          <p style={{ margin: 0, fontSize: 12, color: 'var(--text-muted)' }}>Votre conseiller IA 24/7</p>
        </div>
        <span className="badge badge-new" style={{ marginLeft: 'auto' }}>NEW</span>
      </div>

      {/* Suggestions */}
      <div style={{ padding: '12px 24px', display: 'flex', gap: 8, overflowX: 'auto', borderBottom: '1px solid var(--border-color)' }} className="hide-scrollbar">
        {suggestions.map((s, i) => (
          <button key={i} onClick={() => handleSend(s.query)}
            style={{ display: 'flex', alignItems: 'center', gap: 6, padding: '8px 14px', borderRadius: 20, border: '1px solid var(--border-color)', background: 'var(--bg-card)', color: 'var(--text-secondary)', cursor: 'pointer', whiteSpace: 'nowrap', fontSize: 13, transition: 'all 0.2s' }}
            onMouseEnter={e => { e.target.style.borderColor = '#f39c12'; e.target.style.color = '#f39c12'; }}
            onMouseLeave={e => { e.target.style.borderColor = 'var(--border-color)'; e.target.style.color = 'var(--text-secondary)'; }}>
            {s.icon} {s.label}
          </button>
        ))}
      </div>

      {/* Chat */}
      <div style={{ flex: 1, overflowY: 'auto', padding: '24px', display: 'flex', flexDirection: 'column', gap: 16 }}>
        {messages.map((msg, i) => (
          <motion.div key={i} initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }}
            style={{ display: 'flex', justifyContent: msg.role === 'user' ? 'flex-end' : 'flex-start' }}>
            {msg.role === 'ai' && (
              <div style={{ width: 32, height: 32, borderRadius: 10, background: 'linear-gradient(135deg, #8b5cf6, #6d28d9)', display: 'flex', alignItems: 'center', justifyContent: 'center', marginRight: 10, flexShrink: 0, marginTop: 4 }}>
                <Bot size={16} color="white" />
              </div>
            )}
            <div className={msg.role === 'user' ? 'chat-bubble chat-bubble-user' : 'chat-bubble chat-bubble-ai'}>
              {renderText(msg.text)}
            </div>
          </motion.div>
        ))}
        {isTyping && (
          <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
            <div style={{ width: 32, height: 32, borderRadius: 10, background: 'linear-gradient(135deg, #8b5cf6, #6d28d9)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              <Bot size={16} color="white" />
            </div>
            <div className="chat-bubble chat-bubble-ai" style={{ display: 'flex', gap: 4 }}>
              <span style={{ animation: 'pulse 1s infinite' }}>●</span>
              <span style={{ animation: 'pulse 1s infinite 0.2s' }}>●</span>
              <span style={{ animation: 'pulse 1s infinite 0.4s' }}>●</span>
            </div>
          </div>
        )}
        <div ref={chatEndRef} />
      </div>

      {/* Input */}
      <div style={{ padding: '16px 24px', borderTop: '1px solid var(--border-color)', background: 'var(--bg-secondary)' }}>
        <form onSubmit={(e) => { e.preventDefault(); handleSend(); }} style={{ display: 'flex', gap: 12 }}>
          <input
            value={input}
            onChange={e => setInput(e.target.value)}
            placeholder="Posez votre question à YAO..."
            className="input-field"
            style={{ flex: 1 }}
          />
          <button type="submit" className="btn-primary" style={{ padding: '12px 16px', borderRadius: 12 }}>
            <Send size={18} />
          </button>
        </form>
      </div>
    </div>
  );
};

export default AssistantYAO;
