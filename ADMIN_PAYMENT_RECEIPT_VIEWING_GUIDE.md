# Admin Payment Receipt Viewing Guide

## ✅ Implementation Complete

Admin users can now **view payment receipts** uploaded by customers for GCash orders through the Order Management endpoints.

---

## 🔗 API Endpoints for Admin

### 1. Get All Orders (with receipt info)
```http
GET /api/orders
Authorization: Bearer {staff_token}
```

**Optional Query Parameters:**
- `pending_verification=true` - Filter orders with pending payment verification
- `status=pending` - Filter by order status
- `search=ORD-123` - Search by order number or customer name
- `per_page=15` - Items per page

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "order_number": "ORD-ABC123",
      "customer": {
        "id": 5,
        "name": "Juan Dela Cruz",
        "phone": "09171234567"
      },
      "total_amount": "1234.56",
      "status": "pending",
      "payment_method": "gcash",
      "payment_status": "pending",
      "payment_proof": "payment-proofs/ORD-ABC123.jpg",
      "payment_proof_url": "http://localhost:8000/storage/payment-proofs/ORD-ABC123.jpg",
      "payment_reference": null,
      "requires_payment_proof": true,
      "is_payment_verified": false,
      "verified_at": null,
      "verified_by": null,
      "created_at": "2025-11-09T06:30:00Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

---

### 2. Get Specific Order Details (with receipt)
```http
GET /api/orders/{order_id}
Authorization: Bearer {staff_token}
```

**Response:**
```json
{
  "id": 1,
  "order_number": "ORD-ABC123",
  "customer": {
    "id": 5,
    "name": "Juan Dela Cruz",
    "email": "juan@example.com",
    "phone": "09171234567"
  },
  "items": [
    {
      "product_id": 10,
      "product": {
        "id": 10,
        "name": "Hammer",
        "sku": "TOOL-001"
      },
      "quantity": 2,
      "price": "250.00",
      "subtotal": "500.00"
    }
  ],
  "total_amount": "1234.56",
  "status": "pending",
  "payment_method": "gcash",
  "payment_status": "pending",
  "payment_proof": "payment-proofs/ORD-ABC123.jpg",
  "payment_proof_url": "http://localhost:8000/storage/payment-proofs/ORD-ABC123.jpg",
  "payment_reference": null,
  "requires_payment_proof": true,
  "is_payment_verified": false,
  "verified_at": null,
  "verified_by": null,
  "delivery_method": "pickup",
  "notes": null,
  "created_at": "2025-11-09T06:30:00Z"
}
```

**Key Fields for Receipt Viewing:**
- `payment_proof` - Relative path to the receipt file
- `payment_proof_url` - Full public URL to view the receipt
- `requires_payment_proof` - Boolean indicating if payment method requires proof
- `is_payment_verified` - Boolean indicating if payment has been verified

---

### 3. Get Orders Pending Verification
```http
GET /api/orders/pending-verification
Authorization: Bearer {staff_token}
```

**Purpose:** Quick endpoint to get all orders awaiting payment verification

**Response:**
```json
{
  "count": 3,
  "orders": [
    {
      "id": 1,
      "order_number": "ORD-ABC123",
      "customer": {...},
      "total_amount": "1234.56",
      "payment_proof_url": "http://localhost:8000/storage/payment-proofs/ORD-ABC123.jpg",
      "created_at": "2025-11-09T06:30:00Z"
    },
    {...}
  ]
}
```

---

### 4. View Payment Receipt Image

Once you have the `payment_proof_url`, you can:

#### A. Direct Browser Access
```
http://localhost:8000/storage/payment-proofs/ORD-ABC123.jpg
```

#### B. Display in Frontend (HTML)
```html
<img src="http://localhost:8000/storage/payment-proofs/ORD-ABC123.jpg" 
     alt="Payment Receipt" 
     class="max-w-full h-auto" />
```

#### C. Display in Frontend (Vue.js)
```vue
<template>
  <div v-if="order.payment_proof_url">
    <h3>Payment Receipt</h3>
    <img 
      :src="order.payment_proof_url" 
      alt="Payment Receipt"
      class="max-w-full h-auto border rounded shadow"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'

const order = ref({
  payment_proof_url: 'http://localhost:8000/storage/payment-proofs/ORD-ABC123.jpg'
})
</script>
```

---

## 🔧 Setup Requirements

### 1. Create Storage Symbolic Link

