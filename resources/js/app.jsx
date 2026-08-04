import axios from 'axios';
import {
    CategoryScale, Chart as ChartJS, Filler, Legend, LinearScale, LineElement, PointElement, Tooltip,
} from 'chart.js';
import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { Line } from 'react-chartjs-2';

ChartJS.register(CategoryScale, Filler, Legend, LinearScale, LineElement, PointElement, Tooltip);
axios.defaults.headers.common.Accept = 'application/json';
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const pointMeta = {
    hulu: { title: 'Titik A — Hulu', elevation: '950 mdpl', distance: 'Km 0' },
    tengah: { title: 'Titik B — Tengah', elevation: '820 mdpl', distance: 'Km 7' },
    hilir: { title: 'Titik C — Hilir', elevation: '720 mdpl', distance: 'Km 11' },
};
const statusText = {
    normal: ['NORMAL', 'Semua titik pemantauan dalam kondisi aman'],
    waspada: ['WASPADA', 'Peningkatan indeks risiko terdeteksi — pantau terus'],
    bahaya: ['BAHAYA', 'Kondisi berbahaya — segera lakukan tindakan mitigasi'],
};
const publicTabs = [
    ['overview', 'Overview'], ['detail', 'Detail titik'], ['history', 'Riwayat'],
];
const adminTabs = [
    ['overview', 'Dashboard Admin'], ['history', 'Data Sensor'], ['settings', 'Konfigurasi'],
];

function StatusPill({ status }) {
    return <span className={`status-pill ${status}`}><span className="status-dot" />{statusText[status]?.[0] ?? status}</span>;
}

function PointCard({ reading, name, selected, onClick }) {
    const meta = pointMeta[name];
    return (
        <button className={`point-card ${selected ? 'selected' : ''}`} onClick={onClick}>
            <div className="point-heading"><strong>{meta.title}</strong><StatusPill status={reading.status} /></div>
            {reading.device && <div className="device-line"><span className="device-online" /> <b>{reading.device}</b><span>{reading.connection?.toUpperCase()} · FW {reading.fw_version}</span></div>}
            <div className="sensor-grid">
                <span><small>Level air</small><b>{reading.water_level} cm</b></span>
                <span><small>Curah hujan</small><b>{reading.rainfall} mm/h</b></span>
                <span><small>Suhu</small><b>{reading.temperature} °C</b></span>
                <span><small>Angin</small><b>{reading.wind_speed} km/h</b></span>
                {reading.humidity !== null && <span><small>Kelembapan</small><b>{reading.humidity}%</b></span>}
                {reading.wifi_rssi !== null && <span><small>WiFi RSSI</small><b>{reading.wifi_rssi} dBm</b></span>}
            </div>
            <div className="risk-track"><i style={{ width: `${reading.risk_index}%` }} /></div>
            <div className="risk-caption"><b>IR {reading.risk_index}</b><span>{meta.elevation} · {meta.distance}</span></div>
        </button>
    );
}

function TrendChart({ history, point }) {
    const rows = history.filter((row) => row.point === point);
    const data = {
        labels: rows.map((row) => new Date(row.recorded_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })),
        datasets: [
            { label: 'Level air (cm)', data: rows.map((row) => row.water_level), borderColor: '#2f80ed', backgroundColor: 'rgba(47,128,237,.12)', fill: true, tension: .35 },
            { label: 'Indeks risiko', data: rows.map((row) => row.risk_index), borderColor: '#f59e0b', backgroundColor: 'transparent', tension: .35 },
        ],
    };
    return <div className="chart-wrap"><Line data={data} options={{ responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }} /></div>;
}

