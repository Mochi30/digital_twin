const TICKS = 30;
const AB1 = 35, AB2 = 65;

let state = {
  hulu:   { la:82, ch:4, suhu:24, angin:12, ir:18 },
  tengah: { la:61, ch:2, suhu:25, angin:9,  ir:12 },
  hilir:  { la:45, ch:1, suhu:26, angin:7,  ir:8  }
};

let history = { hulu:[], tengah:[], hilir:[] };
let labels = [];
let irHistory = { hulu:[], tengah:[], hilir:[] };
let statusLog = [];
let lastGlobalStatus = 'normal';
let detailPoint = 'hulu';
let overviewChart, detailChart, irChart;
let scenarioMode = 0;
let scenarioStep = 0;

function genTime() {
  const d = new Date();
  return d.getHours().toString().padStart(2,'0')+':'+d.getMinutes().toString().padStart(2,'0')+':'+d.getSeconds().toString().padStart(2,'0');
}

function computeIR(la, ch, wla, wch) {
  const wlaV = parseInt(document.getElementById('cfg-wla').value)/100;
  const wchV = parseInt(document.getElementById('cfg-wch').value)/100;
  return Math.min(100, Math.round(la * wlaV * 0.55 + ch * wchV * 2.8));
}

function getAB1() { return parseInt(document.getElementById('cfg-ab1').value); }
function getAB2() { return parseInt(document.getElementById('cfg-ab2').value); }

function classifyStatus(ir) {
  if (ir >= getAB2()) return 'bahaya';
  if (ir >= getAB1()) return 'waspada';
  return 'normal';
}

function globalStatus() {
  const maxIR = Math.max(state.hulu.ir, state.tengah.ir, state.hilir.ir);
  return classifyStatus(maxIR);
}

function getLeadTime() {
  const distAB = 7, distBC = 4;
  const elevDiff = (950 - 720);
  const speed = 0.6 + (state.hulu.la / 200);
  return Math.round((distAB + distBC) * 1000 / speed / 60);
}

function noiseStep(val, min, max, step) {
  val += (Math.random() - 0.48) * step;
  return Math.max(min, Math.min(max, val));
}

function scenarioTick() {
  scenarioStep++;
  if (scenarioMode === 0) {
    if (scenarioStep > 40 && scenarioStep < 80) {
      state.hulu.la = Math.min(200, state.hulu.la + 1.8);
      state.hulu.ch = Math.min(30, state.hulu.ch + 0.4);
    } else if (scenarioStep >= 80 && scenarioStep < 110) {
      state.tengah.la = Math.min(180, state.tengah.la + 1.2);
      state.tengah.ch = Math.min(20, state.tengah.ch + 0.25);
    } else if (scenarioStep >= 110) {
      state.hulu.la = noiseStep(state.hulu.la, 60, 95, 2);
      state.hulu.ch = noiseStep(state.hulu.ch, 2, 8, .5);
      state.tengah.la = noiseStep(state.tengah.la, 40, 75, 1.5);
      state.tengah.ch = noiseStep(state.tengah.ch, 1, 6, .4);
    }
    if (scenarioStep > 200) { scenarioStep = 0; state = { hulu:{la:82,ch:4,suhu:24,angin:12,ir:18}, tengah:{la:61,ch:2,suhu:25,angin:9,ir:12}, hilir:{la:45,ch:1,suhu:26,angin:7,ir:8} }; }
  }
  ['hulu','tengah','hilir'].forEach(p => {
    state[p].suhu = noiseStep(state[p].suhu, 20, 32, .3);
    state[p].angin = noiseStep(state[p].angin, 4, 25, .8);
  });
  state.hilir.la = noiseStep(state.tengah.la * 0.72, 30, 150, 1);
  state.hilir.ch = noiseStep(state.tengah.ch * 0.7, 0.5, 15, .3);
  ['hulu','tengah','hilir'].forEach(p => {
    state[p].la = Math.max(20, state[p].la);
    state[p].ch = Math.max(0.2, state[p].ch);
    state[p].ir = computeIR(state[p].la, state[p].ch);
  });
}

