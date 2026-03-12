<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Control de Gastos & Trazabilidad - Bitácora de Campo</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<style>
  :root {
    --primary: #1a3c5e; --primary-light: #2a5c8e; --accent: #e8a020; --accent-light: #f0b840;
    --success: #1a7a4a; --danger: #c0392b; --bg: #f0f3f7; --card: #ffffff;
    --text: #1a2533; --text-muted: #6b7a8d; --border: #d1dbe8;
    --shadow: 0 2px 12px rgba(26,60,94,0.10); --radius: 12px;
  }
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:'Segoe UI',system-ui,sans-serif; background:var(--bg); color:var(--text); min-height:100vh; }
  header { background:linear-gradient(135deg,var(--primary) 0%,var(--primary-light) 100%); color:white; padding:0; box-shadow:0 4px 20px rgba(0,0,0,0.2); position:sticky; top:0; z-index:100; }
  .header-inner { display:flex; align-items:center; justify-content:space-between; padding:14px 24px; }
  .header-logo { display:flex; align-items:center; gap:14px; }
  .header-logo .icon-mine { width:44px; height:44px; background:var(--accent); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:22px; }
  .header-logo h1 { font-size:1.1rem; font-weight:700; line-height:1.2; }
  .header-logo p { font-size:0.72rem; opacity:0.75; }
  .header-logo .area-tag { font-size:0.68rem; opacity:0.6; margin-top:2px; }
  .header-date { text-align:right; font-size:0.78rem; opacity:0.85; line-height:1.6; }
  .header-date strong { font-size:0.92rem; display:block; }
  .tabs { background:var(--primary); display:flex; border-top:1px solid rgba(255,255,255,0.1); overflow-x:auto; }
  .tab-btn { padding:11px 22px; background:none; border:none; color:rgba(255,255,255,0.65); cursor:pointer; font-size:0.82rem; font-weight:600; white-space:nowrap; border-bottom:3px solid transparent; transition:all 0.2s; }
  .tab-btn:hover { color:white; background:rgba(255,255,255,0.08); }
  .tab-btn.active { color:var(--accent-light); border-bottom-color:var(--accent-light); background:rgba(255,255,255,0.05); }
  main { padding:24px; max-width:1100px; margin:0 auto; }
  .section { display:none; }
  .section.active { display:block; }
  .card { background:var(--card); border-radius:var(--radius); box-shadow:var(--shadow); padding:22px; margin-bottom:20px; border:1px solid var(--border); }
  .card-title { font-size:1rem; font-weight:700; color:var(--primary); margin-bottom:16px; display:flex; align-items:center; gap:8px; padding-bottom:12px; border-bottom:2px solid var(--bg); flex-wrap:wrap; }
  .card-title .badge { background:var(--accent); color:white; padding:2px 10px; border-radius:20px; font-size:0.72rem; }
  .card-divider { border:none; border-top:2px dashed var(--border); margin:20px 0; }
  .subsection-label { font-size:0.78rem; font-weight:800; color:var(--primary-light); text-transform:uppercase; letter-spacing:0.8px; margin-bottom:12px; display:flex; align-items:center; gap:8px; }
  .subsection-label::after { content:''; flex:1; height:1px; background:var(--border); }
  .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
  .form-grid.three { grid-template-columns:1fr 1fr 1fr; }
  @media(max-width:640px){ .form-grid,.form-grid.three { grid-template-columns:1fr; } }
  .form-group { display:flex; flex-direction:column; gap:5px; }
  .form-group.span2 { grid-column:span 2; }
  @media(max-width:640px){ .form-group.span2 { grid-column:span 1; } }
  label { font-size:0.78rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.4px; }
  label span.req { color:var(--danger); }
  input[type="text"],input[type="date"],input[type="time"],select,textarea { padding:9px 13px; border:1.5px solid var(--border); border-radius:8px; font-size:0.88rem; color:var(--text); background:#fafbfc; transition:border-color 0.2s,box-shadow 0.2s; font-family:inherit; width:100%; }
  input:focus,select:focus,textarea:focus { outline:none; border-color:var(--primary-light); box-shadow:0 0 0 3px rgba(42,92,142,0.12); background:white; }
  textarea { resize:vertical; min-height:80px; }
  input[readonly] { background:#f0f3f7; color:var(--text-muted); cursor:not-allowed; }
  input[type="text"]::placeholder,textarea::placeholder { color:#b0bcc8; }
  .gps-block { background:linear-gradient(135deg,#eef5ff,#e8f0fe); border:1.5px solid #c5d8f5; border-radius:10px; padding:14px 16px; display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
  .gps-coords { flex:1; min-width:200px; }
  .gps-coords p { font-size:0.78rem; color:var(--text-muted); margin-bottom:4px; }
  .gps-coords strong { font-size:0.95rem; color:var(--primary); font-family:monospace; }
  .btn-gps { padding:9px 16px; background:var(--primary); color:white; border:none; border-radius:8px; cursor:pointer; font-size:0.82rem; font-weight:600; display:flex; align-items:center; gap:6px; transition:background 0.2s; white-space:nowrap; }
  .btn-gps:hover { background:var(--primary-light); }
  .gps-status { font-size:0.75rem; padding:4px 10px; border-radius:20px; font-weight:600; }
  .gps-status.ok { background:#d4f4e4; color:var(--success); }
  .gps-status.loading { background:#fff3d4; color:#9a6800; }
  .gps-status.error { background:#fde8e8; color:var(--danger); }
  .btn-row { display:flex; gap:12px; justify-content:flex-end; margin-top:18px; flex-wrap:wrap; }
  .btn { padding:10px 22px; border:none; border-radius:9px; cursor:pointer; font-size:0.88rem; font-weight:700; display:flex; align-items:center; gap:7px; transition:all 0.18s; }
  .btn-primary { background:var(--primary); color:white; }
  .btn-primary:hover { background:var(--primary-light); transform:translateY(-1px); }
  .btn-accent { background:var(--accent); color:white; }
  .btn-accent:hover { background:var(--accent-light); transform:translateY(-1px); }
  .btn-success { background:var(--success); color:white; }
  .btn-success:hover { opacity:0.88; }
  .btn-danger { background:var(--danger); color:white; }
  .btn-outline { background:white; color:var(--primary); border:2px solid var(--border); }
  .btn-outline:hover { border-color:var(--primary); }
  .table-wrapper { overflow-x:auto; }
  table { width:100%; border-collapse:collapse; font-size:0.82rem; }
  thead th { background:var(--primary); color:white; padding:10px 12px; text-align:left; font-size:0.74rem; text-transform:uppercase; letter-spacing:0.5px; }
  tbody tr { border-bottom:1px solid var(--border); }
  tbody tr:hover { background:#f5f8fd; }
  tbody td { padding:9px 12px; vertical-align:middle; }
  .td-obs { max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
  .badge-tipo { display:inline-block; padding:3px 10px; border-radius:20px; font-size:0.72rem; font-weight:700; }
  .badge-verde { background:#d4f4e4; color:#1a7a4a; }
  .badge-azul { background:#dceeff; color:#1a5c9e; }
  .badge-naranja { background:#fff0d4; color:#a06010; }
  .badge-rojo { background:#fde8e8; color:#c0392b; }
  .badge-morado { background:#ede8fd; color:#6b3aad; }
  .stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px; }
  @media(max-width:700px){ .stats-grid { grid-template-columns:1fr 1fr; } }
  .stat-card { background:white; border-radius:10px; padding:16px 18px; border:1.5px solid var(--border); box-shadow:var(--shadow); display:flex; flex-direction:column; gap:6px; }
  .stat-card .stat-label { font-size:0.73rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; }
  .stat-card .stat-val { font-size:2rem; font-weight:800; color:var(--primary); line-height:1; }
  .stat-card .stat-sub { font-size:0.73rem; color:var(--text-muted); }
  .stat-card.accent .stat-val { color:var(--accent); }
  .stat-card.success .stat-val { color:var(--success); }
  .stat-card.danger .stat-val { color:var(--danger); }
  #toast { position:fixed; bottom:28px; right:24px; padding:13px 22px; border-radius:10px; font-weight:600; font-size:0.88rem; box-shadow:0 4px 20px rgba(0,0,0,0.18); transform:translateY(80px); opacity:0; transition:all 0.3s; z-index:999; display:flex; align-items:center; gap:10px; max-width:380px; }
  #toast.show { transform:translateY(0); opacity:1; }
  #toast.ok { background:#1a7a4a; color:white; }
  #toast.err { background:var(--danger); color:white; }
  #toast.info { background:var(--primary); color:white; }
  .time-range-badge { display:inline-flex; align-items:center; gap:5px; background:var(--bg); border:1px solid var(--border); padding:3px 10px; border-radius:20px; font-size:0.75rem; color:var(--text-muted); }
  .empty-state { text-align:center; padding:40px 20px; color:var(--text-muted); }
  .empty-state .icon { font-size:3rem; margin-bottom:12px; }
  .empty-state p { font-size:0.9rem; }
  .timeline-bar { display:flex; gap:2px; margin-top:10px; overflow-x:auto; padding-bottom:4px; }
  .tl-slot { flex:1; min-width:28px; height:28px; border-radius:5px; display:flex; align-items:center; justify-content:center; font-size:0.65rem; font-weight:700; cursor:pointer; transition:transform 0.1s; }
  .tl-slot:hover { transform:scaleY(1.2); }
  .tl-empty { background:#e8edf3; color:#aab; }
  .tl-filled { background:var(--accent); color:white; }
  .tl-label { font-size:0.65rem; color:var(--text-muted); text-align:center; }
  .section-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; flex-wrap:wrap; gap:10px; }
  .section-header h2 { font-size:1.15rem; font-weight:800; color:var(--primary); }
  .act-btn { background:none; border:none; cursor:pointer; color:var(--danger); font-size:1rem; padding:4px 6px; border-radius:5px; transition:background 0.15s; }
  .act-btn:hover { background:#fde8e8; }
  .contact-count { font-size:0.75rem; background:var(--accent); color:white; padding:1px 8px; border-radius:20px; font-weight:700; }
  .prov-item { background:#f8fafd; border:1.5px solid var(--border); border-radius:9px; padding:14px; margin-bottom:10px; }
  .prov-item-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
  .prov-item-head span { font-size:0.78rem; font-weight:700; color:var(--primary); }
  .rango-box { background:linear-gradient(135deg,#eef5ff,#e8f0fe); border:1.5px solid #c5d8f5; border-radius:12px; padding:18px 20px; margin-bottom:16px; }
  .rango-box-title { font-size:0.85rem; font-weight:800; color:var(--primary); margin-bottom:14px; display:flex; align-items:center; gap:8px; }
  .rango-inner { display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; }
  .rango-inner .form-group { min-width:155px; }
  .historial-tag { display:inline-block; background:var(--primary); color:white; padding:5px 14px; border-radius:20px; font-size:0.75rem; font-weight:700; margin-bottom:12px; }
  .quick-btns { display:flex; gap:8px; flex-wrap:wrap; align-items:flex-end; }
  .btn-sm { padding:8px 14px; font-size:0.78rem; }
</style>
</head>
<body>

<header>
  <div class="header-inner">
    <div class="header-logo">
      <div class="icon-mine">⛏️</div>
      <div>
        <h1>Control de Gastos &amp; Trazabilidad</h1>
        <p>Sistema de Bitácora de Control de Campo</p>
        <p class="area-tag">📌 Área: Control y Eficiencia Comercial</p>
      </div>
    </div>
    <div class="header-date">
      <strong id="fechaHoy"></strong>
      <span id="horaActual"></span>
    </div>
  </div>
  <div class="tabs">
    <button class="tab-btn active" onclick="switchTab('registro',this)">📋 Registrar Visita</button>
    <button class="tab-btn" onclick="switchTab('bitacora',this)">📒 Bitácora / Historial</button>
    <button class="tab-btn" onclick="switchTab('resumen',this)">📊 Resumen</button>
  </div>
</header>

<main>

<!-- ===== REGISTRO ===== -->
<section id="sec-registro" class="section active">
  <div class="section-header">
    <h2>📝 Nueva Visita de Campo</h2>
    <div id="reloj-campo" style="font-size:0.85rem;color:var(--text-muted);font-weight:600;"></div>
  </div>

  <!-- PERSONAL -->
  <div class="card">
    <div class="card-title">👤 Personal de Campo <span class="badge">Datos del Agente</span></div>
    <div class="form-grid three">
      <div class="form-group">
        <label>Nombre del Agente <span class="req">*</span></label>
        <select id="agenteSelect" onchange="toggleAgenteOtro()">
          <option value="">Seleccionar agente...</option>
          <option value="STEVE ALONZO, PINO MEZA">STEVE ALONZO, PINO MEZA</option>
          <option value="SARITA ESTEFANI, CALLE FLORES">SARITA ESTEFANI, CALLE FLORES</option>
          <option value="JEAN MARCO, GAMARRA ALMIRON">JEAN MARCO, GAMARRA ALMIRON</option>
          <option value="JOSE LUIS, MAMANI PUMA">JOSE LUIS, MAMANI PUMA</option>
          <option value="JHONSON, BALDEON SAJAMI">JHONSON, BALDEON SAJAMI</option>
          <option value="OTRO">— OTRO —</option>
        </select>
        <input type="text" id="agenteOtro" placeholder="Ingrese nombre completo..." style="display:none;margin-top:6px;" />
      </div>
      <div class="form-group">
        <label>Cargo</label>
        <select id="cargo">
          <option value="">Seleccionar...</option>
          <option>JEFE DE CONTROL Y EFICIENCIA COMERCIAL</option>
          <option>COORDINADOR DE CONTROL Y EFICIENCIA COMERCIAL</option>
          <option>SUPERVISOR DE CONTROL DE GASTOS</option>
          <option>INSPECTOR DE CONCESIONES</option>
          <option>ASISTENTE DE CONTROL DE GASTOS</option>
        </select>
      </div>
      <div class="form-group">
        <label>Fecha de Visita</label>
        <input type="date" id="fechaVisita" readonly />
      </div>
    </div>
  </div>

  <!-- HORARIO -->
  <div class="card">
    <div class="card-title">⏰ Rango Horario de Visita <span class="badge">Bitácora Temporal</span></div>
    <div class="form-grid three">
      <div class="form-group">
        <label>Hora Inicio <span class="req">*</span></label>
        <input type="time" id="horaInicio" onchange="calcDuracion()" />
      </div>
      <div class="form-group">
        <label>Hora Fin <span class="req">*</span></label>
        <input type="time" id="horaFin" onchange="calcDuracion()" />
      </div>
      <div class="form-group">
        <label>Duración Estimada</label>
        <input type="text" id="duracion" placeholder="Se calcula automáticamente..." readonly />
      </div>
    </div>
  </div>

  <!-- ZONA -->
  <div class="card">
    <div class="card-title">📍 Zona y Ubicación <span class="badge">Localización GPS</span></div>
    <div class="form-grid">
      <div class="form-group">
        <label>Zona / Sector Visitado <span class="req">*</span></label>
        <select id="zona">
          <option value="">Seleccionar zona...</option>
          <option>PLANTA SDLA</option>
          <option>OFICINA BARRENO</option>
          <option>OFICINA ISPACAS</option>
          <option>OFICINA PEDREGAL</option>
          <option>OFICINA SAN CRISTOBAL</option>
          <option>OFICINA ORCOPAMPA</option>
          <option>OFICINA ALTO MOLINO</option>
          <option>OFICINA CUSCO</option>
          <option>OFICINA AREQUIPA</option>
          <option>OTRO</option>
        </select>
      </div>
      <div class="form-group">
        <label>Tipo de Zona</label>
        <select id="tipoZona">
          <option value="">Seleccionar...</option>
          <option>Concesión Minera</option>
          <option>Planta de Procesamiento</option>
          <option>Puesto de Control</option>
          <option>Comunidad / Caserío</option>
          <option>Oficina REINFO</option>
          <option>Depósito / Almacén</option>
          <option>Otro</option>
        </select>
      </div>
      <div class="form-group">
        <label>Provincia</label>
        <input type="text" id="provincia" placeholder="Provincia..." />
      </div>
      <div class="form-group">
        <label>Distrito</label>
        <input type="text" id="distrito" placeholder="Distrito..." />
      </div>
    </div>
  </div>

  <!-- PROVEEDORES MINEROS // OTROS + MOTIVO UNIFICADO -->
  <div class="card">
    <div class="card-title">
      👥 Proveedores Mineros &nbsp;//&nbsp; Otros
      <span id="contadorContactos" class="contact-count">0</span>
      <span class="badge" style="margin-left:auto;">Contactos + Observaciones</span>
    </div>

    <!-- BLOQUE 1: Contactos -->
    <div class="subsection-label">⛏️ Contactos Registrados</div>
    <div id="listaContactos"></div>
    <button class="btn btn-outline" onclick="agregarContacto()" style="font-size:0.82rem;margin-bottom:20px;">
      ➕ Agregar Proveedor / Contacto
    </button>

    <hr class="card-divider">

    <!-- BLOQUE 2: Motivo + Estado + Obs -->
    <div class="subsection-label">📋 Motivo, Estado y Observaciones</div>
    <div class="form-grid">
      <div class="form-group">
        <label>Motivo de Visita <span class="req">*</span></label>
        <select id="motivoVisita">
          <option value="">Seleccionar motivo...</option>
          <option>Verificación de Inscripción REINFO</option>
          <option>Fiscalización de Actividad Minera</option>
          <option>Orientación para Formalización</option>
          <option>Entrega de Documentación</option>
          <option>Control de Trazabilidad de Mineral</option>
          <option>Verificación de Zona de Trabajo</option>
          <option>Reunión con Comunidad</option>
          <option>Supervisión de Compromisos</option>
          <option>Verificación de IGAFOM</option>
          <option>Control de DEMA</option>
          <option>Control de Gastos</option>
          <option>Visita a Proveedor Minero</option>
          <option>Otro</option>
        </select>
      </div>
      <div class="form-group">
        <label>Estado del Proceso</label>
        <select id="estadoProceso">
          <option value="">Seleccionar...</option>
          <option>✅ Conforme — En Proceso Regular</option>
          <option>⚠️ Observado — Requiere Subsanar</option>
          <option>🔴 Crítico — Acción Inmediata</option>
          <option>📋 Pendiente — En Evaluación</option>
          <option>✔️ Formalizado — Proceso Completo</option>
        </select>
      </div>
      <div class="form-group span2">
        <label>Observaciones Detalladas <span class="req">*</span></label>
        <textarea id="observaciones" placeholder="Describa detalladamente: hallazgos, condiciones encontradas, compromisos adquiridos, acciones tomadas, documentos verificados..."></textarea>
      </div>
      <div class="form-group span2">
        <label>Acciones / Compromisos Acordados</label>
        <textarea id="acciones" style="min-height:60px;" placeholder="Indicar compromisos, plazos, seguimiento requerido..."></textarea>
      </div>
    </div>

    <div class="btn-row">
      <button class="btn btn-outline" onclick="limpiarFormulario()">🗑️ Limpiar</button>
      <button class="btn btn-accent" onclick="guardarRegistro()">💾 Guardar Registro</button>
    </div>
  </div>
</section>

<!-- ===== BITÁCORA / HISTORIAL ===== -->
<section id="sec-bitacora" class="section">
  <div class="section-header">
    <h2>📒 Bitácora &amp; Historial Acumulativo</h2>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <button class="btn btn-success" onclick="exportarExcel()">📥 Exportar Excel</button>
      <button class="btn btn-danger" onclick="limpiarTodo()" style="font-size:0.8rem;">🗑 Limpiar Todo</button>
    </div>
  </div>

  <!-- FILTRO RANGO DE FECHAS -->
  <div class="rango-box">
    <div class="rango-box-title">📅 Filtrar Historial por Rango de Fechas</div>
    <div class="rango-inner">
      <div class="form-group">
        <label>📌 Fecha Inicial</label>
        <input type="date" id="filtroFechaIni" onchange="actualizarBitacora()" />
      </div>
      <div class="form-group">
        <label>📌 Fecha Final</label>
        <input type="date" id="filtroFechaFin" onchange="actualizarBitacora()" />
      </div>
      <div class="form-group">
        <label>Agente</label>
        <input type="text" id="filtroAgente" placeholder="Nombre..." oninput="actualizarBitacora()" />
      </div>
      <div class="form-group">
        <label>Zona</label>
        <input type="text" id="filtroZona" placeholder="Zona..." oninput="actualizarBitacora()" />
      </div>
      <div class="form-group">
        <label>Estado</label>
        <select id="filtroEstado" onchange="actualizarBitacora()">
          <option value="">Todos</option>
          <option>Conforme</option>
          <option>Observado</option>
          <option>Crítico</option>
          <option>Pendiente</option>
          <option>Formalizado</option>
        </select>
      </div>
      <div class="quick-btns">
        <button class="btn btn-primary btn-sm" onclick="filtroHoy()">📅 Hoy</button>
        <button class="btn btn-primary btn-sm" onclick="filtroSemana()">📆 Esta Semana</button>
        <button class="btn btn-primary btn-sm" onclick="filtroMes()">🗓 Este Mes</button>
        <button class="btn btn-outline btn-sm" onclick="filtroTodos()">📋 Todos</button>
        <button class="btn btn-outline btn-sm" onclick="limpiarFiltros()">↺ Limpiar</button>
      </div>
    </div>
  </div>

  <!-- TIMELINE -->
  <div class="card" style="padding:16px 20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
      <span style="font-size:0.8rem;font-weight:700;color:var(--primary);">⏱ Actividad por Hora (rango seleccionado)</span>
      <span style="font-size:0.72rem;color:var(--text-muted);">Naranja = visitas registradas</span>
    </div>
    <div class="timeline-bar" id="timelineBar"></div>
    <div style="display:flex;justify-content:space-between;margin-top:4px;">
      <span class="tl-label">00:00</span><span class="tl-label">06:00</span>
      <span class="tl-label">12:00</span><span class="tl-label">18:00</span><span class="tl-label">23:00</span>
    </div>
  </div>

  <!-- LABEL HISTORIAL -->
  <div id="historialLabel"></div>

  <!-- TABLA -->
  <div class="card" style="padding:0;overflow:hidden;">
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>#</th><th>Fecha</th><th>H. Inicio</th><th>H. Fin</th><th>Duración</th>
            <th>Agente</th><th>Cargo</th><th>Zona</th><th>Tipo Zona</th>
            <th>Prov./Dist.</th><th>GPS</th><th>N° Contactos</th>
            <th>Nombres Contactos</th><th>Motivo</th><th>Estado</th>
            <th>Observaciones</th><th>Compromisos</th><th>Del</th>
          </tr>
        </thead>
        <tbody id="tablaBody"></tbody>
      </table>
    </div>
    <div id="emptyState" class="empty-state">
      <div class="icon">📋</div>
      <p>No hay registros para el rango de fechas seleccionado.<br>
      Usa la pestaña <strong>Registrar Visita</strong> para comenzar.</p>
    </div>
  </div>
  <div style="margin-top:12px;font-size:0.78rem;color:var(--text-muted);text-align:right;">
    <span id="totalRegistros">0 registros</span>
  </div>
</section>

<!-- ===== RESUMEN ===== -->
<section id="sec-resumen" class="section">
  <div class="section-header">
    <h2>📊 Resumen de Actividades</h2>
    <button class="btn btn-accent" onclick="renderResumen()">🔄 Actualizar</button>
  </div>
  <div class="stats-grid" id="statsGrid"></div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
    <div class="card"><div class="card-title">📍 Zonas más Visitadas</div><div id="topZonas"></div></div>
    <div class="card"><div class="card-title">👥 Actividad por Agente</div><div id="topAgentes"></div></div>
    <div class="card"><div class="card-title">📋 Motivos de Visita</div><div id="topMotivos"></div></div>
    <div class="card"><div class="card-title">🔴 Estado de Procesos</div><div id="topEstados"></div></div>
  </div>
</section>
</main>
<div id="toast"></div>

<script>
var STORAGE_KEY = 'cgtreinf_v3';
var registros = [];
try { registros = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); } catch(e){ registros = []; }
var contactCount = 0;

window.onload = async function() {
  var hoy = fechaStr(new Date());
  document.getElementById('fechaVisita').value = hoy;
  document.getElementById('filtroFechaIni').value = hoy;
  document.getElementById('filtroFechaFin').value = hoy;
  actualizarReloj();
  setInterval(actualizarReloj, 1000);
  await cargarRegistros();  
  actualizarBitacora();
  renderResumen();
};

function fechaStr(d) {
  return d.getFullYear() + '-' + pad2(d.getMonth()+1) + '-' + pad2(d.getDate());
}
function pad2(n) { return String(n).padStart(2,'0'); }

function actualizarReloj() {
  var n = new Date();
  document.getElementById('fechaHoy').textContent = n.toLocaleDateString('es-PE',{weekday:'long',year:'numeric',month:'long',day:'numeric'});
  document.getElementById('horaActual').textContent = n.toLocaleTimeString('es-PE');
  var rc = document.getElementById('reloj-campo');
  if(rc) rc.textContent = '🕐 ' + n.toLocaleTimeString('es-PE');
}

function toggleAgenteOtro() {
  var v = document.getElementById('agenteSelect').value;
  var o = document.getElementById('agenteOtro');
  if(v === 'OTRO') { o.style.display = 'block'; o.focus(); }
  else { o.style.display = 'none'; o.value = ''; }
}

function getAgente() {
  var v = document.getElementById('agenteSelect').value;
  return v === 'OTRO' ? document.getElementById('agenteOtro').value.trim() : v;
}

function calcDuracion() {
  var i = document.getElementById('horaInicio').value;
  var f = document.getElementById('horaFin').value;
  if(i && f) {
    var p1=i.split(':'), p2=f.split(':');
    var m = (parseInt(p2[0])*60+parseInt(p2[1])) - (parseInt(p1[0])*60+parseInt(p1[1]));
    if(m<0) m+=1440;
    document.getElementById('duracion').value = Math.floor(m/60) > 0 ? Math.floor(m/60)+'h '+(m%60)+'min' : (m%60)+'min';
  }
}

function capturarGPS() {
  var st = document.getElementById('gpsStatus');
  st.style.display='inline'; st.className='gps-status loading'; st.textContent='⏳ Localizando...';
  if(!navigator.geolocation){ st.className='gps-status error'; st.textContent='❌ No disponible'; return; }
  navigator.geolocation.getCurrentPosition(function(pos){
    var lat=pos.coords.latitude.toFixed(6), lon=pos.coords.longitude.toFixed(6);
    var alt=pos.coords.altitude ? pos.coords.altitude.toFixed(0) : 'N/D';
    var el=document.getElementById('gpsDisplay');
    el.textContent='Lat: '+lat+' | Lon: '+lon+' | Alt: '+alt+'m';
    el.dataset.lat=lat; el.dataset.lon=lon; el.dataset.alt=alt;
    st.className='gps-status ok'; st.textContent='✅ GPS OK';
  }, function(){
    st.className='gps-status error'; st.textContent='❌ Error GPS';
  }, {timeout:10000, enableHighAccuracy:true});
}

function agregarContacto() {
  contactCount++;
  var id = 'cont_'+contactCount;
  var div = document.createElement('div');
  div.id = id; div.className = 'prov-item';
  div.innerHTML = '<div class="prov-item-head"><span>👤 Contacto #'+contactCount+'</span>'
    +'<button class="act-btn" onclick="document.getElementById(\''+id+'\').remove();updateContCount();">✕</button></div>'
    +'<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">'
    +'<div class="form-group"><label>Nombre Completo</label><input type="text" name="c_nombre" placeholder="Apellidos y Nombres" /></div>'
    +'<div class="form-group"><label>Tipo</label><select name="c_tipo"><option value="">Seleccionar...</option>'
    +'<option>Proveedor Minero</option><option>Minero Artesanal</option><option>Pequeño Productor</option>'
    +'<option>Minero Informal</option><option>Minero Ilegal</option><option>Contratista</option><option>Otro</option>'
    +'</select></div>'
    +'<div class="form-group"><label>DNI / RUC</label><input type="text" name="c_doc" placeholder="Nro. Documento" /></div>'
    +'<div class="form-group"><label>Código REINFO</label><input type="text" name="c_reinfo" placeholder="REI-000000" /></div>'
    +'</div>';
  document.getElementById('listaContactos').appendChild(div);
  updateContCount();
}

function updateContCount() {
  document.getElementById('contadorContactos').textContent = document.getElementById('listaContactos').children.length;
}

function getContactos() {
  var items = document.getElementById('listaContactos').children, lista = [];
  for(var i=0; i<items.length; i++){
    lista.push({
      nombre: items[i].querySelector('[name="c_nombre"]') ? items[i].querySelector('[name="c_nombre"]').value : '',
      tipo: items[i].querySelector('[name="c_tipo"]') ? items[i].querySelector('[name="c_tipo"]').value : '',
      doc: items[i].querySelector('[name="c_doc"]') ? items[i].querySelector('[name="c_doc"]').value : '',
      reinfo: items[i].querySelector('[name="c_reinfo"]') ? items[i].querySelector('[name="c_reinfo"]').value : ''
    });
  }
  return lista;
}


async function cargarRegistros() {
  try {
    const res = await fetch('/api/visitas.php');
    const data = await res.json();

    registros = Array.isArray(data) ? data : [];

  } catch (err) {
    showToast('Error cargando registros', 'err');
    registros = [];
  }
}

async function guardarRegistro() {

  var agente = getAgente(),
      fecha = document.getElementById('fechaVisita').value,
      hIni = document.getElementById('horaInicio').value,
      hFin = document.getElementById('horaFin').value,
      zona = document.getElementById('zona').value,
      motivo = document.getElementById('motivoVisita').value,
      obs = document.getElementById('observaciones').value.trim();

  if(!agente || !fecha || !hIni || !hFin || !zona || !motivo || !obs){
    showToast('⚠️ Complete todos los campos obligatorios (*)','err');
    return;
  }

  var contactos = getContactos();

  var data = {
    agente: agente,
    cargo: document.getElementById('cargo').value,
    fecha: fecha,
    horaInicio: hIni,
    horaFin: hFin,
    duracion: document.getElementById('duracion').value,
    zona: zona,
    tipoZona: document.getElementById('tipoZona').value,
    provincia: document.getElementById('provincia').value,
    distrito: document.getElementById('distrito').value,
    motivo: motivo,
    estado: document.getElementById('estadoProceso').value,
    observaciones: obs,
    acciones: document.getElementById('acciones').value,
    nContactos: contactos.length,
    nombresContactos: contactos.map(c => c.nombre).filter(Boolean).join(' | ')
  };

  try {

    const res = await fetch('/api/visitas.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });

    const response = await res.json();

    if(response.success){
      showToast('✅ Registro guardado en base de datos','ok');
      limpiarFormulario();
    } else {
      showToast('Error al guardar','err');
    }

  } catch (error) {
    console.error(error);
    showToast('Error de conexión','err');
  }
}

function saveLS() { try{ localStorage.setItem(STORAGE_KEY,JSON.stringify(registros)); }catch(e){} }

function limpiarFormulario() {
  ['cargo','horaInicio','horaFin','duracion','zona','tipoZona','provincia','distrito',
   'motivoVisita','estadoProceso','observaciones','acciones'].forEach(function(id){
    var el=document.getElementById(id); if(el) el.value='';
  });
  document.getElementById('agenteSelect').value='';
  document.getElementById('agenteOtro').value='';
  document.getElementById('agenteOtro').style.display='none';
  document.getElementById('listaContactos').innerHTML='';
  contactCount=0; updateContCount();
  document.getElementById('fechaVisita').value=fechaStr(new Date());
}

// ===== FILTROS RAPIDOS =====
function filtroHoy() {
  var h=fechaStr(new Date());
  document.getElementById('filtroFechaIni').value=h;
  document.getElementById('filtroFechaFin').value=h;
  actualizarBitacora();
}
function filtroSemana() {
  var h=new Date(), lun=new Date(h);
  lun.setDate(h.getDate()-((h.getDay()||7)-1));
  var dom=new Date(lun); dom.setDate(lun.getDate()+6);
  document.getElementById('filtroFechaIni').value=fechaStr(lun);
  document.getElementById('filtroFechaFin').value=fechaStr(dom);
  actualizarBitacora();
}
function filtroMes() {
  var h=new Date();
  var ini=new Date(h.getFullYear(),h.getMonth(),1);
  var fin=new Date(h.getFullYear(),h.getMonth()+1,0);
  document.getElementById('filtroFechaIni').value=fechaStr(ini);
  document.getElementById('filtroFechaFin').value=fechaStr(fin);
  actualizarBitacora();
}
function filtroTodos() {
  document.getElementById('filtroFechaIni').value='';
  document.getElementById('filtroFechaFin').value='';
  actualizarBitacora();
}
function limpiarFiltros() {
  filtroHoy();
  document.getElementById('filtroAgente').value='';
  document.getElementById('filtroZona').value='';
  document.getElementById('filtroEstado').value='';
  actualizarBitacora();
}
function limpiarTodo() {
  var pass = prompt('🔒 Ingrese la contraseña para eliminar todos los registros:');
  if(pass === null) return;
  if(pass !== 'Spinocaj4'){
    showToast('❌ Contraseña incorrecta. Operación cancelada.','err');
    return;
  }
  if(confirm('⚠️ ¿Está seguro? Se eliminarán TODOS los registros del historial. Esta acción no se puede deshacer.')){
    registros=[]; saveLS(); actualizarBitacora(); renderResumen();
    showToast('✅ Historial eliminado correctamente.','info');
  }
}
function eliminarRegistro(id) {
  if(!confirm('¿Eliminar este registro?')) return;

  fetch('/api/visitas.php?id=' + id, {
    method: 'DELETE'
  })
  .then(res => res.json())
  .then(async response => {
    if(response.success){
      await cargarRegistros();
      actualizarBitacora();
      renderResumen();
      showToast('Registro eliminado correctamente','ok');
    } else {
      showToast('Error al eliminar','err');
    }
  })
  .catch(err => {
    console.error(err);
    showToast('Error de conexión','err');
  });
}

function actualizarBitacora() { renderTabla(); renderTimeline(); }

function getFiltrados() {
  var fI=document.getElementById('filtroFechaIni')?document.getElementById('filtroFechaIni').value:'';
  var fF=document.getElementById('filtroFechaFin')?document.getElementById('filtroFechaFin').value:'';
  var fA=(document.getElementById('filtroAgente')?document.getElementById('filtroAgente').value:'').toLowerCase();
  var fZ=(document.getElementById('filtroZona')?document.getElementById('filtroZona').value:'').toLowerCase();
  var fE=(document.getElementById('filtroEstado')?document.getElementById('filtroEstado').value:'').toLowerCase();
  return registros.filter(function(r){
    if(fI && r.fecha < fI) return false;
    if(fF && r.fecha > fF) return false;
    if(fA && !r.agente.toLowerCase().includes(fA)) return false;
    if(fZ && !r.zona.toLowerCase().includes(fZ)) return false;
    if(fE && !r.estado.toLowerCase().includes(fE)) return false;
    return true;
  });
}

function renderTabla() {
  var filtrados = getFiltrados();
  var fI=document.getElementById('filtroFechaIni').value;
  var fF=document.getElementById('filtroFechaFin').value;

  // Badge historial
  var hl=document.getElementById('historialLabel');
  var txt = '📋 ';
  if(!fI && !fF) txt += 'Mostrando TODOS los registros acumulados';
  else if(fI && fF && fI===fF) txt += 'Registros del día ' + formatFecha(fI);
  else if(fI && fF) txt += 'Historial del ' + formatFecha(fI) + ' al ' + formatFecha(fF);
  else if(fI) txt += 'Registros desde ' + formatFecha(fI);
  else txt += 'Registros hasta ' + formatFecha(fF);
  txt += ' — ' + filtrados.length + ' registro(s) | Total acumulado: ' + registros.length;
  hl.innerHTML = '<div class="historial-tag">'+txt+'</div>';

  var tbody=document.getElementById('tablaBody'), empty=document.getElementById('emptyState');
  tbody.innerHTML='';
  if(!filtrados.length){ empty.style.display='block'; document.getElementById('totalRegistros').textContent='0 registros'; return; }
  empty.style.display='none';
  document.getElementById('totalRegistros').textContent=filtrados.length+' registro(s) en rango seleccionado — Total: '+registros.length;

  filtrados.sort(function(a,b){
    if(a.fecha!==b.fecha) return b.fecha.localeCompare(a.fecha);
    return a.horaInicio.localeCompare(b.horaInicio);
  });

  filtrados.forEach(function(r,i){
    var bc=r.estado&&r.estado.includes('Conforme')?'badge-verde':
           r.estado&&r.estado.includes('Observado')?'badge-naranja':
           r.estado&&r.estado.includes('Crítico')?'badge-rojo':
           r.estado&&r.estado.includes('Formalizado')?'badge-azul':'badge-morado';
    var gpsStr=r.lat?parseFloat(r.lat).toFixed(4)+', '+parseFloat(r.lon).toFixed(4):'—';
    var eLabel=r.estado?r.estado.replace(/^[\S]+ /,''):'—';
    var tr=document.createElement('tr');
    tr.innerHTML='<td style="font-weight:700;color:var(--text-muted);">'+(i+1)+'</td>'
      +'<td><strong>'+formatFecha(r.fecha)+'</strong></td>'
      +'<td><span class="time-range-badge">▶ '+r.horaInicio+'</span></td>'
      +'<td><span class="time-range-badge">⏹ '+r.horaFin+'</span></td>'
      +'<td style="font-size:0.78rem;color:var(--text-muted);">'+(r.duracion||'—')+'</td>'
      +'<td><strong>'+r.agente+'</strong></td>'
      +'<td style="font-size:0.75rem;">'+(r.cargo||'—')+'</td>'
      +'<td><strong>'+r.zona+'</strong></td>'
      +'<td style="font-size:0.78rem;">'+(r.tipoZona||'—')+'</td>'
      +'<td style="font-size:0.75rem;">'+([r.provincia,r.distrito].filter(Boolean).join(' / ')||'—')+'</td>'
      +'<td style="font-size:0.72rem;font-family:monospace;">'+gpsStr+'</td>'
      +'<td style="text-align:center;"><span class="contact-count">'+(r.nContactos||0)+'</span></td>'
      +'<td class="td-obs" title="'+(r.nombresContactos||'')+'" style="font-size:0.78rem;">'+(r.nombresContactos||'—')+'</td>'
      +'<td style="font-size:0.78rem;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="'+(r.motivo||'')+'">'+(r.motivo||'—')+'</td>'
      +'<td><span class="badge-tipo '+bc+'" style="font-size:0.68rem;">'+eLabel+'</span></td>'
      +'<td class="td-obs" title="'+(r.observaciones||'')+'">'+(r.observaciones||'—')+'</td>'
      +'<td class="td-obs" style="font-size:0.75rem;color:var(--text-muted);" title="'+(r.acciones||'')+'">'+(r.acciones||'—')+'</td>'
      +'<td><button class="act-btn" onclick="eliminarRegistro('+r.id+')">✕</button></td>';
    tbody.appendChild(tr);
  });
}

function formatFecha(f){ if(!f)return'—'; var p=f.split('-'); return p[2]+'/'+p[1]+'/'+p[0]; }

function renderTimeline() {
  var bar=document.getElementById('timelineBar'); if(!bar)return;
  var filtrados=getFiltrados();
  var horas=new Array(24).fill(0);
  filtrados.forEach(function(r){ if(r.horaInicio){ horas[parseInt(r.horaInicio.split(':')[0])]++; } });
  bar.innerHTML=horas.map(function(c,h){
    var hh=pad2(h);
    return '<div class="tl-slot '+(c>0?'tl-filled':'tl-empty')+'" title="'+hh+':00 — '+c+' visita(s)"'
      +(c>0?' style="opacity:'+Math.min(0.4+c*0.2,1)+';"':'')+'>'+hh+'</div>';
  }).join('');
}

function renderResumen() {
  var total=registros.length;
  var contactos=registros.reduce(function(s,r){return s+(r.nContactos||0);},0);
  var zonas=new Set(registros.map(function(r){return r.zona;})).size;
  var criticos=registros.filter(function(r){return r.estado&&r.estado.includes('Crítico');}).length;
  document.getElementById('statsGrid').innerHTML=
    '<div class="stat-card"><div class="stat-label">Total Visitas</div><div class="stat-val">'+total+'</div><div class="stat-sub">Registros acumulados</div></div>'
    +'<div class="stat-card success"><div class="stat-label">Contactos Totales</div><div class="stat-val">'+contactos+'</div><div class="stat-sub">Proveedores / Otros</div></div>'
    +'<div class="stat-card accent"><div class="stat-label">Zonas Visitadas</div><div class="stat-val">'+zonas+'</div><div class="stat-sub">Sectores únicos</div></div>'
    +'<div class="stat-card danger"><div class="stat-label">Casos Críticos</div><div class="stat-val">'+criticos+'</div><div class="stat-sub">Requieren acción</div></div>';
  renderTop('topZonas',registros.map(function(r){return r.zona;}).filter(Boolean));
  renderTop('topAgentes',registros.map(function(r){return r.agente;}).filter(Boolean));
  renderTop('topMotivos',registros.map(function(r){return r.motivo;}).filter(Boolean));
  renderTop('topEstados',registros.map(function(r){return r.estado;}).filter(Boolean));
}

function renderTop(elId,arr){
  var counts={};
  arr.forEach(function(v){counts[v]=(counts[v]||0)+1;});
  var sorted=Object.entries(counts).sort(function(a,b){return b[1]-a[1];}).slice(0,6);
  var max=sorted[0]?sorted[0][1]:1;
  var el=document.getElementById(elId);
  if(!sorted.length){el.innerHTML='<p style="color:var(--text-muted);font-size:0.82rem;padding:10px 0;">Sin datos aún</p>';return;}
  el.innerHTML=sorted.map(function(kv){
    return '<div style="margin-bottom:10px;">'
      +'<div style="display:flex;justify-content:space-between;margin-bottom:4px;">'
      +'<span style="font-size:0.8rem;font-weight:600;max-width:75%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+kv[0].replace(/^[\S]+ /,'')+'</span>'
      +'<span style="font-size:0.78rem;color:var(--text-muted);">'+kv[1]+'</span></div>'
      +'<div style="height:6px;background:var(--bg);border-radius:3px;">'
      +'<div style="height:100%;width:'+(kv[1]/max*100)+'%;background:var(--accent);border-radius:3px;"></div></div></div>';
  }).join('');
}

function exportarExcel() {
  var filtrados = getFiltrados();
  var fI = document.getElementById('filtroFechaIni').value;
  var fF = document.getElementById('filtroFechaFin').value;
  if(!filtrados.length){ showToast('No hay registros en el rango seleccionado','err'); return; }

  filtrados.sort(function(a,b){
    if(a.fecha!==b.fecha) return a.fecha.localeCompare(b.fecha);
    return a.horaInicio.localeCompare(b.horaInicio);
  });

  if(typeof XLSX === 'undefined'){
    showToast('⏳ Cargando librería Excel, intente de nuevo...','info');
    return;
  }

  var wb = XLSX.utils.book_new();

  /* ===== HOJA 1: BITÁCORA PRINCIPAL ===== */
  var ws1Data = [];
  var h1 = ['#','FECHA','HORA INICIO','HORA FIN','DURACIÓN','AGENTE','CARGO',
            'ZONA / SECTOR','TIPO DE ZONA','PROVINCIA','DISTRITO',
            'LATITUD','LONGITUD','MOTIVO DE VISITA','ESTADO DEL PROCESO',
            'OBSERVACIONES DETALLADAS','ACCIONES / COMPROMISOS','N° CONTACTOS'];
  ws1Data.push(h1);

  filtrados.forEach(function(r,i){
    var eClean = r.estado ? r.estado.replace(/^[^\s]+ /,'') : '';
    ws1Data.push([
      i+1,
      formatFecha(r.fecha),
      r.horaInicio || '',
      r.horaFin || '',
      r.duracion || '',
      r.agente || '',
      r.cargo || '',
      r.zona || '',
      r.tipoZona || '',
      r.provincia || '',
      r.distrito || '',
      r.lat || '',
      r.lon || '',
      r.motivo || '',
      eClean,
      r.observaciones || '',
      r.acciones || '',
      r.nContactos || 0
    ]);
  });

  var ws1 = XLSX.utils.aoa_to_sheet(ws1Data);
  ws1['!cols'] = [
    {wch:4},{wch:12},{wch:10},{wch:10},{wch:10},{wch:28},{wch:32},
    {wch:22},{wch:20},{wch:16},{wch:16},
    {wch:12},{wch:12},{wch:26},{wch:22},
    {wch:45},{wch:38},{wch:8}
  ];
  ws1['!freeze'] = {xSplit:'A', ySplit:'2', topLeftCell:'A2', activePane:'bottomLeft'};
  XLSX.utils.book_append_sheet(wb, ws1, 'Bitacora Principal');

  /* ===== HOJA 2: CONTACTOS DETALLADOS ===== */
  var ws2Data = [];
  var h2 = ['#','FECHA','AGENTE','CARGO','ZONA','MOTIVO','CONTACTO N°',
            'NOMBRE COMPLETO','TIPO','DNI / RUC','CÓDIGO REINFO'];
  ws2Data.push(h2);

  var contRow = 0;
  filtrados.forEach(function(r){
    if(r.contactos && r.contactos.length > 0){
      r.contactos.forEach(function(c,ci){
        contRow++;
        ws2Data.push([
          contRow,
          formatFecha(r.fecha),
          r.agente || '',
          r.cargo || '',
          r.zona || '',
          r.motivo || '',
          ci+1,
          c.nombre || '',
          c.tipo || '',
          c.doc || '',
          c.reinfo || ''
        ]);
      });
    } else {
      contRow++;
      ws2Data.push([
        contRow,
        formatFecha(r.fecha),
        r.agente || '',
        r.cargo || '',
        r.zona || '',
        r.motivo || '',
        '-',
        'Sin contactos registrados',
        '','',''
      ]);
    }
  });

  var ws2 = XLSX.utils.aoa_to_sheet(ws2Data);
  ws2['!cols'] = [
    {wch:4},{wch:12},{wch:28},{wch:32},{wch:22},{wch:26},
    {wch:8},{wch:30},{wch:20},{wch:14},{wch:14}
  ];
  ws2['!freeze'] = {xSplit:'A', ySplit:'2', topLeftCell:'A2', activePane:'bottomLeft'};
  XLSX.utils.book_append_sheet(wb, ws2, 'Contactos Detallados');

  /* ===== HOJA 3: RESUMEN POR AGENTE ===== */
  var agMap = {};
  filtrados.forEach(function(r){
    var ag = r.agente || 'Sin nombre';
    if(!agMap[ag]) agMap[ag]={visitas:0,contactos:0,zonas:new Set(),criticos:0,horas:0};
    agMap[ag].visitas++;
    agMap[ag].contactos += (r.nContactos||0);
    agMap[ag].zonas.add(r.zona);
    if(r.estado&&r.estado.includes('Crítico')) agMap[ag].criticos++;
  });

  var ws3Data = [];
  var h3 = ['AGENTE','TOTAL VISITAS','TOTAL CONTACTOS','ZONAS DISTINTAS','CASOS CRÍTICOS'];
  ws3Data.push(h3);
  Object.keys(agMap).sort().forEach(function(ag){
    var d = agMap[ag];
    ws3Data.push([ag, d.visitas, d.contactos, d.zonas.size, d.criticos]);
  });
  // Fila totales
  ws3Data.push(['TOTAL GENERAL',
    filtrados.length,
    filtrados.reduce(function(s,r){return s+(r.nContactos||0);},0),
    new Set(filtrados.map(function(r){return r.zona;})).size,
    filtrados.filter(function(r){return r.estado&&r.estado.includes('Crítico');}).length
  ]);

  var ws3 = XLSX.utils.aoa_to_sheet(ws3Data);
  ws3['!cols'] = [{wch:32},{wch:14},{wch:16},{wch:16},{wch:14}];
  ws3['!freeze'] = {xSplit:'A', ySplit:'2', topLeftCell:'A2', activePane:'bottomLeft'};
  XLSX.utils.book_append_sheet(wb, ws3, 'Resumen por Agente');

  /* ===== HOJA 4: RESUMEN POR ZONA ===== */
  var zonaMap = {};
  filtrados.forEach(function(r){
    var z = r.zona || 'Sin zona';
    if(!zonaMap[z]) zonaMap[z]={visitas:0,agentes:new Set(),contactos:0};
    zonaMap[z].visitas++;
    zonaMap[z].agentes.add(r.agente);
    zonaMap[z].contactos += (r.nContactos||0);
  });

  var ws4Data = [];
  ws4Data.push(['ZONA / SECTOR','TOTAL VISITAS','AGENTES DISTINTOS','TOTAL CONTACTOS']);
  Object.keys(zonaMap).sort().forEach(function(z){
    var d = zonaMap[z];
    ws4Data.push([z, d.visitas, d.agentes.size, d.contactos]);
  });

  var ws4 = XLSX.utils.aoa_to_sheet(ws4Data);
  ws4['!cols'] = [{wch:28},{wch:14},{wch:18},{wch:16}];
  ws4['!freeze'] = {xSplit:'A', ySplit:'2', topLeftCell:'A2', activePane:'bottomLeft'};
  XLSX.utils.book_append_sheet(wb, ws4, 'Resumen por Zona');

  /* ===== DESCARGAR ===== */
  var n = new Date();
  var sf = (fI&&fF&&fI!==fF)
    ? '_'+fI.replace(/-/g,'')+'_al_'+fF.replace(/-/g,'')
    : '_'+fechaStr(n).replace(/-/g,'');
  var fname = 'Bitacora_CG_Trazabilidad'+sf+'.xlsx';

  XLSX.writeFile(wb, fname);
  showToast('📥 Excel exportado: '+filtrados.length+' registros en 4 hojas','ok');
}

async function switchTab(tab,btn) {
  document.querySelectorAll('.section').forEach(function(s){s.classList.remove('active');});
  document.querySelectorAll('.tab-btn').forEach(function(b){b.classList.remove('active');});
  document.getElementById('sec-'+tab).classList.add('active');
  btn.classList.add('active');
  if(tab==='bitacora'){ await cargarRegistros();console.log("Registros después de cargar:", registros.length); actualizarBitacora();}
  if(tab==='resumen') renderResumen();
}

function showToast(msg,type){
  var t=document.getElementById('toast');
  t.textContent=msg; t.className='show '+(type||'ok');
  setTimeout(function(){t.classList.remove('show');},3500);
}
</script>
</body>
</html>
