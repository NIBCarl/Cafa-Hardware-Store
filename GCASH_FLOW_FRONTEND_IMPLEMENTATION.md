# GCash Payment Flow - Frontend Implementation Guide

## ✅ Implementation Complete

The GCash payment flow has been implemented with a modal-based approach for better UX.

---

## 🎯 Flow Overview

### Current (Fixed) Flow:

```
1. Customer fills cart and goes to checkout
   ↓
2. Selects "GCash" as payment method
   ↓
3. Clicks "Place Order" button
   ↓
4. **GCash Payment Modal Opens** ← NEW!
   ├─ Shows store's GCash number
   ├─ Shows GCash account name
   ├─ Shows total amount to pay
   ├─ Provides upload field for receipt
   └─ Has submit button
   ↓
5. Customer:
   ├─ Opens GCash app
   ├─ Sends payment to store's number
   ├─ Takes screenshot of receipt
   └─ Uploads screenshot in modal
   ↓
6. Customer clicks "Submit Order" in modal
   ↓
7. Order created with payment proof
   ├─ Status: Pending
   ├─ Payment Status: Pending
   └─ Receipt stored on server
   ↓
8. Customer redirected to "My Orders" page
   └─ Shows: "Payment verification pending"
   ↓
9. Admin verifies payment
   └─ Customer receives SMS notification
```

---

## 📁 Files Created/Modified

### 1. New Component Created ✅
**File:** `resources/js/components/customer/GCashPaymentModal.vue`

**Features:**
- Fetches GCash payment info from API
- Displays store's GCash number and name
- Shows total amount to pay
- File upload with preview
- Validation (file type, size)
- Submit handler
- Error display

**Props:**
- `modelValue` - Modal open/close state
- `totalAmount` - Order total

**Events:**
- `@submit` - Emits selected file when user clicks submit
- `@close` - Emits when modal is closed

---

### 2. Modified Component ✅
**File:** `resources/js/views/customer/Cart.vue`

**Changes:**

#### A. Imports
```javascript
import GCashPaymentModal from '@/components/customer/GCashPaymentModal.vue';
```

#### B. New State
```javascript
const showGCashModal = ref(false);
const gcashModalRef = ref(null);
```

#### C. Updated handleCheckout Function
```javascript
const handleCheckout = async () => {
  // ... validation ...

  // NEW: Check if payment method requires upload
  if (checkoutForm.value.payment_method === 'gcash' || 
      checkoutForm.value.payment_method === 'digital_wallet') {
    showGCashModal.value = true;  // Show modal instead of submitting
    return;
  }

  // For other payment methods, proceed normally
  await submitOrder();
};
```

#### D. New handleGCashPayment Function
```javascript
const handleGCashPayment = async (paymentProofFile) => {
  gcashModalRef.value.setSubmitting(true);

  try {
    await submitOrder(paymentProofFile);
    showGCashModal.value = false;  // Close modal on success
  } catch (error) {
    gcashModalRef.value.setError(error.message);
  } finally {
    gcashModalRef.value.setSubmitting(false);
  }
};
```

#### E. Updated submitOrder Function
```javascript
const submitOrder = async (paymentProofFile = null) => {
  // Use FormData for file upload
  const formData = new FormData();
  
  // Add cart items
  cartStore.items.forEach((item, index) => {
    formData.append(`items[${index}][product_id]`, item.product.id);
    formData.append(`items[${index}][quantity]`, item.quantity);
  });

  // Add order details
  formData.append('payment_method', checkoutForm.value.payment_method);
  formData.append('delivery_method', checkoutForm.value.delivery_method);
  
  if (checkoutForm.value.delivery_address) {
    formData.append('delivery_address', checkoutForm.value.delivery_address);
  }
  
  if (checkoutForm.value.notes) {
    formData.append('notes', checkoutForm.value.notes);
  }

  // Add payment proof file if provided
  if (paymentProofFile) {
    formData.append('payment_proof', paymentProofFile);
  }

  const response = await customerOrdersApi.create(formData);
  
  // Success handling
  toastStore.success('Order placed! Payment verification pending.');
  cartStore.clearCart();
  router.push('/customer/orders');
};
```

