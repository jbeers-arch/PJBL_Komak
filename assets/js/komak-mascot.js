/*! komak-mascot.js: Controller Maskot Grok Laundry (Komak-Bot) dengan Animasi Cuci & Gelembung Busa
 * Terinspirasi oleh Anime.js, GSAP Timeline, dan Material Motion Physics.
 * Proyek: PJBL Komak Laundry & SIA Multi-Cabang
 */
(() => {
  'use strict';

  // Cegah inisialisasi ganda
  if (window.KomakMascotInitialized) return;
  window.KomakMascotInitialized = true;

  const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;

  // 1. Injeksi Styles Animasi Mesin Cuci & Gelembung Busa
  const styleEl = document.createElement('style');
  styleEl.id = 'komak-mascot-animations';
  styleEl.textContent = `
    /* Grok Laundry Mascot (Komak-Bot) Core & Washing Animations */
    @keyframes mascotFloat {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-2.5px) rotate(1deg); }
    }
    .mascot-orb {
      animation: mascotFloat 4s ease-in-out infinite;
      transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease;
      will-change: transform;
    }
    .mascot-orb:hover {
      transform: scale(1.08) translateY(-1px);
    }

    /* Mata Maskot */
    .mascot-eye {
      background-color: #ffffff !important;
      background: #ffffff !important;
      forced-color-adjust: none !important;
      transition: transform 0.12s ease-out, height 0.08s ease-in-out, border-radius 0.1s ease;
      box-shadow: none !important;
      filter: none !important;
    }
    html.dark .mascot-eye,
    .dark .mascot-eye {
      background-color: #ffffff !important;
      background: #ffffff !important;
    }
    .mascot-eye.blink {
      transform: scaleY(0.08) !important;
    }
    .mascot-eye.happy {
      height: 4px !important;
      border-radius: 6px 6px 0 0 !important;
      border-top: 2.5px solid #ffffff !important;
      background: transparent !important;
      box-shadow: none !important;
    }
    .mascot-eye.dizzy {
      height: 6px !important;
      width: 6px !important;
      border: 2px solid #ffffff !important;
      background: transparent !important;
      border-radius: 50% !important;
      animation: eyeDizzySpin 0.4s linear infinite;
    }
    @keyframes eyeDizzySpin {
      0% { transform: rotate(0deg) scale(0.9); }
      100% { transform: rotate(360deg) scale(0.9); }
    }

    /* 1. Fase Agitasi Mesin Cuci: Kucek Kencang Kiri-Kanan */
    @keyframes washAgitate {
      0% { transform: rotate(0deg) scale(1); }
      10% { transform: rotate(-32deg) scale(1.08, 0.92); }
      22% { transform: rotate(30deg) scale(0.92, 1.08); }
      35% { transform: rotate(-38deg) scale(1.1, 0.9); }
      48% { transform: rotate(34deg) scale(0.9, 1.1); }
      60% { transform: rotate(-26deg) scale(1.06, 0.94); }
      72% { transform: rotate(22deg) scale(0.95, 1.05); }
      84% { transform: rotate(-14deg) scale(1.02, 0.98); }
      92% { transform: rotate(8deg); }
      100% { transform: rotate(0deg) scale(1); }
    }
    .mascot-orb.is-washing-agitate {
      animation: washAgitate 1.4s cubic-bezier(0.36, 0.07, 0.19, 0.97) infinite !important;
      box-shadow: 0 0 20px 4px rgba(34, 211, 238, 0.55), 0 0 8px 2px rgba(99, 102, 241, 0.4) !important;
    }

    /* 2. Fase Putaran Peras Cepat (Centrifugal Spin Cycle) */
    @keyframes washSpinFast {
      0% { transform: rotate(0deg) scale(1); }
      25% { transform: rotate(240deg) scale(1.12, 0.92); }
      50% { transform: rotate(480deg) scale(1.15, 0.9); }
      75% { transform: rotate(660deg) scale(1.08, 0.96); }
      100% { transform: rotate(720deg) scale(1); }
    }
    .mascot-orb.is-washing-spin {
      animation: washSpinFast 1.1s cubic-bezier(0.25, 0.1, 0.25, 1) !important;
      box-shadow: 0 0 25px 6px rgba(56, 189, 248, 0.7), 0 0 10px 2px rgba(129, 140, 248, 0.5) !important;
    }

    /* 3. Efek Pendaran Air & Pusaran Mesin Cuci (Water Vortex Ring) */
    .mascot-water-vortex {
      position: absolute;
      inset: -3px;
      border-radius: 50%;
      border: 2px dashed rgba(165, 243, 252, 0.85);
      border-top-color: #ffffff;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s ease;
      animation: waterVortexSpin 0.7s linear infinite;
    }
    .is-washing .mascot-water-vortex {
      opacity: 1;
    }
    @keyframes waterVortexSpin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* 4. Lapisan Gelombang Air di dalam Drum Maskot */
    .mascot-water-wave {
      position: absolute;
      inset: 0;
      border-radius: 50%;
      overflow: hidden;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s ease;
    }
    .is-washing .mascot-water-wave {
      opacity: 0.55;
    }
    .mascot-water-wave::before {
      content: '';
      position: absolute;
      left: -20%;
      right: -20%;
      bottom: -10%;
      height: 80%;
      background: radial-gradient(ellipse at center, rgba(34, 211, 238, 0.85) 0%, rgba(37, 99, 235, 0.4) 70%, transparent 100%);
      border-radius: 40% 45% 0 0;
      animation: waveSlosh 1s ease-in-out infinite alternate;
    }
    @keyframes waveSlosh {
      0% { transform: rotate(-15deg) translateY(4px); }
      100% { transform: rotate(15deg) translateY(-2px); }
    }

    /* 5. Kilauan Bersih Kinclong (Clean Shine Sweep) */
    .mascot-shine-sweep {
      position: absolute;
      inset: 0;
      border-radius: 50%;
      overflow: hidden;
      pointer-events: none;
      opacity: 0;
    }
    .mascot-shine-sweep::after {
      content: '';
      position: absolute;
      top: -50%;
      left: -60%;
      width: 40%;
      height: 200%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.85), transparent);
      transform: rotate(35deg);
    }
    .mascot-shine-sweep.active {
      opacity: 1;
    }
    .mascot-shine-sweep.active::after {
      animation: shinePass 0.7s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
    }
    @keyframes shinePass {
      0% { transform: translateX(0%) rotate(35deg); opacity: 0; }
      20% { opacity: 1; }
      100% { transform: translateX(350%) rotate(35deg); opacity: 0; }
    }

    /* 6. Gelembung Busa Sabun Melayang (Realistic Iridescent Soap Bubbles) */
    .komak-soap-bubble {
      position: fixed;
      pointer-events: none;
      border-radius: 50%;
      background: radial-gradient(circle at 30% 28%, rgba(255, 255, 255, 0.95) 0%, rgba(224, 242, 254, 0.7) 25%, rgba(186, 230, 253, 0.45) 55%, rgba(56, 189, 248, 0.8) 95%);
      box-shadow: inset -1.5px -1.5px 3px rgba(255, 255, 255, 0.85),
                  inset 1px 1px 2px rgba(125, 211, 252, 0.7),
                  0 0 8px 1px rgba(56, 189, 248, 0.5);
      border: 0.5px solid rgba(255, 255, 255, 0.7);
      z-index: 99999;
      transform: translate3d(0, 0, 0);
      will-change: transform, opacity;
      animation: bubbleRiseWobble var(--dur, 1.3s) cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
    }
    .komak-soap-bubble::after {
      content: '';
      position: absolute;
      top: 18%;
      left: 20%;
      width: 30%;
      height: 30%;
      background: #ffffff;
      border-radius: 50%;
      opacity: 0.95;
      filter: blur(0.3px);
    }
    @keyframes bubbleRiseWobble {
      0% {
        transform: translate(0, 0) scale(0.3);
        opacity: 0.9;
      }
      25% {
        transform: translate(calc(var(--wobble-x, 8px) * 0.7), calc(var(--drift-y, -35px) * 0.3)) scale(1.05);
        opacity: 1;
      }
      55% {
        transform: translate(calc(var(--wobble-x, 8px) * -0.8), calc(var(--drift-y, -35px) * 0.65)) scale(1);
        opacity: 0.95;
      }
      85% {
        transform: translate(calc(var(--wobble-x, 8px) * 0.4), calc(var(--drift-y, -35px) * 0.9)) scale(1.15);
        opacity: 0.85;
      }
      100% {
        transform: translate(var(--wobble-x, 8px), var(--drift-y, -45px)) scale(1.45);
        opacity: 0;
        filter: blur(0.8px);
      }
    }

    /* 7. Bintang Kilau Bersih (Clean Sparkle Burst) */
    .komak-sparkle-star {
      position: fixed;
      pointer-events: none;
      z-index: 99999;
      color: #38bdf8;
      font-size: var(--sz, 14px);
      line-height: 1;
      text-shadow: 0 0 8px rgba(56, 189, 248, 0.9), 0 0 16px rgba(255, 255, 255, 0.8);
      animation: sparkleBurstAnim var(--dur, 0.8s) cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }
    @keyframes sparkleBurstAnim {
      0% {
        transform: translate(0, 0) scale(0) rotate(0deg);
        opacity: 0;
      }
      35% {
        transform: translate(var(--tx, 0), var(--ty, 0)) scale(1.3) rotate(90deg);
        opacity: 1;
      }
      100% {
        transform: translate(calc(var(--tx, 0) * 1.3), calc(var(--ty, 0) * 1.3)) scale(0) rotate(180deg);
        opacity: 0;
      }
    }

    /* Settle Bounce landing */
    .mascot-orb.is-clean-landing {
      animation: cleanLandingBounce 0.65s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
    }
    @keyframes cleanLandingBounce {
      0% { transform: scale(1.22); }
      40% { transform: scale(0.92); }
      75% { transform: scale(1.06); }
      100% { transform: scale(1); }
    }

    @media (prefers-reduced-motion: reduce) {
      .mascot-orb.is-washing-agitate,
      .mascot-orb.is-washing-spin {
        animation: none !important;
      }
      .komak-soap-bubble,
      .komak-sparkle-star {
        display: none !important;
      }
    }
  `;
  document.head.appendChild(styleEl);

  // 2. Synthesizer Efek Suara Ringan (Web Audio API - Opsional, Lembut, Ramah Pengguna)
  class MascotAudioFx {
    constructor() {
      this.ctx = null;
    }
    init() {
      if (!this.ctx && (window.AudioContext || window.webkitAudioContext)) {
        try {
          const AudioCtx = window.AudioContext || window.webkitAudioContext;
          this.ctx = new AudioCtx();
        } catch (e) {}
      }
      if (this.ctx && this.ctx.state === 'suspended') {
        this.ctx.resume().catch(() => {});
      }
    }
    playBubblePop(pitchMod = 1) {
      try {
        this.init();
        if (!this.ctx) return;
        const now = this.ctx.currentTime;
        const osc = this.ctx.createOscillator();
        const gain = this.ctx.createGain();

        const baseFreq = (480 + Math.random() * 120) * pitchMod;
        osc.type = 'sine';
        osc.frequency.setValueAtTime(baseFreq, now);
        osc.frequency.exponentialRampToValueAtTime(baseFreq * 1.6, now + 0.05);

        gain.gain.setValueAtTime(0.04, now);
        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.06);

        osc.connect(gain);
        gain.connect(this.ctx.destination);

        osc.start(now);
        osc.stop(now + 0.06);
      } catch (e) {}
    }
    playCleanChime() {
      try {
        this.init();
        if (!this.ctx) return;
        const now = this.ctx.currentTime;

        // Dual note chime: C6 (1046Hz) dan G6 (1568Hz)
        [1046.5, 1568.0].forEach((freq, idx) => {
          const osc = this.ctx.createOscillator();
          const gain = this.ctx.createGain();
          const noteTime = now + (idx * 0.08);

          osc.type = 'triangle';
          osc.frequency.setValueAtTime(freq, noteTime);

          gain.gain.setValueAtTime(0.04, noteTime);
          gain.gain.exponentialRampToValueAtTime(0.0001, noteTime + 0.45);

          osc.connect(gain);
          gain.connect(this.ctx.destination);

          osc.start(noteTime);
          osc.stop(noteTime + 0.45);
        });
      } catch (e) {}
    }
  }
  const audio = new MascotAudioFx();

  // 3. Helper Partikel: Pembuat Gelembung Busa Sabun
  function spawnSoapBubble(anchorRect, customAngle = null, customDist = null) {
    if (reduceMotion) return;

    const bubble = document.createElement('div');
    bubble.className = 'komak-soap-bubble';

    // Ukuran acak 9px - 18px
    const size = Math.floor(Math.random() * 10) + 9;
    bubble.style.width = size + 'px';
    bubble.style.height = size + 'px';

    // Posisi awal di sekitar perimeter maskot
    const centerX = anchorRect.left + anchorRect.width / 2;
    const centerY = anchorRect.top + anchorRect.height / 2;

    const angle = customAngle !== null ? customAngle : (Math.random() * Math.PI * 2);
    const radius = (anchorRect.width / 2) * (0.6 + Math.random() * 0.6);

    const startX = centerX + Math.cos(angle) * radius - size / 2;
    const startY = centerY + Math.sin(angle) * radius - size / 2;

    bubble.style.left = startX + 'px';
    bubble.style.top = startY + 'px';

    // Arah drift melayang ke atas dengan goyangan gelombang sinusoidal
    const driftY = -(Math.random() * 45 + 30);
    const wobbleX = (Math.random() - 0.5) * 28;
    const duration = (Math.random() * 0.5 + 0.95).toFixed(2);

    bubble.style.setProperty('--drift-y', driftY + 'px');
    bubble.style.setProperty('--wobble-x', wobbleX + 'px');
    bubble.style.setProperty('--dur', duration + 's');

    document.body.appendChild(bubble);

    // Mainkan suara pop mikro saat meletup
    setTimeout(() => {
      audio.playBubblePop(1 + (15 - size) * 0.05);
    }, (duration * 1000) * 0.85);

    // Hapus dari DOM setelah animasi selesai
    setTimeout(() => {
      if (bubble.parentNode) bubble.parentNode.removeChild(bubble);
    }, duration * 1000 + 100);
  }

  // 4. Helper Partikel: Bintang Kilau Bersih (Sparkle Burst)
  function spawnSparkleStars(anchorRect) {
    if (reduceMotion) return;

    const centerX = anchorRect.left + anchorRect.width / 2;
    const centerY = anchorRect.top + anchorRect.height / 2;
    const stars = ['✦', '✨', '★', '✦', '✧', '✨'];

    stars.forEach((icon, i) => {
      const star = document.createElement('div');
      star.className = 'komak-sparkle-star';
      star.textContent = icon;

      const angle = (i / stars.length) * (Math.PI * 2) + (Math.random() * 0.4);
      const distance = anchorRect.width * 0.75 + Math.random() * 20;

      const tx = Math.cos(angle) * distance;
      const ty = Math.sin(angle) * distance;

      star.style.left = (centerX - 7) + 'px';
      star.style.top = (centerY - 7) + 'px';
      star.style.setProperty('--tx', tx + 'px');
      star.style.setProperty('--ty', ty + 'px');
      star.style.setProperty('--sz', (Math.random() * 6 + 12) + 'px');
      star.style.setProperty('--dur', (Math.random() * 0.25 + 0.65).toFixed(2) + 's');

      document.body.appendChild(star);
      setTimeout(() => {
        if (star.parentNode) star.parentNode.removeChild(star);
      }, 900);
    });
  }

  // 5. Grok Laundry Mascot Controller
  class KomakLaundryMascot {
    constructor(element) {
      this.el = element;
      this.orb = element.querySelector('.mascot-orb');
      this.face = element.querySelector('.mascot-face');
      this.eyes = element.querySelectorAll('.mascot-eye');
      this.speech = element.querySelector('.mascot-speech');
      this.isWashing = false;
      this.isHovering = false;
      this.lastInteraction = Date.now();
      this.cachedRect = null;
      this.lastRectTime = 0;

      this.washQuotes = [
        { pre: 'Mulai cuci... Masukkan deterjen! 🧼', agitate: 'Kucek kucek kucek! Busa melimpah... 🫧', spin: 'Putar kencang! Peras airnya... 🌀', done: 'Kinclong, wangi & bersih tuntas! 🫧✨' },
        { pre: 'Air masuk... Bersiap mencuci! 💧', agitate: 'Kucek bersih noda membandel! 🧺', spin: 'Siklus bilas peras ekstra cepat! ⚡', done: 'Cucian segar siap disetrika! 🌸' },
        { pre: 'Takaran sabun pas, busa siap! 🫧', agitate: 'Agitasi tabung aktif... Kucek rata! 🌀', spin: 'Pengeringan sentrifugal maksimal! 💫', done: 'Bebas kuman, harum semerbak! 🥼✨' }
      ];

      this.greetings = [
        'Halo! Komak-Bot siap bantu! ✨',
        'Cucian wangi, pembukuan rapi! 🫧',
        'Semangat melayani pelanggan! 🧺',
        'Akuntansi SAK-EMKM balance! 📊',
        'Kasir hebat, rezeki lancar! 💫',
        'Ada cucian yang mau dicuci? 🧼'
      ];

      this.initLayers();
      this.bindEvents();
      this.scheduleBlinks();
      this.startIdleLoop();
    }

    // Suntikkan elemen pendukung animasi mesin cuci ke dalam orb maskot
    initLayers() {
      if (!this.orb) return;

      // 1. Vortex ring (pusaran air berputar)
      if (!this.orb.querySelector('.mascot-water-vortex')) {
        const vortex = document.createElement('div');
        vortex.className = 'mascot-water-vortex';
        this.orb.appendChild(vortex);
      }

      // 2. Wave layer (gelombang air cucian)
      if (!this.orb.querySelector('.mascot-water-wave')) {
        const wave = document.createElement('div');
        wave.className = 'mascot-water-wave';
        this.orb.appendChild(wave);
      }

      // 3. Shine sweep (kilatan bersih)
      if (!this.orb.querySelector('.mascot-shine-sweep')) {
        const shine = document.createElement('div');
        shine.className = 'mascot-shine-sweep';
        this.orb.appendChild(shine);
      }
    }

    getRect() {
      const now = performance.now();
      if (!this.cachedRect || now - this.lastRectTime > 800) {
        this.cachedRect = this.orb.getBoundingClientRect();
        this.lastRectTime = now;
      }
      return this.cachedRect;
    }

    setSpeech(text) {
      if (!this.speech) return;
      this.speech.innerText = text;
      this.speech.classList.remove('opacity-0', 'pointer-events-none');
      this.speech.classList.add('opacity-100');
    }

    hideSpeech(delay = 0) {
      if (!this.speech) return;
      setTimeout(() => {
        if (this.isWashing) return;
        this.speech.classList.remove('opacity-100');
        this.speech.classList.add('opacity-0', 'pointer-events-none');
      }, delay);
    }

    triggerBlink(doubleBlink = false) {
      if (this.isWashing) return;
      this.eyes.forEach(e => e.classList.add('blink'));
      setTimeout(() => {
        this.eyes.forEach(e => e.classList.remove('blink'));
        if (doubleBlink) {
          setTimeout(() => {
            this.eyes.forEach(e => e.classList.add('blink'));
            setTimeout(() => this.eyes.forEach(e => e.classList.remove('blink')), 100);
          }, 120);
        }
      }, 130);
    }

    scheduleBlinks() {
      const loop = () => {
        const delay = Math.random() * 3200 + 2200;
        setTimeout(() => {
          this.triggerBlink(Math.random() > 0.72);
          loop();
        }, delay);
      };
      loop();
    }

    // ========================================================
    // SIKLUS UTAMA ANIMASI MESIN CUCI: "KOMAK-BOT LAGI NYUCI"
    // (Timeline Choreography: Isi Air -> Kucek -> Peras -> Kinclong)
    // ========================================================
    runWashCycle() {
      if (this.isWashing) {
        // Jika sedang mencuci dan diklik lagi, lepaskan letupan busa ekstra!
        const r = this.getRect();
        for (let i = 0; i < 4; i++) {
          setTimeout(() => spawnSoapBubble(r), i * 60);
        }
        return;
      }

      this.isWashing = true;
      this.lastInteraction = Date.now();
      audio.init();

      const r = this.getRect();
      const quote = this.washQuotes[Math.floor(Math.random() * this.washQuotes.length)];

      // --- TAHAP 1: PRE-WASH / ISI AIR & DETERJEN (0ms - 400ms) ---
      this.el.classList.add('is-washing');
      this.setSpeech(quote.pre);
      this.orb.style.transform = 'scale(1.12, 0.9)';
      audio.playBubblePop(0.85);

      // Keluarkan 2 gelembung awal
      spawnSoapBubble(r, -Math.PI / 2, 20);
      spawnSoapBubble(r, -Math.PI / 3, 25);

      // --- TAHAP 2: AGITASI / KUCEK MESIN CUCI (400ms - 1900ms) ---
      setTimeout(() => {
        this.setSpeech(quote.agitate);
        this.orb.classList.add('is-washing-agitate');

        // Mata bergoyang dinamis seperti cucian yang dikucek
        this.eyes.forEach(e => e.classList.add('blink'));
        setTimeout(() => {
          this.eyes.forEach(e => e.classList.remove('blink'));
        }, 300);

        // Interval letupan busa sabun melayang (Staggered Bubble Stream ala Anime.js)
        let bubbleCount = 0;
        const bubbleInterval = setInterval(() => {
          if (!this.isWashing || bubbleCount > 9) {
            clearInterval(bubbleInterval);
            return;
          }
          const curRect = this.getRect();
          spawnSoapBubble(curRect);
          bubbleCount++;
        }, 130);

      }, 400);

      // --- TAHAP 3: CENTRIFUGAL SPIN CYCLE / PUTARAN PERAS (1900ms - 3000ms) ---
      setTimeout(() => {
        this.setSpeech(quote.spin);
        this.orb.classList.remove('is-washing-agitate');
        this.orb.classList.add('is-washing-spin');

        // Mata berubah menjadi pusaran pusing yang seru
        this.eyes.forEach(e => e.classList.add('dizzy'));

        // Pusaran gelembung mengitari maskot
        const curRect = this.getRect();
        for (let j = 0; j < 5; j++) {
          setTimeout(() => {
            const angle = (j / 5) * Math.PI * 2;
            spawnSoapBubble(curRect, angle, 35);
          }, j * 100);
        }
      }, 1900);

      // --- TAHAP 4: SELESAI KINCLONG & BERSIH WANGI (3000ms - 4300ms) ---
      setTimeout(() => {
        this.orb.classList.remove('is-washing-spin');
        this.orb.classList.add('is-clean-landing');
        this.el.classList.remove('is-washing');

        // Mata tersenyum bahagia (^ ^)
        this.eyes.forEach(e => {
          e.classList.remove('dizzy');
          e.classList.add('happy');
        });

        // Suara Ting & Denting Kaca Bersih
        audio.playCleanChime();

        // Kilatan cahaya menyapu permukaan maskot
        const shineEl = this.orb.querySelector('.mascot-shine-sweep');
        if (shineEl) {
          shineEl.classList.add('active');
          setTimeout(() => shineEl.classList.remove('active'), 800);
        }

        // Ledakan partikel bintang kilau (Sparkles)
        const finalRect = this.getRect();
        spawnSparkleStars(finalRect);

        this.setSpeech(quote.done);

      }, 3000);

      // --- SELESAI SIKLUS & KEMBALI NORMAL (4400ms) ---
      setTimeout(() => {
        this.orb.classList.remove('is-clean-landing');
        this.eyes.forEach(e => e.classList.remove('happy'));
        this.face.style.transform = 'translate(0px, 0px) scale(1)';
        this.orb.style.transform = 'rotate(0deg) scale(1)';
        this.isWashing = false;
        this.hideSpeech(400);
      }, 4400);
    }

    // Mini kocokan saat mouse hover (Pre-wash Teaser)
    triggerHoverTeaser() {
      if (this.isWashing) return;
      this.isHovering = true;
      const r = this.getRect();
      spawnSoapBubble(r, -Math.PI / 2, 22);
      setTimeout(() => {
        if (this.isHovering && !this.isWashing) {
          spawnSoapBubble(r, -Math.PI / 3, 24);
        }
      }, 180);
    }

    // Mini kocokan saat sistem idle (Automatic Laundry Life)
    triggerMiniIdleWash() {
      if (this.isWashing || Date.now() - this.lastInteraction < 18000) return;
      this.lastInteraction = Date.now();

      const r = this.getRect();
      spawnSoapBubble(r, -Math.PI / 2, 18);
      spawnSoapBubble(r, -Math.PI / 4, 22);

      this.orb.style.transform = 'rotate(-14deg) scale(1.05)';
      setTimeout(() => {
        this.orb.style.transform = 'rotate(12deg) scale(0.97)';
        setTimeout(() => {
          this.orb.style.transform = 'rotate(0deg) scale(1)';
        }, 180);
      }, 180);
    }

    bindEvents() {
      // 1. Klik Maskot -> Jalankan Animasi Cuci Lengkap
      this.el.addEventListener('click', (ev) => {
        ev.stopPropagation();
        this.runWashCycle();
      });

      // 2. Mouse Hover -> Busa mini menggoda
      this.el.addEventListener('mouseenter', () => {
        this.triggerHoverTeaser();
      });
      this.el.addEventListener('mouseleave', () => {
        this.isHovering = false;
      });

      // 3. Eye Tracking (Mengikuti Kursor saat tidak sedang mencuci)
      let mouseX = 0, mouseY = 0, rafPending = false;
      window.addEventListener('mousemove', (e) => {
        if (this.isWashing) return;
        this.lastInteraction = Date.now();
        mouseX = e.clientX;
        mouseY = e.clientY;

        if (!rafPending) {
          rafPending = true;
          requestAnimationFrame(() => {
            rafPending = false;
            if (this.isWashing) return;

            const rect = this.getRect();
            const mX = rect.left + rect.width / 2;
            const mY = rect.top + rect.height / 2;

            const diffX = mouseX - mX;
            const diffY = mouseY - mY;
            const dist = Math.sqrt(diffX * diffX + diffY * diffY);

            const maxDist = 480;
            const clampedDist = Math.min(dist, maxDist) / maxDist;
            const angle = Math.atan2(diffY, diffX);

            const maxOffset = this.el.classList.contains('w-16') ? 5 : 3.6;
            const offsetX = Math.cos(angle) * (clampedDist * maxOffset);
            const offsetY = Math.sin(angle) * (clampedDist * (maxOffset * 0.72));

            this.face.style.transform = `translate(${offsetX.toFixed(1)}px, ${offsetY.toFixed(1)}px)`;
            const tilt = (offsetX * 0.7).toFixed(1);
            this.orb.style.transform = `rotate(${tilt}deg)`;
          });
        }
      }, { passive: true });
    }

    startIdleLoop() {
      // Periksa setiap 22 detik untuk mini wash
      setInterval(() => {
        this.triggerMiniIdleWash();
      }, 22000);
    }
  }

  // 6. Inisialisasi Seluruh Elemen Maskot di Halaman
  function initAllMascots() {
    const mascotElements = document.querySelectorAll('.grok-mascot');
    if (!mascotElements.length) return;

    window.komakMascots = [];
    mascotElements.forEach(el => {
      const instance = new KomakLaundryMascot(el);
      window.komakMascots.push(instance);
    });
  }

  // 7. Global API: Bisa dipanggil saat POS Checkout, Scan Faktur, dll.
  window.triggerKomakWash = function() {
    if (window.komakMascots && window.komakMascots.length > 0) {
      window.komakMascots.forEach(m => m.runWashCycle());
    }
  };
  window.KomakMascot = {
    wash: window.triggerKomakWash,
    spawnBubble: (x, y) => spawnSoapBubble({ left: x, top: y, width: 40, height: 40 }),
    audio
  };

  // Jalankan saat DOM siap
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAllMascots);
  } else {
    initAllMascots();
  }
})();
