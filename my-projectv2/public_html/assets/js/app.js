/* ================================================================
   CONSTANTS
================================================================ */
const STORAGE_KEY = "sdla_conductor_v2";
const KNOWN_PLATES = ["W7U 853", "BNQ 902"];
const CONDUCTORS = [
    "JHONSON, BALDEON SAJAMI",
    "JOSE LUIS, MAMANI PUMA",
    "JEAN MARCO, GAMARRA ALMIRON",
];

let currentIds = {
    vehiculoId: null,
    bitacoraId: null
};

/* ================================================================
   STATE
   masterData structure:
   {
     "W7U 853": { totalDays: N, days: { 1: {header, activities}, ... } },
     "BNQ 902": { ... },
     "OTRO_ABC123": { ... }
   }
================================================================ */
let masterData = {}; // all stored data keyed by plate
let summaryData = {};
let currentPlate = ""; // currently selected plate key
let currentDay = 1;
let activePlateTab = ""; // which plate tab is shown in summary

/* ================================================================
   PERSISTENCE
================================================================ */
function persistSave() {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(masterData));
    } catch (e) {
        console.warn("Storage error", e);
    }
}

function persistLoad() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (raw) masterData = JSON.parse(raw);
    } catch (e) {
        masterData = {};
    }
}

/* ================================================================
   INIT
================================================================ */
document.addEventListener("DOMContentLoaded", () => {
    persistLoad();
    document.getElementById("fecha").value = new Date()
        .toISOString()
        .slice(0, 10);
    renderDayPills();
    renderActivities();
});

/* ================================================================
   TABS
================================================================ */
async function switchTab(name, btn) {
    document
        .querySelectorAll(".tab-panel")
        .forEach((p) => p.classList.remove("active"));
    document
        .querySelectorAll(".tab-btn")
        .forEach((b) => b.classList.remove("active"));
    document.getElementById("tab-" + name).classList.add("active");
    btn.classList.add("active");
    // if (name === "resumen") renderSummary();
    if (name === "resumen") {
        await loadMasterFromAPI();
        renderSummary();
    }
}

/* ================================================================
   PLACA CHANGE — core of persistence logic
================================================================ */
function getPlateKey() {
    const sel = document.getElementById("placa").value;
    if (sel === "OTRO") {
        const manual = document
            .getElementById("placa-manual")
            .value.trim()
            .toUpperCase();
        return manual ? "OTRO_" + manual : "";
    }
    return sel;
}

// function onPlacaChange() {
//   const val = document.getElementById("placa").value;
//   document.getElementById("placa-manual-wrap").style.display =
//     val === "OTRO" ? "" : "none";
//   if (val !== "OTRO") applyPlateData(val);
// }

function onPlacaChange() {

    const placa = document.getElementById("placa").value;

    document.getElementById("placa-manual-wrap").style.display = placa === "OTRO" ? "" : "none";
    if (placa !== "OTRO") applyPlateData(placa);

    if (!placa) {
        resetForm();
        const nav = document.getElementById("days-nav");
        if (nav) nav.innerHTML = "";

        return;
    }

    currentPlate = placa;

    currentDay = 1;

    masterData = {};
    const nav = document.getElementById("days-nav");

    if (nav) nav.innerHTML = "";
    loadFirstDay(placa);
}

/* =========================
   RESET FORM
========================= */
function resetForm() {
    document.getElementById("zona").value = "";

    document.getElementById("modelo").value = "";
    document.getElementById("equipo").value = "";

    document.getElementById("c1-select").value = "";
    document.getElementById("c1-dni").value = "";
    document.getElementById("c2-select").value = "";
    document.getElementById("c2-dni").value = "";

    document.getElementById("fuel-ini").value = "";
    document.getElementById("fuel-abast").value = "";
    document.getElementById("fuel-fin").value = "";
    document.getElementById("fuel-consumo").value = "";
    document.getElementById("fuel-comp").value = "";

    renderActivities([]);

    currentIds = {
        vehiculoId: null,
        bitacoraId: null
    };

}

function onPlacaManualChange() {
    const key = getPlateKey();
    if (key) applyPlateData(key);
}

function applyPlateData(plateKey) {
    if (!plateKey) return;

    // Save current plate data before switching
    if (currentPlate && currentPlate !== plateKey) {
        autoSaveCurrent();
    }

    currentPlate = plateKey;
    updatePlacaIndicator();

    if (masterData[plateKey]) {
        // Load existing data for this plate
        const pd = masterData[plateKey];
        const totalDays = Object.keys(pd.days || {}).length || 1;

        // Update day tracking
        currentDay = totalDays; // go to last day
        renderDayPills();

        // Load last day's data into form
        const dayKeys = Object.keys(pd.days || {})
            .map(Number)
            .sort((a, b) => a - b);
        if (dayKeys.length > 0) {
            const lastDay = dayKeys[dayKeys.length - 1];
            currentDay = lastDay;
            loadDayIntoForm(plateKey, lastDay);
            renderDayPills();
        }

        toast(
            `✓ Datos de ${plateKey} cargados (${dayKeys.length} día${dayKeys.length !== 1 ? "s" : ""})`,
        );
    } else {
        // New plate — reset form day to 1
        currentDay = 1;
        clearForm(false); // don't clear placa fields
        renderDayPills();
        renderActivities();
    }
}

