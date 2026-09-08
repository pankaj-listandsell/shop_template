# Go-live checklist

The storefront is finished. These are the things only you can do, because they need your
own credentials. Until steps 1 and 2 are done **nobody can buy anything** — payment has no
gateway, and the download link has no way to reach the customer.

Run every command with the 8.4 binary: `D:\xampp\php84\php.exe artisan ...`

---

## 1. Payment gateway — blocking

Right now every gateway is disabled and has no credentials. Cash-on-delivery has been
turned off (it makes no sense for a download); bank transfer is left on as a manual
fallback, but it does not auto-complete an order, so the buyer waits for you.

**Admin → Settings → Payment methods**

### Stripe (recommended for card payments)

1. Sign in at <https://dashboard.stripe.com> → **Developers → API keys**
2. Copy the **Publishable key** (`pk_live_…`) and **Secret key** (`sk_live_…`)
3. Paste both into the Stripe row, toggle it **on**, save
4. Still in Stripe: **Developers → Webhooks → Add endpoint**
   - URL: `https://YOUR-DOMAIN/payment/stripe/webhook`
   - Event: `checkout.session.completed`
   - Copy the signing secret back into the Stripe settings

Test with `4242 4242 4242 4242`, any future expiry, any CVC — use your **test** keys for
that, then swap to live keys.

### PayPal

1. <https://developer.paypal.com> → **Apps & Credentials** → **Live** tab → create an app
2. Copy **Client ID** and **Secret** into the PayPal row, toggle on, save
3. Set the mode to **Live** (it defaults to sandbox)

### Others

Razorpay, Mollie, Paystack and SSLCommerz are installed and work the same way — enable
whichever suits your market and paste its keys.

**Verify:** add an item to the cart, reach checkout, and confirm your gateway appears as an
option.

---

## 2. Email — blocking

`email_driver`, `email_host`, `email_username` and `email_from_address` are all unset. For a
digital store the email *is* the delivery: order confirmation, download link and licence key
all go out that way. Nothing sends until this is configured.

**Admin → Settings → Email**

Do **not** use a personal Gmail account — Gmail throttles and marks transactional mail as
spam. Use a transactional provider; all have a free tier that covers a small store.

| Provider | Host | Port | Encryption |
|---|---|---|---|
| Brevo (ex-Sendinblue) | `smtp-relay.brevo.com` | 587 | TLS |
| Mailgun | `smtp.mailgun.org` | 587 | TLS |
| Postmark | `smtp.postmarkapp.com` | 587 | TLS |
| Amazon SES | `email-smtp.<region>.amazonaws.com` | 587 | TLS |

Fill in:

- **Driver:** SMTP
- **Host / Port / Encryption:** from the table
- **Username / Password:** the SMTP credentials your provider issues (not your login password)
- **From address:** something at your own domain, e.g. `orders@shoptemplate.com`
- **From name:** `ShopTemplate`

Then add the provider's **SPF** and **DKIM** DNS records to your domain. Skipping this is the
single most common reason order emails land in spam.

**Verify:** Admin → Settings → Email has a *Send test email* button. Then place a real test
order and confirm the download link arrives.

---

## 3. Before the site goes public

- [ ] **`APP_ENV`** — currently `local`. Set to `production` in `.env` on the live server.
      (`APP_DEBUG` is already `false`.)
- [ ] **`APP_URL`** — currently `http://127.0.0.1:8000`. Set to your real domain.
- [ ] **HTTPS** — required by Stripe and PayPal webhooks.
- [ ] **`DIGITAL_PRODUCT_ALLOWED_MIME_TYPES`** — unset in `.env`, so `.zip` uploads fall back
      to the global media allowlist. Add `zip` before uploading real products.
- [ ] **Currencies** — USD, EUR, VND and NGN are enabled. The last two are demo leftovers;
      remove them in Admin → Ecommerce → Currencies unless you sell in those markets.
- [ ] **Taxes** — three rules are configured from the demo data. Digital goods are taxed
      differently (EU VAT/OSS charges at the buyer's location). Review them.
- [ ] **Guest checkout** — off, so buyers must register. That is deliberate: the account is
      how they re-download later. Turn on in Admin → Ecommerce → Settings → Digital products
      if you would rather trade that for a shorter checkout.

---

## 4. Content still to replace

- [ ] **Products** — the 12 items are demo data (`sku` starts with `DIGI-`) with generated
      screenshots, placeholder demo URLs (`*.demo.example.com`) and sample zips. Replace with
      real products, or delete them: they are all findable by that SKU prefix.
- [ ] **Contact details** — footer still shows `1800 97 97 69` and an address in Australia.
      Admin → Appearance → Theme options → General.
- [ ] **Legal pages** — About us, Terms of Use, Terms & Conditions and Refund Policy have been
      written to match how this store actually operates, but they are **drafts, not legal
      advice**. Have a lawyer review them, and set the governing-law jurisdiction in Terms &
      Conditions before trading.
- [ ] **Reviews** — zero, so every product card shows an empty star rating. They fill in as
      customers review; there is no honest way to seed them.

---

## 5. Useful commands

```
D:\xampp\php84\php.exe artisan serve --host=127.0.0.1 --port=8000   # dev server
D:\xampp\php84\php.exe artisan cache:clear                          # after settings changes
D:\xampp\php84\php.exe artisan cms:theme:assets:publish             # after theme asset changes
```

Rebuild the marketplace stylesheet after editing `assets/sass/digital-home.scss`:

```
node node_modules/sass/sass.js ^
  platform/themes/martfury/assets/sass/digital-home.scss ^
  public/themes/martfury/css/digital-home.css --style=compressed --no-source-map
```

then copy it to `platform/themes/martfury/public/css/` so `assets:publish` keeps it.
Do **not** run `npm run prod` for this — it rebuilds every plugin and theme in the project.
