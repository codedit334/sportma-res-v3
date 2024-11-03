const state = {
    isAuthenticated: false,
    userName: null,
};

const mutations = {
    SET_AUTHENTICATED(state, status) {
        state.isAuthenticated = status;
    },
    SET_USER_NAME(state, name) {
        state.userName = name;
        console.log("Set_USER_NAME", state.userName);
    },
};

const actions = {
    login({ commit }, userName) {
        commit("SET_AUTHENTICATED", true);
        commit("SET_USER_NAME", userName);
        console.log("login", userName);
    },
    logout({ commit }) {
        commit("SET_AUTHENTICATED", false);
        commit("SET_USER_NAME", null);
    },
};

const getters = {
    isAuthenticated: (state) => state.isAuthenticated,
    userName: (state) => state.userName,
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters,
};
