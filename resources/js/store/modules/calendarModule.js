
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
  },
  actions: {
    // Actions
  },
};

export default calendarModule;
