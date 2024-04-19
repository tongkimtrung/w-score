<template>
  <div class="ma-header-wrapper">
    <div class="ma-header-container flex-grow-1 h-100 d-flex justify-content-between">
      <div class="left-side d-flex">
        <div class="logo-content d-flex justify-content-start">
          <div class="nav-logo"></div>
          <div class="logo-title">Word Score</div>
        </div>
      </div>
      <div class="right-side d-flex">
        <div class="right-btt-action right-action icon-notify icon24 has-tooltip" v-tooltip.bottom="'Thông báo'"></div>
        <div class="right-btt-action right-action icon-setting icon24 setting has-tooltip"
             v-tooltip.bottom="'Cài đặt'"></div>
        <div class="h-100">
          <Button type="button" icon="pi pi-ellipsis-v" class="h-100 ms-btn white" @click="toggle" aria-haspopup="true"
                  aria-controls="overlay_menu"/>
          <Menu ref="menu" id="overlay_menu" :model="items" :popup="true"/>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Menu from 'primevue/menu';
import Button from 'primevue/button';

export default {
  components: {
    Menu,
    Button
  },
  data() {
    return {
      items: [
        {
          label: 'Refresh',
          icon: 'pi pi-refresh'
        },
        {
          label: 'Export',
          icon: 'pi pi-upload'
        }
      ]
    };
  },
  methods: {
    toggle(event) {
      this.$refs.menu.toggle(event);
    }
  }

}
</script>

<style lang="scss">
.ma-header-wrapper {
  height: 56px;
  min-height: 56px;
  background: #fff;
  width: 100%;
  box-shadow: inset 0 1.5px 2px 0 rgba(0, 0, 0, .1);

  .ma-header-container {
    .left-side {
      .logo-content {
        display: flex;
        align-items: center;
        width: 204px;
        justify-content: center;
        padding-left: 25px;
      }

      .logo-content .logo-title {
        font-size: 20px;
        text-align: center;
        font-weight: 700;
      }

      .logo-content .nav-logo {
        display: block;
        width: 32px;
        height: 32px;
        background: url('@public/assets/icons/logo_footer.svg') no-repeat;
        background-size: 32px;
        border-radius: 50%;
        margin-right: 12px;
      }

    }

    .right-side {
      .right-btt-action {
        height: 38px;
        width: 40px;
        margin: auto !important;
        border-radius: 50%;
        position: relative;
      }

      .right-action:hover {
        cursor: pointer;
        background-color: #f5f5f5;
        background-size: 24px;
      }
    }
  }
}
</style>
