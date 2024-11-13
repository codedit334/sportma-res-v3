<template>
    <v-card class="my-5 mx-5">
        <v-card-title>Configuration Calendrier </v-card-title>
        <v-card-text>
            <!-- Add Split Type Button -->
            <v-btn color="primary" @click="openAddModal"
                >Ajout Sport / Terrain</v-btn
            >
            <!-- Search Bar -->
            <v-text-field
                class="mt-4"
                v-model="search"
                label="Rechercher..."
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                hide-details
                single-line
            ></v-text-field>

            <!-- Data Table -->
            <v-data-table
                :headers="headers"
                :items="splitTypes.sports"
                :search="search"
                :custom-filter="customFilter"
                class="mt-4"
                item-key="type"
            >
                <template #item.actions="{ item, index }">
                    <v-btn icon @click="openEditModal(index)">
                        <v-icon>mdi-pencil</v-icon>
                    </v-btn>
                    &nbsp;
                    <v-btn icon @click="removeSplitType(item.id)">
                        <v-icon>mdi-delete</v-icon>
                    </v-btn>
                </template>

                <template #item.terrains="{ item }">
                    <v-list dense>
                        <v-list-item
                            v-for="(terrain, tIndex) in item.terrains"
                            :key="tIndex"
                        >
                            <v-list-item-title>{{
                                terrain.label
                            }}</v-list-item-title>
                            <v-list-item-subtitle
                                v-for="price in terrain.prices"
                                :key="price.startTime"
                            >
                                {{ price.startTime }} --- {{ price.endTime }}:
                                {{ price.price }} DH
                            </v-list-item-subtitle>
                        </v-list-item>
                    </v-list>
                </template>
            </v-data-table>
        </v-card-text>

        <!-- Add/Edit Split Modal -->
        <v-dialog v-model="dialog" max-width="500px">
            <v-card>
                <v-card-title>
                    <span>{{
                        editIndex === null
                            ? "Ajout Sport / Terrain"
                            : "Modification Sport / Terrain"
                    }}</span>
                </v-card-title>

                <v-card-text>
                    <v-form
                        @submit.prevent="
                            editIndex === null
                                ? saveSplitType()
                                : updateSplitType()
                        "
                    >
                        <v-select
                            v-model="newSplitType"
                            :items="['Football', 'Padel']"
                            label="Choisir le sport"
                            placeholder="Choisir le sport"
                        ></v-select>

                        <v-text-field
                            v-model="newTerrain"
                            label="Terrain"
                            placeholder="e.g., Terrain 1, Terrain 2"
                            class="mt-4"
                        ></v-text-field>

                        <v-container>
                            <v-row justify="space-around">
                                <!-- Start Time Picker -->
                                <v-col cols="11" sm="4">
                                    <v-text-field
                                        v-model="startPicker"
                                        :active="menu2"
                                        :focus="menu2"
                                        label="Début"
                                        prepend-icon="mdi-clock-time-four-outline"
                                        readonly
                                    >
                                        <v-menu
                                            v-model="menu2"
                                            :close-on-content-click="false"
                                            activator="parent"
                                            transition="scale-transition"
                                        >
                                            <v-time-picker
                                                format="24hr"
                                                v-if="menu2"
                                                v-model="startPicker"
                                                full-width
                                            >
                                                <!-- Actions slot for closing the picker -->
                                                <template #actions>
                                                    <v-btn
                                                        text
                                                        color="primary"
                                                        @click="menu2 = false"
                                                        >Fermer</v-btn
                                                    >
                                                </template>
                                            </v-time-picker>
                                        </v-menu>
                                    </v-text-field>
                                </v-col>

                                <!-- End Time Picker -->
                                <v-col cols="11" sm="4">
                                    <v-text-field
                                        v-model="endPicker"
                                        :active="modal2"
                                        :focused="modal2"
                                        label="Fin"
                                        prepend-icon="mdi-clock-time-four-outline"
                                        readonly
                                    >
                                        <v-dialog
                                            v-model="modal2"
                                            activator="parent"
                                            width="auto"
                                        >
                                            <v-time-picker
                                                format="24hr"
                                                v-if="modal2"
                                                v-model="endPicker"
                                            >
                                                <!-- Actions slot for closing the picker -->
                                                <template #actions>
                                                    <v-btn
                                                        text
                                                        color="primary"
                                                        @click="modal2 = false"
                                                        >Fermer</v-btn
                                                    >
                                                </template>
                                            </v-time-picker>
                                        </v-dialog>
                                    </v-text-field>
                                </v-col>

                                <!-- Price Input Field -->
                                <v-col cols="11" sm="4">
                                    <v-text-field
                                        v-model="price"
                                        label="Prix"
                                        prepend-icon="mdi-currency-usd"
                                        type="number"
                                        min="0"
                                    ></v-text-field>
                                </v-col>
                            </v-row>

                            <!-- Add Price Range Button -->
                            <v-btn
                                color="secondary"
                                @click="addPriceRange"
                                class="mt-2"
                            >
                                Ajouter une plage de prix
                            </v-btn>
                        </v-container>

                        <!-- Display Added Price Ranges -->
                        <v-list dense class="mt-2">
                            <v-list-item
                                v-for="(price, index) in newPrices"
                                :key="index"
                            >
                                <v-list-item-title>
                                    De {{ price.startTime }} &agrave;
                                    {{ price.endTime }}: {{ price.price }} DH
                                    <v-btn
                                        icon
                                        color="red"
                                        @click="removePriceRange(index)"
                                    >
                                        <v-icon>mdi-delete</v-icon>
                                    </v-btn>
                                </v-list-item-title>
                            </v-list-item>
                        </v-list>
                    </v-form>
                </v-card-text>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="red darken-1" text @click="closeDialog"
                        >Annuler</v-btn
                    >
                    <v-btn
                        color="blue darken-1"
                        text
                        @click="
                            editIndex === null
                                ? saveSplitType()
                                : updateSplitType()
                        "
                        >Enregistrer</v-btn
                    >
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-card>
</template>

