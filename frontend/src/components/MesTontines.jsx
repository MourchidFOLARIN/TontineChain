import React from 'react';
import { motion } from 'framer-motion';
import { ArrowLeft, Users, TrendingUp, ShieldCheck, Clock, ChevronRight, Plus } from 'lucide-react';

const MesTontines = ({ groups, onSelectGroup, onNewGroup, onBack }) => {
  const getProgressColor = (pct) => {
    if (pct >= 75) return '#10b981';
    if (pct >= 40) return '#f39c12';
    return '#3b82f6';
  };

  return (
    <div style={{ minHeight: '100vh', background: 'var(--bg-primary)' }}>
      {/* Header */}
      <div style={{
        padding: '20px 24px',
        borderBottom: '1px solid var(--border-color)',
        background: 'var(--bg-secondary)',
        display: 'flex', alignItems: 'center', justifyContent: 'space-between'
      }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
          <button onClick={onBack} style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--text-primary)', display: 'flex' }}>
            <ArrowLeft size={22} />
          </button>
          <div>
            <h2 style={{ margin: 0, fontSize: 20, fontWeight: 700, fontFamily: 'Playfair Display, serif' }}>Mes Tontines</h2>
            <p style={{ margin: 0, fontSize: 13, color: 'var(--text-muted)' }}>{groups.length} groupe{groups.length > 1 ? 's' : ''} actif{groups.length > 1 ? 's' : ''}</p>
          </div>
        </div>
        <button onClick={onNewGroup} className="btn-primary" style={{ padding: '10px 18px', fontSize: 13 }}>
          <Plus size={16} /> Créer
        </button>
      </div>

      {/* Stats Bar */}
      <div style={{ padding: '16px 24px', display: 'flex', gap: 16, overflowX: 'auto' }} className="hide-scrollbar">
        {[
          { label: 'Total épargné', value: `${(groups.reduce((s, g) => s + g.amount, 0)).toLocaleString()} FCFA`, icon: <TrendingUp size={16} color="#10b981" />, color: '#10b981' },
          { label: 'Groupes actifs', value: groups.length, icon: <Users size={16} color="#3b82f6" />, color: '#3b82f6' },
          { label: 'Score moyen', value: '95/100', icon: <ShieldCheck size={16} color="#f39c12" />, color: '#f39c12' },
        ].map((stat, i) => (
          <div key={i} className="card" style={{ flexShrink: 0, minWidth: 140, padding: '12px 16px', display: 'flex', alignItems: 'center', gap: 10 }}>
            <div style={{ width: 34, height: 34, borderRadius: 10, background: `${stat.color}15`, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              {stat.icon}
            </div>
            <div>
              <div style={{ fontSize: 15, fontWeight: 700 }}>{stat.value}</div>
              <div style={{ fontSize: 11, color: 'var(--text-muted)' }}>{stat.label}</div>
            </div>
          </div>
        ))}
      </div>

      {/* Groups list */}
      <div style={{ padding: '8px 24px 40px' }}>
        {groups.length === 0 ? (
          <div style={{ textAlign: 'center', paddingTop: 80 }}>
            <div style={{ fontSize: 48, marginBottom: 16 }}>🏦</div>
            <h3 style={{ fontWeight: 700, marginBottom: 8 }}>Aucune tontine pour le moment</h3>
            <p style={{ color: 'var(--text-muted)', marginBottom: 24 }}>Créez votre première tontine ou rejoignez un groupe existant.</p>
            <button onClick={onNewGroup} className="btn-primary">Créer une tontine <ChevronRight size={16} /></button>
          </div>
        ) : (
          groups.map((group, i) => {
            const pct = Math.round((group.current_cycle / group.members) * 100);
            const color = getProgressColor(pct);
            return (
              <motion.div
                key={group.id}
                initial={{ opacity: 0, y: 16 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: i * 0.07 }}
                className="card"
                onClick={() => onSelectGroup(group)}
                style={{ marginBottom: 14, cursor: 'pointer', display: 'flex', flexDirection: 'column', gap: 14 }}
              >
                {/* Top row */}
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                    <div style={{ width: 46, height: 46, borderRadius: 14, background: `${color}15`, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                      <Users size={22} color={color} />
                    </div>
                    <div>
                      <h3 style={{ margin: 0, fontSize: 15, fontWeight: 700 }}>{group.name}</h3>
                      <p style={{ margin: 0, fontSize: 12, color: 'var(--text-muted)' }}>
                        Leader : {group.leader || 'N/A'} • {group.members} membres
                      </p>
                    </div>
                  </div>
                  <div style={{ textAlign: 'right' }}>
                    <div style={{ fontSize: 16, fontWeight: 800, color: '#f39c12' }}>
                      {group.amount.toLocaleString()} <span style={{ fontSize: 11 }}>FCFA</span>
                    </div>
                    <span style={{ fontSize: 11, padding: '2px 8px', borderRadius: 6, background: `${color}18`, color }}>
                      {group.cycle}
                    </span>
                  </div>
                </div>

                {/* Progress */}
                <div>
                  <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 6, fontSize: 12 }}>
                    <span style={{ color: 'var(--text-secondary)' }}>Cycle {group.current_cycle}/{group.members}</span>
                    <span style={{ color, fontWeight: 700 }}>{pct}%</span>
                  </div>
                  <div style={{ height: 6, borderRadius: 6, background: 'var(--bg-card-hover)', overflow: 'hidden' }}>
                    <motion.div
                      initial={{ width: 0 }}
                      animate={{ width: `${pct}%` }}
                      transition={{ duration: 0.8, delay: i * 0.1 }}
                      style={{ height: '100%', borderRadius: 6, background: `linear-gradient(90deg, ${color}, ${color}bb)` }}
                    />
                  </div>
                </div>

                {/* Code + CTA */}
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                  <span style={{ fontSize: 12, color: 'var(--text-muted)', fontFamily: 'monospace', background: 'var(--bg-card-hover)', padding: '3px 8px', borderRadius: 6 }}>
                    #{group.code || group.id}
                  </span>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 4, color: '#f39c12', fontSize: 13, fontWeight: 600 }}>
                    Voir les détails <ChevronRight size={14} />
                  </div>
                </div>
              </motion.div>
            );
          })
        )}
      </div>
    </div>
  );
};

export default MesTontines;
