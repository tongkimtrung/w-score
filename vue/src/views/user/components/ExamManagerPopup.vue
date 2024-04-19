<template>
    <div class="form-list flex-grow-1 d-flex">
        <div class="d-flex flex-column flex-grow-1">
            <div class="d-flex flex-row title-box align-items-center">
                <div class="icon24 back cursor-pointer me-2" @click="closeExamManagerPopup"></div>
                <div class="list-title flex-grow-1 text-start">{{ modeModal === FormMode.INSERT ? 'Thêm' : 'Sửa' }} kì
                    thi
                </div>
                <div class="right-toolbar d-flex flex-row">
                </div>
            </div>
            <div class="flex-grow-1 flex-row row gx-0 gap-3">
                <TheLoadingProgress v-if="isLoading"/>
                <div class="col-3 left-grade" :class="{'collage': isCollapsed}">
                    <div class="title text-start d-flex justify-content-between">
                        <div class="text">Thông tin kì thi</div>
                        <div class="btn-collapse icon-toggle icon24-only" @click="isCollapsed = true"></div>
                    </div>
                    <div class="w-100 h-100">
                        <div class="outer" @click="isCollapsed = false">
                            <div class="left-grade-content_collage inner rotate">
                                <div style="margin: auto;">Thông tin kì thi</div>
                            </div>
                        </div>
                        <div class="left-grade-content">
                            <div class="group-form_box">
                                <div class="d-flex">
                                    <div class="label">1. Thông tin kì thi</div>
                                </div>
                                <div class="">
                                </div>
                                <div class="ms-error-text"></div>
                            </div>
                            <div class="group-form_box mt-3">
                                <div class="d-flex">
                                    <div class="label">2. Thông tin ca thi</div>
                                    <div class="icon16 icon-note has-tooltip"
                                         v-tooltip.top="{ value: 'Ca thi là bắt buộc', escape: true, class: 'custom-error' }">
                                    </div>
                                </div>
                                <div class="">
                                </div>
                                <div class="ms-error-text"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="list-content flex1 flex-column right-grade">
                    <div class="box list-content flex1 flex-column">
                        <div class="content-body flex1 flex-column">
                            <div class="mb-20">
                                <div class="title-1 flex1 flex-row">
                                    <span>1. Thông tin kì thi</span>
                                </div>
                                <div class="form-group flex-row">
                                    <div class="flex1 mr-10">
                                        <div class="form-group-label d-flex label-form">
                                            Tên kì thi
                                            <span class="required">*</span>
                                        </div>
                                        <div class="ms-input ms-editor w-100">
                                            <InputText v-model="exam.exam_name"
                                                       :class="{ 'error': invalidExamManager['exam_name'] }"
                                                       @input="handlerGenerateCode('exam')"
                                                       placeholder="Nhập tên kì thi"/>
                                            <div class="error-text" v-if="invalidExamManager['exam_name']">
                                                {{ invalidExamManager['exam_name'] }}
                                            </div>
                                        </div>
                                        <div class="flex1">
                                            <div class="ms-input ms-editor w-100"></div>
                                        </div>
                                    </div>
                                    <div class="flex1 mr-10">
                                        <div class="form-group-label d-flex label-form">
                                            Mã kì thi
                                            <span class="required">*</span>
                                        </div>
                                        <div class="ms-input ms-editor w-100">
                                            <InputText v-model="exam.exam_code"
                                                       :class="{ 'error': invalidExamManager['exam_code'] }"
                                                       placeholder="Nhập tên kì thi" @keypress="handlerInputCode"/>
                                            <div class="error-text" v-if="invalidExamManager['exam_code']">
                                                {{ invalidExamManager['exam_code'] }}
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
                                            Ngày bắt đầu
                                            <span class="required">*</span>
                                        </div>
                                        <div class="ms-input ms-editor w-100">
                                            <Calendar v-model="exam.start_date" dataType="string"
                                                      :class="{ 'error': invalidExamManager['start_date'] }"
                                                      locale="vi" dateFormat="dd/mm/yy" class="w-full" id="calendar-24h"
                                                      showTime hourFormat="24" showIcon
                                                      placeholder="Chọn ngày bắt đầu kì thi"/>
                                            <div class="error-text" v-if="invalidExamManager['start_date']">
                                                {{ invalidExamManager['start_date'] }}
                                            </div>
                                        </div>
                                        <div class="flex1">
                                            <div class="ms-input ms-editor w-100"></div>
                                        </div>
                                    </div>
                                    <div class="flex1 mr-10">
                                        <div class="form-group-label d-flex label-form">
                                            Ngày kết thúc
                                            <span class="required">*</span>
                                        </div>
                                        <div class="ms-input ms-editor w-100">
                                            <Calendar v-model="exam.end_date" dateFormat="dd/mm/yy"
                                                      @date-select="functionTest" dataType="string"
                                                      :class="{ 'error': invalidExamManager['end_date'] }"
                                                      :minDate="exam.start_date"
                                                      :disabled="exam.start_date == null"
                                                      class="w-full" id="calendar-24h"
                                                      placeholder="Chọn ngày kết thúc kì thi" showTime hourFormat="24"
                                                      showIcon/>
                                            <div class="error-text" v-if="invalidExamManager['end_date']">
                                                {{ invalidExamManager['end_date'] }}
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
                                            Mô tả
                                            <span class="required">*</span>
                                        </div>
                                        <div class="ms-input ms-editor w-100">
                                                    <Textarea v-model="exam.note" autoResize rows="5" cols="30"
                                                              placeholder="Nhập ghi chú"/>
                                            <div class="error-text">
                                            </div>
                                        </div>
                                        <div class="flex1">
                                            <div class="ms-input ms-editor w-100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-20 flex-column flex1">
                                <div class="title-1 w-100 flex-row">
                                    <span>2. Thông tin ca thi</span>
                                </div>
                                <div class="d-flex w-100 mb-10">
                                    <div class="flex1"><span class="error-text">{{
                                            invalidExamManager['ExamShift']
                                        }}</span></div>
                                    <Button @click="showExamShiftDialogVisible(), modeModalExamShift = FormMode.INSERT"
                                            class="primary custom-btn text-link  d-flex justify-content-center ms-btn_search ps-3 pe-3 gap-1 mb-2">
                                        <div class="icon24 icon-add-blue"></div>
                                        <div class="fw-semibold">Thêm ca thi</div>
                                    </Button>
                                </div>
                                <DataTable class="flex1" rowHover table-class="grid-group"
                                           :class="{ 'loading': isLoading }" :loading="isLoading"
                                           :value="listExamShift"
                                           @rowDblclick="onRowSelectExamShift($event.data), modeExamShiftModal = FormMode.UPDATE, showExamShiftDialogVisible()">
                                    <template #empty>
                                        <div>
                                            Không có dữ liệu
                                        </div>
                                    </template>
                                    <Column field="exam_shift_name" dataKey="id" header="Tên ca thi"
                                            style="min-width: 300px;">
                                        <template #body="{ data, field, slotProps, index }">
                                            <div> {{ data[field] }}</div>
                                        </template>
                                    </Column>
                                    <Column field="exam_shift_code" dataKey="id" header="Mã ca thi"
                                            style="min-width: 160px;">
                                        <template #body="{ data, field }">
                                            <div> {{ data[field] }}</div>
                                        </template>
                                    </Column>
                                    <Column field="start_date" dataKey="id" header="Ngày bắt đầu"
                                            style="min-width: 180px;">
                                        <template #body="{ data, field }">
                                            <div> {{ data[field] }}</div>
                                        </template>
                                    </Column>
                                    <Column field="end_date" dataKey="id" header="Ngày kết thúc"
                                            style="min-width: 180px;">
                                        <template #body="{ data, field }">
                                            <div> {{ data[field] }}</div>
                                        </template>

                                    </Column>
                                    <Column alignFrozen="right" style="width: 90px;" frozen header="Thao tác">
                                        <template #body="slotProps">
                                            <div class="row-actions" style="width: 100%;">
                                                <div class="position-relative">
                                                    <div class="item"
                                                         @click="modeExamShiftModal = FormMode.UPDATE, onRowSelectExamShift(slotProps.data), showExamShiftDialogVisible()">
                                                        <div class="v-popover popover">
                                                            <div class="trigger">
                                                                <div class="icon24 edit"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="item"
                                                         @click="selectedExamShift = { ...slotProps.data }, btnRemoveExamShift(slotProps.index)">
                                                        <div class="v-popover popover">
                                                            <div class="icon24 delete"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </Column>
                                </DataTable>
                            </div>
                        </div>
                        <div class="content-footer">
                            <div class="flex-row">
                                <div class="flex1"></div>
                                <Button
                                    @click="examShiftDialogVisible = false; invalidExamShift = []; closeExamManagerPopup()"
                                    class="ms-btn secondary blue me-3 d-flex justify-content-center ms-btn_search ps-3 pe-3 gap-2">
                                    <div class="">Đóng</div>
                                </Button>
                                <Button @click="saveExamManager" @keyup.enter="saveExamManager"
                                        :disabled="isDisabledExamManager"
                                        class="ms-btn primary blue d-flex justify-content-center ms-btn_search ps-3 pe-3 gap-2">
                                    <div class="">Lưu</div>
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <Dialog v-model:visible="examShiftDialogVisible" modal @keydown.enter.prevent="saveExamShift"
            @afterHide="afterHideExamShiftDialog"
            :header="modeExamShiftModal == FormMode.INSERT ? 'Thêm ca thi' : 'Sửa ca thi'"
            :style="{ width: '50vw' }">
        <TheLoadingProgress v-if="isLoadingPopupExamShift"/>
        <div class="w-full flex flex-column">
            <div class="form-group flex-row">
                <div class="flex1 mr-10">
                    <div class="form-group-label d-flex label-form">
                        Tên ca thi
                        <span class="required">*</span>
                    </div>
                    <div class="ms-input ms-editor w-100">
                        <InputText v-model="selectedExamShift.exam_shift_name" placeholder="Nhập tên ca thi"
                                   :class="{ 'error': invalidExamShift['exam_shift_name'] }"
                                   @input="handlerGenerateCode('examShift')"/>
                        <div class="error-text" v-if="invalidExamShift['exam_shift_name']">
                            {{ invalidExamShift['exam_shift_name'] }}
                        </div>
                    </div>
                    <div class="flex1">
                        <div class="ms-input ms-editor w-100"></div>
                    </div>
                </div>
                <div class="flex1">
                    <div class="form-group-label d-flex label-form">
                        Mã ca thi
                        <span class="required">*</span>
                    </div>
                    <div class="ms-input ms-editor w-100">
                        <InputText v-model="selectedExamShift.exam_shift_code" placeholder="Nhập mã ca thi"
                                   :class="{ 'error': invalidExamShift['exam_shift_code'] }"
                                   @keypress="handlerInputCode"
                                   @input="modeGenerateExamShift = selectedExamShift.exam_shift_code ? false : true;"/>
                        <div class="error-text" v-if="invalidExamShift['exam_shift_code']">
                            {{ invalidExamShift['exam_shift_code'] }}
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
                        Ngày bắt đầu
                        <span class="required">*</span>
                    </div>
                    <div class="ms-input ms-editor w-100">
                        <Calendar v-model="selectedExamShift.start_date" dataType="string"
                                  :class="{ 'error': invalidExamShift['start_date'] }"
                                  :min="exam.start_date"
                                  :max-date="exam.end_date"
                                  locale="vi" dateFormat="dd/mm/yy" class="w-full" id="calendar-24h"
                                  showTime hourFormat="24" showIcon
                                  placeholder="Chọn ngày bắt đầu"/>
                        <div class="error-text" v-if="invalidExamShift['start_date']">
                            {{ invalidExamShift['start_date'] }}
                        </div>
                    </div>
                </div>
                <div class="flex1 mr-10">
                    <div class="form-group-label d-flex label-form">
                        Ngày kết thúc
                        <span class="required">*</span>
                    </div>
                    <div class="ms-input ms-editor w-100">
                        <Calendar v-model="selectedExamShift.end_date" dataType="string"
                                  :class="{ 'error': invalidExamShift['end_date'] }"
                                  :minDate="selectedExamShift.start_date" :max-date="exam.end_date"
                                  :disabled="selectedExamShift.start_date == null"
                                  locale="vi" dateFormat="dd/mm/yy" class="w-full" id="calendar-24h"
                                  showTime hourFormat="24" showIcon
                                  placeholder="Chọn ngày kết thúc"/>
                        <div class="error-text" v-if="invalidExamShift['end_date']">
                            {{ invalidExamShift['end_date'] }}
                        </div>
                    </div>
                </div>
                <div class="flex1 mr-10">
                    <div class="form-group-label d-flex label-form">
                        Phòng thi
                        <span class="required">*</span>
                    </div>
                    <div class="ms-input ms-editor w-100">
                        <MultiSelect v-model="selectedExamShift.departments"
                                     :class="{ 'error': invalidExamShift['departments'] }" :options="departments"
                                     placeholder="Chọn phòng thi" optionLabel="department_name"
                                     optionValue="id"/>
                        <div class="error-text" v-if="invalidExamShift['departments']">
                            {{ invalidExamShift['departments'] }}
                        </div>
                    </div>
                    <div class="flex1">
                        <div class="ms-input ms-editor w-100"></div>
                    </div>
                </div>
                <div class="flex1">
                    <div class="form-group-label d-flex label-form">
                        Đề thi
                        <span class="required">*</span>
                        <div class="icon16 icon-note has-tooltip"
                             v-tooltip.top="{ value: 'Thiết lập đề thi để sử dụng', escape: true, class: 'custom-error' }">
                        </div>
                    </div>
                    <div class="ms-input ms-editor w-100">
                        <MultiSelect v-model="selectedExamShift.exam_bank_id" :options="examBankSetting"
                                     checkboxIcon="test"
                                     placeholder="Chọn đề thi" optionLabel="exam_bank_name" optionValue="id"
                                     :class="{ 'error': invalidExamShift['exam_bank_id'] }"/>
                        <div class="error-text" v-if="invalidExamShift['exam_bank_id']">
                            {{ invalidExamShift['exam_bank_id'] }}
                        </div>
                    </div>
                    <div class="flex1">
                        <div class="ms-input ms-editor w-100"></div>
                    </div>
                </div>
            </div>
            <div class="form-group flex-row">
                <div class="flex1">
                    <div class="form-group-label d-flex label-form">
                        Mô tả
                    </div>
                    <div class="ms-input ms-editor w-100">
                        <Textarea v-model="selectedExamShift.note" autoResize rows="5" cols="30"
                                  placeholder="Nhập ghi chú cho ca thi"/>
                        <div class="error-text">
                        </div>
                    </div>
                    <div class="flex1">
                        <div class="ms-input ms-editor w-100"></div>
                    </div>
                </div>
            </div>

        </div>
        <template #footer>
            <Button label="Đóng" class="ms-btn btn detail-button secondary" @click="examShiftDialogVisible = false"/>
            <Button @click="saveExamShift" @keyup.enter="saveExamShift" :disabled="isDisabledExamShift"
                    class="ms-btn primary blue d-flex justify-content-center ms-btn_search ps-3 pe-3 gap-2">
                <div class="fw-semibold">Lưu</div>
            </Button>
        </template>
    </Dialog>

    <Dialog v-model:visible="warningVisible" modal closeOnEscape :style="{ width: '25vw', height: '20vh' }"
            header="Thông báo">
        <div class="w-full flex flex-column">
            <span> Cập nhật thông tin đề thi, phòng thi sẽ làm mất dữ liệu kết quả bài thi. Bạn có chắc chắn muốn thay đổi
                không? </span>
        </div>
        <template #footer>
            <Button label="Không" class="ms-button btn detail-button secondary"
                    @click="isLoadingPopupExamShift = false; warningVisible = false"/>
            <Button label="Tiếp tục" class="ms-button btn detail-button primary" @click="updateExamShift(true)"/>
        </template>
    </Dialog>

    <Dialog v-model:visible="isPopupDelete" modal closeOnEscape :style="{ width: '25vw' }" header="Xóa ca thi">
        <TheLoadingProgress v-if="isLoadingDelete"/>
        <div class="w-full flex flex-column" style="line-height: 1.5;">
            <span>Ca thi <b>{{
                    selectedExamShift.exam_shift_code
                }}</b> có thể liên quan đến kết quả các bài thi. Bạn có chắc chắn muốn
                xóa
                không?</span>
        </div>
        <template #footer>
            <Button
                class="ms-btn secondary d-flex justify-content-center ms-btn_search ps-3 pe-3 gap-2" @click="isPopupDelete = false">
                <div class="">Không</div>
            </Button>
            <Button @click="handlerDelete"
                    class="ms-btn danger d-flex justify-content-center ms-btn_search ps-3 pe-3 gap-2">
                <div class="">Xóa đề thi</div>
            </Button>
        </template>
    </Dialog>
