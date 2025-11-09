import { defineStore } from 'pinia';

export const useToastStore = defineStore('toast', {
  state: () => ({
    toasts: [],
    nextId: 1
  }),

  actions: {
    add({ type = 'success', message, duration = 3000 }) {
      const id = this.nextId++;
      
      this.toasts.push({
        id,
        type,
        message
      });

      setTimeout(() => {
        this.remove(id);
      }, duration);
    },

    show({ type = 'success', message, duration = 3000 }) {
      this.add({ type, message, duration });
    },

    remove(id) {
      const index = this.toasts.findIndex(toast => toast.id === id);
      if (index > -1) {
        this.toasts.splice(index, 1);
      }
    },

    success(message, duration) {
      this.add({ type: 'success', message, duration });
    },

    error(message, duration) {
      this.add({ type: 'error', message, duration });
    }
  }
});
