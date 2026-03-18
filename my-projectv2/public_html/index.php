<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reporte Diario de Conductor — SDLA-ACOP-001</title>
<link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>

<!-- HEADER -->
<div class="header">
  <div class="header-inner">
    <div class="header-left">
      <span class="company-name">Minera Sol de los Andes</span>
      <span class="doc-title">Reporte Diario de Conductor</span>
    </div>
    <div class="header-right">
      <span class="code-badge">SDLA-ACOP-001</span>
      <span class="version-text">VERSIÓN 2</span>
      <span class="storage-badge"><span class="storage-dot"></span> DATOS GUARDADOS LOCALMENTE</span>
    </div>
  </div>
  <div class="tab-bar">
    <button class="tab-btn active" onclick="switchTab('registro', this)">▪ Registro</button>
    <button class="tab-btn" onclick="switchTab('resumen', this)">▪ Resumen por Placas</button>
  </div>
</div>

<!-- ================================================================ -->
<!-- TAB: REGISTRO -->
<!-- ================================================================ -->
<div id="tab-registro" class="tab-panel active">
<div class="container">

  <!-- ZONA PRODUCTIVA -->
  <div class="section zona-section">
    <div class="section-header"><span class="marker"></span>Zona Productiva</div>
    <div class="section-body">
      <div class="form-grid grid-3">
        <div class="form-group span2">
          <label>Seleccionar Zona</label>
          <select id="zona">
            <option value="">— Seleccionar Zona —</option>
            <option>PLANTA SDLA</option>
            <option>OFICINA BARRENO</option>
            <option>OFICINA ISPACAS</option>
            <option>OFICINA PEDREGAL</option>
            <option>OFICINA SAN CRISTOBAL</option>
            <option>OFICINA ORCOPAMPA</option>
            <option>OFICINA ALTO MOLINO</option>
            <option>OFICINA CUSCO</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- IDENTIFICACIÓN DEL EQUIPO -->
  <div class="section">
    <div class="section-header"><span class="marker"></span>Identificación del Equipo</div>
    <div class="section-body">
      <div id="placa-indicator-wrap"></div>
      <div class="form-grid grid-5">

        <div class="form-group">
          <label>Placa</label>
          <select id="placa" onchange="onPlacaChange()">
            <option value="">— Seleccionar —</option>
            <option>W7U 853</option>
            <option>BNQ 902</option>
            <option value="OTRO">OTRO</option>
          </select>
        </div>

        <div class="form-group" id="placa-manual-wrap" style="display:none"> 
          <label>Placa (ingresar)</label>
          <input type="text" id="placa-manual" placeholder="Ej. ABC 123" oninput="onPlacaManualChange()">
        </div>

        <div class="form-group">
          <label>Equipo</label>
          <select id="equipo">
            <option value="">— Seleccionar —</option>
            <option>CAMIONETA</option>
            <option>IZUSU</option>
            <option>VOLQUETE</option>
          </select>
        </div>

        <div class="form-group">
          <label>Modelo</label>
          <select id="modelo">
            <option value="">— Seleccionar —</option>
            <option>HILUX</option>
            <option>NPR</option>
            <option value="OTRO">OTRO</option>
          </select>
        </div>

        <div class="form-group">
          <label>Fecha</label>
          <input type="date" id="fecha">
        </div>

      </div>
    </div>
  </div>

  <!-- CONDUCTORES -->
  <div class="section">
    <div class="section-header"><span class="marker"></span>Conductores</div>
    <div class="section-body">
      <div class="form-grid grid-4">
        <div class="form-group">
          <label>Conductor 1</label>
          <select id="c1-select" onchange="onConductorChange(1)">
            <option value="">— Seleccionar —</option>
            <option>JHONSON, BALDEON SAJAMI</option>
            <option>JOSE LUIS, MAMANI PUMA</option>
            <option>JEAN MARCO, GAMARRA ALMIRON</option>
            <option value="OTRO">OTRO (ingresar)</option>
          </select>
        </div>
        <div class="form-group" id="c1-manual-wrap" style="display:none">
          <label>Nombre (manual)</label>
          <input type="text" id="c1-manual" placeholder="Apellidos, Nombres">
        </div>
        <div class="form-group">
          <label>DNI Conductor 1</label>
          <input type="text" id="c1-dni" placeholder="8 dígitos" maxlength="8">
        </div>
        <div class="form-group">
          <label>Conductor 2</label>
          <select id="c2-select" onchange="onConductorChange(2)">
            <option value="">— Seleccionar —</option>
            <option>JHONSON, BALDEON SAJAMI</option>
            <option>JOSE LUIS, MAMANI PUMA</option>
            <option>JEAN MARCO, GAMARRA ALMIRON</option>
            <option value="OTRO">OTRO (ingresar)</option>
          </select>
        </div>
        <div class="form-group" id="c2-manual-wrap" style="display:none">
          <label>Nombre (manual)</label>
          <input type="text" id="c2-manual" placeholder="Apellidos, Nombres">
        </div>
        <div class="form-group">
          <label>DNI Conductor 2</label>
          <input type="text" id="c2-dni" placeholder="8 dígitos" maxlength="8">
        </div>
      </div>
    </div>
  </div>

  <!-- COMBUSTIBLE -->
  <div class="section">
    <div class="section-header"><span class="marker"></span>Control de Combustible (B-5)</div>
    <div class="section-body">
      <div class="form-grid grid-fuel">
        <div class="form-group">
          <label>Inicial (gal)</label>
          <input type="number" id="fuel-ini" placeholder="0.0" step="0.01" min="0" oninput="calcFuel()">
        </div>
        <div class="form-group">
          <label>Abastecimiento (gal)</label>
          <input type="number" id="fuel-abast" placeholder="0.0" step="0.01" min="0" oninput="calcFuel()">
        </div>
        <div class="form-group">
          <label>Final (gal)</label>
          <input type="number" id="fuel-fin" placeholder="0.0" step="0.01" min="0" oninput="calcFuel()">
        </div>
        <div class="form-group">
          <label>Consumo (gal) — auto</label>
          <input type="number" id="fuel-consumo" readonly placeholder="—" step="0.01">
        </div>
        <div class="form-group">
          <label>N° Comprobante / Km. abastec.</label>
          <input type="text" id="fuel-comp" placeholder="N° comprobante">
        </div>
      </div>
    </div>
  </div>



  <div class="day-nav">
    <div class="day-nav-label" id="day-label">DÍA 1</div>

    <div class="days-nav" id="days-nav"></div>

    <button class="btn btn-navy btn-sm" onclick="addNewDay()">+ Día</button>
    <button class="btn btn-green btn-sm" onclick="saveDay()">💾 Guardar Día</button>

    <span class="save-indicator" id="days-save-indicator">GUARDADO</span>
