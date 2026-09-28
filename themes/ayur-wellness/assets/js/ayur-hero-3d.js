/**
 * Bhagyoday Ayurveda — Premium Cinematic 3D Hero Scene
 * 
 * Technology: Three.js (WebGL) + Graceful Progressive Enhancement
 * Feature Product: Amrit Heart Health Immunity Juice 500ml (ID: 33)
 * Verified Formulation: 60-Botanical Complex (Herbs, Fruits & Botanical Extracts)
 */

(function () {
  'use strict';

  // Check WebGL availability and reduced motion preference
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  function hasWebGL() {
    try {
      const canvas = document.createElement('canvas');
      return !!(window.WebGLRenderingContext && (canvas.getContext('webgl') || canvas.getContext('experimental-webgl')));
    } catch (e) {
      return false;
    }
  }

  const container = document.getElementById('ayurHero3DContainer');
  if (!container) return;

  const poster = container.querySelector('.ayur-hero-3d-poster');
  const canvas = document.getElementById('ayurHero3DCanvas');

  // If reduced motion or no WebGL, keep high-resolution poster visible
  if (prefersReducedMotion || !hasWebGL() || !canvas || typeof THREE === 'undefined') {
    if (poster) poster.classList.add('ayur-poster-active');
    return;
  }

  // Configuration & Asset URLs from data attributes or defaults
  const assetBase = container.dataset.assetBase || '/wp-content/themes/ayur-wellness/assets/images/hero/';
  const assets = {
    bottle: assetBase + 'authentic-amrit-bottle.webp',
    glass: assetBase + 'glass-pour.webp',
    arjuna: assetBase + 'ingredient-arjuna.webp',
    amla: assetBase + 'ingredient-amla.webp',
    garlic: assetBase + 'ingredient-garlic.webp',
    acv: assetBase + 'ingredient-acv.webp',
    honey: assetBase + 'ingredient-honey.webp'
  };

  let renderer, scene, camera;
  let isVisible = false;
  let animFrameId = null;
  let mouse = { x: 0, y: 0, targetX: 0, targetY: 0 };
  let isMobile = window.innerWidth < 768;

  // Scene Objects
  let bottleMesh, glassMesh;
  let streamMesh, rippleMesh, splashGroup;
  let ingredientMeshes = [];
  let particleSystem;
  let clock = new THREE.Clock();

  function initThree() {
    const width = container.clientWidth || 600;
    const height = container.clientHeight || 550;

    // Renderer
    renderer = new THREE.WebGLRenderer({
      canvas: canvas,
      alpha: true,
      antialias: true,
      powerPreference: 'high-performance'
    });
    const dpr = Math.min(window.devicePixelRatio || 1, isMobile ? 1.0 : 1.5);
    renderer.setPixelRatio(dpr);
    renderer.setSize(width, height);
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.05;

    // Scene
    scene = new THREE.Scene();

    // Camera (Field of View 42 for editorial cinematic feel)
    camera = new THREE.PerspectiveCamera(42, width / height, 0.1, 100);
    camera.position.set(0, 0, isMobile ? 10.5 : 8.5);

    // 1. Studio Lighting System
    // Ambient Warm Ivory Light
    const ambientLight = new THREE.AmbientLight(0xfff8ee, 0.85);
    scene.add(ambientLight);

    // Key Light: Golden Sunlight from top-left
    const keyLight = new THREE.DirectionalLight(0xfff6e5, 1.2);
    keyLight.position.set(-4, 6, 5);
    scene.add(keyLight);

    // Rim Light: Warm Muted Gold from rear-right
    const rimLight = new THREE.DirectionalLight(0xb79552, 0.9);
    rimLight.position.set(5, 3, -3);
    scene.add(rimLight);

    // Soft Botanical Green Floor Bounce
    const floorBounce = new THREE.DirectionalLight(0x315c45, 0.4);
    floorBounce.position.set(0, -5, 2);
    scene.add(floorBounce);

    // Load Textures
    const texLoader = new THREE.TextureLoader();
    let loadedCount = 0;
    const totalCount = 7;

    function onTexLoad() {
      loadedCount++;
      if (loadedCount === totalCount) {
        buildScene();
        // Fade in canvas smoothly once ready
        canvas.classList.add('ayur-canvas-ready');
        if (poster) poster.classList.add('ayur-poster-faded');
      }
    }

    const textures = {};
    for (let key in assets) {
      textures[key] = texLoader.load(assets[key], onTexLoad, undefined, () => {
        // Fallback on texture error: keep poster visible
        if (poster) poster.classList.add('ayur-poster-active');
      });
      textures[key].generateMipmaps = true;
      textures[key].minFilter = THREE.LinearMipmapLinearFilter;
    }

    function buildScene() {
      // 2. Authentic Amrit Juice Bottle Plane
      // Realistic proportions (aspect ratio of bottle is ~ 200 x 700 = 1 : 3.5)
      const bottleH = isMobile ? 4.8 : 5.4;
      const bottleW = bottleH * (210 / 720);
      const bottleGeo = new THREE.PlaneGeometry(bottleW, bottleH);
      const bottleMat = new THREE.MeshStandardMaterial({
        map: textures.bottle,
        transparent: true,
        roughness: 0.25,
        metalness: 0.15
      });
      bottleMesh = new THREE.Mesh(bottleGeo, bottleMat);
      
      // Position: upper-left, tilted gracefully toward the glass
      const bottleX = isMobile ? -1.1 : -1.8;
      const bottleY = isMobile ? 0.8 : 0.6;
      bottleMesh.position.set(bottleX, bottleY, 0.1);
      // Tilted ~15 degrees clockwise toward the glass
      bottleMesh.rotation.z = -15 * Math.PI / 180;
      scene.add(bottleMesh);

      // 3. Luxury Crystal Glass with Amber Herbal Juice
      const glassH = isMobile ? 2.8 : 3.2;
      const glassW = glassH; // square aspect ratio
      const glassGeo = new THREE.PlaneGeometry(glassW, glassH);
      const glassMat = new THREE.MeshStandardMaterial({
        map: textures.glass,
        transparent: true,
        roughness: 0.1,
        metalness: 0.1
      });
      glassMesh = new THREE.Mesh(glassGeo, glassMat);
      const glassX = isMobile ? 1.0 : 1.5;
      const glassY = isMobile ? -1.6 : -1.2;
      glassMesh.position.set(glassX, glassY, 0.2);
      scene.add(glassMesh);

      // 4. Flowing Golden Liquid Stream (Curve & Spline)
      // Connects from bottle neck to glass interior
      buildLiquidStream(bottleMesh, glassMesh);

      // 5. Controlled Botanical Vortex (5 Verified Ingredients)
      buildIngredientVortex(textures, glassMesh);

      // 6. Ambient Botanical Pollen / Sparkles
      buildPollenParticles();

      // Start animation loop
      clock.start();
      animate();
    }

    // Window Resize Handler
    window.addEventListener('resize', onResize, { passive: true });

    // Mouse Movement Parallax (Desktop)
    if (!isMobile) {
      container.addEventListener('mousemove', onMouseMove, { passive: true });
      container.addEventListener('mouseleave', () => {
        mouse.targetX = 0;
        mouse.targetY = 0;
      }, { passive: true });
    }
  }

  // 4. Liquid Stream Construction
  function buildLiquidStream(bottle, glass) {
    // Liquid Stream Spline Curve
    const pStart = new THREE.Vector3(bottle.position.x + 0.65, bottle.position.y + 2.1, 0.15);
    const pMid1 = new THREE.Vector3(bottle.position.x + 1.4, bottle.position.y + 1.8, 0.2);
    const pMid2 = new THREE.Vector3(glass.position.x - 0.2, glass.position.y + 1.1, 0.25);
    const pEnd = new THREE.Vector3(glass.position.x, glass.position.y + 0.35, 0.3);

    const curve = new THREE.CubicBezierCurve3(pStart, pMid1, pMid2, pEnd);
    const tubeGeo = new THREE.TubeGeometry(curve, 32, 0.058, 8, false);

    // Glowing translucent golden amber shader material
    const streamMat = new THREE.MeshStandardMaterial({
      color: 0xf59e0b,
      emissive: 0xb79552,
      emissiveIntensity: 0.45,
      roughness: 0.15,
      metalness: 0.2,
      transparent: true,
      opacity: 0.94
    });

    streamMesh = new THREE.Mesh(tubeGeo, streamMat);
    scene.add(streamMesh);

    // Ripple Disk inside glass
    const rippleGeo = new THREE.RingGeometry(0.04, 0.32, 32);
    const rippleMat = new THREE.MeshBasicMaterial({
      color: 0xfef08a,
      transparent: true,
      opacity: 0.7,
      side: THREE.DoubleSide
    });
    rippleMesh = new THREE.Mesh(rippleGeo, rippleMat);
    rippleMesh.position.set(pEnd.x, pEnd.y, pEnd.z + 0.05);
    rippleMesh.rotation.x = Math.PI * 0.4;
    scene.add(rippleMesh);

    // Splash Micro-Droplets
    splashGroup = new THREE.Group();
    const dropletGeo = new THREE.SphereGeometry(0.024, 8, 8);
    const dropletMat = new THREE.MeshStandardMaterial({
      color: 0xf59e0b,
      emissive: 0xfef08a,
      emissiveIntensity: 0.5,
      roughness: 0.1
    });

    for (let i = 0; i < 7; i++) {
      const drop = new THREE.Mesh(dropletGeo, dropletMat);
      drop.userData = {
        baseX: pEnd.x + (Math.random() - 0.5) * 0.3,
        baseY: pEnd.y + Math.random() * 0.1,
        speed: 1.5 + Math.random() * 1.5,
        height: 0.25 + Math.random() * 0.35,
        offset: i * 0.4
      };
      drop.position.set(drop.userData.baseX, drop.userData.baseY, pEnd.z);
      splashGroup.add(drop);
    }
    scene.add(splashGroup);
  }

  // 5. Controlled Botanical Vortex Construction
  function buildIngredientVortex(textures, glass) {
    const ingredientData = [
      { key: 'arjuna', name: 'Terminalia Arjuna (40%)', size: 1.4, radius: 2.5, speed: 0.35, yBase: 1.6, phase: 0 },
      { key: 'amla', name: 'Wild Indian Amla (30%)', size: 1.6, radius: 2.2, speed: 0.38, yBase: -0.6, phase: 1.25 },
      { key: 'garlic', name: 'Raw Allium Clove (10%)', size: 1.1, radius: 1.8, speed: 0.42, yBase: 2.2, phase: 2.5 },
      { key: 'honey', name: 'Pure Forest Honey (10%)', size: 1.3, radius: 2.0, speed: 0.32, yBase: 1.8, phase: 3.75 },
      { key: 'acv', name: 'Raw Apple Cider (10%)', size: 1.3, radius: 2.4, speed: 0.36, yBase: -0.2, phase: 5.0 }
    ];

    ingredientData.forEach((data) => {
      const geo = new THREE.PlaneGeometry(data.size, data.size);
      const mat = new THREE.MeshStandardMaterial({
        map: textures[data.key],
        transparent: true,
        roughness: 0.4,
        metalness: 0.05
      });
      const mesh = new THREE.Mesh(geo, mat);
      mesh.userData = {
        name: data.name,
        radius: isMobile ? data.radius * 0.75 : data.radius,
        speed: data.speed,
        yBase: data.yBase,
        phase: data.phase,
        orbitCenterX: isMobile ? 0.3 : 1.2,
        rotSpeed: 0.2 + Math.random() * 0.2
      };
      scene.add(mesh);
      ingredientMeshes.push(mesh);
    });
  }

  // 6. Ambient Botanical Pollen Particles
  function buildPollenParticles() {
    const pCount = isMobile ? 18 : 45;
    const geometry = new THREE.BufferGeometry();
    const positions = new Float32Array(pCount * 3);
    const opacities = new Float32Array(pCount);

    for (let i = 0; i < pCount; i++) {
      positions[i * 3] = (Math.random() - 0.5) * 8;
      positions[i * 3 + 1] = (Math.random() - 0.5) * 6;
      positions[i * 3 + 2] = (Math.random() - 0.5) * 4;
      opacities[i] = 0.3 + Math.random() * 0.7;
    }

    geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));

    // Particle Material
    const pMat = new THREE.PointsMaterial({
      color: 0xb79552,
      size: isMobile ? 0.08 : 0.065,
      transparent: true,
      opacity: 0.65,
      blending: THREE.AdditiveBlending
    });

    particleSystem = new THREE.Points(geometry, pMat);
    scene.add(particleSystem);
  }

  function onMouseMove(e) {
    const rect = container.getBoundingClientRect();
    const x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
    const y = -(((e.clientY - rect.top) / rect.height) * 2 - 1);
    // Subtle mouse parallax damping (8–12px max shift)
    mouse.targetX = x * 0.35;
    mouse.targetY = y * 0.25;
  }

  function onResize() {
    if (!renderer || !camera) return;
    const width = container.clientWidth || 600;
    const height = container.clientHeight || 550;
    isMobile = window.innerWidth < 768;

    camera.aspect = width / height;
    camera.position.z = isMobile ? 10.5 : 8.5;
    camera.updateProjectionMatrix();

    renderer.setSize(width, height);
  }

  // Main Render Loop
  function animate() {
    if (!isVisible) return;
    animFrameId = requestAnimationFrame(animate);

    const time = clock.getElapsedTime();

    // Smooth Mouse Camera Parallax (Lerp)
    mouse.x += (mouse.targetX - mouse.x) * 0.06;
    mouse.y += (mouse.targetY - mouse.y) * 0.06;
    camera.position.x = mouse.x;
    camera.position.y = mouse.y;
    camera.lookAt(0, 0, 0);

    // 1. Subtle Bottle Float & Natural Restrained Pouring Angle
    if (bottleMesh) {
      bottleMesh.position.y = (isMobile ? 0.8 : 0.6) + Math.sin(time * 1.4) * 0.04;
      bottleMesh.rotation.z = (-15 * Math.PI / 180) + Math.sin(time * 0.9) * 0.015;
    }

    // 2. Liquid Stream Viscosity & Ripple Animation
    if (streamMesh) {
      streamMesh.scale.x = 1.0 + Math.sin(time * 8.0) * 0.04;
      streamMesh.scale.z = 1.0 + Math.cos(time * 8.0) * 0.04;
    }

    if (rippleMesh) {
      const rippleT = (time * 2.2) % 1;
      const scale = 0.6 + rippleT * 0.9;
      rippleMesh.scale.set(scale, scale, 1);
      rippleMesh.material.opacity = (1 - rippleT) * 0.65;
    }

    if (splashGroup) {
      splashGroup.children.forEach(drop => {
        const d = drop.userData;
        const progress = ((time * d.speed) + d.offset) % 1;
        // Parabolic arc for micro-droplets
        drop.position.y = d.baseY + Math.sin(progress * Math.PI) * d.height;
        drop.material.opacity = (1 - progress) * 0.85;
      });
    }

    // 3. Controlled Botanical Ingredient Vortex Motion
    ingredientMeshes.forEach(mesh => {
      const d = mesh.userData;
      const angle = time * d.speed + d.phase;
      // Elliptical orbit with vertical breathing
      mesh.position.x = d.orbitCenterX + Math.cos(angle) * d.radius;
      mesh.position.z = Math.sin(angle) * (d.radius * 0.7);
      mesh.position.y = d.yBase + Math.sin(time * 1.1 + d.phase) * 0.18;

      // Subtle slow axial rotation
      mesh.rotation.z = Math.sin(time * d.rotSpeed + d.phase) * 0.15;

      // Depth perception: scale slightly based on Z-depth
      const depthScale = 1.0 + mesh.position.z * 0.12;
      mesh.scale.set(depthScale, depthScale, 1);
    });

    // 4. Ambient Botanical Pollen Particle Drift
    if (particleSystem) {
      particleSystem.rotation.y = time * 0.03;
      particleSystem.position.y = Math.sin(time * 0.4) * 0.15;
    }

    renderer.render(scene, camera);
  }

  // IntersectionObserver: Pause rendering when offscreen for 60fps & 0 GPU waste
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      isVisible = entry.isIntersecting;
      if (isVisible) {
        if (!renderer) {
          initThree();
        } else {
          clock.start();
          animate();
        }
      } else {
        if (animFrameId) {
          cancelAnimationFrame(animFrameId);
          animFrameId = null;
        }
      }
    });
  }, { threshold: 0.1 });

  observer.observe(container);

})();
