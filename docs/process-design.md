# طراحی فرایند و نگاشت ProcessMaker 3.5.8

## مسیر اصلی

Start → ثبت درخواست (User Task) → اعتبارسنجی و محاسبه (Script/Trigger) → Gateway انحصاری

- مسیر رد با شرط `@@TOTAL_AMOUNT > 2000000000`: ثبت نتیجه رد → End رد.
- مسیر تأیید با شرط `@@TOTAL_AMOUNT <= 2000000000`: ثبت نتیجه تأیید → نمایش نتیجه/ادامه → ثبت پرداخت (User Task) → ثبت پرداخت و تغییر وضعیت → End موفق.

مسیر رد نباید به فرم پرداخت متصل باشد. نتیجه بررسی صرفاً متغیر سیستمی است و در هیچ Dynaform قابل ویرایش نیست.

## متغیرهای فرایند

| نام متغیر | نوع پیشنهادی | توضیح |
|---|---|---|
| REQUEST_NUMBER | String | شناسه یکتای قابل نمایش |
| REQUESTER_NAME | String | نام درخواست‌کننده |
| REQUESTER_PHONE | String | موبایل |
| PRODUCT_NAME | String | نام کالا |
| PRODUCT_DESCRIPTION | String | توضیحات اختیاری |
| QUANTITY | Integer | عدد صحیح مثبت |
| UNIT_PRICE | Integer | تومان، عدد صحیح مثبت |
| TOTAL_AMOUNT | Integer | فقط خروجی محاسبه سمت سرور |
| REQUEST_DATE | String/Date | تاریخ ثبت |
| REQUEST_STATUS | String | وضعیت سیستم |
| THRESHOLD_AMOUNT | Integer | مقدار ثابت 2000000000 |
| REVIEW_RESULT | String | APPROVED یا REJECTED |
| REVIEW_REASON | String | توضیح نتیجه |
| REVIEW_DATE | String/Date | تاریخ بررسی |
| PAYMENT_AMOUNT | Integer | مبلغ پرداخت |
| PAYMENT_STATUS | String | UNPAID یا PAID |
| PAYMENT_METHOD | String | روش پرداخت |
| PAYMENT_REFERENCE | String | شناسه پیگیری |
| PAYMENT_DATE | String/Date | تاریخ پرداخت |
| PAYMENT_DESCRIPTION | String | توضیح پرداخت |

## اتصال فعالیت‌ها و Triggerها

1. فرم درخواست: فقط فیلدهای ورودی درخواست‌کننده؛ REQUEST_NUMBER و REQUEST_DATE در فرم فقط خواندنی یا پنهان.
2. پس از Submit فرم و پیش از محاسبه: اعتبارسنجی سمت سرور و محاسبه مجدد TOTAL_AMOUNT.
3. پس از محاسبه: ارزیابی Gateway با متغیر سروری TOTAL_AMOUNT.
4. شاخه رد: Trigger ثبت درخواست و بررسی با وضعیت REJECTED، سپس End.
5. شاخه تأیید: Trigger ثبت درخواست و بررسی با وضعیت APPROVED؛ نمایش نتیجه فقط خواندنی؛ سپس فرم پرداخت.
6. فرم پرداخت فقط روی User Task شاخه تأیید قرار گیرد. PAYMENT_AMOUNT باید از TOTAL_AMOUNT مقداردهی شود و قابل ویرایش نباشد.
7. پیش از پایان موفق: اعتبارسنجی پرداخت، ثبت پرداخت و به‌روزرسانی درخواست به PAID.

## نکات حیاتی

- Trigger محاسبه باید پس از Submit فرم و پیش از Gateway اجرا شود.
- Triggerهای ثبت باید idempotent باشند: برای هر CASE_UID فقط یک رکورد در هر جدول.
- در Dynaform از JavaScript فقط برای تجربه کاربری/نمایش مبلغ استفاده شود؛ مرجع محاسبه و تصمیم سمت سرور است.
- برای جلوگیری از دستکاری، متغیرهای مبلغ، وضعیت و نتیجه بررسی را در Dynaform قابل ویرایش نکنید.
- اگر ثبت پرداخت به‌صورت «پرداخت شده» انتخاب شد، تاریخ پرداخت و روش پرداخت الزامی است. برای انتقال بانکی و کارت‌به‌کارت، شماره پیگیری الزامی باشد.
- برای این نسخه، ثبت پرداخت به معنی ثبت اعلام پرداخت است؛ تأییدیه بانکی یا اتصال درگاه پرداخت در دامنه پروژه نیست.