</template>

<script>
import Toast from 'primevue/toast';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import MultiSelect from 'primevue/multiselect';
import Dialog from 'primevue/dialog';
import Skeleton from 'primevue/skeleton';
import Calendar from 'primevue/calendar';
import {getExamBankSetting} from "/api/exam-bank";
import {generateCode, convertTimezoneToDatetime, convertTime} from '@/common/functions';
import {saveData, getDataDepartment} from '/api/department';
import {
    saveExamManager,
    checkExamShiftCodeExits,
    updateExamShift,
    updateExamManager,
    insertExamShift,
    getExamShifts,
    deleteExamShift
} from '/api/exam-manager';
import TheLoadingProgress from '@/components/LoadingProgress.vue';
import {MESSAGE, RESPONSE_STATUS} from "@/common/enums";

export default {
    emits: ["showExamManager", "showToast", "loadExamManager"],
    props: {
        modeModal: {
            default: null
        },
        selectedData: {
            default: null
        }
    },
    components: {
        MultiSelect,
        InputText,
        Dropdown,
        DataTable,
        Column,
        Dialog,
        Skeleton,
        Button,
        Calendar,
        Textarea,
        Toast,
        TheLoadingProgress
    },
    data() {
        return {
            exam: {
                id: null,
                exam_code: null,
                exam_name: null,
                start_date: null,
                end_date: null,
                note: null,
                listExamShift: [],
            },
            isDisabledExamManager: false,
            objectLoading: {},
            isDisabledExamShift: false,
            isLoading: false,
            isCollapsed: false,

            isPopupDelete: false,
            isLoadingPopupExamShift: false,
            isLoadingDelete: false,
            warningVisible: false,
            defaultExamShift: {
                exam_shift_id: null,
                exam_shift_code: null,
                exam_shift_name: null,
                start_date: null,
                end_date: null,
                departments: null,
                exam_bank_id: null,
                note: null
            },

            isLoadingExamShift: false,

            examResult: [],
            examshift: {
                exam_shift_id: null,
                exam_shift_code: null,
                exam_shift_name: null,
                start_date: null,
                end_date: null,
                departments: null,
                exam_bank_id: null,
                note: null
            },

            //chi tiết ca thi
            examshiftdetail: {
                ExamShiftDetailId: null,
                exam_shift_id: null,
                departmentsId: null,
                exam_bank_id: null,
            },

            selectedDataProp: {},
            examBankSetting: [],

            selectedExamShift: {
                exam_shift_id: null,
                exam_shift_code: null,
                exam_shift_name: null,
                start_date: null,
                end_date: null,
                departments: null,
                exam_bank_id: null,
                note: null
            },

            selectedListExamShift: null,

            departments: [],

            listExamShift: [],

            invalidExamShift: [],
            examShiftDialogVisible: false,

            invalidExamManager: [],

            modeGenerateExamManager: true, //cho phép sinh mã theo tên

            modeGenerateExamShift: true, //sinh mã ca thi

            modeExamShiftModal: this.FormMode.INSERT,
        }
    },

    methods: {
        /**
         * Validate mã phòng thi
         * Ko cho phép nhập các kí tự đặc biệt
         * @param {*} event
         */
        handlerInputdepartmentsCode(event) {
            let pattern = /[\W_]/g;
            let res = event.key.match(pattern);
            if (res) {
                event.preventDefault();
            }
        },

        /**
         * Lưu ca thi
         */
        async saveExamShift() {
            this.isDisabledExamShift = true;
            if (await this.validateExamShift()) {
                this.isLoadingPopupExamShift = true;
                //trạng thái của form kì thi
                switch (this.modeModal) {
                    //thêm mới kì thi
                    case this.FormMode.INSERT:
                        this.isLoadingPopupExamShift = false;
                        this.listExamShift.push({...this.selectedExamShift});
                        this.showExamShiftDialogVisible();
                        this.isDisabledExamShift = false;
                        break;
                    //cập nhật kì thi
                    case this.FormMode.UPDATE:
                        switch (this.modeExamShiftModal) {
                            //thêm mới ca thi với kì thi đã có
                            case this.FormMode.INSERT:
                                insertExamShift({
                                    ...this.selectedExamShift,
                                    id: this.selectedDataProp.id
                                }).then(res => {
                                    this.$store.dispatch('handleSuccess', 'Thêm mới thành công');
                                    this.examshift = {...this.defaultExamShift};
                                    this.selectedExamShift = {...this.defaultExamShift};
                                    this.examShiftDialogVisible = false;
                                    this.loadExamShift();
                                }).catch(error => {
                                    if (error.response.status === RESPONSE_STATUS.HTTP_UNPROCESSABLE_ENTITY) {
                                        for (var itemError in error.response.data.errors) {
                                            console.log(error.response.data.errors);
                                            this.invalidExamShift[itemError] = error.response.data.errors[itemError][0];
                                        }
                                    }
                                }).finally(() => {
                                    this.isDisabledExamShift = false;
                                    this.isLoadingPopupExamShift = false;
                                })
                                break;
                            //cập nhật ca thi
                            case this.FormMode.UPDATE:
                                if (JSON.stringify(this.selectedExamShift) !== JSON.stringify(this.examshift)) {
                                    // this.warningVisible = true;
                                    //thay đổi đề thi hoặc phòng thi
                                    if (JSON.stringify(this.selectedExamShift.departments) !== JSON.stringify(this.examshift.departments) || JSON.stringify(this.selectedExamShift.exam_bank_id) !== JSON.stringify(this.examshift.exam_bank_id)) {
                                        this.warningVisible = true;
                                        this.isDisabledExamShift = false;
                                    } else {
                                        this.updateExamShift();
                                    }
                                } else {
                                    this.showExamShiftDialogVisible();
                                    this.isDisabledExamShift = false;
                                    this.isLoadingPopupExamShift = false;
                                }
                                break;
                            default:
                                break;
                        }
                        break;
                    default:
                        break;
                }
            } else {
                this.isLoadingPopupExamShift = false;
                this.isDisabledExamShift = false;
            }
        },

        /**
         * Trước khi ẩn form ca thi
         */
        afterHideExamShiftDialog() {
            this.examshift = {...this.defaultExamShift};
            this.selectedExamShift = {...this.defaultExamShift};
            this.modeExamShiftModal = this.FormMode.INSERT;
            this.isLoadingExamShift = false;
        },

        /**
         * Call api cập nhật ca thi
         * @param {*} flag true: xóa chi tiết ca thi, false: không xóa
         */
        async updateExamShift(flag = false) {
            await updateExamShift({...this.selectedExamShift, id: this.selectedDataProp.id, Flag: flag})
                .then(res => {
                    this.$store.dispatch('handleSuccess', MESSAGE.HTTP_UPDATE_OK);
                    this.examshift = {...this.defaultExamShift};
                    this.selectedExamShift = {...this.defaultExamShift};
                    this.examShiftDialogVisible = false;
                    this.loadExamShift();
                }).catch(error => {
                    if (error.response.status === RESPONSE_STATUS.HTTP_UNPROCESSABLE_ENTITY) {
                        for (var itemError in error.response.data.errors) {
                            console.log(error.response.data.errors);
                            this.invalidExamShift[itemError] = error.response.data.errors[itemError][0];
                        }
                    }
                }).finally(() => {
                    this.isLoadingPopupExamShift = false;
                    this.warningVisible = false;
                    this.isDisabledExamShift = false;
                })
        },

        /**
         * Lấy danh sách ca thi
         */
        async loadExamShift() {
            this.isLoading = true;
            this.listExamShift = [];
            await getExamShifts(this.selectedData).then(res => {
                this.listExamShift = res.data['exam_shifts'];
            }).catch(error => {
                console.log(error);
            }).finally(() => {
                setTimeout(() => {
                    this.isLoading = false;
                }, 300);
            })
        },

        /**
         * Ẩn/hiện form thêm ca thi
         */
        showExamShiftDialogVisible() {
            // if (!this.validateExamManager()) {
            //     this.$store.dispatch('handleError', 'Chưa chọn thông tin kì thi');
            //     return;
            // }
            this.examShiftDialogVisible = !this.examShiftDialogVisible;
            this.invalidExamShift = [];
        },

        /**
         * click nút lọc
         * author: DPQuy(13/11/2022)
         * **/
        btnFilterOnClick() {
            //gọi hàm show filterbox
            this.showFilterBox();
        },

        /**
         * Sự kiện chọn item ca thi
         */
        onRowSelectExamShift(data) {
            //mảng giá trị selected
            this.selectedExamShift = {...data};
            //convert datetime
            this.selectedExamShift.end_date = new Date(this.selectedExamShift.end_date);
            this.selectedExamShift.start_date = new Date(this.selectedExamShift.start_date);

            //sử dụng khi cập nhật kì thi
            if (this.modeModal === this.FormMode.UPDATE) {
                // danh sách phòng theo từng ca thi(lấy duy nhất vì 1 phòng sử dụng nhiều đề nên bị duplicate)
                this.selectedExamShift.departments = [...new Set(this.selectedExamShift.departments.map(item => item.id))];
                this.selectedExamShift.exam_shift_id = data.id;
                //danh sách ca thi
                this.selectedExamShift.exam_bank_id = [...new Set(this.selectedExamShift.exam_banks.map(item => item.id))];
            }
            this.examshift = {...this.selectedExamShift};
        },

        /**
         * Lấy danh sách phòng thi
         */
        async loadDepartments() {
            await getDataDepartment().then(res => {
                this.departments = res.data;
            }).catch(error => {
                console.log(error);
            })
        },

        /**
         * Xử lý hàm sinh mã theo tên
         */
        handlerGenerateCode(name) {
            if (name === 'exam' && this.modeGenerateExamManager) this.exam.exam_code = generateCode(this.exam.exam_name);
            if (name === 'examShift' && this.modeGenerateExamShift) {
                console.log(this.selectedExamShift.exam_shift_name)
                this.selectedExamShift.exam_shift_code = generateCode(this.selectedExamShift.exam_shift_name);
            }
        },

        /**
         * Validate mã
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
         * Xóa ca thi
         * @param {*} index
         */
        btnRemoveExamShift(index) {
            this.isPopupDelete = true;
        },

        /**
         * Click xóa ca thi
         */
        handlerDelete() {
            this.isLoadingDelete = true;
            switch (this.modeModal) {
                case this.FormMode.INSERT:
                    break;
                case this.FormMode.UPDATE:
                    deleteExamShift(this.selectedExamShift).then(res => {
                        this.$store.dispatch('handleSuccess', MESSAGE.HTTP_DELETE_OK);
                        this.loadExamShift();
                    }).catch(error => {

                    }).finally(() => {
                        this.examShiftDialogVisible = false;
                        this.isPopupDelete = false;
                        this.isLoadingDelete = false;
                    });
                    break;
                default:
                    break;
            }
        },

        /**
         * Đóng form
         */
        closeExamManagerPopup() {
            this.$emit('showExamManager');
        },

        /**
         * Click nút lưu kì thi
         */
        async saveExamManager() {
            this.isDisabledExamManager = true;
            if (this.validateExamManager()) {
                try {
                    switch (this.modeModal) {
                        case this.FormMode.INSERT:
                            this.exam.listExamShift = [];
                            this.exam.listExamShift = this.listExamShift;
                            saveExamManager(this.exam).then(res => {
                                this.$emit("showExamManager");
                                this.$emit("loadExamManager");
                            }).then(res => {
                                this.$store.dispatch('handleSuccess', this.Message.HTTP_INSERT_OK);
                            })
                                .catch(error => {
                                    if (error.response.status === RESPONSE_STATUS.HTTP_UNPROCESSABLE_ENTITY) {
                                        for (var itemError in error.response.data.errors) {
                                            console.log(error.response.data.errors);
                                            this.invalidExamManager[itemError] = error.response.data.errors[itemError][0];
                                        }
                                    }
                                }).finally(() => {
                                this.isDisabledExamManager = false;
                            })
                            break;
                        case this.FormMode.UPDATE:
                            //kiểm tra dữ liệu có thay đổi hay không
                            //1.có thay đổi
                            if (JSON.stringify({
                                exam_code: this.selectedDataProp.exam_code,
                                exam_name: this.selectedDataProp.exam_name,
                                start_date: this.selectedDataProp.start_date,
                                end_date: this.selectedDataProp.end_date,
                                note: this.selectedDataProp.note,
                            }) !== JSON.stringify({
                                exam_code: this.exam.exam_code,
                                exam_name: this.exam.exam_name,
                                start_date: this.exam.start_date,
                                end_date: this.exam.end_date,
                                note: this.exam.note,
                            })) {
                                await updateExamManager({
                                    id: this.exam.id,
                                    exam_code: this.exam.exam_code,
                                    exam_name: this.exam.exam_name,
                                    start_date: this.exam.start_date,
                                    end_date: this.exam.end_date,
                                    note: this.exam.note,
                                }).then(res => {
                                    this.$emit("showExamManager");
                                    this.$emit("loadExamManager");
                                    this.$store.dispatch('handleSuccess', this.Message.HTTP_UPDATE_OK);
                                }).catch(error => {
                                    console.log(error);
                                }).finally(() => {
                                    this.isDisabledExamManager = false;
                                });
                            } else {
                                this.isDisabledExamManager = false;
                            }
                            break;
                        default:
                            this.isDisabledExamManager = false;
                            break;
                    }
                } catch (error) {
                    this.isDisabledExamManager = false;
                    console.log(error);
                }
            } else {
                this.isDisabledExamManager = false;
            }
        },

        /**
         * Validate kì thi
         */
        validateExamManager() {
            this.invalidData = [];
            var invalid = true;

            if (this.exam.exam_code == null || String(this.exam.exam_code).trim() == '') {
                this.invalidExamManager['exam_code'] = 'Mã kì thi không được để trống';
                invalid = false;
            }

            if (this.exam.exam_name == null || String(this.exam.exam_name).trim() == '') {
                this.invalidExamManager['exam_name'] = 'Tên kì thi không được để trống';
                invalid = false;
            }

            if (this.exam.start_date == null) {
                this.invalidExamManager['start_date'] = 'Ngày bắt đầu kì thi không được để trống';
                invalid = false;
            }

            if (this.exam.end_date == null) {
                this.invalidExamManager['end_date'] = 'Ngày kết thúc kì thi không được để trống';
                invalid = false;
            }

            if (this.exam.end_date != null && this.exam.start_date != null && this.exam.end_date.getTime() - this.exam.start_date.getTime() < 2 * 60 * 60 * 1000) {
                this.invalidExamManager['end_date'] = 'Thời gian thi tối thiểu phải lớn hơn 2h';
                invalid = false;
            }
            if (!invalid) return invalid;

            if (this.listExamShift.length === 0) {
                this.invalidExamManager['ExamShift'] = 'Ca thi không được để trống';
                invalid = false;
            }


            return invalid;
        },

        functionTest() {
            console.log(convertTimezoneToDatetime(this.exam.end_date.getTime()))
        },

        /**
         * Validate ca thi
         */
        async validateExamShift() {
            this.invalidExamShift = [];
            var invalid = true;
            this.isLoadingPopupExamShift = true;
            if (this.selectedExamShift.exam_shift_code == null || String(this.selectedExamShift.exam_shift_code).trim() === '') {
                this.invalidExamShift['exam_shift_code'] = 'Mã ca thi không được để trống';
                invalid = false;
            }

            if (this.selectedExamShift.exam_shift_name == null || String(this.selectedExamShift.exam_shift_name).trim() === '') {
                this.invalidExamShift['exam_shift_name'] = 'Tên ca thi không được để trống';
                invalid = false;
            }

            if (this.selectedExamShift.start_date == null) {
                this.invalidExamShift['start_date'] = 'Ngày bắt đầu không được để trống';
                invalid = false;
            }
            if (this.selectedExamShift.end_date == null) {
                this.invalidExamShift['end_date'] = 'Ngày kết thúc không được để trống';
                invalid = false;
            }

            if (this.selectedExamShift.start_date != null && this.selectedExamShift.end_date != null && this.selectedExamShift.end_date.getTime() - this.selectedExamShift.start_date.getTime() < 2 * 60 * 60 * 1000) {
                this.invalidExamShift['end_date'] = 'Thời gian tối tiểu là 2h';
                invalid = false;
            }

            if (this.selectedExamShift.departments == null) {
                this.invalidExamShift['departments'] = 'Phòng thi không được để trống';
                invalid = false;
            }

            if (this.selectedExamShift.exam_bank_id == null) {
                this.invalidExamShift['exam_bank_id'] = 'Đề thi không được để trống';
                invalid = false;
            }
            if (!invalid) return;

            var exam_shift_code = this.selectedExamShift.exam_shift_code;
            var valObj = this.listExamShift.filter(function (item) {
                if (item.exam_shift_code === exam_shift_code) return item;
            });
            if (valObj.length && this.modeExamShiftModal !== this.FormMode.UPDATE) {
                this.invalidExamShift['exam_shift_code'] = 'Mã ca thi đã tồn tại';
                invalid = false;
            } else if (this.selectedExamShift.exam_shift_code != null || String(this.selectedExamShift.exam_shift_code).trim() === '') {
                //gọi api kiểm tra mã
                await checkExamShiftCodeExits({
                    data: this.selectedExamShift,
                    mode: this.modeExamShiftModal
                }).then(res => {
                    if (res.data.result) {
                        this.invalidExamShift['exam_shift_code'] = 'Mã ca thi đã tồn tại';
                        invalid = false;
                    }
                }).catch(error => {
                    this.$store.dispatch('handleError');
                }).finally(() => {
                    setTimeout(() => {
                        this.isLoadingPopupExamShift = false;
                    }, 750);
                })
            }
            return invalid;
        },

        /**
         * Event xử lý data selected
         */
        handlerPropData() {
            if (this.modeModal === this.FormMode.UPDATE) {
                this.isLoading = true;
                this.selectedDataProp = {...this.selectedData};
                getExamShifts(this.selectedDataProp).then(res => {
                    this.listExamShift = res.data['exam_shifts'];
                }).catch(error => {
                    console.log(error)
                }).finally(() => {
                    setTimeout(() => {
                    })
                    this.isLoading = false;
                }, 300);
                this.exam = {...this.selectedDataProp};
                this.exam.start_date = new Date(this.selectedDataProp.start_date);
                this.exam.end_date = new Date(this.selectedDataProp.end_date);
                // this.selectedDataProp = {...this.exam};
                // this.selectedListExamShift = JSON.parse(this.exam.ExamShift);
                // //ca thi
                // this.listExamShift = this.getUniqueItems(this.selectedListExamShift, 'exam_shift_code');
            }
        },

        /**
         * Lấy danh sách đề thi đã thiết lập
         */
        getExamBankSetting() {
            getExamBankSetting().then(res => {
                this.examBankSetting = res.data;
            }).catch(error => {
                console.log(error)
            });
        },

        /**
         * Sự kiện nhấn phím
         * @param {*} event
         */
        handleKeyDown(event) {
            if (event.keyCode === this.KeyCode.ENTER) {
                //nếu form ca thi , form cảnh báo xóa, form cảnh báo thay đổi ko mở
                //thì gọi hàm lưu kì thi
                if (!this.examShiftDialogVisible && !this.isPopupDelete && !this.warningVisible) {
                    this.saveExamManager();
                }

                //hiển thị form cảnh báo thay đổi đề thi, phòng thi
                //gọi hàm cập nhật ca thi
                if (this.warningVisible) {
                    this.updateExamShift();
                }
            }

            if (event.keyCode === this.KeyCode.ESC) {
                //nếu form ca thi ko mở
                //đóng form kì thi
                if (!this.examShiftDialogVisible && !this.isPopupDelete) {
                    this.$emit("showExamManager");
                    this.$emit("loadExamManager");
                }
            }
        }
    },


    created() {
        // //lấy danh sách phòng thi
        this.loadDepartments();
        // //lấy danh sách đề thi đã thiết lập
        this.getExamBankSetting();
        // //thông tin cập nhật nếu ở trạng thái sửa
        this.handlerPropData();
        document.addEventListener('keydown', this.handleKeyDown);
    },


    beforeDestroy() {
        document.removeEventListener('keydown', this.handleKeyDown);
    },

}
</script>
<style scoped lang="scss">
.main-view-exam_manger {
    position: absolute;
    top: 0;
    background: #f4f5f8;
    z-index: 10;
    left: 0;
    flex: 1;
    width: 100%;
    height: 100%;
}