function updateUI() {
  const t = genTime();
  document.getElementById('live-clock').textContent = t;

  labels.push(t);
  if (labels.length > TICKS) labels.shift();

  ['hulu','tengah','hilir'].forEach(p => {
    history[p].push(Math.round(state[p].la));
    if (history[p].length > TICKS) history[p].shift();
    irHistory[p].push(state[p].ir);
    if (irHistory[p].length > TICKS) irHistory[p].shift();
  });

  document.getElementById('m-la-hulu').textContent = Math.round(state.hulu.la);
  document.getElementById('m-ch-hulu').textContent = state.hulu.ch.toFixed(1);
  document.getElementById('m-suhu').textContent = state.hulu.suhu.toFixed(1);
  document.getElementById('m-angin').textContent = Math.round(state.hulu.angin);

  const gs = globalStatus();
  const changed = gs !== lastGlobalStatus;
  lastGlobalStatus = gs;

  const banner = document.getElementById('status-banner');
  const slabel = document.getElementById('status-label');
  const ssub = document.getElementById('status-sub');
  const lblock = document.getElementById('leadtime-block');
  banner.className = 'status-banner ' + gs;
  slabel.className = 'status-label ' + gs;

  const icons = {normal:'ti-check', waspada:'ti-alert-triangle', bahaya:'ti-alert-octagon'};
  const labels2 = {normal:'NORMAL', waspada:'WASPADA', bahaya:'BAHAYA'};
  const subs = {normal:'Semua titik pemantauan dalam kondisi aman', waspada:'Peningkatan indeks risiko terdeteksi — pantau terus', bahaya:'KONDISI BERBAHAYA — segera evakuasi wisatawan!'};
  slabel.innerHTML = '<i class="ti '+icons[gs]+'" aria-hidden="true"></i> '+labels2[gs];
  ssub.textContent = subs[gs];

  if (gs !== 'normal') {
    lblock.style.display = 'block';
    document.getElementById('leadtime-val').textContent = getLeadTime() + ' mnt';
  } else {
    lblock.style.display = 'none';
  }

  if (changed) {
    const triggerPoint = state.hulu.ir >= state.tengah.ir ? 'Titik A (Hulu)' : 'Titik B (Tengah)';
    const maxIR = Math.max(state.hulu.ir, state.tengah.ir, state.hilir.ir);
    statusLog.unshift({ waktu: t, status: gs, titik: triggerPoint, ir: maxIR, lt: gs !== 'normal' ? getLeadTime() + ' mnt' : '—' });
    if (statusLog.length > 20) statusLog.pop();
    renderLog();
    addNotif(gs, triggerPoint, maxIR);
  }

  ['hulu','tengah','hilir'].forEach(p => {
    const s = classifyStatus(state[p].ir);
    const dotEl = document.getElementById('dot-' + p);
    dotEl.className = 'status-dot dot-' + s;
    const colors = {normal:'#22c55e', waspada:'#f59e0b', bahaya:'#ef4444'};
    document.getElementById('map-dot-' + p).setAttribute('fill', colors[s]);
    document.getElementById('ir-val-' + p).textContent = state[p].ir;
    const pct = Math.min(100, Math.round(state[p].ir));
    document.getElementById('ir-bar-' + p).style.width = pct + '%';
    document.getElementById('ir-bar-' + p).style.background = colors[s];

    const sensorHtml = `
      <div class="sensor-row"><span class="sensor-name"><i class="ti ti-droplet" aria-hidden="true"></i> Nivel air</span><span class="sensor-val">${Math.round(state[p].la)} cm</span></div>
      <div class="sensor-row"><span class="sensor-name"><i class="ti ti-cloud-rain" aria-hidden="true"></i> Curah hujan</span><span class="sensor-val">${state[p].ch.toFixed(1)} mm/h</span></div>
      <div class="sensor-row"><span class="sensor-name"><i class="ti ti-temperature" aria-hidden="true"></i> Suhu</span><span class="sensor-val">${state[p].suhu.toFixed(1)} °C</span></div>
      <div class="sensor-row"><span class="sensor-name"><i class="ti ti-wind" aria-hidden="true"></i> Angin</span><span class="sensor-val">${Math.round(state[p].angin)} km/h</span></div>`;
    document.getElementById('sensors-' + p).innerHTML = sensorHtml;
  });

  updateDetailMetrics();
  updateCharts();
}

