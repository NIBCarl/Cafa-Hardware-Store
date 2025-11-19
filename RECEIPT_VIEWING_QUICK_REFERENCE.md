# Quick Reference: Admin View Payment Receipts

## ✅ YES, Admin Can View Receipts!

---

## How It Works

### 1. Customer uploads receipt when placing GCash order
```
Customer → Places order → Uploads receipt.jpg → Order created
```

### 2. Receipt is stored on server
```
Location: storage/app/public/payment-proofs/ORD-ABC123.jpg
Public URL: http://localhost:8000/storage/payment-proofs/ORD-ABC123.jpg
```

### 3. Admin views order details
```
GET /api/orders/{id}
```

**Response includes:**
```json
{
  "order_number": "ORD-ABC123",
  "payment_method": "gcash",
  "payment_status": "pending",
  "payment_proof": "payment-proofs/ORD-ABC123.jpg",
  "payment_proof_url": "http://localhost:8000/storage/payment-proofs/ORD-ABC123.jpg",
  "customer": {
    "name": "Juan Dela Cruz",
    "phone": "09171234567"
  }
}
```

### 4. Admin displays receipt in frontend
```html
<img :src="order.payment_proof_url" alt="Payment Receipt" />
```

---

## API Endpoints

### Get Order with Receipt
```http
GET /api/orders/{order_id}
Authorization: Bearer {staff_token}
```

**Returns:**
- Order details
- Customer info
- `payment_proof_url` - Direct link to receipt image

---

### Get All Orders Pending Verification
```http
GET /api/orders/pending-verification
Authorization: Bearer {staff_token}
```

**Returns:**
```json
{
  "count": 3,
  "orders": [
    {
      "order_number": "ORD-ABC123",
      "payment_proof_url": "http://localhost:8000/storage/payment-proofs/ORD-ABC123.jpg"
    }
  ]
}
```

---

### Filter Orders by Pending Verification
```http
GET /api/orders?pending_verification=true
Authorization: Bearer {staff_token}
```

---

### Verify Payment (Approve/Reject)
```http
POST /api/orders/{id}/verify-payment
Authorization: Bearer {staff_token}

{
  "action": "approve",  // or "reject"
  "payment_reference": "GC1234567890"  // optional
}
```

---

## Frontend Example

### Display Receipt in Modal

```vue
<template>
  <div v-if="order.payment_proof_url" class="receipt-modal">
    <h3>Payment Receipt for {{ order.order_number }}</h3>
    
    <!-- Receipt Image -->
    <img 
      :src="order.payment_proof_url" 
      alt="Payment Receipt"
      class="w-full max-w-lg border rounded shadow-lg"
    />
    
    <!-- Order Info -->
    <div class="order-info mt-4">
      <p><strong>Customer:</strong> {{ order.customer.name }}</p>
      <p><strong>Amount:</strong> ₱{{ order.total_amount }}</p>
      <p><strong>Method:</strong> {{ order.payment_method }}</p>
    </div>
    
    <!-- Verification Actions -->
    <div v-if="!order.is_payment_verified" class="actions mt-4">
      <input 
        v-model="referenceNumber" 
        placeholder="GCash Reference Number (optional)"
        class="input mb-2"
      />
      <button @click="approve" class="btn-success mr-2">
        Approve Payment
      </button>
      <button @click="reject" class="btn-danger">
        Reject Payment
      </button>
    </div>
    
    <!-- Already Verified -->
    <div v-else class="verified-badge">
      ✓ Verified by {{ order.verified_by?.name }} 
      on {{ formatDate(order.verified_at) }}
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps(['order'])
const referenceNumber = ref('')

const approve = async () => {
  await axios.post(`/api/orders/${props.order.id}/verify-payment`, {
    action: 'approve',
    payment_reference: referenceNumber.value
  })
  alert('Payment approved!')
}

const reject = async () => {
  if (!confirm('Reject this payment? Order will be cancelled.')) return
  
  await axios.post(`/api/orders/${props.order.id}/verify-payment`, {
    action: 'reject'
  })
  alert('Payment rejected')
}
</script>
```

---

## Setup Checklist

- [x] Database migration applied
- [x] Storage link created (`php artisan storage:link`)
- [x] API endpoints working
- [x] Receipt URLs generated
- [ ] Frontend receipt viewer built
- [ ] Admin verification UI built

---

## File Storage

**Upload Location:**
```
storage/app/public/payment-proofs/
```

**Public Access:**
```
public/storage/payment-proofs/  (symlink)
```

**URL Format:**
```
http://localhost:8000/storage/payment-proofs/{order_number}.jpg
```

---

## Quick Test

1. **Check storage link exists:**
   ```bash
   ls -la public/storage
   ```
   Should show symlink to `../../storage/app/public`

2. **Test API:**
   ```bash
   curl -H "Authorization: Bearer TOKEN" \
        http://localhost:8000/api/orders/1
   ```
   
3. **Check response has:**
   - `payment_proof_url` field
   - `requires_payment_proof` field
   - `is_payment_verified` field

---

## Summary

✅ **Admin can view receipts via:**
- Order details endpoint (`GET /api/orders/{id}`)
- Pending verifications endpoint (`GET /api/orders/pending-verification`)
- Direct image URL from `payment_proof_url` field

✅ **Receipt image is accessible via:**
- Frontend: `<img :src="order.payment_proof_url" />`
- Browser: Direct URL navigation
- API: Field included in all order responses

✅ **Admin can verify payments:**
- Approve → Order confirmed, SMS sent
- Reject → Order cancelled, inventory restored, SMS sent

---

**Ready to use!** 🎉
