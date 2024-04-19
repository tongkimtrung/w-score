<template>
    <div class="form-list flex-grow-1 d-flex">
        <div class="d-flex flex-column flex-grow-1">
            <div class="d-flex flex-row title-box">
                <div class="list-title flex-grow-1 text-start">Danh sách phòng thi</div>
            </div>
            <div class="d-flex flex-row toolbar-box justify-content-between">
                <div class="left-toolbar d-flex flex-row">
                    <div class="m-search_form flex-row d-flex align-items-center d-flex">
                        <InputText type="search" v-model="search" class="ms-input_search w-100" placeholder="Tìm kiếm"/>
                        <div class="icon24 icon search-right search"></div>
                    </div>
                </div>
                <div class="right-toolbar d-flex flex-row">
                    <Button @click="isShowModal = !isShowModal, modeGenerate = true, modePopup=FORM_MODE.INSERT"
                            class="ms-btn primary d-flex justify-content-center flex-grow-1 ms-btn_search ps-3 pe-3 gap-2">
                        <div class="icon24 icon-add-white"></div>
                        <div class="fw-semibold">Thêm phòng thi</div>
                    </Button>
                </div>
            </div>
            <div class="box list-content flex-grow-1 flex-row">
                <DataTable paginator :rows="10" :rowsPerPageOptions="[5, 10, 20, 50]" class="flex1 flex-column"
                           :class="{ 'loading': isLoading }" :loading="isLoading"
                           :value="isLoading ? Array.from({ length: 8 }, () => ({ ...this.department })) : data"
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
                    <Column field="department_code" style="width: 30vw;" header="Mã phòng thi">
                        <template #body="{ data, field, slotProps }">
                            <div v-if="!isLoading"> {{ data[field] }}</div>
                            <div v-else>
                                <Skeleton height="18px" class="mb-2"></Skeleton>
                            </div>
                        </template>
                    </Column>
                    <Column field="department_name" dataKey="id" header="Tên phòng thi">
                        <template #body="{ data, field, slotProps }">
                            <div v-if="!isLoading"> {{ data[field] }}</div>
                            <div v-else>
                                <Skeleton height="18px" class="mb-2"></Skeleton>
                            </div>
                        </template>
                    </Column>
                    <Column dataKey="id" header="Trạng thái" style="width: 200px;">
                        <template #body="{ data, field, slotProps }">
                            <div v-if="!isLoading">
                                <div class="d-flex status-ctn max-content" v-if="data['exam_shifts'].length > 0"
                                     style="background-color: rgb(229, 250, 237);">
                                    <div class="status-dot" style="background-color: rgb(0, 200, 83);"></div>
                                    <div class="status-text" style="color: rgb(0, 200, 83);">Đang sử dụng</div>
                                </div>
                                <div class="d-flex status-ctn max-content" v-else
                                     style="background-color: rgb(254, 243, 231);">
                                    <div class="status-dot" style="background-color: rgb(243, 141, 21);"></div>
                                    <div class="status-text" style="color: rgb(243, 141, 21);">Không sử dụng</div>
                                </div>
                            </div>
                            <div v-else>
                                <Skeleton height="18px" class="mb-2"></Skeleton>
                            </div>
                        </template>
                    </Column>
                    <Column alignFrozen="right" style="width: 100px; text-align: center;" frozen header="Thao tác">
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
    <Dialog v-model:visible="isPopupDelete" modal closeOnEscape :style="{ width: '25vw' }" header="Xóa phòng thi">
        <TheLoadingProgress v-if="isLoadingDelete"/>
        <div class="w-full flex flex-column" style="line-height: 1.5;">
            <span> Bạn có chắc chắn muốn xóa phòng thi <b>{{ department.department_code }}</b> không?</span>
        </div>
        <template #footer>
            <Button
                class="ms-btn secondary d-flex justify-content-center ms-btn_search ps-3 pe-3 gap-2"
                @click="isPopupDelete = false">
                <div class="">Không</div>
            </Button>
            <Button @click="handlerDelete"
                    class="ms-btn danger d-flex justify-content-center ms-btn_search ps-3 pe-3 gap-2">
                <div class="">Xóa phòng thi</div>
            </Button>
        </template>
    </Dialog>

    <Dialog v-model:visible="isShowModal" @keydown.enter.prevent="doSave" modal
            @afterHide="afterHideDialog"
            :header="modePopup == FormMode.INSERT ? 'Thêm phòng thi' : 'Sửa phòng thi'" :style="{ width: '30vw' }"
            closeOnEscape>
        <div class="w-full flex flex-column">
            <div class="form-group flex-row">
                <div class="flex1 mr-10">
                    <div class="group-form_box">
                        <div class="label">Tên phòng thi<span class="required">*</span></div>
                        <div class="mt-2">
                            <InputText v-model="department.department_name" placeholder="Tên phòng thi"
                                       :class="{ 'error': invalidData['department_name'] }"
                                       @input="handlerGenerateCode"></InputText>
                        </div>
                        <div class="ms-error-text" v-if="invalidData['department_name']">
                            {{ invalidData['department_name'] }}
                        </div>
                    </div>
                </div>
                <div class="flex1 mr-10">
                    <div class="ms-input ms-editor w-100">
                        <div class="group-form_box">
                            <div class="label">Mã phòng thi<span class="required">*</span></div>
                            <div class="mt-2">
                                <InputText v-model="department.department_code" placeholder="Mã phòng thi"
                                           :class="{ 'error': invalidData['department_code'] }"
                                           @keypress="handlerInputdepartment_code"
                                           @input="modeGenerate = department.department_code ? false : true;"></InputText>
                            </div>
                            <div class="ms-error-text" v-if="invalidData['department_code']">
                                {{ invalidData['department_code'] }}
                            </div>
                        </div>
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
                <Button
                    class="ms-btn secondary d-flex justify-content-center ms-btn_search ps-3 pe-3 gap-2 me-2"
                    @click="isShowModal = false">
                    <div class="">Đóng</div>
                </Button>
                <Button @click="doSave" @keyup.enter.prevent="doSave" :disabled="isDisabled"
                        class="ms-btn primary blue d-flex justify-content-center flex-grow-1 ms-btn_search ps-3 pe-3 gap-2">
                    <div class="fw-semibold">Lưu</div>
                </Button>
            </div>

        </template>
    </Dialog>

    <Dialog v-model:visible="warningVisible" modal closeOnEscape :style="{ width: '25vw', height: '20vh' }"
            header="Thông báo">
        <div class="w-full flex flex-column">
            <span> Phòng thi <b>{{ department.department_name }}</b> đang được sử dụng. Bạn không thể xóa.</span>
        </div>
        <template #footer>
            <Button
                @click="warningVisible = false"
                class="ms-btn secondary blue d-flex justify-content-center ms-btn_search ps-3 pe-3 gap-2">
                <div class="">Đóng</div>
            </Button>
        </template>
    </Dialog>
