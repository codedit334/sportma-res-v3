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
const token = localStorage.getItem("token");
if (token) {
    axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    store.commit("auth/SET_AUTHENTICATED", true);
}

// Check token expiration and refresh when necessary
setInterval(() => {
    const tokenExpiration = store.getters["auth/tokenExpiration"];
    const timeToExpire = tokenExpiration - Date.now();

    console.log("Time to token expiration:", timeToExpire);

    // Refresh the token 5 minutes before it expires
    if (timeToExpire < 300000 && timeToExpire > 0) {
        store.dispatch("auth/refreshToken");
    }else if(timeToExpire < 0) store.dispatch("auth/logout");
}, 60000); // Check every minute

import router from "./router";

createApp(App).use(store).use(router).use(vuetify).mount("#app");
