<template>
    <div class="form-list flex-grow-1 d-flex">
        <div class="d-flex flex-column flex-grow-1" v-if="!examShiftDialogVisible">
            <div class="d-flex flex-row title-box">
                <div class="list-title flex-grow-1 text-start">Danh sách kì thi</div>
            </div>
            <div class="d-flex flex-row toolbar-box justify-content-between">
                <div class="left-toolbar d-flex flex-row">
                    <div class="m-search_form flex-row d-flex align-items-center d-flex">
                        <InputText type="search" v-model="search" class="ms-input_search w-100" placeholder="Tìm kiếm"/>
                        <div class="icon24 icon search-right search"></div>
                    </div>
                </div>
                <div class="right-toolbar d-flex flex-row">
                    <Button
                        @click="examShiftDialogVisible = !examShiftDialogVisible, modeModal = FormMode.INSERT"
                        class="ms-btn primary d-flex justify-content-center flex-grow-1 ms-btn_search ps-3 pe-3 gap-2">
                        <div class="icon24 icon-add-white"></div>
                        <div class="fw-semibold">Thêm kì thi</div>
                    </Button>
                </div>
            </div>
            <div class="box list-content flex-grow-1 flex-row">
                <DataTable paginator :rows="10" :rowsPerPageOptions="[5, 10, 20, 50]" class="flex1 flex-column"
                           :class="{ 'loading': isLoading }" :loading="isLoading"
                           :value="isLoading ? Array.from({ length: 8 }, () => ({ ...objectLoading })) : dataExamManager"
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
                    <Column field="exam_name" style="width: 30vw;" header="Tên kì thi">
                        <template #body="{ data, field, slotProps }">
                            <div v-if="!isLoading"> {{ data[field] }}</div>
                            <div v-else>
                                <Skeleton height="18px" class="mb-2"></Skeleton>
                            </div>
                        </template>
                    </Column>
                    <Column field="exam_code" dataKey="id" header="Mã kì thi">
                        <template #body="{ data, field, slotProps }">
                            <div v-if="!isLoading"> {{ data[field] }}</div>
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
        <ExamManagerPopup v-if="examShiftDialogVisible" @showExamManager="showExamManager"
                          @loadExamManager="loadExamManager" @showToast="showToast" :modeModal="modeModal"
                          :selectedData="selectedData"/>
    </div>

    <Dialog v-model:visible="isPopupDelete" modal closeOnEscape :style="{ width: '25vw' }" header="Xóa kì thi">
        <TheLoadingProgress v-if="isLoadingDelete"/>
        <div class="w-full flex flex-column" style="line-height: 1.5;">
                <span> Kì thi có rất nhiều dữ liệu liên quan, bạn có chắc chắn muốn xóa kì thi <b>{{
                        selectedData.exam_name
                    }}</b> không?</span>
        </div>
        <template #footer>
            <Button
                class="ms-btn secondary d-flex justify-content-center ms-btn_search ps-3 pe-3 gap-2" @click="isPopupDelete = false">
                <div class="">Không</div>
            </Button>
            <Button @click="handlerDelete"
                    class="ms-btn danger d-flex justify-content-center ms-btn_search ps-3 pe-3 gap-2">
                <div class="">Xóa kì thi</div>
            </Button>
        </template>
    </Dialog>
</template>

<script>
import {generateCode, convertTimezoneToDatetime} from '@/common/functions';
import ExamManagerPopup from './components/ExamManagerPopup.vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dropdown from 'primevue/dropdown';
import Skeleton from 'primevue/skeleton';
import InputText from 'primevue/inputtext';
import TheLoadingProgress from '@/components/LoadingProgress.vue';
import Calendar from 'primevue/calendar';
import {getExamManager, deleteExamManager, insertExamShift} from '/api/exam-manager';
import {MESSAGE} from "@/common/enums";

