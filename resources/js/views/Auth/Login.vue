<template>
    <div class="login-page">
        <h2>Login</h2>
        <input
            v-model="userName"
            type="text"
            placeholder="Enter your name"
            class="login-input"
        />
        <button @click="login" class="login-button">Log In</button>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useStore } from "vuex";

const store = useStore();
const router = useRouter();
const userName = ref("");

const login = () => {
    if (userName.value.trim()) {
        store.dispatch("auth/login", userName.value); // Dispatch login action to Vuex
        router.push("/"); // Redirect to home or any other page
    }
};
</script>

<style scoped>
.login-page {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 5rem;
}

.login-input {
    padding: 0.5rem;
    font-size: 1rem;
    margin-bottom: 1rem;
}

.login-button {
    background-color: #007bff;
    color: white;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.login-button:hover {
    background-color: #0056b3;
}
</style>