#### F. Template Addition
```vue
<!-- GCash Payment Modal -->
<GCashPaymentModal
  v-model="showGCashModal"
  :total-amount="cartStore.total"
  @submit="handleGCashPayment"
  @close="showGCashModal = false"
  ref="gcashModalRef"
/>
```

---

## 🎨 Modal UI Features

### Payment Information Display
```
┌─────────────────────────────────────────┐
│   GCash Payment                    [X]  │
├─────────────────────────────────────────┤
│                                         │
│  ℹ️ Payment Instructions                │
│  1. Send exact amount via GCash         │
│  2. Take screenshot of receipt          │
│  3. Upload screenshot below             │
│  4. Click "Submit Order"                │
│                                         │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━    │
│                                         │
│  Send Payment To:                 ₱     │
│                                         │
│  GCash Number                           │
│  ┌───────────────────────────────────┐ │
│  │  09171234567              [Copy]  │ │
│  └───────────────────────────────────┘ │
│                                         │
│  Account Name                           │
│  CAFA Hardware Store                    │
│                                         │
│  Amount to Pay                          │
│  ₱1,234.56                              │
│                                         │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━    │
│                                         │
│  Payment Receipt *                      │
│  ┌───────────────────────────────────┐ │
│  │  [Click to upload receipt]        │ │
│  │  PNG, JPG up to 5MB               │ │
│  └───────────────────────────────────┘ │
│                                         │
│                        [ Cancel ]       │
│                        [ Submit Order ] │
└─────────────────────────────────────────┘
```

### After Upload
```
│  Payment Receipt *                      │
│  ┌───────────────────────────────────┐ │
│  │  [Receipt Image Preview]          │ │
│  │                                   │ │
│  │  ✓ receipt.png (245 KB)   [X]     │ │
│  └───────────────────────────────────┘ │
```

---

## 🔧 API Integration

### Endpoint Used
```
GET /api/payment-info  (public, no auth required)
```

**Response:**
```json
{
  "gcash": {
    "enabled": true,
    "number": "09171234567",
    "name": "CAFA Hardware Store"
  }
}
```

### Order Submission
```
POST /api/customer/orders
Content-Type: multipart/form-data
Authorization: Bearer {token}
```

**Request (FormData):**
```
items[0][product_id]: 1
items[0][quantity]: 2
payment_method: gcash
delivery_method: pickup
payment_proof: <file>
notes: "Please handle with care"
```

---

## ✨ Features Implemented

### 1. File Validation
- **Allowed types:** JPG, JPEG, PNG
- **Max size:** 5MB
- **Real-time validation**
- **User-friendly error messages**

### 2. File Preview
- Shows thumbnail of uploaded image
- Displays file name and size
- Option to remove and re-upload

### 3. Copy to Clipboard
- Click to copy GCash number
- Helpful for users to paste in GCash app

### 4. Loading States
- Payment info loading spinner
- Submit button disabled during processing
- Visual feedback

### 5. Error Handling
- File validation errors
- API errors displayed in modal
- Graceful error recovery

---

## 🧪 Testing Checklist

### Test Case 1: Cash Payment (No Modal)
- [ ] Select "Cash on Delivery/Pickup"
- [ ] Click "Place Order"
- [ ] Should submit immediately (no modal)
- [ ] Order created successfully
- [ ] Redirected to orders page

### Test Case 2: GCash Payment (With Modal)
- [ ] Select "GCash"
- [ ] Click "Place Order"
- [ ] GCash modal opens
- [ ] Shows GCash number and store name
- [ ] Shows correct total amount
- [ ] File upload field visible

### Test Case 3: File Upload Validation
- [ ] Try uploading PDF → Error message
- [ ] Try uploading file > 5MB → Error message
- [ ] Upload valid JPG → Shows preview
- [ ] Upload valid PNG → Shows preview

### Test Case 4: Submit with Receipt
- [ ] Upload valid receipt
- [ ] Click "Submit Order"
- [ ] Loading state shows
- [ ] Order created successfully
- [ ] Modal closes
- [ ] Redirected to orders page
- [ ] Success message shows