function CctvPanel({ clock }) {
    const [expanded, setExpanded] = useState(false);

    return (
        <section className={`panel cctv-panel ${expanded ? 'cctv-expanded' : ''}`}>
            <div className="panel-title">
                <div><span className="eyebrow">Visual monitoring</span><h2>CCTV Sungai — Realtime</h2></div>
                <span className="cctv-prototype-badge">Prototype</span>
            </div>
            <div className="cctv-layout">
                <div className="cctv-feed" role="img" aria-label="Prototype feed CCTV Sungai Brantas">
                    <div className="cctv-sky" />
                    <div className="cctv-hills hill-back" />
                    <div className="cctv-hills hill-front" />
                    <div className="cctv-river"><i /><i /><i /></div>
                    <div className="cctv-scanline" />
                    <div className="cctv-feed-top">
                        <span className="cctv-camera-name">CAM-01 · HULU</span>
                        <span className="cctv-offline"><i /> STREAM BELUM TERHUBUNG</span>
                    </div>
                    <div className="cctv-reticle"><i /><i /><i /><i /></div>
                    <div className="cctv-feed-bottom">
                        <span>{clock.toLocaleDateString('id-ID')} · {clock.toLocaleTimeString('id-ID')}</span>
                        <span>950 MDPL · KM 0</span>
                    </div>
                </div>
                <aside className="cctv-info">
                    <div className="cctv-info-head"><span className="device-online" /><div><b>Kamera Hulu</b><small>Titik A · Sungai Brantas</small></div></div>
                    <dl>
                        <div><dt>Status</dt><dd>Menunggu URL stream</dd></div>
                        <div><dt>Mode</dt><dd>Realtime monitoring</dd></div>
                        <div><dt>Sumber</dt><dd>Belum dikonfigurasi</dd></div>
                        <div><dt>Kualitas</dt><dd>—</dd></div>
                    </dl>
                    <div className="cctv-notice"><b>Prototype CCTV</b><span>Feed video akan aktif setelah URL kamera diatur melalui dashboard admin.</span></div>
                    <button className="cctv-expand" onClick={() => setExpanded((value) => !value)}>{expanded ? 'Kembali ke dashboard' : 'Perbesar tampilan'}</button>
                </aside>
            </div>
        </section>
    );
}

function Overview({ points, history, selected, setSelected, clock }) {
    const all = Object.values(points);
    const global = all.some((p) => p.status === 'bahaya') ? 'bahaya' : all.some((p) => p.status === 'waspada') ? 'waspada' : 'normal';
    const hulu = points.hulu;
    return (
        <>
            <section className={`status-banner ${global}`}>
                <div><StatusPill status={global} /><p>{statusText[global][1]}</p></div>
                {global !== 'normal' && <div className="lead-time"><b>± 183 mnt</b><span>estimasi ke hilir</span></div>}
            </section>
            <section className="metrics-grid">
                <article><small>Level Air Hulu</small><strong>{hulu.water_level}<em> cm</em></strong></article>
                <article><small>Curah Hujan Hulu</small><strong>{hulu.rainfall}<em> mm/h</em></strong></article>
                <article><small>Suhu Rata-rata</small><strong>{(all.reduce((n, p) => n + Number(p.temperature), 0) / all.length).toFixed(1)}<em> °C</em></strong></article>
                <article><small>Kecepatan Angin</small><strong>{hulu.wind_speed}<em> km/h</em></strong></article>
            </section>
            <CctvPanel clock={clock} />
            <div className="section-head"><div><span className="eyebrow">Jaringan sensor</span><h2>Titik pemantauan</h2></div><span>3 titik aktif</span></div>
            <section className="points-grid">
                {Object.entries(points).map(([name, reading]) => <PointCard key={name} name={name} reading={reading} selected={selected === name} onClick={() => setSelected(name)} />)}
            </section>
            <section className="panel"><div className="panel-title"><div><span className="eyebrow">Telemetri</span><h2>Tren terbaru — {pointMeta[selected].title}</h2></div></div><TrendChart history={history} point={selected} /></section>
        </>
    );
}

