<template>
    <div>
        <nav class="navbar">
            <div class="navbar-content">
                <img
                    src="https://sportma.ma/assets/sportmaApp-ERXWPjF0.jpeg"
                    class="logo"
                    alt="Sportma logo"
                />
                <h1 class="navbar-title">Sportma Dashboard</h1>
                <div class="nav-links">
                    <router-link to="/" class="nav-link">Home</router-link>
                    <router-link to="/profile" class="nav-link"
                        >Profile</router-link
                    >
                    <router-link to="/settings" class="nav-link"
                        >Settings</router-link
                    >
                </div>
            </div>
        </nav>
    </div>
</template>

<script setup>
// No additional script needed for a simple navbar.
</script>

<style lang="scss" scoped>
.navbar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 60px;
    background-color: #d3d3d3; // Same light grey as the sidebar
    color: #2e2e2e; // Dark grey for text
    display: flex;
    align-items: center;
    padding: 0 1rem;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
    z-index: 100;

    .navbar-content {
        display: flex;
        align-items: center;
        width: 100%;
        justify-content: space-between;
    }

    .logo {
        width: 2.5rem;
        margin-right: 1rem;
        filter: grayscale(100%);
    }

    .navbar-title {
        font-size: 1.25rem;
        color: #4f4f4f; // Matching sidebar section titles
    }

    .nav-links {
        display: flex;
        gap: 1rem;

        .nav-link {
            color: #2e2e2e; // Dark grey for nav links
            text-decoration: none;
            font-size: 1rem;
            padding: 0.5rem;
            transition: color 0.2s ease-out;

            &:hover {
                color: #3a3a3a; // Slightly darker grey on hover
            }

            &.router-link-exact-active {
                font-weight: bold;
                border-bottom: 2px solid #bfbfbf; // Soft accent line for active link
            }
        }
    }
}

@media (max-width: 768px) {
    .navbar {
        flex-direction: column;
        height: auto;
    }

    .nav-links {
        flex-direction: row;
        gap: 0.5rem;
    }
}
</style>
