# stripe-checker-cc-php
Stripe Credit Card Checker using PHP

**Installation:**
1. Drag and Drop all the files to your WebHosting.
2. Set your **Stripe SK Key** in `config.php`. (*It works for the 2 files*).

**Usage:**
1. Access `http://URL/checkercc.php?cc=40000000000000000|01|22|000`, and wait for the return. (Replace `?cc=` with the actual **Credit Card** you want to check)

**Troubleshoot:**
1. If it appears `SK Error`, is because your **Stipe SK Key** is invalid or don't accept charges yet. (*Stripe Issue*)
2. If it appears other things before the **json**, disable all **PHP** errors!

**Recomendations:**
1. For specific **Stripe Card Error Details** check: https://stripe.com/docs/error-codes
2. Set a cron for **1 hour cycle** for `stripeCleaner.php`.
3. You may use a **Stripe SK Key** that matches your desired **Currency**.