function updatePlacaIndicator() {
    const wrap = document.getElementById("placa-indicator-wrap");
    if (!currentPlate) {
        wrap.innerHTML = "";
        return;
    }
    const hasData = !!masterData[currentPlate];
    const days = hasData
        ? Object.keys(masterData[currentPlate].days || {}).length
        : 0;
    wrap.innerHTML = `
    <div class="placa-indicator">
      <span class="pi-dot"></span>
      PLACA ACTIVA: ${currentPlate}
      ${hasData ? `&nbsp;|&nbsp; ${days} día${days !== 1 ? "s" : ""} registrado${days !== 1 ? "s" : ""}` : "&nbsp;|&nbsp; NUEVO REGISTRO"}
    </div>`;
}

/* ================================================================
   CONDUCTOR CHANGE
================================================================ */
function onConductorChange(n) {
    const v = document.getElementById(`c${n}-select`).value;
    document.getElementById(`c${n}-manual-wrap`).style.display =
        v === "OTRO" ? "" : "none";
}

/* ================================================================
   FUEL
================================================================ */
function calcFuel() {
    const ini = parseFloat(document.getElementById("fuel-ini").value) || 0;
    const abast = parseFloat(document.getElementById("fuel-abast").value) || 0;
    const fin = parseFloat(document.getElementById("fuel-fin").value) || 0;
    const c = ini + abast - fin;
    document.getElementById("fuel-consumo").value = c >= 0 ? c.toFixed(2) : "";
}

/* ================================================================
   DAY NAVIGATION
================================================================ */
function getTotalDays() {
    if (!currentPlate || !masterData[currentPlate]) return 1;
    const keys = Object.keys(masterData[currentPlate].days || {});
    return keys.length > 0 ? Math.max(...keys.map(Number)) : 1;
}

function renderDayPills() {
    const container = document.getElementById("day-pills");
    container.innerHTML = "";
    const total = Math.max(getTotalDays(), currentDay);
    for (let i = 1; i <= total; i++) {
        const hasData = !!(currentPlate && masterData[currentPlate]?.days?.[i]);
        const btn = document.createElement("button");
        btn.className =
            "day-pill" +
            (i === currentDay ? " active" : "") +
            (hasData ? " has-data" : "");
        btn.textContent = i;
        btn.onclick = () => goToDay(i);
        container.appendChild(btn);
    }
    document.getElementById("day-label").textContent = `DÍA ${currentDay}`;
}

function addDay() {
    if (!currentPlate) {
        toast("⚠ Seleccione una placa primero");
        return;
    }
    autoSaveCurrent();
    const newDay = getTotalDays() + 1;
    currentDay = newDay;
    // Inherit km/horom from last activity of previous day
    initNewDay(newDay);
    renderDayPills();
    renderActivities();
    toast(`Día ${newDay} agregado`);
}



function goToDay(day) {
    if (!currentPlate) {
        toast("⚠ Seleccione una placa primero");
        return;
    }
    autoSaveCurrent();
    currentDay = day;
    if (masterData[currentPlate]?.days?.[day]) {
        loadDayIntoForm(currentPlate, day);
    } else {
        initNewDay(day);
    }
    renderDayPills();
    renderActivities();
}

function initNewDay(day) {
    // Try to inherit from previous day's last activity
    const prevDay = day - 1;
    const prevActs = masterData[currentPlate]?.days?.[prevDay]?.activities || [];
    let kmIni = "",
        hrIni = "";
    if (prevActs.length > 0) {
        const last = prevActs[prevActs.length - 1];
        kmIni = last.kmFin || "";
        hrIni = last.hrFin || "";
    }
    // Store as initial day data with inherited values
    if (!masterData[currentPlate]) masterData[currentPlate] = { days: {} };
    masterData[currentPlate].days[day] = {
        header: { ...getHeaderValues(), fecha: "" },
        activities: [
            {
                frente: "",
                desc: "",
                kmIni,
                kmFin: "",
                hrIni,
                hrFin: "",
                obs: "",
                _inherited: true,
            },
        ],
    };
    document.getElementById("fecha").value = "";
}

