<template>
  <v-container>
    <!-- Time Frame Filter -->
    <v-row>
      <v-col cols="12">
        <div class="bullet-filter mb-4">
          <span
            v-for="option in timeFrameOptions"
            :key="option"
            :class="['bullet-option', { active: selectedTimeFrame === option }]"
            @click="selectTimeFrame(option)"
          >
            {{ option }}
          </span>
        </div>
      </v-col>
    </v-row>

    <!-- Cards Row -->
    <v-row>
      <!-- Reservations Card -->
      <v-col cols="12" md="4">
        <v-card
          class="pa-3 card-hover modern-card reservations-card"
          color="primary"
          dark
        >
          <v-card-title>
            <span class="material-icons material-symbols-outlined icon-align">event</span>
            Reservations
          </v-card-title>
          <v-card-text>
            <div class="headline">{{ reservationsCount }}</div>
            <div class="subtitle-2">
              Croissance: <span>{{ reservationsGrowth }}%</span>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Cancellations Card -->
      <v-col cols="12" md="4">
        <v-card
          class="pa-3 card-hover modern-card cancellations-card"
          color="error"
          dark
        >
          <v-card-title>
            <span class="material-icons material-symbols-outlined icon-align">cancel</span>
            Annulations
          </v-card-title>
          <v-card-text>
            <div class="headline">{{ cancellationsCount }}</div>
            <div class="subtitle-2">
              Croissance: <span>{{ cancellationsGrowth }}%</span>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Income Card -->
      <v-col cols="12" md="4">
        <v-card
          class="pa-3 card-hover modern-card income-card"
          color="success"
          dark
        >
          <v-card-title>
            <span class="material-icons material-symbols-outlined icon-align"
              >attach_money</span
              >Revenue
          </v-card-title>
          <v-card-text>
            <div class="headline">{{ income }}</div>
            <div class="subtitle-2">
              Croissance: <span>{{ incomeGrowth }}%</span>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Chart Row -->
    <v-row>
      <v-col cols="12">
        <v-card class="mt-4">
          <v-card-title>Reservations</v-card-title>
          <v-card-text>
            <select
              v-model="selectedChartTimeFrame"
              @change="updateChartData"
              style="
                width: 100%;
                height: 50px;
                background-color: #f5f5f5;
                padding: 10px;
              "
            >
              <option v-for="option in chartTimeFrameOptions" :value="option" :key="option">
                {{ option }}
              </option>
            </select>
            <div class="chart-container">
              <Bar v-if="chartData" :options="chartOptions" :data="chartData" />
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

  </v-container>
</template>

<script>
import { Bar } from "vue-chartjs";
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

export default {
  components: {
    Bar,
  },
  data() {
    return {
      // Default Data (same as before)
      reservationsCount: 500,
      reservationsGrowth: 10,
      cancellationsCount: 50,
      cancellationsGrowth: -5,
      income: "$10,000",
      incomeGrowth: 8,

      // Time Frame Options
      selectedTimeFrame: "Mois",
      timeFrameOptions: ["Aujourdhui", "Hier", "Semaine", "Mois"],

      // Chart Data
      selectedChartTimeFrame: "Jours",
      chartTimeFrameOptions: ["Jours", "Mois"],
      chartData : null,
    };
  },
  mounted() {
    this.updateChartData();
  },
  methods: {
    // Filter chart data according to selected time frame
    updateChartData() {
      // Update chart data based on selectedChartTimeFrame
      switch (this.selectedChartTimeFrame) {
        case "Jours":
          this.chartData = {
            labels: [ 'J1', 'J2', 'J3', 'J4', 'J5', 'J6', 'J7' ],
            datasets: [ { label: 'Reservations', backgroundColor: '#868e96', data: [40, 20, 12, 10, 8, 6, 4] } ]
          }
          break;
        case "Mois":
          this.chartData = {
            // mounths in french
            labels: [ 'Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jun', 'Jui', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec' ],
            datasets: [ { label: 'Reservations', backgroundColor: '#868e96', data: [40, 20, 12, 10, 8, 6, 4, 10, 12, 14, 16, 18] } ]
          }
          break;
      }
    },
    // Time Frame Selection
    selectTimeFrame(option) {
      this.selectedTimeFrame = option;
      this.filterData();
    },
    filterData() {
      // Filter card data based on selectedTimeFrame (same as before)
      switch (this.selectedTimeFrame) {
        case "Aujourdhui":
          this.reservationsCount = 15;
          this.reservationsGrowth = 2;
          this.cancellationsCount = 2;
          this.cancellationsGrowth = 0;
          this.income = "$1,000";
          this.incomeGrowth = 1;
          break;
        case "Hier":
          this.reservationsCount = 20;
          this.reservationsGrowth = -1;
          this.cancellationsCount = 3;
          this.cancellationsGrowth = -2;
          this.income = "$900";
          this.incomeGrowth = -5;
          break;
        case "Semaine":
          this.reservationsCount = 100;
          this.reservationsGrowth = 5;
          this.cancellationsCount = 8;
          this.cancellationsGrowth = -1;
          this.income = "$7,000";
          this.incomeGrowth = 3;
          break;
        case "Mois":
          this.reservationsCount = 500;
          this.reservationsGrowth = 10;
          this.cancellationsCount = 50;
          this.cancellationsGrowth = -5;
          this.income = "$10,000";
          this.incomeGrowth = 8;
          break;
      }
    },
  },
};
</script>

<style scoped>
.icon-align {
  vertical-align: middle;
  font-size: 24px;
  margin-right: 8px;
  display: inline-flex;
  align-items: center;
}

.card-hover {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card-hover:hover {
  transform: translateY(-5px);
  cursor: pointer;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}

/* Modern Card Styles */
.modern-card {
  border-radius: 0;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  background-color: #e0e0e0; /* Light grey for brutalist look */
}

/* Specific Card Colors */
.reservations-card {
  background: #d0d0d0; /* Light grey */
  color: #f5f5f5; /* Off-white text */
}
.cancellations-card {
  background: #bfbfbf; /* Medium-light grey */
  color: #f0f0f0; /* Off-white text */
}
.income-card {
  background: #c8c8c8; /* Another shade of grey */
  color: #f0f0f0; /* Off-white text */
}

/* Card Text Styles */
.headline {
  font-size: 2em;
  font-weight: bold;
  color: #f0f0f0; /* Light off-white for strong contrast */
}

.subtitle-2 {
  font-size: 1em;
  color: #d9d9d9; /* Light grey for subtitle text */
}

.chart-container {
  position: relative;
  width: 100%;
  height: 400px;
}

/* Bullet Filter */
.bullet-filter {
  display: flex;
  justify-content: space-around;
}

.bullet-option {
  padding: 10px;
  cursor: pointer;
  border-radius: 0;
  background-color: #d3d3d3; /* Light grey */
  color: #3a3a3a; /* Off-white text */
  text-align: center;
  width: 100px;
  height: 35px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 15.5px;
  transition: background-color 0.3s ease;
}

.bullet-option:hover {
  background-color: #b0b0b0; /* Slightly darker grey on hover */
}

.bullet-option.active {
  background-color: #999999; /* Medium grey for active state */
}
</style>



