<template>
    <div>
        <nav class="navbar">
            <div class="navbar-content">
                <img
                    src="https://images.pexels.com/photos/2453205/pexels-photo-2453205.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2"
                    class="logo"
                    alt="Sportma logo"
                />
                <div class="right-section">
                    <span v-if="isLoggedIn" class="username"
                        >Bonjour, {{ userName }}!</span
                    >
                    <button @click="handleAuthAction" class="auth-button">
                        {{ isLoggedIn ? "Se déconnecter" : "Se connecter" }}
                    </button>
                </div>
            </div>
        </nav>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";

const store = useStore();
const router = useRouter();

const isLoggedIn = computed(() => store.getters["auth/isLoggedIn"]);
const userName = computed(() => store.getters["auth/userName"]);

const handleAuthAction = () => {
    if (isLoggedIn.value) {
        store.dispatch("auth/logout");
    } else {
        router.push("/login");
    }
};
</script>

<style lang="scss" scoped>
.navbar {
    position: relative;
    top: 0;
    left: 0;
    width: 100%;
    height: 60px;
    background-color: #d3d3d3;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 1rem;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
}

.logo {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    object-fit: cover;
}

.navbar-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
}

.right-section {
    display: flex;
    align-items: center;
    gap: 1rem;
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
