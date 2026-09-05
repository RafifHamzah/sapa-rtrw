import * as THREE from 'three';
import { RoundedBoxGeometry } from 'three/addons/geometries/RoundedBoxGeometry.js';

/**
 * Pilah Sampah 3D — mini-game edukasi (Three.js).
 * Seret item sampah ke tempat sampah 3D yang benar (organik / anorganik / B3).
 */

const POOL = [
    { emoji: '🍌', name: 'Kulit pisang', cat: 'organik' },
    { emoji: '🍎', name: 'Sisa buah', cat: 'organik' },
    { emoji: '🍂', name: 'Daun kering', cat: 'organik' },
    { emoji: '🥚', name: 'Cangkang telur', cat: 'organik' },
    { emoji: '🥤', name: 'Botol plastik', cat: 'anorganik' },
    { emoji: '🥫', name: 'Kaleng bekas', cat: 'anorganik' },
    { emoji: '📰', name: 'Koran bekas', cat: 'anorganik' },
    { emoji: '🛍️', name: 'Kantong plastik', cat: 'anorganik' },
    { emoji: '🔋', name: 'Baterai bekas', cat: 'b3' },
    { emoji: '💡', name: 'Lampu bekas', cat: 'b3' },
    { emoji: '💊', name: 'Obat kadaluarsa', cat: 'b3' },
    { emoji: '🌡️', name: 'Termometer', cat: 'b3' },
];

const BINS = [
    { key: 'organik', label: 'Organik', emoji: '🌿', color: 0x10b981, x: -3.1 },
    { key: 'anorganik', label: 'Anorganik', emoji: '♻️', color: 0x0ea5e9, x: 0 },
    { key: 'b3', label: 'B3', emoji: '☣️', color: 0xef4444, x: 3.1 },
];

const ROUND = 9;
const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function canvasTexture(draw, w = 256, h = 256) {
    const c = document.createElement('canvas');
    c.width = w; c.height = h;
    draw(c.getContext('2d'), w, h);
    const t = new THREE.CanvasTexture(c);
    t.anisotropy = 4;
    t.needsUpdate = true;
    return t;
}

function emojiTexture(emoji) {
    return canvasTexture((ctx, w, h) => {
        ctx.font = `${w * 0.66}px "Apple Color Emoji","Segoe UI Emoji",serif`;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(emoji, w / 2, h / 2 + h * 0.03);
    });
}

function shadowTexture() {
    return canvasTexture((ctx, w, h) => {
        const g = ctx.createRadialGradient(w / 2, h / 2, 0, w / 2, h / 2, w / 2);
        g.addColorStop(0, 'rgba(15,61,51,0.35)');
        g.addColorStop(1, 'rgba(15,61,51,0)');
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, w, h);
    });
}

function labelTexture(emoji, text, hex) {
    return canvasTexture((ctx, w, h) => {
        ctx.fillStyle = hex;
        const r = 40;
        ctx.beginPath();
        ctx.roundRect(8, 8, w - 16, h - 16, r);
        ctx.fill();
        ctx.font = `${h * 0.42}px serif`;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(emoji, w * 0.24, h / 2);
        ctx.fillStyle = '#ffffff';
        ctx.font = `700 ${h * 0.34}px "Plus Jakarta Sans",system-ui,sans-serif`;
        ctx.textAlign = 'left';
        ctx.fillText(text, w * 0.40, h / 2 + 2);
    }, 512, 160);
}

function hexCss(hex) {
    return '#' + hex.toString(16).padStart(6, '0');
}

