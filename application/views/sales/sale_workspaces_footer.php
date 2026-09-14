<?php
if ($this->uri->segment(1) !== 'sales' || !class_exists('PHPPOSCartSale'))
{
    return;
}

$workspaces = $this->session->userdata('sale_workspaces');
$current_cart_id = $this->session->userdata('current_sale_cart_id');
$workspace_ui = array();

if (is_array($workspaces))
{
    foreach ($workspaces as $workspace_id => $workspace)
    {
        $workspace_cart = PHPPOSCartSale::get_instance($workspace_id);
        $workspace_ui[] = array(
            'id' => $workspace_id,
            'label' => isset($workspace['label']) ? $workspace['label'] : 'Venta',
            'line_count' => count($workspace_cart->get_items()),
            'total' => to_currency($workspace_cart->get_total()),
            'active' => $workspace_id === $current_cart_id
        );
    }
}

if (empty($workspace_ui))
{
    return;
}

$new_workspace_url = site_url('sales') . '?sale_workspace_action=new';
$switch_workspace_url = site_url('sales') . '?sale_workspace_action=switch&cart_id=';
$close_workspace_url = site_url('sales') . '?sale_workspace_action=close&cart_id=';
?>

<style>
.sale-workspaces-wrap {
    margin: 12px 0 14px 0;
    padding: 0 15px;
}
.sale-workspaces-toolbar {
    margin-bottom: 8px;
}
.sale-workspaces-new {
    display: inline-block;
    background: #3f9fe8;
    color: #fff !important;
    padding: 7px 12px;
    border-radius: 2px;
    text-decoration: none !important;
    font-weight: 600;
}
.sale-workspaces-new:hover,
.sale-workspaces-new:focus {
    background: #318fd5;
    color: #fff !important;
}
.sale-workspaces-new.disabled {
    opacity: .55;
    pointer-events: none;
}
.sale-workspaces-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.sale-workspace-card {
    position: relative;
    min-width: 185px;
    max-width: 260px;
    background: #fff;
    border: 1px solid #d9e1e8;
    border-radius: 5px;
    padding: 9px 34px 9px 11px;
    cursor: pointer;
    box-shadow: 0 1px 2px rgba(0,0,0,.04);
    transition: border-color .15s ease, box-shadow .15s ease;
}
.sale-workspace-card:hover {
    border-color: #83c8ef;
    box-shadow: 0 1px 4px rgba(0,0,0,.10);
}
.sale-workspace-card.active {
    border: 2px solid #55c0e8;
    padding: 8px 33px 8px 10px;
}
.sale-workspace-title {
    font-size: 14px;
    font-weight: 700;
    color: #333;
    margin-bottom: 3px;
}
.sale-workspace-meta {
    font-size: 12px;
    color: #6f7a85;
    white-space: nowrap;
}
.sale-workspace-close {
    position: absolute;
    right: 7px;
    top: 7px;
    border: 0;
    background: transparent;
    color: #9da7b0;
    font-size: 18px;
    line-height: 18px;
    padding: 0 3px;
}
.sale-workspace-close:hover {
    color: #d9534f;
}
@media (max-width: 767px) {
    .sale-workspaces-wrap {
        padding: 0 8px;
    }
    .sale-workspace-card {
        min-width: 145px;
        flex: 1 1 145px;
    }
}
</style>

<script>
$(function() {
    var workspaces = <?php echo json_encode($workspace_ui); ?>;
    var newUrl = <?php echo json_encode($new_workspace_url); ?>;
    var switchUrl = <?php echo json_encode($switch_workspace_url); ?>;
    var closeUrl = <?php echo json_encode($close_workspace_url); ?>;

    if (!workspaces || !workspaces.length || !$('.register').length) {
        return;
    }

    var $wrap = $('<div>', { 'class': 'sale-workspaces-wrap hidden-print' });
    var $toolbar = $('<div>', { 'class': 'sale-workspaces-toolbar' });
    var $newButton = $('<a>', {
        'class': 'sale-workspaces-new',
        'href': workspaces.length >= 10 ? '#' : newUrl,
        'text': '+ Nueva ventana de venta'
    });

    if (workspaces.length >= 10) {
        $newButton.addClass('disabled').attr('title', 'Maximo 10 ventas simultaneas');
    }

    $toolbar.append($newButton);
    $wrap.append($toolbar);

    var $list = $('<div>', { 'class': 'sale-workspaces-list' });

    $.each(workspaces, function(index, workspace) {
        var $card = $('<div>', {
            'class': 'sale-workspace-card' + (workspace.active ? ' active' : ''),
            'data-cart-id': workspace.id,
            'role': 'button',
            'tabindex': 0
        });

        $('<div>', {
            'class': 'sale-workspace-title',
            'text': workspace.label
        }).appendTo($card);

        $('<div>', {
            'class': 'sale-workspace-meta',
            'text': workspace.total + ' · ' + workspace.line_count + (workspace.line_count === 1 ? ' articulo' : ' articulos')
        }).appendTo($card);

        if (workspaces.length > 1) {
            var $close = $('<button>', {
                'type': 'button',
                'class': 'sale-workspace-close',
                'html': '&times;',
                'title': 'Cerrar esta venta'
            });

            $close.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var message = workspace.line_count > 0
                    ? 'Esta venta tiene articulos. ¿Deseas cerrarla y descartar su contenido?'
                    : '¿Deseas cerrar esta venta?';

                if (window.confirm(message)) {
                    window.location.href = closeUrl + encodeURIComponent(workspace.id);
                }
            });

            $card.append($close);
        }

        var openWorkspace = function() {
            if (!workspace.active) {
                window.location.href = switchUrl + encodeURIComponent(workspace.id);
            }
        };

        $card.on('click', openWorkspace);
        $card.on('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openWorkspace();
            }
        });

        $list.append($card);
    });

    $wrap.append($list);
    $('.register').first().before($wrap);
});
</script>