<script>
import { useStore } from "vuex";
import { VTimePicker } from "vuetify/labs/VTimePicker";
import { v4 as uuidv4 } from "uuid";

export default {
    components: {
        VTimePicker,
    },
    data() {
        return {
            dialog: false,
            startPicker: null,
            endPicker: null,
            price: null,
            editIndex: null,
            search: "",
            newSplitType: "",
            newTerrain: "",
            newTerrainID: null,
            newTerrains: [],
            newPrice: { startTime: "", endTime: "", price: "" },
            newPrices: [],
            menu2: false,
            modal2: false,
            headers: [
                { title: "Sport", key: "type" },
                { title: "Terrain", key: "terrains" },
                { title: "Actions", key: "actions" },
            ],
        };
    },
    computed: {
        user() {
            return this.$store.getters["auth/user"];
        },
        splitTypes() {
            return this.$store.getters["calendarConfig/splitTypes"];
        },
    },
    methods: {
        customFilter(value, search, item) {
            if (!search) return true;
            const lowerSearch = search.toLowerCase();
            const matchesType = item.raw.type.toLowerCase().includes(lowerSearch);
            const matchesTerrain = item.raw.terrains.some((terrain) =>
                terrain.label.toLowerCase().includes(lowerSearch)
            );
            return matchesType || matchesTerrain;
        },
        openAddModal() {
            this.resetForm();
            this.editIndex = null;
            this.dialog = true;
        },
        openEditModal(index) {
            const split = this.splitTypes.sports[index];
            this.newSplitType = split.type;
            this.newTerrain = split.terrains.length ? split.terrains[0].label : "";
            this.newTerrainID = split.terrains[0]?.terrainID || null;
            this.newPrices = split.terrains[0]?.prices || [];
            this.editIndex = index;
            this.dialog = true;
        },
        async saveSplitType() {
            if (this.newSplitType) {
                try {
                    await this.$store.dispatch("calendarConfig/addSplitType", {
                        companyId: this.user.company_id,
                        createdByUserId: this.user.id,
                        splitType: {
                            type: this.newSplitType,
                            terrains: [
                                {
                                    terrainID: uuidv4(),
                                    label: this.newTerrain,
                                    prices: [...this.newPrices],
                                },
                            ],
                        },
                    });
                    this.resetForm();
                    this.dialog = false;
                } catch (error) {
                    console.error("Error saving split type", error);
                }
            }
        },
        updateSplitType() {
            if (this.editIndex !== null) {
                const split = this.splitTypes.sports[this.editIndex];
                this.newTerrains = [
                    {
                        label: this.newTerrain,
                        prices: [...this.newPrices],
                    },
                ];

                this.$store.dispatch("calendarConfig/updateSplitType", {
                    id: split.id,
                    type: this.newSplitType,
                    terrains: this.newTerrains,
                    companyId: this.user.company_id,
                });
                this.resetForm();
                this.dialog = false;
            }
        },
        addPriceRange() {
            if (this.price <= 0) {
                alert("Erreur : Le prix doit être un nombre positif.");
                return;
            }

            this.newPrice.startTime = this.startPicker;
            this.newPrice.endTime = this.endPicker;
            this.newPrice.price = this.price;

            const newStartTime = new Date(`1970-01-01T${this.newPrice.startTime}:00`);
            const newEndTime = new Date(`1970-01-01T${this.newPrice.endTime}:00`);

            const isOverlap = this.newPrices.some((range) => {
                const existingStart = new Date(`1970-01-01T${range.startTime}:00`);
                const existingEnd = new Date(`1970-01-01T${range.endTime}:00`);
                return (
                    (newStartTime >= existingStart && newStartTime < existingEnd) ||
                    (newEndTime > existingStart && newEndTime <= existingEnd) ||
                    (newStartTime <= existingStart && newEndTime >= existingEnd)
                );
            });

            if (isOverlap) {
                alert("Erreur : Cette plage de temps chevauche une autre plage existante.");
            } else if (this.newPrice.startTime && this.newPrice.endTime && this.newPrice.price) {
                this.newPrices.push({ ...this.newPrice });
                this.newPrice = { startTime: "", endTime: "", price: "" };
            }

            this.resetTimePicker();
        },
        removePriceRange(index) {
            this.newPrices.splice(index, 1);
        },
        removeSplitType(index) {
            this.$store.dispatch("calendarConfig/removeSplitType", {
                id: index,
                companyId: this.user.company_id,
            });
        },
        closeDialog() {
            this.resetForm();
            this.dialog = false;
        },
        resetForm() {
            this.newSplitType = "";
            this.newTerrain = "";
            this.newTerrains = [];
            this.newPrices = [];
            this.newPrice = { startTime: "", endTime: "", price: "" };
            this.resetTimePicker();
        },
        resetTimePicker() {
            this.startPicker = null;
            this.endPicker = null;
            this.price = null;
        },
    },
    async mounted() {
       await this.$store.dispatch("auth/fetchUserProfile");
       await this.$store.dispatch("calendarConfig/fetchCalendarConfig", this.user.company_id);
    },
};
</script>