/* ================================================================
   SAVE / LOAD
================================================================ */
function getHeaderValues() {
    const placaSel = document.getElementById("placa").value;
    const placa =
        placaSel === "OTRO"
            ? document.getElementById("placa-manual").value.trim()
            : placaSel;
    const c1sel = document.getElementById("c1-select").value;
    const c1 =
        c1sel === "OTRO" ? document.getElementById("c1-manual").value : c1sel;
    const c2sel = document.getElementById("c2-select").value;
    const c2 =
        c2sel === "OTRO" ? document.getElementById("c2-manual").value : c2sel;
    return {
        vehiculoId: currentIds?.vehiculoId ?? null,
        bitacoraId: currentIds?.bitacoraId ?? null,
        zona: document.getElementById("zona").value,
        placa,
        equipo: document.getElementById("equipo").value,
        modelo: document.getElementById("modelo").value,
        fecha: document.getElementById("fecha").value,
        conductor1: c1,
        dni1: document.getElementById("c1-dni").value,
        conductor2: c2,
        dni2: document.getElementById("c2-dni").value,
        fuelIni: document.getElementById("fuel-ini").value,
        fuelAbast: document.getElementById("fuel-abast").value,
        fuelFin: document.getElementById("fuel-fin").value,
        fuelConsumo: document.getElementById("fuel-consumo").value,
        fuelComp: document.getElementById("fuel-comp").value,
    };
}

function setHeaderValues(h) {
    if (!h) return;
    // Zona
    document.getElementById("zona").value = h.zona || "";
    // Placa
    if (KNOWN_PLATES.includes(h.placa)) {
        document.getElementById("placa").value = h.placa;
        document.getElementById("placa-manual-wrap").style.display = "none";
    } else if (h.placa) {
        document.getElementById("placa").value = "OTRO";
        document.getElementById("placa-manual").value = h.placa;
        document.getElementById("placa-manual-wrap").style.display = "";
    }
    document.getElementById("equipo").value = h.equipo || "";
    document.getElementById("modelo").value = h.modelo || "";
    document.getElementById("fecha").value = h.fecha || "";
    // C1
    if (CONDUCTORS.includes(h.conductor1)) {
        document.getElementById("c1-select").value = h.conductor1;
        document.getElementById("c1-manual-wrap").style.display = "none";
    } else if (h.conductor1) {
        document.getElementById("c1-select").value = "OTRO";
        document.getElementById("c1-manual").value = h.conductor1;
        document.getElementById("c1-manual-wrap").style.display = "";
    }
    document.getElementById("c1-dni").value = h.dni1 || "";
    // C2
    if (CONDUCTORS.includes(h.conductor2)) {
        document.getElementById("c2-select").value = h.conductor2;
        document.getElementById("c2-manual-wrap").style.display = "none";
    } else if (h.conductor2) {
        document.getElementById("c2-select").value = "OTRO";
        document.getElementById("c2-manual").value = h.conductor2;
        document.getElementById("c2-manual-wrap").style.display = "";
    }
    document.getElementById("c2-dni").value = h.dni2 || "";
    document.getElementById("fuel-ini").value = h.fuelIni || "";
    document.getElementById("fuel-abast").value = h.fuelAbast || "";
    document.getElementById("fuel-fin").value = h.fuelFin || "";
    document.getElementById("fuel-consumo").value = h.fuelConsumo || "";
    document.getElementById("fuel-comp").value = h.fuelComp || "";
}

function getActivitiesFromDOM() {
    const rows = document.querySelectorAll("#act-body tr");
    return Array.from(rows).map((row) => {
        const inputs = row.querySelectorAll("input");
        const txts = row.querySelectorAll("textarea");
        return {
            frente: txts[0]?.value || "",
            desc: txts[1]?.value || "",
            kmIni: inputs[0]?.value || "",
            kmFin: inputs[1]?.value || "",
            hrIni: inputs[2]?.value || "",
            hrFin: inputs[3]?.value || "",
            obs: txts[2]?.value || "",
        };
    });
}

function autoSaveCurrent() {
    if (!currentPlate) return;
    if (!masterData[currentPlate]) masterData[currentPlate] = { days: {} };
    masterData[currentPlate].days[currentDay] = {
        header: getHeaderValues(),
        activities: getActivitiesFromDOM(),
    };
}

// function saveDay() {
//   if (!currentPlate) { toast('⚠ Seleccione una placa primero'); return; }
//   autoSaveCurrent();
//   updatePlacaIndicator();
//   renderDayPills();
//   showSaveIndicator();
//   toast(`✓ Día ${currentDay} guardado — Placa: ${currentPlate}`);
// }
async function saveDay() {
    if (!currentPlate) {
        toast("⚠ Seleccione una placa primero");
        return;
    }

    autoSaveCurrent();

    const editingDay = currentDay;

    const data = {
        header: getHeaderValues(),
        activities: getActivitiesFromDOM()
    };

    const isNewDay = !data.header.bitacoraId;
    try {

        const res = await fetch("/api/reportes.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(data),
        });

        // const text = await res.text();
        // console.log("RAW RESPONSE:", text);

        const response = await res.json();

        if (response.status) {

            updatePlacaIndicator();
            renderDayPills();
            showSaveIndicator();

            // actualizar ids que devuelve el backend
            currentIds.bitacoraId = response.bitacoraId;
            currentIds.vehiculoId = response.vehiculoId;

            const days = await loadVehicleDays(currentPlate);

            if (isNewDay) {
                showDay(days.length);
            } else {
                showDay(editingDay);
            }

            toast("✓ Datos guardados en la base de datos");

        } else {

            toast("❌ Error al guardar en la base de datos");

        }

    } catch (err) {

        console.error(err);
        toast("❌ Error de conexión con el servidor");

    }
}



