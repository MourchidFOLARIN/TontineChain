import { AbsoluteFill, interpolate, useCurrentFrame, useVideoConfig, Img, spring, Series, staticFile } from 'remotion';
import React from 'react';

// =====================
// COMPONENTS
// =====================

const Slide = ({ title, subtitle, image, color = '#f39c12' }: any) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const moveUp = spring({ frame, fps, stiffness: 100 });
  const opacity = interpolate(frame, [0, 20], [0, 1]);
  const scale = interpolate(frame, [0, 150], [1, 1.08]);

  return (
    <AbsoluteFill style={{ backgroundColor: '#070d1f', overflow: 'hidden' }}>
      <Img src={staticFile(image)} style={{ width: '100%', height: '100%', objectFit: 'cover', opacity: 0.35, transform: `scale(${scale})` }} />
      <AbsoluteFill style={{ display: 'flex', flexDirection: 'column', justifyContent: 'center', alignItems: 'center', background: 'linear-gradient(to top, #070d1f 30%, transparent 100%)' }}>
        <div style={{ opacity, transform: `translateY(${100 - moveUp * 100}px)`, textAlign: 'center', padding: 60 }}>
          <h1 style={{ fontFamily: 'Georgia, serif', fontSize: 90, color: 'white', marginBottom: 20, textShadow: '0 10px 40px rgba(0,0,0,0.6)', lineHeight: 1.1 }}>{title}</h1>
          <div style={{ height: 6, width: 180, backgroundColor: color, margin: '0 auto 40px', borderRadius: 3, boxShadow: `0 0 30px ${color}88` }} />
          <p style={{ fontFamily: 'Arial, sans-serif', fontSize: 36, color: '#94a3b8', maxWidth: 1100, lineHeight: 1.6, margin: '0 auto' }}>{subtitle}</p>
        </div>
      </AbsoluteFill>
    </AbsoluteFill>
  );
};

const Intro = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const logoScale = spring({ frame, fps, stiffness: 180, damping: 15 });
  const textOpacity = interpolate(frame, [25, 50], [0, 1]);
  const tagOpacity = interpolate(frame, [45, 70], [0, 1]);
  const glowPulse = interpolate(frame, [0, 45, 90], [0.3, 1, 0.3]);

  return (
    <AbsoluteFill style={{ backgroundColor: '#070d1f', justifyContent: 'center', alignItems: 'center', flexDirection: 'column' }}>
      {/* Background grid */}
      <AbsoluteFill style={{ opacity: 0.1, backgroundImage: 'linear-gradient(#f39c12 1px, transparent 1px), linear-gradient(90deg, #f39c12 1px, transparent 1px)', backgroundSize: '80px 80px' }} />
      <Img src={staticFile('assets/logo.png')} style={{ width: 260, height: 260, borderRadius: '50%', border: `8px solid #f39c12`, transform: `scale(${logoScale})`, boxShadow: `0 0 ${80 * glowPulse}px rgba(243,156,18,${0.5 * glowPulse})`, objectFit: 'cover' }} />
      <div style={{ opacity: textOpacity, marginTop: 40, textAlign: 'center' }}>
        <h1 style={{ color: 'white', fontSize: 96, fontFamily: 'Georgia, serif', margin: 0, letterSpacing: -2 }}>TontineChain</h1>
        <div style={{ height: 4, width: 300, background: 'linear-gradient(90deg, transparent, #f39c12, transparent)', margin: '20px auto' }} />
      </div>
      <p style={{ opacity: tagOpacity, color: '#f39c12', fontSize: 28, letterSpacing: 8, fontWeight: 900, fontFamily: 'Arial, sans-serif', marginTop: 0 }}>FINANCE INCLUSIVE • BLOCKCHAIN • IA</p>
      <p style={{ opacity: tagOpacity, color: '#475569', fontSize: 22, marginTop: 16, letterSpacing: 3 }}>HACKATHON MIABE 2026 — DOMAINE D02</p>
    </AbsoluteFill>
  );
};

