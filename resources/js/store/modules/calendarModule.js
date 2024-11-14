import axios from "axios";

const calendarModule = {
    namespaced: true,
    state: () => ({
        events: [],
        unsavedEvents: [], // Events changed or added but not yet saved to the backend
        pendingUpdates: [],
        // make the overlap state has all the above states
        overLapState: [], // Temporary storage for modified events
    }),
    getters: {
        // Getter for events
        // getEvents(state) {
        //     return state.events || [];
        // },
        getEvents: (state) => {
            const seenIds = new Set();
            return [
                ...state.events,
                ...state.unsavedEvents,
                ...state.pendingUpdates,
            ].filter((event) => {
                if (seenIds.has(event.id)) {
                    return false; // Skip if the event id is already in the set
                }
                seenIds.add(event.id); // Add event id to the set
                return true; // Keep the event
            });
        },
    },
    mutations: {
        SET_EVENTS(state, events) {
            // Filter events with no id
            events = events.filter((event) => event.id);
            state.events = events;
        },
        // Add event mutation
        ADD_EVENT(state, event) {
            state.events.push(event);
        },
        // Remove event mutation
        REMOVE_EVENT(state, eventId) {
            state.events = state.events.filter((event) => event.id !== eventId);
        },
        // Update event mutation
        UPDATE_EVENT(state, updatedEvent) {
            const index = state.events.findIndex(
                (event) => event.id === updatedEvent.id
            );
            if (index !== -1) {
                state.events.splice(index, 1, updatedEvent);
            }
        },
        ADD_TO_UNSAVED_EVENTS(state, event) {
            state.unsavedEvents.push(event);
        },
        CLEAR_UNSAVED_EVENTS(state) {
            state.unsavedEvents = [];
        },
        ADD_PENDING_UPDATE(state, event) {
            // Avoid duplicates by checking if the event is already in pendingUpdates
            const existingIndex = state.pendingUpdates.findIndex(
                (e) => e.id === event.id
            );
            if (existingIndex === -1) {
                state.pendingUpdates.push(event);
            } else {
                // Update the existing pending event
                state.pendingUpdates[existingIndex] = event;
            }
        },
        CLEAR_PENDING_UPDATES(state) {
            state.pendingUpdates = [];
        },
    },
    actions: {
        // Fetch events from the API
        async fetchEvents({ state, commit }) {
            try {
                const response = await axios.get("/api/reservations"); // Update with correct API endpoint
                commit("SET_EVENTS", response.data);
            } catch (error) {
                console.error("Error fetching events:", error);
            }
        },
        // Add a new event through the API
        async addEvent({ commit }, eventData) {
            try {
                // Add to unsaved events instead of posting immediately
                commit("ADD_TO_UNSAVED_EVENTS", eventData);
            } catch (error) {
                console.error("Error adding event to unsaved list:", error);
            }
        },
        async saveAllEvents({ state, commit, dispatch }) {
            try {
                const response = await axios.post("/api/reservations/batch", {
                    reservations: state.unsavedEvents,
                }); // Update with correct endpoint
                // Clear unsaved events after successful batch save
                commit("CLEAR_UNSAVED_EVENTS");
                dispatch("fetchEvents");
                // Optionally update events in state if backend responds with updated data
                // commit("SET_EVENTS", response.data.reservations); // If backend returns the saved events
            } catch (error) {
                console.error("Error saving events:", error);
            }
        },
        // Update an event through the API
        async updateEvent({ commitx, dispatch }, eventData) {
            try {
                const response = await axios.put(
                    `/api/reservations/${eventData.id}`,
                    eventData
                ); // Update with correct API endpoint
                // commit("UPDATE_EVENT", response.data.reservation);
                // commit("UPDATE_EVENT", eventData);
                dispatch("fetchEvents");
            } catch (error) {
                console.error("Error updating event:", error);
            }
        },
        addPendingUpdate({ commit }, event) {
            commit("ADD_PENDING_UPDATE", event);
        },

        // Batch update action
        async batchUpdateEvents({ commit, state }) {
            if (state.pendingUpdates.length === 0) return;

            try {
                // Send the array of pending updates to the backend
                const response = await axios.put(
                    "/api/reservations/batch-update",
                    { events: state.pendingUpdates }
                );

                // Optionally update Vuex state with confirmed event data from backend if needed
                // commit('SET_EVENTS', response.data.events);

                // Clear pending updates after successful batch update
                commit("CLEAR_PENDING_UPDATES");
            } catch (error) {
                console.error("Error in batch updating events:", error);
                // Optional: handle error, e.g., retry logic or display an error message
            }
        },
        // Delete an event through the API
        async storeDeleteEvent({ commit }, eventId) {
            try {
              commit("REMOVE_EVENT", eventId);
                await axios.delete(`/api/reservations/${eventId}`); // Update with correct API endpoint
            } catch (error) {
                console.error("Error deleting event:", error);
            }
        },
    },
};

export default calendarModule;