function Detail({ points, history, selected, setSelected }) {
    const reading = points[selected];
    return (
        <section className="panel">
            <div className="detail-toolbar">
                <div><span className="eyebrow">Analisis sensor</span><h2>{pointMeta[selected].title}</h2></div>
                <select value={selected} onChange={(e) => setSelected(e.target.value)}>{Object.keys(points).map((p) => <option key={p} value={p}>{pointMeta[p].title}</option>)}</select>
            </div>
            <div className="detail-summary">
                <div><small>Status saat ini</small><StatusPill status={reading.status} /></div>
                <div><small>Indeks risiko</small><strong>{reading.risk_index}/100</strong></div>
                <div><small>Posisi</small><strong>{pointMeta[selected].elevation}</strong></div>
                {reading.device && <div><small>Perangkat</small><strong>{reading.device}</strong></div>}
                {reading.connection && <div><small>Koneksi</small><strong>{reading.connection.toUpperCase()} {reading.wifi_rssi ? `(${reading.wifi_rssi} dBm)` : ''}</strong></div>}
                {reading.uptime_ms !== null && <div><small>Uptime</small><strong>{Math.floor(reading.uptime_ms / 60000)} menit</strong></div>}
            </div>
            <TrendChart history={history} point={selected} />
        </section>
    );
}

function History({ history }) {
    return (
        <section className="panel">
            <div className="panel-title"><div><span className="eyebrow">Database Laravel</span><h2>Riwayat pembacaan</h2></div><span>{history.length} rekaman terbaru</span></div>
            <div className="table-wrap"><table><thead><tr><th>Waktu</th><th>Perangkat</th><th>Titik</th><th>Level</th><th>Hujan</th><th>IR</th><th>Status</th></tr></thead>
                <tbody>{[...history].reverse().map((row) => <tr key={row.id}><td>{new Date(row.recorded_at).toLocaleString('id-ID')}</td><td>{row.device ?? 'Simulasi'}</td><td>{pointMeta[row.point].title}</td><td>{row.water_level} cm</td><td>{row.rainfall} mm/h</td><td>{row.risk_index}</td><td><StatusPill status={row.status} /></td></tr>)}</tbody>
            </table></div>
        </section>
    );
}

function Settings({ initial, onSaved }) {
    const [form, setForm] = useState(initial);
    const [message, setMessage] = useState('');
    useEffect(() => setForm(initial), [initial]);
    const update = (key, value) => setForm((old) => ({ ...old, [key]: Number(value) }));
    const save = async (event) => {
        event.preventDefault(); setMessage('Menyimpan…');
        try {
            const { data } = await axios.put('/admin/settings', form);
            onSaved(data.settings); setMessage('Konfigurasi tersimpan di database.');
        } catch (error) { setMessage(error.response?.data?.message ?? 'Gagal menyimpan konfigurasi.'); }
    };
    const fields = [
        ['alert_threshold', 'Ambang waspada', '%'], ['danger_threshold', 'Ambang bahaya', '%'],
        ['water_weight', 'Bobot level air', '%'], ['rain_weight', 'Bobot curah hujan', '%'],
        ['refresh_seconds', 'Interval refresh', 'detik'],
    ];
    return <section className="panel settings"><div><span className="eyebrow">Persisten via API</span><h2>Konfigurasi peringatan dini</h2><p>Perubahan divalidasi Laravel dan disimpan ke SQLite.</p></div>
        <form onSubmit={save}>{fields.map(([key, label, unit]) => <label key={key}><span>{label}</span><div><input type="number" value={form[key] ?? ''} onChange={(e) => update(key, e.target.value)} /><em>{unit}</em></div></label>)}
            <button className="primary" type="submit">Simpan konfigurasi</button><p className="form-message">{message}</p></form>
    </section>;
}