The uploaded files are stored in `storage/app/public/payment-proofs/` but need to be publicly accessible. Laravel uses a symbolic link.

**Run this command once:**
```bash
php artisan storage:link
```

**What it does:**
Creates a symbolic link from `public/storage` → `storage/app/public`

**Verify:**
Check that this folder exists:
```
public/storage/payment-proofs/
```

---

### 2. Verify File Permissions (Production)

On production servers, ensure the storage directory is writable:

```bash
chmod -R 775 storage
chown -R www-data:www-data storage
```

---

## 📱 Frontend Implementation Examples

### Example 1: Order Details Page

```vue
<template>
  <div class="order-details">
    <!-- Order Info -->
    <div class="order-header">
      <h2>Order {{ order.order_number }}</h2>
      <span :class="statusClass">{{ order.status }}</span>
    </div>

    <!-- Customer Info -->
    <div class="customer-info">
      <h3>Customer</h3>
      <p>{{ order.customer.name }}</p>
      <p>{{ order.customer.phone }}</p>
    </div>

    <!-- Payment Info -->
    <div class="payment-info">
      <h3>Payment</h3>
      <p>Method: {{ order.payment_method }}</p>
      <p>Status: {{ order.payment_status }}</p>
      
      <!-- Payment Receipt -->
      <div v-if="order.payment_proof_url" class="receipt-viewer">
        <h4>Payment Receipt</h4>
        <img 
          :src="order.payment_proof_url" 
          alt="Payment Receipt"
          class="receipt-image"
          @click="openLightbox"
        />
        <p class="text-sm text-gray-500">Click to enlarge</p>
      </div>

      <!-- Verification Actions -->
      <div v-if="!order.is_payment_verified && order.payment_proof_url" 
           class="verification-actions">
        <input 
          v-model="referenceNumber" 
          placeholder="Reference Number (optional)"
          class="input"
        />
        <div class="actions">
          <button @click="rejectPayment" class="btn-danger">
            Reject Payment
          </button>
          <button @click="approvePayment" class="btn-success">
            Approve Payment
          </button>
        </div>
      </div>

      <!-- Verification Info -->
      <div v-if="order.is_payment_verified" class="verified-info">
        <p>✓ Verified by {{ order.verified_by?.name }}</p>
        <p>{{ formatDate(order.verified_at) }}</p>
        <p v-if="order.payment_reference">
          Ref: {{ order.payment_reference }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps(['orderId'])
const order = ref({})
const referenceNumber = ref('')

const loadOrder = async () => {
  const response = await axios.get(`/api/orders/${props.orderId}`)
  order.value = response.data
}

const approvePayment = async () => {
  try {
    await axios.post(`/api/orders/${order.value.id}/verify-payment`, {
      action: 'approve',
      payment_reference: referenceNumber.value
    })
    alert('Payment approved!')
    loadOrder()
  } catch (error) {
    alert('Error: ' + error.response.data.message)
  }
}

const rejectPayment = async () => {
  if (!confirm('Are you sure you want to reject this payment?')) return
  
  try {
    await axios.post(`/api/orders/${order.value.id}/verify-payment`, {
      action: 'reject'
    })
    alert('Payment rejected and order cancelled')
    loadOrder()
  } catch (error) {
    alert('Error: ' + error.response.data.message)
  }
}

const openLightbox = () => {
  // Implement image lightbox modal
}

loadOrder()
</script>

<style scoped>
.receipt-image {
  max-width: 400px;
  border: 1px solid #ddd;
  border-radius: 8px;
  cursor: pointer;
  transition: transform 0.2s;
}

.receipt-image:hover {
  transform: scale(1.02);
}
</style>
```

---

### Example 2: Pending Verification Dashboard Widget

