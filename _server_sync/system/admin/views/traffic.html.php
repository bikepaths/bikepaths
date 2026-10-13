<?php if (!defined('HTMLY')) die('HTMLy'); ?>
<?php
$snap       = isset($snapshot) ? $snapshot : array();
$summary    = isset($snap['summary'])            ? $snap['summary']            : array();
$offenders  = isset($snap['security_offenders']) ? $snap['security_offenders'] : array();
$articles   = isset($snap['top_articles'])       ? $snap['top_articles']       : array();
$inquiries  = isset($snap['inquiries_today'])    ? $snap['inquiries_today']    : array();
$sources    = isset($snap['external_sources'])   ? $snap['external_sources']   : array();
$geodata    = isset($snap['geolocation'])        ? $snap['geolocation']        : array();
$generated  = isset($snap['generated_at'])       ? $snap['generated_at']       : null;
?>
<style>
.traffic-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.traffic-header h2{margin:0;font-size:1.6rem;color:#2c3e50;font-weight:700}
.traffic-generated{font-size:.82rem;color:#7f8c8d;margin-top:3px}
.stat-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:18px;margin-bottom:30px}
.stat-card{background:#fff;border-radius:10px;padding:22px 20px;box-shadow:0 2px 8px rgba(0,0,0,.09);border-left:4px solid #3498db;display:flex;flex-direction:column;gap:6px;transition:transform .15s}
.stat-card:hover{transform:translateY(-2px)}
.stat-card.green{border-color:#27ae60}.stat-card.orange{border-color:#e67e22}.stat-card.purple{border-color:#9b59b6}
.stat-card .label{font-size:.78rem;text-transform:uppercase;letter-spacing:.07em;color:#95a5a6}
.stat-card .value{font-size:2rem;font-weight:700;color:#2c3e50;line-height:1}
.stat-card .sub{font-size:.8rem;color:#7f8c8d}
.section-card{background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.09);margin-bottom:26px;overflow:hidden}
.section-card .sc-head{padding:14px 20px;background:#f4f6f8;border-bottom:1px solid #e8ecef;font-weight:600;font-size:.92rem;color:#34495e;display:flex;align-items:center;gap:8px}
.traffic-table{width:100%;border-collapse:collapse;font-size:.85rem}
.traffic-table th{background:#f9fafb;padding:9px 14px;text-align:left;font-weight:600;color:#555;border-bottom:1px solid #eaecef;white-space:nowrap}
.traffic-table td{padding:8px 14px;border-bottom:1px solid #f0f2f5;vertical-align:middle;color:#444}
.traffic-table tbody tr:hover{background:#f7f9fc}
.traffic-table tbody tr:last-child td{border-bottom:none}
.badge-status{display:inline-block;padding:2px 8px;border-radius:20px;font-size:.72rem;font-weight:600;text-transform:uppercase}
.badge-blocked{background:#fde8e8;color:#c0392b}.badge-safebot{background:#eaf6ef;color:#27ae60}.badge-new{background:#eaf1fb;color:#2980b9}
.bar-bg{background:#eef2f7;border-radius:4px;height:6px;min-width:60px;display:inline-block;vertical-align:middle;position:relative;overflow:hidden}
.bar-fill{position:absolute;left:0;top:0;height:100%;background:linear-gradient(90deg,#3498db,#5dade2);border-radius:4px}
.no-data{padding:20px;color:#aaa;font-style:italic;font-size:.88rem}
.refresh-btn{background:linear-gradient(135deg,#3498db,#2980b9);color:#fff;border:none;padding:10px 22px;border-radius:8px;font-size:.88rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:opacity .2s,transform .15s;text-decoration:none}
.refresh-btn:hover{opacity:.9;transform:translateY(-1px);color:#fff}
@keyframes spin{to{transform:rotate(360deg)}}
.refreshing .fa-refresh{animation:spin 1s linear infinite}
.two-col-grid{display:grid;grid-template-columns:1fr 1fr;gap:26px;margin-bottom:26px}
@media(max-width:700px){.two-col-grid{grid-template-columns:1fr}}
</style>

<div class="traffic-header">
  <div>
    <h2><i class="fa fa-line-chart"></i> Site Traffic Dashboard</h2>
    <?php if ($generated): ?>
      <div class="traffic-generated"><i class="fa fa-clock-o"></i> Snapshot: <strong><?php echo htmlspecialchars($generated); ?></strong></div>
    <?php else: ?>
      <div class="traffic-generated" style="color:#e67e22"><i class="fa fa-warning"></i> No snapshot found. Run a live scan to generate data.</div>
    <?php endif; ?>
  </div>
  <form method="post" action="<?php echo site_url(); ?>admin/traffic/refresh" id="refresh-form">
    <button type="submit" class="refresh-btn" onclick="this.closest('form').classList.add('refreshing')">
      <i class="fa fa-refresh"></i> Run Live Scan
    </button>
  </form>
</div>

<div class="stat-cards">
  <div class="stat-card">
    <div class="label">Human Visitors Today</div>
    <div class="value"><?php echo isset($summary['today_human_visits']) ? (int)$summary['today_human_visits'] : '—'; ?></div>
    <div class="sub">Unique IPs, bots filtered</div>
  </div>
  <div class="stat-card green">
    <div class="label">Tracked Articles</div>
    <div class="value"><?php echo isset($summary['total_tracked_articles']) ? (int)$summary['total_tracked_articles'] : '—'; ?></div>
    <div class="sub">Cumulative history</div>
  </div>
  <div class="stat-card orange">
    <div class="label">Inquiries Today</div>
    <div class="value"><?php echo isset($summary['inquiries_today']) ? (int)$summary['inquiries_today'] : '—'; ?></div>
    <div class="sub">Topic suggestions</div>
  </div>
  <div class="stat-card purple">
    <div class="label">Top Country</div>
    <div class="value" style="font-size:1.1rem;padding-top:6px"><?php echo isset($summary['top_country']) ? htmlspecialchars($summary['top_country']) : '—'; ?></div>
    <div class="sub">By unique visitors</div>
  </div>
</div>

<!-- Top Articles -->
<div class="section-card">
  <div class="sc-head"><i class="fa fa-file-text-o"></i> Top Articles (Unique Human Visitors)</div>
  <?php if (empty($articles)): ?>
    <div class="no-data">No article data yet.</div>
  <?php else:
    $vals = array_column($articles, 'day'); $maxDay = count($vals) ? max($vals) : 1; if (!$maxDay) $maxDay=1;
  ?>
  <table class="traffic-table">
    <thead><tr><th>Today</th><th>Total</th><th>Votes ▲/▼</th><th style="width:99%">URI</th></tr></thead>
    <tbody>
    <?php foreach ($articles as $row): ?>
    <tr>
      <td><strong><?php echo (int)$row['day']; ?></strong>
          <span class="bar-bg" style="width:70px"><span class="bar-fill" style="width:<?php echo min(100,round((int)$row['day']/$maxDay*100)); ?>%"></span></span></td>
      <td><?php echo (int)$row['tot']; ?></td>
      <td><span style="color:#27ae60;font-weight:600">+<?php echo (int)$row['likes']; ?></span>
          <span style="color:#e74c3c;font-weight:600"> −<?php echo (int)$row['dislikes']; ?></span></td>
      <td><a href="<?php echo site_url() . ltrim(htmlspecialchars($row['uri']),'/'); ?>" target="_blank"
             style="color:#2980b9;text-decoration:none;word-break:break-all"><?php echo htmlspecialchars($row['uri']); ?></a></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<!-- Security Offenders -->
<div class="section-card">
  <div class="sc-head"><i class="fa fa-shield"></i> Top IP Offenders</div>
  <?php if (empty($offenders)): ?>
    <div class="no-data">No data yet.</div>
  <?php else: ?>
  <table class="traffic-table">
    <thead><tr><th>IP Address</th><th>Hits</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($offenders as $o):
      $st = strtoupper($o['status']);
      $cls = ($st==='BLOCKED') ? 'badge-blocked' : (($st==='SAFE BOT') ? 'badge-safebot' : 'badge-new');
    ?>
    <tr>
      <td><code><?php echo htmlspecialchars($o['ip']); ?></code></td>
      <td><?php echo (int)$o['hits']; ?></td>
      <td><span class="badge-status <?php echo $cls; ?>"><?php echo htmlspecialchars($o['status']); ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<div class="two-col-grid">
  <!-- External Sources -->
  <div class="section-card" style="margin-bottom:0">
    <div class="sc-head"><i class="fa fa-external-link"></i> External Sources</div>
    <?php if (empty($sources)): ?>
      <div class="no-data">No external referrers today.</div>
    <?php else: ?>
    <table class="traffic-table">
      <thead><tr><th>Hits</th><th>Referrer</th></tr></thead>
      <tbody>
      <?php foreach ($sources as $s): ?>
      <tr>
        <td style="width:50px;font-weight:600"><?php echo (int)$s['hits']; ?></td>
        <td style="word-break:break-all;font-size:.8rem">
          <a href="<?php echo htmlspecialchars($s['source']); ?>" target="_blank" rel="noopener noreferrer"
             style="color:#2980b9;text-decoration:none"><?php echo htmlspecialchars($s['source']); ?></a>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <!-- Geolocation -->
  <div class="section-card" style="margin-bottom:0">
    <div class="sc-head"><i class="fa fa-globe"></i> Visitor Geolocation</div>
    <?php if (empty($geodata)): ?>
      <div class="no-data">No geo data available.</div>
    <?php else:
      $gvals = array_column($geodata,'visitors'); $maxGeo = count($gvals) ? max($gvals) : 1; if(!$maxGeo) $maxGeo=1;
    ?>
    <table class="traffic-table">
      <thead><tr><th>Visitors</th><th>Country</th></tr></thead>
      <tbody>
      <?php foreach ($geodata as $g): ?>
      <tr>
        <td style="width:80px;font-weight:600"><?php echo (int)$g['visitors']; ?>
          <span class="bar-bg" style="width:50px"><span class="bar-fill" style="width:<?php echo min(100,round((int)$g['visitors']/$maxGeo*100)); ?>%"></span></span>
        </td>
        <td><?php echo htmlspecialchars($g['country']); ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<!-- Reader Inquiries -->
<div class="section-card">
  <div class="sc-head"><i class="fa fa-comment-o"></i> Reader Inquiries Today</div>
  <?php if (empty($inquiries)): ?>
    <div class="no-data">No inquiries recorded today.</div>
  <?php else: ?>
  <table class="traffic-table">
    <thead><tr><th style="width:175px">Timestamp</th><th>Topic Suggestion</th></tr></thead>
    <tbody>
    <?php foreach ($inquiries as $iq): ?>
    <tr>
      <td style="color:#888;font-size:.8rem;white-space:nowrap"><?php echo htmlspecialchars($iq['time']); ?></td>
      <td><?php echo htmlspecialchars($iq['topic']); ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
