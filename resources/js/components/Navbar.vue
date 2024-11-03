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
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const isLoggedIn = ref(false);
const userName = ref("");

// Check localStorage on component mount to set the login state
onMounted(() => {
    const storedUser = localStorage.getItem("user");
    if (storedUser) {
        const user = JSON.parse(storedUser);
        isLoggedIn.value = true;
        userName.value = user.name;
    }
});

// Handle login or logout action
const handleAuthAction = () => {
    if (isLoggedIn.value) {
        // Log out
        localStorage.removeItem("user");
        isLoggedIn.value = false;
        userName.value = "";
    } else {
        // Redirect to login page
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
