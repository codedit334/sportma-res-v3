<template>
    <div class="login-page">
        <h2>Login</h2>
        <input
            v-model="email"
            type="email"
            placeholder="Email"
            class="login-input"
        />
        <input
            v-model="password"
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
const email = ref("");
const password = ref("");
const errorMessage = ref("");

const login = async () => {
    try {
        // Dispatch login action to Vuex with email and password
        await store.dispatch("auth/login", {
            email: email.value,
            password: password.value,
        });

        // Check if login was successful, based on Vuex state
        if (store.getters["auth/isAuthenticated"]) {
            router.push("/"); // Redirect to home or any other page
        } else {
            errorMessage.value = "Invalid login credentials.";
        }
    } catch (error) {
        console.error("Login error:", error);
        errorMessage.value =
            "An error occurred during login. Please try again.";
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
