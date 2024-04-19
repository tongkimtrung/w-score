<template>
    <div class="ms-loading" :class="{ 'fixed': fixed, 'absolute': !fixed, 'ms-loading_background': useBackground }">
        <span class="loader"></span>
    </div>
</template>

<script>
export default {
    props: {
        fixed: {
            default: false,
        },
        progress: {
            default: null
        },
        useBackground: {
            default: false,
        }
    }
}

</script>
<style>
.fixed {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: hsla(0, 0%, 86.7%, .2);
    display: flex;
    justify-content: center;
    align-items: center;
}

.ms-loading_background {
    z-index: 10000000;
    background: #f4f5f8 !important;
}


.ms-loading.absolute {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: hsla(0, 0%, 86.7%, .2);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000000;
}

.fixed .loader {
    width: 30px;
    height: 30px;
    border: 3px solid transparent;
    border-bottom-color: #2979ff;
    border-radius: 50%;
    display: inline-block;
    box-sizing: border-box;
    animation: rotation .6s linear infinite;
}

.loader {
    width: 30px;
    height: 30px;
    border: 3px solid transparent;
    border-bottom-color: #2979ff;
    border-radius: 50%;
    display: inline-block;
    box-sizing: border-box;
    animation: rotation .6s linear infinite;
}

.p-datatable .p-datatable-loading-overlay{
    z-index: 1000000;
}

@keyframes rotation {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}
</style>