### Test Case 5: GCash Info Not Configured
- [ ] Admin hasn't set GCash details
- [ ] Modal shows error message
- [ ] Submit button disabled

### Test Case 6: Cancel Flow
- [ ] Open modal
- [ ] Upload file
- [ ] Click "Cancel"
- [ ] Modal closes
- [ ] File cleared
- [ ] Can reopen and start fresh

---

## 🎯 User Experience Flow

### Step-by-Step Customer Journey

**1. Cart Page**
```
Customer has items in cart
↓
Selects "GCash" from payment dropdown
↓
Clicks "Place Order"
```

**2. Modal Opens**
```
Modal displays:
- "Send payment to: 09171234567"
- "Account: CAFA Hardware Store"
- "Amount: ₱1,234.56"
- Upload field
```

**3. Customer Actions**
```
Opens GCash app on phone
↓
Sends ₱1,234.56 to 09171234567
↓
GCash shows "Transaction Successful"
↓
Takes screenshot
↓
Returns to browser
↓
Uploads screenshot in modal
```

**4. Submit**
```
Clicks "Submit Order"
↓
Loading spinner shows
↓
"Order placed! Payment verification pending"
↓
Redirected to "My Orders"
```

**5. Orders Page**
```
Shows order with:
- Status: Pending
- Payment: Pending Verification
- Message: "Your payment is being verified"
```

**6. After Admin Verification**
```
Receives SMS:
"CAFA Hardware: Payment verified! 
Your order #ORD-ABC123 is confirmed."
↓
Order status updates to "Confirmed"
```

---

## 🔐 Security Features

1. **File Type Validation**
   - Only images allowed
   - Client-side and server-side validation

2. **File Size Limit**
   - 5MB maximum
   - Prevents server overload

3. **Authentication Required**
   - Must be logged in to place order
   - Token-based authentication

4. **CSRF Protection**
   - Laravel's built-in CSRF protection
   - Automatic with Sanctum

---

## 📱 Responsive Design

The modal is fully responsive:
- **Mobile:** Full screen modal
- **Tablet:** Centered modal, 90% width
- **Desktop:** Centered modal, fixed max-width

---

## 🚀 Next Steps

### For Admin Panel

Create payment verification interface:
1. Dashboard widget showing pending verifications
2. Order detail page with receipt viewer
3. Approve/Reject buttons
4. Verification history

See: `ADMIN_PAYMENT_RECEIPT_VIEWING_GUIDE.md`

---

## 💡 Tips for Users

### Admin Setup
1. Go to Settings → Payment Settings
2. Enable GCash
3. Enter GCash number (e.g., 09171234567)
4. Enter account name
5. Save settings

### Customer Instructions
1. Select GCash at checkout
2. Click Place Order
3. Follow modal instructions
4. Send payment via GCash app
5. Upload receipt screenshot
6. Wait for verification (usually 1-2 hours)

---

## 🐛 Troubleshooting

### Issue: Modal doesn't open
**Solution:** Check browser console for errors, ensure component is imported

### Issue: File upload fails
**Solution:** 
- Check file size < 5MB
- Check file type is JPG or PNG
- Check network connection

### Issue: "Payment info not configured" error
**Solution:** Admin needs to configure GCash settings

### Issue: Order submission fails
**Solution:**
- Ensure receipt is uploaded
- Check internet connection
- Verify authentication token is valid

---

## ✅ Summary

**What Changed:**
1. ✅ Created GCashPaymentModal component
2. ✅ Updated Cart.vue to show modal for GCash
3. ✅ Changed submitOrder to use FormData
4. ✅ Added file upload handling
5. ✅ Integrated with backend payment-proof endpoint

**User Flow:**
1. Select GCash → Modal opens
2. Upload receipt → Submit
3. Order created with pending verification
4. Redirected to orders page

**Ready to Test!** 🎉

---

**Implementation Date:** November 9, 2025  
**Status:** ✅ Complete - Ready for Testing  
**Backend:** Already implemented (previous work)  
**Frontend:** Newly implemented (this session)