const StatsSlide = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const opacity = interpolate(frame, [0, 25], [0, 1]);

  const stats = [
    { value: '600+', label: 'plaintes/an au Bénin', color: '#ef4444', icon: '⚠️' },
    { value: '4.2M', label: 'utilisateurs de tontines', color: '#f39c12', icon: '👥' },
    { value: '85%', label: 'sans accès bancaire', color: '#3b82f6', icon: '🏦' },
    { value: '2B$', label: 'épargne informelle/an', color: '#10b981', icon: '💰' },
  ];

  return (
    <AbsoluteFill style={{ backgroundColor: '#070d1f', justifyContent: 'center', alignItems: 'center', flexDirection: 'column', padding: 80 }}>
      <AbsoluteFill style={{ opacity: 0.05, backgroundImage: 'radial-gradient(circle, #f39c12 1px, transparent 1px)', backgroundSize: '40px 40px' }} />
      <div style={{ opacity, textAlign: 'center', marginBottom: 60 }}>
        <h1 style={{ color: 'white', fontSize: 80, fontFamily: 'Georgia, serif', margin: 0 }}>Le Problème</h1>
        <div style={{ height: 5, width: 150, backgroundColor: '#ef4444', margin: '20px auto', borderRadius: 3, boxShadow: '0 0 20px #ef444488' }} />
        <p style={{ color: '#94a3b8', fontSize: 32 }}>La tontine traditionnelle : un système fragile</p>
      </div>
      <div style={{ display: 'flex', gap: 40, flexWrap: 'wrap', justifyContent: 'center', opacity }}>
        {stats.map((s, i) => {
          const cardSpring = spring({ frame: Math.max(0, frame - i * 8), fps, stiffness: 150 });
          return (
            <div key={i} style={{ transform: `scale(${cardSpring})`, background: `linear-gradient(135deg, ${s.color}22, ${s.color}11)`, border: `2px solid ${s.color}44`, borderRadius: 24, padding: '40px 50px', textAlign: 'center', minWidth: 320 }}>
              <div style={{ fontSize: 60, marginBottom: 10 }}>{s.icon}</div>
              <div style={{ fontSize: 72, fontWeight: 900, color: s.color, fontFamily: 'Arial, sans-serif' }}>{s.value}</div>
              <div style={{ fontSize: 24, color: '#94a3b8', marginTop: 10, maxWidth: 250 }}>{s.label}</div>
            </div>
          );
        })}
      </div>
    </AbsoluteFill>
  );
};

const FeaturesSlide = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const titleOpacity = interpolate(frame, [0, 20], [0, 1]);

  const features = [
    { icon: '🔗', title: 'Blockchain Polygon', desc: 'Smart Contracts immuables', color: '#10b981' },
    { icon: '🤖', title: 'Assistant YAO (IA)', desc: 'Analyse de risque en temps réel', color: '#8b5cf6' },
    { icon: '📱', title: 'OTP SMS/WhatsApp', desc: 'Infobip • Sécurité maximale', color: '#3b82f6' },
    { icon: '💳', title: 'FedaPay MTN/Moov', desc: 'Paiements Mobile Money', color: '#f39c12' },
    { icon: '🏆', title: 'Trust Score IA', desc: 'Score de confiance dynamique', color: '#ef4444' },
    { icon: '🎤', title: 'Audio Multilingue', desc: 'Fon • Yoruba • Français', color: '#06b6d4' },
  ];

  return (
    <AbsoluteFill style={{ backgroundColor: '#070d1f', padding: 60, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center' }}>
      <div style={{ opacity: titleOpacity, textAlign: 'center', marginBottom: 50 }}>
        <h1 style={{ color: 'white', fontSize: 76, fontFamily: 'Georgia, serif', margin: 0 }}>Nos Fonctionnalités</h1>
        <div style={{ height: 5, width: 200, background: 'linear-gradient(90deg, #f39c12, #10b981)', margin: '20px auto', borderRadius: 3 }} />
      </div>
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 30, width: '100%', maxWidth: 1600 }}>
        {features.map((f, i) => {
          const cardOpacity = interpolate(frame, [i * 6, i * 6 + 20], [0, 1]);
          const cardY = interpolate(frame, [i * 6, i * 6 + 25], [40, 0]);
          return (
            <div key={i} style={{ opacity: cardOpacity, transform: `translateY(${cardY}px)`, background: `linear-gradient(135deg, ${f.color}18, #0b1829)`, border: `1px solid ${f.color}33`, borderRadius: 20, padding: '36px 40px', display: 'flex', alignItems: 'flex-start', gap: 24 }}>
              <div style={{ fontSize: 48, lineHeight: 1 }}>{f.icon}</div>
              <div>
                <div style={{ color: 'white', fontSize: 30, fontWeight: 700, marginBottom: 8 }}>{f.title}</div>
                <div style={{ color: '#64748b', fontSize: 22 }}>{f.desc}</div>
              </div>
            </div>
          );
        })}
      </div>
    </AbsoluteFill>
  );
};

