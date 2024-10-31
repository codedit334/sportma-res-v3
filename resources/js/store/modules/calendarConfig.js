// store/modules/calendarConfig.js
export default {
    namespaced: true,
    state: {
        splitTypes: [],
    },
    mutations: {
        SET_SPLIT_TYPES(state, splitTypes) {
            state.splitTypes = splitTypes;
        },
        ADD_SPLIT_TYPE(state, splitType) {
            let timeStep;
            let timeCellHeight;
            if (splitType.type.toLowerCase() === "football") {
                timeStep = 60;
                timeCellHeight = 80;
            } else if (splitType.type.toLowerCase() === "padel") {
                timeStep = 30;
                timeCellHeight = 50;
            }
            state.splitTypes.push({
                type: splitType.type,
                timeStep: timeStep,
                timeCellHeight: timeCellHeight,
                terrains: splitType.terrains,
            });
        },
        UPDATE_SPLIT_TYPE(state, { index, splitType, terrain, terrainID, prices }) {
            const currentSplitType = state.splitTypes[index];
            if (currentSplitType) {
                let timeStep, timeCellHeight;

                // Update timeStep and timeCellHeight based on type if needed
                if (splitType.toLowerCase() === "football") {
                    timeStep = 60;
                    timeCellHeight = 80;
                } else if (splitType.toLowerCase() === "padel") {
                    timeStep = 30;
                    timeCellHeight = 50;
                } else {
                    timeStep = currentSplitType.timeStep; // retain existing values
                    timeCellHeight = currentSplitType.timeCellHeight;
                }

                // Update the split type properties
                state.splitTypes.splice(index, 1, {
                    ...currentSplitType,
                    type: splitType,
                    timeStep: timeStep,
                    timeCellHeight: timeCellHeight,
                    terrains: [
                        ...currentSplitType.terrains.map((t) =>
                            t.terrainID === terrainID
                                ? { ...t, label: terrain, prices: [...prices] }
                                : t
                        ),
                    ],
                });
            }
        },

        REMOVE_SPLIT_TYPE(state, index) {
            state.splitTypes.splice(index, 1);
        },
        ADD_TERRAIN(state, { index, terrain }) {
            // Push an object with name and initialize prices as an empty array
            state.splitTypes[index].terrains.push({
                id: state.splitTypes[index].type + " " + Date.now(),
                label: terrain, // Assuming terrain is a string that represents the terrain name
                prices: [], // Initialize an empty prices array
            });
        },
        REMOVE_TERRAIN(state, { splitIndex, terrainIndex }) {
            state.splitTypes[splitIndex].terrains.splice(terrainIndex, 1);
        },
        ADD_PRICE_RANGE(state, { splitIndex, terrainIndex, priceRange }) {
            // Initialize prices array if it doesn't exist
            const terrain = state.splitTypes[splitIndex].terrains[terrainIndex];
            if (!terrain.prices) {
                terrain.prices = []; // Initialize if not already defined
            }

            terrain.prices.push(priceRange);
        },
        REMOVE_PRICE_RANGE(state, { splitIndex, terrainIndex, priceIndex }) {
            state.splitTypes[splitIndex].terrains[terrainIndex].prices.splice(
                priceIndex,
                1
            );
        },
    },
    actions: {
        updateSplitTypes({ commit }, splitTypes) {
            commit("SET_SPLIT_TYPES", splitTypes);
        },
    },
    getters: {
        splitTypes: (state) => state.splitTypes,
    },
};
