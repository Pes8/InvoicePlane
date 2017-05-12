<?php

    $tax_rates_array = array();
    $show_discounts_items = false;
    $show_discounts_invoice = false;

    foreach ($items as $item) {
        if($item->item_tax_total > 0){
            if(!isset($tax_rates_array[$item->item_tax_rate_id])){
                $tax_rates_array[$item->item_tax_rate_id] = new stdClass;
                $tax_rates_array[$item->item_tax_rate_id]->percent = 0;
                $tax_rates_array[$item->item_tax_rate_id]->total = 0;
                $tax_rates_array[$item->item_tax_rate_id]->percent = $item->item_tax_rate_percent;
            }
            
            $tax_rates_array[$item->item_tax_rate_id]->total += $item->item_tax_total;
        }

        if($item->item_discount != 0){
            $show_discounts_items = true;
        }
    }

    if($invoice->invoice_discount_percent != 0){
        $show_discounts_invoice = true;
    }

    $CI = & get_instance();
    $CI->load->model('payment_methods/mdl_payment_methods');
    $payment = $this->mdl_payment_methods->get_name_by_id($invoice->payment_method);

?>
<html lang="<?php echo trans('cldr'); ?>">
<head>
    <meta charset="utf-8">
    <title><?php echo trans('invoice'); ?></title>
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/default/css/templates.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/template-1-invoice-pdf.css">
</head>
<body>
<header class="clearfix">

    <div id="company">
        <div><b><span class="logo-blue"><?php echo $invoice->user_company; ?></span></b></div>
        <div><span class="logo-blue"><?php echo $invoice->user_address_1; ?> <?php echo $invoice->user_city . '(' . $invoice->user_state .')'. ' - ' . $invoice->user_zip; ?></span></div>
        <?php 
            if(!empty($invoice->user_address_2)):
        ?>
            <div><span class="logo-blue"><?php echo $invoice->user_address_2; ?></span></div>
        <?php
            endif;
        ?>
        <?php if(!empty($invoice->user_custom_registro_imprese)): ?><div><span class="small-text">Registro Imprese</span><span class="logo-blue"><?php echo $invoice->user_custom_registro_imprese; ?></span></div><?php endif; ?>
        <?php if(!empty($invoice->user_vat_id)): ?><div><span class="small-text">P.IVA: </span><span class="logo-blue"><?php echo $invoice->user_vat_id; ?></span></div><?php endif; ?>
        <?php if(!empty($invoice->user_tax_code)): ?><div><span class="small-text">Cod.Fisc: </span><span class="logo-blue"><?php echo $invoice->user_tax_code; ?></span></div><?php endif; ?>
        <?php if(!empty($invoice->user_email)): ?><div><span class="small-text">Email: </span><span class="logo-blue"><?php echo $invoice->user_email; ?></span></div><?php endif; ?>
        <?php if(!empty($invoice->user_web)): ?><div><span class="small-text">Sito: </span><span class="logo-blue"><?php echo $invoice->user_web; ?></span></div><?php endif; ?>

    </div>
        
    <div style="float: right;width: 30%;text-align: center;">
        <br/>
        <span><strong>FATTURA</strong></span>
    </div>
    
    <div class="clearfix"></div>
    <br>

    <div id="client">
        <span class="th-small-title">Spett.le ditta</span>
        <div>
            <b><?php echo $invoice->client_name; ?></b>
        </div>
        <?php


        if ($invoice->client_address_1) {
            echo '<div>' . $invoice->client_address_1 . '</div>';
        }
        if ($invoice->client_address_2) {
            echo '<div>' . $invoice->client_address_2 . '</div>';
        }
        if ($invoice->client_city && $invoice->client_zip) {
            echo '<div>' . $invoice->client_zip . ' - ' . $invoice->client_city;
        } else {
            if ($invoice->client_city) {
                echo '<div>' . $invoice->client_city;
            }
            if ($invoice->client_zip) {
                echo '<div>' . $invoice->client_zip;
            }
        }
        if ($invoice->client_state) {
            echo ' (' . $invoice->client_state . ')</div>';
        } else {
            echo '</div>';
        }

        /*if ($invoice->client_country) {
            echo '<div>' . get_country_name(trans('cldr'), $invoice->client_country) . '</div>';
        }*/

        if ($invoice->client_vat_id) {
            echo '<div>' . trans('vat_id_short') . ': ' . $invoice->client_vat_id . '</div>';
        }
        if ($invoice->client_tax_code) {
            echo '<div>' . trans('tax_code_short') . ': ' . $invoice->client_tax_code . '</div>';
        }

        echo '<br/>';

        if ($invoice->client_phone) {
            echo '<div>' . trans('phone_abbr') . ': ' . $invoice->client_phone . '</div>';
        } ?>

    </div>

    <div class="invoice-details clearfix">
        <table>
            <tr>
                <td>Fattura N°</td>
                <td style="text-align: center;"><strong><?php echo $invoice->invoice_number; ?></strong></td>
            </tr>
            <tr>
                <td>Data</td>
                <td><strong><?php echo date_from_mysql($invoice->invoice_date_created, true); ?></strong></td>
            </tr>
        </table>
    </div>

    <div class="clearfix"></div>
    <br/>

    <div id="th-payment-details">
        <div class="row clearfix">
            <div class="label-inline">Pagamento</div>
            <div class="content-after-label"><?php echo $payment; ?></div>
        </div>

        <div class="row clearfix">
            <div class="label-inline">Banca</div>
            <div class="content-after-label"><?php echo $invoice->user_custom_banca; ?></div>
        </div>

        <div class="row clearfix">
            <div class="label-inline">Codice IBAN</div>
            <div class="content-after-label"><strong><?php echo $invoice->user_custom_iban; ?></strong></div>
        </div>
    </div>

