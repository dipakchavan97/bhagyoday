/**
 * Bhagyoday Ayurveda - Photorealistic Commercial Hero Animation Layer
 * 
 * Luxury Ayurvedic beverage commercial brought gently to life.
 * Pure Vanilla 2D Canvas + Lightweight CSS Parallax
 * Zero external libraries | Device-aware | Reduced-Motion Compliant
 */
(function() {
  'use strict';

  function initCinematicHero() {
    const hero = document.getElementById('ayurCinematicHero');
    const card = document.getElementById('ayurCommercialStageCard');
    const canvas = document.getElementById('ayurCommercialCanvas');
    const glow = document.getElementById('ayurCommercialGlow');

    if (!hero || !card || !canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    // Check reduced motion preference
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (prefersReducedMotion.matches) {
      canvas.style.display = 'none';
      return;
    }

    // Stream coordinates normalized (0.0 to 1.0)
    // Desktop (1440x1080 master)
    const DESKTOP_STREAM = {
      startX: 0.710,
      startY: 0.278,
      ctrlX: 0.740,
      ctrlY: 0.440,
      endX: 0.758,
      endY: 0.575,
      contactX: 0.760,
      contactY: 0.585
    };

    // Mobile (900x750 master)
    const MOBILE_STREAM = {
      startX: 0.723,
      startY: 0.267,
      ctrlX: 0.759,
      ctrlY: 0.453,
      endX: 0.777,
      endY: 0.560,
      contactX: 0.780,
      contactY: 0.585
    };

    // Droplets pool (2-3 organic droplets)
    const droplets = [
      { t: 0.08, speed: 0.38, sideOffset: -4, size: 2.2, alpha: 0, delay: 0 },
      { t: 0.52, speed: 0.42, sideOffset: 4.5, size: 2.6, alpha: 0, delay: 1.4 },
      { t: 0.85, speed: 0.35, sideOffset: -2.5, size: 1.8, alpha: 0, delay: 3.1 }
    ];

    // Glass ripples pool (2-3 subtle elliptical ripples)
    const ripples = [
      { r: 0, maxR: 36, speed: 17, alpha: 0, active: false, timer: 0.2 },
      { r: 0, maxR: 28, speed: 14, alpha: 0, active: false, timer: 1.5 },
      { r: 0, maxR: 44, speed: 18, alpha: 0, active: false, timer: 2.8 }
    ];

    // Bottle light reflection sweep
    let sweepProgress = 0;
    let sweepActive = true;
    let sweepWaitTimer = 0;

    let isVisible = true;
    let animId = null;
    let lastTime = performance.now();

    // Mouse parallax state (desktop only)
    let mouseX = 0, mouseY = 0;
    let curMouseX = 0, curMouseY = 0;
    const isDesktopPointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    function getStreamConfig() {
      return (window.innerWidth <= 767) ? MOBILE_STREAM : DESKTOP_STREAM;
    }

    function resizeCanvas() {
      const dpr = Math.min(window.devicePixelRatio || 1, 2);
      const rect = card.getBoundingClientRect();
      const newW = Math.round(rect.width * dpr);
      const newH = Math.round(rect.height * dpr);

      if (canvas.width !== newW || canvas.height !== newH) {
        canvas.width = newW;
        canvas.height = newH;
      }
    }

    function getBezierPoint(t, p0, p1, p2) {
      const invT = 1 - t;
      return invT * invT * p0 + 2 * invT * t * p1 + t * t * p2;
    }

    function render(now) {
      if (!isVisible) {
        animId = null;
        return;
      }

      const dt = Math.min((now - lastTime) / 1000, 0.1);
      lastTime = now;

      const w = canvas.width;
      const h = canvas.height;

      if (w === 0 || h === 0) {
        animId = requestAnimationFrame(render);
        return;
      }

      const scaleX = w / 1440;
      const scaleY = h / 1080;
      const stream = getStreamConfig();

      ctx.clearRect(0, 0, w, h);

      const p0 = { x: stream.startX * w, y: stream.startY * h };
      const p1 = { x: stream.ctrlX * w, y: stream.ctrlY * h };
      const p2 = { x: stream.endX * w, y: stream.endY * h };
      const pContact = { x: stream.contactX * w, y: stream.contactY * h };

      const streamTime = now * 0.001;

      // ==========================================
      // 1. JUICE STREAM FLOW DYNAMICS
      // ==========================================
      ctx.save();

      // Ambient cylinder stream sheen
      ctx.beginPath();
      ctx.moveTo(p0.x, p0.y);
      ctx.quadraticCurveTo(p1.x, p1.y, p2.x, p2.y);

      const streamGrad = ctx.createLinearGradient(p0.x, p0.y, p2.x, p2.y);
      streamGrad.addColorStop(0.0, 'rgba(255, 235, 175, 0.14)');
      streamGrad.addColorStop(0.35, 'rgba(235, 175, 50, 0.22)');
      streamGrad.addColorStop(0.70, 'rgba(215, 140, 25, 0.20)');
      streamGrad.addColorStop(1.0, 'rgba(255, 225, 145, 0.26)');

      ctx.strokeStyle = streamGrad;
      ctx.lineWidth = 14 * scaleX;
      ctx.lineCap = 'round';
      ctx.stroke();

      // Primary core specular highlight glint (moving downward smoothly)
      ctx.beginPath();
      ctx.moveTo(p0.x - (1.2 * scaleX), p0.y);
      ctx.quadraticCurveTo(p1.x - (1.2 * scaleX), p1.y, p2.x - (1.2 * scaleX), p2.y);
      ctx.strokeStyle = 'rgba(255, 250, 225, 0.36)';
      ctx.lineWidth = 2.4 * scaleX;
      ctx.setLineDash([18 * scaleX, 24 * scaleX, 36 * scaleX, 20 * scaleX]);
      ctx.lineDashOffset = -streamTime * 145 * scaleX;
      ctx.stroke();

      // Secondary fine glint (subtle right edge refraction)
      ctx.beginPath();
      ctx.moveTo(p0.x + (3.2 * scaleX), p0.y);
      ctx.quadraticCurveTo(p1.x + (3.2 * scaleX), p1.y, p2.x + (3.2 * scaleX), p2.y);
      ctx.strokeStyle = 'rgba(255, 235, 150, 0.24)';
      ctx.lineWidth = 1.8 * scaleX;
      ctx.setLineDash([26 * scaleX, 32 * scaleX, 16 * scaleX, 22 * scaleX]);
      ctx.lineDashOffset = -streamTime * 125 * scaleX;
      ctx.stroke();
      ctx.restore();

      // Internal fluid turbulence pulses (natural variations in density)
      ctx.save();
      for (let i = 0; i < 3; i++) {
        const pulseProgress = ((streamTime * 0.45) + (i * 0.333)) % 1.0;
        const px = getBezierPoint(pulseProgress, p0.x, p1.x, p2.x);
        const py = getBezierPoint(pulseProgress, p0.y, p1.y, p2.y);

        const pulseAlpha = Math.sin(pulseProgress * Math.PI) * 0.22;
        const pulseGrad = ctx.createRadialGradient(px, py, 1, px, py, 9 * scaleX);
        pulseGrad.addColorStop(0, `rgba(255, 248, 220, ${pulseAlpha})`);
        pulseGrad.addColorStop(0.5, `rgba(240, 180, 50, ${pulseAlpha * 0.55})`);
        pulseGrad.addColorStop(1, 'rgba(210, 130, 20, 0)');

        ctx.fillStyle = pulseGrad;
        ctx.beginPath();
        ctx.arc(px, py, 9 * scaleX, 0, Math.PI * 2);
        ctx.fill();
      }
      ctx.restore();

      // ==========================================
      // 2. GLASS CONTACT POINT & SURFACE RIPPLES
      // ==========================================
      // Contact point meniscus glow
      ctx.save();
      const meniscusAlpha = 0.20 + (Math.sin(streamTime * 8) * 0.07);
      const meniscusGrad = ctx.createRadialGradient(pContact.x, pContact.y, 1, pContact.x, pContact.y, 11 * scaleX);
      meniscusGrad.addColorStop(0, `rgba(255, 250, 235, ${meniscusAlpha})`);
      meniscusGrad.addColorStop(0.5, `rgba(245, 195, 80, ${meniscusAlpha * 0.5})`);
      meniscusGrad.addColorStop(1, 'rgba(220, 145, 25, 0)');
      ctx.fillStyle = meniscusGrad;
      ctx.beginPath();
      ctx.ellipse(pContact.x, pContact.y, 12 * scaleX, 4.8 * scaleY, 0, 0, Math.PI * 2);
      ctx.fill();
      ctx.restore();

      // Elliptical perspective surface ripples
      ctx.save();
      ripples.forEach(rip => {
        rip.timer -= dt;
        if (rip.timer <= 0) {
          rip.active = true;
          rip.r += rip.speed * scaleX * dt;
          rip.alpha = Math.max(0, 0.30 * (1 - (rip.r / rip.maxR)));

          if (rip.r >= rip.maxR) {
            rip.r = 0;
            rip.timer = 1.4 + Math.random() * 1.8;
            rip.active = false;
          }
        }

        if (rip.active && rip.alpha > 0.01) {
          ctx.beginPath();
          // 2.6 : 1 perspective ratio matching glass rim tilt
          ctx.ellipse(pContact.x, pContact.y + (2 * scaleY), rip.r * scaleX, (rip.r * 0.38) * scaleY, 0, 0, Math.PI * 2);

          const ripGrad = ctx.createLinearGradient(pContact.x - rip.r, pContact.y, pContact.x + rip.r, pContact.y);
          ripGrad.addColorStop(0, `rgba(255, 245, 200, ${rip.alpha * 0.2})`);
          ripGrad.addColorStop(0.4, `rgba(255, 252, 235, ${rip.alpha})`);
          ripGrad.addColorStop(0.8, `rgba(240, 175, 55, ${rip.alpha * 0.55})`);
          ripGrad.addColorStop(1, `rgba(210, 130, 20, ${rip.alpha * 0.1})`);

          ctx.strokeStyle = ripGrad;
          ctx.lineWidth = Math.max(0.7, (1.5 * (1 - rip.r / rip.maxR))) * scaleX;
          ctx.stroke();
        }
      });
      ctx.restore();

      // ==========================================
      // 3. DROPLETS (2-3 Organic Micro-Drops)
      // ==========================================
      ctx.save();
      droplets.forEach(drop => {
        drop.t += drop.speed * dt;
        if (drop.t > 1.0) {
          drop.t = 0.06;
          drop.delay = 1.0 + Math.random() * 2.6;
        }

        if (drop.delay > 0) {
          drop.delay -= dt;
          return;
        }

        const basePx = getBezierPoint(drop.t, p0.x, p1.x, p2.x);
        const basePy = getBezierPoint(drop.t, p0.y, p1.y, p2.y);
        const dx = basePx + (drop.sideOffset * scaleX);
        const dy = basePy;

        const dropAlpha = Math.sin(drop.t * Math.PI) * 0.62;
        if (dropAlpha > 0.05) {
          ctx.beginPath();
          ctx.ellipse(dx, dy, drop.size * scaleX, (drop.size * 1.35) * scaleY, 0.15, 0, Math.PI * 2);
          const dropGrad = ctx.createRadialGradient(
            dx - (0.5 * scaleX), dy - (0.5 * scaleY), 0.2,
            dx, dy, drop.size * scaleX
          );
          dropGrad.addColorStop(0, `rgba(255, 252, 225, ${dropAlpha})`);
          dropGrad.addColorStop(0.5, `rgba(240, 170, 45, ${dropAlpha * 0.9})`);
          dropGrad.addColorStop(1, `rgba(180, 100, 15, ${dropAlpha * 0.35})`);
          ctx.fillStyle = dropGrad;
          ctx.fill();

          // Tiny specular glint dot
          ctx.beginPath();
          ctx.arc(dx - (0.6 * scaleX), dy - (0.6 * scaleY), 0.7 * scaleX, 0, Math.PI * 2);
          ctx.fillStyle = `rgba(255, 255, 255, ${dropAlpha * 0.95})`;
          ctx.fill();
        }
      });
      ctx.restore();

      // ==========================================
      // 4. BOTTLE STUDIO LIGHT REFLECTION SWEEP
      // ==========================================
      ctx.save();
      if (sweepActive) {
        sweepProgress += dt * 0.11; // ~9s cycle
        if (sweepProgress >= 1.0) {
          sweepActive = false;
          sweepProgress = 0;
          sweepWaitTimer = 5.5 + Math.random() * 4.0; // 5.5s-9.5s pause
        } else {
          const sweepX = (0.28 + (sweepProgress * 0.35)) * w;
          const sweepY = (0.35 + (sweepProgress * 0.28)) * h;

          const sweepGrad = ctx.createLinearGradient(
            sweepX - (45 * scaleX), sweepY - (60 * scaleY),
            sweepX + (45 * scaleX), sweepY + (60 * scaleY)
          );
          const sweepAlpha = Math.sin(sweepProgress * Math.PI) * 0.10;
          sweepGrad.addColorStop(0, 'rgba(255, 255, 255, 0)');
          sweepGrad.addColorStop(0.45, `rgba(255, 250, 240, ${sweepAlpha * 0.7})`);
          sweepGrad.addColorStop(0.50, `rgba(255, 255, 255, ${sweepAlpha})`);
          sweepGrad.addColorStop(0.55, `rgba(255, 245, 225, ${sweepAlpha * 0.6})`);
          sweepGrad.addColorStop(1, 'rgba(255, 255, 255, 0)');

          ctx.fillStyle = sweepGrad;
          ctx.beginPath();
          ctx.ellipse(sweepX, sweepY, 32 * scaleX, 120 * scaleY, -0.65, 0, Math.PI * 2);
          ctx.fill();
        }
      } else {
        sweepWaitTimer -= dt;
        if (sweepWaitTimer <= 0) {
          sweepActive = true;
          sweepProgress = 0;
        }
      }
      ctx.restore();

      // ==========================================
      // 5. DESKTOP MOUSE PARALLAX (SMOOTH INTERPOLATION)
      // ==========================================
      if (isDesktopPointer && window.innerWidth > 991) {
        curMouseX += (mouseX - curMouseX) * 0.05;
        curMouseY += (mouseY - curMouseY) * 0.05;

        // Subtle 1-2px movement
        card.style.transform = `translate3d(${curMouseX * 1.2}px, ${curMouseY * 1.0}px, 0)`;
        if (glow) {
          glow.style.transform = `translate3d(${curMouseX * -2.2}px, ${curMouseY * -1.8}px, 0)`;
        }
      }

      animId = requestAnimationFrame(render);
    }

    // Window events
    window.addEventListener('resize', resizeCanvas, { passive: true });
    resizeCanvas();

    // Mouse listener (desktop fine pointer only)
    if (isDesktopPointer && window.innerWidth > 991) {
      hero.addEventListener('mousemove', function(e) {
        const rect = hero.getBoundingClientRect();
        const nx = (e.clientX - rect.left) / rect.width - 0.5;
        const ny = (e.clientY - rect.top) / rect.height - 0.5;
        mouseX = nx * 2;
        mouseY = ny * 2;
      }, { passive: true });

      hero.addEventListener('mouseleave', function() {
        mouseX = 0;
        mouseY = 0;
      }, { passive: true });
    }

    // IntersectionObserver to pause rendering when hero is off-screen
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          isVisible = entry.isIntersecting;
          if (isVisible && !animId) {
            lastTime = performance.now();
            animId = requestAnimationFrame(render);
          }
        });
      }, { threshold: 0.05 });
      observer.observe(hero);
    } else {
      animId = requestAnimationFrame(render);
    }
  }

  // Initialize once DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCinematicHero);
  } else {
    initCinematicHero();
  }
})();
