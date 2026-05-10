import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { ShieldCheck, Eye, Zap, Bot, MessageCircle, Link2, ChevronDown, ChevronRight, AlertTriangle, BookOpen, Users, ArrowRight, Menu, X, Fingerprint, Phone, Smartphone, CreditCard, TrendingUp, Lock, Wifi, Brain } from 'lucide-react';
import logoOfficial from '../assets/logo_official.png';

const LandingPage = ({ onNavigateLogin, theme, toggleTheme }) => {
  const [openFaq, setOpenFaq] = useState(null);
  const [mobileMenu, setMobileMenu] = useState(false);

  const faqs = [
    { q: "Qu'est-ce que TontineChain ?", a: "TontineChain est une plateforme qui sécurise les tontines béninoises grâce à la blockchain. Vos fonds sont protégés par des smart contracts immuables et transparents." },
    { q: "Comment mes fonds sont-ils protégés ?", a: "Chaque groupe dispose d'un smart contract dédié sur la blockchain Polygon. Les règles sont codées et exécutées automatiquement, rendant tout détournement impossible." },
    { q: "Dois-je connaître la blockchain pour utiliser TontineChain ?", a: "Non ! L'interface est aussi simple qu'une application mobile classique. La blockchain fonctionne en arrière-plan pour vous protéger." },
    { q: "Quels sont les frais ?", a: "TontineChain est gratuit pour les utilisateurs. Les frais de transaction blockchain sont pris en charge par la plateforme (gasless)." },
    { q: "Comment rejoindre une tontine ?", a: "Créez votre compte, puis rejoignez un groupe existant via un code d'invitation ou créez le vôtre en quelques clics." },
  ];

  const navLinks = [
    { label: 'Problème', href: '#probleme' },
    { label: 'Solution', href: '#solution' },
    { label: 'Sécurité', href: '#securite' },
    { label: 'Fonctionnalités', href: '#fonctionnalites' },
    { label: 'FAQ', href: '#faq' },
  ];

  return (
    <div style={{ background: 'var(--bg-primary)', color: 'var(--text-primary)' }}>
      {/* ===== NAVBAR ===== */}
      <nav style={{ position: 'fixed', top: 0, left: 0, right: 0, zIndex: 50, background: 'var(--sidebar-bg)', backdropFilter: 'blur(20px)', borderBottom: '1px solid var(--border-color)' }}>
        <div style={{ maxWidth: 1200, margin: '0 auto', padding: '0 24px', display: 'flex', alignItems: 'center', justifyContent: 'space-between', height: 80 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
            <img src={logoOfficial} alt="TontineChain Logo" style={{ width: 48, height: 48, borderRadius: '50%', border: '2px solid var(--tontine-gold)', filter: 'drop-shadow(0 2px 10px rgba(243,156,18,0.3))' }} />
            <span style={{ fontFamily: 'Playfair Display, serif', fontWeight: 800, fontSize: 22 }}>TontineChain</span>
          </div>

          {/* Desktop nav */}
          <div style={{ display: 'flex', alignItems: 'center', gap: 24 }} className="hidden md:flex">
            {navLinks.map(l => (
              <a key={l.href} href={l.href} style={{ color: 'var(--text-secondary)', textDecoration: 'none', fontSize: 14, fontWeight: 500, transition: 'color 0.2s' }}
                onMouseEnter={e => e.target.style.color = '#f39c12'} onMouseLeave={e => e.target.style.color = 'var(--text-secondary)'}>{l.label}</a>
            ))}
            <button onClick={toggleTheme} style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)', borderRadius: 8, padding: '6px 10px', cursor: 'pointer', color: 'var(--text-primary)', fontSize: 16 }}>
              {theme === 'dark' ? '☀️' : '🌙'}
            </button>
            <button onClick={onNavigateLogin} className="btn-primary" style={{ padding: '8px 20px', fontSize: 14 }}>
              Connexion
            </button>
          </div>

          {/* Mobile hamburger */}
          <button onClick={() => setMobileMenu(!mobileMenu)} className="md:hidden" style={{ background: 'none', border: 'none', color: 'var(--text-primary)', cursor: 'pointer' }}>
            {mobileMenu ? <X size={24} /> : <Menu size={24} />}
          </button>
        </div>

        {/* Mobile menu */}
        {mobileMenu && (
          <div style={{ padding: '16px 24px', borderTop: '1px solid var(--border-color)', background: 'var(--sidebar-bg)' }} className="md:hidden">
            {navLinks.map(l => (
              <a key={l.href} href={l.href} onClick={() => setMobileMenu(false)} style={{ display: 'block', padding: '10px 0', color: 'var(--text-secondary)', textDecoration: 'none', fontSize: 15 }}>{l.label}</a>
            ))}
            <button onClick={onNavigateLogin} className="btn-primary" style={{ width: '100%', marginTop: 12, justifyContent: 'center' }}>Connexion</button>
          </div>
        )}
      </nav>

      {/* ===== HERO ===== */}
      <section style={{ paddingTop: 140, paddingBottom: 80, textAlign: 'center', position: 'relative', overflow: 'hidden' }}>
        <div style={{ position: 'absolute', top: '-20%', left: '-10%', width: 400, height: 400, background: 'rgba(243,156,18,0.08)', borderRadius: '50%', filter: 'blur(100px)' }} />
        <div style={{ position: 'absolute', bottom: '-20%', right: '-10%', width: 400, height: 400, background: 'rgba(30,58,138,0.08)', borderRadius: '50%', filter: 'blur(100px)' }} />
        <motion.div initial={{ opacity: 0, y: 30 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.8 }} style={{ maxWidth: 800, margin: '0 auto', padding: '0 24px', position: 'relative', zIndex: 1 }}>
          <div style={{ display: 'inline-flex', alignItems: 'center', gap: 8, background: 'rgba(243,156,18,0.1)', border: '1px solid rgba(243,156,18,0.2)', borderRadius: 20, padding: '6px 16px', marginBottom: 24, fontSize: 13, color: '#f39c12', fontWeight: 600 }}>
            🇧🇯 Hackathon MIABE 2026
          </div>
          <h1 style={{ fontFamily: 'Playfair Display, serif', fontSize: 'clamp(2rem, 5vw, 3.5rem)', fontWeight: 700, lineHeight: 1.2, marginBottom: 20 }}>
            Sécurisez vos tontines avec la <span style={{ background: 'linear-gradient(135deg, #f39c12, #f1c40f)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent' }}>Blockchain</span>
          </h1>
          <p style={{ fontSize: 18, color: 'var(--text-secondary)', maxWidth: 600, margin: '0 auto 32px', lineHeight: 1.7 }}>
            Au Bénin, <strong>600+ plaintes</strong> de détournements chaque année. TontineChain utilise des smart contracts immuables pour protéger vos <strong>200 milliards FCFA</strong> d'épargne collective.
          </p>
          <div style={{ display: 'flex', gap: 16, justifyContent: 'center', flexWrap: 'wrap' }}>
            <button onClick={onNavigateLogin} className="btn-primary" style={{ fontSize: 16, padding: '14px 32px' }}>
              Commencer gratuitement <ArrowRight size={18} />
            </button>
            <a href="#comment" className="btn-secondary" style={{ fontSize: 16, padding: '14px 32px', textDecoration: 'none' }}>
              Comment ça marche
            </a>
          </div>
        </motion.div>
      </section>

      {/* ===== PROBLÈME ===== */}
      <section id="probleme" style={{ padding: '80px 24px', maxWidth: 1200, margin: '0 auto' }}>
        <div style={{ textAlign: 'center', marginBottom: 48 }}>
          <h2 className="section-title">Le Problème</h2>
          <p className="section-subtitle">Les tontines traditionnelles exposent des millions de Béninois à des risques majeurs</p>
        </div>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))', gap: 24 }}>
          {[
            { icon: <AlertTriangle size={28} color="#ef4444" />, title: "Cale Sèche", desc: "Les collecteurs disparaissent avec l'épargne de tout le groupe", stats: "600+ plaintes à Porto-Novo (2021)", color: '#ef4444' },
            { icon: <BookOpen size={28} color="#f39c12" />, title: "Carnets Falsifiés", desc: "Registres physiques perdus, brûlés ou modifiés frauduleusement", stats: "Litiges impossibles à résoudre", color: '#f39c12' },
            { icon: <Users size={28} color="#8b5cf6" />, title: "Femmes Vulnérables", desc: "70% des participantes sont des femmes entrepreneures sans protection", stats: "59% dépendent des tontines", color: '#8b5cf6' },
          ].map((item, i) => (
            <motion.div key={i} initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ delay: i * 0.15 }}
              className="card" style={{ textAlign: 'center' }}>
              <div style={{ width: 56, height: 56, borderRadius: 16, background: `${item.color}15`, display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 16px' }}>
                {item.icon}
              </div>
              <h3 style={{ fontSize: 18, fontWeight: 700, marginBottom: 8 }}>{item.title}</h3>
              <p style={{ color: 'var(--text-secondary)', fontSize: 14, marginBottom: 12, lineHeight: 1.6 }}>{item.desc}</p>
              <span className="badge badge-warning">{item.stats}</span>
            </motion.div>
          ))}
        </div>
      </section>

      {/* ===== SOLUTION ===== */}
      <section id="solution" style={{ padding: '80px 24px', maxWidth: 1200, margin: '0 auto' }}>
        <div style={{ textAlign: 'center', marginBottom: 48 }}>
          <h2 className="section-title">Notre Solution</h2>
          <p className="section-subtitle">Architecture Smart Contract pour une sécurité absolue</p>
        </div>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))', gap: 20, marginBottom: 48 }}>
          {[
            { step: '01', title: 'Création du Groupe', desc: 'Définissez les membres et les règles' },
            { step: '02', title: 'Factory Déploie', desc: 'Un smart contract unique pour votre groupe' },
            { step: '03', title: 'Fonds Sécurisés', desc: 'Coffre-fort numérique immuable' },
            { step: '04', title: 'Distribution Auto', desc: 'Paiements automatiques selon l\'ordre' },
          ].map((item, i) => (
            <motion.div key={i} initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ delay: i * 0.1 }}
              className="card" style={{ textAlign: 'center', position: 'relative' }}>
              <div style={{ fontSize: 48, fontWeight: 900, background: 'linear-gradient(135deg, rgba(243,156,18,0.15), rgba(241,196,15,0.05))', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', marginBottom: 8 }}>{item.step}</div>
              <h4 style={{ fontWeight: 700, marginBottom: 6, fontSize: 15 }}>{item.title}</h4>
              <p style={{ color: 'var(--text-secondary)', fontSize: 13 }}>{item.desc}</p>
            </motion.div>
          ))}
        </div>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))', gap: 24 }}>
          {[
            { icon: <ShieldCheck size={28} color="#10b981" />, title: "Sécurité Absolue", items: ["Isolation des fonds par groupe", "Impossible de détourner", "Audité OpenZeppelin"] },
            { icon: <Eye size={28} color="#3b82f6" />, title: "Transparence Totale", items: ["Historique immuable", "Vérification indépendante", "Preuve de solvabilité"] },
            { icon: <Zap size={28} color="#f39c12" />, title: "Automatisation", items: ["Libération automatique", "Gestion des retards", "Notifications temps réel"] },
          ].map((item, i) => (
            <motion.div key={i} initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ delay: i * 0.15 }}
              className="card">
              <div style={{ marginBottom: 16 }}>{item.icon}</div>
              <h3 style={{ fontSize: 18, fontWeight: 700, marginBottom: 12 }}>{item.title}</h3>
              <ul style={{ listStyle: 'none', padding: 0, margin: 0 }}>
                {item.items.map((li, j) => (
                  <li key={j} style={{ padding: '6px 0', color: 'var(--text-secondary)', fontSize: 14, display: 'flex', alignItems: 'center', gap: 8 }}>
                    <span style={{ color: '#10b981' }}>✓</span> {li}
                  </li>
                ))}
              </ul>
            </motion.div>
          ))}
        </div>
      </section>

      {/* ===== SÉCURITÉ BACKEND ===== */}
      <section id="securite" style={{ padding: '80px 24px', background: 'linear-gradient(180deg, rgba(243,156,18,0.03), transparent, rgba(243,156,18,0.03))' }}>
        <div style={{ maxWidth: 1200, margin: '0 auto' }}>
          <div style={{ textAlign: 'center', marginBottom: 48 }}>
            <div style={{ display: 'inline-flex', alignItems: 'center', gap: 8, background: 'rgba(16,185,129,0.1)', border: '1px solid rgba(16,185,129,0.2)', borderRadius: 20, padding: '6px 16px', marginBottom: 16, fontSize: 13, color: '#10b981', fontWeight: 600 }}>
              🔒 Infrastructure de Sécurité
            </div>
            <h2 className="section-title">7 Couches de Protection</h2>
            <p className="section-subtitle">Chaque interaction est sécurisée par notre architecture backend multi-niveaux</p>
          </div>

          {/* Main security grid */}
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: 20, marginBottom: 40 }}>
            {[
              { icon: <Phone size={24} />, title: "Authentification OTP", desc: "Connexion sans mot de passe via un code unique envoyé par SMS et WhatsApp. Impossible de compromettre votre compte.", color: '#3b82f6', tags: ['SMS', 'WhatsApp', 'Infobip'] },
              { icon: <Fingerprint size={24} />, title: "Vérification KYC (NPI)", desc: "Chaque membre doit fournir son Numéro Personnel d'Identification (NPI) avant d'accéder aux fonctionnalités financières.", color: '#8b5cf6', tags: ['NPI', 'Identité', 'Anti-fraude'] },
              { icon: <TrendingUp size={24} />, title: "Score de Confiance Dynamique", desc: "Un algorithme évalue la fiabilité de chaque membre en temps réel (ponctualité, historique, comportement).", color: '#f39c12', tags: ['0-100 pts', 'Temps réel', 'Automatique'] },
              { icon: <Smartphone size={24} />, title: "Notifications Multi-Canal", desc: "Alertes critiques envoyées simultanément par SMS, WhatsApp et Email via l'API Infobip professionnelle.", color: '#10b981', tags: ['SMS', 'WhatsApp', 'Email'] },
              { icon: <CreditCard size={24} />, title: "Paiements Sécurisés (FedaPay)", desc: "Intégration directe avec MTN Mobile Money et Moov Money via la passerelle FedaPay certifiée.", color: '#ef4444', tags: ['MTN MoMo', 'Moov', 'Webhook'] },
              { icon: <Brain size={24} />, title: "Analyse de Risque IA", desc: "Un moteur prédictif scanne chaque groupe pour détecter les risques de défaut et recommander des actions préventives.", color: '#ec4899', tags: ['Prédictif', 'Préventif', 'ML'] },
            ].map((item, i) => (
              <motion.div key={i} initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ delay: i * 0.1 }}
                className="card" style={{ position: 'relative', overflow: 'hidden' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 12 }}>
                  <div style={{ width: 44, height: 44, borderRadius: 12, background: `${item.color}15`, display: 'flex', alignItems: 'center', justifyContent: 'center', color: item.color, flexShrink: 0 }}>
                    {item.icon}
                  </div>
                  <h3 style={{ fontSize: 16, fontWeight: 700, margin: 0 }}>{item.title}</h3>
                </div>
                <p style={{ color: 'var(--text-secondary)', fontSize: 13, lineHeight: 1.7, marginBottom: 14 }}>{item.desc}</p>
                <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap' }}>
                  {item.tags.map((tag, j) => (
                    <span key={j} style={{ padding: '3px 10px', borderRadius: 6, fontSize: 11, fontWeight: 600, background: `${item.color}12`, color: item.color, border: `1px solid ${item.color}25` }}>{tag}</span>
                  ))}
                </div>
              </motion.div>
            ))}
          </div>

          {/* Blockchain proof bar */}
          <motion.div initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }}
            className="card" style={{ display: 'flex', flexWrap: 'wrap', alignItems: 'center', justifyContent: 'center', gap: 32, padding: '28px 32px', textAlign: 'center', background: 'linear-gradient(135deg, rgba(243,156,18,0.06), rgba(241,196,15,0.02))' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
              <Lock size={20} color="#f39c12" />
              <div style={{ textAlign: 'left' }}>
                <div style={{ fontSize: 13, fontWeight: 700 }}>Blockchain Polygon</div>
                <div style={{ fontSize: 11, color: 'var(--text-muted)' }}>Preuve immuable on-chain</div>
              </div>
            </div>
            <div style={{ width: 1, height: 32, background: 'var(--border-color)' }} />
            <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
              <Wifi size={20} color="#10b981" />
              <div style={{ textAlign: 'left' }}>
                <div style={{ fontSize: 13, fontWeight: 700 }}>Gasless (EIP-2771)</div>
                <div style={{ fontSize: 11, color: 'var(--text-muted)' }}>0 frais pour les utilisateurs</div>
              </div>
            </div>
            <div style={{ width: 1, height: 32, background: 'var(--border-color)' }} />
            <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
              <ShieldCheck size={20} color="#3b82f6" />
              <div style={{ textAlign: 'left' }}>
                <div style={{ fontSize: 13, fontWeight: 700 }}>Laravel Sanctum</div>
                <div style={{ fontSize: 11, color: 'var(--text-muted)' }}>Tokens API sécurisés</div>
              </div>
            </div>
            <div style={{ width: 1, height: 32, background: 'var(--border-color)' }} />
            <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
              <Eye size={20} color="#8b5cf6" />
              <div style={{ textAlign: 'left' }}>
                <div style={{ fontSize: 13, fontWeight: 700 }}>Smart Contracts Solidity</div>
                <div style={{ fontSize: 11, color: 'var(--text-muted)' }}>Audité OpenZeppelin</div>
              </div>
            </div>
          </motion.div>
        </div>
      </section>

      {/* ===== FONCTIONNALITÉS ===== */}
      <section id="fonctionnalites" style={{ padding: '80px 24px', maxWidth: 1200, margin: '0 auto' }}>
        <div style={{ textAlign: 'center', marginBottom: 48 }}>
          <h2 className="section-title">Fonctionnalités</h2>
          <p className="section-subtitle">Des outils intelligents pour gérer vos tontines en toute simplicité</p>
        </div>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))', gap: 24 }}>
          {[
            { icon: <Link2 size={28} color="#f39c12" />, title: "Blockchain Sécurisée", desc: "Smart contracts audités sur Polygon. Vos fonds sont protégés 24/7 par la technologie blockchain.", tags: ["Immuable", "Transparent", "Automatisé"] },
            { icon: <Bot size={28} color="#8b5cf6" />, title: "YAO - Assistant IA", desc: "Votre conseiller financier intelligent disponible 24/7 pour vous guider dans vos décisions.", tags: ["Conseils", "Instantané", "Personnalisé"] },
            { icon: <MessageCircle size={28} color="#10b981" />, title: "Messagerie Intégrée", desc: "Communiquez avec les membres de votre groupe en temps réel, directement dans l'application.", tags: ["Chat direct", "Notifications", "Fichiers"] },
          ].map((item, i) => (
            <motion.div key={i} initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ delay: i * 0.15 }}
              className="card">
              <div style={{ marginBottom: 16 }}>{item.icon}</div>
              <h3 style={{ fontSize: 18, fontWeight: 700, marginBottom: 8 }}>{item.title}</h3>
              <p style={{ color: 'var(--text-secondary)', fontSize: 14, marginBottom: 16, lineHeight: 1.6 }}>{item.desc}</p>
              <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
                {item.tags.map((t, j) => <span key={j} className="badge badge-info">{t}</span>)}
              </div>
            </motion.div>
          ))}
        </div>
      </section>

      {/* ===== COMMENT ÇA MARCHE ===== */}
      <section id="comment" style={{ padding: '80px 24px', maxWidth: 800, margin: '0 auto' }}>
        <div style={{ textAlign: 'center', marginBottom: 48 }}>
          <h2 className="section-title">Comment ça marche</h2>
          <p className="section-subtitle">Simple comme bonjour, sécurisé par la blockchain</p>
        </div>
        {[
          { step: 1, title: "Créer votre groupe", desc: "Rassemblez vos amis, collègues ou membres de votre association" },
          { step: 2, title: "Définir les règles", desc: "Montant, fréquence, pénalités — tout est codé dans le smart contract" },
          { step: 3, title: "Inviter les membres", desc: "Chaque membre connecte son compte et accepte les règles" },
          { step: 4, title: "Commencer à épargner", desc: "Les cotisations sont automatiques, la distribution aussi !" },
        ].map((item, i) => (
          <motion.div key={i} initial={{ opacity: 0, x: -20 }} whileInView={{ opacity: 1, x: 0 }} viewport={{ once: true }} transition={{ delay: i * 0.15 }}
            style={{ display: 'flex', gap: 20, alignItems: 'flex-start', marginBottom: 32 }}>
            <div style={{ width: 48, height: 48, borderRadius: 14, background: 'linear-gradient(135deg, #f39c12, #f1c40f)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontWeight: 800, color: '#0b1120', fontSize: 18, flexShrink: 0 }}>
              {item.step}
            </div>
            <div>
              <h4 style={{ fontWeight: 700, fontSize: 17, marginBottom: 4 }}>{item.title}</h4>
              <p style={{ color: 'var(--text-secondary)', fontSize: 14, lineHeight: 1.6, margin: 0 }}>{item.desc}</p>
            </div>
          </motion.div>
        ))}
        <div style={{ textAlign: 'center', marginTop: 32 }}>
          <button onClick={onNavigateLogin} className="btn-primary" style={{ fontSize: 16, padding: '14px 32px' }}>
            Commencer gratuitement <ArrowRight size={18} />
          </button>
          <p style={{ color: 'var(--text-muted)', fontSize: 13, marginTop: 12 }}>Gratuit • Sécurisé • Sans engagement</p>
        </div>
      </section>

      {/* ===== FAQ ===== */}
      <section id="faq" style={{ padding: '80px 24px', maxWidth: 800, margin: '0 auto' }}>
        <div style={{ textAlign: 'center', marginBottom: 48 }}>
          <h2 className="section-title">FAQ</h2>
          <p className="section-subtitle">On a les réponses !</p>
        </div>
        {faqs.map((faq, i) => (
          <div key={i} className="card" style={{ marginBottom: 12, cursor: 'pointer', padding: 0 }} onClick={() => setOpenFaq(openFaq === i ? null : i)}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '16px 20px' }}>
              <span style={{ fontWeight: 600, fontSize: 15 }}>{faq.q}</span>
              <ChevronDown size={18} style={{ transform: openFaq === i ? 'rotate(180deg)' : 'rotate(0)', transition: 'transform 0.2s', color: 'var(--text-muted)' }} />
            </div>
            {openFaq === i && (
              <div style={{ padding: '0 20px 16px', color: 'var(--text-secondary)', fontSize: 14, lineHeight: 1.7 }}>
                {faq.a}
              </div>
            )}
          </div>
        ))}
      </section>

      {/* ===== CTA ===== */}
      <section style={{ padding: '80px 24px', textAlign: 'center', background: 'linear-gradient(180deg, transparent, rgba(243,156,18,0.05))' }}>
        <h2 className="section-title" style={{ fontSize: '2rem' }}>Rejoignez la révolution</h2>
        <p style={{ color: 'var(--text-secondary)', maxWidth: 500, margin: '0 auto 32px', fontSize: 16 }}>
          Sécurisez vos tontines béninoises avec la blockchain. Fini les détournements, place à la transparence totale.
        </p>
        <button onClick={onNavigateLogin} className="btn-primary" style={{ fontSize: 16, padding: '16px 40px' }}>
          Commencer maintenant <ChevronRight size={18} />
        </button>
      </section>

      {/* ===== FOOTER ===== */}
      <footer style={{ borderTop: '1px solid var(--border-color)', padding: '48px 24px 24px' }}>
        <div style={{ maxWidth: 1200, margin: '0 auto', display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: 32, marginBottom: 32 }}>
          <div>
            <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 16 }}>
              <img src={logoOfficial} alt="TontineChain Logo" style={{ width: 44, height: 44, borderRadius: '50%', border: '1px solid var(--tontine-gold)' }} />
              <span style={{ fontFamily: 'Playfair Display, serif', fontWeight: 800, fontSize: 18 }}>TontineChain</span>
            </div>
            <p style={{ color: 'var(--text-muted)', fontSize: 13, lineHeight: 1.6 }}>Sécurisez vos tontines avec la blockchain Polygon.</p>
          </div>
          <div>
            <h4 style={{ fontWeight: 700, fontSize: 14, marginBottom: 12 }}>Produit</h4>
            {['Créer une Tontine', 'Rejoindre une Tontine', 'Comment ça marche'].map(l => (
              <p key={l} style={{ color: 'var(--text-secondary)', fontSize: 13, marginBottom: 8, cursor: 'pointer' }}>{l}</p>
            ))}
          </div>
          <div>
            <h4 style={{ fontWeight: 700, fontSize: 14, marginBottom: 12 }}>Ressources</h4>
            {['Documentation', 'FAQ', 'Support'].map(l => (
              <p key={l} style={{ color: 'var(--text-secondary)', fontSize: 13, marginBottom: 8, cursor: 'pointer' }}>{l}</p>
            ))}
          </div>
          <div>
            <h4 style={{ fontWeight: 700, fontSize: 14, marginBottom: 12 }}>Contact</h4>
            <p style={{ color: 'var(--text-secondary)', fontSize: 13, marginBottom: 8 }}>📧 contact@tontinechain.bj</p>
            <p style={{ color: 'var(--text-secondary)', fontSize: 13 }}>📍 Cotonou, Bénin</p>
          </div>
        </div>
        <div style={{ borderTop: '1px solid var(--border-color)', paddingTop: 20, display: 'flex', justifyContent: 'space-between', flexWrap: 'wrap', gap: 12 }}>
          <p style={{ color: 'var(--text-muted)', fontSize: 12 }}>© 2026 TontineChain. Hackathon MIABE 2026.</p>
          <div style={{ display: 'flex', gap: 16 }}>
            {['Conditions d\'utilisation', 'Politique de confidentialité', 'Mentions légales'].map(l => (
              <span key={l} style={{ color: 'var(--text-muted)', fontSize: 12, cursor: 'pointer' }}>{l}</span>
            ))}
          </div>
        </div>
      </footer>
    </div>
  );
};

export default LandingPage;
