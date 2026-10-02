<?php $this->load->view('partial/header'); ?>
<div class="panel panel-piluku">
    <div class="panel-heading"><h3>Cancelaciones de ventas en curso</h3>
        <p><?php echo html_escape($location_name); ?> · Auditoría de tickets cancelados antes de completar la venta</p>
    </div>
    <div class="panel-body">
        <form action="<?php echo site_url('reports/cancelled_sales'); ?>" method="get" class="form-inline">
            <div class="form-group">
                <label for="audit_start_date">Desde</label>
                <input id="audit_start_date" name="start_date" type="date" class="form-control"
                    value="<?php echo html_escape($start_date); ?>" <?php echo $can_change_date ? '' : 'readonly'; ?>>
            </div>
            <div class="form-group">
                <label for="audit_end_date">Hasta</label>
                <input id="audit_end_date" name="end_date" type="date" class="form-control"
                    value="<?php echo html_escape($end_date); ?>" <?php echo $can_change_date ? '' : 'readonly'; ?>>
            </div>
            <button type="submit" class="btn btn-primary">Consultar</button>
        </form>
        <?php if ($audit_error) { ?>
            <div class="alert alert-danger" style="margin-top:15px;"><?php echo html_escape($audit_error); ?></div>
        <?php } else { ?>
            <div class="well" style="margin-top:15px;">
                <strong><?php echo (int)$summary['operations']; ?> cancelaciones</strong>
                · Importe de tickets cancelados: <?php echo to_currency($summary['total']); ?>
                <p class="text-muted">Este importe corresponde a tickets descartados; no representa ventas cobradas.</p>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr>
                        <th scope="col">Folio de auditoría</th><th scope="col">Fecha y hora</th>
                        <th scope="col">Empleado que canceló</th><th scope="col">Caja</th>
                        <th scope="col">Motivo</th><th scope="col">Importe</th><th scope="col">Ticket</th>
                    </tr></thead>
                    <tbody>
                    <?php if (!$rows) { ?>
                        <tr><td colspan="7">No hay cancelaciones en este periodo.</td></tr>
                    <?php } ?>
                    <?php foreach ($rows as $row) {
                        $ticket = json_decode($row['ticket_data'], TRUE);
                        $employee_name = trim($row['first_name'].' '.$row['last_name']);
                    ?>
                        <tr>
                            <td><?php echo (int)$row['cancelled_sale_id']; ?></td>
                            <td><?php echo html_escape($row['cancelled_at']); ?></td>
                            <td><?php echo html_escape($employee_name ?: 'Empleado #'.$row['employee_id']); ?></td>
                            <td><?php echo html_escape($row['register_name'] ?: 'Sin caja'); ?></td>
                            <td style="white-space:pre-wrap;overflow-wrap:anywhere;"><?php echo html_escape($row['reason']); ?></td>
                            <td><?php echo to_currency($row['total']); ?></td>
                            <td><details><summary>Ver productos</summary>
                                <?php if (is_array($ticket) && isset($ticket['items']) && is_array($ticket['items'])) { ?>
                                <ul>
                                    <?php foreach ($ticket['items'] as $item) { ?>
                                    <li><?php echo html_escape(isset($item['name']) ? $item['name'] : '');
                                        echo ' · Cantidad: '.html_escape(isset($item['quantity']) ? (string)$item['quantity'] : '');
                                        if (isset($item['unit_price'])) echo ' · Precio: '.to_currency($item['unit_price']); ?></li>
                                    <?php } ?>
                                </ul>
                                <?php } else { ?><p>El detalle del ticket no está disponible.</p><?php } ?>
                                <?php if ($row['comment']) { ?><p>Comentario: <?php echo html_escape($row['comment']); ?></p><?php } ?>
                            </details></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
            <p>Página <?php echo (int)$page; ?> · Hasta 100 registros por página</p>
            <?php
            $query = array('start_date' => $start_date, 'end_date' => $end_date);
            if ($page > 1) {
                $query['page'] = $page - 1;
            ?>
                <a class="btn btn-default" href="<?php echo html_escape(site_url('reports/cancelled_sales').'?'.http_build_query($query)); ?>">Anterior</a>
            <?php }
            if ($page * 100 < (int)$summary['operations']) {
                $query['page'] = $page + 1;
            ?>
                <a class="btn btn-default" href="<?php echo html_escape(site_url('reports/cancelled_sales').'?'.http_build_query($query)); ?>">Siguiente</a>
            <?php } ?>
        <?php } ?>
    </div>
</div>
<?php $this->load->view('partial/footer'); ?>
