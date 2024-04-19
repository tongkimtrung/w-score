<template>
    <nav class="ma-navbar position-relative d-flex flex-column justify-content-between"
         :class="{'collapsed': isCollapsed}">
        <div class="left-container flex-grow-1">
            <PanelMenu :model="items" multiple>
                <template #item="{ item }">
                    <router-link v-if="item.route && !item.items" :to="item.route"
                                 class="ma-navbar-parent d-flex align-items-center">
                        <div class="d-flex flex-grow-1 align-items-center cursor-pointer menu-item">
                            <span :class="[item.icon, 'text-primary']"/>
                            <span :class="['ml-2', { 'font-semibold': item.items }]">{{ item.label }}</span>
                        </div>
                    </router-link>
                    <div v-else class="ma-navbar-parent d-flex align-items-center">
                        <div class="menu-item flex-grow-1 d-flex">
                            <div class="icon"></div>
                            <div>{{ item.label }}</div>
                            <span v-if="item.items" class="pi pi-angle-down text-primary ml-auto"/>
                        </div>
                    </div>
                </template>
            </PanelMenu>
        </div>
        <div class="toggle-button-container">
            <div class="toggle-button gap-2 d-flex pointer show justify-content-start" :class="{'hidden': isCollapsed}"
                 @click="isCollapsed = !isCollapsed">
                <span class="icon"></span>
                <span class="toggle-title text-start">Thu gọn</span>
            </div>
        </div>

    </nav>
</template>

<script>
import PanelMenu from 'primevue/panelmenu';

export default {
    components: {
        PanelMenu
    },
    data() {
        return {
            isCollapsed: false,
            items: [
                {
                    label: 'Tổng quan',
                    icon: 'icon icon-dashboard',
                    route: '/',
                },
                {
                    label: 'Ngân hàng đề thi',
                    icon: 'icon icon-category',
                    route: '/exam-list'
                },
                {
                    label: 'Danh sách phòng thi',
                    icon: 'icon icon-report',
                    route: '/departments'
                },
                {
                    label: 'Danh sách kì thi',
                    icon: 'icon icon-movement',
                    route: '/exam-manager'
                },
                {
                    label: 'Thực hiện chấm thi',
                    icon: 'icon icon-decision',
                    route: '/calculate'
                },
                {
                    label: 'Quản lý tài khoản',
                    icon: 'icon icon-profile',
                    route: '/users'
                },
            ]
        }
    },
    methods: {}
}
</script>

