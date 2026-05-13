<div id="page-konfigurasi" class="page">
  <div class="config-grid">
    <div class="config-card">
      <h3><i class="ti ti-adjustments" aria-hidden="true" style="margin-right:6px"></i>Ambang batas & eskalasi</h3>
      <div class="config-row">
        <div class="config-label">AB1 — ambang WASPADA (IR)</div>
        <div class="config-input-row">
          <input type="range" min="20" max="60" value="35" id="cfg-ab1" oninput="document.getElementById('v-ab1').textContent=this.value">
          <span class="config-val" id="v-ab1">35</span>
        </div>
      </div>
      <div class="config-row">
        <div class="config-label">AB2 — ambang BAHAYA (IR)</div>
        <div class="config-input-row">
          <input type="range" min="40" max="90" value="65" id="cfg-ab2" oninput="document.getElementById('v-ab2').textContent=this.value">
          <span class="config-val" id="v-ab2">65</span>
        </div>
      </div>
      <div class="config-row">
        <div class="config-label">T_max — durasi maks WASPADA (menit)</div>
        <div class="config-input-row">
          <input type="range" min="5" max="60" value="15" id="cfg-tmax" oninput="document.getElementById('v-tmax').textContent=this.value">
          <span class="config-val" id="v-tmax">15</span>
        </div>
      </div>
    </div>

    <div class="config-card">
      <h3><i class="ti ti-scale" aria-hidden="true" style="margin-right:6px"></i>Bobot parameter IR</h3>
      <div class="config-row">
        <div class="config-label">Bobot level air (LA) — %</div>
        <div class="config-input-row">
          <input type="range" min="20" max="80" value="60" id="cfg-wla" oninput="syncBobot('wla')">
          <span class="config-val" id="v-wla">60</span>
        </div>
      </div>
      <div class="config-row">
        <div class="config-label">Bobot curah hujan (CH) — %</div>
        <div class="config-input-row">
          <input type="range" min="20" max="80" value="40" id="cfg-wch" oninput="syncBobot('wch')">
          <span class="config-val" id="v-wch">40</span>
        </div>
      </div>
      <div class="config-row">
        <div class="config-label">Interval pengiriman data (detik)</div>
        <div class="config-input-row">
          <input type="range" min="5" max="60" value="10" id="cfg-interval" oninput="document.getElementById('v-interval').textContent=this.value">
          <span class="config-val" id="v-interval">10</span>
        </div>
      </div>
    </div>

    <div class="config-card">
      <h3><i class="ti ti-bell" aria-hidden="true" style="margin-right:6px"></i>Penerima notifikasi</h3>
      <div class="config-row">
        <div class="config-label">Telegram / WhatsApp</div>
        <input type="text" placeholder="+62812xxxx / @username" style="width:100%;font-size:13px">
      </div>
      <div class="config-row">
        <div class="config-label">SMS cadangan</div>
        <input type="text" placeholder="+62812xxxx" style="width:100%;font-size:13px">
      </div>
    </div>

    <div class="config-card">
      <h3><i class="ti ti-info-circle" aria-hidden="true" style="margin-right:6px"></i>Info sistem</h3>
      <table style="width:100%;font-size:12px;border-collapse:collapse">
        <tr><td style="color:var(--color-text-secondary);padding:5px 0">Versi sistem</td><td style="text-align:right;font-weight:500">EWS-DT v1.0</td></tr>
        <tr><td style="color:var(--color-text-secondary);padding:5px 0">Uptime</td><td style="text-align:right;color:var(--color-text-success);font-weight:500">99.8%</td></tr>
        <tr><td style="color:var(--color-text-secondary);padding:5px 0">Data loss</td><td style="text-align:right;font-weight:500">0%</td></tr>
        <tr><td style="color:var(--color-text-secondary);padding:5px 0">Titik aktif</td><td style="text-align:right;font-weight:500">3 / 3</td></tr>
        <tr><td style="color:var(--color-text-secondary);padding:5px 0">Integrasi BMKG</td><td style="text-align:right;color:var(--color-text-success);font-weight:500">Terhubung</td></tr>
      </table>
    </div>
  </div>
  <button class="save-btn" onclick="alert('Konfigurasi berhasil disimpan.')">Simpan konfigurasi</button>
</div>