async function loadFirstDay(placa) {

    const days = await loadVehicleDays(placa);

    if (!days || days.length === 0) {
        resetForm();
        return;
    }

    currentPlate = placa;

    showDay(1);
}

async function loadVehicleDays(placa) {

    const res = await fetch(`/api/reportes.php?firstDay=${encodeURIComponent(placa)}`);
    const data = await res.json();

    if (!data || !data.days) return null;

    const days = data.days;

    // guardar ids globales
    currentIds.vehiculoId = data.vehiculoId;

    // reconstruir masterData
    if (!masterData[placa]) masterData[placa] = { days: {} };

    days.forEach((d, i) => {

        const h = d.header;

        masterData[placa].days[i + 1] = {
            header: {
                vehiculoId: data.vehiculoId,
                bitacoraId: d.bitacoraId,
                ...h
            },
            activities: (d.activities || []).map(a => ({
                frente: a.frente,
                desc: a.descripcion,
                kmIni: a.kmIni,
                kmFin: a.kmFin,
                hrIni: a.hrIni,
                hrFin: a.hrFin,
                obs: a.observaciones
            }))
        };

    });

    renderDayButtons(days);

    return days;
}

function showDay(dayNumber) {

    const d = masterData[currentPlate]?.days?.[dayNumber];
    if (!d) return;

    const h = d.header;

    document.getElementById("zona").value = h.zona || "";
    document.getElementById("modelo").value = h.modelo || "";
    document.getElementById("equipo").value = h.equipo || "";

    document.getElementById("c1-select").value = h.conductor1 || "";
    document.getElementById("c1-dni").value = h.dni1 || "";
    document.getElementById("c2-select").value = h.conductor2 || "";
    document.getElementById("c2-dni").value = h.dni2 || "";

    document.getElementById("fuel-ini").value = h.fuelIni || "";
    document.getElementById("fuel-abast").value = h.fuelAbast || "";
    document.getElementById("fuel-fin").value = h.fuelFin || "";
    document.getElementById("fuel-consumo").value = h.fuelConsumo || "";
    document.getElementById("fuel-comp").value = h.fuelComp || "";

    currentIds.bitacoraId = h.bitacoraId;

    currentDay = dayNumber;

    renderActivities();
    renderDayPills();

}




function renderDayButtons(days) {

    const nav = document.getElementById("days-nav");
    if (!nav) return;

    nav.innerHTML = "";

    days.forEach((day, index) => {

        const dayNumber = index + 1;

        const btn = document.createElement("button");
        btn.className = "day-btn";

        btn.textContent = dayNumber;

        if (dayNumber === currentDay) {
            btn.classList.add("active");
        }

        btn.onclick = () => loadDay(day, dayNumber, days);

        nav.appendChild(btn);

    });

}

function loadDay(day, dayNumber, allDays) {

    const h = day.header;

    document.getElementById("zona").value = h.zona || "";
    document.getElementById("modelo").value = h.modelo || "";
    document.getElementById("equipo").value = h.equipo || "";

    document.getElementById("c1-select").value = h.conductor1 || "";
    document.getElementById("c1-dni").value = h.dni1 || "";

    document.getElementById("c2-select").value = h.conductor2 || "";
    document.getElementById("c2-dni").value = h.dni2 || "";

    document.getElementById("fuel-ini").value = h.fuelIni || "";
    document.getElementById("fuel-abast").value = h.fuelAbast || "";
    document.getElementById("fuel-fin").value = h.fuelFin || "";
    document.getElementById("fuel-consumo").value = h.fuelConsumo || "";
    document.getElementById("fuel-comp").value = h.fuelComp || "";

    masterData[currentPlate].days[dayNumber] = {
        activities: (day.activities || []).map(a => ({
            frente: a.frente,
            desc: a.descripcion,
            kmIni: a.kmIni,
            kmFin: a.kmFin,
            hrIni: a.hrIni,
            hrFin: a.hrFin,
            obs: a.observaciones
        }))
    };

    currentDay = dayNumber;
    renderDayPills();

    renderActivities();

    currentIds.bitacoraId = day.bitacoraId;

    // actualizar botón activo
    document.querySelectorAll(".day-btn").forEach(b => b.classList.remove("active"));
    document.querySelectorAll(".day-btn")[dayNumber - 1].classList.add("active");
}