<style lang="scss">
.ma-navbar {
    overflow: hidden;
    width: 240px;
    padding: 1px;
    transition: width .2s;
    box-shadow: inset 0 1.5px 2px 0 rgba(0, 0, 0, .1);
    background: #fff;
    height: 100%;

    &:hover {
        overflow-y: auto;
    }

    &.collapsed {
        width: 56px;
    }

    .left-container {
        .p-panelmenu .p-panelmenu-header .p-panelmenu-header-content {
            border-radius: unset;
        }

        .p-panelmenu .p-panelmenu-content .p-menuitem:not(.p-highlight):not(.p-disabled).p-focus > .p-menuitem-content {
            background: unset;
        }

        .p-panelmenu .p-panelmenu-content .p-menuitem > .p-menuitem-content {
            color: #000;
        }

        .p-panelmenu .p-panelmenu-content .p-menuitem:not(.p-highlight):not(.p-disabled) > .p-menuitem-content:hover {
            background: #fff;
        }

        .p-panelmenu .p-panelmenu-header .p-panelmenu-header-content {
            color: #000;
        }

        .p-panelmenu .p-panelmenu-content {
            border: unset;
            padding: unset;
        }


        .p-panelmenu {
            a:hover {

            }

            ol, ul, dl {
                margin: unset;
                padding-left: 1.5rem;
            }

            .p-menuitem.p-focus {
                background: #fff;
            }

            .p-panelmenu-panel {
                margin: unset;

                .p-panelmenu-header {
                    .p-panelmenu-header-content {
                        background: #fff;
                        border: unset;
                    }
                }
            }

            .menu-item {
                padding: 12px;
                margin: 8px 8px 0 8px;
                border-radius: 8px;
                white-space: nowrap;

                .icon-category {
                    background-image: url('@public/assets/icons/ic_category_default.d46f95b8.svg');
                }

                .icon-dashboard {
                    background-image: url('@public/assets/icons/ic_dashboard_default.110fa5f3.svg');
                }

                .icon-report {
                    background-image: url('@public/assets/icons/ic_report_default.67157268.svg');
                }

                .icon-decision {
                    background-image: url('@public/assets/icons/ic_decision_menu_default.cae653f2.svg');
                }

                .icon-profile {
                    background-image: url('@public/assets/icons/ic_profile_default.d730fb8f.svg');
                }

                .icon-movement {
                    background-image: url('@public/assets/icons/ic_movement_default.102c9e3d.svg');
                }

                &:hover {
                    background: #eff7ff;
                    color: #ff6d00;
                    cursor: pointer;

                    .icon-category {
                        background-image: url('@public/assets/icons/ic_category.f257fcc6.svg');
                    }

                    .icon-dashboard {
                        background-image: url('@public/assets/icons/ic_dashboard.41eba193.svg');
                    }

                    .icon-report {
                        background-image: url('@public/assets/icons/ic_report.87c66795.svg');
                    }

                    .icon-decision {
                        background-image: url('@public/assets/icons/ic_decision_menu.3fd2f620.svg');
                    }

                    .icon-profile {
                        background-image: url('@public/assets/icons/ic_profile.628cd9e5.svg');
                    }

                    .icon-movement {
                        background-image: url('@public/assets/icons/ic_movement.629f9e51.svg');
                    }
                }

                .icon {
                    width: 24px;
                    height: 24px;
                    margin-right: 8px;
                }
            }

            .router-link-active.router-link-exact-active {
                .menu-item {
                    background: #fbe9e7;

                    .icon-category {
                        background-image: url('@public/assets/icons/ic_category.f257fcc6.svg');
                    }

                    .icon-dashboard {
                        background-image: url('@public/assets/icons/ic_dashboard.41eba193.svg');
                    }

                    .icon-report {
                        background-image: url('@public/assets/icons/ic_report.87c66795.svg');
                    }

                    .icon-decision {
                        background-image: url('@public/assets/icons/ic_decision_menu.3fd2f620.svg');
                    }

                    .icon-profile {
                        background-image: url('@public/assets/icons/ic_profile.628cd9e5.svg');
                    }

                    .icon-movement {
                        background-image: url('@public/assets/icons/ic_movement.629f9e51.svg');
                    }
                }

                &.ma-navbar-parent {
                    font-weight: 500;
                    display: block;
                    color: #ff6d00;
                }
            }

        }
    }


    .toggle-button-container {
        position: sticky;
        bottom: 0;
        height: 58px;
        background: #fff;

        .toggle-button {
            cursor: pointer;
            margin: 8px 8px 18px 8px;
            height: 40px;
            border-radius: 8px;
            line-height: 40px;
            background: #edf1f5;

            .toggle-title {
                display: block;
            }
        }

        .toggle-button {
            .icon:before {
                content: "";
                display: inline-block;
                margin-top: 8px;
                margin-left: 16px;
                left: 0;
                height: 24px;
                width: 24px;
                background: transparent url('@public/assets/icons/ic_toggle.4abb3c52.svg') no-repeat;
                transform: rotate(0deg);
                transition-duration: .4s;
            }
        }

        .toggle-button.hidden {
            width: 70%;
        }

        .toggle-button.hidden {
            .icon:before {
                transform: rotate(180deg) !important;
                margin-left: 8px;
            }

            .toggle-title {
                display: none;
            }
        }
    }


}
</style>
