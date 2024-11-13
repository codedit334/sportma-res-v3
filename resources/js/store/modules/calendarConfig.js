import axios from "axios";

export default {
    namespaced: true,
    state: {
        splitTypes: [], // Remove localStorage reference; initialize as empty array
    },
    mutations: {
        SET_SPLIT_TYPES(state, splitTypes) {
            state.splitTypes = splitTypes; // No localStorage update here
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
            }
        },
        REMOVE_SPLIT_TYPE(state, index) {
            // state.splitTypes.splice(index, 1); // No localStorage update
        },
    },
    actions: {
        async fetchCalendarConfig({ commit }, companyId) {
            try {
                console.log(companyId);
                console.log("III")
                const response = await axios.get(`/api/sports/${companyId}`);
                if (response.data) {
                    console.log("Sports config",response.data);
                    commit("SET_SPLIT_TYPES", response.data);
                }
            } catch (error) {
                console.error("Error fetching calendar config", error);
            }
        },
        // async saveCalendarConfig({ state }, companyId) {
        //     try {
        //         await axios.put(`/api/sports/`, {
        //             company_id: companyId,
        //             configurations: state.splitTypes,
        //         });
        //         console.log("Calendar config saved successfully");
        //     } catch (error) {
        //         console.error("Error saving calendar config", error);
        //     }
        // },
        async addSplitType({ dispatch }, { companyId, splitType }) {
            try {
                const response = await axios.post(`/api/sports`, {
                    company_id: companyId,
                    type: splitType.type, // Pass the new split type as an array of configurations
                    terrains: splitType.terrains, // Pass the new split type as an array of configurations
                });
        
                // Fetch updated configurations after the creation
                dispatch("fetchCalendarConfig", companyId);
        
                console.log("Calendar config created successfully");
            } catch (error) {
                console.error("Error adding split type", error);
            }
        },
        async updateSplitType({ dispatch }, { id, type, terrains, companyId }) {
            try {
                await axios.put(`/api/sports/${id}`, {
                    type: type, // Pass the new split type as an array of configurations
                    terrains: terrains, // Pass the new split type as an array of configurations
                });
                dispatch("fetchCalendarConfig", companyId); // Refresh data from backend after update
            } catch (error) {
                console.error("Error updating split type", error);
            }
        },
        async removeSplitType({ dispatch }, {id, companyId }) {
            try {
                await axios.delete(`/api/sports/${id}`);
                dispatch("fetchCalendarConfig", companyId); // Refresh data from backend after deletion
            } catch (error) {
                console.error("Error removing split type", error);
            }
        },
    },
    getters: {
        splitTypes: (state) => state.splitTypes,
    },
};
