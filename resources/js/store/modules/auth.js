import axios from "axios";

const state = {
    isAuthenticated: false,
    userName: null,
    isAdmin: false,
    permissions: [],
};

const mutations = {
    SET_AUTHENTICATED(state, status) {
        state.isAuthenticated = status;
    },
    SET_USER_NAME(state, name) {
        state.userName = name;
    },
    SET_IS_ADMIN(state, isAdmin) {
        state.isAdmin = isAdmin;
    },
    SET_PERMISSIONS(state, permissions) {
        state.permissions = permissions;
    },
};

const actions = {
    async login({ commit }, { email, password }) {
        try {
            const response = await axios.post("/api/login", {
                email,
                password,
            });
            if (response.data.success) {
                const { name, role, permissions } = response.data;

                commit("SET_AUTHENTICATED", true);
                commit("SET_USER_NAME", name);
                commit("SET_IS_ADMIN", role.toLowerCase() === "admin"); // Set isAdmin if the role is "Admin"
                commit("SET_PERMISSIONS", permissions || []); // Store permissions array

                console.log("User logged in:", name);
            } else {
                console.error("Login failed:", response.data.message);
            }
        } catch (error) {
            console.error("Error during login:", error);
        }
    },

    async logout({ commit }) {
        try {
            const response = await axios.post("/api/logout");
            if (response.data.success) {
                commit("SET_AUTHENTICATED", false);
                commit("SET_USER_NAME", null);
                commit("SET_IS_ADMIN", false);
                commit("SET_PERMISSIONS", []);
                console.log("User logged out");
            } else {
                console.error("Logout failed:", response.data.message);
            }
        } catch (error) {
            console.error("Error during logout:", error);
        }
    },
};

const getters = {
    isAuthenticated: (state) => state.isAuthenticated,
    userName: (state) => state.userName,
    isAdmin: (state) => state.isAdmin,
    permissions: (state) => state.permissions,
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters,
};
