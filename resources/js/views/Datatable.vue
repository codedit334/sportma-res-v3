<template>
    <v-card title="Reservations" flat class="my-5 mx-5">
        <template v-slot:text class="my-5 mx-5">
            <v-text-field
                v-model="search"
                label="Search"
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                hide-details
                single-line
            ></v-text-field>
        </template>

        <v-btn class="mt-2 mb-4 mx-5" color="primary" @click="exportToPDF">
            Exporter en PDF
        </v-btn>

        <!-- Dropdown filters for each column -->
        <v-row class="mb-4 ml-1">
            <v-col cols="12" md="2">
                <v-select
                    v-model="selectedFilters.nom"
                    :items="uniqueValues('nom')"
                    label="Nom"
                    clearable
                    @change="applyFilters"
                ></v-select>
            </v-col>
            <v-col cols="12" md="2">
                <v-select
                    v-model="selectedFilters.categorie"
                    :items="uniqueValues('categorie')"
                    label="Categorie"
                    clearable
                    @change="applyFilters"
                ></v-select>
            </v-col>
            <v-col cols="12" md="2">
                <v-select
                    v-model="selectedFilters.terrain"
                    :items="uniqueValues('terrain')"
                    label="Terrain"
                    clearable
                    @change="applyFilters"
                ></v-select>
            </v-col>
            <v-col cols="12" md="2">
                <v-select
                    v-model="selectedFilters.prix"
                    :items="uniqueValues('prix')"
                    label="Prix (DH)"
                    clearable
                    @change="applyFilters"
                ></v-select>
            </v-col>
            <v-col cols="12" md="2">
                <v-select
                    v-model="selectedFilters.statut"
                    :items="uniqueValues('statut')"
                    label="Statut"
                    clearable
                    @change="applyFilters"
                ></v-select>
            </v-col>
        </v-row>

        <v-data-table
            :headers="headers"
            :items="filteredReservations"
            :search="search"
            :no-data-text="'Aucune donnée disponible'"
            :loading-text="'Chargement...'"
            :items-per-page-text="'Éléments par page'"
            :pagination-text="'Pagination'"
        ></v-data-table>
    </v-card>
</template>

<script setup>
import { ref, computed } from "vue";
import { useStore } from "vuex";
import jsPDF from "jspdf";
import "jspdf-autotable";


const store = useStore();

const search = ref("");
const headers = [
    { align: "start", key: "nom", title: "Nom" },
    { key: "categorie", title: "Categorie" },
    { key: "terrain", title: "Terrain" },
    { key: "prix", title: "Prix (DH)" },
    { key: "statut", title: "Statut" },
];

// Computed property to get the events array from the Vuex store
const events = computed(() => store.state.calendar.events);

// Define reservations and selected filters for each column
const reservations = computed(() =>
    events.value.map((event) => ({
        id: event.id,
        nom: event.title,
        categorie: event.category,
        terrain: event.terrain,
        prix: event.price,
        statut: event.statut,
    }))
);

// State for selected filters
const selectedFilters = ref({
    nom: null,
    categorie: null,
    terrain: null,
    prix: null,
    statut: null,
});

// Compute unique values for each filter dropdown
function uniqueValues(key) {
    return [...new Set(reservations.value.map((item) => item[key]))];
}

// Filter reservations based on selected dropdown filters
const filteredReservations = computed(() => {
    return reservations.value.filter((item) => {
        return Object.keys(selectedFilters.value).every((key) => {
            return (
                !selectedFilters.value[key] ||
                item[key] === selectedFilters.value[key]
            );
        });
    });
});

// Trigger filtering on dropdown change
function applyFilters() {
    // Just here to trigger `filteredReservations` to recompute
}

const exportToPDF = () => {
  const doc = new jsPDF();
  
  console.log("reservations", reservations.value);
  // Define table columns and rows
  const columns = headers.map(header => header.title);
  const rows = filteredReservations.value.map(row => headers.map(header => row[header.key]));

  // Add the table to the PDF
  doc.autoTable({
    head: [columns],
    body: rows,
    startY: 10,
  });

  // Save the PDF
  doc.save("reservations.pdf");
};

</script>