</div>

  
  <!-- NAVEGADOR DÍAS -->
  <div class="day-nav"  style="display: none" >
    <div class="day-nav-label" id="day-label">DÍA 1</div>
    <div class="day-pills" id="day-pills"></div>
    <button class="btn btn-navy btn-sm" onclick="addNewDay()">+ Día</button>
    <button class="btn btn-green btn-sm" onclick="saveDay()">💾 Guardar Día</button>
    <span class="save-indicator" id="save-indicator">GUARDADO</span>
  </div>

  <!-- ACTIVIDADES -->
  <div class="section">
    <div class="section-header"><span class="marker"></span>Actividades del Día</div>
    <div class="section-body">
      <div class="act-scroll">
        <table class="act-table" id="act-table">
          <thead>
            <tr>
              <th style="text-align:center">#</th>
              <th style="min-width:170px">Frente de Trabajo</th>
              <th style="min-width:220px">Descripción del Trabajo</th>
              <th style="min-width:98px">Kilom. Inicial</th>
              <th style="min-width:98px">Kilom. Final</th>
              <th style="min-width:98px">Horom. Inic.</th>
              <th style="min-width:98px">Horom. Final</th>
              <th style="min-width:160px">Observaciones</th>
              <th style="min-width:36px"></th>
            </tr>
          </thead>
          <tbody id="act-body"></tbody>
        </table>
      </div>
      <div class="act-bar">
        <button class="btn btn-navy btn-sm" onclick="addActivity()">+ Agregar Actividad</button>
        <span id="act-count" style="font-size:0.75rem;color:var(--muted);font-family:'Oswald',sans-serif;letter-spacing:1px"></span>
      </div>
    </div>
  </div>

</div>
</div>

<!-- ================================================================ -->
<!-- TAB: RESUMEN -->
<!-- ================================================================ -->
<div id="tab-resumen" class="tab-panel">
<div class="container">

  <div class="stats-row" id="stats-row">
    <div class="stat-card">
      <div class="stat-label">Total Días</div>
      <div class="stat-value" id="st-dias">0</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Total Actividades</div>
      <div class="stat-value" id="st-acts">0</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Km Recorridos</div>
      <div class="stat-value" id="st-km">0</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Hrs Operadas</div>
      <div class="stat-value" id="st-hr">0</div>
    </div>
  </div>

  <div class="section">
    <div class="section-header"><span class="marker"></span>Resumen de Movimientos por Placa</div>
    <div class="section-body">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:8px">
        <span style="font-family:'Oswald',sans-serif;font-size:0.8rem;color:var(--muted);letter-spacing:1px" id="sum-count">Sin registros</span>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <button class="btn btn-gold btn-sm" onclick="exportExcel()">⬇ Descargar Excel (todas las placas)</button>
          <button class="btn btn-red btn-sm" onclick="clearAll()">🗑 Limpiar todo</button>
        </div>
      </div>

      <!-- PLATE TABS -->
      <div class="plate-tabs" id="plate-tabs"></div>

      <!-- TABLE WRAPPER -->
      <div class="sum-wrap">
        <div style="overflow-x:auto">
          <table class="sum-table" id="sum-table">
            <thead>
              <tr>
                <th>Día</th>
                <th>Zona Productiva</th>
                <th>Fecha</th>
                <th>Placa</th>
                <th>Equipo</th>
                <th>Modelo</th>
                <th>Conductor 1</th>
                <th>DNI C1</th>
                <th>Conductor 2</th>
                <th>DNI C2</th>
                <th>Comb. Ini (gal)</th>
                <th>Abastec. (gal)</th>
                <th>Comb. Fin (gal)</th>
                <th>Consumo (gal)</th>
                <th>Comprobante</th>
                <th>Act. #</th>
                <th>Frente de Trabajo</th>
                <th>Descripción</th>
                <th>Km Inicial</th>
                <th>Km Final</th>
                <th>Horom. Inic.</th>
                <th>Horom. Final</th>
                <th>Observaciones</th>
              </tr>
            </thead>
            <tbody id="sum-body">
              <tr><td colspan="23" class="no-data-cell">No hay registros guardados.</td></tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>
</div>

<div class="toast" id="toast"></div>

<script src="/assets/js/app.js"></script>

<!-- XLSX lib -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</body>
</html>
