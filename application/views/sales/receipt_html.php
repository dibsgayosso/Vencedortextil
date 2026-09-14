<?php
foreach ($payments as $payment_id => $payment) {
    ?>
    <tr>
        <td colspan="3" align="right">
            <div class="invoice-footer-heading"><?php echo (isset($show_payment_times) && $show_payment_times) ? date(get_date_format() . ' ' . get_time_format(), strtotime($payment->payment_date)) : lang('common_payment'); ?></div>

            <?php if (($is_integrated_credit_sale || sale_has_partial_credit_card_payment($cart) || $is_sale_integrated_ebt_sale || sale_has_partial_ebt_payment($cart)) && ($payment->payment_type == lang('common_credit') || $payment->payment_type == lang('sales_partial_credit') || $payment->payment_type == lang('common_ebt') || $payment->payment_type == lang('common_partial_ebt') || $payment->payment_type == lang('common_ebt_cash') || $payment->payment_type == lang('common_partial_ebt_cash'))) { ?>
                <div class="invoice-footer-value"><?php echo $is_sale_integrated_ebt_sale ? 'EBT ' : ''; ?><?php echo H($payment->card_issuer . ': ' . $payment->truncated_card); ?></div>
            <?php } else { ?>
                <div class="invoice-footer-value">&nbsp;<?php $splitpayment = explode(':', $payment->payment_type); echo H($splitpayment[0]); ?></div>
            <?php } ?>
        </td>

        <td align="right">
            <div class="invoice-footer-value invoice-payment">
                <?php
                if (isset($exchange_name) && $exchange_name) {
                    ?>
                    <?php echo $this->config->item('round_cash_on_sales') && $payment->payment_type == lang('common_cash') ? to_currency_as_exchange($cart, round_to_nearest_05($payment->payment_amount)) : to_currency_as_exchange($cart, $payment->payment_amount); ?>
                <?php } else { ?>
                    <?php echo $this->config->item('round_cash_on_sales') && $payment->payment_type == lang('common_cash') ? to_currency(round_to_nearest_05($payment->payment_amount)) : to_currency($payment->payment_amount); ?>
                <?php
                }
                ?>
            </div>
        </td>
    </tr>
    
    <?php if (strpos($payment->payment_type, lang('common_credit')) !== false) { ?>
        <tr>
            <td colspan="6" class="invoice-policy" style="padding: 10px 0; border-top: 1px dashed #ccc; font-size: 11px;">
                <strong>PAGARÉ Y CONVENIO DE INTERESES:</strong><br>
                El cliente se obliga a pagar a <?php echo $company; ?> la cantidad de <?php echo to_currency($payment->payment_amount); ?> 
                (<?php echo num_to_words($payment->payment_amount); ?> pesos) a más tardar el <?php echo date('d/m/Y', strtotime('+30 days')); ?>.<br>
                <strong>INTERESES MORATORIOS:</strong> En caso de incumplimiento, se generará un interés moratorio del 10% mensual 
                sobre el saldo insoluto, calculado desde la fecha de vencimiento hasta la fecha de pago total.
                <div style="margin-top: 15px; border-top: 1px solid #000; width: 60%; padding-top: 5px;">
                    Firma del cliente: ___________________________<br>
                    Nombre: ____________________________________<br>
                    RFC: _______________________________________
                </div>
            </td>
        </tr>
    <?php } ?>
    
    <?php if (/* condicional para pagos con tarjeta */) { ?>
        <tr>
            <td colspan="4">
                <!-- Detalles adicionales del pago -->
            </td>
        </tr>
    <?php } ?>
<?php
}
?>