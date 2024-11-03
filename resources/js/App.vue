<template>
    <div class="app">
        <navbar />
        <sidebar v-if="isAuthenticated" />

        <div
            class="app-content"
            style="position: relative; left: calc(2rem + 32px)"
        >
            <router-view />
        </div>
    </div>
</template>

<script setup>
import Sidebar from "./components/Sidebar.vue";
import Navbar from "./components/Navbar.vue";
import { useStore } from "vuex";
import { computed } from "vue";

const store = useStore();
const isAuthenticated = computed(() => store.getters["auth/isAuthenticated"]);

</script>

<style lang="scss">
:root {
    --light: #f7f7f7;
    --sidebar-width: 300px;
}
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Fira sans", sans-serif;
}
body {
    background: var(--light);
}
button {
    cursor: pointer;
    appearance: none;
    border: none;
    outline: none;
    background: none;
}

.app-content {
    width: calc(100% - 2rem - 72px);
}

.app {
    main {
        flex: 1 1 0;
        padding: 2rem;
        @media (max-width: 1024px) {
            padding-left: 6rem;
        }
    }
}
</style>
