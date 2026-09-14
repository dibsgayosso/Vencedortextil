<?php
$company = ($company = $this->Location->get_info_for_key('company')) ? $company : $this->config->item('company');
?>
<div class="dashboard-container">
    <!-- Tarjetas de resumen mejoradas -->
    <div class="summary-cards-grid">
        <?php foreach($summary_data as $name=>$value) { ?>
            <div class="summary-card">
                <div class="card-icon">
                    <?php 
                    // Iconos diferentes según el tipo de dato
                    $icon = 'fa-dollar-sign';
                    if ($name == 'sales_per_time_period') $icon = 'fa-chart-line';
                    if ($name == 'total_customers') $icon = 'fa-users';
                    if ($name == 'average_sale') $icon = 'fa-calculator';
                    if ($name == 'items_sold') $icon = 'fa-shopping-cart';
                    ?>
                    <i class="fas <?php echo $icon; ?>"></i>
                </div>
                <div class="card-content">
                    <div class="card-value">
                        <?php
                        if($name == 'sales_per_time_period') {
                            echo str_replace(' ','&nbsp;', to_quantity($value));
                        } else {
                            echo to_currency($value);
                        }
                        ?>
                    </div>
                    <div class="card-label"><?php echo lang('reports_'.$name); ?></div>
                </div>
            </div>
        <?php }?>
    </div>

    <!-- Panel de gráfica mejorado -->
    <div class="chart-panel">
        <div class="panel-header">
            <div class="panel-title">
                <i class="fas fa-chart-bar"></i>
                <?php echo lang('reports_reports'); ?> - <?php echo $company; ?> <?php echo $title ?>
            </div>
            <div class="panel-actions">
                <?php if($key) { ?>
                    <a href="<?php echo site_url("reports/delete_saved_report/".$key);?>" class="btn btn-danger delete_saved_report">
                        <i class="fas fa-trash"></i> <?php echo lang('reports_unsave_report'); ?>
                    </a>	
                <?php } else { ?>
                    <button class="btn btn-primary save_report_button" data-message="<?php echo H(lang('reports_enter_report_name'));?>">
                        <i class="fas fa-save"></i> <?php echo lang('reports_save_report'); ?>
                    </button>
                <?php } ?>
            </div>
        </div>
        
        <div class="panel-body">
            <div class="chart-container">
                <div class="chart-legend-container">
                    <div id="chart-legend" class="chart-legend"></div>
                </div>
                <div class="chart-canvas-container">
                    <canvas id="chart"></canvas>
                </div>
            </div>
            
            <!-- Controles de gráfica (opcional) -->
            <div class="chart-controls">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-secondary chart-type-btn active" data-type="bar">
                        <i class="fas fa-chart-bar"></i> <?php echo lang('reports_bar'); ?>
                    </button>
                    <button type="button" class="btn btn-outline-secondary chart-type-btn" data-type="line">
                        <i class="fas fa-chart-line"></i> <?php echo lang('reports_line'); ?>
                    </button>
                    <button type="button" class="btn btn-outline-secondary chart-type-btn" data-type="pie">
                        <i class="fas fa-chart-pie"></i> <?php echo lang('reports_pie'); ?>
                    </button>
                </div>
                
                <div class="date-range-selector">
                    <select class="form-control form-control-sm">
                        <option value="today"><?php echo lang('reports_today'); ?></option>
                        <option value="week"><?php echo lang('reports_this_week'); ?></option>
                        <option value="month" selected><?php echo lang('reports_this_month'); ?></option>
                        <option value="year"><?php echo lang('reports_this_year'); ?></option>
                        <option value="custom"><?php echo lang('reports_custom'); ?></option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-container {
    padding: 20px;
    background-color: #f8f9fa;
    min-height: 100vh;
}

/* Estilos para las tarjetas de resumen */
.summary-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.summary-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-left: 4px solid #4e73df;
}

.summary-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}

.card-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    flex-shrink: 0;
}

.card-icon i {
    font-size: 24px;
    color: white;
}

.card-content {
    flex: 1;
}

.card-value {
    font-size: 24px;
    font-weight: 700;
    color: #2e384d;
    margin-bottom: 5px;
}

.card-label {
    font-size: 14px;
    color: #8798ad;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Estilos para el panel de gráfica */
.chart-panel {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 30px;
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    background: linear-gradient(135deg, #f8f9fc 0%, #e3e6f0 100%);
    border-bottom: 1px solid #e3e6f0;
}

.panel-title {
    font-size: 18px;
    font-weight: 600;
    color: #2e384d;
    display: flex;
    align-items: center;
}

.panel-title i {
    margin-right: 10px;
    color: #4e73df;
}

.panel-actions .btn {
    border-radius: 8px;
    padding: 8px 16px;
    font-weight: 500;
}

.panel-body {
    padding: 25px;
}

.chart-container {
    display: flex;
    margin-bottom: 20px;
}

.chart-legend-container {
    width: 200px;
    padding-right: 20px;
    flex-shrink: 0;
}

.chart-canvas-container {
    flex: 1;
    min-height: 400px;
    position: relative;
}

.chart-legend {
    background: #f8f9fc;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #e3e6f0;
}

.chart-legend div {
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    font-size: 13px;
}

.chart-legend span {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 3px;
    margin-right: 8px;
}

.chart-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 20px;
    border-top: 1px solid #e3e6f0;
}

.btn-group .btn {
    border-radius: 6px;
    margin-right: 5px;
}

.btn-group .btn.active {
    background: #4e73df;
    color: white;
    border-color: #4e73df;
}

.date-range-selector {
    width: 150px;
}

/* Responsive */
@media (max-width: 768px) {
    .summary-cards-grid {
        grid-template-columns: 1fr;
    }
    
    .chart-container {
        flex-direction: column;
    }
    
    .chart-legend-container {
        width: 100%;
        padding-right: 0;
        margin-bottom: 20px;
    }
    
    .chart-controls {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
    
    .date-range-selector {
        width: 100%;
    }
}
</style>

<script type="text/javascript">
// Cargar Chart.js si no está cargado
if (typeof Chart === 'undefined') {
    document.write('<script src="<?php echo base_url();?>assets/js/Chart.min.js"><\/script>');
}

// Script para cambiar tipo de gráfica
document.addEventListener('DOMContentLoaded', function() {
    // Manejar cambio de tipo de gráfica
    const chartTypeButtons = document.querySelectorAll('.chart-type-btn');
    let currentChart = null;
    
    chartTypeButtons.forEach(button => {
        button.addEventListener('click', function() {
            chartTypeButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Aquí implementarías la lógica para cambiar el tipo de gráfica
            const chartType = this.getAttribute('data-type');
            changeChartType(chartType);
        });
    });
    
    function changeChartType(type) {
        // Esta función cambiaría el tipo de gráfica
        // Necesitarías implementar la lógica específica según tu gráfica
        console.log('Cambiando a gráfica tipo:', type);
    }
    
    <?php $this->load->view('reports/outputs/graphs/'.$graph); ?>
});
</script>