export default {
    components: {
        DataTable,
        Column,
        Dialog,
        Button,
        Calendar,
        InputText,
        Dropdown,
        Skeleton,
        ExamManagerPopup,
        TheLoadingProgress
    },
    data() {
        return {
            dialogVisible: true,
            examShiftDialogVisible: false,

            columns: [
                {field: 'exam_shift_code', header: 'Mã ca thi'},
                {field: 'exam_shift_name', header: 'Tên ca thi'},
                {field: 'DateTimeStart', header: 'Ngày bắt đầu'},
                {field: 'DateTimeEnd', header: 'Ngày kết thúc'},
                {field: 'Department', header: 'Phòng thi'},
            ],
            search: null,
            //ca thi
            listExamShift: [],
            objectLoading: {},

            isPopupDelete: false,
            isLoadingDelete: false,

            examshift: {
                ExamShiftId: null,
                exam_shift_code: null,
                exam_shift_name: null,
                DateTimeStart: null,
                DateTimeEnd: null,
                Department: null
            },

            modeModal: this.FormMode.Insert,

            //tên các ca thi
            exam_shift_names: null,

            isLoading: false,
            selectedData: {},

            dataExamManager: [],

            modeGenerate: true, //cho phép sinh mã theo tên

            modeGenerateExamShift: true, //sinh mã ca thi
            exam: {
                ExamId: null,
                ExamCode: null,
                ExamName: null,
                start_date: null,
                end_date: null,
                Note: null,
            },

            invalidData: [],

            invalidExamShift: [],

        }
    },

    methods: {

        /**
         * Ẩn / hiển form thêm kì thi
         */
        showExamManager() {
            this.examShiftDialogVisible = !this.examShiftDialogVisible;
        },

        /**
         * Xử lý hàm sinh mã theo tên
         */
        handlerGenerateCode(name) {
            if (name === 'exam' && this.modeGenerate) this.exam.ExamCode = generateCode(this.exam.ExamName);
            if (name === 'examShift' && this.modeGenerateExamShift) {
                this.examshift.exam_shift_code = generateCode(this.examshift.exam_shift_name);
            }
        },

        /**
         * Xóa kì thi
         * @param {*} data
         */
        deleteRowSelect(data) {
            this.selectedData = {...data};
            this.isPopupDelete = true;

            console.log(data);
        },

        /**
         * Call api xóa kì thi
         */
        async handlerDelete() {
            this.isLoadingDelete = true;
            await deleteExamManager(this.selectedData.id).then(res => {
                this.$store.dispatch('handleSuccess', MESSAGE.HTTP_DELETE_OK);
                this.loadExamManager();
            }).catch(error => {
                console.log(error);
            }).finally(() => {
                setTimeout(() => {
                    this.isLoadingDelete = false;
                    this.isPopupDelete = false;
                }, 750);
            })
        },

        /**
         * Hiển thị toast message
         * @param {*} message
         */
        showToast(message, severity = 'success') {
            this.$toast.add({severity: severity, summary: 'Thông báo', detail: message, life: 3000});
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
         * selected item
         * @param {*} data
         */
        onRowSelect(data) {
            this.modeModal = this.FormMode.UPDATE;
            this.examShiftDialogVisible = !this.examShiftDialogVisible;
            this.selectedData = {...data};
        },

        /**
         * Lấy ra các phần tử không trùng lặp trong mảng
         * @param {*} data Mảng
         * @param {*} propName Phần tử cần lấy
         */
        getUniqueItems(data, propName) {
            return data.reduce((acc, curr) => {
                if (!acc.some(item => item[propName] === curr[propName])) {
                    acc.push(curr);
                }
                return acc;
            }, []);
        },

        /**
         * Lấy danh sách kì thi
         */
        async loadExamManager() {
            try {
                this.isLoading = true;
                await getExamManager().then(res => {
                    if (res[0]) {
                        var data = JSON.parse(res[0].ExamShift);
                        const exam_shift_names = data.reduce((acc, curr) => {
                            if (!acc.includes(curr.exam_shift_name) && !acc.includes(curr.exam_shift_code)) {
                                acc.push(curr.exam_shift_name);
                            }
                            return acc;
                        }, []).join(', ');
                        setTimeout(() => {
                            this.isLoading = false
                        }, 500);
                    }
                    this.dataExamManager = res.data;
                    // console.log(exam_shift_names);
                }).catch(error => {
                    this.showToast("Có lỗi xảy ra, vui lòng liên hệ nhà phát triển", 'error');
                    console.log(error);
                }).finally(() => {
                    setTimeout(() => {
                        this.isLoading = false
                    }, 500);
                });
            } catch (error) {
                console.log(error);
            }
        },

        /**
         * Thêm ca thi
         */
        btnAddExamShift() {
            this.examShift.push({
                exam_shift_code: null,
                exam_shift_name: null,
                start_date: null,
                end_date: null,
                department: null
            });
        },

        /**
         * Validate ngày kết thúc kì thi
         */
        handlerend_date() {
            this.invalidData = [];
            if (this.exam.end_date.getTime() - this.exam.start_date.getTime() < 2 * 60 * 60 * 1000) {
                this.invalidData['end_date'] = 'Thời gian tối tiểu là 2h';
            }
        },

        /**
         * Xóa ca thi
         * @param {*} index
         */
        btnRemoveExamShift(index) {
            this.examShift.splice(index, 1)
            if (this.examShift.length < 1) this.btnAddExamShift();
        }
    },

    created() {
        this.loadExamManager();
    }
}
</script>

<style scoped>
.p-panel.p-component.p-0 .p-panel-content {
    padding: 0 !important;
}
</style>
