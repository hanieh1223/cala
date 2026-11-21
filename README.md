# فرایند مدیریت درخواست خرید — ProcessMaker 3.5.8

> وضعیت تحویل: **بسته مرجع و مستندات آماده شده؛ فایل PMX نهایی هنوز تولید نشده است.** این مخزن شامل مدل BPMN مرجع، SQL، کدهای Trigger مرجع، نقشه فیلدها و تست‌های مستقل قواعد کسب‌وکار است. Import و اجرای واقعی در ProcessMaker تأیید نشده است.

## قانون تصمیم‌گیری

همه مبالغ بر حسب تومان و از نوع عدد صحیح هستند:

- `TOTAL_AMOUNT = QUANTITY * UNIT_PRICE`
- حد نصاب: `2,000,000,000`
- تأیید: `TOTAL_AMOUNT <= 2000000000`
- رد خودکار: `TOTAL_AMOUNT > 2000000000`

محاسبه نهایی در سمت سرور انجام می‌شود. مبلغ کل ارسالی از مرورگر منبع معتبر نیست.

## ساختار مخزن

- `process/purchase-request-reference.bpmn`: مدل مرجع BPMN 2.0؛ جایگزین PMX نیست.
- `process/README.md`: وضعیت و محدودیت مدل BPMN.
- `tables/purchase-tables.sql`: تعریف سه جدول عملیاتی و ایندکس‌های یکتا.
- `triggers/00_generate_request_number.php`: تولید شماره درخواست سمت سرور بر پایه Case UID.
- `triggers/01_validate_and_calculate.php`: اعتبارسنجی و محاسبه مبلغ.
- `triggers/02_persist_request_and_review.php`: ثبت درخواست و نتیجه بررسی.
- `triggers/03_validate_and_persist_payment.php`: اعتبارسنجی/ثبت پرداخت و تغییر وضعیت.
- `docs/field-map.md`: قرارداد فیلدهای سه فرم.
- `docs/trigger-attachment-map.md`: محل اتصال Triggerها و موارد نیازمند تطبیق.
- `docs/process-design.md`, `docs/forms-and-validation.md`, `docs/deployment-and-roles.md`: راهنمای طراحی و استقرار.
- `docs/test-plan.md`: ماتریس آزمون و وضعیت شواهد.
- `tests/`: تست‌های مستقل منطق کسب‌وکار.

## نکته مهم درباره PMX

در ProcessMaker 3.5.8، فایل `.pmx` بسته Export بومی ProcessMaker است و صرفاً تغییر پسوند یک فایل BPMN یا ZIP دلخواه، آن را به PMX معتبر تبدیل نمی‌کند. خروجی بومی شامل اشیای ProcessMaker مانند Variables، Dynaforms، Triggers و تنظیمات فرایند است. PM Tables نیز جداگانه منتقل می‌شوند.

برای تولید PMX واقعی باید فرایند در یک نمونه ProcessMaker 3.5.8 ساخته و از Designer با گزینه Export Process صادر شود. تا زمانی که این مرحله روی نمونه واقعی انجام نشده، فایلی با پسوند PMX در این مخزن قرار داده نمی‌شود تا فایل نمایشی یا غیرقابل Import به‌عنوان خروجی نهایی جا زده نشود.

## راه‌اندازی

1. پایگاه داده و نسخه PHP/ProcessMaker مقصد را پشتیبان‌گیری کنید.
2. فایل `tables/purchase-tables.sql` را در دیتابیس مناسب اجرا کنید؛ پیشوند، موتور DB و دسترسی‌ها را با سیاست نصب خود تطبیق دهید.
3. فرایند را مطابق `docs/process-design.md` در Designer نسخه 3.5.8 ایجاد کنید.
4. فیلدهای سه فرم را طبق `docs/field-map.md` بسازید.
5. Triggerها را مطابق `docs/trigger-attachment-map.md` متصل و پیش از اجرا با API دیتابیس همان نصب تطبیق دهید.
6. Triggerها را ابتدا در محیط آزمایشی اجرا و دسترسی SQL را محدود کنید.
7. پس از تکمیل در Designer، از خود ProcessMaker خروجی PMX بگیرید و Import آن را در یک workspace آزمایشی دیگر بررسی کنید.
8. کاربران/گروه‌های تخصیص‌یافته را در محیط مقصد جداگانه تنظیم کنید.

**هشدار:** کد Triggerهای فعلی الگوی مرجع است. Queryها باید با API پارامتری/امن نصب، تراکنش و رفتار idempotent تکمیل شوند؛ به‌ویژه `addslashes` به‌تنهایی روش امن کافی برای ساخت SQL نیست. بدون این بازبینی روی محیط عملیاتی اجرا نکنید.

## آزمون

از ریشه مخزن اجرا کنید:

```bash
python -m unittest discover -s tests -v
```

این تست‌ها آزمون واحد منطق پایتون هستند و **تست ProcessMaker نیستند**. در محیط تهیه این مخزن فقط آزمون‌های مستقل محاسبه/حدنصاب شامل ۵ حالت اجرا شدند و همگی موفق بودند. هیچ نمونه ProcessMaker 3.5.8 برای Import، اجرای Case، Trigger، دیتابیس یا آزمون UI در دسترس نبوده است.

## منابع فنی

- [ProcessMaker 3.1–3.8: Importing and Exporting Processes](https://wiki.processmaker.net/node/591)
- [ProcessMaker 3.5–3.8: REST API Dynaforms](https://wiki.processmaker.net/3.2/REST_API_Designer/Dynaforms)

## وضعیت معیارهای تحویل

| معیار | وضعیت |
|---|---|
| منطق مبلغ و مرز حد نصاب | کد مرجع؛ ۵ آزمون مستقل محاسباتی اجرا شد |
| SQL سه جدول | فایل SQL ایجاد شده؛ روی DB مقصد اجرا نشده |
| Triggerها | کد مرجع ایجاد شده؛ نیازمند بازبینی API، تراکنش و idempotency |
| مدل BPMN | مرجع BPMN 2.0؛ اجرایی در ProcessMaker نیست |
| Dynaformهای واقعی ProcessMaker | نقشه فیلدها موجود؛ خود فرم‌ها در Designer ساخته نشده‌اند |
| فایل PMX قابل Import | **ساخته نشده** |
| تست واقعی در ProcessMaker 3.5.8 | **انجام نشده** |
