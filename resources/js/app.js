import "./bootstrap";
import { createApp } from "vue";
import App from "./App.vue";
import store from "./store";

// Axios
import axios from "axios";

// Vuetify
import "vuetify/styles"; // Ensure you are using css-loader
import { createVuetify } from "vuetify";
import "@mdi/font/css/materialdesignicons.css";
import * as components from "vuetify/components";
import * as directives from "vuetify/directives";
import { VTimePicker } from "vuetify/labs/VTimePicker";
// Translations provided by Vuetify
import { fr } from "vuetify/locale";

export default createVuetify({
    components: {
        VTimePicker,
    },
});

const vuetify = createVuetify({
    components,
    directives,
    locale: {
        locale: "fr",
        messages: { fr },
    },
});

// Set the default Authorization header if a token exists
const token = localStorage.getItem('token');
if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    store.commit('auth/SET_AUTHENTICATED', true);
}

setInterval(() => {
    const tokenExpiration = store.state.auth.tokenExpiration;
    const timeToExpire = tokenExpiration - Date.now();

    console.log("Time to token expiration:", timeToExpire);

    if (timeToExpire < 300000 && timeToExpire > 0) { // refresh 5 minutes before expiration
        store.dispatch("auth/refreshToken");
    }
}, 60000); // check every minute

import router from "./router";

createApp(App).use(store).use(router).use(vuetify).mount("#app");

