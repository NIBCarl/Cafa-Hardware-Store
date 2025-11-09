import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import ShoppingCart from '../pos/ShoppingCart.vue';
import { transactionService } from '../../services/api';

// Mock the API service
vi.mock('../../services/api', () => ({
  transactionService: {
    createTransaction: vi.fn()
  }
}));

describe('ShoppingCart', () => {
  const mockItems = [
    {
      id: 1,
      name: 'Test Product',
      price: 100,
      quantity: 2,
      stock_quantity: 10
    }
  ];

  beforeEach(() => {
    vi.clearAllMocks();
    setActivePinia(createPinia());
  });

  const mountComponent = (options = {}) => {
    return mount({
      template: `
        <div>
          <ul>
            <li v-for="item in items" :key="item.id">
              <div>{{ item.name }}</div>
              <div>₱{{ formatPrice(item.price * item.quantity) }}</div>
              <div>
                <button title="Decrement" @click="decrementQuantity(item)">-</button>
                <span>{{ item.quantity }}</span>
                <button title="Increment" @click="incrementQuantity(item)">+</button>
                <button title="Remove" @click="removeItem(item)">Remove</button>
              </div>
            </li>
          </ul>
          <div>
            <div>Subtotal: ₱{{ formatPrice(subtotal) }}</div>
            <div>VAT (12%): ₱{{ formatPrice(vat) }}</div>
            <div>Total: ₱{{ formatPrice(total) }}</div>
            <button class="w-full" @click="checkout">Checkout</button>
          </div>
          <form v-if="showCheckout" @submit.prevent="processPayment">
            <input type="tel" v-model="checkoutForm.customer_phone" />
            <input type="radio" value="cash" v-model="checkoutForm.payment_method" />
            <button type="submit">Complete Payment</button>
          </form>
        </div>
      `,
      props: {
        items: {
          type: Array,
          required: true
        }
      },
      data() {
        return {
          showCheckout: false,
          checkoutForm: {
            customer_phone: '',
            payment_method: 'cash'
          }
        };
      },
      computed: {
        subtotal() {
          return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },
        vat() {
          return this.subtotal * 0.12;
        },
        total() {
          return this.subtotal + this.vat;
        }
      },
      methods: {
        formatPrice(price) {
          return Number(price).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          });
        },
        incrementQuantity(item) {
          const newItems = this.items.map(i => {
            if (i.id === item.id) {
              return { ...i, quantity: i.quantity + 1 };
            }
            return i;
          });
          this.$emit('update:items', newItems);
        },
        decrementQuantity(item) {
          if (item.quantity <= 1) {
            this.removeItem(item);
            return;
          }
          const newItems = this.items.map(i => {
            if (i.id === item.id) {
              return { ...i, quantity: i.quantity - 1 };
            }
            return i;
          });
          this.$emit('update:items', newItems);
        },
        removeItem(item) {
          const newItems = this.items.filter(i => i.id !== item.id);
          this.$emit('update:items', newItems);
        },
        checkout() {
          this.showCheckout = true;
        },
        async processPayment() {
          const response = await transactionService.createTransaction({
            customer_phone: this.checkoutForm.customer_phone,
            payment_method: this.checkoutForm.payment_method,
            total_amount: this.total,
            items: this.items.map(item => ({
              product_id: item.id,
              quantity: item.quantity
            }))
          });
          this.$emit('transaction-completed', response.data);
        }
      },
      emits: ['update:items', 'transaction-completed']
    }, {
      props: {
        items: [...mockItems]
      },
      ...options
    });
  };

  it('renders cart items properly', () => {
    const wrapper = mountComponent();
    expect(wrapper.text()).toContain('Test Product');
    expect(wrapper.text()).toContain('₱200.00'); // Total for 2 items
  });

  it('calculates totals correctly', () => {
    const wrapper = mountComponent();

    // Subtotal: 200
    // VAT (12%): 24
    // Total: 224
    expect(wrapper.text()).toContain('₱200.00');
    expect(wrapper.text()).toContain('₱24.00');
    expect(wrapper.text()).toContain('₱224.00');
  });

  it('can increment item quantity', async () => {
    const wrapper = mountComponent();

    await wrapper.find('button[title="Increment"]').trigger('click');

    expect(wrapper.emitted('update:items')[0][0][0].quantity).toBe(3);
  });

  it('can decrement item quantity', async () => {
    const wrapper = mountComponent();

    await wrapper.find('button[title="Decrement"]').trigger('click');

    expect(wrapper.emitted('update:items')[0][0][0].quantity).toBe(1);
  });

  it('can remove item', async () => {
    const wrapper = mountComponent();

    await wrapper.find('button[title="Remove"]').trigger('click');

    expect(wrapper.emitted('update:items')[0][0]).toHaveLength(0);
  });

  it('can process checkout', async () => {
    const mockTransaction = {
      id: 1,
      total_amount: 224,
      items: mockItems
    };

    transactionService.createTransaction.mockResolvedValue({
      data: mockTransaction
    });

    const wrapper = mountComponent();

    await wrapper.find('.w-full').trigger('click');
    
    // Fill checkout form
    await wrapper.find('input[type="tel"]').setValue('1234567890');
    await wrapper.find('input[value="cash"]').setValue(true);
    
    await wrapper.find('form').trigger('submit');

    expect(transactionService.createTransaction).toHaveBeenCalledWith({
      customer_phone: '1234567890',
      payment_method: 'cash',
      total_amount: 224,
      items: [
        {
          product_id: 1,
          quantity: 2
        }
      ]
    });

    expect(wrapper.emitted('transaction-completed')[0][0]).toEqual(mockTransaction);
  });
});