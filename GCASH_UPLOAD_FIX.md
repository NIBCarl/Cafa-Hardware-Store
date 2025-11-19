# GCash Payment Upload Fix

## 🐛 Issue Fixed

**Problem:** Payment proof file was not being sent properly to backend, causing validation error: "The payment proof field is required when payment method is gcash."

**Root Cause:** The axios instance was forcing `Content-Type: application/json` which interfered with FormData uploads.

---

## ✅ Fix Applied

### 1. Updated `customerApi.js` - Axios Interceptor
**File:** `resources/js/services/customerApi.js`

**Changes:**
- Removed default `Content-Type: application/json` from axios instance
- Added conditional logic in interceptor to detect FormData
- Allows axios to automatically set correct Content-Type with boundary for file uploads

**Before:**
```javascript
const customerApiClient = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',  // ❌ This was breaking FormData
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
});
```

**After:**
```javascript
const customerApiClient = axios.create({
  baseURL: '/api',
  headers: {
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
});

// Interceptor sets Content-Type conditionally
customerApiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('customer_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    
    // Only set Content-Type to JSON if it's not FormData
    if (!(config.data instanceof FormData)) {
      config.headers['Content-Type'] = 'application/json';
    }
    // For FormData, axios sets multipart/form-data automatically
    
    return config;
  }
);
```

---

### 2. Enhanced `Cart.vue` - Debugging
**File:** `resources/js/views/customer/Cart.vue`

**Changes:**
- Added console logging for debugging
- Explicitly pass filename to FormData.append()

```javascript
// Add payment proof if provided (for GCash)
if (paymentProofFile) {
  console.log('Adding payment proof file:', paymentProofFile);
  formData.append('payment_proof', paymentProofFile, paymentProofFile.name);
}

// Debug: Log FormData contents
console.log('FormData contents:');
for (let pair of formData.entries()) {
  console.log(pair[0], pair[1]);
}
```

---

## 🧪 How to Test

### Step 1: Clear Browser Cache
```
Press Ctrl+Shift+R (hard refresh)
or
Clear browser cache and reload
```

### Step 2: Test the Flow
1. Go to shopping cart
2. Add items to cart
3. Select "GCash" as payment method
4. Click "Place Order"
5. GCash modal should open ✓

### Step 3: Upload Receipt
1. Click "Click to upload receipt"
2. Select a JPG or PNG image (under 5MB)
3. Image preview should appear ✓
4. File info shows at bottom ✓

### Step 4: Submit Order
1. Click "Submit Order" button
2. **Check browser console** (F12 → Console tab)
3. Should see logs:
   ```
   Adding payment proof file: File {...}
   FormData contents:
   items[0][product_id] 1
   items[0][quantity] 2
   payment_method gcash
   delivery_method pickup
   payment_proof [object File]
   ```

### Step 5: Verify Success
1. No validation error should appear ✓
2. Success message: "Order placed! Payment verification pending" ✓
3. Redirected to "/customer/orders" ✓
4. Order appears with "Pending Verification" status ✓

---

## 🔍 Browser Console Checks

Open browser console (F12) and look for:

### ✅ Good Signs:
```
Adding payment proof file: File {name: "receipt.jpg", ...}
FormData contents:
payment_proof File {...}
```

### ❌ Bad Signs:
```
Error: The payment proof field is required when payment method is gcash
```

### Network Tab Check:
1. Open F12 → Network tab
2. Submit order
3. Find POST request to `/api/customer/orders`
4. Click on it → Headers tab
5. **Request Headers should show:**
   ```
   Content-Type: multipart/form-data; boundary=----WebKitFormBoundary...
   ```
6. **Form Data tab should show:**
   ```
   payment_proof: (binary)
   ```

---

## ⚠️ Important Note

**Admin Must Configure GCash Settings First!**

Before testing, ensure admin has set up GCash payment info:

1. Login as admin
2. Go to Settings → Payment Settings
3. Enable GCash
4. Enter GCash number (e.g., 09171234567)
5. Enter account name
6. Save

**If not configured:**
- Modal will show "Not configured" for GCash number
- Payment can still be submitted
- But it's recommended to configure it first

---

## 🛠️ If Still Not Working

### Check 1: File Size
- Max 5MB
- Try with smaller image

### Check 2: File Type
- Only JPG, JPEG, PNG allowed
- Check file extension

### Check 3: Browser Console
- Look for JavaScript errors
- Check network request payload

### Check 4: Server Logs
```bash
tail -f storage/logs/laravel.log
```

Look for validation errors or file upload errors.

---

## 📝 What Was Wrong?

The issue was in the axios configuration chain:

1. **Axios instance** had hardcoded `Content-Type: application/json`
2. **FormData uploads** require `Content-Type: multipart/form-data; boundary=...`
3. **Setting Content-Type manually** breaks the boundary parameter
4. **Solution:** Let axios auto-detect FormData and set headers automatically

---

## ✅ Summary

| Aspect | Status |
|--------|--------|
| Axios configuration | ✅ Fixed |
| FormData detection | ✅ Working |
| File upload | ✅ Enabled |
| Content-Type header | ✅ Automatic |
| Backend validation | ✅ Should pass |

---

**Next Test:** Try placing an order with GCash payment and receipt upload!

---

**Fixed:** November 9, 2025  
**Files Modified:** 
- `resources/js/services/customerApi.js`
- `resources/js/views/customer/Cart.vue`
