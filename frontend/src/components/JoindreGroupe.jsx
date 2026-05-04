import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { ArrowLeft, Search, UserPlus, ShieldCheck, Users, Clock, ChevronRight, Check, Loader2 } from 'lucide-react';

const DEMO_GROUPS = [
  { id: 10, code: 'MIABE-26', name: "Tontine MIABE 2026", amount: 75000, cycle: "Mensuel", members: 12, current_cycle: 2, leader: "Dr. Koffi A.", description: "Groupe officiel du Hackathon MIABE 2026", open: true },
  { id: 11, code: 'MKT-BJ', name: "Commerçants du Grand Marché", amount: 30000, cycle: "Hebdomadaire", members: 20, current_cycle: 5, leader: "Amina S.", description: "Réseau d'épargne des marchands de Cotonou", open: true },
  { id: 12, code: 'TECH-01', name: "Startup Tech Bénin", amount: 50000, cycle: "Mensuel", members: 8, current_cycle: 1, leader: "Jean D.", description: "Tontine pour entrepreneurs du numérique", open: true },
  { id: 13, code: 'EDU-BJ', name: "Fonds Éducation Bénin", amount: 20000, cycle: "Mensuel", members: 15, current_cycle: 3, leader: "Marie T.", description: "Épargne collective pour les frais scolaires", open: false },
];

