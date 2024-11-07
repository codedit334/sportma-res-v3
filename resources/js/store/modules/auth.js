import axios from "axios";

const state = {
    isAuthenticated:
        JSON.parse(localStorage.getItem("isAuthenticated")) || false,
    userName: localStorage.getItem("userName") || null,
    isAdmin: JSON.parse(localStorage.getItem("isAdmin")) || false,
    permissions: JSON.parse(localStorage.getItem("permissions")) || [],
    tokenExpiration:
        JSON.parse(localStorage.getItem("tokenExpiration")) || null, // new
    refreshToken: localStorage.getItem("refreshToken") || null, // new
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
    SET_REFRESH_TOKEN(state, refreshToken) {
        state.refreshToken = refreshToken;
        localStorage.setItem("refreshToken", refreshToken);
    },
    SET_TOKEN_EXPIRATION(state, expiration) {
        state.tokenExpiration = expiration;
        localStorage.setItem("tokenExpiration", JSON.stringify(expiration));
    },
    CLEAR_TOKEN() {
        localStorage.removeItem("token");
        localStorage.removeItem("refreshToken");
        localStorage.removeItem("tokenExpiration");
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
                const {
                    token,
                    refreshToken,
                    name,
                    role,
                    permissions,
                    expiresIn,
                } = response.data;

                commit("SET_AUTHENTICATED", true);
                commit("SET_USER_NAME", name);
                commit("SET_IS_ADMIN", role.toLowerCase() === "admin");
                commit("SET_PERMISSIONS", permissions || []);
                commit("SET_TOKEN", token);
                commit("SET_REFRESH_TOKEN", refreshToken);

                const expirationTime = Date.now() + expiresIn * 1000;
                commit("SET_TOKEN_EXPIRATION", expirationTime);

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

    async refreshToken({ commit }) {
        try {
            const response = await axios.post("/api/refresh-token");
            const { access_token, expiresIn } = response.data; // Assuming expiresIn is returned from the backend

            // Commit the new access token to the store and set it as the default for Axios
            commit("SET_TOKEN", access_token);

            // Update the expiration time (assuming `expiresIn` is the number of seconds the token is valid)
            const expirationTime = Date.now() + expiresIn * 1000;
            commit("SET_TOKEN_EXPIRATION", expirationTime);

            console.log("Token refreshed");
        } catch (error) {
            console.error("Failed to refresh token:", error);
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