function addNewDay() {

    if (!currentPlate) {
        toast("⚠ Seleccione una placa primero");
        return;
    }

    const nav = document.getElementById("days-nav");
    //const nav = document.getElementById("day-pills");
    const nextDay = document.querySelectorAll(".day-btn").length + 1;

    const btn = document.createElement("button");
    btn.className = "day-btn active";
    btn.textContent = nextDay;

    btn.onclick = () => loadDay({
        header: {},
        activities: []
    }, nextDay);

    // quitar active de los otros
    document.querySelectorAll(".day-btn").forEach(b => b.classList.remove("active"));

    nav.appendChild(btn);

    currentDay = nextDay;

    currentIds.bitacoraId = null;

    if (!masterData[currentPlate]) {
        masterData[currentPlate] = { days: {} };
    }

    clearDayFields();

    masterData[currentPlate].days[currentDay] = {
        header: {},
        activities: []
    };
    renderDayPills();
}

function clearDayFields() {
    const ids = [
        "zona", "c1-select", "c1-dni", "c2-select", "c2-dni",
        "fuel-ini", "fuel-abast", "fuel-fin", "fuel-consumo", "fuel-comp"
    ];

    ids.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = "";
    });

    renderActivities([]);
}



function showSaveIndicator() {
    const el = document.getElementById("save-indicator");
    el.classList.add("visible");
    setTimeout(() => el.classList.remove("visible"), 2500);
}

function loadDayIntoForm(plateKey, day) {
    const d = masterData[plateKey]?.days?.[day];
    if (!d) return;
    setHeaderValues(d.header);
    // Activities will be rendered by renderActivities()
}

/* ================================================================
   ACTIVITIES
================================================================ */
function renderActivities() {
    const tbody = document.getElementById("act-body");
    tbody.innerHTML = "";
    const acts =
        currentPlate && masterData[currentPlate]?.days?.[currentDay]?.activities;
    if (!acts || acts.length === 0) {
        addActivity();
        return;
    }
    acts.forEach((act, i) => buildRow(i + 1, act));
    updateActCount();
}

function buildRow(num, act) {
    const tbody = document.getElementById("act-body");
    const tr = document.createElement("tr");
    const inh = act._inherited || false;
    tr.innerHTML = `
    <td class="row-num">${num}</td>
    <td><textarea style="width:100%;min-height:38px;font-size:0.82rem" placeholder="Frente de trabajo...">${esc(act.frente)}</textarea></td>
    <td><textarea style="width:100%;min-height:38px;font-size:0.82rem" placeholder="Descripción del trabajo...">${esc(act.desc)}</textarea></td>
    <td><input type="number" value="${esc(act.kmIni)}" placeholder="Km." step="0.1"
        class="${inh && act.kmIni ? "inherited" : ""}"
        oninput="propagateKm(this)" style="width:100%"></td>
    <td><input type="number" value="${esc(act.kmFin)}" placeholder="Km." step="0.1"
        oninput="onKmFinalInput(this)" style="width:100%"></td>
    <td><input type="number" value="${esc(act.hrIni)}" placeholder="Hr." step="0.01"
        class="${inh && act.hrIni ? "inherited" : ""}"
        oninput="propagateHr(this)" style="width:100%"></td>
    <td><input type="number" value="${esc(act.hrFin)}" placeholder="Hr." step="0.01"
        oninput="onHrFinalInput(this)" style="width:100%"></td>
    <td><textarea style="width:100%;min-height:38px;font-size:0.82rem" placeholder="Observaciones (opcional)">${esc(act.obs)}</textarea></td>
    <td style="text-align:center">
      <button class="btn btn-red btn-sm" onclick="removeRow(this)" title="Eliminar">✕</button>
    </td>`;
    tbody.appendChild(tr);
    updateActCount();
}

function addActivity() {
    const tbody = document.getElementById("act-body");
    const rows = tbody.querySelectorAll("tr");
    let kmIni = "",
        hrIni = "",
        isInherited = false;

    if (rows.length > 0) {
        const prevInputs = rows[rows.length - 1].querySelectorAll("input");
        kmIni = prevInputs[1]?.value || "";
        hrIni = prevInputs[3]?.value || "";
        isInherited = true;
    } else if (currentDay > 1 && currentPlate) {
        const prevDayActs =
            masterData[currentPlate]?.days?.[currentDay - 1]?.activities || [];
        if (prevDayActs.length > 0) {
            const last = prevDayActs[prevDayActs.length - 1];
            kmIni = last.kmFin || "";
            hrIni = last.hrFin || "";
            isInherited = true;
        }
    }

    buildRow(rows.length + 1, {
        frente: "",
        desc: "",
        kmIni,
        kmFin: "",
        hrIni,
        hrFin: "",
        obs: "",
        _inherited: isInherited,
    });
    renumberRows();
}

function onKmFinalInput(input) {
    // propagate to next row's km initial
    const row = input.closest("tr");
    const next = row.nextElementSibling;
    if (next) {
        const nextIn = next.querySelectorAll("input")[0];
        if (nextIn) {
            nextIn.value = input.value;
            nextIn.classList.add("inherited");
        }
    }
}

