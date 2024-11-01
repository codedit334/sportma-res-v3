<template>
    <div>
        <nav class="navbar">
            <div class="navbar-content">
                <img
                    src="https://images.pexels.com/photos/2453205/pexels-photo-2453205.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2"
                    class="logo"
                    alt="Sportma logo"
                />
            </div>
        </nav>
    </div>
</template>

<script setup>
// No additional script needed for a simple navbar.
</script>

<style lang="scss" scoped>
.navbar {
    position: relative;
    top: 0;
    left: 0;
    width: 100%;
    height: 60px;
    background-color: #d3d3d3; // Light grey background to match sidebar
    display: flex;
    align-items: center;
    justify-content: flex-end; // Align content to the right
    padding: 0 1rem;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
}

.logo {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%; // Makes the logo circular
    object-fit: cover; // Ensures the image fits within the circle
}
</style>
