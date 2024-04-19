<template>
    <div class="form-list flex-grow-1 d-flex">
        <div class="d-flex flex-column flex-grow-1">
            <div class="d-flex flex-row title-box">
                <LoadingProgress v-if="isLoading"/>
                <div class="list-title flex-grow-1 text-start">Thực hiện chấm thi</div>
                <div class="right-toolbar d-flex flex-row">
                </div>
            </div>
            <div class="flex-grow-1 flex-row row gx-0 gap-3">
                <div class="col-3 left-grade" :class="{'collage': isCollapsed}">
                    <div class="title text-start d-flex justify-content-between">
                        <div class="text">Thông tin kì thi</div>
                        <div class="btn-collapse icon-toggle icon24-only" @click="isCollapsed = true"></div>
                    </div>
                    <div class="w-100 h-100">
                        <div class="outer" @click="isCollapsed = false">
                            <div class="left-grade-content_collage inner rotate">
                                <div style="margin: auto;">Thông tin chấm thi</div>
                            </div>
                        </div>
                        <div class="left-grade-content">
                            <div class="group-form_box">
                                <div class="d-flex">
                                    <div class="label">Kì thi</div>
                                    <span class="required">*</span>
                                </div>
                                <div class="">
                                    <Dropdown v-model="selectedManager" :options="examManager"
                                              optionLabel="exam_name"
                                              placeholder="Kì thi"
                                              :disabled="this.activeStep !== 0"
                                              :class="{'error': invalidData['exam']}"
                                              @change="onChangeExamManager"
                                              class="ms-category text-start"/>
                                </div>
                                <div class="ms-error-text" v-if="invalidData['exam']">
                                    {{ invalidData['exam'] }}
                                </div>
                            </div>
                            <div class="group-form_box mt-3">
                                <div class="d-flex">
                                    <div class="label">Ca thi</div>
                                    <span class="required">*</span>
                                </div>
                                <div class="">
                                    <Dropdown v-model="selectedExamShift" :options="examShift"
                                              optionLabel="exam_shift_name"
                                              placeholder="Ca thi"
                                              :disabled="activeStep !== 0"
                                              :class="{'error': invalidData['exam_shift']}"
                                              @change="onChangeExamShift"
                                              class="ms-category text-start"/>
                                </div>
                                <div class="ms-error-text" v-if="invalidData['exam_shift']">
                                    {{ invalidData['exam_shift'] }}
                                </div>
                            </div>
                            <div class="group-form_box mt-3">
                                <div class="d-flex">
                                    <div class="label">Phòng thi</div>
                                    <span class="required">*</span>
                                </div>
                                <div class="">
                                    <Dropdown v-model="selectedDepartment" :options="department"
                                              optionLabel="department_name"
                                              @change="onChangeDepartment"
                                              placeholder="Phòng thi"
                                              :disabled="activeStep !== 0"
                                              :class="{'error': invalidData['department']}"
                                              class="ms-category text-start"/>
                                </div>
                                <div class="ms-error-text" v-if="invalidData['department']">
                                    {{ invalidData['department'] }}
                                </div>
                            </div>
                            <div class="group-form_box mt-4">
                                <div class="flex-grow-1 d-flex justify-content-center">
                                    <Button @click="refineExam" :disabled="selectedExamShift == null || activeStep != 0"
                                            class="ms-btn secondary d-flex justify-content-center flex-grow-1 ms-btn_search ps-3 pe-3 gap-2">
                                        <div class="">Tinh chỉnh đề thi</div>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="list-content flex1 flex-column right-grade">
                    <div class="content-body flex1 flex-column">
                        <div class="mb-20">
                            <Panel header="1. Thông tin file" :collapsed="examResult.length > 0" toggleable
                                   class="customPanel flex1" :toggle-button-props="{ 'aria-label': 'customPanel' }">
                                <Stepper linear :activeStep="activeStep">
                                    <StepperPanel header="File danh sách thi">
                                        <template #content="{ index, nextCallback }">
                                            <input type="file" hidden ref="file" accept=".xlsx" id="assetsFieldHandle"
                                                   @change="onChangeFile($event, valuesFile[0])">
                                            <div class="flex-grow-1 d-flex" @dragover="dragover" @dragleave="dragleave"
                                                 @drop="drop($event, valuesFile[0])">
                                                <label for="assetsFieldHandle" class="flex-grow-1 upload-wrapper">
                                                    <div class="d-flex flex-grow-1">
                                                        <div
                                                            class="upload-container flex-grow-1 d-flex justify-content-center align-items-center"
                                                            v-if="!this.valuesFile[0].FileSelected">
                                                            <div class="no-file d-flex file-xlsx">
                                                            </div>
                                                        </div>
                                                        <div class="import-attachment-container flex1"
                                                             v-if="this.valuesFile[0].FileSelected">
                                                            <div class="file-info d-flex">
                                                                <div class="d-flex text-truncate">
                                                                    <div class="file-icon text-left "
                                                                         style="width: 20px;"></div>
                                                                    <div class="file-name text-left">
                                                                        {{ this.valuesFile[0].FileSelected.name }}
                                                                    </div>
                                                                </div>
                                                                <div class="file-size text-left">
                                                                    {{
                                                                        formatSize(this.valuesFile[0].FileSelected.size)
                                                                    }}
                                                                </div>
                                                                <div
                                                                    class="file-accepted text-left d-flex justify-content-between">
                                                                    <div class="icon-success" v-if="FileSuccess"></div>
                                                                    <div v-if="FileSuccess">Hợp lệ</div>
                                                                    <div class="icon-unsuccess"
                                                                         v-if="!FileSuccess"></div>
                                                                    <div v-if="!FileSuccess">Không hợp lệ</div>
                                                                </div>
                                                                <div
                                                                    class="change-file blue-text pointer text-left text-link mw-maxcontent justify-content-end">
                                                                    Đổi
                                                                    tệp khác
                                                                </div>
                                                            </div>
                                                            <div class="file-caution" v-if="FileSuccess">
                                                                <div class="file-caution-img">
                                                                </div>
                                                                <div class="file-caution-center mt-20 text-center">
                                                                    Lưu ý
                                                                    <span data-v-6d95cd2a="" data-v-30ce9692=""
                                                                          style="color: red;">*</span>
                                                                    : Bạn vui lòng chọn sheet danh sách và
                                                                    <br>
                                                                    dòng tiêu đề của sheet đó.
                                                                </div>
                                                            </div>
                                                            <div class="file-error" v-else>
                                                                <div class="file-error-title red-text"> Lý do không hợp
                                                                    lệ:
                                                                </div>
                                                                <div class="file-error-title red-text">- Dung lượng quá
                                                                    lớn
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                            <div class="form-group flex-row mt-3" v-if="valuesFile[0].FileSelected">
                                                <div class="flex1 mr-10">
                                                    <div class="form-group-label d-flex label-form">
                                                        Sheet danh sách
                                                        <span class="required">*</span>
                                                    </div>
                                                    <div class="ms-input ms-editor w-100">
                                                        <Dropdown v-model="selectedSheet" :options="sheetOptions"
                                                                  optionLabel="sheetName"
                                                                  placeholder="Sheet danh sách"
                                                                  @change="changeSheetIndex"
                                                                  :class="{'error': invalidData['selectedSheet']}"
                                                                  class="ms-category text-start"/>
                                                        <div class="error-text" v-if="invalidData['selectedSheet']">
                                                            {{ invalidData['selectedSheet'] }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex1 mr-10">
                                                    <div class="form-group-label d-flex label-form">
                                                        Dòng tiêu đề
                                                        <span class="required">*</span>
                                                    </div>
                                                    <div class="ms-input d-flex flex-column ms-editor w-100">
                                                        <InputNumber
                                                            v-model="subjectLine"
                                                            :class="{ 'error': invalidData[`subjectLine`] }"
                                                            :min="0"
                                                            :max="100"
                                                            @input="changeSheetIndex"
                                                            :placeholder="MESSAGE.INPUT_PLACEHOLDER_SETUP"
                                                            class="ms-point flex-grow-1"/>
                                                        <div class="error-text" v-if="invalidData['subjectLine']">
                                                            {{ invalidData['subjectLine'] }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex pt-4 justify-content-end" v-if="!isNextStepper">
                                                <Button label="Tiếp tục" icon="pi pi-arrow-right" iconPos="right"
                                                        :disabled="!valuesFile[0].FileSelected"
                                                        @click="fileSelectedOnUpload = valuesFile[0], changeStepperIndex(index)"/>
                                            </div>
                                        </template>
                                    </StepperPanel>
                                    <StepperPanel header="File bài thi">
                                        <template #content="{ prevCallback, nextCallback }">
                                            <div class="flex flex-column h-12rem">
                                                <input type="file" hidden ref="file" accept=".zip"
                                                       id="assetsFieldHandle"
                                                       @change="onChangeFile($event, valuesFile[1])">
                                                <div class="flex1 d-flex" @dragover="dragover" @dragleave="dragleave"
                                                     @drop="drop($event, valuesFile[1])">
                                                    <label for="assetsFieldHandle" class="flex1 upload-wrapper">
                                                        <div class="d-flex flex-grow-1">
                                                            <div
                                                                class="upload-container flex-grow-1 d-flex justify-content-center align-items-center"
                                                                v-if="!valuesFile[1].FileSelected">
                                                                <div class="no-file d-flex file-zip">
                                                                </div>
                                                            </div>
                                                            <div class="import-attachment-container flex1"
                                                                 v-if="valuesFile[1].FileSelected">
                                                                <div class="file-info d-flex">
                                                                    <div class="d-flex text-truncate">
                                                                        <div class="file-icon text-left "
                                                                             style="width: 20px;"></div>
                                                                        <div class="file-name text-left">
                                                                            {{ valuesFile[1].FileSelected.name }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="file-size text-left">
                                                                        {{
                                                                            formatSize(valuesFile[1].FileSelected.size)
                                                                        }}
                                                                    </div>
                                                                    <div class="file-accepted text-left d-flex">
                                                                        <div class="icon-success"
                                                                             v-if="FileSuccess"></div>
                                                                        <div v-if="FileSuccess">Hợp lệ</div>
                                                                        <div class="icon-unsuccess"
                                                                             v-if="!FileSuccess"></div>
                                                                        <div v-if="!FileSuccess">Không hợp lệ</div>
                                                                    </div>
                                                                    <div
                                                                        class="change-file blue-text pointer text-left text-link mw-maxcontent">
                                                                        Đổi
                                                                        tệp khác
                                                                    </div>
                                                                </div>
                                                                <div class="file-caution" v-if="FileSuccess">
                                                                    <div class="file-caution-img">
                                                                    </div>
                                                                    <div class="file-caution-center mt-20 text-center">
                                                                        Lưu ý
                                                                        <span data-v-6d95cd2a="" data-v-30ce9692=""
                                                                              style="color: red;">*</span>
                                                                        : Bạn vui lòng kiểm tra lại danh sách
                                                                        <br>
                                                                        trước khi thực hiện chấm.
                                                                    </div>
                                                                </div>
                                                                <div class="file-error" v-else>
                                                                    <div class="file-error-title red-text"> Lý do không
                                                                        hợp
                                                                        lệ:
                                                                    </div>
                                                                    <div class="file-error-title red-text">- Dung lượng
                                                                        quá
                                                                        lớn
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="flex pt-4 justify-content-between">
                                                <Button label="Back" severity="secondary" icon="pi pi-arrow-left"
                                                        @click="prevCallback, this.activeStep= 0"/>
                                                <Button label="Tiếp tục" icon="pi pi-arrow-right" iconPos="right"
                                                        :disabled="!valuesFile[1].FileSelected"
                                                        @click="fileSelectedOnUpload = valuesFile[1], uploadEvent()"/>
                                            </div>
                                        </template>
                                    </StepperPanel>
                                    <StepperPanel header="Kiểm tra thông tin">
                                        <template #content="{ prevCallback, nextCallback }">
                                            <div class="flex flex-column h-12rem">
                                                <DataTable class="flex1 mt-10 flex-column"
                                                           :class="{ 'loading': isLoading }"
                                                           :loading="isLoading" table-class="grid-group"
                                                           :value="isLoading ? Array.from({ length: 8 }, () => ({ ...this.department })) : fileInfoResponse.data"
                                                           tableStyle="min-width: 100%" rowHover>

                                                    <Column header="Số báo danh" field="candidateNumber"
                                                            style="width: 40px;"
                                                            class="text-center">
                                                        <template #body="{ data, field, slotProps }">
                                                            <div v-if="!isLoading"> {{ data[field] }}</div>
                                                            <div v-else>
                                                                <Skeleton height="18px" class="mb-2"></Skeleton>
                                                            </div>
                                                        </template>
                                                    </Column>
                                                    <Column header="Mã sinh viên" field="studentID" style="width: 100px"
                                                            class="text-center">
                                                        <template #body="{ data, field, slotProps }">
                                                            <div v-if="!isLoading"> {{ data[field] }}</div>
                                                            <div v-else>
                                                                <Skeleton height="18px" class="mb-2"></Skeleton>
                                                            </div>
                                                        </template>
                                                    </Column>
                                                    <Column header="Họ và tên" field="studentName" style="width: 80px;"
                                                            class="text-left">
                                                        <template #body="{ data, field, slotProps }">
                                                            <div v-if="!isLoading"> {{ data[field] }}</div>
                                                            <div v-else>
                                                                <Skeleton height="18px" class="mb-2"></Skeleton>
                                                            </div>
                                                        </template>
                                                    </Column>
                                                    <Column header="Phòng thi" field="departmentName"
                                                            style="width: 100px;"
                                                            class="text-center">
                                                        <template #body="{ data, field, slotProps }">
                                                            <div v-if="!isLoading"> {{ data[field] }}</div>
                                                            <div v-else>
                                                                <Skeleton height="18px" class="mb-2"></Skeleton>
                                                            </div>
                                                        </template>
                                                    </Column>
                                                    <Column header="Đề thi" field="examBankName" style="width: 50px;"
                                                            class="text-center">
                                                        <template #body="{ data, field, slotProps }">
                                                            <div v-if="!isLoading"> {{ data[field] }}</div>
                                                            <div v-else>
                                                                <Skeleton height="18px" class="mb-2"></Skeleton>
                                                            </div>
                                                        </template>
                                                    </Column>
                                                </DataTable>
                                            </div>
                                            <div class="flex pt-4 justify-content-between">
                                                <Button label="Back" severity="secondary" icon="pi pi-arrow-left"
                                                        @click="prevCallback"/>
                                                <Button label="Next" icon="pi pi-arrow-right" iconPos="right"
                                                        @click="nextCallback"/>
                                            </div>
                                        </template>
                                    </StepperPanel>
                                    <StepperPanel header="Chấm thi">
                                        <template #content="{ prevCallback }">
                                            <div class="flex flex-column h-12rem">
                                                <div
                                                    class="border-2 border-dashed surface-border border-round surface-ground flex-auto flex justify-content-center align-items-center font-medium">
                                                </div>
                                            </div>
                                            <div class="flex pt-4 justify-content-start">
                                                <Button label="Back" severity="secondary" icon="pi pi-arrow-left"
                                                        @click="prevCallback"/>
                                                <Button label="Chấm thi" icon="pi pi-arrow-right"
                                                        @click="calculate"/>
                                            </div>
                                        </template>
                                    </StepperPanel>
                                </Stepper>
                            </Panel>
                        </div>
                        <div class="mb-20 flex-column flex1">
                            <Panel header="2. Kết quả chấm" toggleable :collapsed="examResult.length == 0"
                                   class="customPanel flex1" :toggle-button-props="{ 'aria-label': 'customPanel' }">
                                <div class="ms-panel-header">
                                    <div class="flex1"></div>
                                    <!--                                    <Button class="ms-button btn primary has-tooltip" aria-label="Youtube"-->
                                    <!--                                            v-if="examResult.length != 0" @click="exportExamResult">-->
                                    <!--                                        &lt;!&ndash;                                        <img alt="logo"&ndash;&gt;-->
                                    <!--                                        &lt;!&ndash;                                             src="../../../../public/assets/icon/ic_export_excel.cd7bdef7.svg"&ndash;&gt;-->
                                    <!--                                        &lt;!&ndash;                                             class="export-excel" />&ndash;&gt;-->
                                    <!--                                        <span class="px-3 text">Xuất kết quả</span>-->
                                    <!--                                    </Button>-->
                                </div>
                                <DataTable class="flex1 mt-10 flex-column" :class="{ 'loading': isLoading }"
                                           :loading="isLoading" table-class="grid-group"
                                           :value="isLoading ? Array.from({ length: 8 }, () => ({ ...this.department })) : examResult"
                                           tableStyle="min-width: 100%" rowHover>

                                    <Column header="STT" style="width: 40px;" class="text-center">
                                        <template #body="slotProps">
                                            <div> {{ slotProps.index + 1 }}</div>
                                        </template>
                                    </Column>
                                    <Column header="Số báo danh" field="candidate_number" style="width: 40px;"
                                            class="text-center">
                                        <template #body="{ data, field, slotProps }">
                                            <div v-if="!isLoading"> {{ data[field] }}</div>
                                            <div v-else>
                                                <Skeleton height="18px" class="mb-2"></Skeleton>
                                            </div>
                                        </template>
                                    </Column>
                                    <Column header="Họ và tên" field="student_name" style="width: 80px;"
                                            class="text-left">
                                        <template #body="{ data, field, slotProps }">
                                            <div v-if="!isLoading"> {{ data[field] }}</div>
                                            <div v-else>
                                                <Skeleton height="18px" class="mb-2"></Skeleton>
                                            </div>
                                        </template>
                                    </Column>
                                    <Column header="Đề thi" field="exam_bank_name" style="width: 80px;"
                                            class="text-left">
                                        <template #body="{ data, field, slotProps }">
                                            <div v-if="!isLoading"> {{ data[field] }}</div>
                                            <div v-else>
                                                <Skeleton height="18px" class="mb-2"></Skeleton>
                                            </div>
                                        </template>
                                    </Column>
                                    <Column header="Tổng điểm" field="total" style="width: 50px;"
                                            class="text-center">
                                        <template #body="{ data, field, slotProps }">
                                            <div v-if="!isLoading"> {{ data[field] }}</div>
                                            <div v-else>
                                                <Skeleton height="18px" class="mb-2"></Skeleton>
                                            </div>
                                        </template>
                                    </Column>
                                    <Column alignFrozen="right" style="width: 80px;" frozen header="Thao tác">
                                        <template #body="slotProps">
                                            <div class="row-actions" v-if="!isLoading">
                                                <div class="text-link"
                                                     @click="onRowSelect(slotProps.data), selectedResult = slotProps.data, loadExamResultDetail()">
                                                    <div>Xem chi tiết</div>
                                                </div>
                                            </div>
                                            <div v-else>
                                                <Skeleton height="18px" class="mb-2"></Skeleton>
                                            </div>
                                        </template>
                                    </Column>
                                </DataTable>
                            </Panel>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <Dialog v-model:visible="visibleExamResultDetail" modal
            :header="selectedResult ? `Điểm chi tiết ${selectedResult.student_name}` : ''" :style="{ width: '75vw' }"
            scrollable
            closeIcon="close-button">
        <DataTable class="flex1 flex-column" :class="{ 'loading': isLoading }" :loading="isLoading"
                   rowGroupMode="rowspan" groupRowsBy="parent_criteria_id" sortMode="single" sortField="parent_criteria_id" :sortOrder="1"
                   :value="isLoading ? Array.from({ length: 8 }, () => ({ ...this.department })) : resultDetail"
                   tableStyle="min-width: 100%" rowHover>
            <Column field="parent_criteria_id" header="Tiêu chí" style="width: 100px;">
                <template #body="slotProps">
                    <div class="flex align-items-center gap-2">
                        <span>{{ slotProps.data.parent_criteria_name }}</span>
                    </div>
                </template>
            </Column>
            <Column header="STT" style="width: 15px;" class="text-left">
                <template #body="slotProps">
                    <div v-if="!isLoading"> {{ slotProps.index + 1 }}</div>
                    <div v-else>
                        <Skeleton height="18px" class="mb-2"></Skeleton>
                    </div>
                </template>
            </Column>
            <Column header="Mã sinh viên" field="student_code" style="width: 100px;" class="text-left">
                <template #body="{ data, field, slotProps }">
                    <div v-if="!isLoading"> {{ data[field] }}</div>
                    <div v-else>
                        <Skeleton height="18px" class="mb-2"></Skeleton>
                    </div>
                </template>
            </Column>
            <Column header="Đề thi" field="exam_bank_name" style="width: 100px;" class="text-left">
                <template #body="{ data, field, slotProps }">
                    <div v-if="!isLoading"> {{ data[field] }}</div>
                    <div v-else>
                        <Skeleton height="18px" class="mb-2"></Skeleton>
                    </div>
                </template>
            </Column>
            <Column header="Phòng thi" field="department_name" style="width: 100px;" class="text-left">
                <template #body="{ data, field, slotProps }">
                    <div v-if="!isLoading"> {{ data[field] }}</div>
                    <div v-else>
                        <Skeleton height="18px" class="mb-2"></Skeleton>
                    </div>
                </template>
            </Column>
            <Column header="Tiêu chí" field="property_name" style="width: 100px;" class="text-left">
                <template #body="{ data, field, slotProps }">
                    <div v-if="!isLoading"> {{ data[field] }}</div>
                    <div v-else>
                        <Skeleton height="18px" class="mb-2"></Skeleton>
                    </div>
                </template>
            </Column>
            <Column header="Điểm tiêu chí" field="point" style="width: 100px;" class="text-left">
                <template #body="{ data, field, slotProps }">
                    <div v-if="!isLoading"> {{ data[field] }}</div>
                    <div v-else>
                        <Skeleton height="18px" class="mb-2"></Skeleton>
                    </div>
                </template>
            </Column>
            <Column header="Điểm đạt được" field="real_point" style="width: 100px;" class="text-left">
                <template #body="{ data, field, slotProps }">
                    <div v-if="!isLoading"> {{ data[field] }}</div>
                    <div v-else>
                        <Skeleton height="18px" class="mb-2"></Skeleton>
                    </div>
                </template>
            </Column>
        </DataTable>
        <template #footer>
            <Button label="Đóng" class="text-white ms-button text-white btn w-100 danger"
                    @click="visibleExamResultDetail = false, selectedResult = null"/>
        </template>
    </Dialog>

    <Dialog v-model:visible="visibleRefineExam" modal :style="{ width: '60vw' }"
            scrollable
            closeIcon="close-button">
        <template #header>
            <div class="inline-flex align-items-center justify-content-center gap-2">
                <span class="fw-bold fs-16">Tinh chỉnh đề thi</span>
                <div class="mt-2 fw-lighter">Thực hiện sắp xếp đề thi tương ứng với danh sách thi</div>
            </div>
        </template>
        <DataTable class="flex1 flex-column" :class="{ 'loading': isLoading }" :loading="isLoading"
                   table-class="grid-group"
                   :reorderableColumns="true" @rowReorder="onRowReorder"
                   :value="listExamBankForExamShift"
                   tableStyle="min-width: 100%" rowHover>
            <Column rowReorder headerStyle="width: 1.5rem" :reorderableColumn="false"/>
            <Column header="STT" style="width: 15px;" class="text-left">
                <template #body="slotProps">
                    <div> {{ slotProps.index + 1 }}</div>
                </template>
            </Column>
            <Column header="Mã đề thi" field="exam_bank_code" style="width: 100px;" class="text-left">
                <template #body="{ data, field, slotProps }">
                    <div> {{ data[field] }}</div>
                </template>
            </Column>
            <Column header="Tên đề thi" field="exam_bank_name" style="width: 100px;" class="text-left">
                <template #body="{ data, field, slotProps }">
                    <div> {{ data[field] }}</div>
                </template>
            </Column>
        </DataTable>
        <template #footer>
            <div class="d-flex flex-row">
                <div class="flex1"></div>
                <Button
                    class="ms-btn secondary d-flex justify-content-center ms-btn_search ps-3 pe-3 gap-2 me-2"
                    @click="visibleRefineExam = false">
                    <div class="">Đóng</div>
                </Button>
            </div>
        </template>
    </Dialog>
</template>

<script>
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Skeleton from 'primevue/skeleton';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dropdown from 'primevue/dropdown';
import Panel from 'primevue/panel';
import FileUpload from 'primevue/fileupload';
import Stepper from 'primevue/stepper';
import StepperPanel from 'primevue/stepperpanel';
import Resumable from 'resumablejs';
import Dialog from 'primevue/dialog';
import * as XLSX from 'xlsx';
import ProgressBar from 'primevue/progressbar';
import {FILE_TYPE, MESSAGE} from "@/common/enums";
import LoadingProgress from "@/components/LoadingProgress.vue";
import {getExamResultDetail, getExamResult} from "../../../../api/exam-result";
import {get, calculate, getDetailExamManager} from '/api/grade-master';
import Auth from "../../../../api/utils/auth";
import InputNumber from 'primevue/inputnumber';

export default {
    computed: {
        MESSAGE() {
            return MESSAGE
        }
    },
    components: {
        Button,
        InputText,
        Panel,
        Skeleton,
        DataTable,
        Dropdown,
        ProgressBar,
        Column,
        FileUpload,
        Stepper,
        StepperPanel,
        LoadingProgress,
        InputNumber,
        Dialog

    },
    data() {
        return {
            valuesFile: [
                {
                    STT: 1,
                    Type: 'File danh sách thi',
                    Resource: 'Chưa có file danh sách thi. Tải lên để thực hiện chấm',
                    Empty: true,
                    FileSelected: null,
                    FileSelectedOld: null,
                    ResourcePath: null,
                    FileName: null,
                    FileType: FILE_TYPE.LIST,
                    FileAccept: '.xlsx',
                    MaxFileSize: 30 * 1024 * 1024
                },
                {
                    STT: 2,
                    Type: 'File bài thi',
                    Resource: 'Chưa có file bài thi. Tải lên để thực hiện chấm',
                    Empty: true,
                    FileSelected: null,
                    FileSelectedOld: null,
                    ResourcePath: null,
                    FileName: null,
                    FileType: FILE_TYPE.EXAM,
                    FileAccept: '.zip',
                    MaxFileSize: 800 * 1024 * 1024
                },
            ],
            sheetOptions: [],
            selectedSheet: null,
            subjectLine: 2,
            isNextStepper: false,
            columnsFile: [
                {field: 'STT', header: 'STT'},
                {field: 'Type', header: 'Loại file'},
                {field: 'Resource', header: 'Tên File'},
            ],
            isCollapsed: false,
            isLoading: false,
            data: [],
            selectedCity: null,
            examResult: [],
            cities: [
                {name: 'New York', code: 'NY'},
                {name: 'Rome', code: 'RM'},
                {name: 'London', code: 'LDN'},
                {name: 'Istanbul', code: 'IST'},
                {name: 'Paris', code: 'PRS'}
            ],
            headerUploadFile: 'Tải file',
            fileSelectedOnUpload: null,
            gradingType: false, //loại chấm điểm
            isShowGradingType: false,
            gradingTypeVisible: false,
            deductedPoint: 50, //điểm bị trừ
            invalidDeductedPoint: null,

            fileSelected: null,
            isShowPopupUploadFile: false,
            File: null,
            FileSuccess: false,
            isUpload: false,
            totalSize: 0,
            dialogVisible: false,
            totalSizePercent: 0,
            activeStep: 0,
            fileInfoResponse: [],

            resultDetail: [],
            visibleExamResultDetail: false,
            selectedResult: null,
            objCheckSelectedResult: null,


            isLoadingComponent: false,

            modeFile: null,
            fileXlsxSelected: null,
            fileZipSelected: null,

            resumable: null,
            progress: 0,
            invalidData: [],
            listExamBankForExamShift: [],
            isDisableInfo: false,
            visibleRefineExam: false,

            examManager: [],
            selectedManager: null,
            examShift: [],
            selectedExamShift: null,
            department: [],
            selectedDepartment: null,

        }
    },
    methods: {
        onRowSelect(data) {
            // console.log(data);
        },

        async btnEvaluator(flag) {
            //thực hiện chấm
            if (flag) {
                this.gradingTypeVisible = false;
                this.deductedPoint = 50;
                this.isLoadingComponent = true;
                this.isShowGradingType = false;
                var data = this.examManager.find(_item => this.selectedManager == _item.ExamId);
                data = JSON.parse(data.ExamShift).filter(_item => _item.ExamShiftId == this.selectedExamShift && _item.DepartmentId == this.selectedDepartment && _item.ExamShiftId == this.selectedExamShift);
                await excuteEvaluator({
                    data: data,
                    gradingType: this.gradingType,
                    deductedPoint: this.deductedPoint
                }).then(res => {
                    this.loadExamResult();
                }).catch(error => {
                    console.log(error);
                }).finally(() => {
                    setTimeout(() => {
                        this.isLoadingComponent = false;
                    }, 300);
                });
            }
        },

        /**
         * Sự kiện chọn kì thi
         */
        onChangeExamManager() {
            this.valuesFile[1].FileName = this.valuesFile[0].FileName = null;
            this.valuesFile[1].Empty = this.valuesFile[0].Empty = true;
            this.selectedExamShift = null;
            //dữ liệu kì thi
            this.selectedFirst();
            // this.loadExamResult();
        },

        /**
         * Sự kiện chọn ca thi
         */
        onChangeExamShift() {
            this.department = this.selectedExamShift.departments;
            this.selectedDepartment = null;
            this.listExamBankForExamShift = this.selectedExamShift.exam_banks;
        },

        /**
         * Sự kiện chọn phòng thi
         */
        async onChangeDepartment() {
            await this.loadExamResult()
        },

        /**
         *
         * @param {*} arr
         */
        selectedFirst() {
            try {
                this.examShift = this.selectedManager.exam_shifts;
                this.selectedExamShift = null;
                this.selectedDepartment = null;
            } catch (error) {
                console.log(error);
            }
        },

        /**
         * Sự kiện kéo thả file
         * @param {*} event
         * @param data
         */
        drop(event, data) {
            event.preventDefault();
            this.$refs.file.files = event.dataTransfer.files;
            this.onChangeFile(event, data); // Trigger the onChange event manually
        },

        /**
         * Sự kiện kéo file vào
         * @param {*} event
         */
        dragover(event) {
            event.preventDefault();
            // Add some visual fluff to show the user can drop its files
            if (!event.currentTarget.classList.contains('bg-green-300')) {
                event.currentTarget.classList.remove('bg-gray-100');
                event.currentTarget.classList.add('bg-green-300');
            }
        },

        /**
         * Sự kiện kéo file ra
         * @param {*} event
         */
        dragleave(event) {
            // Clean up
            event.currentTarget.classList.add('bg-gray-100');
            event.currentTarget.classList.remove('bg-green-300');
        },

        /**
         * Lấy danh sách đã phân chia đề thi
         */
        getListExam() {
            if (this.selectedDepartment == null) {
                this.showToast("Chưa chọn thông tin kì thi", 'error');
                return;
            }

            if (this.selectedManager == null) {
                this.showToast("Chưa chọn thông tin kì thi", 'error');
                return;
            }

            if (this.selectedExamShift == null) {
                this.showToast("Chưa chọn thông tin kì thi", 'error');
                return;
            }

            if (this.valuesFile[0].FileName == null) {
                this.showToast("Chưa tải lên file danh sách thi", 'error');
                return;
            }

            this.isLoadingComponent = true;
            //dữ liệu kì thi
            var data = this.examManager.find(_item => this.selectedManager == _item.ExamId);
            data = JSON.parse(data.ExamShift);
            //dữ liệu ca thi
            var result = data.filter(_item => _item.ExamShiftId == this.selectedExamShift);
            //Mảng dữ liệu phòng thi
            this.department = this.getUniqueItems(result, 'DepartmentId');

            var index = this.department.findIndex(_item => _item.DepartmentId == this.selectedDepartment);
            data = data.filter(_item => _item.ExamShiftId == this.selectedExamShift && _item.DepartmentId == this.selectedDepartment && _item.ExamShiftId == this.selectedExamShift);
            exportExamList({fileInfo: this.department[index], exam: data}).then(res => {
                var filename = this.department.find(_item => _item.DepartmentId == this.selectedDepartment).DepartmentName;
                const url = window.URL.createObjectURL(new Blob([res]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `Danh sách phân chia đề thi ${filename}.xlsx`);
                document.body.appendChild(link);
                link.click();
            }).catch(error => {

            }).finally(() => {
                setTimeout(() => {
                    this.isLoadingComponent = false;
                }, 750);
            });

            console.log(this.department[index]);

        },

        validateExam() {
            this.invalidData = [];
            if (this.selectedDepartment == null) {
                this.invalidData['department'] = 'Vui lòng chọn phòng thi';
            }

            if (this.selectedManager == null) {
                this.invalidData['exam'] = 'Vui lòng chọn kì thi';
            }

            if (this.selectedExamShift == null) {
                this.invalidData['exam_shift'] = 'Vui lòng chọn ca thi';
            }
            if (this.selectedSheet == null) {
                this.invalidData['selectedSheet'] = 'Vui lòng chọn sheet';
            }
            if (this.subjectLine == null) {
                this.invalidData['subjectLine'] = 'Vui lòng chọn dòng tiêu đề';
            }
            if (Object.keys(this.invalidData).length > 0) {
                return false;
            }
            return true;
        },

        /**
         * Thông tin kì thi
         */
        async loadExamManager() {
            this.isLoading = true;
            await getDetailExamManager().then(res => {
                this.examManager = res.data;
            }).catch(error => {
                console.log(error);
            }).finally(() => {
                setTimeout(() => {
                    this.isLoading = false;
                }, 300);
            })
        },

        changeStepperIndex() {
            this.uploadEvent(this.activeStep);
        },

        /**
         * Click button tinh chỉnh đề thi
         */
        refineExam() {
            this.visibleRefineExam = true;
        },

        onRowReorder(event) {
            this.listExamBankForExamShift = event.value;
            this.$toast.add({severity: 'success', summary: 'Rows Reordered', life: 3000});
        },

        /**
         * Xuất kết quả
         */
        exportExamResult() {
            if (this.selectedDepartment != null && this.selectedExamShift != null && this.selectedManager != null && this.examResult.length > 0) {
                this.isLoadingComponent = true;
                exportExamResult({data: this.examResult}).then(res => {
                    var filename = this.department.find(_item => _item.DepartmentId == this.selectedDepartment).DepartmentName;
                    const url = window.URL.createObjectURL(new Blob([res]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', `Kết quả chấm ${filename}.xlsx`);
                    document.body.appendChild(link);
                    link.click();
                }).catch(error => {
                    this.showToast("Đã xảy ra lỗi, vui lòng liên hệ với nhà phát triển", 'error');
                    console.log(error);
                }).finally(() => {
                    setTimeout(() => {
                        this.isLoadingComponent = false;
                    }, 750);
                });
            }
        },

        /**
         * Sự kiện thay đổi file
         */
        onChangeFile(event, data) {
            event.preventDefault();
            if (this.$refs.file.files[0]) {
                this.fileSelected = this.$refs.file.files[0];
                this.$refs.file.value = null;
                // kiểm tra dung lượng file
                if (data.MaxFileSize < this.fileSelected.size) {
                    this.FileSuccess = false;
                    return;
                }
                this.FileSuccess = true;
                //xlsx
                if (data.FileType === FILE_TYPE.LIST) {
                    this.valuesFile[0].FileSelected = this.fileSelected;
                    var validExts = new Array(".xlsx", ".xls");
                    var fileExt = this.fileSelected.name;
                    this.sheetOptions = [];
                    fileExt = fileExt.substring(fileExt.lastIndexOf('.'));
                    if (validExts.indexOf(fileExt) < 0) {
                        this.contentDialog = ' File danh sách thi chỉ hỗ trợ định dạng *.xlsx';
                        this.dialogVisible = true;
                        return;
                    }
                    //đọc file upload lấy ra danh sách tên các sheet
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        /* Parse data */
                        const bstr = e.target.result;
                        const workbook = XLSX.read(bstr, {type: 'binary'});
                        //gán sheet mặc định là sheet đầu tiên
                        //duyệt các sheet
                        for (let index = 0; index < workbook.SheetNames.length; index++) {
                            this.sheetOptions.push({
                                sheetIndex: index,
                                sheetName: workbook.SheetNames[index]
                            });
                        }
                        this.selectedSheet = this.sheetOptions[0];
                    }
                    reader.readAsBinaryString(this.fileSelected);
                }
                //zip
                else {
                    var validExts = new Array(".zip");
                    var fileExt = this.fileSelected.name;
                    fileExt = fileExt.substring(fileExt.lastIndexOf('.'));
                    this.valuesFile[1].FileSelected = this.fileSelected;
                    if (validExts.indexOf(fileExt) < 0) {
                        this.contentDialog = ' File bài thi chỉ hỗ trợ định dạng *.zip';
                        this.dialogVisible = true;
                        return;
                    }
                }
            }
        },

        /**
         * sự kiện đối giá trị sheet danh sách
         */
        changeSheetIndex() {
            if (this.activeStep === 0) {
                this.valuesFile[this.activeStep].FileSelectedOld = null;
                this.fileSelected = this.valuesFile[this.activeStep].FileSelected;
            }
        },

        /**
         * Click nút upload
         */
        uploadEvent(index) {
            try {
                if (!this.validateExam()) {
                    return;
                }
                if (this.valuesFile[this.activeStep].FileSelectedOld === this.valuesFile[this.activeStep].FileSelected) {
                    this.activeStep++;
                    return;
                }
                this.isDisable = true;
                this.isLoading = true;
                //tên kì thi
                // this.resumable.opts.query.ExamName = this.examManager.find(_item => _item.ExamId == this.selectedManager).ExamName;
                // cập nhật param
                this.resumable.opts.query.departmentId = this.selectedDepartment.id;
                this.resumable.opts.query.examId = this.selectedManager.id;
                this.resumable.opts.query.examShiftId = this.selectedExamShift.id;
                this.resumable.opts.query.fileType = this.fileSelectedOnUpload.FileType;
                this.resumable.opts.query.subjectLine = this.subjectLine;
                this.resumable.opts.query.sheetIndex = this.selectedSheet.sheetIndex;
                this.resumable.opts.query.departmentName = this.selectedDepartment.department_name;
                let listExamBank = [];
                this.listExamBankForExamShift.forEach(item => {
                    let newItem = {
                        id: item.id,
                        exam_bank_name: item.exam_bank_name
                    };
                    listExamBank.push(newItem);
                });
                this.resumable.opts.query.examBanks = JSON.stringify(listExamBank);
                this.resumable.addFile(this.fileSelected);
            } catch (error) {
                console.log(error)
            }
        },

        /**
         * click button chấm thi
         */
        calculate() {
            this.isLoading = true;
            this.examResult = [];
            let params = {
                'cakeListName': this.fileInfoResponse.cakeListName,
                'cakeStudentName': this.fileInfoResponse.cakeStudentName,
                'examId': this.selectedManager.id,
                'department': this.selectedDepartment,
                'listExamBank': this.listExamBankForExamShift,
                'examShift': this.selectedExamShift,
            }
            calculate(params).then(res => {
                this.examResult = res.data;
            }).catch(error => {
                console.log(error)
            }).finally(() => {
                setTimeout(() => {
                    this.isLoading = false;
                }, 300);
            })
        },

        /**
         * Lấy kết quả chấm
         */
        async loadExamResult() {
            this.isLoading = true;
            this.examResult = [];
            if (this.selectedDepartment == null || this.selectedExamShift == null || this.selectedManager == null) {
                return;
            }
            await getExamResult({
                examId: this.selectedManager.id,
                departmentId: this.selectedDepartment.id,
                examShiftId: this.selectedExamShift.id
            }).then(res => {
                this.examResult = res.data;
            }).catch(error => {
                this.$store.dispatch('handleServerError');
                console.log(error);
            }).finally(() => {
                setTimeout(() => {
                    this.isLoading = false;
                }, 750);
            });
            var department = this.department.find(_department => _department.DepartmentId == this.selectedDepartment);

            if (department != null && department.ResourcePathFileList != null) {
                this.valuesFile[0].FileName = department.ResourcePathFileList.substring(3);
                this.valuesFile[0].Empty = false;
            }

            if (department != null && department.ResourcePathFileAssignment != null) {
                this.valuesFile[1].FileName = department.ResourcePathFileAssignment.substring(3);
                this.valuesFile[1].Empty = false;
            }
        },

        /**
         * Khởi tạo Resumable upload file
         */
        createResumable() {
            this.resumable = new Resumable({
                target: 'http://localhost:9000/api/word/upload-file',
                method: 'POST',
                query: {},
                headers: {
                    'Accept': 'application/json',
                    'Content-Disposition': 'form-data; name="title"',
                    'Authorization': 'Bearer ' + Auth.getToken()
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
         * Thêm file
         * @param {*} file
         */
        onFileAdded(file) {
            this.progress = 0;
            this.resumable.upload();
        },

        /**
         *
         * @param {*} file
         */
        onFileProgress(file) {
            this.progress = Math.floor(file.progress() * 100);
        },

        /**
         *
         * @param {*} file
         * @param {*} response
         */
        async onFileSuccess(file, response) {
            this.isDisable = false;
            this.progress = 100;
            this.isLoading = false;
            if (this.activeStep === 0 || this.activeStep === 1) {
                this.valuesFile[this.activeStep].FileSelectedOld = this.fileSelected;
            }
            try {
                console.log(JSON.parse(response))
                this.fileInfoResponse = JSON.parse(response).data;
            } catch (error) {
                console.log(error)
            }
            this.activeStep++;
            this.$store.dispatch('handleSuccess', 'Tải file thành công');
            //cập nhật lại thông tin file trên view
            this.resumable.removeFile(file);
            this.fileSelected = null

        },

        onFileError(file, response) {
            this.isLoading = false;
            this.resumable.removeFile(file);
            if (response) {
                try {
                    response = JSON.parse(response).data?.message
                    this.$store.dispatch('handleError', response)
                } catch (e) {
                    this.$store.dispatch('handleError')
                }
            }
        },

        /**
         * Lấy thông tin chi tiết điểm của sinh viên
         */
        async loadExamResultDetail() {
            this.visibleExamResultDetail = true;
            if (JSON.stringify(this.selectedResult) !== JSON.stringify(this.objCheckSelectedResult)) {
                this.resultDetail = [];
                this.isLoading = true;
                await getExamResultDetail({
                    'student_code': this.selectedResult.student_code,
                    'exam_shift_detail_id': this.selectedResult.exam_shift_detail_id
                }).then(res => {
                    this.resultDetail = res.data;
                    console.log(res);
                }).catch(error => {
                    this.showToast("Đã xảy ra lỗi, vui lòng liên hệ với nhà phát triển");
                    console.log(error);
                }).finally(() => {
                    setTimeout(() => {
                        this.isLoading = false;
                    }, 300);
                })
            }
            this.objCheckSelectedResult = {...this.selectedResult}
        },

        /**
         * Sự kiện trước khi ẩn popup upload file
         */
        afterHideUploadFile() {
            if (this.resumable.isUploading()) {
                this.resumable.cancel();
                this.showToast("File đang được tải lên, bạn có chắc chắn muốn thoát không?");
                return;
            }
            this.progress = 0;
            this.fileSelectedOnUpload = null;
            this.fileSelected = null;
            this.isShowPopupUploadFile = false;
            this.fileSelected = null
        },

        /**
         * Format file size
         * @param {*} bytes Dung lượng cần format
         */
        formatSize(bytes) {
            if (bytes === 0) return "0 B";
            const k = 1024;
            const sizes = ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
        },
    },
    async created() {
        await this.loadExamManager();
        //lấy kết quả chấm
        await this.loadExamResult();
    },

    mounted() {
        this.createResumable();
    },
}
</script>

<style lang="scss">
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

            .icon-unsuccess {
                width: 16px;
                height: 16px;
                background: transparent url('@public/assets/icons/ic_warning_red.68e15434.svg') no-repeat;
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

        .file-error {
            .red-text {
                color: #ef5350;
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
