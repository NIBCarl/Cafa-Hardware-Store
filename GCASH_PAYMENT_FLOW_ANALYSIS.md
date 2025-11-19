# GCash Payment Receipt Flow Analysis & Implementation

## Current Flow Issues ❌

### Problem 1: No Payment Proof Field
**Current State:**
- Orders table has `payment_method` field including 'gcash'
- No `payment_proof` field to store receipt/screenshot
- Customer can select GCash but cannot upload proof of payment

### Problem 2: No GCash Number Storage
**Current State:**
- No place to store store owner's GCash number
- Customer doesn't know where to send payment
- No way to display payment instructions

### Problem 3: Incorrect Order Flow
**Current Issue:**
```php
// OrderController.php line 88
'payment_status' => 'pending',  // Always pending, even for GCash
```
**Problem:** Inventory is reduced immediately when order is placed, but GCash payment requires verification first.

**Risk:** Customer places order → stock reduced → never pays → stock locked

### Problem 4: No Validation for GCash Receipts
**Current State:**
- If payment_method is 'gcash', no requirement to upload receipt
- Order is accepted without proof of payment

---

## Correct Flow Design ✅

### Flow 1: Cash/Card (Pickup) - Existing, No Changes Needed
```
Customer selects Cash/Card 
→ Places order 
→ Stock reduced immediately 
→ Status: Pending 
→ Customer picks up and pays on-site
→ Staff confirms → Status: Completed
```

### Flow 2: GCash Payment - NEW IMPLEMENTATION
```
Step 1: Customer selects GCash payment method
↓
Step 2: System displays modal with:
  - Store's GCash Number (from settings)
  - Store's GCash Name
  - Total amount to pay
  - Instructions
↓
Step 3: Customer sends payment via GCash app
↓
Step 4: Customer uploads payment receipt/screenshot
  - Required field
  - Image validation (jpg, png, max 5MB)
  - Stored securely
↓
Step 5: Order submitted with receipt
  - Status: Pending
  - Payment Status: Pending (awaiting verification)
  - Stock: NOT reduced yet (reserved instead)
↓
Step 6: Admin/Staff verifies payment receipt
  - Views uploaded receipt
  - Verifies amount and reference number
  - Options:
    → Approve: Payment Status = Paid, reduce stock
    → Reject: Cancel order, release reservation
↓
Step 7a: If Approved
  - Stock reduced
  - Status: Confirmed
  - SMS sent: "Payment verified, order confirmed"
↓
Step 7b: If Rejected
  - Order cancelled
  - SMS sent: "Payment verification failed, order cancelled"
```

---

## Implementation Plan

### Phase 1: Database Schema
1. Add migration for payment_proof field
2. Update Order model

### Phase 2: Settings for GCash
1. Add GCash settings (number, name)
2. Create endpoint to get payment info