const DemoSlide = ({ title, recording }: any) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const opacity = interpolate(frame, [0, 20], [0, 1]);
  const scale = spring({ frame, fps, stiffness: 100, damping: 20 });

  return (
    <AbsoluteFill style={{ backgroundColor: '#070d1f', padding: '50px 60px 20px' }}>
      <div style={{ opacity, textAlign: 'center', marginBottom: 30 }}>
        <h2 style={{ color: 'white', fontSize: 56, fontFamily: 'Georgia, serif', margin: 0 }}>{title}</h2>
        <div style={{ height: 4, width: 100, backgroundColor: '#f39c12', margin: '16px auto', borderRadius: 3, boxShadow: '0 0 20px #f39c1288' }} />
      </div>
      <div style={{ flex: 1, display: 'flex', justifyContent: 'center', alignItems: 'flex-start' }}>
        <div style={{ width: '88%', backgroundColor: '#0f172a', borderRadius: '18px 18px 0 0', border: '1.5px solid #1e293b', overflow: 'hidden', boxShadow: '0 40px 80px rgba(0,0,0,0.9)', transform: `scale(${0.92 + scale * 0.08}) rotateX(4deg)` }}>
          <div style={{ height: 36, backgroundColor: '#1e293b', display: 'flex', alignItems: 'center', padding: '0 16px', gap: 8 }}>
            <div style={{ width: 10, height: 10, borderRadius: '50%', backgroundColor: '#ef4444' }} />
            <div style={{ width: 10, height: 10, borderRadius: '50%', backgroundColor: '#f39c12' }} />
            <div style={{ width: 10, height: 10, borderRadius: '50%', backgroundColor: '#10b981' }} />
            <div style={{ marginLeft: 16, flex: 1, height: 20, backgroundColor: '#0f172a', borderRadius: 10, fontSize: 13, color: '#475569', display: 'flex', alignItems: 'center', paddingLeft: 12 }}>
              🔒 tontine-chain-frontend.onrender.com
            </div>
          </div>
          <Img src={staticFile(recording)} style={{ width: '100%', height: 'auto', display: 'block' }} />
        </div>
      </div>
    </AbsoluteFill>
  );
};