function onHrFinalInput(input) {
    const row = input.closest("tr");
    const next = row.nextElementSibling;
    if (next) {
        const nextIn = next.querySelectorAll("input")[2];
        if (nextIn) {
            nextIn.value = input.value;
            nextIn.classList.add("inherited");
        }
    }
}

function propagateKm(input) {
    /* handled by onKmFinalInput on the final field */
}
function propagateHr(input) {
    /* handled by onHrFinalInput on the final field */
}

function removeRow(btn) {
    btn.closest("tr").remove();
    renumberRows();
    updateActCount();
    recalcInherited();
}

function renumberRows() {
    document.querySelectorAll("#act-body tr").forEach((r, i) => {
        const c = r.querySelector(".row-num");
        if (c) c.textContent = i + 1;
    });
}

function recalcInherited() {
    const rows = document.querySelectorAll("#act-body tr");
    rows.forEach((row, i) => {
        if (i === 0) return;
        const prevInputs = rows[i - 1].querySelectorAll("input");
        const curInputs = row.querySelectorAll("input");
        if (curInputs[0]) {
            curInputs[0].value = prevInputs[1]?.value || "";
            curInputs[0].classList.toggle("inherited", !!prevInputs[1]?.value);
        }
        if (curInputs[2]) {
            curInputs[2].value = prevInputs[3]?.value || "";
            curInputs[2].classList.toggle("inherited", !!prevInputs[3]?.value);
        }
    });
}

function updateActCount() {
    const n = document.querySelectorAll("#act-body tr").length;
    document.getElementById("act-count").textContent =
        `${n} actividad${n !== 1 ? "es" : ""}`;
}

function clearForm(clearPlate = true) {
    if (clearPlate) {
        document.getElementById("placa").value = "";
        document.getElementById("placa-manual-wrap").style.display = "none";
        document.getElementById("placa-manual").value = "";
    }
    document.getElementById("zona").value = "";
    document.getElementById("equipo").value = "";
    document.getElementById("modelo").value = "";
    document.getElementById("fecha").value = new Date()
        .toISOString()
        .slice(0, 10);
    document.getElementById("c1-select").value = "";
    document.getElementById("c1-manual-wrap").style.display = "none";
    document.getElementById("c1-manual").value = "";
    document.getElementById("c1-dni").value = "";
    document.getElementById("c2-select").value = "";
    document.getElementById("c2-manual-wrap").style.display = "none";
    document.getElementById("c2-manual").value = "";
    document.getElementById("c2-dni").value = "";
    document.getElementById("fuel-ini").value = "";
    document.getElementById("fuel-abast").value = "";
    document.getElementById("fuel-fin").value = "";
    document.getElementById("fuel-consumo").value = "";
    document.getElementById("fuel-comp").value = "";
    document.getElementById("act-body").innerHTML = "";
}

