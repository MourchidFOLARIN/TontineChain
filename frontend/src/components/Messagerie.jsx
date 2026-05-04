import React, { useState, useRef, useEffect } from 'react';
import { motion } from 'framer-motion';
import { ArrowLeft, Send, Search, Users, Check, CheckCheck } from 'lucide-react';

const Messagerie = ({ onBack, user }) => {
  const [activeChat, setActiveChat] = useState(null);
  const [input, setInput] = useState('');
  const [searchQuery, setSearchQuery] = useState('');
  const chatEndRef = useRef(null);

  const [conversations, setConversations] = useState([
    {
      id: 1, name: "Tontine des Femmes de Dantokpa", type: 'group', unread: 3, avatar: '👩‍💼',
      members: ['Fatou M.', 'Awa K.', 'Mourchid F.', 'Koffi A.'],
      messages: [
        { sender: 'Fatou M.', text: "N'oubliez pas la cotisation de ce mois !", time: '14:30', isMe: false },
        { sender: 'Awa K.', text: "C'est fait pour moi ✅", time: '14:35', isMe: false },
        { sender: 'Mourchid F.', text: "Je verse ce soir après le marché", time: '14:40', isMe: true },
      ]
    },
    {
      id: 2, name: "Association des Artisans", type: 'group', unread: 0, avatar: '🔨',
      members: ['Jean D.', 'Pierre L.', 'Mourchid F.'],
      messages: [
        { sender: 'Jean D.', text: "Bienvenue à tous dans le groupe !", time: '09:00', isMe: false },
        { sender: 'Pierre L.', text: "Merci ! Hâte de commencer", time: '09:15', isMe: false },
      ]
    },
    {
      id: 3, name: "Koffi Adjovi", type: 'direct', unread: 1, avatar: '👤',
      members: [],
      messages: [
        { sender: 'Koffi A.', text: "Salut Mourchid, tu peux me confirmer ta position dans Diamant ?", time: '16:00', isMe: false },
        { sender: 'Mourchid F.', text: "Oui, je suis en position 1 ce cycle", time: '16:05', isMe: true },
        { sender: 'Koffi A.', text: "Parfait, je vais proposer un swap avec toi. Ça te va ?", time: '16:10', isMe: false },
      ]
    },
  ]);

  useEffect(() => {
    chatEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [activeChat, conversations]);

  const handleSend = (e) => {
    e.preventDefault();
    if (!input.trim() || !activeChat) return;

    const updated = conversations.map(c => {
      if (c.id === activeChat.id) {
        return {
          ...c,
          messages: [...c.messages, { sender: 'Mourchid F.', text: input, time: new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }), isMe: true }]
        };
      }
      return c;
    });
    setConversations(updated);
    setActiveChat(updated.find(c => c.id === activeChat.id));
    setInput('');
  };

  const filteredConversations = conversations.filter(c =>
    c.name.toLowerCase().includes(searchQuery.toLowerCase())
  );

  // Chat list view
  if (!activeChat) {
    return (
      <div style={{ minHeight: '100vh', background: 'var(--bg-primary)' }}>
        {/* Header */}
        <div style={{ padding: '16px 24px', borderBottom: '1px solid var(--border-color)', display: 'flex', alignItems: 'center', gap: 16, background: 'var(--bg-secondary)' }}>
          <button onClick={onBack} style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--text-primary)', display: 'flex' }}>
            <ArrowLeft size={22} />
          </button>
          <h2 style={{ margin: 0, fontSize: 18, fontWeight: 700 }}>Messagerie</h2>
          <span className="badge badge-info" style={{ marginLeft: 'auto' }}>
            {conversations.reduce((sum, c) => sum + c.unread, 0)} non lus
          </span>
        </div>

        {/* Search */}
        <div style={{ padding: '16px 24px' }}>
          <div style={{ position: 'relative' }}>
            <Search size={18} style={{ position: 'absolute', left: 14, top: '50%', transform: 'translateY(-50%)', color: 'var(--text-muted)' }} />
            <input
              value={searchQuery}
              onChange={e => setSearchQuery(e.target.value)}
              placeholder="Rechercher une conversation..."
              className="input-field"
              style={{ paddingLeft: 42 }}
            />
          </div>
        </div>

        {/* Conversations list */}
        <div style={{ padding: '0 24px' }}>
          {filteredConversations.map((conv, i) => (
            <motion.div key={conv.id} initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: i * 0.05 }}
              onClick={() => { setActiveChat(conv); setConversations(conversations.map(c => c.id === conv.id ? { ...c, unread: 0 } : c)); }}
              className="card" style={{ marginBottom: 8, cursor: 'pointer', display: 'flex', alignItems: 'center', gap: 14, padding: '14px 16px' }}>
              <div style={{ width: 48, height: 48, borderRadius: 14, background: 'var(--bg-card-hover)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 22, flexShrink: 0 }}>
                {conv.avatar}
              </div>
              <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 4 }}>
                  <span style={{ fontWeight: 600, fontSize: 14 }}>{conv.name}</span>
                  <span style={{ fontSize: 11, color: 'var(--text-muted)' }}>{conv.messages[conv.messages.length - 1]?.time}</span>
                </div>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                  <p style={{ margin: 0, fontSize: 13, color: 'var(--text-secondary)', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', maxWidth: '80%' }}>
                    {conv.messages[conv.messages.length - 1]?.text}
                  </p>
                  {conv.unread > 0 && (
                    <span style={{ width: 20, height: 20, borderRadius: '50%', background: 'linear-gradient(135deg, #f39c12, #f1c40f)', color: '#0b1120', fontSize: 11, fontWeight: 700, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                      {conv.unread}
                    </span>
                  )}
                </div>
              </div>
            </motion.div>
          ))}
        </div>
      </div>
    );
  }

  // Chat detail view
  return (
    <div style={{ display: 'flex', flexDirection: 'column', height: '100vh', background: 'var(--bg-primary)' }}>
      {/* Chat header */}
      <div style={{ padding: '14px 24px', borderBottom: '1px solid var(--border-color)', display: 'flex', alignItems: 'center', gap: 14, background: 'var(--bg-secondary)' }}>
        <button onClick={() => setActiveChat(null)} style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--text-primary)', display: 'flex' }}>
          <ArrowLeft size={22} />
        </button>
        <div style={{ width: 40, height: 40, borderRadius: 12, background: 'var(--bg-card-hover)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 20 }}>
          {activeChat.avatar}
        </div>
        <div>
          <h3 style={{ margin: 0, fontSize: 15, fontWeight: 700 }}>{activeChat.name}</h3>
          <p style={{ margin: 0, fontSize: 12, color: 'var(--text-muted)' }}>
            {activeChat.type === 'group' ? (
              <><Users size={12} style={{ display: 'inline', verticalAlign: 'middle', marginRight: 4 }} />{activeChat.members.length} membres</>
            ) : 'En ligne'}
          </p>
        </div>
      </div>

      {/* Messages */}
      <div style={{ flex: 1, overflowY: 'auto', padding: 24, display: 'flex', flexDirection: 'column', gap: 12 }}>
        {activeChat.messages.map((msg, i) => (
          <motion.div key={i} initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }}
            style={{ display: 'flex', flexDirection: 'column', alignItems: msg.isMe ? 'flex-end' : 'flex-start' }}>
            {!msg.isMe && activeChat.type === 'group' && (
              <span style={{ fontSize: 11, color: '#f39c12', fontWeight: 600, marginBottom: 2, marginLeft: 4 }}>{msg.sender}</span>
            )}
            <div className={msg.isMe ? 'chat-bubble chat-bubble-user' : 'chat-bubble chat-bubble-ai'}>
              <p style={{ margin: 0 }}>{msg.text}</p>
            </div>
            <span style={{ fontSize: 10, color: 'var(--text-muted)', marginTop: 2, display: 'flex', alignItems: 'center', gap: 4, padding: '0 4px' }}>
              {msg.time}
              {msg.isMe && <CheckCheck size={12} color="#10b981" />}
            </span>
          </motion.div>
        ))}
        <div ref={chatEndRef} />
      </div>

      {/* Input */}
      <div style={{ padding: '14px 24px', borderTop: '1px solid var(--border-color)', background: 'var(--bg-secondary)' }}>
        <form onSubmit={handleSend} style={{ display: 'flex', gap: 12 }}>
          <input
            value={input}
            onChange={e => setInput(e.target.value)}
            placeholder="Écrire un message..."
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

export default Messagerie;