const BlockchainSlide = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const opacity = interpolate(frame, [0, 20], [0, 1]);

  const nodes = [
    { x: 200, y: 200, label: 'Membre A', color: '#f39c12' },
    { x: 700, y: 100, label: 'Smart Contract', color: '#10b981' },
    { x: 1200, y: 200, label: 'Membre B', color: '#3b82f6' },
    { x: 700, y: 400, label: 'Polygon PoS', color: '#8b5cf6' },
    { x: 200, y: 550, label: 'Paiement MTN', color: '#ef4444' },
    { x: 1200, y: 550, label: 'Proof of Payout', color: '#06b6d4' },
  ];

  const pulse = interpolate(frame, [0, 30, 60], [0.8, 1.2, 0.8]);

  return (
    <AbsoluteFill style={{ backgroundColor: '#070d1f', display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', padding: 60 }}>
      <div style={{ opacity, textAlign: 'center', marginBottom: 50 }}>
        <h1 style={{ color: 'white', fontSize: 80, fontFamily: 'Georgia, serif', margin: 0 }}>Architecture Blockchain</h1>
        <div style={{ height: 5, width: 200, backgroundColor: '#10b981', margin: '20px auto', borderRadius: 3, boxShadow: '0 0 25px #10b98188' }} />
        <p style={{ color: '#94a3b8', fontSize: 30 }}>Polygon PoS • Smart Contracts • Gasless Transactions</p>
      </div>
      <div style={{ display: 'flex', gap: 60, justifyContent: 'center', flexWrap: 'wrap', opacity }}>
        {nodes.map((n, i) => {
          const nodeScale = spring({ frame: Math.max(0, frame - i * 5), fps, stiffness: 200 });
          return (
            <div key={i} style={{ transform: `scale(${nodeScale})`, background: `radial-gradient(circle, ${n.color}22, transparent)`, border: `2px solid ${n.color}66`, borderRadius: 16, padding: '24px 36px', textAlign: 'center', boxShadow: `0 0 ${20 * pulse}px ${n.color}44` }}>
              <div style={{ fontSize: 40, marginBottom: 8 }}>⬡</div>
              <div style={{ color: n.color, fontSize: 24, fontWeight: 700 }}>{n.label}</div>
            </div>
          );
        })}
      </div>
    </AbsoluteFill>
  );
};

const OutroSlide = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const logoScale = spring({ frame, fps, stiffness: 150 });
  const textOpacity = interpolate(frame, [15, 40], [0, 1]);
  const btnOpacity = interpolate(frame, [35, 55], [0, 1]);
  const glow = interpolate(frame, [0, 45, 90], [0.3, 1.0, 0.3]);

  return (
    <AbsoluteFill style={{ backgroundColor: '#070d1f', justifyContent: 'center', alignItems: 'center', flexDirection: 'column' }}>
      <AbsoluteFill style={{ opacity: 0.07, backgroundImage: 'linear-gradient(#f39c12 1px, transparent 1px), linear-gradient(90deg, #f39c12 1px, transparent 1px)', backgroundSize: '80px 80px' }} />
      <Img src={staticFile('assets/logo.png')} style={{ width: 200, height: 200, borderRadius: '50%', border: '6px solid #f39c12', transform: `scale(${logoScale})`, boxShadow: `0 0 ${70 * glow}px rgba(243,156,18,${0.6 * glow})`, objectFit: 'cover' }} />
      <div style={{ opacity: textOpacity, textAlign: 'center', marginTop: 40 }}>
        <h1 style={{ color: 'white', fontSize: 80, fontFamily: 'Georgia, serif', margin: 0, lineHeight: 1.1 }}>Rejoignez la Révolution<br />Financière</h1>
        <div style={{ height: 4, width: 400, background: 'linear-gradient(90deg, transparent, #f39c12, transparent)', margin: '30px auto' }} />
        <p style={{ color: '#64748b', fontSize: 26, letterSpacing: 3 }}>HACKATHON MIABE 2026 • DOMAINE D02 • INCLUSION FINANCIÈRE</p>
      </div>
      <div style={{ opacity: btnOpacity, marginTop: 40, display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 16 }}>
        <div style={{ background: 'linear-gradient(90deg, #f39c12, #e67e22)', borderRadius: 16, padding: '20px 60px', fontSize: 32, fontWeight: 900, color: '#070d1f', letterSpacing: 1, boxShadow: '0 0 40px rgba(243,156,18,0.5)' }}>
          tontine-chain-frontend.onrender.com
        </div>
        <div style={{ color: '#475569', fontSize: 20, letterSpacing: 2 }}>Made with ❤️ by Mourchid AI</div>
      </div>
    </AbsoluteFill>
  );
};

