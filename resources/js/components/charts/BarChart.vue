<template>
  <div class="relative">
    <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-10">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>
    <div v-else-if="isEmpty" class="absolute inset-0 flex flex-col items-center justify-center bg-gray-50 rounded-lg z-10">
      <svg class="h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
      </svg>
      <p class="text-gray-500 font-medium">No data available</p>
      <p class="text-gray-400 text-sm mt-1">No sales data found for the selected period</p>
    </div>
    <canvas ref="chartRef"></canvas>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, onUnmounted } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
  data: {
    type: Object,
    required: true,
    default: () => ({
      labels: [],
      datasets: []
    })
  },
  options: {
    type: Object,
    default: () => ({})
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const chartRef = ref(null);
const chart = ref(null);

const isEmpty = computed(() => {
  return !props.loading && 
         (!props.data.labels || props.data.labels.length === 0) &&
         (!props.data.datasets || props.data.datasets.length === 0 || 
          props.data.datasets.every(ds => !ds.data || ds.data.length === 0));
});

const createChart = () => {
  if (!chartRef.value) return;

  // Destroy existing chart if it exists
  if (chart.value) {
    chart.value.destroy();
    chart.value = null;
  }

  // Deep clone the data to remove all reactivity
  const chartData = JSON.parse(JSON.stringify(props.data));

  const ctx = chartRef.value.getContext('2d');
  chart.value = new Chart(ctx, {
    type: 'bar',
    data: chartData,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top'
        },
        tooltip: {
          enabled: true
        }
      },
      scales: {
        y: {
          beginAtZero: true
        }
      },
      ...props.options
    }
  });
};

// Watch for data changes and recreate the chart
watch(() => props.data, () => {
  createChart();
});

onMounted(() => {
  createChart();
});

onUnmounted(() => {
  if (chart.value) {
    chart.value.destroy();
    chart.value = null;
  }
});
</script>