const JoindreGroupe = ({ onBack, onJoin }) => {
  const [code, setCode] = useState('');
  const [searchQuery, setSearchQuery] = useState('');
  const [joining, setJoining] = useState(null);
  const [joined, setJoined] = useState([]);
  const [error, setError] = useState('');
  const [tab, setTab] = useState('discover'); // 'discover' | 'code'

  const filteredGroups = DEMO_GROUPS.filter(g =>
    g.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
    g.code.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const handleJoinById = async (group) => {
    if (joined.includes(group.id) || !group.open) return;
    setJoining(group.id);
    await new Promise(r => setTimeout(r, 1200));
    setJoining(null);
    setJoined(prev => [...prev, group.id]);
    onJoin(group.code);
  };

  const handleJoinByCode = async (e) => {
    e.preventDefault();
    setError('');
    if (!code.trim()) { setError('Veuillez saisir un code d\'invitation.'); return; }
    const found = DEMO_GROUPS.find(g => g.code.toLowerCase() === code.trim().toLowerCase());
    if (!found) { setError(`Aucun groupe trouvé avec le code "${code}". Vérifiez et réessayez.`); return; }
    if (!found.open) { setError('Ce groupe n\'accepte plus de nouveaux membres.'); return; }
    setJoining('code');
    await new Promise(r => setTimeout(r, 1200));
    setJoining(null);
    setJoined(prev => [...prev, found.id]);
    onJoin(code.trim());
    setCode('');
  };

  return (
    <div style={{ minHeight: '100vh', background: 'var(--bg-primary)' }}>
      {/* Header */}
      <div style={{ padding: '20px 24px', borderBottom: '1px solid var(--border-color)', background: 'var(--bg-secondary)', display: 'flex', alignItems: 'center', gap: 14 }}>
        <button onClick={onBack} style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--text-primary)', display: 'flex' }}>
          <ArrowLeft size={22} />
        </button>
        <div>
          <h2 style={{ margin: 0, fontSize: 20, fontWeight: 700, fontFamily: 'Playfair Display, serif' }}>Rejoindre un Groupe</h2>
          <p style={{ margin: 0, fontSize: 13, color: 'var(--text-muted)' }}>Trouvez une tontine qui vous correspond</p>
        </div>
      </div>

      {/* Tabs */}
      <div style={{ padding: '16px 24px 0', display: 'flex', gap: 8, borderBottom: '1px solid var(--border-color)', background: 'var(--bg-secondary)' }}>
        {[
          { key: 'discover', label: '🔍 Découvrir' },
          { key: 'code', label: '🔑 Code d\'invitation' },
        ].map(t => (
          <button key={t.key} onClick={() => setTab(t.key)}
            style={{ padding: '10px 20px', background: 'none', border: 'none', cursor: 'pointer', fontWeight: 600, fontSize: 14, color: tab === t.key ? '#f39c12' : 'var(--text-muted)', borderBottom: tab === t.key ? '2px solid #f39c12' : '2px solid transparent', marginBottom: -1 }}>
            {t.label}
          </button>
        ))}
      </div>

      <div style={{ padding: '24px' }}>
        <AnimatePresence mode="wait">
          {tab === 'code' ? (
            <motion.div key="code" initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0 }}>
              <div className="card" style={{ maxWidth: 500, margin: '0 auto' }}>
                <div style={{ textAlign: 'center', marginBottom: 24 }}>
                  <div style={{ width: 56, height: 56, borderRadius: 16, background: 'rgba(243,156,18,0.12)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 12px' }}>
                    <UserPlus size={26} color="#f39c12" />
                  </div>
                  <h3 style={{ fontWeight: 700, marginBottom: 6 }}>Entrer un code d'invitation</h3>
                  <p style={{ fontSize: 13, color: 'var(--text-muted)' }}>Demandez le code à l'administrateur du groupe</p>
                </div>
                <form onSubmit={handleJoinByCode} style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
                  <input
                    value={code}
                    onChange={e => { setCode(e.target.value.toUpperCase()); setError(''); }}
                    placeholder="Ex: MIABE-26"
                    className="input-field"
                    style={{ fontFamily: 'monospace', fontSize: 16, textAlign: 'center', letterSpacing: 2 }}
                  />
                  {error && (
                    <div style={{ padding: '10px 14px', borderRadius: 10, background: 'rgba(239,68,68,0.1)', border: '1px solid rgba(239,68,68,0.2)', color: '#ef4444', fontSize: 13 }}>
                      ⚠️ {error}
                    </div>
                  )}
                  <button type="submit" className="btn-primary" style={{ justifyContent: 'center' }} disabled={joining === 'code'}>
                    {joining === 'code' ? <><Loader2 size={16} className="animate-spin" /> Vérification...</> : <>Rejoindre <ChevronRight size={16} /></>}
                  </button>
                </form>
                <p style={{ fontSize: 12, color: 'var(--text-muted)', textAlign: 'center', marginTop: 16 }}>
                  Codes de test : <strong>MIABE-26</strong>, <strong>MKT-BJ</strong>, <strong>TECH-01</strong>
                </p>
              </div>
            </motion.div>
          ) : (
            <motion.div key="discover" initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0 }}>
              {/* Search */}
              <div style={{ position: 'relative', marginBottom: 20 }}>
                <Search size={18} style={{ position: 'absolute', left: 14, top: '50%', transform: 'translateY(-50%)', color: 'var(--text-muted)' }} />
                <input
                  value={searchQuery}
                  onChange={e => setSearchQuery(e.target.value)}
                  placeholder="Rechercher un groupe..."
                  className="input-field"
                  style={{ paddingLeft: 44 }}
                />
              </div>

              {/* Groups */}
              <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
                {filteredGroups.map((group, i) => {
                  const isJoined = joined.includes(group.id);
                  const isJoining = joining === group.id;
                  return (
                    <motion.div key={group.id} initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: i * 0.06 }}
                      className="card" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 16 }}>
                      <div style={{ display: 'flex', alignItems: 'center', gap: 14, flex: 1 }}>
                        <div style={{ width: 46, height: 46, borderRadius: 14, background: group.open ? 'rgba(16,185,129,0.1)' : 'rgba(107,114,128,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                          <Users size={22} color={group.open ? '#10b981' : '#6b7280'} />
                        </div>
                        <div style={{ minWidth: 0 }}>
                          <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 2 }}>
                            <h4 style={{ margin: 0, fontSize: 14, fontWeight: 700, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{group.name}</h4>
                            {!group.open && <span className="badge badge-warning" style={{ fontSize: 10, flexShrink: 0 }}>Complet</span>}
                          </div>
                          <p style={{ margin: 0, fontSize: 12, color: 'var(--text-muted)' }}>
                            {group.members} membres • {group.amount.toLocaleString()} FCFA/{group.cycle} • Code: <strong>{group.code}</strong>
                          </p>
                        </div>
                      </div>
                      <button
                        onClick={() => handleJoinById(group)}
                        disabled={!group.open || isJoined || isJoining}
                        style={{
                          flexShrink: 0, padding: '9px 18px', borderRadius: 10, fontWeight: 600, fontSize: 13, cursor: (!group.open || isJoined) ? 'not-allowed' : 'pointer',
                          background: isJoined ? 'rgba(16,185,129,0.12)' : !group.open ? 'var(--bg-card-hover)' : 'linear-gradient(135deg, #f39c12, #f1c40f)',
                          color: isJoined ? '#10b981' : !group.open ? 'var(--text-muted)' : '#0b1120',
                          border: 'none', display: 'flex', alignItems: 'center', gap: 6, opacity: (!group.open && !isJoined) ? 0.6 : 1
                        }}>
                        {isJoining ? <Loader2 size={14} className="animate-spin" /> : isJoined ? <><Check size={14} /> Rejoint</> : 'Rejoindre'}
                      </button>
                    </motion.div>
                  );
                })}
              </div>
            </motion.div>
          )}
        </AnimatePresence>
      </div>
    </div>
  );
};

export default JoindreGroupe;
