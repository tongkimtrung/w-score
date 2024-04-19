<template>
  <div class="ma-wrapper w-100 d-flex h-100vh flex-column">
    <router-view name="header"></router-view>
    <div class="d-flex ma-main flex-grow-1">
      <router-view name="navbar"></router-view>
      <div class="ma-content d-flex overflow-auto">
        <router-view name="content"></router-view>
      </div>
    </div>
  </div>
</template>

<script>

export default {
  components: {},
  data() {
    return {}
  }
}
</script>

<style lang="scss">
.ma-wrapper {
  color: #212121;
  background: #f4f5f8;
  overflow: hidden;

  .ma-main {
    overflow: auto;

    .ma-content {
      padding: 24px;
      transition: padding .2s;
      width: 100%;
      height: 100%;
      box-shadow: inset 0 1.5px 2px 0 rgba(0, 0, 0, .1);

      .form-list .list-title {
        font-size: 20px;
        font-weight: 700;
        line-height: 35px;
      }

      .group-form_list{
        gap: 16px;
      }

      .group-form_box {
        display: flex;
        flex-direction: column;

        .label {
          color: var(--Gray-900);
          text-align: left;
          margin-bottom: 10px;
        }

        .description{
          text-align: left;
          color: rgba(0,0,0,.55);
          margin-bottom: 10px;
        }
      }

      .form-list {
        .title-box {
          margin: 0 0 16px;
        }

        .toolbar-box {
          margin: 0 0 16px;
        }

        .m-search_form {
          width: 265px;
          background: #fff;
          border: 1px solid var(--Gray-100);
          border-radius: 4px;
        }

        .m-search_form:focus-within {
          border: 1px solid #0073e6;
        }

        .box, .card {
          background-color: #fff;
          border-radius: 4px;
          box-shadow: 0 0 11px rgba(0, 0, 0, .08);
        }

        .list-content {
          position: relative;
        }
      }
    }
  }
}
</style>
