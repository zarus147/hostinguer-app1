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
    contactos: contactos,  
    nContactos: Number(contactos.length),
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
// function limpiarTodo() {
//   var pass = prompt('🔒 Ingrese la contraseña para eliminar todos los registros:');
//   if(pass === null) return;
//   if(pass !== 'Spinocaj4'){
//     showToast('❌ Contraseña incorrecta. Operación cancelada.','err');
//     return;
//   }
//   if(confirm('⚠️ ¿Está seguro? Se eliminarán TODOS los registros del historial. Esta acción no se puede deshacer.')){
//     registros=[]; saveLS(); actualizarBitacora(); renderResumen();
//     showToast('✅ Historial eliminado correctamente.','info');
//   }
// }
function limpiarTodo() {

  var pass = prompt('🔒 Ingrese la contraseña para eliminar todos los registros:');

  if(pass === null) return;

  if(pass !== 'Spinocaj4'){
    showToast('❌ Contraseña incorrecta. Operación cancelada.','err');
    return;
  }

  if(!confirm('⚠️ ¿Está seguro? Se eliminarán TODOS los registros del historial.')) return;

  fetch('/api/visitas.php?all=true', {
    method: 'DELETE'
  })
  .then(res => res.json())
  .then(async response => {

    if(response.success){

      await cargarRegistros();

      actualizarBitacora();
      renderResumen();

      showToast('✅ Historial eliminado correctamente.','info');

    } else {

      showToast('Error al eliminar registros','err');

    }

  })
  .catch(err => {
    console.error(err);
    showToast('Error de conexión','err');
  });

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
  var contactos = registros.reduce(function(s,r){
    return s + Number(r.nContactos || 0);
  },0);
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
    //ws3Data.push([ag, d.visitas, d.contactos, d.zonas.size, d.criticos]);
    ws3Data.push([
      ag,
      Number(d.visitas),
      Number(d.contactos),
      Number(d.zonas.size),
      Number(d.criticos)
    ]);
  });
  // Fila totales
  // ws3Data.push(['TOTAL GENERAL',
  //   filtrados.length,
  //   filtrados.reduce(function(s,r){return s+(r.nContactos||0);},0),
  //   new Set(filtrados.map(function(r){return r.zona;})).size,
  //   filtrados.filter(function(r){return r.estado&&r.estado.includes('Crítico');}).length
  // ]);
  ws3Data.push([
    'TOTAL GENERAL',
    Number(filtrados.length),
    filtrados.reduce(function(s,r){
      return s + Number(r.nContactos || 0);
    },0),
    Number(new Set(filtrados.map(function(r){return r.zona;})).size),
    Number(filtrados.filter(function(r){
      return r.estado && r.estado.includes('Crítico');
    }).length)
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
    //ws4Data.push([z, d.visitas, d.agentes.size, d.contactos]);
    ws4Data.push([
      z,
      Number(d.visitas),
      Number(d.agentes.size),
      Number(d.contactos)
    ]);
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
  if(tab==='bitacora'){ await cargarRegistros(); actualizarBitacora();}
  if(tab==='resumen') renderResumen();
}

function showToast(msg,type){
  var t=document.getElementById('toast');
  t.textContent=msg; t.className='show '+(type||'ok');
  setTimeout(function(){t.classList.remove('show');},3500);
}