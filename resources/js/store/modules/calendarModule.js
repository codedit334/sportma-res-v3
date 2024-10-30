
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
      state.events = events;
      console.log(events);
    },
  },
  actions: {
    // Actions
  },
};

export default calendarModule;
