import "./bootstrap";
import { createApp } from "vue";
import App from "./App.vue";
import store from "./store";

// Vuetify
import "vuetify/styles"; // Ensure you are using css-loader
import { createVuetify } from "vuetify";
import "@mdi/font/css/materialdesignicons.css";
import * as components from "vuetify/components";
import * as directives from "vuetify/directives";
import { VTimePicker } from "vuetify/labs/VTimePicker";
// Translations provided by Vuetify
import { pl, zhHans, fr } from "vuetify/locale";

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

store.dispatch("auth/initializeAuth");

import router from "./router";

createApp(App).use(store).use(router).use(vuetify).mount("#app");