function updateDetailMetrics() {
  const p = detailPoint;
  const names = {hulu:'Titik A — Hulu',tengah:'Titik B — Tengah',hilir:'Titik C — Hilir'};
  document.getElementById('detail-metrics').innerHTML = `
    <div class="metric-card"><div class="metric-lbl">Titik</div><div class="metric-val" style="font-size:14px">${names[p]}</div></div>
    <div class="metric-card"><div class="metric-lbl">Level air</div><div><span class="metric-val">${Math.round(state[p].la)}</span> <span class="metric-unit">cm</span></div></div>
    <div class="metric-card"><div class="metric-lbl">Curah hujan</div><div><span class="metric-val">${state[p].ch.toFixed(1)}</span> <span class="metric-unit">mm/h</span></div></div>
    <div class="metric-card"><div class="metric-lbl">Indeks Risiko</div><div><span class="metric-val">${state[p].ir}</span></div></div>
    <div class="metric-card"><div class="metric-lbl">Suhu</div><div><span class="metric-val">${state[p].suhu.toFixed(1)}</span> <span class="metric-unit">°C</span></div></div>
    <div class="metric-card"><div class="metric-lbl">Angin</div><div><span class="metric-val">${Math.round(state[p].angin)}</span> <span class="metric-unit">km/h</span></div></div>`;
}

function renderLog() {
  const tbody = document.getElementById('log-body');
  tbody.innerHTML = statusLog.slice(0,10).map(r =>
    `<tr>
      <td>${r.waktu}</td>
      <td><span class="badge badge-${r.status}">${r.status.toUpperCase()}</span></td>
      <td>${r.titik}</td>
      <td>${r.ir}</td>
      <td>${r.lt}</td>
    </tr>`
  ).join('');
}

function addNotif(status, titik, ir) {
  const msgs = {
    waspada: `⚠️ PERINGATAN WASPADA\nTitik pemicu: ${titik}\nIR = ${ir} (≥ AB1)\nLead time ke hilir: ${getLeadTime()} menit\nPantau kondisi dan bersiap evakuasi wisatawan.`,
    bahaya:  `🚨 BAHAYA — EVAKUASI SEGERA\nTitik pemicu: ${titik}\nIR = ${ir} (≥ AB2)\nLead time ke hilir: ${getLeadTime()} menit\nAktifkan sirine. Pandu wisatawan ke titik aman!`
  };
  if (!msgs[status]) return;
  const nl = document.getElementById('notif-list');
  const t = genTime();
  nl.insertAdjacentHTML('afterbegin', `
    <div class="notif-card">
      <div class="notif-header">
        <span class="notif-title"><span class="badge badge-${status}" style="margin-right:6px">${status.toUpperCase()}</span>Notifikasi otomatis dikirim</span>
        <span class="notif-time">${t}</span>
      </div>
      <div class="notif-body" style="white-space:pre-line">${msgs[status]}</div>
    </div>`);
}

function updateCharts() {
  if (!overviewChart || !detailChart || !irChart) return;
  const lbl = labels.slice();

  overviewChart.data.labels = lbl;
  overviewChart.data.datasets[0].data = history.hulu.slice();
  overviewChart.data.datasets[1].data = history.tengah.slice();
  overviewChart.data.datasets[2].data = history.hilir.slice();
  overviewChart.update('none');

  detailChart.data.labels = lbl;
  detailChart.data.datasets[0].data = history[detailPoint].slice();
  detailChart.data.datasets[1].data = state[detailPoint] ? Array(lbl.length).fill(0).map((_, i) =>
    (irHistory[detailPoint][i] !== undefined ? parseFloat((irHistory[detailPoint][i] / 8).toFixed(1)) : null)) : [];
  detailChart.update('none');

  const ab1 = getAB1(), ab2 = getAB2();
  irChart.data.labels = lbl;
  irChart.data.datasets[0].data = irHistory[detailPoint].slice();
  irChart.data.datasets[1].data = Array(lbl.length).fill(ab1);
  irChart.data.datasets[2].data = Array(lbl.length).fill(ab2);
  irChart.update('none');
}

function switchTab(name) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
  document.getElementById('page-' + name).classList.add('active');
  event.target.classList.add('active');
}

