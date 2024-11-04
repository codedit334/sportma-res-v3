<template>
    <div>
        <aside :class="`${is_expanded && 'is_expanded'}`">
            <div class="logo">
                <img
                    src="https://sportma.ma/assets/sportmaApp-ERXWPjF0.jpeg"
                    class="logo vue"
                    alt="Vue logo"
                />
            </div>
            <div class="menu-toggle-wrap">
                <button class="menu-toggle" @click="ToggleMenu">
                    <span class="material-icons material-symbols-outlined">
                        keyboard_double_arrow_right
                    </span>
                </button>
            </div>
            <h3>Menu</h3>
            <div class="menu">
                <router-link v-if="hasPermission('Dashboard')" class="button" to="/">
                    <span class="material-icons material-symbols-outlined">insert_chart</span>
                    <span class="text">Dashboard</span>
                </router-link>
                <router-link v-if="hasPermission('Calendrier')" class="button" to="/calender">
                    <span class="material-icons material-symbols-outlined">calendar_today</span>
                    <span class="text">Calendrier</span>
                </router-link>
                <router-link class="button" to="/data">
                    <span class="material-icons material-symbols-outlined">table</span>
                    <span class="text">Comptabilité</span>
                </router-link>

                <!-- Conditional rendering based on permissions -->
                <router-link
                    v-if="hasPermission('Configuration')"
                    class="button"
                    to="/calender/configuration"
                >
                    <span class="material-icons material-symbols-outlined">settings</span>
                    <span class="text">Configuration</span>
                </router-link>
                <router-link
                    v-if="hasPermission('Staff')"
                    class="button"
                    to="/users"
                >
                    <span class="material-icons material-symbols-outlined">group</span>
                    <span class="text">Staff</span>
                </router-link>
            </div>
            <div class="flex"></div>
            <div class="menu"></div>
        </aside>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useStore } from "vuex"; // Import Vuex store

const is_expanded = ref(false);
const store = useStore();

const userName = computed(() => store.getters['auth/userName']);
const permissions = computed(() => store.getters['auth/permissions'] || []);
const isAdmin = computed(() => userName.value && userName.value.toLowerCase() === 'admin');

// Helper function to check if the user has a specific permission
const hasPermission = (permission) => {
    return permissions.value.includes(permission);
};

const ToggleMenu = () => {
    is_expanded.value = !is_expanded.value;
};
</script>


<style lang="scss" scoped>
aside {
    position: fixed;
    top: 0;
    z-index: 99;
    display: flex;
    flex-direction: column;
    width: calc(2rem + 32px);
    min-height: 100vh;
    overflow: hidden;
    padding: 1rem;
    background-color: #d3d3d3; // Light grey background
    color: #2e2e2e; // Dark grey for contrast
    transition: 0.2s ease-out;

    .logo {
        margin-bottom: 1rem;
        img {
            width: 2rem;
            filter: grayscale(100%);
        }
    }

    .flex {
        flex: 1 1 0;
    }

    .menu-toggle-wrap {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 1rem;
        position: relative;
        top: 0;
        transition: 0.2s ease-out;

        .menu-toggle {
            transition: 0.2s ease-out;

            .material-icons {
                font-size: 2rem;
                color: #5a5a5a; // Light grey for icon
                transition: 0.2s ease-out;
            }

            &:hover {
                .material-icons {
                    color: #7a7a7a; // Slightly darker grey on hover
                    transform: translateX(0.5rem);
                }
            }
        }
    }

    h3,
    .button .text {
        opacity: 0;
        transition: 0.3s ease-out;
    }

    h3 {
        color: #4f4f4f; // Medium grey for section titles
        font-size: 1rem;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
    }

    .menu {
        margin: 0 -1rem;

        .button {
            display: flex;
            align-items: center;
            text-decoration: none;
            padding: 0.5rem 1rem;
            transition: 0.2s ease-out;
            background-color: #e6e6e6; // Light grey for button background

            .material-icons {
                font-size: 2rem;
                color: #5a5a5a; // Light grey for icons
                transition: 0.2s ease-out;
                width: 35px;
            }

            .text {
                color: #2e2e2e; // Dark grey for text
                transition: 0.2s ease-out;
            }

            &:hover,
            &.router-link-exact-active {
                background-color: #c9c9c9; // Slightly darker grey on active and hover

                .material-icons,
                .text {
                    color: #3a3a3a; // Subtle change to dark grey on hover
                }
            }

            &.router-link-exact-active {
                border-right: 5px solid #bfbfbf; // Soft accent on active border
            }
        }
    }

    &.is_expanded {
        width: var(--sidebar-width);

        .menu-toggle-wrap {
            top: -3rem;

            .menu-toggle {
                transform: rotate(-180deg);
            }
        }

        h3,
        .button .text {
            opacity: 1;
        }

        .button {
            .material-icons {
                margin-right: 1rem;
                color: #5a5a5a; // Consistent icon color
            }
        }
    }

    @media (max-width: 768px) {
        position: fixed;
        z-index: 99;
    }
}
</style>
