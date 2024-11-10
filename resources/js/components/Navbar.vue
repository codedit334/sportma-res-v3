<template>
    <nav class="navbar">
        <div class="navbar-content">
            <div class="auth-controls" v-if="user">
                <span v-if="isAuthenticated && user" class="username">{{ user.name }}</span>
                <button v-if="isAuthenticated" @click="logout" class="auth-button">
                    Se déconnecter
                </button>
                <button v-else @click="goToLogin" class="auth-button">
                    Se connecter
                </button>
                <router-link to="/profile">
                    <img
                        v-if="isAuthenticated && user"
                        :src="user.profile_picture ? '/storage/' + user.profile_picture : defaultUser"
                        class="logo"
                        alt="User profile picture"
                    />
                </router-link>
            </div>
        </div>
    </nav>
</template>


<script setup>
import { computed, onMounted, watch } from "vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import defaultUser from "../assets/user.png";

const store = useStore();
const router = useRouter();

const isAuthenticated = computed(() => store.getters["auth/isAuthenticated"]);

onMounted(() => {
    if (isAuthenticated.value) {
        store.dispatch("auth/fetchUserProfile");
    }
});

const user = computed(() => store.getters["auth/user"]) ;
console.log("user new", user.value);


watch(
    () => user.value,
    (newValue) => {
        console.log("User updated:", newValue);
    }
);

const logout = async () => {
    try {
        // Dispatch the logout action and wait for it to complete
        await store.dispatch("auth/logout");

        // After logout, redirect to the login page
        router.push("/login");
    } catch (error) {
        console.error("Logout error:", error);
    }
};

const goToLogin = () => {
    router.push("/login");
};
</script>

<style lang="scss" scoped>
.navbar {
    width: 100%;
    height: 60px;
    background-color: #d3d3d3;
    display: flex;
    align-items: center;
    justify-content: flex-end; /* Align everything to the right */
    padding: 0 1rem;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
}

.navbar-content {
    display: flex;
    align-items: center;
    gap: 1rem; /* Spacing between elements */
}

.auth-controls {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.logo {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    object-fit: cover;
}

.username {
    font-size: 1rem;
    color: #333;
}

.auth-button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    cursor: pointer;
}

.auth-button:hover {
    background-color: #0056b3;
}
</style>