function setDetailPoint(p) {
  detailPoint = p;
  ['hulu','tengah','hilir'].forEach(k => {
    const b = document.getElementById('btn-' + k);
    b.style.border = k === p ? '2px solid var(--color-border-info)' : '0.5px solid var(--color-border-tertiary)';
    b.style.color = k === p ? 'var(--color-text-info)' : 'var(--color-text-secondary)';
    b.style.fontWeight = k === p ? '500' : '400';
  });
  updateDetailMetrics();
  updateCharts();
}

function selectPoint(p) {
  document.querySelectorAll('.point-card').forEach(c => c.classList.remove('selected'));
  document.getElementById('card-' + p).classList.add('selected');
}

function syncBobot(which) {
  const wla = parseInt(document.getElementById('cfg-wla').value);
  const wch = parseInt(document.getElementById('cfg-wch').value);
  document.getElementById('v-wla').textContent = wla;
  document.getElementById('v-wch').textContent = wch;
}

function initCharts() {
  const tickConf = { maxTicksLimit: 5, font: { size: 10 }, color: '#888780' };
  const gridConf = { color: 'rgba(136,135,128,0.12)', drawBorder: false };

  overviewChart = new Chart(document.getElementById('chartOverview'), {
    type: 'line',
    data: {
      labels: [],
      datasets: [
        { label: 'Hulu', data: [], borderColor: '#378ADD', backgroundColor: 'transparent', tension: 0.4, pointRadius: 0, borderWidth: 2 },
        { label: 'Tengah', data: [], borderColor: '#EF9F27', backgroundColor: 'transparent', tension: 0.4, pointRadius: 0, borderWidth: 2, borderDash: [4,3] },
        { label: 'Hilir', data: [], borderColor: '#1D9E75', backgroundColor: 'transparent', tension: 0.4, pointRadius: 0, borderWidth: 2, borderDash: [2,3] }
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false, animation: false,
      plugins: { legend: { display: true, labels: { font:{size:11}, color:'#888780', boxWidth:12, padding:12 } } },
      scales: {
        x: { ticks: tickConf, grid: gridConf },
        y: { ticks: tickConf, grid: gridConf, title: { display: true, text: 'cm', font:{size:10}, color:'#888780' } }
      }
    }
  });

  detailChart = new Chart(document.getElementById('chartDetail'), {
    type: 'line',
    data: {
      labels: [],
      datasets: [
        { label: 'Level air (cm)', data: [], borderColor: '#378ADD', backgroundColor: 'rgba(55,138,221,0.08)', tension: 0.4, pointRadius: 0, borderWidth: 2, fill: true },
        { label: 'Curah hujan (scaled)', data: [], borderColor: '#EF9F27', backgroundColor: 'transparent', tension: 0.4, pointRadius: 0, borderWidth: 2, borderDash: [4,3] }
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false, animation: false,
      plugins: { legend: { display: true, labels: { font:{size:11}, color:'#888780', boxWidth:12, padding:12 } } },
      scales: { x: { ticks: tickConf, grid: gridConf }, y: { ticks: tickConf, grid: gridConf } }
    }
  });

  irChart = new Chart(document.getElementById('chartIR'), {
    type: 'line',
    data: {
      labels: [],
      datasets: [
        { label: 'IR', data: [], borderColor: '#D85A30', backgroundColor: 'rgba(216,90,48,0.08)', tension: 0.4, pointRadius: 0, borderWidth: 2, fill: true },
        { label: 'AB1 (waspada)', data: [], borderColor: '#f59e0b', backgroundColor: 'transparent', tension: 0, pointRadius: 0, borderWidth: 1.5, borderDash: [5,4] },
        { label: 'AB2 (bahaya)', data: [], borderColor: '#ef4444', backgroundColor: 'transparent', tension: 0, pointRadius: 0, borderWidth: 1.5, borderDash: [5,4] }
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false, animation: false,
      plugins: { legend: { display: true, labels: { font:{size:11}, color:'#888780', boxWidth:12, padding:12 } } },
      scales: { x: { ticks: tickConf, grid: gridConf }, y: { min:0, max:100, ticks: tickConf, grid: gridConf } }
    }
  });
}

initCharts();
statusLog.push({ waktu: '08:01:15', status:'normal', titik:'—', ir:18, lt:'—' });
renderLog();

setInterval(() => {
  scenarioTick();
  updateUI();
}, 1500);
updateUI();