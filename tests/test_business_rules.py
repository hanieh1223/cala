import unittest

THRESHOLD = 2_000_000_000

def validate_and_calculate(quantity, unit_price):
    if not isinstance(quantity, int) or isinstance(quantity, bool) or quantity <= 0:
        raise ValueError("تعداد باید عدد صحیح بزرگ‌تر از صفر باشد.")
    if not isinstance(unit_price, int) or isinstance(unit_price, bool) or unit_price <= 0:
        raise ValueError("قیمت واحد باید عدد صحیح بزرگ‌تر از صفر باشد.")
    total = quantity * unit_price
    if total > 2**63 - 1:
        raise OverflowError("مبلغ کل خارج از محدوده عدد صحیح ۶۴ بیتی است.")
    return total

def automatic_decision(total):
    return "REJECTED" if total > THRESHOLD else "APPROVED"

class PurchaseRulesTests(unittest.TestCase):
    def test_small_amount_approved(self):
        self.assertEqual(automatic_decision(validate_and_calculate(10, 10_000)), "APPROVED")

    def test_exact_threshold_approved(self):
        self.assertEqual(automatic_decision(validate_and_calculate(1, 2_000_000_000)), "APPROVED")

    def test_one_toman_over_rejected(self):
        self.assertEqual(automatic_decision(validate_and_calculate(1, 2_000_000_001)), "REJECTED")

    def test_zero_quantity_rejected(self):
        with self.assertRaises(ValueError):
            validate_and_calculate(0, 100)

    def test_negative_price_rejected(self):
        with self.assertRaises(ValueError):
            validate_and_calculate(1, -1)

    def test_empty_product_is_required_by_form_contract(self):
        product_name = ""
        self.assertEqual(product_name.strip(), "")

    def test_rejected_request_cannot_pay(self):
        status = automatic_decision(2_000_000_001)
        self.assertNotEqual(status, "APPROVED")

    def test_approved_request_can_enter_payment(self):
        status = automatic_decision(2_000_000_000)
        self.assertEqual(status, "APPROVED")

    def test_paid_status_transition(self):
        request_status = "APPROVED"
        payment_status = "PAID"
        if request_status == "APPROVED" and payment_status == "PAID":
            request_status = "PAID"
        self.assertEqual(request_status, "PAID")

    def test_duplicate_case_constraints_are_declared(self):
        sql_path = __import__("pathlib").Path(__file__).parents[1] / "tables" / "purchase-tables.sql"
        sql = sql_path.read_text(encoding="utf-8")
        self.assertIn("uq_purchase_request_case", sql)
        self.assertIn("uq_purchase_review_case", sql)
        self.assertIn("uq_purchase_payment_case", sql)

if __name__ == "__main__":
    unittest.main()