.left-box {
    width: 202px;
    margin-right: 18px;
    height: 100%;
}

.box,
.card {
    background-color: #fff;
    border-radius: 4px;
    box-shadow: 0 0 11px rgba(0, 0, 0, .08);
}

.list-tab-menu {
    padding: 33px 8px 0;
    overflow: auto;
    height: 100%;
    color: #707070;
}

.tab-item {
    margin-bottom: 15px;
    cursor: pointer;
}

.list-tab-menu .tab-item-parent.active {
    font-weight: 600;
    color: #ff6d00;
}

.list-tab-menu .tab-item-parent {
    height: 19px;
    margin-bottom: 4px;
    padding-left: 10px;
}

.content-body {
    padding: 17px 20px 12px 16px;
    overflow: auto;
}

.content-body .title-1 {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 15px;
    line-height: 26px;
    align-items: center;
}

.content-body .title-2 {
    font-size: 14px;
    font-weight: 700;
    line-height: 19px;
}

.content-footer {
    padding: 12px 24px;
    border-top: 1px solid #e0e0e0;
}

.left-grade {

    background: #fff;
    border: 1px solid rgb(221, 221, 221);
    border-bottom-right-radius: 4px;
    border-top-right-radius: 4px;
    height: 100%;

    .title {
        height: 50px;
        width: 100%;
        padding: 18px 12px 15px 15px;
        align-items: center;
        border-bottom: 1px solid rgb(221, 221, 221);

        .text {
            font-weight: 600;
        }

        .btn-collapse {
            cursor: pointer;
            transform: rotate(0deg);
            transition-duration: 0.4s;
        }
    }

    .outer {
        position: relative;
        display: none;
        width: 100%;
        cursor: pointer;
        margin: auto;

        .inner {
            width: 100vh;
            height: 45px;
            font-size: 16px;
            font-weight: 600;
            position: absolute;
            display: flex;
            left: 50%;
            top: 36vh;
        }

        .rotate {
            transform: translateX(-50%) translateY(-50%) rotate(-90deg);
        }
    }

    &.collage {
        width: 56px;

        .title {
            .text {
                display: none;
            }

            .btn-collapse {
                transform: rotate(180deg);
            }
        }

        .left-grade-content {
            display: none;
        }

        .outer {
            position: relative;
            display: inline-block;
            width: 100%;
            cursor: pointer;
            margin: auto;
        }
    }

    .left-grade-content {
        overflow: auto;
        height: 100%;
        padding: 16px;
    }
}

