export default {
    namespaced: true,
    state: {
        isLoggedIn: false,
        userName: "",
    },
    mutations: {
        SET_USER(state, name) {
            state.isLoggedIn = true;
            state.userName = name;
        },
        CLEAR_USER(state) {
            state.isLoggedIn = false;
            state.userName = "";
        },
    },
    actions: {
        login({ commit }, name) {
            commit("SET_USER", name);
            localStorage.setItem("user", JSON.stringify({ name }));
        },
        logout({ commit }) {
            commit("CLEAR_USER");
            localStorage.removeItem("user");
        },
        initializeAuth({ commit }) {
            const savedUser = localStorage.getItem("user");
            if (savedUser) {
                const user = JSON.parse(savedUser);
                commit("SET_USER", user.name);
            }
        },
    },
    getters: {
        isLoggedIn: (state) => state.isLoggedIn,
        userName: (state) => state.userName,
    },
};
