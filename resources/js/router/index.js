import { createRouter, createWebHistory } from "vue-router";
import { useStore } from "vuex";

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: "/",
            component: () => import("../views/Dashboard.vue"),
        },
        {
            path: "/login",
            component: () => import("../views/Auth/Login.vue"),
        },
        {
            path: "/calender",
            component: () => import("../views/Calender.vue"),
        },
        {
            path: "/data",
            component: () => import("../views/Datatable.vue"),
        },
        {
            path: "/calender/configuration",
            component: () => import("../views/Settings.vue"),
        },
    ],
});

router.beforeEach((to, from, next) => {
    const store = useStore();
    const isAuthenticated = store.getters["auth/isAuthenticated"];

    if (to.path !== "/login" && !isAuthenticated) {
        next("/login");
    } else if (to.path === "/login" && isAuthenticated) {
        next("/"); // Redirect to home if already authenticated
    } else {
        next();
    }
});

export default router;
