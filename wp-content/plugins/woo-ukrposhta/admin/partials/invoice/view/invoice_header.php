<?php
use deliveryplugin\Ukrposhta\classes\invoice\InvoiceController;

$invoiceController = new InvoiceController();
?>
<script src="<?php echo MUP_PLUGIN_URL . 'admin/js/script.js?ver='.MUP_PLUGIN_VERSION.' '; ?>"></script>
<link rel="stylesheet" href="<?php echo MUP_PLUGIN_URL . 'admin/css/style.css?ver='.MUP_PLUGIN_VERSION.' '; ?>"/>

<?php $invoiceController->displayNav(); ?>

<div class="container">
    <h1 style="font-size:23px;font-weight:400;line-height:1.3;"><?php echo 'Нове відправлення Укрпошти №' . $order_id; ?></h1>
    <form class="form-invoice" action="admin.php?page=morkvaup_invoice" method="post" name="invoice">
        <?php  if ( isset( $invoiceModel ) && $invoiceModel->isSuccessSaved ) { ?>
            <div id="messagebox" class="messagebox_show updated" data="160" style="height:0px;padding:0px">
                <?php $invoiceController->displaySuccessNotice( $invoice ); ?>
            </div>
            <?php if ( $invoiceOrder->isNotDimensions && ! $invoiceOrder->getWPOptionLength() || ! $length ) : ?>
                <div class="notice notice-warning is-dismissible" style="margin:0 -5px 0 0;">
                    <?php $invoiceController->displayWarningNotice(); ?>
                </div>
            <?php endif; ?>
        <?php }
        elseif ( isset($message) && isset($_POST['sender_first_name']) || isset($_POST['up_company_sender_name']) || isset($_POST['up_company_sender_edrpou']) ) { ?>
        <div id="messagebox" class="messagebox_show error" data="110" style="height: 0px;padding:0px;">
            <div class="card text-white bg-danger">
                <h3>Накладну створити не вдалося.</h3>
                <p><?php echo $message . $ukrposhtaApi->httpCode401 . $ukrposhtaApi->httpCode403 . $ukrposhtaApi->httpCode404; ?></p>
                <div class="clr"></div>
            </div>
        </div>
        <?php } ?>
        <div class="alink">
            <?php
                if ( ! empty($order_data->get_id() ) ) {
                    echo '<a class="btn" href="/wp-admin/post.php?post=' . $order_data->get_id() . '&action=edit">Повернутись до замовлення</a>';
                echo '';
                }
            ?>
            <a href="edit.php?post_type=shop_order">Повернутись до замовлень</a>
        </div>
