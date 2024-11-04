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
        component: () => import("../views/calendar.vue"),
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
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Route guard for authentication and permissions
router.beforeEach((to, from, next) => {
    const store = useStore();
    const isAuthenticated = store.getters["auth/isAuthenticated"];
    const userPermissions = store.getters["auth/permissions"]; // Adjust according to your Vuex store structure

    // Check if the route requires authentication
    if (to.meta.requiresAuth && !isAuthenticated) {
        next("/login"); // Redirect to login if not authenticated
    } else if (to.meta.requiresAuth) {
        // Check if the user has the required permissions for the route
        const hasPermission = to.meta.permissions.some((permission) =>
            userPermissions.includes(permission)
        );
        if (!hasPermission) {
            next("/"); // Redirect to home or another designated page if no permission
        } else {
            next(); // Allow access to the route
        }
    } else {
        next(); // Allow access to the route
    }
});

export default router;
