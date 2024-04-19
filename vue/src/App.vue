<template>
    <router-view name="home"></router-view>
    <Toast />
</template>

<script>
import Toast from "primevue/toast";
import {getCsrfCookie, login} from "../api/user";
export default {
    components:{
        Toast
    },
    created() {
    }
}
</script>

<style lang="scss">
@import url('@public/scss/main.scss');
</style>
