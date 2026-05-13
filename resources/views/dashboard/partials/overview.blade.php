<div id="page-overview" class="page active">
  <div id="status-banner" class="status-banner normal">
    <div>
      <div id="status-label" class="status-label normal"><i class="ti ti-check" aria-hidden="true"></i> NORMAL</div>
      <div class="status-sub" id="status-sub">Semua titik pemantauan dalam kondisi aman</div>
    </div>
    <div class="status-right" id="leadtime-block" style="display:none">
      <div class="leadtime-val" id="leadtime-val">— mnt</div>
      <div class="leadtime-lbl">lead time ke hilir</div>
    </div>
  </div>

  <div class="metrics-grid">
    <div class="metric-card">
      <div class="metric-lbl"><i class="ti ti-droplet" aria-hidden="true"></i> Level Air Hulu</div>
      <div><span class="metric-val" id="m-la-hulu">—</span> <span class="metric-unit">cm</span></div>
    </div>
    <div class="metric-card">
      <div class="metric-lbl"><i class="ti ti-cloud-rain" aria-hidden="true"></i> Curah Hujan Hulu</div>
      <div><span class="metric-val" id="m-ch-hulu">—</span> <span class="metric-unit">mm/h</span></div>
    </div>
    <div class="metric-card">
      <div class="metric-lbl"><i class="ti ti-temperature" aria-hidden="true"></i> Suhu Rata-rata</div>
      <div><span class="metric-val" id="m-suhu">—</span> <span class="metric-unit">°C</span></div>
    </div>
    <div class="metric-card">
      <div class="metric-lbl"><i class="ti ti-wind" aria-hidden="true"></i> Kecepatan Angin</div>
      <div><span class="metric-val" id="m-angin">—</span> <span class="metric-unit">km/h</span></div>
    </div>
  </div>

  <div class="section-title">Titik pemantauan</div>
  <div class="points-grid">
    <div class="point-card" id="card-hulu" onclick="selectPoint('hulu')">
      <div class="point-header">
        <span class="point-name">Titik A — Hulu</span>
        <span class="status-dot dot-normal" id="dot-hulu"></span>
      </div>
      <div class="point-sensors" id="sensors-hulu"></div>
      <div class="ir-bar-wrap"><div class="ir-bar-fill" id="ir-bar-hulu" style="width:0%;background:#22c55e"></div></div>
      <div class="ir-label">IR: <span id="ir-val-hulu">—</span> <span style="color:var(--color-text-tertiary);font-size:10px">| 950 mdpl · Km 0</span></div>
    </div>
    <div class="point-card" id="card-tengah" onclick="selectPoint('tengah')">
      <div class="point-header">
        <span class="point-name">Titik B — Tengah</span>
        <span class="status-dot dot-normal" id="dot-tengah"></span>
      </div>
      <div class="point-sensors" id="sensors-tengah"></div>
      <div class="ir-bar-wrap"><div class="ir-bar-fill" id="ir-bar-tengah" style="width:0%;background:#22c55e"></div></div>
      <div class="ir-label">IR: <span id="ir-val-tengah">—</span> <span style="color:var(--color-text-tertiary);font-size:10px">| 780 mdpl · Km 7</span></div>
    </div>
    <div class="point-card" id="card-hilir" onclick="selectPoint('hilir')">
      <div class="point-header">
        <span class="point-name">Titik C — Hilir</span>
        <span class="status-dot dot-normal" id="dot-hilir"></span>
      </div>
      <div class="point-sensors" id="sensors-hilir"></div>
      <div class="ir-bar-wrap"><div class="ir-bar-fill" id="ir-bar-hilir" style="width:0%;background:#22c55e"></div></div>
      <div class="ir-label">IR: <span id="ir-val-hilir">—</span> <span style="color:var(--color-text-tertiary);font-size:10px">| 720 mdpl · Km 11</span></div>
    </div>
  </div>

  <div class="map-container">
    <div class="section-title" style="margin-bottom:12px">Peta sebaran sensor — Sungai Brantas</div>
    <svg class="river-svg" viewBox="0 0 620 160" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Peta aliran sungai dari hulu ke hilir dengan 3 titik sensor">
      <defs>
        <marker id="arr" markerWidth="8" markerHeight="6" refX="8" refY="3" orient="auto">
          <polygon points="0 0, 8 3, 0 6" fill="#888780"/>
        </marker>
      </defs>
      <path d="M 60 80 C 120 60, 200 100, 280 80 C 360 60, 440 100, 560 80" stroke="#3B82F6" stroke-width="8" fill="none" stroke-linecap="round" opacity=".35"/>
      <path d="M 60 80 C 120 60, 200 100, 280 80 C 360 60, 440 100, 560 80" stroke="#60A5FA" stroke-width="3" fill="none" stroke-linecap="round" opacity=".6" stroke-dasharray="6 4" marker-end="url(#arr)"/>
      <circle id="map-dot-hulu" cx="80" cy="78" r="10" fill="#22c55e" opacity=".9"/>
      <text x="80" y="50" text-anchor="middle" font-size="11" fill="var(--color-text-secondary)">Titik A</text>
      <text x="80" y="62" text-anchor="middle" font-size="10" fill="var(--color-text-tertiary)">950 mdpl</text>
      <circle id="map-dot-tengah" cx="295" cy="80" r="10" fill="#22c55e" opacity=".9"/>
      <text x="295" y="50" text-anchor="middle" font-size="11" fill="var(--color-text-secondary)">Titik B</text>
      <text x="295" y="62" text-anchor="middle" font-size="10" fill="var(--color-text-tertiary)">780 mdpl</text>
      <circle id="map-dot-hilir" cx="550" cy="80" r="10" fill="#22c55e" opacity=".9"/>
      <text x="550" y="50" text-anchor="middle" font-size="11" fill="var(--color-text-secondary)">Titik C</text>
      <text x="550" y="62" text-anchor="middle" font-size="10" fill="var(--color-text-tertiary)">720 mdpl</text>
      <text x="80" y="105" text-anchor="middle" font-size="10" fill="var(--color-text-tertiary)">Km 0</text>
      <text x="295" y="105" text-anchor="middle" font-size="10" fill="var(--color-text-tertiary)">Km 7</text>
      <text x="550" y="105" text-anchor="middle" font-size="10" fill="var(--color-text-tertiary)">Km 11</text>
      <text x="80" y="140" text-anchor="middle" font-size="10" fill="var(--color-text-tertiary)">Hulu</text>
      <text x="295" y="140" text-anchor="middle" font-size="10" fill="var(--color-text-tertiary)">Tengah</text>
      <text x="550" y="140" text-anchor="middle" font-size="10" fill="var(--color-text-tertiary)">Hilir</text>
      <text x="310" y="120" text-anchor="middle" font-size="10" fill="#888780">← arah aliran</text>
    </svg>
  </div>

  <div class="chart-wrap">
    <div class="chart-title">Level air real-time (semua titik) — 30 menit terakhir</div>
    <div style="position:relative;width:100%;height:180px;">
      <canvas id="chartOverview" role="img" aria-label="Grafik level air 30 menit terakhir untuk titik hulu, tengah, dan hilir"></canvas>
    </div>
  </div>
</div>
