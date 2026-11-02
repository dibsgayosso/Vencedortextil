<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link rel="apple-touch-icon" sizes="180x180" href="<?php echo base_url('assets/owner-money-icon.png'); ?>">
<link rel="icon" type="image/png" href="<?php echo base_url('assets/owner-money-icon.png'); ?>">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Panel General">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<section style="padding:24px;font-family:Arial,sans-serif;color:#172554">
<h2>Panel General · Multibase</h2>
<p>Resumen de hoy de las bases de datos configuradas. Las conexiones son de solo lectura.</p>
<div style="display:flex;flex-wrap:wrap;gap:14px;margin-bottom:24px">
<?php foreach (array('sales'=>'Ventas','operations'=>'Operaciones','expenses'=>'Gastos') as $key=>$label): ?>
<div style="background:#eef6ff;padding:18px;border-radius:12px;min-width:180px"><small><?php echo $label; ?></small><h2><?php echo $key==='operations' ? (int)$multi['totals'][$key] : to_currency($multi['totals'][$key]); ?></h2></div>
<?php endforeach; ?>
</div>
<table style="border-collapse:collapse;width:100%;background:white">
<thead><tr><th style="text-align:left;padding:12px">Base de datos</th><th>Ventas hoy</th><th>Operaciones</th><th>Gastos</th><th>Última venta</th><th>Estado</th></tr></thead>
<tbody><?php foreach ($multi['sources'] as $source): ?>
<tr style="border-top:1px solid #ddd"><td style="padding:12px"><?php echo html_escape($source['name']); ?></td>
<td style="text-align:center"><?php echo to_currency($source['sales']); ?></td><td style="text-align:center"><?php echo (int)$source['operations']; ?></td>
<td style="text-align:center"><?php echo to_currency($source['expenses']); ?></td>
<td style="text-align:center"><?php echo $source['last_sale'] ? html_escape($source['last_sale']) : 'Sin ventas hoy'; ?></td>
<td style="text-align:center"><?php echo $source['error'] ? html_escape($source['error']) : 'Conectada'; ?></td></tr>
<?php endforeach; ?></tbody></table>
<p style="color:#64748b;font-size:12px;margin-top:20px">Los indicadores de una base sin conexión no se incluyen en el consolidado. La utilidad, cortes y créditos se agregarán tras validar las consultas originales.</p>
</section>