</header>

<main>

    <table class="item-table">
        <thead>
        <tr>
            <?php if(empty($invoice->invoice_custom_non_mostrare_quantita)): ?>
                <th class="item-amount text-right"><?php echo trans('qty'); ?></th>
            <?php endif; ?>
            <th class="item-desc"><?php echo trans('description'); ?></th>
            
            
            <th class="item-price text-right"><?php echo 'Importo' ?></th>

            <?php if ($show_discounts_items) : ?>
                <th class="item-discount text-right"><?php echo 'Sconto' ?></th>
                <th class="item-discount text-right"><?php echo 'Totale' ?></th>
            <?php endif; ?>

            <th class="item-total text-right"><?php echo trans('tax_rate'); ?></th>
        </tr>
        </thead>
        <tbody>

        <?php
        foreach ($items as $item) { ?>
            <tr>
                <?php if(empty($invoice->invoice_custom_non_mostrare_quantita)): ?>
                    <td class="text-right">
                        <?php echo $item->item_price > 0 ? format_amount($item->item_quantity) : null;?>
                    </td>
                <?php endif; ?>



                <td><?php echo $item->item_price <= 0 ? "<!--<strong>-->" : null; ?><?php echo nl2br($item->item_description); ?><?php echo $item->item_price <= 0 ? "<!--</strong>-->" : null; ?></td>
                


                <td class="text-right">
                    <?php echo $item->item_price > 0 ? format_currency($item->item_price * $item->item_quantity) : null; ?>
                </td>

                <?php if ($show_discounts_items) : ?>
                    <td class="text-right">
                        <?php echo format_currency($item->item_discount); ?>
                    </td>

                    <td class="text-right">
                        <?php echo format_currency($item->item_total); ?>
                    </td>
                <?php endif; ?>

                <td class="text-right">
                    <?php echo $item->item_price > 0 ? ($item->item_tax_rate_percent != NULL ? format_amount($item->item_tax_rate_percent) : '0') . '%' : null; ?>
                </td>
            </tr>
        <?php } ?>

        </tbody>
        
    </table>

</main>

<footer>
    
    <div clasS="box-info">
        <p><?php 
        if(empty($invoice->invoice_custom_non_mostrare_frase_reverse_charge)){
            echo $invoice->user_custom_reverse_charge;
        }
        echo "</p><br/><br/><p>";
        if(!empty($invoice->invoice_custom_riga_aggiuntiva)){
            echo $invoice->invoice_custom_riga_aggiuntiva;
        }
        ?></p>
    </div>

    <div class="box-totale">
        <table style="float:right">
            <tr>
                <td>
                    <?php echo $show_discounts_invoice ? 'SubTotale' : 'Imponibile:'; ?>
                </td>
                <td class="txt-right">
                    <?php echo format_currency($invoice->invoice_item_subtotal); ?>
                </td>
            </tr>
            <?php if ($show_discounts_invoice) : ?>
            <tr>
                <td>
                    Sconto:
                </td>
                <td class="txt-right">
                    <?php echo $invoice->invoice_discount_percent; ?> %
                </td>
            </tr>
            <?php endif; ?>
            <?php
            foreach($tax_rates_array as $tax){
                echo "<tr><td>Imposta " . format_amount($tax->percent) . '%</td><td class="txt-right">' . format_currency($tax->total) . '</td></tr>';
            }
            ?>

            <tr>
                <td>
                    <br/><strong>Totale Fattura:</strong>
                </td>
                <td class="txt-right">
                    <br/><strong><?php echo format_currency($invoice->invoice_total); ?></strong>
                </td>
            </tr>
        </table>

    </div>
</footer>
</body>
</html>