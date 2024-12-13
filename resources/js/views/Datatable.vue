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
                <v-btn
                    class="mt-2 mb-4 mr-5"
                    color="primary"
                    @click="exportToPDF"
                >
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

<script>
import { useStore } from "vuex";
import jsPDF from "jspdf";
import "jspdf-autotable";
import { VDateInput } from "vuetify/labs/VDateInput";

export default {
    data() {
        return {
            events: [], // Store the events array
            search: "",
            menu2: false,
            startPicker: null,
            selectedFilters: {
                nom: null,
                categorie: null,
                terrain: null,
                prix: null,
                statut: null,
                dateRange: null,
            },
            dateMenu: false,
        };
    },
    components: { VDateInput },
    computed: {
        headers() {
            return [
                { align: "start", key: "nom", title: "Nom" },
                { key: "categorie", title: "Categorie" },
                { key: "terrain", title: "Terrain" },
                { key: "prix", title: "Prix (DH)" },
                { key: "statut", title: "Statut" },
            ];
        },
        reservations() {
            return this.events.map((event) => ({
                id: event.id,
                nom: event.title,
                categorie: event.category,
                terrain: event.terrain,
                prix: event.price,
                statut: event.status,
                date: new Date(event.start), // Ensure date is in Date format
            }));
        },
        dateRangeText() {
            if (this.selectedFilters.dateRange) {
                const [start, end] = this.selectedFilters.dateRange;
                return start && end
                    ? `${start.toLocaleDateString()} - ${end.toLocaleDateString()}`
                    : "";
            }
            return "";
        },
        filteredReservations() {
            return this.reservations.filter((item) => {
                return Object.keys(this.selectedFilters).every((key) => {
                    if (key === "dateRange") {
                        // Check if dateRange is defined
                        if (
                            !this.selectedFilters.dateRange ||
                            !this.selectedFilters.dateRange.length
                        ) {
                            return true;
                        }

                        // Convert itemDate to just the date part
                        const itemDate = new Date(item.date);
                        itemDate.setHours(0, 0, 0, 0);

                        // Extract the first and last dates from the dateRange
                        const dateRangeArray =
                            this.selectedFilters.dateRange.map(
                                (date) => new Date(date)
                            );
                        const firstDate = dateRangeArray[0];
                        const lastDate =
                            dateRangeArray[dateRangeArray.length - 1];

                        // Check if itemDate is within the range
                        const isWithinRange =
                            itemDate >= firstDate && itemDate <= lastDate;

                        return isWithinRange;
                    }

                    return (
                        !this.selectedFilters[key] ||
                        item[key] === this.selectedFilters[key]
                    );
                });
            });
        },
        user() {
            return this.$store.state.auth.user; // Correctly access user data from the store
        },
    },
    methods: {
        uniqueValues(key) {
            return [...new Set(this.reservations.map((item) => item[key]))];
        },
        applyFilters() {
            // Logic to apply filters, if needed
        },
        calculateTotalPrice() {
            return this.filteredReservations.reduce(
                (sum, row) =>
                    sum +
                    (row.statut === "Payé" ? parseFloat(row.prix) || 0 : 0),
                0
            );
        },
        async exportToPDF() {
            const doc = new jsPDF();
            const columns = this.headers.map((header) => header.title);
            const rows = this.filteredReservations.map((row) => [
                row.nom,
                row.categorie,
                row.terrain,
                row.prix,
                row.statut,
            ]);

            // Add logos to the top
            let logoLeftUrl = "/storage/profile_pictures/sportmalogo.jpeg";
            let logoRightUrl = "/storage/" + this.user.profile_picture;
            // Await the conversion of both images
            const logoLeftBase64 = await this.convertImageToBase64(logoLeftUrl);
            const logoRightBase64 = await this.convertImageToBase64(
                logoRightUrl
            );

            console.log("HERE", logoLeftBase64);

            const logoWidth = 15;
            const logoHeight = 15;

            // Set y-coordinate to position logos at the top of the page
            const topYPosition = 10; // 10 units from the top
            // Add the logos to the PDF
            doc.addImage(
                logoLeftBase64,
                this.getImageFormat(logoLeftBase64),
                10,
                topYPosition,
                logoWidth,
                logoHeight
            );
            doc.addImage(
                logoRightBase64,
                this.getImageFormat(logoRightBase64),
                doc.internal.pageSize.width - logoWidth - 10,
                topYPosition,
                logoWidth,
                logoHeight
            );

            // Add date or date range if selected
            const dateText = this.dateRangeText
                ? `Date: ${this.dateRangeText}`
                : this.selectedFilters.dateRange
                ? `Date: ${new Date(
                      this.selectedFilters.dateRange[0]
                  ).toLocaleDateString()}`
                : "";

            const dateTextHeight = 10; // Height to ensure there's space for the date text
            doc.text(dateText, 10, topYPosition + logoHeight + dateTextHeight);

            // Add table below the date text
            doc.autoTable({
                head: [columns],
                body: rows,
                startY: topYPosition + logoHeight + dateTextHeight + 10, // Start the table below the date text
            });

            const totalPrice = this.calculateTotalPrice();
            doc.autoTable({
                body: [["", "", "", "Prix Total (DH)", totalPrice]],
                startY: doc.lastAutoTable.finalY + 10,
                theme: "plain",
                styles: { fontStyle: "bold" },
            });

            doc.save("reservations.pdf");
        },
        async convertImageToBase64(imageUrl) {
            try {
                // Fetch the image as a Blob
                const response = await fetch(imageUrl);
                const blob = await response.blob();

                // Wrap the FileReader operations in a Promise
                const base64String = await new Promise((resolve, reject) => {
                    const reader = new FileReader();

                    reader.onloadend = () => {
                        resolve(reader.result); // Resolve with the base64 string
                    };

                    reader.onerror = (error) => {
                        reject(error); // Reject if there's an error
                    };

                    reader.readAsDataURL(blob); // Pass the Blob to FileReader
                });

                return base64String; // Return the base64 string
            } catch (error) {
                console.error("Error fetching or converting image:", error);
                throw error; // Rethrow the error if needed
            }
        },

        getImageFormat(base64String) {
            // Extract the image format from the Base64 string prefix
            if (base64String.startsWith("data:image/jpeg")) {
                return "JPEG";
            } else if (base64String.startsWith("data:image/png")) {
                return "PNG";
            } else if (base64String.startsWith("data:image/webp")) {
                return "WEBP";
            } else {
                console.warn("Unknown image format, defaulting to JPEG");
                return "JPEG"; // Default to JPEG if format is unknown
            }
        },
    },
    mounted() {
        this.$store.dispatch("calendar/fetchEvents").then(() => {
            this.events = this.$store.state.calendar.events;
        });
    },
};
</script>