// =====================
// MAIN COMPOSITION
// =====================

export const TontinePresentation = () => {
  return (
    <Series>
      {/* Intro — 3s */}
      <Series.Sequence durationInFrames={90}>
        <Intro />
      </Series.Sequence>

      {/* Stats / Problème — 5s */}
      <Series.Sequence durationInFrames={150}>
        <StatsSlide />
      </Series.Sequence>

      {/* La Solution Blockchain */}
      <Series.Sequence durationInFrames={120}>
        <Slide title="La Solution" subtitle="TontineChain sécurise votre épargne grâce à la Blockchain Polygon. Les règles sont gravées dans des Smart Contracts immuables — personne ne peut tricher." image="assets/solution.png" color="#10b981" />
      </Series.Sequence>

      {/* Fonctionnalités Grid — 6s */}
      <Series.Sequence durationInFrames={180}>
        <FeaturesSlide />
      </Series.Sequence>

      {/* Sécurité OTP / NPI */}
      <Series.Sequence durationInFrames={120}>
        <Slide title="Sécurité & Identité" subtitle="OTP par SMS et WhatsApp via Infobip. Vérification NPI pour une identité béninoise certifiée. Zéro fraude, zéro imposteur." image="assets/security.png" color="#3b82f6" />
      </Series.Sequence>

      {/* Demo */}
      <Series.Sequence durationInFrames={300}>
        <DemoSlide title="Application Live" recording="assets/demo.webp" />
      </Series.Sequence>

      {/* Assistant YAO */}
      <Series.Sequence durationInFrames={150}>
        <Slide title="Assistant YAO" subtitle="Une IA conversationnelle disponible 24h/24 pour gérer vos tontines, analyser les risques et vous guider en Français, Fon et Yoruba." image="assets/yao.png" color="#8b5cf6" />
      </Series.Sequence>

      {/* Blockchain Architecture */}
      <Series.Sequence durationInFrames={150}>
        <BlockchainSlide />
      </Series.Sequence>

      {/* Trust Score */}
      <Series.Sequence durationInFrames={120}>
        <Slide title="Économie de Confiance" subtitle="Un Trust Score dynamique calculé par IA récompense les membres fidèles, pénalise les retardataires et sécurise chaque cycle." image="assets/trust.png" color="#f59e0b" />
      </Series.Sequence>

      {/* Paiements */}
      <Series.Sequence durationInFrames={120}>
        <Slide title="Paiements Inclusifs" subtitle="FedaPay intégré pour MTN Mobile Money et Moov Money. Des millions de béninois peuvent cotiser sans carte bancaire." image="assets/solution.png" color="#f1c40f" />
      </Series.Sequence>

      {/* Marché des Enchères */}
      <Series.Sequence durationInFrames={120}>
        <Slide title="Marché des Enchères" subtitle="Besoin urgent de fonds ? Le système d'enchères inversées permet d'obtenir votre payout en avance, tout en rémunérant le groupe." image="assets/impact.png" color="#e67e22" />
      </Series.Sequence>

      {/* Audio multilingue */}
      <Series.Sequence durationInFrames={120}>
        <Slide title="Inclusivité Vocale" subtitle="Guides audio en Fon, Yoruba et Français pour une accessibilité universelle — même sans savoir lire ou écrire." image="assets/security.png" color="#06b6d4" />
      </Series.Sequence>

      {/* Impact Social */}
      <Series.Sequence durationInFrames={120}>
        <Slide title="Impact Social" subtitle="TontineChain est bien plus qu'une application. C'est un moteur d'inclusion financière pour des millions de béninois exclus du système bancaire." image="assets/impact.png" color="#10b981" />
      </Series.Sequence>

      {/* Outro / CTA — 4s */}
      <Series.Sequence durationInFrames={120}>
        <OutroSlide />
      </Series.Sequence>
    </Series>
  );
};
