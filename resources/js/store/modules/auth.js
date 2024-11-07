import axios from "axios";

const state = {
    isAuthenticated:
        JSON.parse(localStorage.getItem("isAuthenticated")) || false,
    isAdmin: JSON.parse(localStorage.getItem("isAdmin")) || false,
    tokenExpiration:
        JSON.parse(localStorage.getItem("tokenExpiration")) || null, // new
    refreshToken: localStorage.getItem("refreshToken") || null, // new
    user: localStorage.getItem("user") || [],
};

const mutations = {
    SET_AUTHENTICATED(state, status) {
        state.isAuthenticated = status;
        localStorage.setItem("isAuthenticated", status);
    },
    SET_USER_PROFILE(state, userData) {
        state.user = userData;
        localStorage.setItem("user", JSON.stringify(userData));
    },
    CLEAR_USER_PROFILE(state) {
        state.user = null;
        localStorage.removeItem("user");
    },
    SET_IS_ADMIN(state, isAdmin) {
        state.isAdmin = isAdmin;
        localStorage.setItem("isAdmin", isAdmin);
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
    async login({ commit, dispatch }, { email, password }) {
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
                    profile_picture,
                    permissions,
                    expiresIn,
                } = response.data;

                commit("SET_AUTHENTICATED", true);
                commit("SET_IS_ADMIN", role.toLowerCase() === "admin");
                commit("SET_TOKEN", token);
                commit("SET_REFRESH_TOKEN", refreshToken);
                commit("SET_USER_PROFILE", response.data);
                
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

    async fetchUserProfile({ commit }) {
        try {
            const response = await axios.get("/api/user/profile");
            let userData = response.data;

            // If permissions are stored as a string, parse it into an array
            if (typeof userData.permissions === "string") {
                userData.permissions = JSON.parse(userData.permissions);
            }

            commit("SET_USER_PROFILE", userData);
        } catch (error) {
            console.error("Error fetching user profile:", error);
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
                commit("SET_IS_ADMIN", false);
                commit("CLEAR_TOKEN");
                commit("CLEAR_USER_PROFILE");

                localStorage.removeItem("isAuthenticated");
                localStorage.removeItem("isAdmin");

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
    isAdmin: (state) => state.isAdmin,
    user: (state) => state.user,
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters,
};