```vue
<template>
  <div class="pending-verifications-widget">
    <div class="widget-header">
      <h3>Pending Payment Verifications</h3>
      <span class="badge">{{ pendingCount }}</span>
    </div>

    <div v-if="loading" class="loading">Loading...</div>

    <div v-else-if="orders.length === 0" class="empty">
      <p>No pending verifications</p>
    </div>

    <div v-else class="orders-list">
      <div 
        v-for="order in orders" 
        :key="order.id"
        class="order-item"
        @click="viewOrder(order.id)"
      >
        <div class="order-info">
          <strong>{{ order.order_number }}</strong>
          <span>{{ order.customer.name }}</span>
        </div>
        <div class="order-amount">
          ₱{{ order.total_amount }}
        </div>
        <div class="order-time">
          {{ timeAgo(order.created_at) }}
        </div>
      </div>
    </div>

    <router-link to="/orders?pending_verification=true" class="view-all">
      View All →
    </router-link>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()
const orders = ref([])
const pendingCount = ref(0)
const loading = ref(true)

const loadPendingVerifications = async () => {
  try {
    const response = await axios.get('/api/orders/pending-verification')
    orders.value = response.data.orders
    pendingCount.value = response.data.count
  } catch (error) {
    console.error('Failed to load pending verifications:', error)
  } finally {
    loading.value = false
  }
}

const viewOrder = (orderId) => {
  router.push(`/orders/${orderId}`)
}

const timeAgo = (date) => {
  // Implement time ago logic
  const now = new Date()
  const then = new Date(date)
  const diff = Math.floor((now - then) / 1000 / 60) // minutes
  
  if (diff < 60) return `${diff}m ago`
  if (diff < 1440) return `${Math.floor(diff / 60)}h ago`
  return `${Math.floor(diff / 1440)}d ago`
}

onMounted(() => {
  loadPendingVerifications()
  // Refresh every 30 seconds
  setInterval(loadPendingVerifications, 30000)
})
</script>
```

---

## 🔍 Testing the Feature

### Step 1: Upload Storage Link
```bash
php artisan storage:link
```

Expected output:
```
The [public/storage] link has been connected to [storage/app/public].
The links have been created.
```

---

### Step 2: Test File Access

1. Place a test image in:
   ```
   storage/app/public/payment-proofs/test.jpg
   ```

2. Access via browser:
   ```
   http://localhost:8000/storage/payment-proofs/test.jpg
   ```

3. Should display the image ✓

---

### Step 3: Test API Endpoints

**Get pending verifications:**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     http://localhost:8000/api/orders/pending-verification
```

**Get specific order:**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     http://localhost:8000/api/orders/1
```

**Verify the response includes:**
- ✓ `payment_proof_url` field
- ✓ `requires_payment_proof` field
- ✓ `is_payment_verified` field

---

## 📋 Admin Workflow

### Scenario: Customer placed GCash order

1. **Customer completes order**
   - Uploads receipt
   - Order status: `pending`
   - Payment status: `pending`

2. **Admin receives notification**
   - Dashboard shows: "1 Pending Verification"
   - Widget displays order in list

3. **Admin opens order details**
   ```
   GET /api/orders/{id}
   ```
   - Views customer info
   - Views order items
   - **Views payment receipt image** via `payment_proof_url`

4. **Admin verifies receipt**
   - Checks if amount matches: ₱1,234.56
   - Checks GCash number is correct
   - Checks transaction is valid

5. **Admin takes action**

   **Option A: Approve**
   ```
   POST /api/orders/{id}/verify-payment
   {
     "action": "approve",
     "payment_reference": "GC1234567890"
   }
   ```
   - ✓ Payment status → `paid`
   - ✓ Order status → `confirmed`
   - ✓ SMS sent to customer

   **Option B: Reject**
   ```
   POST /api/orders/{id}/verify-payment
   {
     "action": "reject",
     "notes": "Invalid receipt"
   }
   ```
   - ✓ Order cancelled
   - ✓ Inventory restored
   - ✓ SMS sent to customer

---

## 🔐 Security Notes

1. **File Access:**
   - Only authenticated staff/admin can verify payments
   - Receipt URLs are public but hard to guess (unique order numbers)
   - Consider adding signed URLs for production

2. **Data Privacy:**
   - Receipts may contain sensitive information
   - Consider auto-deletion after 90 days (GDPR)
   - Log all access to payment receipts

3. **Fraud Prevention:**
   - Log who verified each payment
   - Track verification timestamps
   - Maintain audit trail

---

## 🎯 Summary

✅ **Admin CAN view payment receipts**
- Via `payment_proof_url` field in order responses
- Direct image URL accessible via browser
- Images stored in `storage/app/public/payment-proofs/`
- Publicly accessible via `/storage/payment-proofs/`

✅ **Complete API Support**
- Get all orders with receipt info
- Get specific order with receipt
- Get pending verifications list
- Verify payment (approve/reject)

✅ **Ready for Frontend**
- All backend endpoints working
- Receipt URLs properly generated
- Storage link configuration documented

---

**Last Updated:** November 9, 2025  
**Status:** ✅ Fully Implemented and Tested
