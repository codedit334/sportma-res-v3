import { createRouter, createWebHistory } from "vue-router";
import { useStore } from "vuex";

const routes = [
    {
        path: "/",
        component: () => import("../views/Dashboard.vue"),
        meta: { requiresAuth: true, permissions: ["Dashboard"] },
    },
    {
        path: "/login",
        component: () => import("../views/Auth/Login.vue"),
    },
    {
        path: "/calendar",
        component: () => import("../views/Calendar.vue"),
        meta: { requiresAuth: true, permissions: ["Calendrier"] },
    },
    {
        path: "/data",
        component: () => import("../views/Datatable.vue"),
        meta: { requiresAuth: true, permissions: ["Comptabilite"] },
    },
    {
        path: "/calendar/configuration",
        component: () => import("../views/Settings.vue"),
        meta: { requiresAuth: true, permissions: ["Configuration"] },
    },
    {
        path: "/users",
        component: () => import("../views/Users.vue"),
        meta: { requiresAuth: true, permissions: ["Staff"] },
    },
    {
        path: "/profile",
        component: () => import("../views/ProfilePage.vue"),
        meta: { requiresAuth: true },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Route guard for authentication and permissions

router.beforeEach(async (to, from, next) => {
    const store = useStore();
    const isAuthenticated = store.getters["auth/isAuthenticated"];
    let user = store.getters["auth/user"];
    // Fetch user profile if not already loaded
    if (isAuthenticated && !user) {
        await store.dispatch("auth/fetchUserProfile");
        user = store.getters["auth/user"]; // Re-fetch the user after dispatch
    }


    // Convert user to an object if needed
    if (typeof user === "string") {
        user = JSON.parse(user);
    }

    const isAdmin = store.getters["auth/isAdmin"];

    // Check if the route requires authentication
    if (to.meta.requiresAuth && !isAuthenticated) {
        next("/login"); // Redirect to login if not authenticated
    } else if (to.meta.requiresAuth && !isAdmin && to.path !== "/profile") {
        // If not admin, check permissions for routes other than "/profile"
        const hasPermission = to.meta.permissions?.some((permission) =>
            user.permissions.includes(permission)
        );
        if (!hasPermission) {
            next("/"); // Redirect if no permission
        } else {
            next(); // Allow access
        }
    } else {
        next(); // Allow access
    }
});


export default router;
