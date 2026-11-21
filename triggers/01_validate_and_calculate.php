<?php
/**
 * Reference ProcessMaker 3.5.8 trigger.
 * Attach after the request Dynaform submission and before the decision gateway.
 * Treat browser-supplied TOTAL_AMOUNT as untrusted.
 */

$threshold = 2000000000;

$name = trim((string) @@REQUESTER_NAME);
$phone = trim((string) @@REQUESTER_PHONE);
$product = trim((string) @@PRODUCT_NAME);
$qtyRaw = (string) @@QUANTITY;
$priceRaw = (string) @@UNIT_PRICE;

if ($name === '') {
    throw new Exception('نام و نام خانوادگی را وارد کنید.');
}
if (!preg_match('/^09[0-9]{9}$/', $phone)) {
    throw new Exception('شماره موبایل معتبر وارد کنید.');
}
if ($product === '') {
    throw new Exception('نام کالا الزامی است.');
}
if (!preg_match('/^[0-9]+$/', $qtyRaw) || (int) $qtyRaw <= 0) {
    throw new Exception('تعداد باید عدد صحیح بزرگ‌تر از صفر باشد.');
}
if (!preg_match('/^[0-9]+$/', $priceRaw) || (int) $priceRaw <= 0) {
    throw new Exception('قیمت واحد باید عدد صحیح بزرگ‌تر از صفر باشد.');
}

$qty = (int) $qtyRaw;
$price = (int) $priceRaw;

// Avoid overflow before multiplication. PHP integer arithmetic is used;
// supported deployment must be 64-bit PHP for the configured threshold.
if ($qty > intdiv(PHP_INT_MAX, $price)) {
    throw new Exception('مبلغ کل خارج از محدوده مجاز محاسبات است.');
}
$total = $qty * $price;

@@THRESHOLD_AMOUNT = $threshold;
@@TOTAL_AMOUNT = $total;
@@REQUEST_DATE = date('Y-m-d H:i:s');
@@REQUEST_STATUS = ($total <= $threshold) ? 'APPROVED' : 'REJECTED';
@@REVIEW_RESULT = @@REQUEST_STATUS;
@@REVIEW_DATE = date('Y-m-d H:i:s');

if ($total > $threshold) {
    @@REVIEW_REASON = 'درخواست خرید به دلیل بیشتر بودن مبلغ کل از سقف مجاز، به‌صورت خودکار رد شد.';
} else {
    @@REVIEW_REASON = 'درخواست خرید بر اساس مبلغ کل، به‌صورت خودکار تأیید شد و به مرحله پرداخت ارسال گردید.';
}
