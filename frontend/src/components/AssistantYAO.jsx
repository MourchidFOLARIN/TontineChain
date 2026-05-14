import React, { useState, useRef, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Bot, Send, Sparkles, TrendingUp, CalendarClock, HelpCircle, ArrowLeft, ShieldCheck, Loader2 } from 'lucide-react';
import api from '../services/api';

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
    { icon: <TrendingUp size={14} />, label: "Mon épargne", query: "Analyse mon épargne" },
    { icon: <CalendarClock size={14} />, label: "Échéances", query: "Mes prochaines échéances" },
    { icon: <Sparkles size={14} />, label: "Conseils VIP", query: "Conseils pour épargner" },
  ];

  useEffect(() => {
    chatEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages, isTyping]);

  const handleSend = async (text) => {
    const msg = text || input;
    if (!msg.trim()) return;

    setMessages(prev => [...prev, { role: 'user', text: msg, time: 'Maintenant' }]);
    setInput('');
    setIsTyping(true);

    try {
      const response = await api.post('/ai/chat', { message: msg });
      
      setMessages(prev => [...prev, { 
        role: 'ai', 
        text: response.data.message, 
        time: 'Maintenant',
        analysis: response.data.demo_notice?.message 
      }]);
    } catch (err) {
      setMessages(prev => [...prev, { 
        role: 'ai', 
        text: "Désolé, j'ai eu un souci technique. Pouvez-vous répéter ?", 
        time: 'Maintenant' 
      }]);
    } finally {
      setIsTyping(false);
    }
  };

  const renderText = (text) => {
    return text.split('\n').map((line, i) => {
      const boldParsed = line.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
      return <p key={i} className="mb-1 leading-relaxed" dangerouslySetInnerHTML={{ __html: boldParsed }} />;
    });
  };

  return (
    <div className="flex flex-col h-full bg-slate-50 min-h-screen">
      {/* Header */}
      <div className="p-6 bg-white border-b border-slate-100 flex items-center justify-between sticky top-0 z-30">
        <div className="flex items-center gap-4">
          <button onClick={onBack} className="p-2 hover:bg-slate-50 rounded-full transition-colors text-slate-400">
            <ArrowLeft size={22} />
          </button>
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-TontineChain-orange flex items-center justify-center shadow-lg shadow-orange-500/20 text-white">
              <Bot size={22} />
            </div>
            <div>
              <h3 className="font-bold text-slate-800 text-sm">Assistant YAO</h3>
              <div className="flex items-center gap-1.5">
                <div className="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse" />
                <span className="text-[9px] uppercase font-bold text-slate-400 tracking-widest">Expert IA En Ligne</span>
              </div>
            </div>
          </div>
        </div>
        <ShieldCheck size={20} className="text-slate-200" />
      </div>

      {/* Chat Messages */}
      <div className="flex-1 overflow-y-auto p-6 space-y-6 hide-scrollbar">
        <AnimatePresence initial={false}>
          {messages.map((msg, i) => (
            <motion.div 
              key={i} 
              initial={{ opacity: 0, y: 10 }} 
              animate={{ opacity: 1, y: 0 }}
              className={`flex ${msg.role === 'user' ? 'justify-end' : 'justify-start'}`}
            >
              <div className={`flex gap-3 max-w-[85%] ${msg.role === 'user' ? 'flex-row-reverse' : 'flex-row'}`}>
                <div className="space-y-1">
                  <div className={`p-4 rounded-2xl text-sm leading-relaxed shadow-sm ${
                    msg.role === 'user' 
                    ? 'bg-TontineChain-blue text-white rounded-tr-none' 
                    : 'bg-white text-slate-700 border border-slate-100 rounded-tl-none'
                  }`}>
                    {renderText(msg.text)}
                    {msg.analysis && (
                      <div className="mt-3 pt-2 border-t border-slate-100 italic text-[10px] text-TontineChain-orange font-bold">
                        ✨ {msg.analysis}
                      </div>
                    )}
                  </div>
                  <p className={`text-[9px] font-bold text-slate-300 uppercase tracking-tighter ${msg.role === 'user' ? 'text-right' : 'text-left'}`}>
                    {msg.time}
                  </p>
                </div>
              </div>
            </motion.div>
          ))}
          
          {isTyping && (
            <div className="flex justify-start">
              <div className="bg-white border border-slate-100 p-4 rounded-2xl rounded-tl-none shadow-sm flex items-center gap-3">
                <Loader2 size={14} className="text-TontineChain-orange animate-spin" />
                <span className="text-xs text-slate-400 font-medium">YAO réfléchit...</span>
              </div>
            </div>
          )}
        </AnimatePresence>
        <div ref={chatEndRef} />
      </div>

      {/* Input Area */}
      <div className="p-6 bg-white border-t border-slate-100 safe-area-bottom">
        {/* Suggestions */}
        <div className="flex gap-2 overflow-x-auto mb-4 hide-scrollbar">
          {suggestions.map((s, i) => (
            <button 
              key={i} 
              onClick={() => handleSend(s.query)}
              className="flex items-center gap-2 px-4 py-2 rounded-full bg-slate-50 border border-slate-100 text-[11px] font-bold text-slate-500 whitespace-nowrap hover:bg-orange-50 hover:text-TontineChain-orange hover:border-orange-100 transition-all"
            >
              {s.icon} {s.label}
            </button>
          ))}
        </div>

        <form 
          onSubmit={(e) => { e.preventDefault(); handleSend(); }} 
          className="flex gap-3"
        >
          <input
            value={input}
            onChange={e => setInput(e.target.value)}
            placeholder="Posez votre question..."
            className="TontineChain-input flex-1"
          />
          <button 
            type="submit" 
            disabled={!input.trim() || isTyping}
            className="w-14 h-14 bg-TontineChain-orange text-white rounded-2xl flex items-center justify-center shadow-lg shadow-orange-500/20 active:scale-95 disabled:opacity-50 transition-all"
          >
            <Send size={22} />
          </button>
        </form>
      </div>
    </div>
  );
};

export default AssistantYAO;
