# Payment Receipt Image Not Showing - Troubleshooting Guide

## 🐛 Issue
Payment receipt images uploaded by customers are not displaying in the admin order details modal.

---

## 🔍 Diagnosis Steps

### Step 1: Check Console for Debug Info

1. Open the order details modal
2. Open browser console (F12 → Console tab)
3. Look for these logs:
   ```
   Payment proof URL: http://localhost:8000/storage/payment-proofs/ORD-XXX.jpg
   Payment proof path: payment-proofs/ORD-XXX.jpg
   ```

4. If you see "Failed to load image:", note the URL

---

### Step 2: Verify Storage Link Exists

**Run this command:**
```bash
cd c:\Users\Engr. John Rome\Desktop\capstone\SNSU CAPSTONE\cafa-pos
php artisan storage:link
```

**Expected output:**
```
The links have been created.
```

**Or if link already exists:**
```
The [public\storage] link already exists.
```

---

### Step 3: Check Physical File Location

**Navigate to:**
```
c:\Users\Engr. John Rome\Desktop\capstone\SNSU CAPSTONE\cafa-pos\storage\app\public\payment-proofs\
```

**Check:**
- Does the `payment-proofs` folder exist?
- Are there `.jpg` or `.png` files inside?
- Note one of the filenames (e.g., `ORD-6736C747A473.jpg`)

---

### Step 4: Verify Symlink

**Check if this folder exists:**
```
c:\Users\Engr. John Rome\Desktop\capstone\SNSU CAPSTONE\cafa-pos\public\storage
```

**It should be a symlink/shortcut pointing to:**
```
c:\Users\Engr. John Rome\Desktop\capstone\SNSU CAPSTONE\cafa-pos\storage\app\public
```

---

### Step 5: Manual URL Test

**If file exists at:**
```
storage\app\public\payment-proofs\ORD-6736C747A473.jpg
```

**Try accessing in browser:**
```
http://localhost:8000/storage/payment-proofs/ORD-6736C747A473.jpg
```

**Should display the image** ✓

---

## 🔧 Solutions

### Solution 1: Create Storage Link (Most Common)

**Windows (Run as Administrator):**
```bash
cd c:\Users\Engr. John Rome\Desktop\capstone\SNSU CAPSTONE\cafa-pos
php artisan storage:link
```

**If error "link already exists":**
```bash
# Delete existing link
rmdir public\storage

# Recreate
php artisan storage:link
```

---

### Solution 2: Create payment-proofs Folder

**If folder doesn't exist:**
```bash
cd c:\Users\Engr. John Rome\Desktop\capstone\SNSU CAPSTONE\cafa-pos
mkdir storage\app\public\payment-proofs
```

---

### Solution 3: Fix File Permissions (if on Linux/Mac)

```bash
chmod -R 775 storage
chmod -R 775 public/storage
```

---

### Solution 4: Check .env APP_URL

**Open `.env` file:**
```env
APP_URL=http://localhost:8000
```

**Make sure it matches your actual URL!**

If you're using `http://127.0.0.1:8000`, update to that.

---

### Solution 5: Clear Laravel Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## 🧪 Test After Fixing

### Test 1: Place New Order
1. As customer, place new GCash order
2. Upload receipt
3. Submit order
4. Check console logs for the FormData

### Test 2: View in Admin
1. Open admin Orders page
2. Click "View" on the GCash order
3. **Check browser console** for:
   ```
   Payment proof URL: http://localhost:8000/storage/payment-proofs/ORD-XXX.jpg
   ```
4. Image should display ✓

### Test 3: Direct URL Access
Copy the URL from console and paste in new browser tab
- Should show the receipt image ✓

---

## 🔍 Advanced Debugging

### Check Database Value

**Run in database:**
```sql
SELECT id, order_number, payment_method, payment_proof, payment_proof_url 
FROM orders 
WHERE payment_method = 'gcash' 
ORDER BY id DESC 
LIMIT 5;
```

**Expected:**
```
payment_proof: payment-proofs/ORD-XXX.jpg
payment_proof_url: NULL (this is computed dynamically)
```

---

### Check File Upload in Controller

**Add temporary debug in OrderController.php:**

```php
// After line 92
Log::info('Payment proof stored', [
    'path' => $paymentProofPath,
    'full_path' => storage_path('app/public/' . str_replace('public/', '', $paymentProofPath)),
    'exists' => file_exists(storage_path('app/public/' . str_replace('public/', '', $paymentProofPath)))
]);
```

Check `storage/logs/laravel.log` for the output.

---

## ✅ Quick Fix Checklist

- [ ] Run `php artisan storage:link`
- [ ] Verify `public\storage` folder exists
- [ ] Check file exists in `storage\app\public\payment-proofs\`
- [ ] Try direct URL in browser
- [ ] Check browser console for errors
- [ ] Verify APP_URL in .env
- [ ] Clear Laravel cache
- [ ] Hard refresh browser (Ctrl+Shift+R)

---

## 📱 Expected Result

**After fixing, you should see:**

```
┌─────────────────────────────────────┐
│ Payment Receipt                     │
├─────────────────────────────────────┤
│  [Actual Receipt Image Displayed]  │
│  Click image to enlarge             │
└─────────────────────────────────────┘
```

---

## 🆘 Still Not Working?

### Check These:

1. **Web server running?**
   ```bash
   php artisan serve
   ```

2. **Correct port?**
   - Using 8000 but accessing 8080?
   - Check APP_URL matches

3. **Windows-specific symlink issue?**
   - May need administrator privileges
   - Run command prompt as Administrator

4. **Antivirus blocking?**
   - Temporarily disable and retry

5. **File actually uploaded?**
   - Check FormData in Network tab
   - Should see `payment_proof: (binary)`

---

## 💡 Alternative: Use Full Path in Development

**Temporary workaround (Order.php):**

```php
public function getPaymentProofUrlAttribute()
{
    if (!$this->payment_proof) {
        return null;
    }

    $path = str_replace('public/', '', $this->payment_proof);
    
    // For local development, use full path
    if (app()->environment('local')) {
        return url('storage/' . $path);
    }
    
    return asset('storage/' . $path);
}
```

---

## 📸 Debugging Screenshot

When you open the modal and see the broken image:
1. Right-click the broken image
2. Select "Inspect Element"
3. Look at the `src` attribute
4. Copy the full URL
5. Try accessing it directly in browser
6. Check browser's Network tab for the response

**What you're looking for:**
- 404 = File not found (symlink issue)
- 403 = Permission denied
- 200 = File exists but not rendering (file corruption)

---

**Last Updated:** November 9, 2025  
**Status:** Troubleshooting Guide
