<template>
    <div class="bg-image position-relative" v-if="!loggedUser"></div>
    <div class="box box-login flex-row" v-if="!loggedUser">
        <div class="notification">
            <div class="toast-wrong-account flex-row" v-if="invalid['error']">
                <div class="text-wrong-account">Tên đăng nhập hoặc mật khẩu không đúng.</div>
            </div>
        </div>
        <div class="box-img-left">
            <img src="@public/assets/icons/welcome.png" alt="">
        </div>
        <div class="box-body flex-column">
            <div class="login-form">
                <div class="bg-title">
                    <img src="@public/assets/icons/logo-it.webp" alt="" class="title">
                </div>
                <form action="" @submit.prevent="doLogin">
                    <div class="content">
                        <div class="form-group validate-input">
                            <label class="top-label d-flex subtitle" style="width:100%;">
                                <span style="font-weight: lighter">Đăng nhập để làm việc với<b
                                    style="margin-left: 5px">Office
                                        Garder</b>
                                </span>
                            </label>
                            <div>
                                <InputText v-model.trim="user.email" class="form-control input"
                                           placeholder="Địa chỉ email"
                                           @keypress="validateSpace"/>
                            </div>
                            <div class="error-text d-flex flex-start" v-if="invalid['email']">
                                <span class="mi-icon24"></span>
                                <span style="padding-left: 2px;" class="text-start">{{ invalid['email'] }}</span>
                            </div>
                        </div>
                        <div class="form-group validate-input">
                            <div class="flex-column">
                                <label style="width:100%; padding-right: 0; position: relative">
                                    <InputText v-model="user.password" :type="isShowPassword ? 'text' : 'password'"
                                               class="form-control input" placeholder="Mật khẩu"/>
                                    <div id="togglepassword" class="eye" @click="isShowPassword = !isShowPassword"
                                         :class="{ 'eye-slash': isShowPassword }"></div>
                                </label>
                                <div class=" flex-row align-items-center" v-if="invalid['password']">
                                    <div style="padding-top: 2px;" class="icon-error-text"></div>
                                    <div style="padding-left: 2px; margin-top: 0;" class="error-text">{{
                                            invalid['password']
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <Button type="submit" label="Đăng nhập"
                                class="d-flex align-items-center btn btn-primary btn-login text" @click="doLogin"
                                @keyup.enter="doLogin"/>
                    </div>
                </form>
                <div class="footer-login flex-center">
                    <button class="btn btn-link text">Quên mật khẩu</button>
                </div>
            </div>
        </div>
        <div class="copy-right">
            <div class="copy-right-text">Copyright © 2023 - 2024 K70 CNTT</div>
        </div>
    </div>
    <TheLoading v-if="isLoading"/>
</template>

<script>
import {login} from '/api/user'
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import TheLoading from '../components/LoadingProgress.vue';
import {mapState, mapActions} from 'vuex'
import Auth from "../../api/utils/auth";
import {RESPONSE_STATUS} from "@/common/enums";

export default {
    components: {
        Button,
        InputText,
        TheLoading,
    },
    emits: ["setLoggedUser"],
    data() {
        return {
            isShowPassword: false,

            invalid: [],
            loggedUser: false,
            isLoading: false,
            user: {
                email: null,
                password: null,
            }
        }
    },
    methods: {
        ...mapActions('user', ['saveUser']),

        doLogin(event) {
            event.preventDefault();
            this.invalid = [];
            if (this.user.email == null) {
                this.invalid['email'] = 'Email không được để trống';
            }

            if (!this.validateemail()) {
                this.invalid['email'] = 'Email không đúng định dạng';
            }
            if (this.user.password == null || String(this.user.password).trim() == '') {
                this.invalid['password'] = 'Mật khẩu không được để trống';
            }
            //xảy ra lỗi
            if (Object.keys(this.invalid).length > 0) return;

            this.isLoading = true;
            login(this.user).then(async res => {
                try {
                    Auth.login(res.data.token, res.data.user);
                    this.$router.push({path: '/departments'});
                } catch (error) {
                    console.log(error)
                }
                // Auth.login(res.access_token, res.user); //set local storage
            }).catch(error => {
                console.log(error)
                if (error.response && error.response.status === RESPONSE_STATUS.HTTP_UNPROCESSABLE_ENTITY) {
                    this.invalid['error'] = error.response.data.data.errors;
                }
            }).finally(() => {
                setTimeout(() => {
                    this.isLoading = false;
                }, 750);
            })
        },

        /**
         * Validate email
         */
        validateemail() {
            if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(this.user.email) && this.user.email != null && String(this.user.email).trim() != '') {
                return true;
            }
            return false;
        },

        /**
         * Không cho nhập khoảng trắng
         * @param {*} event
         */
        validateSpace(event) {
            if (event.keyCode === 32) {
                event.preventDefault();
            }
        },

        handleKeyDown(event) {
            if (event.keyCode === 13) {
                this.doLogin(event);
            }
        }
    },

    created() {
        document.addEventListener('keydown', this.handleKeyDown);
    },

    beforeDestroy() {
        document.removeEventListener('keydown', this.handleKeyDown);
    },
    beforeCreate() {

    }

}
</script>

<style scoped>
.bg-image {
    background-image: url('@public/assets/icons/bg_login.webp');
    height: 100%;
    width: 100%;
    min-height: 100vh;
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
}

.box.box-login {
    display: flex;
    height: 498px;
    width: 768px;
    background-color: #ffffff;
    color: #888888;
    border-radius: 14px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 2;
    background: url('@public/assets/icons/bg.jpg') no-repeat;
    background-size: cover;
    background-position: center;
    text-align: center;
}

.box.box-login .box-img-left img {
    width: 80%;
    height: auto;
}

.box.box-login .box-img-left {
    width: 50%;
    height: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    border-top-left-radius: 14px;
    border-bottom-left-radius: 15px;
    background-size: cover;
    background-position: center;
}

.box-body {
    width: 50%;
    height: auto;
    padding: 57px 33px 12px 33px;
    box-shadow: 0 0 0 rgb(0 0 0 / 8%);
}

.toast-wrong-account {
    width: 345px;
    height: 23px;
    margin: 0;
    top: -30px;
    left: 50%;
    background-color: #f65335;
    position: absolute;
    text-align: center;
    color: white;
    transform: translate(-50%, -50%);
    align-items: center;
    font-weight: 500;
    border-radius: 5px;
}

.toast-wrong-account .text-wrong-account {
    margin-left: 16px;
}

.box-body .title {
    background-image: url('@public/assets/icons/logo-it.webp');
    background-repeat: no-repeat;
    height: 60px;
    width: 60px;
}

.box-body .bg-title {
    margin: 0px 0px 40px 0;
}

.btn-login:hover {
    transition-duration: 0.4s;
}

label {
    display: inline-block;
    margin-bottom: 0.5rem;
}

.eye {
    content: "";
    float: right;
    height: 24px;
    width: 24px;
    position: absolute;
    background-image: url('@public/assets/icons/ic_view_eye.svg');
    cursor: pointer;
    right: 20px;
    top: 8px;
}

.eye-slash {
    content: "";
    float: right;
    height: 24px;
    width: 24px;
    position: absolute;
    background-image: url('@public/assets/icons/ic_hide_eye.svg');
    cursor: pointer;
    right: 20px;
    top: 8px;
}

.copy-right-text {
    width: 345px !important;
    height: 23px !important;
    color: #666666 !important;
    transform: translate(-50%, -50%) !important;
    font-weight: lighter !important;
    position: absolute;
    margin: 0;
    top: calc(100% + 25px);
    left: 50%;
}

.footer-login {
    margin-bottom: 0 !important;
    margin-top: auto !important;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label.top-label {
    padding: 0;
    color: #666;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 8px;
}

.box-body .subtitle {
    margin-bottom: 17px !important;
    margin-left: 28px;
    color: #202124 !important;
}

.form-control {
    display: block;
    width: 100%;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 0.2em;
}

.spinner-border {
    display: inline-block;
    width: 2rem;
    height: 2rem;
    vertical-align: text-bottom;
    border: 0.25em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    -webkit-animation: spinner-border .75s linear infinite;
    animation: spinner-border .75s linear infinite;
}

.btn-login {
    width: 100%;
    height: 38px;
    border-radius: 6px;
    /* background-image: linear-gradient(to right, #fbc139, #ff5722); */
    cursor: pointer;
}

.validate-input.input-error .input {
    border: 1px solid #ff1d1d;
}

.form-control:focus {
    color: #495057;
    background-color: #fff;
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
}
</style>
