<template>
    <div class="form-list flex-grow-1 d-flex">
        <div class="d-flex flex-column flex-grow-1">
            <div class="d-flex flex-row title-box">
                <div class="list-title flex-grow-1 text-start">Danh sách đề thi</div>
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
                        @click="isShowModal = !isShowModal, modeGenerate = true, modeModal = FormMode.INSERT"
                        class="ms-btn primary d-flex justify-content-center flex-grow-1 ms-btn_search ps-3 pe-3 gap-2">
                        <div class="icon24 icon-add-white"></div>
                        <div class="fw-semibold">Thêm đề thi</div>
                    </Button>
                </div>
            </div>
            <div class="box list-content flex-grow-1 flex-row">
                <DataTable paginator :rows="10" :rowsPerPageOptions="[5, 10, 20, 50]" class="flex1 flex-column"
                           :class="{ 'loading': isLoading }" :loading="isLoading"
                           :value="isLoading ? Array.from({ length: 8 }, () => ({ ...objectLoading })) : examBankData"
                           currentPageReportTemplate="{first} to {last} of {totalRecords}"
                           paginatorTemplate="FirstPageLink PrevPageLink flex1 CurrentPageReport NextPageLink LastPageLink RowsPerPageDropdown"
                           @rowDblclick="onRowSelect($event.data), isShowModal = true" tableStyle="min-width: 100%"
                           rowHover>
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
                    <Column field="exam_bank_name" style="width: 25vw;" header="Tên đề thi">
                        <template #body="{ data, field, slotProps }">
                            <div v-if="!isLoading"> {{ data[field] }}</div>
                            <div v-else>
                                <Skeleton height="18px" class="mb-2"></Skeleton>
                            </div>
                        </template>
                    </Column>
                    <Column field="exam_bank_code" style="width: 20vw;" dataKey="id" header="Mã đề thi">
                        <template #body="{ data, field, slotProps }">
                            <div v-if="!isLoading"> {{ data[field] }}</div>
                            <div v-else>
                                <Skeleton height="18px" class="mb-2"></Skeleton>
                            </div>
                        </template>
                    </Column>
                    <Column dataKey="id" header="Trạng thái dề thi" style="width: 200px;">
                        <template #body="{ data, field, slotProps }">
                            <div v-if="!isLoading">
                                <div class="d-flex status-ctn max-content" v-if="data['criterias'].length > 0"
                                     style="background-color: rgb(229, 250, 237);">
                                    <div class="status-dot" style="background-color: rgb(0, 200, 83);"></div>
                                    <div class="status-text" style="color: rgb(0, 200, 83);">Đã thiết lập</div>
                                </div>
                                <div class="d-flex status-ctn max-content" v-else
                                     style="background-color: rgb(254, 243, 231);">
                                    <div class="status-dot" style="background-color: rgb(243, 141, 21);"></div>
                                    <div class="status-text" style="color: rgb(243, 141, 21);">Chưa thiết lập</div>
                                </div>
                            </div>
                            <div v-else>
                                <Skeleton height="18px" class="mb-2"></Skeleton>
                            </div>
                        </template>
                    </Column>
                    <Column dataKey="id" header="Trạng thái sử dụng" style="width: 200px;">
                        <template #body="{ data, field, slotProps }">
                            <div v-if="!isLoading">
                                <div class="d-flex status-ctn max-content" v-if="data['departments'].length > 0"
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
                                <Button
                                    class="ms-btn white ms-1 rounded-circle d-flex icon-only icon-only-border justify-content-center ms-btn_search"
                                    @click="toggle($event, slotProps.data)" aria-haspopup="true"
                                    aria-controls="overlay_menu">
                                    <div class="icon-only icon24 more"></div>
                                </Button>
                                <Menu ref="menu" id="overlay_menu" :model="actionItems" :popup="true"/>
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
    <Dialog v-model:visible="isPopupDelete" modal closeOnEscape :style="{ width: '25vw' }" header="Xóa đề thi">
        <TheLoadingProgress v-if="isLoadingDelete"/>
        <div class="w-full flex flex-column" style="line-height: 1.5;">
            <span> Bạn có chắc chắn muốn xóa đề thi <b>{{ selectedData.exam_bank_code }}</b> không?</span>
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

    <Dialog v-model:visible="isShowModal" @keydown.enter.prevent="doSave" modal
            :header="modeModal == FormMode.INSERT ? 'Thêm đề thi' : 'Sửa đề thi'" @afterHide="afterHide"
            :style="{ width: '35vw' }" closeOnEscape>
        <TheLoadingProgress v-if="popupLoading"/>
        <div class="w-full flex flex-column">
            <div class="form-group flex-row">
                <div class="flex1 mr-10">
                    <div class="form-group-label d-flex label-form">
                        Tên đề thi
                        <span class="required">*</span>
                    </div>
                    <div class="ms-input ms-editor w-100">
                        <InputText v-model="selectedData.exam_bank_name"
                                   :class="{ 'error': invalidData['exam_bank_name'] }"
                                   @input="handlerGenerateCode"
                                   placeholder="Nhập tên đề thi"/>
                        <div class="error-text" v-if="invalidData['exam_bank_name']">
                            {{ invalidData['exam_bank_name'] }}
                        </div>
                    </div>
                </div>
                <div class="flex1 mr-10">
                    <div class="form-group-label d-flex label-form">
                        Mã đề thi
                        <span class="required">*</span>
                    </div>
                    <div class="ms-input ms-editor w-100">
                        <InputText v-model="selectedData.exam_bank_code"
                                   :class="{ 'error': invalidData['exam_bank_code'] }"
                                   @keypress="handlerInputCode"
                                   @input="modeGenerate = selectedData.exam_bank_code ? false : true"
                                   placeholder="Nhập mã đề thi"/>
                        <div class="error-text" v-if="invalidData['exam_bank_code']">
                            {{ invalidData['exam_bank_code'] }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group flex-row">
                <div class="flex1 mr-10">
                    <div class="form-group-label d-flex label-form">
                        File tư liệu
                        <span class="required">*</span>
                    </div>
                    <div class="flex-grow-1">
                        <div class="content-step d-flex flex-column">
                            <div class="upload-container flex1 flex-center" v-if="!file.fileName"
                                 @click="$refs.fileInput.click()">
                                <div class="no-file d-flex">
                                    <input type="file" id="fileInput" ref="fileInput"
                                           @change="onFileChange('fileInput')"
                                           accept=".docx"
                                           hidden>
                                </div>
                            </div>
                            <div class="import-attachment-container flex1" v-else>
                                <div class="file-info d-flex">
                                    <div class="d-flex">
                                        <div class="file-icon text-left"></div>
                                        <div class="file-name flex text-left">{{ file.fileName }}</div>
                                    </div>
                                    <div class="file-size text-left"> {{ file.fileSize }}</div>
                                    <div class="file-accepted text-left d-flex">
                                        <div class="icon-success" v-if="file.success"></div>
                                        <div v-if="file.success">Hợp lệ</div>
                                        <div class="icon-unsuccess" v-if="!file.success"></div>
                                        <div v-if="!file.success">Không hợp lệ</div>
                                    </div>
                                    <input type="file" ref="fileInput1" hidden
                                           @change="onFileChange('fileInput1')"
                                           accept=".docx">
                                    <div class="change-file blue-text pointer text-left text-link"
                                         @click="$refs.fileInput1.click()"> Đổi tệp khác
                                    </div>
                                </div>
                                <div class="file-caution" v-if="file.success">
                                    <div class="file-caution-img"></div>
                                    <div class="file-caution-center mt-20">
                                        <!--                                        Lưu ý <span style="color: red;">*</span>: Bạn vui lòng chọn sheet tư-->
                                        <!--                                        liệu và dòng tiêu đề-->
                                        <!--                                        <br>của sheet đó.-->
                                    </div>
                                </div>
                                <div class="file-error" v-if="!file.success">
                                    <div class="file-error-title red-text"> Lý do không hợp lệ:</div>
                                    <div class="file-error-title red-text">- Dung lượng quá lớn</div>
                                </div>
                            </div>
                            <div class="error-text" v-if="invalidData['fileData']">
                                {{ invalidData['fileData'] }}
                            </div>
                        </div>
                        <div class="ms-input ms-editor w-100"></div>
                    </div>
                </div>
            </div>
        </div>
        <template #footer>
            <div class="d-flex flex-row">
                <div class="flex1"></div>
                <Button
                    @click="isShowModal = false, selectedData = defaultData"
                    class="ms-btn secondary blue me-2 d-flex justify-content-center ms-btn_search ps-3 pe-3 gap-2">
                    <div class="">Đóng</div>
                </Button>
                <Button @click="doSave" @keyup.enter.prevent="doSave"
                        class="ms-btn primary blue d-flex justify-content-center ms-btn_search ps-3 pe-3 gap-2">
                    <div class="">Lưu</div>
                </Button>
            </div>
        </template>
    </Dialog>

    <Dialog v-model:visible="warningVisible" modal closeOnEscape :style="{ width: '25vw', height: '20vh' }"
            header="Thông báo">
        <div class="w-full flex flex-column">
            <span> Đề thi <b>{{ selectedData.exam_bank_name }}</b> đang được sử dụng. Bạn không thể xóa.</span>
        </div>
        <template #footer>
            <Button
                @click="warningVisible = false"
                class="ms-btn secondary blue d-flex justify-content-center ms-btn_search ps-3 pe-3 gap-2">
                <div class="">Đóng</div>
            </Button>
        </template>
    </Dialog>
    <ExamSetup :selectedData="selectedData" v-if="isconfigureExam"/>
</template>

<script>
import vClickOutside from 'click-outside-vue3'
import InputText from 'primevue/inputtext';
import ExamPopup from '@/views/user/components/ExamPopup.vue';
import ExamSetup from '@/views/user/components/ExamSetup.vue';
import DataTable from 'primevue/datatable';
import Menu from 'primevue/menu';
import Auth from "../../../api/utils/auth";
import Dialog from 'primevue/dialog';
import Column from 'primevue/column';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Skeleton from 'primevue/skeleton';
import {generateCode} from '@/common/functions'
import Resumable from 'resumablejs';
import TheLoadingProgress from '@/components/LoadingProgress.vue'
import Button from 'primevue/button';
import {deleteExamBank, getExamBank, insertExamBank, updateExamBank} from '/api/exam-bank';
import {MESSAGE, RESPONSE_STATUS} from "@/common/enums";

export default {
    directives: {
        clickOutside: vClickOutside.directive
    },
    components: {
        ExamPopup,
        ExamSetup,
        DataTable,
        Menu,
        Column,
        Button,
        Dropdown,
        InputText,
        Dialog,
        InputNumber,
        TheLoadingProgress,
        Skeleton,
    },
    data() {
        return {
            search: null,
            selectedData: {
                id: null,
                exam_bank_code: null,
                exam_bank_name: null,
                file_size: null,
                file_name: null,
            },

            objSelectedData: {},
            objFileSelected: null,

            isLoadingDelete: false,
            popupLoading: false,

            warningVisible: false,
            file: {
                fileName: null,
                fileSize: null,
                success: true,
            },
            actionItems: [
                {
                    label: 'Thiết lập đề',
                    icon: 'pi pi-refresh',
                    command: () => {
                        this.activeMenuItem(1)
                    }
                },
                {
                    label: 'Xóa',
                    icon: 'pi pi-upload',
                    command: () => {
                        this.activeMenuItem(2)
                    }
                }
            ],

            defaultData: {
                id: null,
                exam_bank_code: null,
                exam_bank_name: null,
            },
            resumable: null,


            invalidData: {
                exam_bank_code: null,
                exam_bank_name: null,
                SheetIndexReference: null,
                RowReference: null,
                fileData: null
            },
            modeModal: this.FormMode.INSERT,
            modeGenerate: true,
            examBankData: [],
            isShowModal: false,
            isShowActions: false,
            isLoading: false,
            isPopupDelete: false,
            objectLoading: {},
            top: 0,
            left: 0,
            isconfigureExam: false,
        }
    },
    methods: {
        /**
         * Ẩn / hiện model thêm mới
         */
        showModal() {
            this.isShowModal = !this.isShowModal;
        },

        async doSave() {
            try {
                if (this.validateExamBank()) {
                    let data = {...this.selectedData};
                    this.resumable.opts.query.param = JSON.stringify(data);
                    this.popupLoading = true;
                    switch (this.modeModal) {
                        case this.FormMode.INSERT:
                            this.resumable.opts.target = 'http://localhost:9000/api/exam-banks';
                            this.resumable.addFile(this.selectedFile);
                            break;
                        case this.FormMode.UPDATE:
                            if (JSON.stringify(this.selectedData) !== JSON.stringify(this.objSelectedData) || JSON.stringify(this.file) !== JSON.stringify(this.objFileSelected)) {
                                //update file tư liệu
                                if (this.selectedFile) {
                                    this.resumable.opts.target = 'http://localhost:9000/api/exam-banks';
                                    this.resumable.addFile(this.selectedFile);
                                } else {
                                    this.popupLoading = true;
                                    await updateExamBank(data).then(res => {
                                        this.$store.dispatch('handleSuccess', MESSAGE.HTTP_UPDATE_OK)
                                        this.loadExamBank();
                                        this.showModal();
                                    }).catch(error => {
                                        if (error.response.status === RESPONSE_STATUS.HTTP_UNPROCESSABLE_ENTITY) {
                                            for (let itemError in error.response.data.errors) {
                                                this.invalidData[itemError] = error.response.data.errors[itemError][0];
                                            }
                                        } else {
                                            this.$store.dispatch('handleServerError')
                                        }
                                    }).finally(() => {
                                        this.popupLoading = false
                                    });
                                }
                            } else {
                                this.popupLoading = false;
                                this.showModal();
                            }
                            break;
                        default:
                            break;
                    }
                }
            } catch (error) {
                this.popupLoading = false;
                console.log(error);
            }
        }
        ,

        /**
         * Click nút xóa
         */
        onRowDelete() {
            //đề thi đang được sử dụng
            if (this.selectedData.is_exist) {
                this.warningVisible = true;
            }
            //không sử dụng
            else {
                this.isPopupDelete = true;
            }
        }
        ,

        /**
         * Xử lý hàm sinh mã theo tên
         */
        handlerGenerateCode() {
            if (this.modeGenerate) this.selectedData.exam_bank_code = generateCode({value: this.selectedData.exam_bank_name});
        }
        ,

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
         * Ẩn hiện action row
         * @param {*} index
         * @param {*} data
         */
        showActions(index, data) {
            this.isShowActions = !this.isShowActions;
            this.selectedData = data;
            var position = this.$refs[`busstop${index}`].getBoundingClientRect();
            this.top = position.y + position.height;
            this.left = position.x - position.width / 2;
        },

        /**
         * Load dữ liệu ngân hàng đề thi
         */
        async loadExamBank() {
            this.isLoading = true;
            await getExamBank().then(res => {
                this.examBankData = res.data;
            }).catch(error => {

            }).finally(() => {
                setTimeout(() => {
                    this.isLoading = false;
                }, 750);
            });
        },

        /**
         * Click nút xóa phòng thi
         */
        handlerDelete() {
            this.isLoadingDelete = true;
            deleteExamBank(this.selectedData.id).then(res => {
                this.isPopupDelete = false;
                this.$store.dispatch('handleSuccess', MESSAGE.HTTP_DELETE_OK);
                this.loadExamBank();
            }).catch(error => {

            }).finally(() => {
                setTimeout(() => {
                    this.isLoadingDelete = false;
                }, 300);
            })
        },

        /**
         * sự kiện click menu item
         * @param index
         */
        activeMenuItem(index) {
            switch (index) {
                // click item thiết lập đề
                case 1:
                    this.$router.push({name: 'setup', params: {id: this.selectedData.id}});
                    break;
                // click item xóa
                case 2:
                    console.log(this.selectedData)
                    if (this.selectedData.departments.length > 0) {
                        this.warningVisible = true;
                        return
                    }
                    this.isPopupDelete = true;
                    break;
            }
        },

        /**
         * Ẩn form
         */
        afterHide() {
            this.selectedData = {...this.defaultData};
            this.file = {
                fileName: null,
                fileSize: null,
                success: true,
            };
            this.invalidData = [];
            this.exambank = {};
        },

        /**
         *  Validate dữ liệu
         *  @return boolean
         */
        validateExamBank() {
            let invalid = true;
            this.invalidData = [];
            if (this.selectedData.exam_bank_code == null || this.selectedData.exam_bank_code === '') {
                invalid = false;
                this.invalidData.exam_bank_code = 'Mã đề thi không được để trống';
            } else {
                this.invalidData.exam_bank_code = null;
            }
            if (this.selectedData.exam_bank_name == null || this.selectedData.exam_bank_name === '') {
                invalid = false;
                this.invalidData.exam_bank_name = 'Tên đề thi không được để trống';
            } else {
                this.invalidData.exam_bank_name = null;
            }
            if (!this.file.fileName) {
                invalid = false;
                this.invalidData.fileData = 'Vui lòng tải lên file tư liệu';
            } else {
                this.invalidData.fileData = null;
            }
            return invalid;
        },

        /**
         * Sự kiện thay đổi file
         * @param {*} ref ref input
         *
         */
        onFileChange(ref) {
            if (this.$refs[ref].files[0].type === "application/vnd.openxmlformats-officedocument.wordprocessingml.document") {
                this.selectedFile = this.$refs[ref].files[0];
                let _size = this.selectedFile.size;
                let fSExt = ['Bytes', 'KB', 'MB', 'GB'],
                    i = 0;
                while (_size > 900) {
                    _size /= 1024;
                    i++;
                }
                this.selectedData.file_size = (Math.round(_size * 100) / 100) + ' ' + fSExt[i];
                this.selectedData.file_name = this.selectedFile.name;

                this.file.fileSize = (Math.round(_size * 100) / 100) + ' ' + fSExt[i];
                this.file.fileName = this.selectedFile.name;
                //kiểm tra dung lượng file
                if (this.$refs[ref].files[0].size > 30 * 1024 * 1024) {
                    this.file.success = false;
                } else {
                    this.file.success = true;
                }
            } else {
                this.contentDialog = ' File tư liệu chỉ hỗ trợ định dạng *.docx';
                this.dialogVisible = true;
            }
        },

        /**
         * Thêm file
         * @param {*} file
         */
        onFileAdded(file) {
            this.resumable.upload();
        },


        /**
         * Tiến trình tải
         * @param {*} file
         */
        onFileProgress(file) {
        },

        /**
         * Upload thành công
         * @param {*} file
         * @param {*} response
         */
        onFileSuccess(file, response) {
            this.selectedFile = null;
            this.resumable.removeFile(file);
            this.popupLoading = false;
            this.showModal();
            let message = this.modeModal === this.FormMode.INSERT ? "Thêm đề thi thành công" : "Cập nhật đề thi thành công";
            this.$store.dispatch('handleSuccess', message);
            this.loadExamBank();
        },

        /**
         * Upload thất bại
         * @param {*} file
         * @param {*} message
         */
        onFileError(file, response) {
            try {
                this.popupLoading = false;
                this.resumable.removeFile(file);
                response = JSON.parse(response);
                if (response.data && response.data.errorCode === 422) {
                    for (let error in response.data.errors) {
                        this.invalidData[error] = response.data.errors[error][0];
                    }
                } else {
                }
            } catch (error) {
                console.log(error);
            }
        },

        /**
         * Khởi tạo Resumable upload file
         */
        createResumable() {
            this.resumable = new Resumable({
                target: 'http://localhost:9000/api/exam-banks',
                method: 'POST',
                query: {},
                withCredentials: true,
                headers: {
                    'Authorization': 'Bearer ' + Auth.getToken(),
                    // 'Access-Control-Allow-Credentials': true,
                },
                testChunks: false,
                throttleProgressCallbacks: 1,
                simultaneousUploads: 4,
            });
            this.resumable.on('fileAdded', this.onFileAdded);
            this.resumable.on('fileProgress', this.onFileProgress);
            this.resumable.on('fileSuccess', this.onFileSuccess);
            this.resumable.on('fileError', this.onFileError);
        },

        /**
         * Cập nhật đề thi
         */
        onRowUpdate() {
            //object để kiểm tra xem bản ghi có thay đối hay ko
            this.objSelectedData = {...this.selectedData};

            this.showModal(this.FormMode.UPDATE);
        },

        deleteRowSelect(data) {
            this.selectedData = {...data};
            this.isPopupDelete = true;
        },

        /**
         *
         */
        onRowSelect(data) {
            this.modeModal = this.FormMode.UPDATE;
            this.isShowModal = true;
            this.objSelectedData = {...data};
            this.selectedData = {...data};
            this.file.fileSize = this.selectedData.file_size
            this.file.fileName = this.selectedData.file_name
        },
        /**
         * mở action menu
         * @param event
         * @param data
         */
        toggle(event, data) {
            this.$refs.menu.toggle(event);
            this.selectedData = {...data};
        }
    },
    async created() {
        await this.loadExamBank();
    },

    mounted() {
        this.createResumable();
    },
}
</script>

<style lang="scss">
.content-step {
    height: 250px;

    .upload-container {
        border: 1px dashed #ddd;
        border-radius: 4px;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;

        .no-file {
            background: url('@public/assets/icons/add_attachment_docs.svg') no-repeat;
            width: 334px;
            height: 110px;
        }
    }

    .import-attachment-container {
        border-radius: 4px;
        border: 1px solid #ddd;
        height: 260px;
        margin-bottom: 16px;
        color: #333;
        cursor: pointer;

        .file-info {
            width: 100%;
            height: 48px;
            align-items: center;
            padding: 0 16px;
            justify-content: space-between;
            border-bottom: 1px solid #ddd;

            &.file-info .icon-success {
                width: 16px;
                height: 16px;
                background: transparent url('@public/assets/icons/ic_check_green.d1ac113b.svg') no-repeat;
                margin-right: 8px;
            }
        }

        .file-caution {
            width: 100%;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: calc(100% - 48px);
        }

        .file-caution-img {
            width: 100%;
            height: calc(100% - 74px);
            padding-top: 20px;
            background: url('@public/assets/icons/img_import.7804e3d3.svg') no-repeat 50%;
        }
    }
}
</style>
