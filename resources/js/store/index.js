import { createStore } from 'vuex';
import calendarConfig from './modules/calendarConfig';
import calendarModule from './modules/calendarModule';

const store = createStore({

  state: {
    count: 0,
  },
  modules: {
    calendarConfig,
    calendar: calendarModule,
  },
  mutations: {
    increment(state) {
      state.count++;
    },
  },
  actions: {
    increment({ commit }) {
      commit('increment');
    },
  },
  getters: {
    getCount(state) {
      return state.count;
    },
  },
});

export default store;
