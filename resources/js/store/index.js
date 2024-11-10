import { createStore } from 'vuex';
import calendarConfig from './modules/calendarConfig';
import calendarModule from './modules/calendarModule';
import auth from './modules/auth';

const store = createStore({

  modules: {
    calendarConfig,
    calendar: calendarModule,
    auth
  },
});

export default store;
