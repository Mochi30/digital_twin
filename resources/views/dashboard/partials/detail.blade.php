<div id="page-detail" class="page">
  <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap">
    <button onclick="setDetailPoint('hulu')" id="btn-hulu" style="padding:7px 14px;font-size:13px;font-weight:500;border-radius:var(--border-radius-md);cursor:pointer;border:2px solid var(--color-border-info);color:var(--color-text-info)">Titik A — Hulu</button>
    <button onclick="setDetailPoint('tengah')" id="btn-tengah" style="padding:7px 14px;font-size:13px;border-radius:var(--border-radius-md);cursor:pointer;border:0.5px solid var(--color-border-tertiary);color:var(--color-text-secondary)">Titik B — Tengah</button>
    <button onclick="setDetailPoint('hilir')" id="btn-hilir" style="padding:7px 14px;font-size:13px;border-radius:var(--border-radius-md);cursor:pointer;border:0.5px solid var(--color-border-tertiary);color:var(--color-text-secondary)">Titik C — Hilir</button>
  </div>

  <div class="metrics-grid" id="detail-metrics"></div>

  <div class="chart-wrap">
    <div class="chart-title">Level air & curah hujan — 30 menit terakhir</div>
    <div style="position:relative;width:100%;height:200px;">
      <canvas id="chartDetail" role="img" aria-label="Grafik level air dan curah hujan di titik terpilih"></canvas>
    </div>
  </div>
  <div class="chart-wrap">
    <div class="chart-title">Indeks risiko (IR) — 30 menit terakhir</div>
    <div style="position:relative;width:100%;height:150px;">
      <canvas id="chartIR" role="img" aria-label="Grafik indeks risiko di titik terpilih dengan ambang batas AB1 dan AB2"></canvas>
    </div>
  </div>
</div>
