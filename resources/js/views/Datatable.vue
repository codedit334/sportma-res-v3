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

    <v-data-table
      :headers="headers"
      :items="reservations"
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

const store = useStore();

// Computed property to get the events array from the Vuex store
const events = computed(() => store.state.calendar.events);
console.log(events.value);


const search = ref("");
const headers = [
  {
    align: "start",
    key: "nom",
    title: "Nom",
  },
  {
    key: "categorie",
    title: "Categorie",
  },
  {
    key: "terrain",
    title: "Terrain",
  },
  {
    key: "prix",
    title: "Prix (DH)",
  },
  {
    key: "statut",
    title: "Statut",
  },
];

// Map through events array and assign it to reservations

const reservations = events.value.map((event) => ({
  id: event.id,
  nom: event.title,
  categorie: event.category,
  terrain: event.terrain,
  prix: event.price,
  statut: event.statut,
}));



</script>
