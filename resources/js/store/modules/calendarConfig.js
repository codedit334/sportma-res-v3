import axios from "axios";

export default {
    namespaced: true,
    state: {
        splitTypes: JSON.parse(localStorage.getItem("splitTypes")) || [],
    },
    mutations: {
        SET_SPLIT_TYPES(state, splitTypes) {
            state.splitTypes = splitTypes;
            localStorage.setItem("splitTypes", JSON.stringify(splitTypes)); // Save to localStorage
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
            localStorage.setItem(
                "splitTypes",
                JSON.stringify(state.splitTypes)
            ); // Update localStorage
        },
        UPDATE_SPLIT_TYPE(
            state,
            { index, splitType, terrain, terrainID, prices }
        ) {
            const currentSplitType = state.splitTypes[index];
            if (currentSplitType) {
                let timeStep, timeCellHeight;

                if (splitType.toLowerCase() === "football") {
                    timeStep = 60;
                    timeCellHeight = 80;
                } else if (splitType.toLowerCase() === "padel") {
                    timeStep = 30;
                    timeCellHeight = 50;
                } else {
                    timeStep = currentSplitType.timeStep;
                    timeCellHeight = currentSplitType.timeCellHeight;
                }

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
                localStorage.setItem(
                    "splitTypes",
                    JSON.stringify(state.splitTypes)
                ); // Update localStorage
            }
        },
        REMOVE_SPLIT_TYPE(state, index) {
            state.splitTypes.splice(index, 1);
            localStorage.setItem(
                "splitTypes",
                JSON.stringify(state.splitTypes)
            ); // Update localStorage
        },
        ADD_TERRAIN(state, { index, terrain }) {
            state.splitTypes[index].terrains.push({
                id: state.splitTypes[index].type + " " + Date.now(),
                label: terrain,
                prices: [],
            });
            localStorage.setItem(
                "splitTypes",
                JSON.stringify(state.splitTypes)
            ); // Update localStorage
        },
        REMOVE_TERRAIN(state, { splitIndex, terrainIndex }) {
            state.splitTypes[splitIndex].terrains.splice(terrainIndex, 1);
            localStorage.setItem(
                "splitTypes",
                JSON.stringify(state.splitTypes)
            ); // Update localStorage
        },
        ADD_PRICE_RANGE(state, { splitIndex, terrainIndex, priceRange }) {
            const terrain = state.splitTypes[splitIndex].terrains[terrainIndex];
            if (!terrain.prices) {
                terrain.prices = [];
            }
            terrain.prices.push(priceRange);
            localStorage.setItem(
                "splitTypes",
                JSON.stringify(state.splitTypes)
            ); // Update localStorage
        },
        REMOVE_PRICE_RANGE(state, { splitIndex, terrainIndex, priceIndex }) {
            state.splitTypes[splitIndex].terrains[terrainIndex].prices.splice(
                priceIndex,
                1
            );
            localStorage.setItem(
                "splitTypes",
                JSON.stringify(state.splitTypes)
            ); // Update localStorage
        },
    },
    actions: {
        async fetchCalendarConfig({ commit }, companyId) {
            try {
                const response = await axios.get(
                    `/api/calendar-configs/${companyId}`
                );
                if (response.data && response.data.configurations) {
                    commit("SET_SPLIT_TYPES", response.data.configurations);
                }
            } catch (error) {
                console.error("Error fetching calendar config", error);
            }
        },
        async saveCalendarConfig({ state }, companyId) {
            try {
                const response = await axios.put(
                    `/api/calendar-configs/${companyId}`,
                    {
                        configurations: state.splitTypes,
                    }
                );
                console.log("Calendar config saved:", response.data);
            } catch (error) {
                console.error("Error saving calendar config", error);
            }
        },
        updateSplitTypes({ commit }, splitTypes) {
            commit("SET_SPLIT_TYPES", splitTypes);
        },
    },
    getters: {
        splitTypes: (state) => state.splitTypes,
    },
};
