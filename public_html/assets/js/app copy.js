

var registros = [];


window.onload = function () {
  var hoy = fechaStr(new Date());
  document.getElementById('fechaVisita').value = hoy;

  actualizarReloj();
  setInterval(actualizarReloj, 1000);

  cargarRegistros();
};



function fechaStr(d) {
  return d.getFullYear() + '-' +
    String(d.getMonth() + 1).padStart(2, '0') + '-' +
    String(d.getDate()).padStart(2, '0');
}

function actualizarReloj() {
  var n = new Date();
  document.getElementById('fechaHoy').textContent =
    n.toLocaleDateString('es-PE', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });

  document.getElementById('horaActual').textContent =
    n.toLocaleTimeString('es-PE');
}

function showToast(msg, type) {
  var t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'show ' + (type || 'ok');
  setTimeout(function () {
    t.classList.remove('show');
  }, 3000);
}

// ==============================
// CARGAR REGISTROS (GET)
// ==============================

async function cargarRegistros() {
  try {
    const res = await fetch('/api/visitas.php');

    const data = await res.json();  

    registros = data;      

    renderTabla();

  } catch (error) {
    console.error("Error cargando registros:", error);
    showToast('Error cargando registros', 'err');
  }
}

// ==============================
// GUARDAR REGISTRO (POST)
// ==============================

function guardarRegistro() {

  var agente = document.getElementById('agenteSelect').value === 'OTRO'
    ? document.getElementById('agenteOtro').value.trim()
    : document.getElementById('agenteSelect').value;

  var fecha = document.getElementById('fechaVisita').value;
  var horaInicio = document.getElementById('horaInicio').value;
  var horaFin = document.getElementById('horaFin').value;
  var zona = document.getElementById('zona').value;
  var motivo = document.getElementById('motivoVisita').value;
  var observaciones = document.getElementById('observaciones').value.trim();

  if (!agente || !fecha || !horaInicio || !horaFin || !zona || !motivo || !observaciones) {
    showToast('⚠️ Complete todos los campos obligatorios', 'err');
    return;
  }

  var data = {
    agente: agente,
    cargo: document.getElementById('cargo').value,
    fecha: fecha,
    horaInicio: horaInicio,
    horaFin: horaFin,
    duracion: document.getElementById('duracion').value,
    zona: zona,
    tipoZona: document.getElementById('tipoZona').value,
    provincia: document.getElementById('provincia').value,
    distrito: document.getElementById('distrito').value,
    motivo: motivo,
    estado: document.getElementById('estadoProceso').value,
    observaciones: observaciones,
    acciones: document.getElementById('acciones').value,
    nContactos: 0,
    nombresContactos: ''
  };

  fetch('/api/visitas.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  })
    .then(res => res.json())
    .then(() => {
      showToast('✅ Registro guardado', 'ok');
      limpiarFormulario();
      cargarRegistros();
    })
    .catch(() => {
      showToast('Error al guardar', 'err');
    });
}

// ==============================
// ELIMINAR REGISTRO (DELETE)
// ==============================

function eliminarRegistro(id) {
  if (!confirm('¿Eliminar este registro?')) return;

  fetch('/api/visitas.php?id=' + id, {
    method: 'DELETE'
  })
    .then(res => res.json())
    .then(() => {
      showToast('Registro eliminado', 'info');
      cargarRegistros();
    })
    .catch(() => {
      showToast('Error al eliminar', 'err');
    });
}

// ==============================
// RENDER TABLA
// ==============================

function renderTabla() {

  var tbody = document.getElementById('tablaBody');
  tbody.innerHTML = '';

  if (!registros.length) return;

  registros.sort((a, b) => b.fecha.localeCompare(a.fecha));

  registros.forEach((r, i) => {

    var tr = document.createElement('tr');

    tr.innerHTML = `
      <td>${i + 1}</td>
      <td>${r.fecha}</td>
      <td>${r.agente}</td>
      <td>${r.zona}</td>
      <td>${r.motivo}</td>
      <td>${r.estado || ''}</td>
      <td>
        <button onclick="eliminarRegistro(${r.id})" class="act-btn">✕</button>
      </td>
    `;

    tbody.appendChild(tr);
  });
}

// ==============================
// LIMPIAR FORMULARIO
// ==============================

function limpiarFormulario() {
  document.querySelectorAll('input, textarea, select').forEach(el => {
    if (el.type !== 'date') el.value = '';
  });

  document.getElementById('fechaVisita').value = fechaStr(new Date());
}

// ==============================
// TABS
// ==============================

function switchTab(tab, btn) {
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));

  document.getElementById('sec-' + tab).classList.add('active');
  btn.classList.add('active');

  if (tab === 'bitacora') cargarRegistros();
}