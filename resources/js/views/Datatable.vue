<template>
    <v-card title="Reservations" flat class="my-5 mx-5">
        <template v-slot:text class="my-5 mx-5">
            <v-text-field
                v-model="search"
                label="Rechercher..."
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                hide-details
                single-line
            ></v-text-field>
        </template>

        <!-- Dropdown filters for each column -->
        <v-row class="mb-4 ml-1">
            <!-- Right-aligned button column -->
            <v-col cols="12" class="d-flex justify-end">
                <v-btn class="mt-2 mb-4 mr-5" color="primary" @click="exportToPDF">
                    Exporter en PDF
                </v-btn>
            </v-col>
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
            <v-col cols="12" sm="3">
                <v-date-input
                    v-model="selectedFilters.dateRange"
                    label="Du - Au"
                    max-width="368"
                    multiple="range"
                    prepend-icon=""
                    prepend-inner-icon="$calendar"
                ></v-date-input>
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
import { ref, computed, watch } from "vue";
import { useStore } from "vuex";
import jsPDF from "jspdf";
import "jspdf-autotable";
import { VDateInput } from "vuetify/labs/VDateInput";

const store = useStore();

const search = ref("");
const menu2 = ref(false);
const startPicker = ref(null);

const headers = [
    { align: "start", key: "nom", title: "Nom" },
    { key: "categorie", title: "Categorie" },
    { key: "terrain", title: "Terrain" },
    { key: "prix", title: "Prix (DH)" },
    { key: "statut", title: "Statut" },
];

const events = computed(() => store.state.calendar.events);

const reservations = computed(() =>
    events.value.map((event) => ({
        id: event.id,
        nom: event.title,
        categorie: event.category,
        terrain: event.terrain,
        prix: event.price,
        statut: event.statut,
        date: new Date(event.start), // Ensure date is in Date format
    }))
);

const selectedFilters = ref({
    nom: null,
    categorie: null,
    terrain: null,
    prix: null,
    statut: null,
    dateRange: null,
});

const dateMenu = ref(false);
const dateRangeText = computed(() => {
    if (selectedFilters.value.dateRange) {
        const [start, end] = selectedFilters.value.dateRange;
        return start && end
            ? `${start.toLocaleDateString()} - ${end.toLocaleDateString()}`
            : "";
    }
    return "";
});

function uniqueValues(key) {
    return [...new Set(reservations.value.map((item) => item[key]))];
}

const filteredReservations = computed(() => {
    return reservations.value.filter((item) => {
        const matchesFilters = Object.keys(selectedFilters.value).every((key) => {
            if (key === "dateRange" && selectedFilters.value.dateRange && selectedFilters.value.dateRange.length) {
                const dateRange = selectedFilters.value.dateRange.map(date => new Date(date).toDateString());
                
                // Check if the item's date falls on any of the selected dates in the range
                return dateRange.some(rangeDate => 
                    new Date(item.date).toDateString() === rangeDate
                );
            }
            return (
                !selectedFilters.value[key] ||
                item[key] === selectedFilters.value[key]
            );
        });
        return matchesFilters;
    });
});


function applyFilters() {}

const calculateTotalPrice = () => {
    return filteredReservations.value.reduce(
        (sum, row) =>
            sum + (row.statut === "Payé" ? parseFloat(row.prix) || 0 : 0),
        0
    );
};

const exportToPDF = () => {
    const doc = new jsPDF();
    const columns = headers.map((header) => header.title);
    const rows = filteredReservations.value.map((row) => [
        row.nom,
        row.categorie,
        row.terrain,
        row.prix,
        row.statut,
    ]);

    doc.autoTable({
        head: [columns],
        body: rows,
        startY: 10,
    });

    const totalPrice = calculateTotalPrice();
    doc.autoTable({
        body: [["", "", "", "Total Price (DH)", totalPrice]],
        startY: doc.lastAutoTable.finalY + 10,
        theme: "plain",
        styles: { fontStyle: "bold" },
    });

    const logoLeft = "https://sportma.ma/assets/sportmaApp-ERXWPjF0.jpeg";
    const logoRight =
        "https://images.pexels.com/photos/2453205/pexels-photo-2453205.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2";
    const pageHeight = doc.internal.pageSize.height;
    const logoWidth = 15;
    const logoHeight = 15;

    doc.addImage(
        logoLeft,
        "JPEG",
        10,
        pageHeight - logoHeight - 10,
        logoWidth,
        logoHeight
    );
    doc.addImage(
        logoRight,
        "JPEG",
        doc.internal.pageSize.width - logoWidth - 10,
        pageHeight - logoHeight - 10,
        logoWidth,
        logoHeight
    );

    doc.save("reservations.pdf");
};
</script>

<style scoped></style>
