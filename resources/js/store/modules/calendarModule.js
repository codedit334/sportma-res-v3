import axios from 'axios';

const calendarModule = {
  namespaced: true,
  state: () => ({
    events: [],
  }),
  getters: {
    // Getter for events
    getEvents(state) {
      return state.events || [];
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
      const index = state.events.findIndex(event => event.id === updatedEvent.id);
      if (index !== -1) {
        state.events.splice(index, 1, updatedEvent);
      }
    },
  },
  actions: {
    // Fetch events from the API
    async fetchEvents({ commit }) {
      try {
        const response = await axios.get('/api/reservations');  // Update with correct API endpoint
        commit('SET_EVENTS', response.data.reservations);
      } catch (error) {
        console.error('Error fetching events:', error);
      }
    },
    // Add a new event through the API
    async addEvent({ commit }, eventData) {
      try {
        const response = await axios.post('/api/reservations', eventData); // Update with correct API endpoint
        commit('ADD_EVENT', response.data.reservation);
      } catch (error) {
        console.error('Error adding event:', error);
      }
    },
    // Update an event through the API
    async updateEvent({ commit }, eventData) {
      try {
        const response = await axios.put(`/api/reservations/${eventData.id}`, eventData); // Update with correct API endpoint
        commit('UPDATE_EVENT', response.data.reservation);
      } catch (error) {
        console.error('Error updating event:', error);
      }
    },
    // Delete an event through the API
    async deleteEvent({ commit }, eventId) {
      try {
        await axios.delete(`/api/reservations/${eventId}`); // Update with correct API endpoint
        commit('REMOVE_EVENT', eventId);
      } catch (error) {
        console.error('Error deleting event:', error);
      }
    },
  },
};

export default calendarModule;
