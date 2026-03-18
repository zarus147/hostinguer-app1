<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Control de Gastos & Trazabilidad - Bitácora de Campo</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<link rel="stylesheet" href="/assets/css/styles.css">
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

<script src="/assets/js/app.js"></script>
</body>
</html>
