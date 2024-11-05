import axios from "axios";

const state = {
    isAuthenticated:
        JSON.parse(localStorage.getItem("isAuthenticated")) || false,
    userName: localStorage.getItem("userName") || null,
    isAdmin: JSON.parse(localStorage.getItem("isAdmin")) || false,
    permissions: JSON.parse(localStorage.getItem("permissions")) || [],
};

const mutations = {
    SET_AUTHENTICATED(state, status) {
        state.isAuthenticated = status;
        localStorage.setItem("isAuthenticated", status);
    },
    SET_USER_NAME(state, name) {
        state.userName = name;
        localStorage.setItem("userName", name);
    },
    SET_IS_ADMIN(state, isAdmin) {
        state.isAdmin = isAdmin;
        localStorage.setItem("isAdmin", isAdmin);
    },
    SET_PERMISSIONS(state, permissions) {
        state.permissions = permissions;
        localStorage.setItem("permissions", JSON.stringify(permissions));
    },
    SET_TOKEN(state, token) {
        localStorage.setItem("token", token);
        axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    },
    CLEAR_TOKEN() {
        localStorage.removeItem("token");
        delete axios.defaults.headers.common["Authorization"];
    },
};

const actions = {
    async login({ commit }, { email, password }) {
        try {
            const response = await axios.post("/api/login", {
                email,
                password,
            });
            console.log("Login response:", response.data);

            if (response.data.token) {
                const { token, name, role, permissions } = response.data;

                commit("SET_AUTHENTICATED", true);
                commit("SET_USER_NAME", name);
                commit("SET_IS_ADMIN", role.toLowerCase() === "admin");
                commit("SET_PERMISSIONS", permissions || []);
                commit("SET_TOKEN", token);

                console.log("User logged in:", name);
            } else {
                console.error("Login failed:", response.data.message);
                throw new Error(response.data.message);
            }
        } catch (error) {
            commit("SET_AUTHENTICATED", false);
            console.error("Error during login:", error);
            throw new Error(
                error.response?.data?.error || "Login failed. Please try again."
            );
        }
    },

    async logout({ commit }) {
        try {
            const response = await axios.post("/api/logout");
            console.log(response.data);

            if (response.data.success) {
                commit("SET_AUTHENTICATED", false);
                commit("SET_USER_NAME", null);
                commit("SET_IS_ADMIN", false);
                commit("SET_PERMISSIONS", []);
                commit("CLEAR_TOKEN");

                localStorage.removeItem("isAuthenticated");
                localStorage.removeItem("userName");
                localStorage.removeItem("isAdmin");
                localStorage.removeItem("permissions");

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