</template>

<script>
import Skeleton from 'primevue/skeleton';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import {generateCode} from '@/common/functions'
import {FORM_MODE, MESSAGE, RESPONSE_STATUS} from "@/common/enums";
import {saveData, getDataDepartment, updateDepartment, deleteDepartment} from '/api/department';
import TheLoadingProgress from "@/components/LoadingProgress.vue";

export default {
    name: "DepartmentList",
    computed: {
        FORM_MODE() {
            return FORM_MODE
        }
    },
    components: {
        TheLoadingProgress,
        Dialog,
        Button,
        InputText,
        DataTable,
        Column,
        Skeleton,
    },

    data() {
        return {
            search: null,
            modePopup: this.FormMode.INSERT,
            department: {
                id: null,
                department_name: null,
                department_code: null,
            },
            warningVisible: false,
            dataChanged: null,

            data: [],  // data table
            isShowModal: false,
            popupLoading: false,

            isLoading: false,

            isPopupDelete: false,
            isDisabled: false,
            isLoadingDelete: false,

            invalidData: [],   //mảng chứa thông tin lỗi
            modeGenerate: true, //cho phép sinh mã theo tên
        }
    },
    methods: {

        /**
         * Xử lý hàm sinh mã theo tên
         */
        handlerGenerateCode() {
            if (this.modeGenerate) this.department.department_code = generateCode({value: this.department.department_name});
        },

        /**
         * Validate mã phòng thi
         * Ko cho phép nhập các kí tự đặc biệt
         * @param {*} event
         */
        handlerInputdepartment_code(event) {
            let pattern = /[\W_]/g;
            let res = event.key.match(pattern);
            if (res) {
                event.preventDefault();
            }
        },

        /**
         * Lưu phòng thi
         */
        doSave(event) {
            event.preventDefault();
            this.invalidData = [];
            this.isDisabled = true;
            if (this.validateData()) {
                switch (this.modePopup) {
                    case this.FormMode.INSERT:
                        saveData(this.department).then(res => {
                            this.department = {};
                            this.$store.dispatch('handleSuccess', 'Thêm mới thành công');
                            this.isShowModal = false;
                            this.loadData();
                        }).catch(error => {
                            if (error.response.status === RESPONSE_STATUS.HTTP_UNPROCESSABLE_ENTITY) {
                                this.$store.dispatch('handleError');
                                for (const itemError in error.response.data.errors) {
                                    this.invalidData[itemError] = error.response.data.errors[itemError][0];
                                }
                            }
                        }).finally(() => {
                            this.isDisabled = false;
                        })
                        break;
                    case this.FormMode.UPDATE:
                        if (JSON.stringify(this.dataChanged) !== JSON.stringify(this.department)) {
                            updateDepartment(this.department).then(res => {
                                this.department = {};
                                this.isShowModal = false;
                                this.$store.dispatch('handleSuccess', MESSAGE.HTTP_UPDATE_OK);
                                this.loadData();
                            }).catch(error => {
                                if (error.response.status === RESPONSE_STATUS.HTTP_UNPROCESSABLE_ENTITY) {
                                    for (var itemError in error.response.data.errors) {
                                        this.invalidData[itemError] = error.response.data.errors[itemError][0];
                                    }
                                }
                                return;
                            }).finally(() => {
                                this.isDisabled = false;
                            })
                        } else {
                            this.isShowModal = false;
                            this.isDisabled = false;
                        }
                        break;
                    default:
                        break;
                }
            } else this.isDisabled = false;
            this.modeGenerate = true;
        },

        /**
         * Click nút xóa phòng thi
         */
        handlerDelete() {
            this.isLoadingDelete = true;
            deleteDepartment(this.department.id).then(res => {
                this.isPopupDelete = false;
                this.department = {
                    'id': null,
                    'department_name': null,
                    'department_code': null,
                };
                this.$store.dispatch('handleSuccess', MESSAGE.HTTP_DELETE_OK);
                this.loadData();
            }).catch(error => {
                console.log(error);
            }).finally(() => {
                setTimeout(() => {
                    this.isLoadingDelete = false
                }, 300);
            })
        },

        afterHideDialog() {
            this.department = {
                id: null,
                department_name: null,
                department_code: null,
            };
        },

        /**
         * Xóa phòng thi
         * @param {*} data
         */
        deleteRowSelect(data) {
            this.department = data;
            if (data.exam_shifts.length > 0) {
                this.warningVisible = true;
            } else {
                this.isPopupDelete = true;
                this.department = data;
            }
        },

        /**
         * validate dữ liệu
         */
        validateData() {
            var invalid = true;
            if (this.department.department_name == null) {
                this.invalidData['department_name'] = "Tên phòng thi không được để trống";
                invalid = false;
            }
            if (this.department.department_code == null) {
                this.invalidData['department_code'] = "Mã phòng thi không được để trống";
                invalid = false;
            }
            return invalid;
        },

        /**
         * Lấy thông tin bản ghi
         * @param {*} data
         */
        onRowSelect(data) {
            this.modePopup = this.FormMode.UPDATE;
            this.department = {...data};
            this.dataChanged = data;
            this.isShowModal = true;
        },

        /**
         * Load dữ liệu
         */
        async loadData() {
            this.isLoading = true;
            // this.data = Array.from({ length: 5 }, () => ({ ...this.department }));
            await getDataDepartment().then(res => {
                this.data = res.data;
            }).catch(error => {
                console.log(error);
            })
                .finally(() => {
                    setTimeout(() => {
                        this.isLoading = false;
                    }, 300);
                });
        }
    },

    created() {
        this.loadData();
    },

    mounted() {
    }
}
</script>
