// myStore.ts
import {createStore} from 'vuex';
import {handleServerError, handleSuccess, handleWarning, handleError} from "@/main";

export default {
    state: {
        token: null,
        user: null,
    },
    mutations: {
        // Định nghĩa mutations nếu cần thiết
        SET_TOKEN(state: any, token: any) {
            state.token = token;
        },
        SET_USER_TOKEN(state: any, payload: { token: any, user: any }) {
            state.token = payload.token;
            state.user = payload.user;
        }
    },
    actions: {
        setToken({commit}: { commit: Function }, token: any) {
            commit('SET_TOKEN', token)
        },
        setUserWithToken({commit}: { commit: Function }, {token, user}: { token: any, user: any }) {
            commit('SET_USER_TOKEN', {token, user});
        },
        checkToken(context: any, {commit}: { commit: Function }) {
            return context.getters.getToken
        }
    },
    getters: {
        getToken(state: any, {commit}: { commit: Function }) {
            return state.token;
        },
        getUser(state: any) {
            return state.user;
        }
    }
};
