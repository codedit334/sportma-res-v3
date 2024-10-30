import { createRouter, createWebHistory } from "vue-router";

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: "/",
      component: () => import("../views/Dashboard.vue"),
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

export default router;
