<template>
    <v-card class="my-5 mx-5">
        <v-card-title>Configuration Calendrier </v-card-title>
        <v-card-text>
            <!-- Add Split Type Button -->
            <v-btn color="primary" @click="openAddModal"
                >Ajout Sport / Terrain</v-btn
            >

            <!-- Data Table -->
            <v-data-table
                :headers="headers"
                :items="splitTypes"
                class="mt-4"
                item-key="type"
            >
                <template #item.actions="{ item, index }">
                    <v-btn icon @click="openEditModal(index)">
                        <v-icon>mdi-pencil</v-icon>
                    </v-btn>
                    &nbsp;
                    <v-btn icon @click="removeSplitType(index)">
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

<script setup>
import { ref, computed } from "vue";
import { useStore } from "vuex";
import { VTimePicker } from "vuetify/labs/VTimePicker";
import { v4 as uuidv4 } from "uuid";

const store = useStore();
const dialog = ref(false);
const startPicker = ref(null);
const endPicker = ref(null);
const price = ref(null);
const editIndex = ref(null);
const newSplitType = ref("");
const newTerrain = ref("");
const newTerrainID = ref(null);
const newPrice = ref({ startTime: "", endTime: "", price: "" });
const newPrices = ref([]);
const splitTypes = computed(() => store.getters["calendarConfig/splitTypes"]);
const menu2 = ref(false);
const modal2 = ref(false);

// Define your data table headers
const headers = ref([
    { title: "Sport", value: "type" },
    { title: "Terrain", value: "terrains" },
    { title: "Actions", value: "actions" },
]);

const openAddModal = () => {
    resetForm();
    editIndex.value = null;
    dialog.value = true;
};

const openEditModal = (index) => {
    const split = splitTypes.value[index];
    newSplitType.value = split.type;
    newTerrain.value = split.terrains.length ? split.terrains[0].label : "";
    // get terrain id
    newTerrainID.value = split.terrains[0]?.terrainID || null;
    newPrices.value = split.terrains[0]?.prices || [];
    editIndex.value = index;
    dialog.value = true;
};

const saveSplitType = () => {
    if (newSplitType.value) {
        store.commit("calendarConfig/ADD_SPLIT_TYPE", {
            type: newSplitType.value,
            terrains: [
                {
                    // type: newSplitType.value,
                    terrainID: uuidv4(),
                    label: newTerrain.value,
                    prices: [...newPrices.value],
                },
            ],
        });
        resetForm();
        dialog.value = false;
    }
};

const updateSplitType = () => {
    if (editIndex.value !== null) {
        store.commit("calendarConfig/UPDATE_SPLIT_TYPE", {
            index: editIndex.value,
            splitType: newSplitType.value,
            terrain: newTerrain.value,
            terrainID: newTerrainID.value,
            prices: [...newPrices.value],
        });
        resetForm();
        dialog.value = false;
    }
};

const addPriceRange = () => {
    newPrice.value.startTime = startPicker;
    newPrice.value.endTime = endPicker;
    newPrice.value.price = price;

    // Convert start and end times to Date objects for easy comparison
    const newStartTime = new Date(`1970-01-01T${newPrice.value.startTime}:00`);
    const newEndTime = new Date(`1970-01-01T${newPrice.value.endTime}:00`);

    // Check if any existing price range overlaps with the new range
    const isOverlap = newPrices.value.some((range) => {
        const existingStart = new Date(`1970-01-01T${range.startTime}:00`);
        const existingEnd = new Date(`1970-01-01T${range.endTime}:00`);
        return (
            (newStartTime >= existingStart && newStartTime < existingEnd) ||
            (newEndTime > existingStart && newEndTime <= existingEnd) ||
            (newStartTime <= existingStart && newEndTime >= existingEnd)
        );
    });

    // If there's an overlap, alert an error in French
    if (isOverlap) {
        alert(
            "Erreur : Cette plage de temps chevauche une autre plage existante."
        );
    } else if (
        newPrice.value.startTime &&
        newPrice.value.endTime &&
        newPrice.value.price
    ) {
        // Push the new price range if no overlap
        newPrices.value.push({ ...newPrice.value });
        newPrice.value = { startTime: "", endTime: "", price: "" };
    }

    resetTimePicker();
};

const removePriceRange = (index) => {
    newPrices.value.splice(index, 1);
};

const removeSplitType = (index) => {
    store.commit("calendarConfig/REMOVE_SPLIT_TYPE", index);
};

const closeDialog = () => {
    resetForm();
    dialog.value = false;
};

const resetForm = () => {
    newSplitType.value = "";
    newTerrain.value = "";
    newPrices.value = [];
    newPrice.value = { startTime: "", endTime: "", price: "" };
    resetTimePicker();
};

const resetTimePicker = () => {
    startPicker.value = null;
    endPicker.value = null;
    price.value = null;
};
</script>