class Game {
    constructor(root) {
        this.root = root;
        this.canvasHost = root.querySelector('#ps-canvas');
        this.completeUrl = root.dataset.completeUrl;
        this.els = {
            correct: root.querySelector('#ps-correct'),
            progressText: root.querySelector('#ps-progress-text'),
            progressBar: root.querySelector('#ps-progress-bar'),
            hint: root.querySelector('#ps-hint'),
            results: root.querySelector('#ps-results'),
            resultsEmoji: root.querySelector('#ps-results-emoji'),
            resultsTitle: root.querySelector('#ps-results-title'),
            resultsSub: root.querySelector('#ps-results-sub'),
            xp: root.querySelector('#ps-xp'),
            restart: root.querySelector('#ps-restart'),
        };

        this.raycaster = new THREE.Raycaster();
        this.pointer = new THREE.Vector2();
        this.dragPlane = new THREE.Plane(new THREE.Vector3(0, 1, 0), -1.1);
        this.dragging = false;
        this.busy = false;
        this.bins = [];

        this.initScene();
        this.bindEvents();
        this.start();
        this.animate();
    }

    initScene() {
        const width = this.canvasHost.clientWidth;
        const height = this.canvasHost.clientHeight;

        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0xecfdf5);

        this.camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 100);
        this.camera.position.set(0, 5.2, 8.4);
        this.camera.lookAt(0, 0.8, -0.5);

        this.renderer = new THREE.WebGLRenderer({ antialias: true });
        this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        this.renderer.setSize(width, height);
        this.canvasHost.appendChild(this.renderer.domElement);

        this.scene.add(new THREE.HemisphereLight(0xffffff, 0xbfe6d4, 1.05));
        const dir = new THREE.DirectionalLight(0xffffff, 1.1);
        dir.position.set(4, 8, 6);
        this.scene.add(dir);

        // Ground disc
        const ground = new THREE.Mesh(
            new THREE.CircleGeometry(9, 64),
            new THREE.MeshStandardMaterial({ color: 0xd1fae5, roughness: 1 }),
        );
        ground.rotation.x = -Math.PI / 2;
        ground.position.y = -0.02;
        this.scene.add(ground);

        // Bins
        const binGeo = new RoundedBoxGeometry(1.7, 1.5, 1.7, 4, 0.18);
        const shadowTex = shadowTexture();
        for (const b of BINS) {
            const mat = new THREE.MeshStandardMaterial({ color: b.color, roughness: 0.55, metalness: 0.05 });
            const mesh = new THREE.Mesh(binGeo, mat);
            mesh.position.set(b.x, 0.75, -2.1);
            this.scene.add(mesh);

            // rim
            const rim = new THREE.Mesh(
                new THREE.TorusGeometry(0.72, 0.09, 12, 32),
                new THREE.MeshStandardMaterial({ color: 0xffffff, roughness: 0.4, transparent: true, opacity: 0.85 }),
            );
            rim.rotation.x = -Math.PI / 2;
            rim.position.set(b.x, 1.52, -2.1);
            this.scene.add(rim);

            // label sprite
            const label = new THREE.Sprite(new THREE.SpriteMaterial({ map: labelTexture(b.emoji, b.label, hexCss(b.color)), transparent: true }));
            label.scale.set(2.3, 0.72, 1);
            label.position.set(b.x, 2.35, -2.1);
            this.scene.add(label);

            // ground shadow
            const sh = new THREE.Mesh(new THREE.PlaneGeometry(2.4, 2.4), new THREE.MeshBasicMaterial({ map: shadowTex, transparent: true, depthWrite: false }));
            sh.rotation.x = -Math.PI / 2;
            sh.position.set(b.x, 0.01, -2.1);
            this.scene.add(sh);

            this.bins.push({ ...b, mesh, rim, label, basePos: mesh.position.clone() });
        }

        // Item container + its shadow
        this.itemShadow = new THREE.Mesh(new THREE.PlaneGeometry(2, 2), new THREE.MeshBasicMaterial({ map: shadowTex, transparent: true, depthWrite: false }));
        this.itemShadow.rotation.x = -Math.PI / 2;
        this.itemShadow.position.y = 0.02;
        this.scene.add(this.itemShadow);

        this.item = new THREE.Sprite(new THREE.SpriteMaterial({ transparent: true }));
        this.item.scale.set(1.7, 1.7, 1);
        this.item.visible = false;
        this.scene.add(this.item);
        this.homePos = new THREE.Vector3(0, 1.4, 1.9);
    }

    bindEvents() {
        const dom = this.renderer.domElement;
        dom.style.touchAction = 'none';
        dom.addEventListener('pointerdown', (e) => this.onDown(e));
        window.addEventListener('pointermove', (e) => this.onMove(e));
        window.addEventListener('pointerup', () => this.onUp());
        window.addEventListener('resize', () => this.onResize());
        this.els.restart?.addEventListener('click', () => this.start());
    }

    setPointer(e) {
        const r = this.renderer.domElement.getBoundingClientRect();
        this.pointer.x = ((e.clientX - r.left) / r.width) * 2 - 1;
        this.pointer.y = -((e.clientY - r.top) / r.height) * 2 + 1;
    }

    onDown(e) {
        if (this.busy || !this.item.visible) return;
        this.setPointer(e);
        this.raycaster.setFromCamera(this.pointer, this.camera);
        if (this.raycaster.intersectObject(this.item).length) {
            this.dragging = true;
            this.item.scale.set(1.95, 1.95, 1);
        }
    }

    onMove(e) {
        if (!this.dragging) return;
        this.setPointer(e);
        this.raycaster.setFromCamera(this.pointer, this.camera);
        const hit = new THREE.Vector3();
        if (this.raycaster.ray.intersectPlane(this.dragPlane, hit)) {
            hit.x = THREE.MathUtils.clamp(hit.x, -4.4, 4.4);
            hit.z = THREE.MathUtils.clamp(hit.z, -2.4, 2.6);
            this.item.position.set(hit.x, 1.4, hit.z);
        }
    }

    onUp() {
        if (!this.dragging) return;
        this.dragging = false;
        this.item.scale.set(1.7, 1.7, 1);

        let target = null;
        let best = 2.0;
        for (const b of this.bins) {
            const d = Math.hypot(this.item.position.x - b.mesh.position.x, this.item.position.z - b.mesh.position.z);
            if (d < best) { best = d; target = b; }
        }

        if (target) this.evaluate(target);
        else this.tween(this.item.position, this.homePos.clone(), 260);
    }

    evaluate(bin) {
        this.busy = true;
        const correct = bin.key === this.current.cat;
        if (correct) {
            this.correct++;
            this.updateHud();
            this.pulse(bin, 0x10b981);
            this.tween(this.item.position, new THREE.Vector3(bin.mesh.position.x, 1.9, bin.mesh.position.z), 220, () => {
                this.tween(this.item.scale, new THREE.Vector3(0.01, 0.01, 1), 200, () => this.next());
            });
        } else {
            const right = this.bins.find((b) => b.key === this.current.cat);
            this.pulse(right, 0x10b981);
            this.flashHint(`Kurang tepat — ini masuk <b>${right.label}</b>`, 'wrong');
            this.shake(this.item, () => {
                this.tween(this.item.position, this.homePos.clone(), 240, () => this.next());
            });
        }
    }

    next() {
        this.index++;
        this.item.scale.set(1.7, 1.7, 1);
        this.busy = false;
        if (this.index >= this.queue.length) this.finish();
        else this.spawn();
    }

    spawn() {
        this.current = this.queue[this.index];
        this.item.material.map = emojiTexture(this.current.emoji);
        this.item.material.needsUpdate = true;
        this.item.position.copy(this.homePos);
        this.item.visible = true;
        this.updateHud();
        this.flashHint('Seret sampah ke tempat yang benar', 'idle');
    }

    updateHud() {
        this.els.correct.textContent = this.correct;
        this.els.progressText.textContent = `${Math.min(this.index + 1, ROUND)} / ${ROUND}`;
        this.els.progressBar.style.width = `${(this.index / ROUND) * 100}%`;
    }

    flashHint(html, type) {
        if (!this.els.hint) return;
        this.els.hint.innerHTML = html;
        this.els.hint.className = 'text-sm font-medium text-center transition-colors ' +
            (type === 'wrong' ? 'text-red-600' : 'text-slate-500');
    }

    start() {
        this.queue = [...POOL].sort(() => Math.random() - 0.5).slice(0, ROUND);
        this.index = 0;
        this.correct = 0;
        this.busy = false;
        this.els.results.classList.add('hidden');
        this.root.querySelector('#ps-stage').classList.remove('hidden');
        this.spawn();
    }

    async finish() {
        this.item.visible = false;
        const pass = this.correct >= Math.ceil(ROUND * 0.6);
        this.root.querySelector('#ps-stage').classList.add('hidden');
        this.els.resultsEmoji.textContent = pass ? '🏆' : '💪';
        this.els.resultsTitle.textContent = pass ? 'Hebat!' : 'Terus Berlatih!';
        this.els.resultsSub.textContent = `Kamu memilah ${this.correct} dari ${ROUND} sampah dengan benar.`;
        this.els.results.classList.remove('hidden');
        if (pass && window.confettiBurst) window.confettiBurst();

        try {
            const res = await fetch(this.completeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({ correct: this.correct, total: ROUND }),
            });
            const d = await res.json();
            this.els.xp.textContent = d.message;
            this.els.xp.classList.toggle('hidden', !d.message);
            if (d.awarded && window.toast) window.toast(d.message, 'success');
        } catch (e) { /* offline */ }
    }

    // --- small animation helpers ---
    tween(vec, to, ms, done) {
        const from = vec.clone();
        const t0 = performance.now();
        const step = () => {
            const k = reduced ? 1 : Math.min(1, (performance.now() - t0) / ms);
            const e = 1 - Math.pow(1 - k, 3);
            vec.lerpVectors(from, to, e);
            if (k < 1) requestAnimationFrame(step);
            else done && done();
        };
        step();
    }

    shake(obj, done) {
        const x0 = this.homePos.x;
        const t0 = performance.now();
        const step = () => {
            const k = Math.min(1, (performance.now() - t0) / 380);
            obj.position.x = x0 + Math.sin(k * Math.PI * 6) * 0.35 * (1 - k);
            if (k < 1) requestAnimationFrame(step);
            else done && done();
        };
        if (reduced) done && done(); else step();
    }

    pulse(bin, color) {
        if (!bin) return;
        const t0 = performance.now();
        const base = bin.mesh.material.emissive?.clone() ?? new THREE.Color(0);
        bin.mesh.material.emissive = new THREE.Color(color);
        const step = () => {
            const k = Math.min(1, (performance.now() - t0) / 500);
            bin.mesh.material.emissiveIntensity = (1 - k) * 0.6;
            const s = 1 + Math.sin(k * Math.PI) * 0.08;
            bin.mesh.scale.setScalar(s);
            if (k < 1) requestAnimationFrame(step);
            else { bin.mesh.scale.setScalar(1); bin.mesh.material.emissive = base; bin.mesh.material.emissiveIntensity = 0; }
        };
        step();
    }

    onResize() {
        const w = this.canvasHost.clientWidth;
        const h = this.canvasHost.clientHeight;
        if (!w || !h) return;
        this.camera.aspect = w / h;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(w, h);
    }

    animate() {
        const loop = (t) => {
            requestAnimationFrame(loop);
            const sec = t / 1000;
            if (this.item.visible && !this.dragging && !this.busy) {
                this.item.position.y = 1.4 + (reduced ? 0 : Math.sin(sec * 2) * 0.12);
            }
            this.itemShadow.position.set(this.item.position.x, 0.02, this.item.position.z);
            const near = 1.4;
            this.itemShadow.material.opacity = this.item.visible ? THREE.MathUtils.clamp(1 - (this.item.position.y - near) * 0.6, 0.25, 0.8) : 0;
            if (!reduced) this.camera.position.x = Math.sin(sec * 0.25) * 0.5;
            this.camera.lookAt(0, 0.8, -0.5);
            this.renderer.render(this.scene, this.camera);
        };
        requestAnimationFrame(loop);
    }
}

const root = document.getElementById('ps-game');
if (root) {
    try {
        new Game(root);
    } catch (e) {
        console.error('Pilah Sampah 3D gagal dimuat:', e);
        const fb = root.querySelector('#ps-fallback');
        if (fb) fb.classList.remove('hidden');
    }
}
