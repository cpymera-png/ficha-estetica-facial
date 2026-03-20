/* ========================================
   FICHA CLÍNICA — App Logic
   Signature pads, photo uploads, save/reset
   ======================================== */

(function () {
  'use strict';

  // ── SIGNATURE PADS ──────────────────────────
  class SignaturePad {
    constructor(canvas) {
      this.canvas = canvas;
      this.ctx = canvas.getContext('2d');
      this.drawing = false;
      this.paths = [];
      this.currentPath = [];

      this.resize();
      this.bindEvents();
    }

    resize() {
      const rect = this.canvas.getBoundingClientRect();
      const dpr = window.devicePixelRatio || 1;
      this.canvas.width = rect.width * dpr;
      this.canvas.height = rect.height * dpr;
      this.ctx.scale(dpr, dpr);
      this.ctx.lineWidth = 2.5;
      this.ctx.lineCap = 'round';
      this.ctx.lineJoin = 'round';
      this.ctx.strokeStyle = '#3d2e26';
      this.redraw();
    }

    bindEvents() {
      // Pointer events (works for touch + mouse + stylus)
      this.canvas.addEventListener('pointerdown', (e) => this.start(e));
      this.canvas.addEventListener('pointermove', (e) => this.move(e));
      this.canvas.addEventListener('pointerup', () => this.end());
      this.canvas.addEventListener('pointerleave', () => this.end());
      this.canvas.addEventListener('pointercancel', () => this.end());

      // Prevent scroll while signing
      this.canvas.addEventListener('touchstart', (e) => {
        if (this.drawing) e.preventDefault();
      }, { passive: false });
      this.canvas.addEventListener('touchmove', (e) => {
        if (this.drawing) e.preventDefault();
      }, { passive: false });

      window.addEventListener('resize', () => this.resize());
    }

    getPoint(e) {
      const rect = this.canvas.getBoundingClientRect();
      return {
        x: e.clientX - rect.left,
        y: e.clientY - rect.top
      };
    }

    start(e) {
      this.drawing = true;
      this.canvas.classList.add('signing');
      this.currentPath = [this.getPoint(e)];
      this.ctx.beginPath();
      const p = this.getPoint(e);
      this.ctx.moveTo(p.x, p.y);
    }

    move(e) {
      if (!this.drawing) return;
      const p = this.getPoint(e);
      this.currentPath.push(p);
      this.ctx.lineTo(p.x, p.y);
      this.ctx.stroke();
      this.ctx.beginPath();
      this.ctx.moveTo(p.x, p.y);
    }

    end() {
      if (!this.drawing) return;
      this.drawing = false;
      this.canvas.classList.remove('signing');
      if (this.currentPath.length > 0) {
        this.paths.push([...this.currentPath]);
      }
      this.currentPath = [];
    }

    clear() {
      this.paths = [];
      this.currentPath = [];
      const rect = this.canvas.getBoundingClientRect();
      this.ctx.clearRect(0, 0, rect.width, rect.height);
    }

    redraw() {
      const rect = this.canvas.getBoundingClientRect();
      this.ctx.clearRect(0, 0, rect.width, rect.height);
      this.paths.forEach(path => {
        if (path.length < 2) return;
        this.ctx.beginPath();
        this.ctx.moveTo(path[0].x, path[0].y);
        for (let i = 1; i < path.length; i++) {
          this.ctx.lineTo(path[i].x, path[i].y);
        }
        this.ctx.stroke();
      });
    }

    isEmpty() {
      return this.paths.length === 0;
    }

    toDataURL() {
      return this.canvas.toDataURL('image/png');
    }
  }

  // Initialize signature pads
  const sigPaciente = new SignaturePad(document.getElementById('signaturePaciente'));
  const sigCosmiatra = new SignaturePad(document.getElementById('signatureCosmiatra'));

  // Clear buttons
  document.querySelectorAll('.btn-clear-sig').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = btn.getAttribute('data-target');
      if (target === 'signaturePaciente') sigPaciente.clear();
      if (target === 'signatureCosmiatra') sigCosmiatra.clear();
    });
  });


  // ── PHOTO UPLOADS ──────────────────────────
  function setupPhotoUpload(dropId, inputId, previewId, placeholderId) {
    const drop = document.getElementById(dropId);
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    const placeholder = document.getElementById(placeholderId);

    drop.addEventListener('click', () => input.click());

    input.addEventListener('change', () => {
      const file = input.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = (e) => {
        preview.src = e.target.result;
        drop.classList.add('has-image');
      };
      reader.readAsDataURL(file);
    });
  }

  setupPhotoUpload('dropBefore', 'inputBefore', 'previewBefore', 'placeholderBefore');
  setupPhotoUpload('dropAfter', 'inputAfter', 'previewAfter', 'placeholderAfter');


  // ── TOAST NOTIFICATION ──────────────────────
  function showToast(message) {
    let toast = document.querySelector('.toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.className = 'toast';
      document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
  }


  // ── SAVE FORM ──────────────────────────────
  document.getElementById('btnSave').addEventListener('click', async () => {
    const form = document.getElementById('fichaForm');
    const formData = new FormData(form);
    const data = {};

    for (const [key, value] of formData.entries()) {
      if (data[key]) {
        if (Array.isArray(data[key])) {
          data[key].push(value);
        } else {
          data[key] = [data[key], value];
        }
      } else {
        data[key] = value;
      }
    }

    // Add signatures
    if (!sigPaciente.isEmpty()) {
      data.firma_paciente = sigPaciente.toDataURL();
    }
    if (!sigCosmiatra.isEmpty()) {
      data.firma_cosmiatra = sigCosmiatra.toDataURL();
    }

    // Add photos
    const previewBefore = document.getElementById('previewBefore');
    const previewAfter = document.getElementById('previewAfter');
    if (previewBefore.src && previewBefore.src !== window.location.href) {
      data.foto_antes = previewBefore.src;
    }
    if (previewAfter.src && previewAfter.src !== window.location.href) {
      data.foto_despues = previewAfter.src;
    }

    // Send to server
    const btnSave = document.getElementById('btnSave');
    btnSave.disabled = true;
    btnSave.textContent = 'Guardando...';

    try {
      const response = await fetch('./api/guardar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });

      const result = await response.json();

      if (result.success) {
        showToast('Ficha #' + result.id + ' guardada correctamente');
      } else {
        showToast('Error: ' + (result.error || 'No se pudo guardar'));
      }
    } catch (err) {
      // Fallback: descargar como JSON si el servidor no está disponible
      console.warn('Servidor no disponible, descargando JSON:', err);
      const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      const nombre = data.apellido_nombre || 'paciente';
      const fecha = new Date().toISOString().split('T')[0];
      a.download = `ficha-${nombre.replace(/\s+/g, '-').toLowerCase()}-${fecha}.json`;
      a.click();
      URL.revokeObjectURL(url);
      showToast('Servidor no disponible — ficha descargada como archivo');
    } finally {
      btnSave.disabled = false;
      btnSave.textContent = 'Guardar ficha';
    }
  });


  // ── RESET FORM ─────────────────────────────
  document.getElementById('btnReset').addEventListener('click', () => {
    if (confirm('¿Está seguro de que desea limpiar todo el formulario?')) {
      document.getElementById('fichaForm').reset();
      sigPaciente.clear();
      sigCosmiatra.clear();

      // Clear photo previews
      ['dropBefore', 'dropAfter'].forEach(id => {
        document.getElementById(id).classList.remove('has-image');
      });
      document.getElementById('previewBefore').src = '';
      document.getElementById('previewAfter').src = '';

      showToast('Formulario limpiado');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  });


  // ── AUTO-CALCULATE AGE ─────────────────────
  document.getElementById('fecha_nacimiento').addEventListener('change', function () {
    const birthDate = new Date(this.value);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
      age--;
    }
    if (age >= 0 && age < 150) {
      document.getElementById('edad').value = age;
    }
  });

})();