function esc(v) {
    if (v == null) return "";
    return String(v)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

/* ================================================================
   SUMMARY
================================================================ */
function buildRowsForPlate(plateKey) {
    const pd = masterData[plateKey];
    if (!pd) return [];
    const rows = [];
    const dayNums = Object.keys(pd.days || {})
        .map(Number)
        .sort((a, b) => a - b);
    dayNums.forEach((day) => {
        const d = pd.days[day];
        const h = d.header;
        const acts = d.activities || [];
        if (acts.length === 0) {
            rows.push({
                day,
                zona: h.zona,
                fecha: h.fecha,
                placa: h.placa,
                equipo: h.equipo,
                modelo: h.modelo,
                c1: h.conductor1,
                dni1: h.dni1,
                c2: h.conductor2,
                dni2: h.dni2,
                fIni: h.fuelIni,
                fAbast: h.fuelAbast,
                fFin: h.fuelFin,
                fConsumo: h.fuelConsumo,
                fComp: h.fuelComp,
                actNum: "—",
                frente: "",
                desc: "",
                kmIni: "",
                kmFin: "",
                hrIni: "",
                hrFin: "",
                obs: "",
            });
        } else {
            acts.forEach((act, i) => {
                rows.push({
                    day,
                    zona: h.zona,
                    fecha: h.fecha,
                    placa: h.placa,
                    equipo: h.equipo,
                    modelo: h.modelo,
                    c1: h.conductor1,
                    dni1: h.dni1,
                    c2: h.conductor2,
                    dni2: h.dni2,
                    fIni: h.fuelIni,
                    fAbast: h.fuelAbast,
                    fFin: h.fuelFin,
                    fConsumo: h.fuelConsumo,
                    fComp: h.fuelComp,
                    actNum: i + 1,
                    frente: act.frente,
                    desc: act.desc,
                    kmIni: act.kmIni,
                    kmFin: act.kmFin,
                    hrIni: act.hrIni,
                    hrFin: act.hrFin,
                    obs: act.obs,
                });
            });
        }
    });
    return rows;
}

async function loadMasterFromAPI() {
    try {
        const res = await fetch("/api/reportes.php?resumen=1");
        const data = await res.json();

        masterData = transformData(data);

    } catch (err) {
        console.error(err);
        toast("❌ Error cargando datos");
    }
}

function transformData(apiData) {
    const result = {};

    apiData.forEach(item => {
        const placa = item.placa;

        if (!result[placa]) {
            result[placa] = { days: {} };
        }

        const days = item.days || {};

        let i = 1;
        Object.values(days).forEach(day => {
            result[placa].days[i] = day;
            i++;
        });
    });

    return result;
}

function renderSummary() {
    autoSaveCurrent();

    const plates = Object.keys(masterData).sort();
    if (!activePlateTab || !plates.includes(activePlateTab)) {
        activePlateTab = plates[0] || "";
    }

    // Global stats
    let totalDaysCount = 0,
        totalActs = 0,
        totalKm = 0,
        totalHr = 0;
    plates.forEach((pk) => {
        const pd = masterData[pk];
        const days = Object.keys(pd.days || {});
        totalDaysCount += days.length;
        days.forEach((d) => {
            const acts = pd.days[d].activities || [];
            totalActs += acts.length;
            acts.forEach((a) => {
                const kd = (parseFloat(a.kmFin) || 0) - (parseFloat(a.kmIni) || 0);
                const hd = (parseFloat(a.hrFin) || 0) - (parseFloat(a.hrIni) || 0);
                if (kd > 0) totalKm += kd;
                if (hd > 0) totalHr += hd;
            });
        });
    });
    document.getElementById("st-dias").textContent = totalDaysCount;
    document.getElementById("st-acts").textContent = totalActs;
    document.getElementById("st-km").textContent = totalKm.toFixed(1);
    document.getElementById("st-hr").textContent = totalHr.toFixed(1);

    // Plate tabs
    const tabsContainer = document.getElementById("plate-tabs");
    tabsContainer.innerHTML = "";

    if (plates.length === 0) {
        document.getElementById("sum-count").textContent =
            "Sin registros guardados";
        document.getElementById("sum-body").innerHTML =
            `<tr><td colspan="23" class="no-data-cell">No hay registros guardados. Complete el formulario y presione "Guardar Día".</td></tr>`;
        return;
    }

    // ALL tab
    const allCount = Object.values(masterData).reduce(
        (s, pd) =>
            s +
            Object.values(pd.days || {}).reduce(
                (ss, d) => ss + (d.activities || []).length,
                0,
            ),
        0,
    );
    const allTab = document.createElement("button");
    allTab.className =
        "plate-tab" + (activePlateTab === "__ALL__" ? " active" : "");
    allTab.innerHTML = `TODAS <span class="count-badge">${allCount}</span>`;
    allTab.onclick = () => {
        activePlateTab = "__ALL__";
        renderSummary();
    };
    tabsContainer.appendChild(allTab);

    plates.forEach((pk) => {
        const rowCount = buildRowsForPlate(pk).length;
        const btn = document.createElement("button");
        btn.className = "plate-tab" + (activePlateTab === pk ? " active" : "");
        const label = pk.startsWith("OTRO_") ? pk.replace("OTRO_", "") : pk;
        btn.innerHTML = `${label} <span class="count-badge">${rowCount}</span>`;
        btn.onclick = () => {
            activePlateTab = pk;
            renderSummary();
        };
        tabsContainer.appendChild(btn);
    });

    // Build table rows
    let rows = [];
    if (activePlateTab === "__ALL__") {
        plates.forEach((pk) => rows.push(...buildRowsForPlate(pk)));
    } else if (activePlateTab) {
        rows = buildRowsForPlate(activePlateTab);
    }

    document.getElementById("sum-count").textContent =
        `${rows.length} registro${rows.length !== 1 ? "s" : ""} — ${activePlateTab === "__ALL__" ? "Todas las placas" : activePlateTab.startsWith("OTRO_") ? activePlateTab.replace("OTRO_", "") : activePlateTab}`;

    const tbody = document.getElementById("sum-body");
    if (rows.length === 0) {
        tbody.innerHTML = `<tr><td colspan="23" class="no-data-cell">Sin registros para esta placa.</td></tr>`;
        return;
    }

    tbody.innerHTML = "";
    rows.forEach((r) => {
        const tr = document.createElement("tr");
        tr.innerHTML = [
            r.day,
            r.zona,
            r.fecha,
            r.placa,
            r.equipo,
            r.modelo,
            r.c1,
            r.dni1,
            r.c2,
            r.dni2,
            r.fIni,
            r.fAbast,
            r.fFin,
            r.fConsumo,
            r.fComp,
            r.actNum,
            r.frente,
            r.desc,
            r.kmIni,
            r.kmFin,
            r.hrIni,
            r.hrFin,
            r.obs,
        ]
            .map((v) => `<td>${esc(v)}</td>`)
            .join("");
        tbody.appendChild(tr);
    });
}

/* ================================================================
   EXPORT EXCEL  — one sheet per plate + one "TODAS"
================================================================ */
function exportExcel() {
    autoSaveCurrent();

    const script = document.createElement("script");
    script.src =
        "https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js";
    script.onload = doExport;
    if (typeof XLSX !== "undefined") {
        doExport();
        return;
    }
    document.head.appendChild(script);
}

function doExport() {
    const wb = XLSX.utils.book_new();
    const HEADERS = [
        "Día",
        "Zona Productiva",
        "Fecha",
        "Placa",
        "Equipo",
        "Modelo",
        "Conductor 1",
        "DNI C1",
        "Conductor 2",
        "DNI C2",
        "Comb. Ini (gal)",
        "Abastec. (gal)",
        "Comb. Fin (gal)",
        "Consumo (gal)",
        "Comprobante",
        "Act. #",
        "Frente de Trabajo",
        "Descripción del Trabajo",
        "Km Inicial",
        "Km Final",
        "Horom. Inic.",
        "Horom. Final",
        "Observaciones",
    ];

    const WIDTHS = [
        4, 20, 12, 10, 12, 8, 28, 10, 28, 10, 12, 12, 12, 12, 16, 6, 24, 32, 10, 10,
        10, 10, 32,
    ];

    const plates = Object.keys(masterData).sort();
    const allRows = [];

    plates.forEach((pk) => {
        const rows = buildRowsForPlate(pk);
        const wsData = [
            HEADERS,
            ...rows.map((r) => [
                r.day,
                r.zona,
                r.fecha,
                r.placa,
                r.equipo,
                r.modelo,
                r.c1,
                r.dni1,
                r.c2,
                r.dni2,
                r.fIni,
                r.fAbast,
                r.fFin,
                r.fConsumo,
                r.fComp,
                r.actNum,
                r.frente,
                r.desc,
                r.kmIni,
                r.kmFin,
                r.hrIni,
                r.hrFin,
                r.obs,
            ]),
        ];
        const ws = XLSX.utils.aoa_to_sheet(wsData);
        ws["!cols"] = WIDTHS.map((w) => ({ wch: w }));
        const sheetName = (
            pk.startsWith("OTRO_") ? pk.replace("OTRO_", "") : pk
        ).substring(0, 31);
        XLSX.utils.book_append_sheet(wb, ws, sheetName);
        allRows.push(...rows);
    });

    // Sheet "TODAS"
    if (allRows.length > 0) {
        const wsAllData = [
            HEADERS,
            ...allRows.map((r) => [
                r.day,
                r.zona,
                r.fecha,
                r.placa,
                r.equipo,
                r.modelo,
                r.c1,
                r.dni1,
                r.c2,
                r.dni2,
                r.fIni,
                r.fAbast,
                r.fFin,
                r.fConsumo,
                r.fComp,
                r.actNum,
                r.frente,
                r.desc,
                r.kmIni,
                r.kmFin,
                r.hrIni,
                r.hrFin,
                r.obs,
            ]),
        ];
        const wsAll = XLSX.utils.aoa_to_sheet(wsAllData);
        wsAll["!cols"] = WIDTHS.map((w) => ({ wch: w }));
        XLSX.utils.book_append_sheet(wb, wsAll, "TODAS LAS PLACAS");
    }

    const now = new Date();
    const fname = `SDLA_Conductor_${now.getFullYear()}${String(now.getMonth() + 1).padStart(2, "0")}${String(now.getDate()).padStart(2, "0")}.xlsx`;
    XLSX.writeFile(wb, fname);
    toast(
        `✓ Excel descargado (${plates.length} placa${plates.length !== 1 ? "s" : ""})`,
    );
}

/* ================================================================
   CLEAR ALL
================================================================ */
async function clearAll() {
    if (
        !confirm(
            "¿Eliminar TODOS los registros guardados de TODAS las placas?\n\nEsta acción no se puede deshacer.",
        )
    ) return;

    try {
        const response = await fetch("/api/reportes.php?all=1", {
            method: "DELETE",
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error("No se pudo eliminar en el servidor");
        }

        masterData = {};
        persistSave();
        currentPlate = "";
        currentDay = 1;
        activePlateTab = "";
        clearForm(true);
        document.getElementById("placa-indicator-wrap").innerHTML = "";
        document.getElementById("days-nav").innerHTML = "";
        renderDayPills();
        renderActivities();
        renderSummary();

        toast("Todos los registros eliminados");
    } catch (error) {
        console.error(error);
        toast("Error al eliminar los registros");
    }
}

/* ================================================================
   TOAST
================================================================ */
function toast(msg) {
    const el = document.getElementById("toast");
    el.textContent = msg;
    el.classList.add("show");
    setTimeout(() => el.classList.remove("show"), 3000);
}
