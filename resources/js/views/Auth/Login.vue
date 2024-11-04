<template>
    <div class="login-page">
        <h2>Login</h2>
        <input
            v-model="userName"
            type="text"
            placeholder="(user or admin)"
            class="login-input"
        />
        <input
            type="password"
            placeholder="Password"
            class="login-input"
        />
        <button @click="login" class="login-button">Log In</button>
        <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useStore } from "vuex";

const store = useStore();
const router = useRouter();
const userName = ref("");
const errorMessage = ref("");

const login = () => {
    const normalizedUser = userName.value.trim().toLowerCase();

    if (normalizedUser === "user" || normalizedUser === "admin") {
        // Dispatch login action to Vuex
        store.dispatch("auth/login", userName.value);
        router.push("/"); // Redirect to home or any other page
    } else {
        errorMessage.value = "Invalid login. Please enter 'user' or 'admin'.";
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
    border: 1px solid #ccc;
    border-radius: 5px;
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

.error-message {
    color: red;
    margin-top: 0.5rem;
}
</style>