.right-grade {
    border: 1px solid rgb(221, 221, 221);
    border-radius: 4px;
    background: #fff;

    .p-panel-header {
        background: #fff !important;
        border: none !important;
    }

    .p-panel.customPanel .p-panel-header .p-panel-title {
        font-size: 16px;
        font-weight: 700;
    }

    button.p-panel-toggler[aria-label='customPanel'] span {
        width: max-content;
        display: flex;
        align-items: center;
    }

    button.p-panel-toggler[aria-label='customPanel'] span::before {
        margin-right: 4px;
        content: "";
        height: 16px !important;
        width: 16px !important;
    }


    button.p-panel-toggler[aria-label='customPanel'] span::after {
        width: max-content;
        color: #2979ff !important;
        cursor: pointer;
        border: none !important;
        font-weight: 400 !important;
        font-size: 14px;
    }


    .upload-container .info-file {
        padding: 20px;
    }

    .info-file .icon-zip {
        width: 40px;
        height: 40px;
        background: url('@public/assets/icons/zip.svg') no-repeat;
        margin-right: 10px;
        background-position: center;
        background-size: 100%;
    }

    .left-box {
        width: 202px;
        margin-right: 18px;
        height: 100%;
    }

    .box,
    .card {
        background-color: #fff;
        border-radius: 4px;
        box-shadow: 0 0 11px rgba(0, 0, 0, .08);
    }

    .list-tab-menu {
        padding: 25px 8px 0;
        overflow: auto;
        height: 100%;
        color: #707070;
    }

    .p-panel {
        &.customPanel {
            .p-panel-content {
                border: unset;
            }
        }
    }

    .tab-item {
        margin-bottom: 15px;
        cursor: pointer;
    }

    .list-tab-menu .tab-item-parent.active {
        font-weight: 600;
        color: #ff6d00;
    }

    .list-tab-menu .tab-item-parent:hover {
        border-radius: 4px;
        background-color: #fbe9e7;
        color: #ff6d00;
    }

    .grid-nodata-content {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        width: 100%;
        background-color: #fff;
        border-radius: 4px;
    }

    .grid-nodata-content .img-nodata {
        background-image: url('@public/assets/icons/ic_empty_grid.681d1e47.svg');
        height: 160px;
        width: 260px;
        background-position: 50%;
    }

    .description-nodata {
        text-align: center;
        font-size: 14px;
        color: #182d4b;
        padding-bottom: 8px;
    }

    .grid-nodata-content .description-nodata {
        color: #9e9fab;
        padding-bottom: 8px;
    }

    .list-tab-menu .tab-item-parent {
        height: 36px;
        padding-left: 10px;
        padding-bottom: 10px;
        padding-top: 10px;
    }

    .content-body {
        padding: 17px 20px 12px 16px;
        overflow: auto;
    }

    .content-body .title-1 {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 15px;
        line-height: 26px;
        align-items: center;
    }

    .content-body .title-2 {
        font-size: 14px;
        font-weight: 700;
        line-height: 19px;
    }

    /* .content-footer {
        background-color: #fbfbfe;
        padding: 12px 24px;
    } */
    .p-button-label {
        padding: 0 16px;
    }

    .tab-item-child {
        padding: 10px 0 0px 20px;
    }

    .tab-item .tab-item-child:not(:first-child) {
        padding-top: 0;
    }

    .box.left-box {
        width: 13vw;
    }

    .p-button .export-excel {
        display: inline-block;
        margin: -7px 7px -7px 12px;
    }

    .export-excel {
        width: 20px;
        height: 20px;
    }


    .p-button .export-excel ~ .text {
        padding-right: 16px;
        padding-left: 0;
    }

    .customPanel.p-panel.p-panel-toggleable .p-panel-header {
        padding: 0px 14px 14px 0 !important;
    }

    .upload-container .no-file.file-xlsx {
        background: url('@public/assets/icons/add_attachment.c604b695.svg') no-repeat;
        width: 334px;
        height: 110px;
        margin-top: 20px;
        background-size: contain;
        background-position: center;
        margin-bottom: 20px;
    }

    .upload-wrapper {
        border: 1px dashed #ddd;
        border-radius: 4px;
        display: flex;
        height: 329px;
        justify-content: center;
    }

    .import-attachment-container {
        border-radius: 4px;
        height: 329px;
        cursor: pointer;

        .file-info {
            width: 100%;
            height: 48px;
            align-items: center;
            padding: 0 16px;
            border-bottom: 1px dashed #ddd;

            .file-icon {
                width: 16px;
                height: 16px;
                background: transparent url('@public/assets/icons/ic_file_gray.84c03213.svg') no-repeat -2px 0;
                margin-right: 8px;
            }

            .file-name, .main-content .file-info .file-size {
                text-overflow: ellipsis;
                padding-right: 16px;
                overflow: hidden;
                white-space: nowrap;
            }

            .file-size {
                width: 95px;
                text-overflow: ellipsis;
                padding-right: 16px;
                overflow: hidden;
                white-space: nowrap;
            }

            .file-accepted {
                padding-right: 16px;
                white-space: nowrap;
            }

            .icon-success {
                width: 16px;
                height: 16px;
                background: transparent url('@public/assets/icons/ic_check_green.d1ac113b.svg') no-repeat;
                margin-right: 8px;
            }

            .change-file {
                width: 100px;
                overflow: hidden;
                white-space: nowrap;

                &.text-link {
                    color: #2979ff !important;
                    cursor: pointer;
                    border: none !important;
                    font-weight: 400 !important;
                }
            }
        }

        .file-caution {
            width: 100%;
            height: calc(100% - 48px);
        }

        .file-caution-img {
            width: 100%;
            height: calc(100% - 74px);
            background: url('@public/assets/icons/img_import.7804e3d3.svg') no-repeat 50%;
        }
    }

    .file-zip {
        background: url('@public/assets/icons/upload-zip.svg') no-repeat;
        width: 334px;
        height: 110px;
        margin-top: 20px;
        background-size: contain;
        background-position: center;
        margin-bottom: 20px;
    }
}
</style>