function AdminLogin() {
    const [form, setForm] = useState({ username: '', password: '' });
    const [message, setMessage] = useState('');
    const [busy, setBusy] = useState(false);

    const submit = async (event) => {
        event.preventDefault();
        setBusy(true);
        setMessage('');
        try {
            const { data } = await axios.post('/ruang-kendali-ews/login', form);
            window.location.assign(data.redirect);
        } catch (error) {
            setMessage(error.response?.data?.message ?? 'Login tidak dapat diproses.');
            setBusy(false);
        }
    };

    return <main className="login-screen">
        <section className="login-card">
            <div className="login-emblem">EWS</div>
            <span className="eyebrow">Restricted system</span>
            <h1>Ruang kendali</h1>
            <p>Masukkan identitas pengelola untuk melanjutkan.</p>
            <form onSubmit={submit}>
                <label><span>Identitas</span><input autoComplete="username" value={form.username} onChange={(e) => setForm({ ...form, username: e.target.value })} required /></label>
                <label><span>Kunci akses</span><input type="password" autoComplete="current-password" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} required /></label>
                <button className="primary" disabled={busy}>{busy ? 'Memverifikasi…' : 'Masuk ke sistem'}</button>
                <p className="login-message">{message}</p>
            </form>
        </section>
    </main>;
}

function App() {
    const isAdmin = window.location.pathname.startsWith('/admin');
    const isLogin = window.location.pathname === '/ruang-kendali-ews';
    const [data, setData] = useState(null);
    const [error, setError] = useState('');
    const [tab, setTab] = useState('overview');
    const [selected, setSelected] = useState('hulu');
    const [clock, setClock] = useState(new Date());
    const load = async () => {
        try {
            const { data: payload } = await axios.get('/api/dashboard');
            if (isAdmin) {
                const { data: protectedData } = await axios.get('/admin/settings');
                payload.settings = protectedData.settings;
            }
            setData(payload);
            setError('');
        } catch (requestError) {
            if (requestError.response?.status === 401 && isAdmin) window.location.assign('/ruang-kendali-ews');
            setError('API Laravel tidak dapat dijangkau.');
        }
    };

    useEffect(() => { load(); const timer = setInterval(() => setClock(new Date()), 1000); return () => clearInterval(timer); }, []);
    const refreshMs = useMemo(() => Math.min(Number(data?.settings?.refresh_seconds ?? 2), 2) * 1000, [data?.settings?.refresh_seconds]);
    useEffect(() => { const timer = setInterval(load, refreshMs); return () => clearInterval(timer); }, [refreshMs]);

    if (isLogin) return <AdminLogin />;
    if (!data) return <main className="state-screen"><div className="loader" /><h1>Memuat Digital Twin</h1><p>{error || 'Menghubungkan React ke API Laravel…'}</p></main>;
    const tabs = isAdmin ? adminTabs : publicTabs;
    const logout = async () => {
        const { data: response } = await axios.post('/admin/logout');
        window.location.assign(response.redirect);
    };

    return <div className="app-shell">
        <header><div className="brand-mark">EWS</div><div className="brand-copy"><strong>{isAdmin ? 'Dashboard Admin' : 'Digital Twin'}</strong><span>Sungai Brantas · Kota Batu</span></div>{isAdmin && <button className="logout" onClick={logout}>Keluar</button>}<div className="live"><i /> LIVE <b>{clock.toLocaleTimeString('id-ID')}</b></div></header>
        <nav>{tabs.map(([key, label]) => <button key={key} className={tab === key ? 'active' : ''} onClick={() => setTab(key)}>{label}</button>)}</nav>
        <main>
            {error && <div className="api-error">{error}</div>}
            {tab === 'overview' && <Overview points={data.points} history={data.history} selected={selected} setSelected={setSelected} clock={clock} />}
            {tab === 'detail' && <Detail points={data.points} history={data.history} selected={selected} setSelected={setSelected} />}
            {tab === 'history' && <History history={data.history} />}
            {tab === 'settings' && <Settings initial={data.settings} onSaved={(settings) => setData((old) => ({ ...old, settings }))} />}
        </main>
        <footer><span>{isAdmin ? 'Area pengelola · session protected' : 'Laravel API + React.js'}</span><span>Terakhir sinkron: {new Date(data.server_time).toLocaleString('id-ID')}</span></footer>
    </div>;
}

createRoot(document.getElementById('root')).render(<React.StrictMode><App /></React.StrictMode>);
