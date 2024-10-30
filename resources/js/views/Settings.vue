<template>
  <v-card class="my-5 mx-5">
    <v-card-title>Calendar Configuration</v-card-title>
    <v-card-text>
      <v-form @submit.prevent="addSplitType">
        <v-select
          v-model="newSplitType"
          :items="['Football', 'Padel']"
          label="Select Split Type"
          placeholder="Choose a sport"
        ></v-select>
        <v-btn color="primary" @click="addSplitType">Add Split Type</v-btn>
      </v-form>

      <v-list>
        <v-list-item v-for="(split, index) in splitTypes" :key="index">
          <v-list-item-title>
            {{ split.type }}
            <v-btn icon @click="removeSplitType(index)">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </v-list-item-title>

          <!-- Terrain Form -->
          <v-form @submit.prevent="addTerrain(index)">
            <v-text-field
              v-model="newTerrains[index]"
              label="Add Terrain"
              placeholder="e.g., Terrain 1, Terrain 2"
            ></v-text-field>
            <v-btn color="secondary" @click="addTerrain(index)"
              >Add Terrain</v-btn
            >
          </v-form>

          <!-- Terrain List -->
          <v-list dense>
            <v-list-item
              v-for="(terrain, tIndex) in split.terrains"
              :key="tIndex"
            >
              <v-list-item-title>{{ terrain.name }}</v-list-item-title>
              <v-btn icon @click="removeTerrain(index, tIndex)">
                <v-icon>mdi-delete</v-icon>
              </v-btn>

              <!-- Price Range Form -->
              <v-form @submit.prevent="addPriceRange(index, tIndex)">
                <v-text-field
                  v-model="newPriceRange[index][tIndex].startTime"
                  label="Start Time"
                  placeholder="08:00"
                ></v-text-field>
                <v-text-field
                  v-model="newPriceRange[index][tIndex].endTime"
                  label="End Time"
                  placeholder="12:00"
                ></v-text-field>
                <v-text-field
                  v-model="newPriceRange[index][tIndex].price"
                  label="Price"
                  placeholder="e.g., 20$"
                ></v-text-field>
                <v-btn color="primary" @click="addPriceRange(index, tIndex)"
                  >Add Price Range</v-btn
                >
              </v-form>

              <!-- Price List -->
              <v-list dense>
                <v-list-item
                  v-for="(price, pIndex) in terrain.prices"
                  :key="pIndex"
                >
                  <v-list-item-title>
                    {{ price.startTime }} - {{ price.endTime }}:
                    {{ price.price }}
                  </v-list-item-title>
                  <v-btn icon @click="removePriceRange(index, tIndex, pIndex)">
                    <v-icon>mdi-delete</v-icon>
                  </v-btn>
                </v-list-item>
              </v-list>
            </v-list-item>
          </v-list>
        </v-list-item>
      </v-list>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { ref, computed } from "vue";
import { useStore } from "vuex";

const store = useStore();
const newSplitType = ref("");
const newTerrains = ref([]);
const newPriceRange = ref([]);
const splitTypes = computed(() => store.getters["calendarConfig/splitTypes"]);

// log split types from store
console.log(splitTypes.value);
console.log("in");
const addSplitType = () => {
  if (newSplitType.value) {
    store.commit("calendarConfig/ADD_SPLIT_TYPE", {
      type: newSplitType.value,
      terrains: [],
    });
    newSplitType.value = "";
    newTerrains.value.push("");
    newPriceRange.value.push([]);
  }
};

const removeSplitType = (index) => {
  store.commit("calendarConfig/REMOVE_SPLIT_TYPE", index);
  newTerrains.value.splice(index, 1);
  newPriceRange.value.splice(index, 1);
};

const addTerrain = (index) => {
  if (newTerrains.value[index]) {
    store.commit("calendarConfig/ADD_TERRAIN", {
      index,
      terrain: newTerrains.value[index],
    });
    newTerrains.value[index] = "";
    newPriceRange.value[index].push({ startTime: "", endTime: "", price: "" });
  }
};

const addPriceRange = (splitIndex, terrainIndex) => {
  const price = newPriceRange.value[splitIndex][terrainIndex];
  if (price.startTime && price.endTime && price.price) {
    store.commit("calendarConfig/ADD_PRICE_RANGE", {
      splitIndex,
      terrainIndex,
      priceRange: { ...price },
    });
    newPriceRange.value[splitIndex][terrainIndex] = {
      startTime: "",
      endTime: "",
      price: "",
    };
  }
};

const removeTerrain = (splitIndex, terrainIndex) => {
  store.commit("calendarConfig/REMOVE_TERRAIN", { splitIndex, terrainIndex });
  newPriceRange.value[splitIndex].splice(terrainIndex, 1);
};

const removePriceRange = (splitIndex, terrainIndex, priceIndex) => {
  store.commit("calendarConfig/REMOVE_PRICE_RANGE", {
    splitIndex,
    terrainIndex,
    priceIndex,
  });
};
</script>
