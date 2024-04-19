<template>
    <div class="form-list flex-grow-1 d-flex">
        <div class="d-flex flex-column flex-grow-1">
            <div class="d-flex flex-row title-box">
                <div class="list-title flex-grow-1 text-start">Quản lý tài khoản</div>
            </div>
            <div class="d-flex flex-row toolbar-box justify-content-between">
                <div class="left-toolbar d-flex flex-row">
                    <div class="m-search_form flex-row d-flex align-items-center d-flex">
                        <InputText type="search" v-model="search" class="ms-input_search w-100" placeholder="Tìm kiếm"/>
                        <div class="icon24 icon search-right search"></div>
                    </div>
                </div>
                <div class="right-toolbar d-flex flex-row">
                    <Button @click="isShowModal = !isShowModal"
                            class="ms-btn primary d-flex justify-content-center flex-grow-1 ms-btn_search ps-3 pe-3 gap-2">
                        <div class="icon24 icon-add-white"></div>
                        <div class="fw-semibold">Thêm tài khoản</div>
                    </Button>
                </div>
            </div>
            <div class="box list-content flex-grow-1 flex-row">
                <DataTable paginator :rows="10" :rowsPerPageOptions="[5, 10, 20, 50]" class="flex1 flex-column"
                           :class="{ 'loading': isLoading }" :loading="isLoading"
                           :value="isLoading ? Array.from({ length: 8 }, () => ({ ...this.department })) : users"
                           currentPageReportTemplate="{first} to {last} of {totalRecords}"
                           paginatorTemplate="FirstPageLink PrevPageLink flex1 CurrentPageReport NextPageLink LastPageLink RowsPerPageDropdown"
                           @rowDblclick="onRowSelect($event.data)" tableStyle="min-width: 100%" rowHover>
                    <template #paginatorstart>
                        <Button type="button" icon="pi pi-refresh" text/>
                    </template>
                    <template #paginatorend>
                        bản ghi/trang
                    </template>
                    <template #empty>
                        <div>
                            Không có dữ liệu
                        </div>
                    </template>
                    <Column header="STT" style="width: 100px;" class="text-center">
                        <template #body="slotProps">
                            <div v-if="!isLoading"> {{ slotProps.index + 1 }}</div>
                            <div v-else>
                                <Skeleton height="18px" class="mb-2"></Skeleton>
                            </div>
                        </template>
                    </Column>
                    <Column field="name" style="width: 15vw;" header="Họ và tên">
                        <template #body="{ data, field, slotProps }">
                            <div v-if="!isLoading"> {{ data[field] }}</div>
                            <div v-else>
                                <Skeleton height="18px" class="mb-2"></Skeleton>
                            </div>
                        </template>
                    </Column>
                    <Column field="user_code" dataKey="id" header="Mã tài khoản" style="width: 20vw;">
                        <template #body="{ data, field, slotProps }">
                            <div v-if="!isLoading"> {{ data[field] }}</div>
                            <div v-else>
                                <Skeleton height="18px" class="mb-2"></Skeleton>
                            </div>
                        </template>
                    </Column>
                    <Column field="email" dataKey="id" header="Email">
                        <template #body="{ data, field, slotProps }">
                            <div v-if="!isLoading"> {{ data[field] }}</div>
                            <div v-else>
                                <Skeleton height="18px" class="mb-2"></Skeleton>
                            </div>
                        </template>
                    </Column>
                    <Column alignFrozen="right" style="width: 15vw; text-align: center;" frozen header="Thao tác">
                        <template #body="slotProps">
                            <div class="row-actions flex-row" v-if="!isLoading">
                                <div class="item" @click="onRowSelect(slotProps.data)">
                                    <div class="v-popover popover">
                                        <div class="trigger">
                                            <div class="icon24 edit"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="item" @click="deleteRowSelect(slotProps.data)">
                                    <div class="v-popover popover">
                                        <div class="icon24 delete"></div>
                                    </div>
                                </div>
                            </div>
                            <div v-else>
                                <Skeleton height="18px" class="mb-2"></Skeleton>
                            </div>
                        </template>
                    </Column>

                </DataTable>
            </div>
        </div>
    </div>
    <Dialog v-model:visible="isPopupDelete" modal closeOnEscape :style="{ width: '25vw', height: '20vh' }"
            header="Xóa đề thi">
        <div class="w-full flex flex-column">
            <span> Bạn có chắc chắn muốn xóa tài khoản <b>{{ selectedUsers.name }}</b> không?</span>
        </div>
        <template #footer>
            <Button label="Không" class="ms-button btn detail-button secondary" @click="isPopupDelete = false"/>
            <Button label="Xóa tài khoản" class="ms-button btn w-100 danger" @click="handlerDelete"/>
        </template>
    </Dialog>

    <Dialog v-model:visible="isShowModal" @keydown.enter.prevent="doSave" modal @afterHide="afterHideDialog"
            :header="modeModal == FormMode.INSERT ? 'Thêm tài khoản' : 'Sửa tài khoản'" :style="{ width: '50vw' }"
            closeOnEscape>
        <TheLoadingProgress v-if="isLoadingComponent"/>
        <div class="w-full flex flex-column">
            <div class="form-group flex-row">
                <div class="flex1 mr-10">
                    <div class="group-form_box">
                        <div class="label"> Họ và tên<span class="required">*</span></div>
                        <div class="mt-2">
                            <InputText v-model="selectedUsers.name" placeholder="Nhập họ tên"
                                       :class="{ 'error': invalidData['name'] }"
                                       @input="handlerGenerateCode"></InputText>
                        </div>
                        <div class="ms-error-text" v-if="invalidData['name']">
                            {{ invalidData['name'] }}
                        </div>
                    </div>
                </div>
                <div class="flex1 mr-10">
                    <div class="group-form_box">
                        <div class="label">Mã tài khoản<span class="required">*</span></div>
                        <div class="mt-2">
                            <InputText v-model="selectedUsers.user_code" placeholder="Tên phòng thi"
                                       :class="{ 'error': invalidData['user_code'] }"
                                       @keypress="handlerInputCode"
                                       @input="modeGenerate = selectedUsers.user_code ? false : true;"></InputText>
                        </div>
                        <div class="ms-error-text" v-if="invalidData['user_code']">
                            {{ invalidData['user_code'] }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group flex-row">
                <div class="flex1 mr-10">
                    <div class="group-form_box">
                        <div class="label">Email<span class="required">*</span></div>
                        <div class="mt-2">
                            <InputText v-model="selectedUsers.email" placeholder="Nhập email"
                                       :class="{ 'error': invalidData['user_code'] }"
                                       @keypress="validateSpace"></InputText>
                        </div>
                        <div class="ms-error-text" v-if="invalidData['email']">
                            {{ invalidData['email'] }}
                        </div>
                    </div>
                </div>
                <div class="flex1 mr-10">
                    <div class="group-form_box">
                        <div class="label">Loại tài khoản<span class="required">*</span></div>
                        <div class="mt-2">
                            <Dropdown v-model="selectedUsers.level" optionLabel="description" optionValue="value"
                                      :class="{ 'error': invalidData['level'] }" :options="levelOptions" showClear
                                      placeholder="Chọn loại tài khoản"/>
                        </div>
                        <div class="ms-error-text" v-if="invalidData['level']">
                            {{ invalidData['level'] }}
                        </div>
                    </div>
                    <div class="flex1">
                        <div class="ms-input ms-editor w-100"></div>
                    </div>
                </div>
            </div>
            <div class="form-group flex-row">
                <div class="flex1 mr-10">
                    <div class="form-group-label d-flex label-form">
                        Ghi chú
                    </div>
                    <div class="ms-input ms-editor w-100">
                            <Textarea v-model="selectedUsers.note" autoResize rows="5" cols="30"
                                      placeholder="Nhập ghi chú"/>
                    </div>
                    <div class="flex1">
                        <div class="ms-input ms-editor w-100"></div>
                    </div>
                </div>
            </div>
        </div>
        <template #footer>
            <div class="d-flex flex-row">
                <div class="flex1"></div>
                <Button label="Đóng" class="ms-button btn detail-button secondary" @click="isShowModal = false"/>
                <Button @click="doSave" @keyup.enter="doSave"
                        class="ms-btn primary blue d-flex justify-content-center flex-grow-1 ms-btn_search ps-3 pe-3 gap-2">
                    <div class="fw-semibold">Lưu</div>
                </Button>
            </div>

        </template>
    </Dialog>

</template>

<script>
import {generateCode} from '@/common/functions';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import DataTable from 'primevue/datatable';
import TheLoadingProgress from '@/components/LoadingProgress.vue';
import Column from 'primevue/column';
import Dropdown from 'primevue/dropdown';
import Skeleton from 'primevue/skeleton';
import InputText from 'primevue/inputtext';
import Calendar from 'primevue/calendar';
import {MESSAGE, RESPONSE_STATUS} from "@/common/enums";
import {getUsers, saveUser, updateUser, deleteUser} from '/api/user';

export default {
    components: {
        DataTable,
        Column,
        Dialog,
        Textarea,
        Button,
        Calendar,
        InputText,
        Dropdown,
        TheLoadingProgress,
        Skeleton,
    },
    data() {
        return {
            isShowModal: false,
            columns: [
                {field: 'name', header: 'Họ và tên'},
                {field: 'user_code', header: 'Mã user'},
                {field: 'email', header: 'email'},
                {field: 'level', header: 'Loại tài khoản'},
                {field: 'note', header: 'Ghi chú'},
            ],

            department: {},
            exam: {},
            levelOptions: [
                {
                    description: 'Chấm thi',
                    value: 1,
                },
                {
                    description: 'Tạo đề',
                    value: 2,
                },
                {
                    description: 'Tạo đề và chấm thi',
                    value: 3,
                },
            ],
            search: null,
            isLoadingComponent: false,
            isPopupDelete: false,

            isLoading: true,
            isDisable: false,
            selectedUsers: {
                name: null,
                user_code: null,
                email: null,
                note: null,
                level: null,
            },


            modeModal: this.FormMode.INSERT,
            users: [],
            modeGenerate: true, //cho phép sinh mã theo tên
            warningVisible: false,
            invalidData: [],

        }
    },

    methods: {

        /**
         * Xử lý hàm sinh mã theo tên
         */
        handlerGenerateCode() {
            if (this.modeGenerate)
                this.selectedUsers.user_code = generateCode(this.selectedUsers.name);
        },

        doSave() {
            try {
                this.isDisable = true;
                if (this.validateData()) {
                    this.isLoadingComponent = true;
                    if (this.modeModal === this.FormMode.INSERT) {
                        saveUser(this.selectedUsers).then(res => {
                            this.loadUsers();
                            this.$store.dispatch('handleSuccess', MESSAGE.HTTP_INSERT_OK);
                            this.isShowModal = false;
                            this.modeModal = this.FormMode.INSERT;
                            this.selectedUsers = {
                                name: null,
                                user_code: null,
                                email: null,
                                note: null,
                                level: null,
                            };
                        }).catch(error => {
                            if (error.response.status === RESPONSE_STATUS.HTTP_UNPROCESSABLE_ENTITY) {
                                for (var itemError in error.response.data.errors) {
                                    console.log(error.response.data.errors);
                                    this.invalidData[itemError] = error.response.data.errors[itemError][0];
                                }
                            }
                        }).finally(() => {
                            this.isDisable = false;
                            setTimeout(() => {
                                this.isLoadingComponent = false;
                            }, 300);
                        });

                    } else if (this.modeModal === this.FormMode.UPDATE) {
                        updateUser(this.selectedUsers).then(res => {
                            this.loadUsers();
                            this.$store.dispatch('handleSuccess', MESSAGE.HTTP_UPDATE_OK);
                            this.isShowModal = false;
                            this.modeModal = this.FormMode.INSERT;
                            this.selectedUsers = {
                                name: null,
                                user_code: null,
                                email: null,
                                note: null,
                                level: null,
                            };
                        }).catch(error => {
                            console.log(error);
                        }).finally(() =>
                            setTimeout(() => {
                                this.isLoadingComponent = false;
                            }, 750));
                    }
                }
                this.isDisable = false;
            } catch (error) {
                this.isDisable = false;
                this.isLoadingComponent = false;
            }
        },

        /**
         * Lấy thông tin bản ghi
         * @param {*} data
         */
        onRowSelect(data) {
            this.modeModal = this.FormMode.UPDATE;
            this.selectedUsers = {...data};
            this.isShowModal = true;
        },

        afterHideDialog() {
            this.selectedUsers = {
                name: null,
                user_code: null,
                email: null,
                note: null,
                level: null,
            };
        },

        /**
         * Xóa phòng thi
         * @param {*} data
         */
        deleteRowSelect(data) {
            this.isPopupDelete = true;
            this.selectedUsers = data;
        },

        /**
         * Click nút xóa phòng thi
         */
        handlerDelete() {
            deleteUser(this.selectedUsers.id).then(res => {
                this.isPopupDelete = false;
                this.$store.dispatch('handleSuccess', MESSAGE.HTTP_DELETE_OK);
                this.loadUsers();
            }).catch(error => {
                console.log(error);
            })
        },

        /**
         * Validate dữ liệu
         */
        validateData() {
            var invalid = true;
            this.invalidData = [];
            if (this.selectedUsers.name == null || String(this.selectedUsers.name).trim() == '') {
                this.invalidData['name'] = 'Tên tài khoản không được để trống';
                invalid = false;
            }

            if (this.selectedUsers.user_code == null || String(this.selectedUsers.user_code).trim() == '') {
                this.invalidData['user_code'] = 'Mã tài khoản không được để trống';
                invalid = false;
            }

            if (this.selectedUsers.email != null && !this.validateemail()) {
                this.invalidData['email'] = 'Email không đúng định dạng';
                invalid = false;
            }

            if (this.selectedUsers.email == null) {
                this.invalidData['email'] = 'Email không được để trống';
                invalid = false;
            }

            if (this.selectedUsers.level == null) {
                this.invalidData['level'] = 'Loại tài khoản không được để trống';
                invalid = false;
            }
            return invalid;
        },

        /**
         * Validate email
         */
        validateemail() {
            if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(this.selectedUsers.email) && this.selectedUsers.email != null && String(this.selectedUsers.email).trim() != '') {
                return true;
            }
            return false;
        },

        /**
         * Validate mã phòng thi
         * Ko cho phép nhập các kí tự đặc biệt
         * @param {*} event
         */
        handlerInputDepartmentCode(event) {
            let pattern = /[\W_]/g;
            let res = event.key.match(pattern);
            if (res) {
                event.preventDefault();
            }
        },

        /**
         * Validate mã phòng thi
         * Ko cho phép nhập các kí tự đặc biệt
         * @param {*} event
         */
        handlerInputCode(event) {
            let pattern = /[\W_]/g;
            let res = event.key.match(pattern);
            if (res) {
                event.preventDefault();
            }
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

        /**
         * Lấy danh sách user
         */
        async loadUsers() {
            this.isLoading = true;
            await getUsers().then(res => {
                this.users = res.data;
            }).catch(error => {
                console.log(error);
            }).finally(() => {
                setTimeout(() => {
                    this.isLoading = false
                }, 500);
            })
        },
    },

    created() {
        this.loadUsers();
    },

}
</script>