### Phase 3: Backend Logic
1. Update validation rules
2. Handle file upload
3. Add payment verification endpoint
4. Update inventory logic (don't reduce until verified)

### Phase 4: API Endpoints
1. `GET /api/payment-info` - Get GCash details
2. `POST /api/customer/orders` - Accept file upload
3. `POST /api/orders/{id}/verify-payment` - Admin verifies (approve/reject)

---

## Database Changes Required

### Migration: Add payment_proof to orders table
```php
Schema::table('orders', function (Blueprint $table) {
    $table->string('payment_proof')->nullable()->after('payment_status');
});
```

### Settings: GCash Information
```php
Setting::set('payment', [
    'gcash_number' => '09171234567',
    'gcash_name' => 'CAFA Hardware Store',
    'gcash_enabled' => true,
]);
```

---

## Validation Rules

### For GCash Orders
```php
'payment_proof' => 'required_if:payment_method,gcash|file|mimes:jpg,jpeg,png|max:5120',
```

### File Upload Rules
- **Allowed formats:** jpg, jpeg, png
- **Max size:** 5MB (5120 KB)
- **Storage:** `storage/app/public/payment-proofs/{order_number}.jpg`
- **Accessible via:** `/storage/payment-proofs/{order_number}.jpg`

---

## Security Considerations

### 1. File Upload Security
- Validate file type and size
- Sanitize filename
- Store outside public root initially
- Only admins can access uploaded proofs

### 2. Privacy
- Only customer who created order can view their receipt
- Only admin/staff can verify receipts
- Receipts should be deleted after 90 days (GDPR compliance)

### 3. Fraud Prevention
- Log all payment verification actions
- Track who verified each payment
- Store verification timestamp

---

## UI/UX Flow

### Customer Portal: Place Order

**Step 1: Select Payment Method**
```
[ ] Cash (Pay on pickup/delivery)
[ ] Card (Pay on pickup/delivery)
[x] GCash (Pay now via GCash)
```

**Step 2: GCash Payment Modal (shows if GCash selected)**
```
┌─────────────────────────────────────────┐
│   GCash Payment Instructions            │
├─────────────────────────────────────────┤
│                                         │
│  Send payment to:                       │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━     │
│  GCash Number: 09171234567              │
│  Account Name: CAFA Hardware Store      │
│                                         │
│  Amount to Pay: ₱1,234.56               │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━     │
│                                         │
│  After sending payment:                 │
│  1. Take a screenshot of receipt        │
│  2. Upload it below                     │
│                                         │
│  Upload Payment Receipt: *              │
│  [Choose File] receipt.png              │
│  Max size: 5MB (jpg, png only)          │
│                                         │
│  [ Cancel ]  [ Submit Order ]           │
└─────────────────────────────────────────┘
```

**Step 3: Order Confirmation**
```
✓ Order Placed Successfully!

Order Number: ORD-ABC123
Status: Pending Payment Verification

Your payment receipt is being verified by our staff.
You will receive an SMS once verified.

Expected verification time: Within 1-2 hours
```

### Admin Portal: Verify Payment

**Payment Verification Dashboard**
```
┌────────────────────────────────────────────────┐
│  Pending Payment Verifications (3)             │
├────────────────────────────────────────────────┤
│                                                │
│  ORD-ABC123  |  Juan Dela Cruz  |  ₱1,234.56  │
│  [View Receipt] [Approve] [Reject]             │
│                                                │
│  ORD-DEF456  |  Maria Santos    |  ₱2,500.00  │
│  [View Receipt] [Approve] [Approve]            │
│                                                │
└────────────────────────────────────────────────┘
```

**Receipt Verification Modal**
```
┌─────────────────────────────────────────┐
│   Verify Payment Receipt                │
├─────────────────────────────────────────┤
│                                         │
│  Order: ORD-ABC123                      │
│  Customer: Juan Dela Cruz               │
│  Amount: ₱1,234.56                      │
│                                         │
│  Uploaded Receipt:                      │
│  ┌──────────────────────┐              │
│  │  [Receipt Image]     │              │
│  │  Screenshot showing  │              │
│  │  GCash transaction   │              │
│  └──────────────────────┘              │
│                                         │
│  Reference Number: _____________        │
│  (Optional, for record keeping)         │
│                                         │
│  [ Reject Payment ]  [ Approve Payment ]│
└─────────────────────────────────────────┘
```

---

## SMS Notifications

### On Order Placement (GCash)
```
CAFA Hardware: Your order #ORD-ABC123 (₱1,234.56) is received. 
Payment verification in progress. You'll be notified once verified.
```

### On Payment Approved
```
CAFA Hardware: Payment verified! Your order #ORD-ABC123 is confirmed. 
Delivery/pickup details will follow. Thank you!
```

### On Payment Rejected
```
CAFA Hardware: Payment verification failed for order #ORD-ABC123. 
Order cancelled. Please contact us if you believe this is an error.
```

---

## API Specification

### 1. Get Payment Information (Public)
```http
GET /api/payment-info
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

### 2. Place Order with GCash (Customer)
```http
POST /api/customer/orders
Content-Type: multipart/form-data
Authorization: Bearer {token}
```

**Request:**
```json
{
  "items": [
    {"product_id": 1, "quantity": 2}
  ],
  "payment_method": "gcash",
  "delivery_method": "pickup",
  "payment_proof": <file>
}
```

**Response:**
```json
{
  "message": "Order placed successfully. Payment verification pending.",
  "order": {
    "id": 1,
    "order_number": "ORD-ABC123",
    "status": "pending",
    "payment_status": "pending",
    "payment_method": "gcash",
    "total_amount": "1234.56",
    "payment_proof_url": "/storage/payment-proofs/ORD-ABC123.jpg"
  }
}
```

### 3. Verify Payment (Admin)
```http
POST /api/orders/{id}/verify-payment
Authorization: Bearer {admin_token}
```

**Request:**
```json
{
  "action": "approve",  // or "reject"
  "reference_number": "1234567890",  // optional
  "notes": "Payment verified via GCash"  // optional
}
```

**Response:**
```json
{
  "message": "Payment approved successfully",
  "order": {
    "id": 1,
    "payment_status": "paid",
    "status": "confirmed",
    "verified_at": "2025-11-09T14:30:00Z",
    "verified_by": 2
  }
}
```

---

## Error Handling

### Case 1: GCash selected but no receipt uploaded
```json
{
  "message": "Validation failed",
  "errors": {
    "payment_proof": [
      "Payment receipt is required for GCash payments"
    ]
  }
}
```

### Case 2: Invalid file type
```json
{
  "errors": {
    "payment_proof": [
      "Payment receipt must be an image (jpg, png)"
    ]
  }
}
```

### Case 3: File too large
```json
{
  "errors": {
    "payment_proof": [
      "Payment receipt must not exceed 5MB"
    ]
  }
}
```

---

## Implementation Checklist

### Backend
- [ ] Create migration for `payment_proof` field
- [ ] Create migration for `verified_at` and `verified_by` fields
- [ ] Update Order model with new fields
- [ ] Add GCash settings to SettingsController
- [ ] Update OrderController validation
- [ ] Implement file upload handling
- [ ] Create payment verification endpoint
- [ ] Update inventory logic (don't reduce until verified)
- [ ] Add SMS notifications for verification
- [ ] Create public payment-info endpoint

### Frontend (Customer Portal)
- [ ] Create GCash payment modal component
- [ ] Add file upload field
- [ ] Display payment instructions
- [ ] Show order status "pending verification"
- [ ] Handle file validation errors

### Frontend (Admin Portal)
- [ ] Create payment verification dashboard
- [ ] Add pending payments list
- [ ] Create receipt viewer modal
- [ ] Add approve/reject buttons
- [ ] Show verification status

### Testing
- [ ] Test file upload (valid/invalid formats)
- [ ] Test order without receipt (should fail)
- [ ] Test payment approval flow
- [ ] Test payment rejection flow
- [ ] Test inventory reduction timing
- [ ] Test SMS notifications

---

## Inventory Management Strategy

### Option 1: Stock Reservation (RECOMMENDED)
```php
// On order placement
Order::create([
    'status' => 'pending',
    'payment_status' => 'pending',
    'stock_reserved' => true,  // New field
]);

// Stock is marked as "reserved" but not reduced
Product::where('id', $productId)
    ->increment('reserved_quantity', $quantity);

// On payment approval
Product::where('id', $productId)
    ->decrement('reserved_quantity', $quantity)
    ->decrement('stock_quantity', $quantity);

// On rejection/cancellation
Product::where('id', $productId)
    ->decrement('reserved_quantity', $quantity);
```

### Option 2: Immediate Reduction with Rollback
```php
// On order placement
InventoryService::reduceStock($productId, $quantity);

// On rejection
InventoryService::addStock($productId, $quantity);
```

**Recommendation:** Use Option 1 for better inventory accuracy and to prevent overselling.

---

## Next Steps

1. Review and approve this flow
2. Implement database migrations
3. Update backend controllers
4. Create API endpoints
5. Build frontend components
6. Test thoroughly
7. Deploy to staging
8. User acceptance testing
9. Deploy to production

---

**Document Version:** 1.0  
**Last Updated:** November 9, 2025  
**Status:** Pending Implementation
